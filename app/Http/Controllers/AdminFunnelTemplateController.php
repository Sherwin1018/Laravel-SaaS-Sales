<?php

namespace App\Http\Controllers;

use App\Models\FunnelStep;
use App\Models\FunnelTemplate;
use App\Models\FunnelTemplateAsset;
use App\Models\FunnelTemplateStep;
use App\Models\FunnelTemplateStepRevision;
use App\Services\FunnelTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminFunnelTemplateController extends Controller
{
    private const MAX_STEP_REVISIONS = 40;
    private const MAX_MANUAL_VERSIONS = 25;

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $showLegacy = $request->boolean('legacy');

        $templates = FunnelTemplate::query()
            ->when(!$showLegacy, function ($query) {
                $query->where('template_type', '!=', FunnelTemplate::TEMPLATE_TYPE_UNCATEGORIZED);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->withCount('steps')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.funnel-templates._rows', compact('templates'))->render();
        }

        return view('admin.funnel-templates.index', compact('templates', 'search', 'showLegacy'));
    }

    public function create()
    {
        return view('admin.funnel-templates.create', [
            'templateTypeOptions' => FunnelTemplate::selectableTemplateTypes(),
            'templateFunnelPurposeOptions' => FunnelTemplate::FUNNEL_PURPOSE_OPTIONS,
        ]);
    }

    public function import()
    {
        return view('admin.funnel-templates.import', [
            'templateTypeOptions' => FunnelTemplate::selectableTemplateTypes(),
            'templateFunnelPurposeOptions' => FunnelTemplate::FUNNEL_PURPOSE_OPTIONS,
        ]);
    }

    public function importFromFile(Request $request, FunnelTemplateService $templateService)
    {
        $path = base_path('animals-template.json');
        if (!is_file($path)) {
            return redirect()->route('admin.funnel-templates.index')
                ->with('error', 'Local template file not found: animals-template.json');
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return redirect()->route('admin.funnel-templates.index')
                ->with('error', 'Unable to read local template file.');
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return redirect()->route('admin.funnel-templates.index')
                ->with('error', 'Local template JSON is invalid.');
        }

        try {
            $template = $templateService->importTemplateFromJson($decoded, auth()->user(), [
                'template_type' => 'single_page',
                'publish' => true,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('admin.funnel-templates.index')
                ->with('error', 'Template import failed from local file.');
        }

        return redirect()->route('admin.funnel-templates.edit', $template)
            ->with('success', 'Template imported from local file.');
    }

    public function store(Request $request, FunnelTemplateService $templateService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'template_type' => ['required', Rule::in(array_keys(FunnelTemplate::selectableTemplateTypes()))],
            'funnel_purpose' => ['required', Rule::in(array_keys(FunnelTemplate::FUNNEL_PURPOSE_OPTIONS))],
            'template_tags' => 'nullable|string|max:500',
        ]);

        try {
            $validated['template_tags'] = $this->attachFunnelPurposeTag(
                $this->normalizeTemplateTags($validated['template_tags'] ?? ''),
                (string) ($validated['funnel_purpose'] ?? 'service')
            );
            $template = $templateService->createStarterTemplate($validated, auth()->user());
            return redirect()->route('admin.funnel-templates.edit', $template)->with('success', 'Template created successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Template creation failed.');
        }
    }

    public function importStore(Request $request, FunnelTemplateService $templateService)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'description' => 'nullable|string|max:2000',
            'template_type' => ['required', Rule::in(array_keys(FunnelTemplate::selectableTemplateTypes()))],
            'funnel_purpose' => ['required', Rule::in(array_keys(FunnelTemplate::FUNNEL_PURPOSE_OPTIONS))],
            'template_tags' => 'nullable|string|max:500',
            'import_json' => 'required|string',
            'publish_now' => 'nullable|boolean',
        ]);

        try {
            $decoded = json_decode((string) $validated['import_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return redirect()->back()->withInput()->with('error', 'Import JSON is invalid. Please paste valid JSON.');
        }

        try {
            $template = $templateService->importTemplateFromJson($decoded, auth()->user(), [
                'name' => trim((string) ($validated['name'] ?? '')) !== '' ? $validated['name'] : null,
                'description' => array_key_exists('description', $validated) ? $validated['description'] : null,
                'template_type' => $validated['template_type'],
                'template_tags' => $this->attachFunnelPurposeTag(
                    $this->normalizeTemplateTags($validated['template_tags'] ?? ''),
                    (string) ($validated['funnel_purpose'] ?? 'service')
                ),
                'publish' => (bool) $request->boolean('publish_now'),
            ]);

            return redirect()->route('admin.funnel-templates.edit', $template)->with('success', 'Template imported successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Template import failed. Make sure the JSON includes at least one step or layout.');
        }
    }

    public function edit(FunnelTemplate $funnelTemplate)
    {
        $funnelTemplate->load(['steps.revisions']);
        $seededMissingRevisions = false;
        foreach ($funnelTemplate->steps as $step) {
            if ($this->ensureStepHasInitialRevision($step)) {
                $seededMissingRevisions = true;
            }
        }
        if ($seededMissingRevisions) {
            $funnelTemplate->load(['steps.revisions']);
        }

        $defaultStep = $funnelTemplate->steps->sortBy('position')->first();

        return view('funnels.edit', [
            'funnel' => $funnelTemplate,
            'stepTypes' => FunnelStep::TYPES,
            'stepLayouts' => FunnelStep::LAYOUTS,
            'stepTemplates' => FunnelStep::TEMPLATES,
            'defaultStepId' => $defaultStep?->id,
            'builderMode' => 'template',
            'builderSingleScrollMode' => $this->singleScrollModeEnabledForTemplate(auth()->user(), $funnelTemplate),
            'builderUpdateUrl' => route('admin.funnel-templates.update', $funnelTemplate),
            'builderPublishUrl' => route('admin.funnel-templates.publish', $funnelTemplate),
            'builderUnpublishUrl' => route('admin.funnel-templates.unpublish', $funnelTemplate),
            'builderExitUrl' => route('admin.funnel-templates.index'),
            'builderSaveUrl' => route('admin.funnel-templates.builder.layout.save', $funnelTemplate),
            'builderAssetLibraryUrl' => route('admin.funnel-templates.builder.assets.index', $funnelTemplate),
            'builderAssetDeleteUrl' => route('admin.funnel-templates.builder.assets.destroy', $funnelTemplate),
            'builderUploadUrl' => route('admin.funnel-templates.builder.image.upload', $funnelTemplate),
            'builderPreviewUrlTemplate' => route('admin.funnel-templates.preview', ['funnel_template' => $funnelTemplate, 'step' => '__STEP__']),
            'builderTestUrlTemplate' => route('admin.funnel-templates.test', ['funnel_template' => $funnelTemplate, 'step' => '__STEP__']),
            'builderStepVersionUrlTemplate' => route('admin.funnel-templates.steps.versions.store', ['funnel_template' => $funnelTemplate, 'step' => '__STEP__']),
            'builderStepStoreUrl' => route('admin.funnel-templates.steps.store', $funnelTemplate),
            'builderStepUpdateUrlTemplate' => route('admin.funnel-templates.steps.update', ['funnel_template' => $funnelTemplate, 'step' => '__STEP__']),
            'builderStepDeleteUrlTemplate' => route('admin.funnel-templates.steps.destroy', ['funnel_template' => $funnelTemplate, 'step' => '__STEP__']),
            'builderStepReorderUrl' => route('admin.funnel-templates.steps.reorder', $funnelTemplate),
            'builderPublicStepUrlTemplate' => '#',
            'builderTagPlaceholder' => 'Template description and layout are managed here.',
            'builderTagInputDisabled' => true,
            'builderTagValue' => '',
            'builderSharedTemplates' => $this->builderSharedTemplatesPayload(),
            'builderPurpose' => $funnelTemplate->resolvedFunnelPurpose(),
        ]);
    }

    private function singleScrollModeEnabledForTemplate($user, FunnelTemplate $template): bool
    {
        $activeStepCount = $template->relationLoaded('steps')
            ? $template->steps->where('is_active', true)->count()
            : $template->steps()->where('is_active', true)->count();

        if ($activeStepCount > 1) {
            return false;
        }

        $templateTags = collect($template->template_tags ?? [])
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter();

        if ($templateTags->contains('__single_scroll') || $templateTags->contains('single-scroll')) {
            return true;
        }

        return FunnelTemplate::normalizeTemplateType($template->template_type) === 'single_page';
    }

    private function builderSharedTemplatesPayload(): array
    {
        return FunnelTemplate::query()
            ->where('status', 'published')
            ->where('template_type', '!=', FunnelTemplate::TEMPLATE_TYPE_UNCATEGORIZED)
            ->with(['steps' => fn ($query) => $query->orderBy('position')])
            ->latest('published_at')
            ->latest('id')
            ->get()
            ->map(function (FunnelTemplate $template) {
                $steps = $template->steps
                    ->sortBy('position')
                    ->values()
                    ->map(function ($step) {
                        return [
                            'id' => $step->id,
                            'title' => $step->title,
                            'slug' => $step->slug,
                            'type' => $step->type,
                            'subtitle' => $step->subtitle,
                            'content' => $step->content,
                            'cta_label' => $step->cta_label,
                            'price' => $step->price,
                            'position' => $step->position,
                            'is_active' => (bool) $step->is_active,
                            'template' => $step->template,
                            'template_data' => $step->template_data,
                            'step_tags' => $step->step_tags,
                            'background_color' => $step->background_color,
                            'button_color' => $step->button_color,
                            'layout_style' => $step->layout_style,
                            'layout_json' => $step->layout_json,
                        ];
                    })
                    ->all();

                $firstType = (string) data_get($steps, '0.type', 'custom');
                $preview = in_array($firstType, ['checkout', 'sales'], true)
                    ? 'pricing'
                    : (in_array($firstType, ['opt_in', 'form'], true) ? 'lead' : 'hero');
                $stepTypeTags = collect($steps)
                    ->pluck('type')
                    ->filter()
                    ->map(fn ($type) => strtoupper(str_replace('_', ' ', (string) $type)))
                    ->unique()
                    ->take(2)
                    ->values()
                    ->all();

                return [
                    'id' => 'shared_template_' . $template->id,
                    'template_id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description ?: 'Saved super-admin funnel template.',
                    'funnel_purpose' => $template->resolvedFunnelPurpose(),
                    'status' => (string) $template->status,
                    'update_url' => route('admin.funnel-templates.update', $template),
                    'preview' => $preview,
                    'preview_image' => $template->preview_image,
                    'tags' => $this->templateCardTags($template, count($steps), $stepTypeTags),
                    'steps' => $steps,
                ];
            })
            ->all();
    }

    private function normalizeTemplateTags(mixed $raw): array
    {
        $values = is_array($raw) ? $raw : (preg_split('/[,\\n]+/', (string) $raw) ?: []);

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    private function attachFunnelPurposeTag(array $tags, string $funnelPurpose): array
    {
        $normalizedPurpose = FunnelTemplate::normalizeFunnelPurpose($funnelPurpose);
        $purposeTag = FunnelTemplate::PURPOSE_TAG_PREFIX . $normalizedPurpose;

        $cleaned = collect($tags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->reject(function (string $tag) {
                return str_starts_with(mb_strtolower($tag), FunnelTemplate::PURPOSE_TAG_PREFIX);
            })
            ->values();

        $cleaned->push($purposeTag);

        return $cleaned
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    private function templateCardTags(FunnelTemplate $template, int $stepCount, array $fallbackStepTypeTags): array
    {
        $custom = collect($template->template_tags ?? [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->values()
            ->all();

        if ($custom !== []) {
            return $custom;
        }

        return array_values(array_filter(array_merge(
            [$template->templateTypeLabel()],
            [$stepCount . ' Pages'],
            $fallbackStepTypeTags,
            [strtoupper((string) $template->status)]
        )));
    }

    public function update(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateService $templateService)
    {
        if ($request->wantsJson() && $request->has('purpose') && ! $request->has('name')) {
            $purpose = FunnelTemplate::normalizeFunnelPurpose($request->input('purpose', 'service'));
            $tags = $this->attachFunnelPurposeTag($funnelTemplate->template_tags ?? [], $purpose);
            $funnelTemplate->update(['template_tags' => $tags]);
            return response()->json([
                'message' => 'Purpose updated.',
                'funnel' => ['purpose' => $purpose],
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'template_type' => ['nullable', Rule::in(array_keys(FunnelTemplate::TEMPLATE_TYPES))],
            'status' => ['nullable', Rule::in(array_keys(FunnelTemplate::STATUSES))],
            'template_tags' => ['nullable'],
        ]);

        try {
            $templateTags = $request->has('template_tags')
                ? $this->normalizeTemplateTags($request->input('template_tags'))
                : $funnelTemplate->template_tags;
            $funnelTemplate->update([
                'name' => $validated['name'],
                'slug' => $templateService->generateUniqueTemplateSlug($validated['name'], $funnelTemplate->id),
                'description' => $validated['description'] ?? null,
                'template_type' => $validated['template_type'] ?? $funnelTemplate->template_type,
                'template_tags' => $templateTags,
                'status' => $validated['status'] ?? $funnelTemplate->status,
            ]);

            return redirect()->back()->with('success', 'Template updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Template update failed.');
        }
    }

    public function destroy(FunnelTemplate $funnelTemplate)
    {
        $assets = $funnelTemplate->assets()->get();
        foreach ($assets as $asset) {
            try {
                Storage::disk((string) ($asset->disk ?: 'public'))->delete($asset->path);
            } catch (\Throwable $e) {
                // Ignore storage errors so DB cleanup still happens.
            }
            $asset->delete();
        }

        $funnelTemplate->steps()->with('revisions')->get()->each(function (FunnelTemplateStep $step) {
            $step->revisions()->delete();
            $step->delete();
        });

        $funnelTemplate->delete();

        return redirect()
            ->route('admin.funnel-templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function publish(FunnelTemplate $funnelTemplate)
    {
        $validated = request()->validate([
            'description' => 'nullable|string|max:2000',
        ]);

        $issues = $this->validatePublishReadiness(
            $funnelTemplate->steps()->where('is_active', true)->orderBy('position')->get(),
            (string) $funnelTemplate->template_type
        );
        if ($issues !== []) {
            return redirect()->back()->with('error', implode(' ', $issues));
        }

        $funnelTemplate->update([
            'description' => $validated['description'] ?? $funnelTemplate->description,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Template published successfully.');
    }

    public function unpublish(FunnelTemplate $funnelTemplate)
    {
        $funnelTemplate->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return redirect()->back()->with('success', 'Template unpublished successfully.');
    }

    public function preview(FunnelTemplate $funnelTemplate, ?FunnelTemplateStep $step = null)
    {
        $steps = $funnelTemplate->steps()->where('is_active', true)->orderBy('position')->get()->values();
        abort_if($steps->isEmpty(), 404);
        if ($step && (int) $step->funnel_template_id !== (int) $funnelTemplate->id) {
            abort(404);
        }

        $resolvedStep = $step ?: $steps->first();

        return view('funnels.portal.step', [
            'funnel' => $funnelTemplate,
            'step' => $resolvedStep,
            'nextStep' => $this->nextStep($steps, $resolvedStep->id),
            'allSteps' => $steps,
            'isFirstStep' => (int) $steps->first()->id === (int) $resolvedStep->id,
            'isPreview' => true,
            'selectedPricing' => null,
        ]);
    }

    public function test(Request $request, FunnelTemplate $funnelTemplate, ?FunnelTemplateStep $step = null)
    {
        [$steps, $resolvedStep] = $this->resolveTemplateTestStepContext($funnelTemplate, $step);
        $selectedPricing = $this->syncTemplateTestSelectedPricing(
            $request,
            $funnelTemplate,
            (int) $steps->first()->id === (int) $resolvedStep->id
        );

        return view('funnels.portal.step', [
            'funnel' => $funnelTemplate,
            'step' => $resolvedStep,
            'nextStep' => $this->nextStep($steps, $resolvedStep->id),
            'allSteps' => $steps,
            'isFirstStep' => (int) $steps->first()->id === (int) $resolvedStep->id,
            'isPreview' => false,
            'isTemplateTest' => true,
            'selectedPricing' => $selectedPricing,
            'productInventory' => [],
            'approvedReviews' => collect(),
            'reviewPrefill' => [
                'name' => '',
                'email' => '',
            ],
            'reviewAlreadySubmitted' => false,
            'currentJourneyReview' => null,
        ]);
    }

    public function testOptIn(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        [$steps, $resolvedStep] = $this->resolveTemplateTestStepContext($funnelTemplate, $step, 'opt_in');
        $request->validate([
            'website' => 'nullable|string|size:0',
        ]);

        return $this->redirectAfterTemplateTestStep($funnelTemplate, $steps, $resolvedStep);
    }

    public function testCheckout(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        [$steps, $resolvedStep] = $this->resolveTemplateTestStepContext($funnelTemplate, $step, 'checkout');
        $request->validate([
            'amount' => 'nullable',
            'website' => 'nullable|string|size:0',
            'checkout_pricing_id' => 'nullable|string|max:120',
            'checkout_pricing_source_step' => 'nullable|string|max:120',
            'checkout_pricing_plan' => 'nullable|string|max:200',
            'checkout_pricing_price' => 'nullable|string|max:120',
            'checkout_pricing_regular_price' => 'nullable|string|max:120',
            'checkout_pricing_period' => 'nullable|string|max:60',
            'checkout_pricing_subtitle' => 'nullable|string|max:300',
            'checkout_pricing_badge' => 'nullable|string|max:80',
            'checkout_pricing_image' => 'nullable|string|max:2000',
            'checkout_pricing_features' => 'nullable|string|max:5000',
        ]);

        $selection = $this->templateTestPricingSelectionFromCheckoutPayload($request->all());
        if (is_array($selection)) {
            session()->put($this->templateTestSelectedPricingSessionKey($funnelTemplate->id), $selection);
        }

        return $this->redirectAfterTemplateTestStep($funnelTemplate, $steps, $resolvedStep);
    }

    public function testOffer(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        [$steps, $resolvedStep] = $this->resolveTemplateTestStepContext($funnelTemplate, $step);
        abort_unless(in_array($resolvedStep->type, ['upsell', 'downsell'], true), 422, 'Invalid template offer step.');

        $validated = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'decline'])],
            'website' => 'nullable|string|size:0',
            'checkout_pricing_id' => 'nullable|string|max:120',
            'checkout_pricing_source_step' => 'nullable|string|max:120',
            'checkout_pricing_plan' => 'nullable|string|max:200',
            'checkout_pricing_price' => 'nullable|string|max:120',
            'checkout_pricing_regular_price' => 'nullable|string|max:120',
            'checkout_pricing_period' => 'nullable|string|max:60',
            'checkout_pricing_subtitle' => 'nullable|string|max:300',
            'checkout_pricing_badge' => 'nullable|string|max:80',
            'checkout_pricing_image' => 'nullable|string|max:2000',
            'checkout_pricing_features' => 'nullable|string|max:4000',
        ]);

        $selection = $this->templateTestPricingSelectionFromCheckoutPayload($validated);
        if (is_array($selection)) {
            session()->put($this->templateTestSelectedPricingSessionKey($funnelTemplate->id), $selection);
        }

        return $this->redirectAfterTemplateTestOfferDecision($funnelTemplate, $steps, $resolvedStep, $validated['decision'] === 'accept');
    }

    public function saveLayout(Request $request, FunnelTemplate $funnelTemplate)
    {
        $validated = $request->validate([
            'step_id' => [
                'required',
                'integer',
                Rule::exists('funnel_template_steps', 'id')->where(fn ($q) => $q->where('funnel_template_id', $funnelTemplate->id)),
            ],
            'layout_json' => 'required',
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'skip_revision' => ['nullable', 'boolean'],
        ]);

        $rawLayout = $validated['layout_json'];
        if (is_string($rawLayout)) {
            $decoded = json_decode($rawLayout, true);
            $rawLayout = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($rawLayout)) {
            $rawLayout = [];
        }
        if (! isset($rawLayout['root']) && ! isset($rawLayout['sections'])) {
            $rawLayout = ['root' => [], 'sections' => []];
        }

        $step = $funnelTemplate->steps()->where('id', $validated['step_id'])->firstOrFail();
        $skipRevision = (bool) ($validated['skip_revision'] ?? false);
        if (! $skipRevision) {
            $this->rememberStepRevision($step, $this->normalizeRevisionLayout($step->layout_json), $this->normalizeRevisionBackground($step->background_color));
        }

        $layout = $this->enforceBuilderStructureRules($rawLayout);
        $this->mergeElementSizeFromRaw($layout, $rawLayout);

        $step->update([
            'layout_json' => $layout,
            'background_color' => $validated['background_color'] ?? null,
        ]);

        if (! $skipRevision) {
            $this->rememberStepRevision($step, $layout, $this->normalizeRevisionBackground($step->background_color));
        }

        $step->load('revisions');

        return response()->json([
            'message' => 'Layout saved successfully.',
            'step_id' => $step->id,
            'layout_json' => $layout,
            'background_color' => $step->background_color,
            'revision_history' => $this->revisionHistoryPayload($step),
        ]);
    }

    public function builderAssets(Request $request, FunnelTemplate $funnelTemplate)
    {
        $kind = trim((string) $request->query('kind', ''));

        $assets = $funnelTemplate->assets()
            ->get()
            ->map(fn (FunnelTemplateAsset $asset) => $this->builderAssetPayload($asset))
            ->filter()
            ->when($kind !== '', fn ($items) => $items->where('kind', $kind))
            ->values()
            ->all();

        return response()->json(['assets' => $assets]);
    }

    public function destroyBuilderAssets(Request $request, FunnelTemplate $funnelTemplate)
    {
        $validated = $request->validate([
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string'],
        ]);

        $paths = collect($validated['paths'])->map(fn ($path) => trim((string) $path))->filter()->values();
        $assets = $funnelTemplate->assets()->whereIn('path', $paths)->get();

        foreach ($assets as $asset) {
            Storage::disk((string) ($asset->disk ?: 'public'))->delete($asset->path);
            $asset->delete();
        }

        return response()->json(['message' => 'Assets deleted successfully.']);
    }

    public function uploadBuilderImage(Request $request, FunnelTemplate $funnelTemplate)
    {
        if (! $request->hasFile('image')) {
            return response()->json(['message' => 'No file received.'], 422);
        }

        $validated = $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,wmv,mkv,webm,m4v,3gp,ogv|max:102400',
        ]);

        $file = $validated['image'];
        $path = $file->store('funnel-builder/templates/' . $funnelTemplate->id, 'public');
        $asset = FunnelTemplateAsset::updateOrCreate(
            ['disk' => 'public', 'path' => $path],
            [
                'funnel_template_id' => $funnelTemplate->id,
                'created_by' => auth()->id(),
                'kind' => $this->builderAssetKindFromPath($path),
                'original_name' => $file->getClientOriginalName(),
                'size' => (int) $file->getSize(),
            ]
        );

        return response()->json([
            'message' => 'Uploaded successfully.',
            'asset' => $this->builderAssetPayload($asset),
            'url' => $this->builderPublicAssetUrl($path, 'public'),
        ]);
    }

    public function storeStep(Request $request, FunnelTemplate $funnelTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('funnel_template_steps', 'slug')->where(fn ($q) => $q->where('funnel_template_id', $funnelTemplate->id)),
            ],
            'type' => ['required', Rule::in(array_keys(FunnelStep::TYPES))],
            'template' => ['nullable', Rule::in(array_keys(FunnelStep::TEMPLATES))],
            'template_data' => 'nullable|array',
            'content' => 'nullable|string|max:6000',
            'hero_image' => 'nullable|image|max:2048',
            'layout_style' => ['nullable', Rule::in(array_keys(FunnelStep::LAYOUTS))],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cta_label' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0.01',
            'step_tags' => 'nullable|string|max:500',
        ]);

        try {
            $position = (int) $funnelTemplate->steps()->max('position') + 1;
            $heroUrl = null;
            if ($request->hasFile('hero_image')) {
                $path = $request->file('hero_image')->store('funnel-template-heroes', 'public');
                $heroUrl = Storage::url($path);
            }

            $step = $funnelTemplate->steps()->create([
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'template' => $validated['template'] ?? 'simple',
                'template_data' => $validated['template_data'] ?? null,
                'step_tags' => $this->normalizeTagsString($validated['step_tags'] ?? null),
                'content' => $validated['content'] ?? null,
                'hero_image_url' => $heroUrl,
                'layout_style' => $validated['layout_style'] ?? 'centered',
                'background_color' => $validated['background_color'] ?? null,
                'button_color' => $validated['button_color'] ?? null,
                'cta_label' => $validated['cta_label'] ?? null,
                'price' => $validated['price'] ?? null,
                'layout_json' => ['root' => [], 'sections' => []],
                'position' => $position,
                'is_active' => true,
            ]);
            $this->ensureStepHasInitialRevision($step);

            return response()->json([
                'message' => 'Added Successfully',
                'step' => $this->builderStepPayload($step),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Added Failed'], 422);
        }
    }

    public function updateStep(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        if ((int) $step->funnel_template_id !== (int) $funnelTemplate->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('funnel_template_steps', 'slug')->where(fn ($q) => $q->where('funnel_template_id', $funnelTemplate->id))->ignore($step->id),
            ],
            'type' => ['required', Rule::in(array_keys(FunnelStep::TYPES))],
            'template' => ['nullable', Rule::in(array_keys(FunnelStep::TEMPLATES))],
            'template_data' => 'nullable|array',
            'content' => 'nullable|string|max:6000',
            'hero_image' => 'nullable|image|max:2048',
            'layout_style' => ['nullable', Rule::in(array_keys(FunnelStep::LAYOUTS))],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'button_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'cta_label' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
            'step_tags' => 'nullable|string|max:500',
        ]);

        try {
            $heroUrl = $step->hero_image_url;
            if ($request->hasFile('hero_image')) {
                $path = $request->file('hero_image')->store('funnel-template-heroes', 'public');
                $heroUrl = Storage::url($path);
            }

            $step->update([
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'template' => $validated['template'] ?? ($step->template ?: 'simple'),
                'template_data' => $validated['template_data'] ?? null,
                'step_tags' => $this->normalizeTagsString($validated['step_tags'] ?? null),
                'content' => $validated['content'] ?? null,
                'hero_image_url' => $heroUrl,
                'layout_style' => $validated['layout_style'] ?? ($step->layout_style ?: 'centered'),
                'background_color' => $validated['background_color'] ?? $step->background_color,
                'button_color' => $validated['button_color'] ?? $step->button_color,
                'cta_label' => $validated['cta_label'] ?? null,
                'price' => $validated['price'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? $step->is_active),
            ]);

            return response()->json([
                'message' => 'Edited Successfully',
                'step' => $this->builderStepPayload($step),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Edited Failed'], 422);
        }
    }

    public function destroyStep(FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        if ((int) $step->funnel_template_id !== (int) $funnelTemplate->id) {
            abort(404);
        }
        if ((int) $funnelTemplate->steps()->count() <= 1) {
            return response()->json(['message' => 'Cannot delete the last page.'], 422);
        }

        $step->delete();
        return response()->json(['message' => 'Deleted Successfully']);
    }

    public function reorderSteps(Request $request, FunnelTemplate $funnelTemplate)
    {
        $validated = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'required|integer',
        ]);

        $ids = collect($validated['order'])->map(fn ($id) => (int) $id)->values();
        $existingIds = $funnelTemplate->steps()->pluck('id')->map(fn ($id) => (int) $id)->values();
        if ($ids->sort()->values()->all() !== $existingIds->sort()->values()->all()) {
            abort(422, 'Invalid step order.');
        }

        foreach ($ids as $index => $id) {
            $funnelTemplate->steps()->where('id', $id)->update(['position' => $index + 1]);
        }

        return response()->json([
            'message' => 'Edited Successfully',
            'order' => $ids->all(),
        ]);
    }

    public function storeVersion(Request $request, FunnelTemplate $funnelTemplate, FunnelTemplateStep $step)
    {
        if ((int) $step->funnel_template_id !== (int) $funnelTemplate->id) {
            abort(404);
        }

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:120'],
        ]);

        $step->revisions()->create([
            'user_id' => auth()->id(),
            'layout_json' => $this->normalizeRevisionLayout($step->layout_json),
            'background_color' => $this->normalizeRevisionBackground($step->background_color),
            'version_type' => 'manual',
            'label' => $this->normalizeManualVersionLabel($validated['label'] ?? null),
        ]);

        $manualKeepIds = $step->revisions()
            ->reorder()
            ->orderByDesc('id')
            ->get()
            ->filter(fn (FunnelTemplateStepRevision $revision) => (string) ($revision->version_type ?? 'autosave') === 'manual')
            ->take(self::MAX_MANUAL_VERSIONS)
            ->pluck('id');

        if ($manualKeepIds->isNotEmpty()) {
            $step->revisions()
                ->reorder()
                ->where('version_type', 'manual')
                ->whereNotIn('id', $manualKeepIds)
                ->delete();
        }

        $step->load('revisions');

        return response()->json([
            'message' => 'Version saved successfully.',
            'manual_versions' => $this->manualVersionPayload($step),
        ]);
    }

    private function nextStep($steps, int $currentStepId): ?FunnelTemplateStep
    {
        $ordered = collect($steps)->values();
        $index = $ordered->search(fn ($candidate) => (int) $candidate->id === (int) $currentStepId);
        if ($index === false) {
            return null;
        }

        return $ordered->get($index + 1);
    }

    private function resolveTemplateTestStepContext(
        FunnelTemplate $funnelTemplate,
        ?FunnelTemplateStep $step = null,
        ?string $expectedType = null
    ): array {
        $steps = $funnelTemplate->steps()->where('is_active', true)->orderBy('position')->get()->values();
        abort_if($steps->isEmpty(), 404);
        if ($step && (int) $step->funnel_template_id !== (int) $funnelTemplate->id) {
            abort(404);
        }

        $resolvedStep = $step ?: $steps->first();
        abort_if(! $resolvedStep, 404);

        if ($expectedType !== null) {
            abort_unless($resolvedStep->type === $expectedType, 422, 'Invalid template step type.');
        }

        return [$steps, $resolvedStep];
    }

    private function templateTestSelectedPricingSessionKey(int $templateId): string
    {
        return "template_test_selected_pricing_{$templateId}";
    }

    private function currentTemplateTestSelectedPricing(int $templateId): ?array
    {
        $selection = session()->get($this->templateTestSelectedPricingSessionKey($templateId));
        return is_array($selection) ? $selection : null;
    }

    private function templateTestPricingSelectionFromCheckoutPayload(array $payload): ?array
    {
        $pricingId = trim((string) ($payload['checkout_pricing_id'] ?? ''));
        $sourceStepSlug = strtolower(trim((string) ($payload['checkout_pricing_source_step'] ?? '')));
        $plan = mb_substr(trim((string) ($payload['checkout_pricing_plan'] ?? '')), 0, 200);
        $price = mb_substr(trim((string) ($payload['checkout_pricing_price'] ?? '')), 0, 120);
        $regularPrice = mb_substr(trim((string) ($payload['checkout_pricing_regular_price'] ?? '')), 0, 120);
        $period = mb_substr(trim((string) ($payload['checkout_pricing_period'] ?? '')), 0, 60);
        $subtitle = mb_substr(trim((string) ($payload['checkout_pricing_subtitle'] ?? '')), 0, 300);
        $badge = mb_substr(trim((string) ($payload['checkout_pricing_badge'] ?? '')), 0, 80);
        $image = mb_substr(trim((string) ($payload['checkout_pricing_image'] ?? '')), 0, 2000);
        $features = [];
        $rawFeatures = trim((string) ($payload['checkout_pricing_features'] ?? ''));
        if ($rawFeatures !== '') {
            $decoded = json_decode($rawFeatures, true);
            if (is_array($decoded)) {
                foreach ($decoded as $feature) {
                    if (! is_scalar($feature)) {
                        continue;
                    }
                    $featureText = mb_substr(trim((string) $feature), 0, 200);
                    if ($featureText !== '') {
                        $features[] = $featureText;
                    }
                }
            }
        }

        if (
            $pricingId === ''
            && $sourceStepSlug === ''
            && $plan === ''
            && $price === ''
            && $regularPrice === ''
            && $period === ''
            && $subtitle === ''
            && $badge === ''
            && $image === ''
            && $features === []
        ) {
            return null;
        }

        return [
            'pricingId' => $pricingId,
            'sourceStepSlug' => $sourceStepSlug,
            'plan' => $plan,
            'price' => $price,
            'regularPrice' => $regularPrice,
            'period' => $period,
            'subtitle' => $subtitle,
            'badge' => $badge,
            'image' => $image,
            'features' => $features,
        ];
    }

    private function templateTestPricingSelectionFromQuery(Request $request): ?array
    {
        return $this->templateTestPricingSelectionFromCheckoutPayload([
            'checkout_pricing_id' => $request->query('offer_pricing', ''),
            'checkout_pricing_source_step' => $request->query('offer_step', ''),
            'checkout_pricing_plan' => $request->query('offer_plan', ''),
            'checkout_pricing_price' => $request->query('offer_price', ''),
            'checkout_pricing_regular_price' => $request->query('offer_regular_price', ''),
            'checkout_pricing_period' => $request->query('offer_period', ''),
            'checkout_pricing_subtitle' => $request->query('offer_subtitle', ''),
            'checkout_pricing_badge' => $request->query('offer_badge', ''),
            'checkout_pricing_image' => $request->query('offer_image', ''),
            'checkout_pricing_features' => $request->query('offer_features', ''),
        ]);
    }

    private function syncTemplateTestSelectedPricing(Request $request, FunnelTemplate $template, bool $isFirstStep): ?array
    {
        $selection = $this->templateTestPricingSelectionFromQuery($request);
        if (is_array($selection)) {
            session()->put($this->templateTestSelectedPricingSessionKey($template->id), $selection);
            return $selection;
        }

        if ($isFirstStep) {
            session()->forget($this->templateTestSelectedPricingSessionKey($template->id));
            return null;
        }

        return $this->currentTemplateTestSelectedPricing($template->id);
    }

    private function redirectAfterTemplateTestStep(FunnelTemplate $template, $steps, FunnelTemplateStep $step)
    {
        $next = $this->nextStep($steps, $step->id);
        if (! $next) {
            return redirect()->route('admin.funnel-templates.test', ['funnel_template' => $template, 'step' => $step]);
        }

        return redirect()->route('admin.funnel-templates.test', ['funnel_template' => $template, 'step' => $next]);
    }

    private function redirectAfterTemplateTestOfferDecision(
        FunnelTemplate $template,
        $steps,
        FunnelTemplateStep $step,
        bool $accept
    ) {
        $ordered = collect($steps)->values();
        $currentIndex = $ordered->search(fn ($item) => (int) $item->id === (int) $step->id);
        $target = null;

        if ($currentIndex !== false) {
            $immediateNext = $ordered->get($currentIndex + 1);
            if ($step->type === 'upsell' && ! $accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $immediateNext;
            } elseif ($step->type === 'upsell' && $accept && $immediateNext && $immediateNext->type === 'downsell') {
                $target = $ordered->get($currentIndex + 2);
            } else {
                $target = $immediateNext;
            }
        }

        if (! $target) {
            $target = $ordered->last();
        }

        return redirect()->route('admin.funnel-templates.test', ['funnel_template' => $template, 'step' => $target]);
    }

    private function builderAssetKindFromPath(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            return 'image';
        }
        if (in_array($ext, ['mp4', 'mov', 'avi', 'wmv', 'mkv', 'webm', 'm4v', '3gp', 'ogv'], true)) {
            return 'video';
        }

        return null;
    }

    private function builderAssetPayload(FunnelTemplateAsset $asset): ?array
    {
        $disk = Storage::disk((string) ($asset->disk ?: 'public'));
        if (! $disk->exists($asset->path)) {
            return null;
        }

        $modifiedAt = $asset->created_at?->getTimestamp() ?: $asset->updated_at?->getTimestamp() ?: now()->getTimestamp();

        return [
            'id' => $asset->id,
            'name' => trim((string) ($asset->original_name ?? '')) !== '' ? trim((string) $asset->original_name) : basename($asset->path),
            'path' => $asset->path,
            'url' => $this->builderPublicAssetUrl($asset->path, (string) ($asset->disk ?: 'public')),
            'kind' => (string) ($asset->kind ?: $this->builderAssetKindFromPath($asset->path) ?: 'image'),
            'size' => (int) ($asset->size ?? 0),
            'modified_at' => $asset->created_at?->toIso8601String() ?: date(DATE_ATOM, $modifiedAt),
            'modified_at_ts' => $modifiedAt,
        ];
    }

    private function builderPublicAssetUrl(string $path, string $disk = 'public'): string
    {
        $rawUrl = Storage::disk($disk)->url($path);
        if (! is_string($rawUrl) || trim($rawUrl) === '') {
            return '/storage/' . ltrim($path, '/');
        }
        if (str_starts_with($rawUrl, '/')) {
            return $rawUrl;
        }

        $parts = parse_url($rawUrl);
        $pathPart = is_array($parts) ? (string) ($parts['path'] ?? '') : '';
        $queryPart = is_array($parts) && isset($parts['query']) ? ('?' . $parts['query']) : '';
        $fragmentPart = is_array($parts) && isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';

        return $pathPart !== '' ? $pathPart . $queryPart . $fragmentPart : '/storage/' . ltrim($path, '/');
    }

    private function builderStepPayload(FunnelTemplateStep $step): array
    {
        return [
            'id' => $step->id,
            'title' => $step->title,
            'slug' => $step->slug,
            'type' => $step->type,
            'layout_json' => $step->layout_json,
            'background_color' => $step->background_color,
            'position' => (int) $step->position,
            'is_active' => (bool) $step->is_active,
            'layout_style' => $step->layout_style,
            'template' => $step->template,
            'subtitle' => $step->subtitle,
            'content' => $step->content,
            'cta_label' => $step->cta_label,
            'price' => $step->price,
            'button_color' => $step->button_color,
            'step_tags' => $step->step_tags ?? [],
            'revision_history' => $this->revisionHistoryPayload($step),
            'manual_versions' => $this->manualVersionPayload($step),
        ];
    }

    private function ensureStepHasInitialRevision(FunnelTemplateStep $step): bool
    {
        if ($step->relationLoaded('revisions')) {
            if ($step->revisions->isNotEmpty()) {
                return false;
            }
        } elseif ($step->revisions()->exists()) {
            return false;
        }

        $this->rememberStepRevision($step, $this->normalizeRevisionLayout($step->layout_json), $this->normalizeRevisionBackground($step->background_color));
        return true;
    }

    private function revisionHistoryPayload(FunnelTemplateStep $step): array
    {
        $revisions = $step->relationLoaded('revisions') ? $step->revisions : $step->revisions()->get();

        return $revisions->sortBy(fn (FunnelTemplateStepRevision $revision) => [
            $revision->created_at?->getTimestamp() ?? 0,
            $revision->id,
        ])->map(function (FunnelTemplateStepRevision $revision) {
            return [
                'id' => $revision->id,
                'label' => trim((string) ($revision->label ?? '')) !== '' ? trim((string) $revision->label) : null,
                'version_type' => (string) ($revision->version_type ?? 'autosave'),
                'layout_json' => $this->normalizeRevisionLayout($revision->layout_json),
                'background_color' => $this->normalizeRevisionBackground($revision->background_color),
                'created_at' => $revision->created_at?->toIso8601String(),
            ];
        })->values()->all();
    }

    private function manualVersionPayload(FunnelTemplateStep $step): array
    {
        $revisions = $step->relationLoaded('revisions') ? $step->revisions : $step->revisions()->get();

        return $revisions
            ->filter(fn (FunnelTemplateStepRevision $revision) => (string) ($revision->version_type ?? 'autosave') === 'manual')
            ->sortBy(fn (FunnelTemplateStepRevision $revision) => [$revision->created_at?->getTimestamp() ?? 0, $revision->id])
            ->map(function (FunnelTemplateStepRevision $revision) {
                return [
                    'id' => $revision->id,
                    'label' => trim((string) ($revision->label ?? '')) !== '' ? trim((string) $revision->label) : 'Saved version',
                    'layout_json' => $this->normalizeRevisionLayout($revision->layout_json),
                    'background_color' => $this->normalizeRevisionBackground($revision->background_color),
                    'created_at' => $revision->created_at?->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function rememberStepRevision(FunnelTemplateStep $step, mixed $layout, ?string $backgroundColor): void
    {
        $normalizedLayout = $this->normalizeRevisionLayout($layout);
        $normalizedBackground = $this->normalizeRevisionBackground($backgroundColor);
        $latest = $step->revisions()->latest('id')->first();

        if ($latest) {
            $latestLayout = $this->normalizeRevisionLayout($latest->layout_json);
            $latestBackground = $this->normalizeRevisionBackground($latest->background_color);
            if ($latestLayout === $normalizedLayout && $latestBackground === $normalizedBackground) {
                return;
            }
        }

        $step->revisions()->create([
            'user_id' => auth()->id(),
            'layout_json' => $normalizedLayout,
            'background_color' => $normalizedBackground,
        ]);

        $keepIds = $step->revisions()->reorder()->orderByDesc('id')->limit(self::MAX_STEP_REVISIONS)->pluck('id');
        if ($keepIds->isNotEmpty()) {
            $step->revisions()->reorder()->whereNotIn('id', $keepIds)->delete();
        }
    }

    private function normalizeRevisionLayout(mixed $layout): array
    {
        if (! is_array($layout) || (! isset($layout['root']) && ! isset($layout['sections']))) {
            return ['root' => [], 'sections' => []];
        }

        return $layout;
    }

    private function normalizeRevisionBackground(?string $backgroundColor): ?string
    {
        $bg = trim((string) $backgroundColor);
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $bg) ? $bg : null;
    }

    private function normalizeManualVersionLabel(?string $label): string
    {
        $value = trim((string) $label);
        return $value !== '' ? mb_substr($value, 0, 120) : 'Saved version';
    }

    private function mergeElementSizeFromRaw(array &$layout, array $rawLayout): void
    {
        $mergeElementSize = function (array &$sanitizedElement, array $rawElement): void {
            $rawStyle = is_array($rawElement['style'] ?? null) ? $rawElement['style'] : [];
            $rawSettings = is_array($rawElement['settings'] ?? null) ? $rawElement['settings'] : [];
            $type = (string) ($rawElement['type'] ?? $sanitizedElement['type'] ?? '');
            if ($type !== 'video' && $type !== 'image') {
                return;
            }
            foreach (['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight'] as $key) {
                $value = '';
                if (isset($rawStyle[$key]) && trim((string) $rawStyle[$key]) !== '') {
                    $value = mb_substr(trim((string) $rawStyle[$key]), 0, 60);
                } elseif ($key === 'width' && isset($rawSettings[$key]) && trim((string) $rawSettings[$key]) !== '') {
                    $value = mb_substr(trim((string) $rawSettings[$key]), 0, 60);
                }
                if ($value !== '') {
                    $sanitizedElement['style'] = $sanitizedElement['style'] ?? [];
                    $sanitizedElement['style'][$key] = $value;
                }
            }
        };

        $mergeColumn = function (array &$sanitizedColumn, array $rawColumn) use ($mergeElementSize): void {
            foreach (($sanitizedColumn['elements'] ?? []) as $ei => $_element) {
                $mergeElementSize($sanitizedColumn['elements'][$ei], (array) ($rawColumn['elements'][$ei] ?? []));
            }
        };

        $mergeRow = function (array &$sanitizedRow, array $rawRow) use ($mergeColumn): void {
            foreach (($sanitizedRow['columns'] ?? []) as $ci => $_column) {
                $mergeColumn($sanitizedRow['columns'][$ci], (array) ($rawRow['columns'][$ci] ?? []));
            }
        };

        $mergeSection = function (array &$sanitizedSection, array $rawSection) use ($mergeElementSize, $mergeRow): void {
            foreach (($sanitizedSection['elements'] ?? []) as $ei => $_element) {
                $mergeElementSize($sanitizedSection['elements'][$ei], (array) ($rawSection['elements'][$ei] ?? []));
            }
            foreach (($sanitizedSection['rows'] ?? []) as $ri => $_row) {
                $mergeRow($sanitizedSection['rows'][$ri], (array) ($rawSection['rows'][$ri] ?? []));
            }
        };

        $layout['root'] = is_array($layout['root'] ?? null) ? $layout['root'] : [];
        $rawRoot = is_array($rawLayout['root'] ?? null) ? $rawLayout['root'] : [];
        foreach ($layout['root'] as $ri => $rootItem) {
            $rawItem = (array) ($rawRoot[$ri] ?? []);
            $kind = strtolower((string) ($rootItem['kind'] ?? 'section'));
            if ($kind === 'section') {
                $mergeSection($layout['root'][$ri], $rawItem);
            } elseif ($kind === 'row') {
                $mergeRow($layout['root'][$ri], $rawItem);
            } elseif ($kind === 'column') {
                $mergeColumn($layout['root'][$ri], $rawItem);
            } elseif ($kind === 'el') {
                $mergeElementSize($layout['root'][$ri], $rawItem);
            }
        }
        $layout['sections'] = collect($layout['root'])
            ->filter(fn ($item) => is_array($item) && strtolower((string) ($item['kind'] ?? '')) === 'section')
            ->map(function (array $item) {
                unset($item['kind']);
                return $item;
            })
            ->values()
            ->all();
    }

    private function enforceBuilderStructureRules(array $layout): array
    {
        $root = is_array($layout['root'] ?? null) ? $layout['root'] : [];
        $editor = is_array($layout['__editor'] ?? null) ? $layout['__editor'] : null;
        $resultRoot = [];
        $menuItems = [];
        $pendingElements = [];
        $firstSectionIndex = null;

        $sanitizeColumn = function (array $column): array {
            $elements = collect($column['elements'] ?? [])
                ->filter(fn ($element) => is_array($element))
                ->take(60)
                ->values()
                ->all();

            return [
                'id' => $column['id'] ?? ('col_' . Str::lower(Str::random(8))),
                'style' => is_array($column['style'] ?? null) ? $column['style'] : [],
                'settings' => is_array($column['settings'] ?? null) ? $column['settings'] : [],
                'elements' => $elements,
            ];
        };

        $sanitizeRow = function (array $row) use ($sanitizeColumn): array {
            $columns = collect($row['columns'] ?? [])
                ->filter(fn ($column) => is_array($column))
                ->take(4)
                ->map(fn (array $column) => $sanitizeColumn($column))
                ->values()
                ->all();

            return [
                'id' => $row['id'] ?? ('row_' . Str::lower(Str::random(8))),
                'style' => is_array($row['style'] ?? null) ? $row['style'] : [],
                'settings' => is_array($row['settings'] ?? null) ? $row['settings'] : [],
                'columns' => $columns,
            ];
        };

        $normalizeMenuElement = function (array $item): array {
            $normalized = array_merge(['kind' => 'el'], $item);
            $normalized['settings'] = is_array($normalized['settings'] ?? null) ? $normalized['settings'] : [];
            $style = is_array($normalized['style'] ?? null) ? $normalized['style'] : [];
            unset($style['position'], $style['top'], $style['left'], $style['right'], $style['bottom'], $style['transform']);
            $style['width'] = '100%';
            $normalized['style'] = $style;

            return $normalized;
        };

        $splitMenuFromElements = function (array $elements) use (&$menuItems, $normalizeMenuElement): array {
            $content = [];
            foreach ($elements as $element) {
                if (! is_array($element)) {
                    continue;
                }
                $elementType = strtolower((string) ($element['type'] ?? ''));
                if ($elementType === 'menu') {
                    $menuItems[] = $normalizeMenuElement($element);
                    continue;
                }
                $content[] = $element;
            }
            return $content;
        };

        foreach ($root as $item) {
            if (! is_array($item)) {
                continue;
            }

            $kind = strtolower((string) ($item['kind'] ?? 'section'));

            if ($kind === 'section') {
                $sectionElements = collect($item['elements'] ?? [])
                    ->filter(fn ($element) => is_array($element))
                    ->take(60)
                    ->values()
                    ->all();
                $sectionElements = $splitMenuFromElements($sectionElements);

                $rows = collect($item['rows'] ?? [])
                    ->filter(fn ($row) => is_array($row))
                    ->take(12)
                    ->map(function (array $row) use ($sanitizeRow, &$menuItems, $normalizeMenuElement) {
                        $sanitized = $sanitizeRow($row);
                        foreach ($sanitized['columns'] as $ci => $col) {
                            $filtered = [];
                            foreach ($col['elements'] as $el) {
                                if (is_array($el) && strtolower((string) ($el['type'] ?? '')) === 'menu') {
                                    $menuItems[] = $normalizeMenuElement($el);
                                } else {
                                    $filtered[] = $el;
                                }
                            }
                            $sanitized['columns'][$ci]['elements'] = $filtered;
                        }
                        return $sanitized;
                    })
                    ->values()
                    ->all();

                $normalizedSection = [
                    'kind' => 'section',
                    'id' => $item['id'] ?? ('sec_' . Str::lower(Str::random(10))),
                    'style' => is_array($item['style'] ?? null) ? $item['style'] : [],
                    'settings' => is_array($item['settings'] ?? null) ? $item['settings'] : [],
                    'elements' => $sectionElements,
                    'rows' => $rows,
                ];
                if ($firstSectionIndex === null) {
                    $firstSectionIndex = count($resultRoot);
                }
                $resultRoot[] = $normalizedSection;
                continue;
            }

            if ($kind === 'row') {
                $sanitized = $sanitizeRow($item);
                $resultRoot[] = array_merge(['kind' => 'row'], $sanitized);
                continue;
            }

            if ($kind === 'column' || $kind === 'col') {
                $sanitized = $sanitizeColumn($item);
                $resultRoot[] = array_merge(['kind' => 'column'], $sanitized);
                continue;
            }

            $elementType = strtolower((string) ($item['type'] ?? ''));
            if ($elementType === 'menu') {
                $menuItems[] = $normalizeMenuElement($item);
                continue;
            }

            $pendingElements[] = array_merge(['kind' => 'el'], $item);
        }

        if (! empty($pendingElements)) {
            if ($firstSectionIndex === null) {
                $section = [
                    'kind' => 'section',
                    'id' => 'sec_' . Str::lower(Str::random(10)),
                    'style' => ['padding' => '20px', 'backgroundColor' => '#ffffff', 'minHeight' => '30vh'],
                    'settings' => ['contentWidth' => 'full'],
                    'elements' => [],
                    'rows' => [],
                ];
                $resultRoot[] = $section;
                $firstSectionIndex = count($resultRoot) - 1;
            }
            $existing = is_array($resultRoot[$firstSectionIndex]['elements'] ?? null) ? $resultRoot[$firstSectionIndex]['elements'] : [];
            $resultRoot[$firstSectionIndex]['elements'] = array_values(array_merge($existing, $pendingElements));
        }

        $resultRoot = array_values(array_merge($menuItems, $resultRoot));

        $sections = collect($resultRoot)
            ->filter(fn ($item) => is_array($item) && strtolower((string) ($item['kind'] ?? '')) === 'section')
            ->map(function (array $item) {
                unset($item['kind']);
                return $item;
            })
            ->values()
            ->all();

        $out = [
            'root' => array_values($resultRoot),
            'sections' => $sections,
        ];

        if ($editor !== null) {
            $out['__editor'] = $editor;
        }

        $out['__editor'] = is_array($out['__editor'] ?? null) ? $out['__editor'] : [];
        if (! isset($out['__editor']['canvasWidth'])) {
            $out['__editor']['canvasWidth'] = 1366;
        }
        if (! isset($out['__editor']['canvasContentWidth'])) {
            $out['__editor']['canvasContentWidth'] = 1366;
        }

        return $out;
    }

    private function normalizeTagsString(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter()
            ->map(function ($tag) {
                $clean = preg_replace('/[^a-z0-9\-_ ]/i', '', $tag) ?? '';
                return mb_substr(trim($clean), 0, 40);
            })
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    private function requiredStepTypesForTemplateType(string $templateType): array
    {
        return match (FunnelTemplate::normalizeTemplateType($templateType)) {
            'single_page' => [],
            'digital_product', 'physical_product' => ['sales', 'checkout', 'thank_you'],
            'hybrid' => ['landing', 'sales', 'checkout', 'thank_you'],
            default => ['landing', 'opt_in', 'sales', 'checkout', 'thank_you'],
        };
    }

    private function validatePublishReadiness($steps, string $templateType = 'service'): array
    {
        $ordered = collect($steps)->values();
        $issues = [];
        $normalizedTemplateType = FunnelTemplate::normalizeTemplateType($templateType);
        $requiredTypes = $this->requiredStepTypesForTemplateType($templateType);

        foreach ($requiredTypes as $requiredType) {
            if (! $ordered->contains(fn ($step) => strtolower(trim((string) ($step->type ?? ''))) === $requiredType)) {
                $issues[] = 'Add an active ' . str_replace('_', '-', $requiredType) . ' step.';
            }
        }

        $firstStep = $ordered->first();
        $lastStep = $ordered->last();
        if ($firstStep && strtolower(trim((string) ($firstStep->type ?? ''))) === 'thank_you') {
            $issues[] = 'The first active step cannot be a Thank You step.';
        }
        if (
            $normalizedTemplateType !== 'single_page'
            && $lastStep
            && strtolower(trim((string) ($lastStep->type ?? ''))) !== 'thank_you'
        ) {
            $issues[] = 'The last active step must be a Thank You step so the flow resolves safely.';
        }

        return array_values(array_unique($issues));
    }
}
