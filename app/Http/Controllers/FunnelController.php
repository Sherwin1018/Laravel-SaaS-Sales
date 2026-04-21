<?php

namespace App\Http\Controllers;

use App\Mail\FunnelOrderDeliveryUpdateMail;
use App\Models\Funnel;
use App\Models\FunnelBuilderAsset;
use App\Models\FunnelEvent;
use App\Models\Lead;
use App\Models\FunnelStep;
use App\Models\FunnelStepRevision;
use App\Models\FunnelTemplate;
use Illuminate\Support\Facades\DB;
use App\Services\FunnelTrackingService;
use App\Support\TenantPlanEnforcer;
use App\Support\XlsxWorkbookBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FunnelController extends Controller
{
    private const MAX_STEP_REVISIONS = 40;
    private const MAX_MANUAL_VERSIONS = 25;
    private const CREATE_PURPOSE_KEYS = ['service', 'single_page', 'digital_product', 'physical_product', 'hybrid'];

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $search = trim((string) $request->query('search', ''));
        $normalizedSearch = mb_strtolower($search);
        $purposeMatches = collect(Funnel::PURPOSES)
            ->filter(function (string $label, string $key) use ($normalizedSearch) {
                if ($normalizedSearch === '') {
                    return false;
                }

                $normalizedLabel = mb_strtolower($label);
                $normalizedKey = mb_strtolower(str_replace('_', ' ', $key));

                return str_contains($normalizedLabel, $normalizedSearch)
                    || str_contains($normalizedKey, $normalizedSearch);
            })
            ->keys()
            ->values()
            ->all();

        $funnels = Funnel::where('tenant_id', $tenantId)
            ->when($search !== '', function ($query) use ($search, $purposeMatches) {
                $query->where(function ($innerQuery) use ($search, $purposeMatches) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('purpose', 'like', "%{$search}%");

                    if ($purposeMatches !== []) {
                        $innerQuery->orWhereIn('purpose', $purposeMatches);
                    }
                });
            })
            ->withCount('steps')
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $planUsage = app(TenantPlanEnforcer::class)->usageSummary(auth()->user()->tenant);

        if ($request->ajax()) {
            return view('funnels._rows', compact('funnels'))->render();
        }

        return view('funnels.index', compact('funnels', 'search', 'planUsage'));
    }

    public function create()
    {
        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateFunnel(auth()->user()->tenant);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->route('funnels.index')->with('error', $e->getMessage());
        }

        return view('funnels.create', [
            'templateTypeOptions' => FunnelTemplate::selectableTemplateTypes(),
            'funnelPurposeOptions' => FunnelTemplate::FUNNEL_PURPOSE_OPTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'template_type' => ['required', Rule::in(array_keys(FunnelTemplate::selectableTemplateTypes()))],
            'funnel_purpose' => ['required', Rule::in(array_keys(FunnelTemplate::FUNNEL_PURPOSE_OPTIONS))],
        ]);

        $purpose = $validated['template_type'] === 'single_page'
            ? 'single_page'
            : FunnelTemplate::normalizeFunnelPurpose($validated['funnel_purpose']);

        $user = auth()->user();

        try {
            app(TenantPlanEnforcer::class)->ensureCanCreateFunnel($user->tenant);

            $funnel = Funnel::create([
                'tenant_id' => $user->tenant_id,
                'created_by' => $user->id,
                'name' => $validated['name'],
                'slug' => $this->generateUniqueFunnelSlug($validated['name'], $user->tenant_id),
                'description' => $validated['description'] ?? null,
                'purpose' => $purpose,
                'status' => 'draft',
            ]);

            $starterSteps = $this->starterStepsForPurpose($purpose);

            foreach ($starterSteps as $index => $step) {
                $createdStep = FunnelStep::create([
                    'funnel_id' => $funnel->id,
                    'title' => $step['title'],
                    'slug' => $step['slug'],
                    'type' => $step['type'],
                    'content' => $step['content'],
                    'step_tags' => [],
                    'cta_label' => $step['cta_label'] ?? null,
                    'price' => $step['price'] ?? null,
                    'position' => $index + 1,
                    'is_active' => true,
                ]);
                $this->ensureStepHasInitialRevision($createdStep);
            }

            $funnel->load('steps.revisions');
            foreach ($funnel->steps as $step) {
                $this->ensureStepHasInitialRevision($step);
            }

            return redirect()->route('funnels.edit', $funnel)->with('success', 'Added Successfully');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    private function starterStepsForPurpose(string $purpose): array
    {
        return match (Funnel::normalizePurpose($purpose)) {
            'single_page' => [
                ['title' => 'Single Page Funnel', 'slug' => 'single-page', 'type' => 'landing', 'content' => 'Build the full one-page journey here: hero, offer, proof, and checkout sections.', 'cta_label' => 'Get Started'],
            ],
            'digital_product' => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Introduce the digital product and build interest before the sales page.', 'cta_label' => 'Learn More'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present your digital product offer here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete your digital order.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank you for your purchase. Deliver access details here.', 'cta_label' => null],
            ],
            'physical_product' => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Introduce the product and capture interest before the full sales page.', 'cta_label' => 'Shop Now'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Show the product, price, benefits, and buying reason here.', 'cta_label' => 'Buy Now'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Collect customer, shipping, and payment details here.', 'cta_label' => 'Place Order', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Confirm the order and tell the buyer what happens next.', 'cta_label' => null],
            ],
            'hybrid' => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Introduce the offer and guide buyers into the right next step.', 'cta_label' => 'Continue'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present the main offer details here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete your order here.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank you for your purchase.', 'cta_label' => null],
            ],
            default => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Welcome to our funnel.', 'cta_label' => 'Continue'],
                ['title' => 'Opt-in', 'slug' => 'opt-in', 'type' => 'opt_in', 'content' => 'Fill out the form to continue.', 'cta_label' => 'Submit'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present your offer details here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete your order.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank you for your purchase.', 'cta_label' => null],
            ],
        };
    }

    public function edit(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $funnel->load(['steps.revisions']);
        $seededMissingRevisions = false;
        foreach ($funnel->steps as $step) {
            if ($this->ensureStepHasInitialRevision($step)) {
                $seededMissingRevisions = true;
            }
        }
        if ($seededMissingRevisions) {
            $funnel->load(['steps.revisions']);
        }
        $defaultStep = $funnel->steps->sortBy('position')->first();

        // Best-effort inventory snapshot for the builder sidebar (used for restocking UX).
        $builderProductInventory = [];
        try {
            $products = [];

            foreach ($funnel->steps()->where('is_active', true)->get(['layout_json']) as $step) {
                $layout = is_array($step->layout_json ?? null) ? $step->layout_json : [];
                $roots = is_array($layout['root'] ?? null)
                    ? $layout['root']
                    : (is_array($layout['sections'] ?? null) ? $layout['sections'] : []);

                $walk = function ($node) use (&$walk, &$products) {
                    if (! is_array($node)) {
                        return;
                    }

                    if (strtolower(trim((string) ($node['type'] ?? ''))) === 'product_offer') {
                        $id = trim((string) ($node['id'] ?? ''));
                        $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
                        $qtyRaw = $settings['stockQuantity'] ?? null;
                        $qty = is_numeric($qtyRaw) ? max(0, (int) $qtyRaw) : 0;

                        if ($id !== '' && $qty > 0) {
                            $products[$id] = [
                                'stock_quantity' => $qty,
                                'sold_units' => (int) ($products[$id]['sold_units'] ?? 0),
                                'sold_offset' => max(0, (int) ($settings['stockSoldOffset'] ?? 0)),
                                'remaining_stock' => $qty,
                                'is_out_of_stock' => false,
                            ];
                        }
                    }

                    foreach (['root', 'sections', 'rows', 'columns', 'elements'] as $k) {
                        $children = $node[$k] ?? null;
                        if (! is_array($children)) {
                            continue;
                        }
                        foreach ($children as $child) {
                            $walk($child);
                        }
                    }
                };

                foreach ($roots as $node) {
                    $walk($node);
                }
            }

            if ($products !== []) {
                $paidEvents = FunnelEvent::query()
                    ->where('tenant_id', $funnel->tenant_id)
                    ->where('funnel_id', $funnel->id)
                    ->where('event_name', FunnelTrackingService::EVENT_PAYMENT_PAID)
                    ->get(['meta']);

                foreach ($paidEvents as $event) {
                    $items = data_get($event->meta, 'order_items');
                    if (! is_array($items)) {
                        continue;
                    }
                    foreach ($items as $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $productId = trim((string) ($item['id'] ?? ''));
                        if ($productId === '' || ! isset($products[$productId])) {
                            continue;
                        }
                        $products[$productId]['sold_units'] += max(1, (int) ($item['quantity'] ?? 1));
                    }
                }

                foreach ($products as $id => $product) {
                    $stockQuantity = (int) ($product['stock_quantity'] ?? 0);
                    $soldUnits = (int) ($product['sold_units'] ?? 0);
                    $soldOffset = (int) ($product['sold_offset'] ?? 0);

                    if ($soldOffset <= 0 && $soldUnits > $stockQuantity) {
                        $soldOffset = $soldUnits;
                    }

                    $effectiveSold = max(0, $soldUnits - $soldOffset);
                    $products[$id]['sold_offset'] = $soldOffset;
                    $products[$id]['remaining_stock'] = max(0, $stockQuantity - $effectiveSold);
                    $products[$id]['is_out_of_stock'] = $products[$id]['remaining_stock'] <= 0;
                }

                $builderProductInventory = $products;
            }
        } catch (\Throwable $e) {
            report($e);
            $builderProductInventory = [];
        }

        return view('funnels.edit', [
            'funnel' => $funnel,
            'stepTypes' => FunnelStep::TYPES,
            'stepLayouts' => FunnelStep::LAYOUTS,
            'stepTemplates' => FunnelStep::TEMPLATES,
            'defaultStepId' => $defaultStep?->id,
            'builderSharedTemplates' => $this->builderSharedTemplatesPayload(),
            'builderSharedTemplatesUrl' => route('funnels.shared-templates'),
            'builderSingleScrollMode' => $this->singleScrollModeEnabledForFunnel(auth()->user(), $funnel),
            'builderPurpose' => $funnel->purpose ?? 'service',
            'builderProductInventory' => $builderProductInventory,
        ]);
    }

    public function sharedTemplates()
    {
        return response()->json([
            'templates' => $this->builderSharedTemplatesPayload(auth()->user()),
        ]);
    }

    public function updateSharedTemplate(Request $request, FunnelTemplate $funnelTemplate)
    {
        $user = auth()->user();
        if (! $this->canEditSharedTemplates($user)) {
            abort(403, 'You are not allowed to edit built-in template cards.');
        }

        $accessible = $this->findAccessiblePublishedTemplateForUser($user, (int) $funnelTemplate->id);
        if (! $accessible) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in(array_keys(FunnelTemplate::STATUSES))],
            'template_tags' => ['nullable', 'array'],
            'template_tags.*' => ['nullable', 'string', 'max:40'],
        ]);

        $funnelTemplate->update([
            'name' => trim((string) $validated['name']) !== '' ? trim((string) $validated['name']) : $funnelTemplate->name,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'template_tags' => collect($validated['template_tags'] ?? [])
                ->map(fn ($tag) => trim((string) $tag))
                ->filter()
                ->unique()
                ->take(6)
                ->values()
                ->all(),
        ]);

        return response()->json([
            'message' => 'Template card updated successfully.',
        ]);
    }

    public function applySharedTemplateAllPages(Request $request, Funnel $funnel, FunnelTemplate $funnelTemplate)
    {
        $this->ensureTenantFunnelAccess($funnel);
        $user = auth()->user();

        $accessible = $this->findAccessiblePublishedTemplateForUser($user, (int) $funnelTemplate->id);
        if (! $accessible) {
            abort(404);
        }

        $templateSteps = $accessible->steps()->orderBy('position')->get();
        if ($templateSteps->isEmpty()) {
            return response()->json(['message' => 'Template has no steps.'], 422);
        }

        DB::transaction(function () use ($funnel, $templateSteps) {
            $existingSteps = $funnel->steps()->orderBy('position')->get()->values();
            $keepIds = [];
            $usedSlugs = [];

            $slugify = function (string $raw): string {
                $raw = mb_strtolower(trim($raw));
                $raw = str_replace(['_', ' '], '-', $raw);
                $raw = preg_replace('/[^a-z0-9\-]/', '', $raw) ?? '';
                $raw = preg_replace('/-+/', '-', $raw) ?? '';
                $raw = trim($raw, '-');
                return mb_substr($raw, 0, 120);
            };

            $uniqueSlug = function (string $base) use (&$usedSlugs): string {
                $base = mb_substr($base, 0, 120);
                if ($base === '') {
                    $base = 'page';
                }
                $slug = $base;
                $i = 2;
                while (isset($usedSlugs[$slug])) {
                    $suffix = '-' . $i;
                    $maxBaseLen = max(1, 120 - mb_strlen($suffix));
                    $slug = mb_substr($base, 0, $maxBaseLen) . $suffix;
                    $i++;
                }
                $usedSlugs[$slug] = true;
                return $slug;
            };

            foreach ($templateSteps as $index => $tplStep) {
                $position = $index + 1;
                $target = $existingSteps->get($index);

                $title = trim((string) ($tplStep->title ?? ''));
                if ($title === '') {
                    $title = ucfirst(str_replace('_', ' ', (string) ($tplStep->type ?? 'custom')));
                }
                $title = mb_substr($title, 0, 120);
                $subtitle = trim((string) ($tplStep->subtitle ?? ''));
                $subtitle = $subtitle !== '' ? mb_substr($subtitle, 0, 160) : null;

                $baseSlug = trim((string) ($tplStep->slug ?? ''));
                if ($baseSlug === '') {
                    $baseSlug = $title;
                }
                $baseSlug = $slugify($baseSlug);
                if ($baseSlug === '') {
                    $baseSlug = 'page-' . $position;
                }
                $slug = $uniqueSlug($baseSlug);

                $payload = [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'slug' => $slug,
                    'type' => $tplStep->type,
                    'template' => $tplStep->template ?? 'simple',
                    'template_data' => $tplStep->template_data,
                    'step_tags' => $tplStep->step_tags,
                    'content' => $tplStep->content,
                    'hero_image_url' => $tplStep->hero_image_url,
                    'layout_style' => $tplStep->layout_style ?? 'centered',
                    'background_color' => $tplStep->background_color,
                    'button_color' => $tplStep->button_color,
                    'cta_label' => $tplStep->cta_label,
                    'price' => $tplStep->price,
                    'layout_json' => $tplStep->layout_json ?? ['root' => [], 'sections' => []],
                    'layout_json_tablet' => $tplStep->layout_json_tablet,
                    'layout_json_mobile' => $tplStep->layout_json_mobile,
                    'position' => $position,
                    'is_active' => (bool) $tplStep->is_active,
                ];

                if ($target) {
                    $target->update($payload);
                    $this->ensureStepHasInitialRevision($target);
                    $keepIds[] = (int) $target->id;
                } else {
                    $created = $funnel->steps()->create(array_merge($payload, [
                        'position' => $position,
                    ]));
                    $this->ensureStepHasInitialRevision($created);
                    $keepIds[] = (int) $created->id;
                }
            }

            // Remove any extra steps from the funnel so it exactly matches the template.
            $funnel->steps()
                ->whereNotIn('id', $keepIds)
                ->delete();
        });

        $freshSteps = $funnel->steps()->orderBy('position')->get();

        return response()->json([
            'message' => 'Template applied to all pages.',
            'steps' => $freshSteps->map(fn ($s) => $this->builderStepPayload($s))->values()->all(),
        ]);
    }

    private function builderSharedTemplatesPayload($user = null): array
    {
        $resolvedUser = $user ?: auth()->user();

        return $this->publishedTemplatesQueryForUser($resolvedUser)
            ->with(['steps' => fn ($query) => $query->orderBy('position')])
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
                    'template_type' => (string) $template->template_type,
                    'funnel_purpose' => $template->resolvedFunnelPurpose(),
                    'status' => (string) $template->status,
                    'update_url' => $this->canEditSharedTemplates(auth()->user())
                        ? route('funnels.shared-templates.update', $template)
                        : null,
                    'preview' => $preview,
                    'preview_image' => $template->preview_image,
                    'tags' => $this->templateCardTags($template, count($steps), $stepTypeTags),
                    'steps' => $steps,
                ];
            })
            ->all();
    }

    private function publishedTemplatesQueryForUser($user)
    {
        $query = FunnelTemplate::query()
            ->where('status', 'published')
            ->where('template_type', '!=', FunnelTemplate::TEMPLATE_TYPE_UNCATEGORIZED)
            ->latest('published_at')
            ->latest('id');

        if ($this->restrictToSinglePageTemplates($user)) {
            $query->where('template_type', 'single_page');
        }

        $limit = $this->templateLibraryLimitForUser($user);
        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query;
    }

    private function restrictToSinglePageTemplates($user): bool
    {
        if (! $user) {
            return true;
        }

        // Allow funnel builders (AO/marketing) to access step-by-step templates too.
        // Only restrict viewers without builder roles to single-page templates.
        return ! (bool) $user->hasAnyRole(['super-admin', 'account-owner', 'marketing-manager']);
    }

    private function templateLibraryLimitForUser($user): ?int
    {
        if (! $user || ! $user->tenant) {
            return null;
        }

        $plan = app(TenantPlanEnforcer::class)->resolvePlan($user->tenant);
        if (! $plan) {
            return null;
        }

        $limit = $plan->max_templates;
        if ($limit === null) {
            $limit = $plan->max_funnels;
        }

        return $limit === null ? null : max(0, (int) $limit);
    }

    private function findAccessiblePublishedTemplateForUser($user, int $templateId): ?FunnelTemplate
    {
        if ($templateId <= 0) {
            return null;
        }

        return $this->publishedTemplatesQueryForUser($user)
            ->where('id', $templateId)
            ->first();
    }

    private function singleScrollModeEnabledForFunnel($user, Funnel $funnel): bool
    {
        $activeStepCount = $funnel->relationLoaded('steps')
            ? $funnel->steps->where('is_active', true)->count()
            : $funnel->steps()->where('is_active', true)->count();

        if ($activeStepCount > 1) {
            return false;
        }

        $defaultTags = collect($funnel->default_tags ?? [])
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter();

        if ($defaultTags->contains('__single_scroll') || $defaultTags->contains('single-scroll')) {
            return true;
        }

        return Funnel::normalizePurpose($funnel->purpose ?? $funnel->template_type) === 'single_page';
    }

    private function canEditSharedTemplates($user): bool
    {
        // Shared template cards represent super-admin curated templates.
        // Only super-admin can edit card metadata (name/description/tags/status).
        return (bool) ($user && $user->hasRole('super-admin'));
    }

    private function templateCardTags(FunnelTemplate $template, int $stepCount, array $fallbackStepTypeTags): array
    {
        $custom = collect($template->template_tags ?? [])
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->take(6)
            ->values()
            ->all();

        if (!empty($custom)) {
            return $custom;
        }

        return array_values(array_filter(array_merge(
            [$template->templateTypeLabel()],
            [$stepCount . ' Pages'],
            $fallbackStepTypeTags,
            [strtoupper((string) $template->status)]
        )));
    }

    public function preview(Request $request, Funnel $funnel, ?FunnelStep $step = null)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $steps = $funnel->steps()->where('is_active', true)->orderBy('position')->get()->values();
        abort_if($steps->isEmpty(), 404);

        if ($step && (int) $step->funnel_id !== (int) $funnel->id) {
            abort(404);
        }

        $resolvedStep = $step ?: $steps->first();
        abort_if(!$resolvedStep, 404);
        $selectedPricing = $this->previewSelectedPricingFromRequest($request, $steps);

        return view('funnels.portal.step', [
            'funnel' => $funnel->load('tenant'),
            'step' => $resolvedStep,
            'nextStep' => $this->nextStep($steps, $resolvedStep->id),
            'allSteps' => $steps,
            'isFirstStep' => (int) $steps->first()->id === (int) $resolvedStep->id,
            'isPreview' => true,
            'selectedPricing' => $selectedPricing,
        ]);
    }

    public function analytics(Request $request, Funnel $funnel, FunnelTrackingService $tracking)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $filters = $tracking->normalizeDateFilters($request->only(['from', 'to', 'step_id', 'event_name', 'per_page']));
        $analytics = $tracking->analyticsForFunnel($funnel, $filters);

        if ($request->expectsJson()) {
            return response()->json([
                'analytics' => $analytics,
            ]);
        }

        $events = $tracking->eventsForFunnel($funnel, array_merge($filters, ['per_page' => 15]));

        return view('funnels.analytics', [
            'funnel' => $funnel->load('steps'),
            'analytics' => $analytics,
            'events' => $events,
            'filters' => [
                'from' => $request->query('from', ''),
                'to' => $request->query('to', ''),
                'step_id' => $request->query('step_id', ''),
                'event_name' => $request->query('event_name', ''),
            ],
            'supportedEvents' => $analytics['events_supported'] ?? [],
        ]);
    }

    public function exportAnalytics(Request $request, Funnel $funnel, FunnelTrackingService $tracking)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $filters = $tracking->normalizeDateFilters($request->only(['from', 'to', 'step_id', 'event_name']));
        $events = $tracking->eventsCollectionForExport($funnel, $filters);

        $filename = 'funnel-analytics-' . $funnel->slug . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($events) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Occurred At', 'Event', 'Step', 'Lead', 'Payment Status', 'Amount', 'Session']);

            foreach ($events as $event) {
                fputcsv($handle, [
                    optional($event->occurred_at)->toDateTimeString(),
                    $event->event_name,
                    $event->step->title ?? '',
                    $event->lead->email ?? ($event->lead->name ?? ''),
                    $event->payment->status ?? '',
                    $event->payment ? number_format((float) $event->payment->amount, 2, '.', '') : '',
                    $event->session_identifier ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPhysicalOrdersExcel(Request $request, Funnel $funnel, FunnelTrackingService $tracking)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $filters = $tracking->normalizeDateFilters($request->only(['from', 'to', 'step_id', 'event_name']));
        $analytics = $tracking->analyticsForFunnel($funnel, $filters);
        $purpose = Funnel::normalizePurpose($funnel->purpose ?? ($funnel->template_type ?? 'service'));

        abort_unless(in_array($purpose, ['physical_product', 'hybrid'], true), 404);

        $section = Str::lower(trim((string) $request->query('section', 'directory')));
        $config = $this->physicalOrderExcelExportConfig($section, $analytics);

        abort_if($config === null, 404);

        $filename = Str::slug($config['title'])
            . '-'
            . $funnel->slug
            . '-'
            . now()->format('Ymd_His')
            . '.xlsx';

        return response(
            (new XlsxWorkbookBuilder(
                $config['worksheet'],
                $config['title'],
                $this->physicalOrderExcelSummaryLine($funnel, $config['title'], $request, count($config['rows'])),
                $config['headings'],
                $config['widths'],
                $config['rows']
            ))->build(),
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            ]
        );
    }

    private function physicalOrderExcelExportConfig(string $section, array $analytics): ?array
    {
        $section = Str::lower(trim($section));

        return match ($section) {
            'pending' => [
                'worksheet' => 'Pending Orders',
                'title' => 'Pending Orders',
                'headings' => ['Customer', 'Email', 'Phone', 'Order Items', 'Qty', 'Amount (PHP)', 'Delivery Address', 'Last Activity'],
                'widths' => [160, 220, 105, 250, 55, 95, 240, 145],
                'rows' => collect($analytics['physical_pending_orders'] ?? [])
                    ->map(fn (array $row) => [
                        $this->excelTextCell($row['customer'] ?? 'Anonymous visitor'),
                        $this->excelTextCell($row['email'] ?? 'N/A'),
                        $this->excelTextCell($row['phone'] ?? 'N/A'),
                        $this->excelTextCell($this->physicalOrderItemsForExcel($row), 'wrap'),
                        $this->excelNumberCell((int) ($row['order_quantity'] ?? 0), 'center'),
                        $this->excelNumberCell((float) ($row['checkout_amount'] ?? 0), 'currency'),
                        $this->excelTextCell($row['delivery_address'] ?? 'N/A', 'wrap'),
                        $this->excelTextCell($row['last_activity'] ?? 'N/A'),
                    ])
                    ->all(),
            ],
            'paid' => [
                'worksheet' => 'Paid Orders',
                'title' => 'Paid Orders',
                'headings' => ['Customer', 'Email', 'Phone', 'Paid Order', 'Qty', 'Amount (PHP)', 'Delivery Status', 'Last Delivery Email', 'Tracking URL', 'Delivery Address'],
                'widths' => [160, 220, 105, 250, 55, 95, 125, 155, 220, 240],
                'rows' => collect($analytics['physical_paid_orders'] ?? [])
                    ->map(fn (array $row) => [
                        $this->excelTextCell($row['customer'] ?? 'Anonymous visitor'),
                        $this->excelTextCell($row['email'] ?? 'N/A'),
                        $this->excelTextCell($row['phone'] ?? 'N/A'),
                        $this->excelTextCell($this->physicalOrderItemsForExcel($row), 'wrap'),
                        $this->excelNumberCell((int) ($row['order_quantity'] ?? 0), 'center'),
                        $this->excelNumberCell((float) ($row['checkout_amount'] ?? 0), 'currency'),
                        $this->excelTextCell($this->formatDeliveryStatusForExcel($row['delivery_status'] ?? 'paid'), 'status'),
                        $this->excelTextCell($row['delivery_updated_label'] ?? 'N/A'),
                        $this->excelTextCell($row['tracking_url'] ?? 'N/A', 'wrap'),
                        $this->excelTextCell($row['delivery_address'] ?? 'N/A', 'wrap'),
                    ])
                    ->all(),
            ],
            'directory' => [
                'worksheet' => 'Order Directory',
                'title' => 'Order Directory',
                'headings' => ['Customer', 'Email', 'Phone', 'Order Items', 'Qty', 'Status', 'Checkout Paid (PHP)', 'Delivery Address', 'Order Notes', 'Last Activity'],
                'widths' => [160, 220, 105, 250, 55, 115, 110, 240, 180, 145],
                'rows' => collect($analytics['physical_orders'] ?? [])
                    ->map(fn (array $row) => [
                        $this->excelTextCell($row['customer'] ?? 'Anonymous visitor'),
                        $this->excelTextCell($row['email'] ?? 'N/A'),
                        $this->excelTextCell($row['phone'] ?? 'N/A'),
                        $this->excelTextCell($this->physicalOrderItemsForExcel($row), 'wrap'),
                        $this->excelNumberCell((int) ($row['order_quantity'] ?? 0), 'center'),
                        $this->excelTextCell(Str::upper(str_replace('_', ' ', (string) ($row['order_status'] ?? 'pending'))), 'status'),
                        $this->excelNumberCell((float) ($row['checkout_amount'] ?? 0), 'currency'),
                        $this->excelTextCell($row['delivery_address'] ?? 'N/A', 'wrap'),
                        $this->excelTextCell($row['notes'] ?? 'N/A', 'wrap'),
                        $this->excelTextCell($row['last_activity'] ?? 'N/A'),
                    ])
                    ->all(),
            ],
            default => null,
        };
    }

    private function physicalOrderExcelSummaryLine(Funnel $funnel, string $title, Request $request, int $rowCount): string
    {
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));
        $range = $from !== '' || $to !== ''
            ? trim(($from !== '' ? $from : 'Start') . ' to ' . ($to !== '' ? $to : 'Today'))
            : 'All dates';

        return 'Funnel: '
            . $funnel->name
            . ' | Export: '
            . $title
            . ' | Date Range: '
            . $range
            . ' | Rows: '
            . number_format($rowCount)
            . ' | Generated: '
            . now()->format('Y-m-d h:i A');
    }

    private function physicalOrderItemsForExcel(array $row): string
    {
        $items = $row['order_items'] ?? null;
        if (is_array($items) && $items !== []) {
            $lines = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $name = trim((string) ($item['name'] ?? 'Product'));
                $quantity = max(1, (int) ($item['quantity'] ?? 1));
                $price = trim((string) ($item['price'] ?? ''));
                $badge = trim((string) ($item['badge'] ?? ''));

                $line = $name !== '' ? $name : 'Product';
                $line .= ' x' . $quantity;

                $details = array_values(array_filter([$badge, $price]));
                if ($details !== []) {
                    $line .= ' (' . implode(' | ', $details) . ')';
                }

                $lines[] = $line;
            }

            if ($lines !== []) {
                return implode("\n", $lines);
            }
        }

        return trim((string) ($row['order_items_label'] ?? ($row['selected_offer'] ?? 'N/A'))) ?: 'N/A';
    }

    private function formatDeliveryStatusForExcel(?string $value): string
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return 'N/A';
        }

        return Str::title(str_replace('_', ' ', $normalized));
    }

    private function excelTextCell(mixed $value, string $style = 'text'): array
    {
        return [
            'type' => 'String',
            'style' => $style,
            'value' => trim((string) $value) !== '' ? (string) $value : 'N/A',
        ];
    }

    private function excelNumberCell(int|float $value, string $style = 'number'): array
    {
        return [
            'type' => 'Number',
            'style' => $style,
            'value' => $value,
        ];
    }

    public function sendDeliveryUpdate(Request $request, Funnel $funnel, FunnelTrackingService $tracking)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'order_key' => 'required|string|max:191',
            'recipient_email' => 'nullable|email|max:150',
            'delivery_status' => ['required', Rule::in(['processing', 'shipped', 'out_for_delivery', 'delivered'])],
            // Couriers like LBC often give a tracking NUMBER, not a URL.
            // Accept either a URL or a plain tracking number/reference.
            'tracking_number' => 'nullable|string|max:120',
            'courier_name' => 'nullable|string|max:80',
            'custom_message' => 'nullable|string|max:600',
        ]);

        $order = $tracking->findPhysicalOrderRow($funnel, (string) $validated['order_key']);
        if (! is_array($order)) {
            return redirect()->back()->with('error', 'Order could not be found.');
        }
        if (($order['order_status'] ?? '') !== 'paid') {
            return redirect()->back()->with('error', 'Only paid orders can receive delivery update emails.');
        }

        $recipientEmail = trim((string) ($validated['recipient_email'] ?? ($order['email'] ?? '')));
        if ($recipientEmail === '') {
            return redirect()->back()->with('error', 'This order does not have a customer email address.');
        }

        $courierName = trim((string) ($validated['courier_name'] ?? ''));
        if ($courierName === '') {
            $courierName = 'LBC';
        }

        try {
            $trackingRaw = trim((string) ($validated['tracking_number'] ?? ''));
            // Treat full URLs as links, otherwise store as tracking number.
            $trackingValue = $trackingRaw !== '' ? $trackingRaw : null;
            Mail::to($recipientEmail)->send(new FunnelOrderDeliveryUpdateMail(
                (string) $funnel->name,
                (string) ($order['customer'] ?? 'Customer'),
                (string) $validated['delivery_status'],
                $trackingValue,
                $courierName,
                is_array($order['order_items'] ?? null) ? $order['order_items'] : [],
                (int) ($order['order_quantity'] ?? 0),
                trim((string) ($validated['custom_message'] ?? '')) ?: null
            ));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Delivery update email failed to send.');
        }

        $trackingRaw = trim((string) ($validated['tracking_number'] ?? ''));
        $trackingIsUrl = $trackingRaw !== '' && preg_match('/^https?:\\/\\//i', $trackingRaw) === 1;
        $tracking->trackOrderDeliveryUpdate($funnel, $order, [
            'recipient_email' => $recipientEmail,
            'delivery_status' => (string) $validated['delivery_status'],
            'tracking_url' => $trackingIsUrl ? $trackingRaw : '',
            'tracking_number' => $trackingIsUrl ? '' : $trackingRaw,
            'courier_name' => $courierName,
            'custom_message' => trim((string) ($validated['custom_message'] ?? '')),
        ]);

        $leadId = (int) ($order['lead_id'] ?? 0);
        if ($leadId > 0) {
            $lead = Lead::query()
                ->where('tenant_id', auth()->user()->tenant_id)
                ->find($leadId);
            if ($lead) {
                $lead->activities()->create([
                    'activity_type' => 'Delivery Update Sent',
                    'notes' => 'Sent ' . Str::headline((string) $validated['delivery_status']) . ' update to ' . $recipientEmail,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Delivery update emailed successfully.');
    }

    public function events(Request $request, Funnel $funnel, FunnelTrackingService $tracking)
    {
        $this->ensureTenantFunnelAccess($funnel);

        return response()->json(
            $tracking->eventsForFunnel(
                $funnel,
                $tracking->normalizeDateFilters($request->only(['from', 'to', 'step_id', 'event_name', 'per_page']))
            )
        );
    }

    public function saveLayout(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'step_id' => [
                'required',
                'integer',
                Rule::exists('funnel_steps', 'id')->where(fn ($q) => $q->where('funnel_id', $funnel->id)),
            ],
            'layout_json' => 'required',
            'layout_breakpoint' => ['nullable', 'string', Rule::in(['desktop', 'mobile', 'tablet'])],
            'background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'skip_revision' => ['nullable', 'boolean'],
        ]);

        $layoutBreakpoint = strtolower((string) ($validated['layout_breakpoint'] ?? 'desktop'));
        if ($layoutBreakpoint === 'tablet') {
            $layoutBreakpoint = 'mobile';
        }
        if (! in_array($layoutBreakpoint, ['desktop', 'mobile'], true)) {
            $layoutBreakpoint = 'desktop';
        }

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

        $step = $funnel->steps()->where('id', $validated['step_id'])->firstOrFail();
        $skipRevision = (bool) ($validated['skip_revision'] ?? false);
        if (! $skipRevision && $layoutBreakpoint === 'desktop') {
            $this->rememberStepRevision(
                $step,
                $this->normalizeRevisionLayout($step->layout_json),
                $this->normalizeRevisionBackground($step->background_color)
            );
        }

        $layout = $this->sanitizeLayoutJson($rawLayout);
        $this->mergeElementSizeFromRaw($layout, $rawLayout);

        $update = [
            'background_color' => $validated['background_color'] ?? null,
        ];
        if ($layoutBreakpoint === 'desktop') {
            $update['layout_json'] = $layout;
        } else {
            $update['layout_json_mobile'] = $layout;
        }
        $step->update($update);

        if (! $skipRevision && $layoutBreakpoint === 'desktop') {
            $this->rememberStepRevision(
                $step,
                $this->normalizeRevisionLayout($step->layout_json),
                $this->normalizeRevisionBackground($step->background_color)
            );
        }
        $step->load('revisions');

        return response()->json([
            'message' => 'Layout saved successfully.',
            'step_id' => $step->id,
            'layout_json' => $step->layout_json,
            'layout_json_tablet' => $step->layout_json_tablet,
            'layout_json_mobile' => $step->layout_json_mobile,
            'layout_breakpoint' => $layoutBreakpoint,
            'background_color' => $step->background_color,
            'revision_history' => $this->revisionHistoryPayload($step),
        ]);
    }

    public function storeVersion(Request $request, Funnel $funnel, FunnelStep $step)
    {
        $this->ensureTenantFunnelAccess($funnel);
        if ((int) $step->funnel_id !== (int) $funnel->id) {
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
            ->filter(fn (FunnelStepRevision $revision) => (string) ($revision->version_type ?? 'autosave') === 'manual')
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

    /**
     * Copy width/height from raw request layout into sanitized layout so they never get dropped.
     */
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
                $v = '';
                if (isset($rawStyle[$key]) && trim((string) $rawStyle[$key]) !== '') {
                    $v = mb_substr(trim((string) $rawStyle[$key]), 0, 60);
                } elseif ($key === 'width' && isset($rawSettings[$key]) && trim((string) $rawSettings[$key]) !== '') {
                    $v = mb_substr(trim((string) $rawSettings[$key]), 0, 60);
                }
                if ($v !== '') {
                    $sanitizedElement['style'] = $sanitizedElement['style'] ?? [];
                    $sanitizedElement['style'][$key] = $v;
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
        if (count($rawRoot) === 0 && is_array($rawLayout['sections'] ?? null)) {
            $rawRoot = collect($rawLayout['sections'])
                ->filter(fn ($section) => is_array($section))
                ->map(fn (array $section) => array_merge(['kind' => 'section'], $section))
                ->values()
                ->all();
        }

        foreach ($layout['root'] as $ri => $rootItem) {
            $rawItem = (array) ($rawRoot[$ri] ?? []);
            $kind = strtolower((string) ($rootItem['kind'] ?? 'section'));
            if ($kind === 'section') {
                $mergeSection($layout['root'][$ri], $rawItem);
                continue;
            }
            if ($kind === 'row') {
                $mergeRow($layout['root'][$ri], $rawItem);
                continue;
            }
            if ($kind === 'column') {
                $mergeColumn($layout['root'][$ri], $rawItem);
                continue;
            }
            if ($kind === 'el') {
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

    public function uploadBuilderImage(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        // Check if file was received and if PHP reported an upload error (e.g. size limit)
        if (! $request->hasFile('image')) {
            return response()->json([
                'message' => 'No file received. The server may be rejecting it. Ensure php.ini has upload_max_filesize and post_max_size at least 100M, and that the form uses multipart/form-data.',
            ], 422);
        }

        $file = $request->file('image');
        if (! $file->isValid()) {
            $code = $file->getError();
            $messages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server limit (upload_max_filesize in php.ini). Use the php.ini that your web server actually uses (run "php --ini" to see).',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
                UPLOAD_ERR_PARTIAL => 'Upload was interrupted (partial upload).',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            ];

            return response()->json([
                'message' => $messages[$code] ?? 'Upload error (code ' . $code . ').',
            ], 422);
        }

        try {
            $validated = $request->validate([
                'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,wmv,mkv,webm,m4v,3gp,ogv|max:102400',
            ], [
                'image.required' => 'No file selected.',
                'image.file' => 'Invalid file.',
                'image.mimes' => 'File must be an image (jpg, png, gif, webp, svg) or video (mp4, mov, webm, etc.).',
                'image.max' => 'File is too large (max 100 MB).',
            ]);

            $path = $validated['image']->store('funnel-builder/tenant-' . $funnel->tenant_id, 'public');
            $relativeUrl = $this->builderPublicAssetUrl($path);
            $assetKind = $this->builderAssetKindFromPath($path);

            $asset = FunnelBuilderAsset::updateOrCreate(
                [
                    'disk' => 'public',
                    'path' => $path,
                ],
                [
                    'tenant_id' => $funnel->tenant_id,
                    'funnel_id' => $funnel->id,
                    'user_id' => auth()->id(),
                    'original_name' => $validated['image']->getClientOriginalName(),
                    'mime_type' => $validated['image']->getMimeType(),
                    'kind' => $assetKind ?? 'image',
                    'size' => (int) ($validated['image']->getSize() ?? 0),
                ]
            );

            return response()->json([
                'asset' => $this->builderAssetPayload($asset),
                'url' => $relativeUrl,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $imageErrors = $errors['image'] ?? [];
            $isUploadFailed = ! empty($imageErrors) && collect($imageErrors)->contains(fn ($m) => str_contains((string) $m, 'failed to upload'));
            if ($isUploadFailed) {
                return response()->json([
                    'message' => 'File was too large or the upload was interrupted. Try a smaller file or use Video URL (e.g. YouTube or Vimeo link). Increase upload_max_filesize and post_max_size in the php.ini that your server uses (run "php --ini" to find it).',
                ], 422);
            }
            // Return all validation errors so the user sees the exact reason
            $first = collect($imageErrors)->first();
            return response()->json([
                'message' => is_string($first) ? $first : 'Validation failed.',
                'errors' => $errors,
            ], 422);
        } catch (\Throwable $e) {
            $message = trim((string) ($e->getMessage() ?? ''));
            return response()->json([
                'message' => $message !== '' ? $message : 'Upload failed. Please check file type and size (max 100 MB).',
            ], 500);
        }
    }

    public function builderAssets(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $kind = strtolower(trim((string) $request->query('kind', '')));
        if (! in_array($kind, ['', 'image', 'video'], true)) {
            $kind = '';
        }

        $assets = FunnelBuilderAsset::query()
            ->where('tenant_id', $funnel->tenant_id)
            ->when($kind !== '', fn ($query) => $query->where('kind', $kind))
            ->latest('id')
            ->get()
            ->map(fn (FunnelBuilderAsset $asset) => $this->builderAssetPayload($asset))
            ->filter()
            ->keyBy('path');

        foreach ($this->legacyBuilderAssetPayload($funnel, $kind) as $legacyAsset) {
            if (! $assets->has($legacyAsset['path'])) {
                $assets->put($legacyAsset['path'], $legacyAsset);
            }
        }

        $assets = $assets
            ->sortByDesc('modified_at_ts')
            ->take(200)
            ->values()
            ->map(function (array $asset) {
                unset($asset['modified_at_ts']);
                return $asset;
            })
            ->all();

        return response()->json([
            'assets' => $assets,
        ]);
    }

    public function destroyBuilderAssets(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'paths' => ['required', 'array', 'min:1', 'max:200'],
            'paths.*' => ['required', 'string', 'max:2048'],
        ]);

        $tenantId = (int) $funnel->tenant_id;
        $deleted = 0;

        $paths = collect($validated['paths'] ?? [])
            ->map(fn ($path) => ltrim(str_replace('\\', '/', (string) $path), '/'))
            ->filter()
            ->unique()
            ->values();

        foreach ($paths as $path) {
            if (! $this->builderAssetVisibleToTenant($path, $tenantId)) {
                continue;
            }

            $asset = FunnelBuilderAsset::query()
                ->where('tenant_id', $tenantId)
                ->where('path', $path)
                ->first();

            $diskName = (string) ($asset?->disk ?: 'public');
            $disk = Storage::disk($diskName);
            $removed = false;

            if ($disk->exists($path)) {
                $removed = (bool) $disk->delete($path);
            }

            if ($asset) {
                $asset->delete();
                $removed = true;
            }

            if ($removed) {
                $deleted++;
            }
        }

        return response()->json([
            'deleted' => $deleted,
            'message' => $deleted === 1 ? 'Deleted 1 file.' : 'Deleted ' . $deleted . ' files.',
        ]);
    }

    public function publish(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);
        $this->ensureTenantCanPublishFunnel($funnel);

        $steps = $funnel->steps()->where('is_active', true)->orderBy('position')->get()->values();
        if ($steps->isEmpty()) {
            return redirect()->back()->with('error', 'Publishing failed: add at least one active step.');
        }
        $issues = $this->validatePublishReadiness($steps, (string) $funnel->purpose);
        if (count($issues) > 0) {
            return redirect()->back()->with('error', 'Publishing failed: ' . implode(' ', $issues));
        }

        $funnel->update(['status' => 'published']);

        return redirect()->back()->with('success', 'Funnel published successfully.');
    }

    public function unpublish(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $funnel->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Funnel unpublished successfully.');
    }

    public function update(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $jsonRequest = $request->expectsJson();
        $validated = $request->validate([
            'name' => [$jsonRequest ? 'sometimes' : 'required', 'string', 'max:120'],
            'description' => [$jsonRequest ? 'sometimes' : 'nullable', 'nullable', 'string', 'max:2000'],
            'status' => [$jsonRequest ? 'sometimes' : 'required', Rule::in(array_keys(Funnel::STATUSES))],
            'default_tags' => [$jsonRequest ? 'sometimes' : 'nullable', 'nullable', 'string', 'max:500'],
            'purpose' => ['sometimes', 'nullable', Rule::in(array_keys(Funnel::PURPOSES))],
        ]);

        try {
            if (array_key_exists('default_tags', $validated)) {
                $validated['default_tags'] = $this->normalizeTagsString($validated['default_tags'] ?? null);
            }
            $funnel->update($validated);
            if ($jsonRequest) {
                return response()->json([
                    'message' => 'Edited Successfully',
                    'funnel' => [
                        'id' => $funnel->id,
                        'purpose' => (string) $funnel->fresh()->purpose,
                    ],
                ]);
            }
            return redirect()->back()->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            if ($jsonRequest) {
                return response()->json([
                    'message' => 'Edited Failed',
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Edited Failed');
        }
    }

    public function destroy(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        try {
            $funnel->delete();
            return redirect()->route('funnels.index')->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Deleted Failed');
        }
    }

    public function storeStep(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:160',
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('funnel_steps', 'slug')->where(fn ($q) => $q->where('funnel_id', $funnel->id)),
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
            $position = (int) $funnel->steps()->max('position') + 1;
            $heroUrl = null;
            if ($request->hasFile('hero_image')) {
                $path = $request->file('hero_image')->store('funnel-heroes', 'public');
                $heroUrl = Storage::url($path);
            }

            $step = $funnel->steps()->create([
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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Added Successfully',
                    'step' => $this->builderStepPayload($step),
                ]);
            }

            return redirect()->back()->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Added Failed'], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    public function updateStep(Request $request, Funnel $funnel, FunnelStep $step)
    {
        $this->ensureTenantFunnelAccess($funnel);
        if ((int) $step->funnel_id !== (int) $funnel->id) {
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
                Rule::unique('funnel_steps', 'slug')
                    ->where(fn ($q) => $q->where('funnel_id', $funnel->id))
                    ->ignore($step->id),
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
                $path = $request->file('hero_image')->store('funnel-heroes', 'public');
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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Edited Successfully',
                    'step' => $this->builderStepPayload($step),
                ]);
            }

            return redirect()->back()->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Edited Failed'], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Edited Failed');
        }
    }

    public function destroyStep(Funnel $funnel, FunnelStep $step)
    {
        $this->ensureTenantFunnelAccess($funnel);
        if ((int) $step->funnel_id !== (int) $funnel->id) {
            abort(404);
        }

        $totalSteps = (int) $funnel->steps()->count();
        if ($totalSteps <= 1) {
            $msg = 'Cannot delete the last page.';
            if (request()->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $requiredTypes = ['landing', 'opt_in', 'sales', 'checkout', 'thank_you'];
        if (in_array((string) $step->type, $requiredTypes, true)) {
            $typeCount = (int) $funnel->steps()->where('type', $step->type)->count();
            if ($typeCount <= 1) {
                $label = (string) ($step->title ?: ucfirst(str_replace('_', ' ', (string) $step->type)));
                $msg = 'Cannot delete the last required page: ' . $label . '.';
                if (request()->expectsJson()) {
                    return response()->json(['message' => $msg], 422);
                }
                return redirect()->back()->with('error', $msg);
            }
        }

        try {
            $step->delete();
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Deleted Successfully']);
            }
            return redirect()->back()->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Deleted Failed'], 422);
            }
            return redirect()->back()->with('error', 'Deleted Failed');
        }
    }

    public function reorderSteps(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'required|integer',
        ]);

        $ids = collect($validated['order'])->map(fn ($id) => (int) $id)->values();
        $existingIds = $funnel->steps()->pluck('id')->map(fn ($id) => (int) $id)->values();

        if ($ids->sort()->values()->all() !== $existingIds->sort()->values()->all()) {
            abort(422, 'Invalid step order.');
        }

        foreach ($ids as $index => $id) {
            $funnel->steps()->where('id', $id)->update(['position' => $index + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Edited Successfully',
                'order' => $ids->values()->all(),
            ]);
        }

        return redirect()->back()->with('success', 'Edited Successfully');
    }


    private function ensureTenantFunnelAccess(Funnel $funnel): void
    {
        if ((int) $funnel->tenant_id !== (int) auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function ensureTenantCanPublishFunnel(Funnel $funnel): void
    {
        $tenant = auth()->user()->tenant;
        if (! $tenant) {
            return;
        }

        $plan = app(TenantPlanEnforcer::class)->resolvePlan($tenant);
        if (! $plan || $plan->max_funnels === null) {
            return;
        }

        if ((string) $funnel->status === 'published') {
            return;
        }

        $publishedCount = Funnel::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'published')
            ->count();

        if ($publishedCount >= (int) $plan->max_funnels) {
            abort(422, sprintf(
                'You have reached your published funnels limit for the %s plan. Upgrade your subscription to publish another funnel.',
                $plan->name
            ));
        }
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

    private function builderAssetPayload(FunnelBuilderAsset $asset): ?array
    {
        $disk = Storage::disk((string) ($asset->disk ?: 'public'));

        if (! $disk->exists($asset->path)) {
            return null;
        }

        $relativeUrl = $this->builderPublicAssetUrl($asset->path, (string) ($asset->disk ?: 'public'));
        $modifiedAt = $asset->created_at?->getTimestamp() ?: $asset->updated_at?->getTimestamp() ?: now()->getTimestamp();

        return [
            'id' => $asset->id,
            'name' => trim((string) ($asset->original_name ?? '')) !== '' ? trim((string) $asset->original_name) : basename($asset->path),
            'path' => $asset->path,
            'url' => $relativeUrl,
            'kind' => (string) ($asset->kind ?: $this->builderAssetKindFromPath($asset->path) ?: 'image'),
            'size' => (int) ($asset->size ?? 0),
            'modified_at' => $asset->created_at?->toIso8601String() ?: date(DATE_ATOM, $modifiedAt),
            'modified_at_ts' => $modifiedAt,
        ];
    }

    private function legacyBuilderAssetPayload(Funnel $funnel, string $kind = ''): array
    {
        $disk = Storage::disk('public');

        return collect($disk->allFiles('funnel-builder'))
            ->filter(fn (string $path) => $this->builderAssetVisibleToTenant($path, (int) $funnel->tenant_id))
            ->map(function (string $path) use ($disk) {
                $assetKind = $this->builderAssetKindFromPath($path);
                if ($assetKind === null) {
                    return null;
                }

                $relativeUrl = $this->builderPublicAssetUrl($path, 'public');
                $modifiedAt = $disk->lastModified($path);

                return [
                    'name' => basename($path),
                    'path' => $path,
                    'url' => $relativeUrl,
                    'kind' => $assetKind,
                    'size' => (int) $disk->size($path),
                    'modified_at' => date(DATE_ATOM, $modifiedAt),
                    'modified_at_ts' => $modifiedAt,
                ];
            })
            ->filter()
            ->when($kind !== '', fn ($items) => $items->where('kind', $kind))
            ->values()
            ->all();
    }

    private function builderAssetVisibleToTenant(string $path, int $tenantId): bool
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if (! str_starts_with($normalized, 'funnel-builder/')) {
            return false;
        }

        $relative = substr($normalized, strlen('funnel-builder/'));
        if ($relative === false || $relative === '') {
            return false;
        }

        if (! str_starts_with($relative, 'tenant-')) {
            return true;
        }

        return str_starts_with($relative, 'tenant-' . $tenantId . '/');
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

        if ($pathPart !== '') {
            return $pathPart . $queryPart . $fragmentPart;
        }

        return '/storage/' . ltrim($path, '/');
    }

    private function builderStepPayload(FunnelStep $step): array
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

    private function manualVersionPayload(FunnelStep $step): array
    {
        $revisions = $step->relationLoaded('revisions')
            ? $step->revisions
            : $step->revisions()->get();

        return $revisions
            ->filter(fn (FunnelStepRevision $revision) => (string) ($revision->version_type ?? 'autosave') === 'manual')
            ->sortBy(fn (FunnelStepRevision $revision) => [
                $revision->created_at?->getTimestamp() ?? 0,
                $revision->id,
            ])
            ->map(function (FunnelStepRevision $revision) {
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

    private function ensureStepHasInitialRevision(FunnelStep $step): bool
    {
        if ($step->relationLoaded('revisions')) {
            if ($step->revisions->isNotEmpty()) {
                return false;
            }
        } elseif ($step->revisions()->exists()) {
            return false;
        }

        $this->rememberStepRevision(
            $step,
            $this->normalizeRevisionLayout($step->layout_json),
            $this->normalizeRevisionBackground($step->background_color)
        );

        return true;
    }

    private function revisionHistoryPayload(FunnelStep $step): array
    {
        $revisions = $step->relationLoaded('revisions')
            ? $step->revisions
            : $step->revisions()->get();

        return $revisions
            ->sortBy(fn (FunnelStepRevision $revision) => [
                $revision->created_at?->getTimestamp() ?? 0,
                $revision->id,
            ])
            ->map(function (FunnelStepRevision $revision) {
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

    private function rememberStepRevision(FunnelStep $step, mixed $layout, ?string $backgroundColor): void
    {
        $normalizedLayout = $this->normalizeRevisionLayout($layout);
        $normalizedBackground = $this->normalizeRevisionBackground($backgroundColor);
        $latest = $step->revisions()->latest('id')->first();

        if ($latest) {
            $latestLayout = $this->normalizeRevisionLayout($latest->layout_json);
            $latestBackground = $this->normalizeRevisionBackground($latest->background_color);
            if (
                $this->revisionLayoutsMatch($latestLayout, $normalizedLayout)
                && $latestBackground === $normalizedBackground
            ) {
                return;
            }
        }

        $step->revisions()->create([
            'user_id' => auth()->id(),
            'layout_json' => $normalizedLayout,
            'background_color' => $normalizedBackground,
        ]);

        $keepIds = $step->revisions()
            ->reorder()
            ->orderByDesc('id')
            ->limit(self::MAX_STEP_REVISIONS)
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            $step->revisions()
                ->reorder()
                ->whereNotIn('id', $keepIds)
                ->delete();
        }
    }

    private function normalizeRevisionLayout(mixed $layout): array
    {
        if (! is_array($layout)) {
            return ['root' => [], 'sections' => []];
        }

        if (! isset($layout['root']) && ! isset($layout['sections'])) {
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
        if ($value === '') {
            return 'Saved version';
        }

        return mb_substr($value, 0, 120);
    }

    private function revisionLayoutsMatch(array $left, array $right): bool
    {
        return json_encode($left) === json_encode($right);
    }

    private function sanitizeLayoutJson(array $layout): array
    {
        $sanitizeElement = function (array $element): array {
            $type = (string) ($element['type'] ?? 'text');
            $type = in_array($type, [
                'heading', 'text', 'image', 'button', 'icon', 'form', 'shipping_details', 'video', 'countdown', 'spacer', 'menu', 'carousel',
                'testimonial', 'faq', 'pricing', 'product_offer', 'checkout_summary', 'physical_checkout_summary', 'review_form', 'reviews',
            ], true) ? $type : 'text';

            $rawContent = (string) ($element['content'] ?? '');
            $content = in_array($type, ['heading', 'text', 'button'], true)
                ? $this->sanitizeRichText($rawContent)
                : mb_substr(trim($rawContent), 0, 5000);

            $rawStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
            $style = $this->sanitizeStyle($rawStyle);
            $rawSettings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
            $settings = $this->sanitizeSettings($rawSettings);
            if ($type === 'video' || $type === 'image' || $type === 'form') {
                foreach (['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight'] as $sizeKey) {
                    $fromStyle = isset($rawStyle[$sizeKey]) && trim((string) $rawStyle[$sizeKey]) !== '';
                    $fromSettings = isset($rawSettings[$sizeKey]) && trim((string) $rawSettings[$sizeKey]) !== '';
                    if ($fromStyle) {
                        $style[$sizeKey] = mb_substr(trim((string) $rawStyle[$sizeKey]), 0, 60);
                    } elseif ($fromSettings && $sizeKey === 'width') {
                        $style['width'] = mb_substr(trim((string) $rawSettings[$sizeKey]), 0, 60);
                    } elseif ($type === 'form' && $sizeKey === 'width' && isset($rawSettings['formWidth']) && trim((string) $rawSettings['formWidth']) !== '') {
                        $style['width'] = mb_substr(trim((string) $rawSettings['formWidth']), 0, 60);
                    }
                }
            }

            return [
                'id' => $this->sanitizeId($element['id'] ?? null, 'el'),
                'type' => $type,
                'content' => $content,
                'style' => $style,
                'settings' => $settings,
            ];
        };
        $sanitizeColumn = function (array $column) use ($sanitizeElement): array {
            $elements = collect($column['elements'] ?? [])
                ->filter(fn ($element) => is_array($element))
                ->take(60)
                ->map(fn (array $element) => $sanitizeElement($element))
                ->values()
                ->all();

            return [
                'id' => $this->sanitizeId($column['id'] ?? null, 'col'),
                'style' => $this->sanitizeStyle($column['style'] ?? []),
                'settings' => $this->sanitizeContainerSettings($column['settings'] ?? []),
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
                'id' => $this->sanitizeId($row['id'] ?? null, 'row'),
                'style' => $this->sanitizeStyle($row['style'] ?? []),
                'settings' => $this->sanitizeContainerSettings($row['settings'] ?? []),
                'columns' => $columns,
            ];
        };

        $sanitizeSection = function (array $section) use ($sanitizeElement, $sanitizeRow): array {
            $sectionElements = collect($section['elements'] ?? [])
                ->filter(fn ($element) => is_array($element))
                ->take(60)
                ->map(fn (array $element) => $sanitizeElement($element))
                ->values()
                ->all();
            $rows = collect($section['rows'] ?? [])
                ->filter(fn ($row) => is_array($row))
                ->take(12)
                ->map(fn (array $row) => $sanitizeRow($row))
                ->values()
                ->all();

            return [
                'id' => $this->sanitizeId($section['id'] ?? null, 'sec'),
                'style' => $this->sanitizeStyle($section['style'] ?? []),
                'settings' => $this->sanitizeSectionSettings($section['settings'] ?? []),
                'elements' => $sectionElements,
                'rows' => $rows,
            ];
        };

        $rawRoot = is_array($layout['root'] ?? null) ? $layout['root'] : [];
        if (count($rawRoot) === 0 && is_array($layout['sections'] ?? null)) {
            $rawRoot = collect($layout['sections'])
                ->filter(fn ($section) => is_array($section))
                ->map(fn (array $section) => array_merge(['kind' => 'section'], $section))
                ->values()
                ->all();
        }

        $root = collect($rawRoot)
            ->filter(fn ($item) => is_array($item))
            ->take(120)
            ->map(function (array $item) use ($sanitizeElement, $sanitizeSection, $sanitizeRow, $sanitizeColumn) {
                $kind = strtolower((string) ($item['kind'] ?? 'section'));
                if ($kind === 'section') {
                    return array_merge(['kind' => 'section'], $sanitizeSection($item));
                }
                if ($kind === 'row') {
                    return array_merge(['kind' => 'row'], $sanitizeRow($item));
                }
                if ($kind === 'column' || $kind === 'col') {
                    return array_merge(['kind' => 'column'], $sanitizeColumn($item));
                }
                return array_merge(['kind' => 'el'], $sanitizeElement($item));
            })
            ->values()
            ->all();

        $sections = collect($root)
            ->filter(fn ($item) => is_array($item) && strtolower((string) ($item['kind'] ?? '')) === 'section')
            ->map(function (array $item) {
                unset($item['kind']);
                return $item;
            })
            ->values()
            ->all();

        $result = ['root' => $root, 'sections' => $sections];

        if (is_array($layout['__editor'] ?? null)) {
            $editor = [];
            if (isset($layout['__editor']['canvasBg']) && is_string($layout['__editor']['canvasBg'])) {
                $bg = trim($layout['__editor']['canvasBg']);
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $bg)) {
                    $editor['canvasBg'] = $bg;
                }
            }
            if (isset($layout['__editor']['canvasWidth']) && is_numeric($layout['__editor']['canvasWidth'])) {
                $editor['canvasWidth'] = (int) $layout['__editor']['canvasWidth'];
            }
            if (isset($layout['__editor']['canvasInnerWidth']) && is_numeric($layout['__editor']['canvasInnerWidth'])) {
                $editor['canvasInnerWidth'] = (int) $layout['__editor']['canvasInnerWidth'];
            }
            if (isset($layout['__editor']['canvasContentWidth']) && is_numeric($layout['__editor']['canvasContentWidth'])) {
                $editor['canvasContentWidth'] = (int) $layout['__editor']['canvasContentWidth'];
            }
            if (! empty($editor)) {
                $result['__editor'] = $editor;
            }
        }

        $result = $this->enforceBuilderStructureRules($result);
        $result['__editor'] = is_array($result['__editor'] ?? null) ? $result['__editor'] : [];
        if (! isset($result['__editor']['canvasWidth'])) {
            $result['__editor']['canvasWidth'] = 1366;
        }
        if (! isset($result['__editor']['canvasContentWidth'])) {
            $result['__editor']['canvasContentWidth'] = 1366;
        }

        return $result;
    }

    private function enforceBuilderStructureRules(array $layout): array
    {
        $root = is_array($layout['root'] ?? null) ? $layout['root'] : [];
        $editor = is_array($layout['__editor'] ?? null) ? $layout['__editor'] : null;
        $resultRoot = [];
        $menuItems = [];
        $pendingElements = [];
        $firstSectionIndex = null;

        $collectElementsFromColumn = function (array $column): array {
            return collect($column['elements'] ?? [])
                ->filter(fn ($element) => is_array($element))
                ->values()
                ->all();
        };

        $collectElementsFromRow = function (array $row) use ($collectElementsFromColumn): array {
            $elements = [];
            foreach ((array) ($row['columns'] ?? []) as $column) {
                if (! is_array($column)) {
                    continue;
                }
                $elements = array_merge($elements, $collectElementsFromColumn($column));
            }
            return $elements;
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
        $splitMenuElements = function (array $elements) use (&$menuItems, $normalizeMenuElement): array {
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
                $elements = collect($item['elements'] ?? [])
                    ->filter(fn ($element) => is_array($element))
                    ->values()
                    ->all();
                foreach ((array) ($item['rows'] ?? []) as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $elements = array_merge($elements, $collectElementsFromRow($row));
                }
                $elements = $splitMenuElements($elements);

                $normalizedSection = [
                    'kind' => 'section',
                    'id' => $item['id'] ?? ('sec_' . Str::lower(Str::random(10))),
                    'style' => is_array($item['style'] ?? null) ? $item['style'] : [],
                    'settings' => is_array($item['settings'] ?? null) ? $item['settings'] : [],
                    'elements' => $elements,
                    'rows' => [],
                ];
                if ($firstSectionIndex === null) {
                    $firstSectionIndex = count($resultRoot);
                }
                $resultRoot[] = $normalizedSection;
                continue;
            }

            if ($kind === 'row') {
                $pendingElements = array_merge($pendingElements, $splitMenuElements($collectElementsFromRow($item)));
                continue;
            }

            if ($kind === 'column' || $kind === 'col') {
                $pendingElements = array_merge($pendingElements, $splitMenuElements($collectElementsFromColumn($item)));
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
                $item['rows'] = [];
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

    private function sanitizeId(mixed $value, string $prefix): string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return $prefix . '_' . Str::lower(Str::random(10));
        }

        $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '', $raw) ?: '';
        $safe = mb_substr($safe, 0, 60);

        return $safe !== '' ? $safe : $prefix . '_' . Str::lower(Str::random(10));
    }

    private function sanitizeStyle(mixed $style): array
    {
        if (!is_array($style)) {
            return [];
        }

        $allowedKeys = [
            'backgroundColor',
            'overlayColor',
            'overlayOpacity',
            'color',
            'fontSize',
            'fontWeight',
            'fontStyle',
            'fontFamily',
            'padding',
            'margin',
            'textAlign',
            'borderRadius',
            'border',
            'boxShadow',
            'width',
            'height',
            'maxWidth',
            'minWidth',
            'maxHeight',
            'minHeight',
            'backgroundImage',
            'backgroundSize',
            'backgroundPosition',
            'backgroundRepeat',
            'backgroundAttachment',
            'justifyContent',
            'alignItems',
            'flex',
            'flexDirection',
            'flexWrap',
            'gap',
            'lineHeight',
            'letterSpacing',
            'textDecorationColor',
            'textDecoration',
            'position',
            'left',
            'top',
            'zIndex',
        ];

        $safe = [];
        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $style)) {
                continue;
            }

            $value = trim((string) $style[$key]);
            $value = mb_substr($value, 0, 260);
            if ($value === '') {
                continue;
            }

            if ($key === 'backgroundImage') {
                if (preg_match('/^url\(((https?:\/\/|\/)[^\s)]+)\)$/i', $value)) {
                    $safe[$key] = $value;
                }
                continue;
            }

            if ($key === 'overlayColor') {
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                    $safe[$key] = $value;
                }
                continue;
            }

            if ($key === 'overlayOpacity') {
                if (is_numeric($value)) {
                    $n = (float) $value;
                    // Accept either 0..1 or 0..100 (percent). Store as given to preserve UI intent.
                    if (($n >= 0.0 && $n <= 1.0) || ($n >= 0.0 && $n <= 100.0)) {
                        $safe[$key] = rtrim(rtrim((string) $n, '0'), '.');
                    }
                }
                continue;
            }

            // Persist CSS size values (50%, 100%, 400px, etc.) so editor width/height survive refresh
            if ($key === 'position') {
                if (in_array($value, ['absolute', 'relative'], true)) {
                    $safe[$key] = $value;
                }
                continue;
            }

            if ($key === 'zIndex') {
                $n = (int) $value;
                if ($n >= 0 && $n <= 9999) {
                    $safe[$key] = (string) $n;
                }
                continue;
            }

            $sizeKeys = ['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight', 'left', 'top'];
            if (in_array($key, $sizeKeys, true)) {
                $len = mb_strlen($value);
                if ($len > 0 && $len <= 60) {
                    $safe[$key] = $value;
                }
                continue;
            }

            if (preg_match('/^[#(),.%:\-\/\sA-Za-z0-9]+$/', $value)) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    private function sanitizeSettings(mixed $settings): array
    {
        if (!is_array($settings)) {
            return [];
        }

        $safe = [];

        $readString = function (string $key, int $max = 1024) use (&$settings): ?string {
            if (!array_key_exists($key, $settings) || (!is_scalar($settings[$key]) && $settings[$key] !== null)) {
                return null;
            }
            return mb_substr(trim((string) $settings[$key]), 0, $max);
        };
        $readStringAllowEmpty = function (string $key, int $max = 1024) use (&$settings): ?string {
            if (!array_key_exists($key, $settings) || (!is_scalar($settings[$key]) && $settings[$key] !== null)) {
                return null;
            }
            return mb_substr(trim((string) $settings[$key]), 0, $max);
        };
        $readEnum = function (string $key, array $allowed, ?string $default = null) use (&$settings): ?string {
            if (!array_key_exists($key, $settings) || !is_scalar($settings[$key])) {
                return null;
            }
            $v = trim((string) $settings[$key]);
            if (in_array($v, $allowed, true)) {
                return $v;
            }
            return $default;
        };
        $readBool = function (string $key) use (&$settings): ?bool {
            if (!array_key_exists($key, $settings)) {
                return null;
            }
            return filter_var($settings[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        };
        $readInt = function (string $key, int $min, int $max) use (&$settings): ?int {
            if (!array_key_exists($key, $settings)) {
                return null;
            }
            if (!is_scalar($settings[$key])) {
                return null;
            }
            $n = (int) $settings[$key];
            if ($n < $min) {
                $n = $min;
            }
            if ($n > $max) {
                $n = $max;
            }
            return $n;
        };
        $readFloat = function (string $key, float $min, float $max) use (&$settings): ?float {
            if (!array_key_exists($key, $settings)) {
                return null;
            }
            if (!is_scalar($settings[$key]) || !is_numeric($settings[$key])) {
                return null;
            }
            $n = (float) $settings[$key];
            if ($n < $min) {
                $n = $min;
            }
            if ($n > $max) {
                $n = $max;
            }
            return $n;
        };
        $readColor = function (string $key, bool $allowEmpty = false) use (&$settings): ?string {
            if (!array_key_exists($key, $settings) || !is_scalar($settings[$key])) {
                return null;
            }
            $v = trim((string) $settings[$key]);
            if ($v === '' && $allowEmpty) {
                return '';
            }
            if (preg_match('/^#[0-9A-Fa-f]{6}$/', $v)) {
                return $v;
            }
            return null;
        };

        foreach ([
            'link' => 2048,
            'src' => 2048,
            'alt' => 1024,
            'placeholder' => 1024,
            'targetDate' => 120,
            'platform' => 120,
            'width' => 60,
            'actionStepSlug' => 120,
        ] as $k => $maxLen) {
            $v = $readString($k, $maxLen);
            if ($v !== null && $v !== '') {
                $safe[$k] = $v;
            }
        }
        foreach ([
            'quote' => 2000,
            'heading' => 300,
            'physicalHeading' => 300,
            'name' => 200,
            'role' => 200,
            'avatar' => 2048,
            'plan' => 200,
            'price' => 200,
            'regularPrice' => 200,
            'period' => 60,
            'subtitle' => 300,
            'physicalSubtitle' => 500,
            'description' => 4000,
            'badge' => 80,
            'buttonLabel' => 120,
            'successMessage' => 500,
            'publicLabel' => 300,
            'emptyText' => 500,
            'ctaLabel' => 120,
            'ctaActionStepSlug' => 120,
            'ctaLink' => 2048,
            'quickViewLabel' => 120,
            'endAt' => 120,
            'label' => 120,
            'expiredText' => 200,
            'promoKey' => 120,
            'linkedPricingId' => 120,
            'expandLabel' => 120,
            'collapseLabel' => 120,
            'leftButtonLabel' => 120,
            'leftButtonUrl' => 2048,
            'rightLogoUrl' => 2048,
            'rightLogoAlt' => 300,
        ] as $k => $maxLen) {
            $v = $readStringAllowEmpty($k, $maxLen);
            if ($v !== null) {
                if (in_array($k, ['price', 'regularPrice'], true) && preg_match('/^\s*\$/', $v) === 1) {
                    $v = preg_replace('/^\s*\$/', '₱', $v) ?? $v;
                }
                $safe[$k] = $v;
            }
        }
        $iconName = strtolower((string) ($readString('iconName', 40) ?? ''));
        if ($iconName !== null && preg_match('/^[a-z0-9-]{1,40}$/', $iconName)) {
            $safe['iconName'] = $iconName;
        }

        foreach ([
            'alignment' => ['left', 'center', 'right'],
            'widthBehavior' => ['fluid', 'fill'],
            'imageSourceType' => ['direct', 'upload'],
            'videoSourceType' => ['direct', 'upload'],
            'iconStyle' => ['solid', 'regular', 'brands'],
            'menuAlign' => ['left', 'center', 'right'],
            'buttonAlign' => ['left', 'center', 'right'],
            'vAlign' => ['top', 'center', 'bottom'],
            'slideshowMode' => ['manual', 'auto'],
            'layout' => ['list', 'grid'],
            'actionType' => ['next_step', 'step', 'link', 'checkout', 'offer_accept', 'offer_decline'],
            'ctaActionType' => ['next_step', 'step', 'link', 'checkout'],
            'positionMode' => ['absolute', 'relative', 'flow'],
        ] as $k => $allowed) {
            $v = $readEnum($k, $allowed);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }

        foreach (['autoplay', 'controls', 'showArrows', 'imageRadiusLinked', 'videoRadiusLinked', 'quickViewEnabled', 'cartEnabled', 'showRating', 'showDate', 'collapsible', 'leftButtonBold', 'leftButtonItalic'] as $k) {
            $v = $readBool($k);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }

        foreach ([
            'itemGap' => [0, 300],
            'activeIndex' => [0, 500],
            'activeSlide' => [0, 500],
            'carouselActiveRow' => [0, 500],
            'carouselActiveCol' => [0, 500],
            'activeMedia' => [0, 500],
            'stockQuantity' => [0, 1000000],
            'fixedWidth' => [50, 2400],
            'fixedHeight' => [50, 1600],
            'offsetX' => [-2400, 2400],
            'freeX' => [0, 9999],
            'freeY' => [0, 9999],
            'cropTop' => [0, 1800],
            'cropRight' => [0, 2400],
            'cropBottom' => [0, 1800],
            'cropLeft' => [0, 2400],
            'maxItems' => [1, 24],
            'filterRating' => [0, 5],
            'collapsedCount' => [1, 24],
            'leftButtonTextSize' => [10, 48],
            'leftButtonBorderRadius' => [0, 80],
            'leftButtonPaddingY' => [4, 40],
            'leftButtonPaddingX' => [8, 80],
        ] as $k => $range) {
            $v = $readInt($k, $range[0], $range[1]);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }
        $scale = $readFloat('contentScale', 0.5, 3.0);
        if ($scale !== null) {
            $safe['contentScale'] = $scale;
        }

        foreach (['textColor', 'controlsColor', 'arrowColor', 'bodyBgColor', 'containerBgColor', 'labelColor', 'placeholderColor', 'buttonBgColor', 'buttonTextColor', 'questionColor', 'answerColor', 'numberColor', 'ctaBgColor', 'ctaTextColor', 'leftButtonBgColor', 'leftButtonTextColor'] as $k) {
            $v = $readColor($k);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }
        $underline = $readColor('underlineColor', true);
        if ($underline !== null) {
            $safe['underlineColor'] = $underline;
        }

        if (isset($settings['items']) && is_array($settings['items'])) {
            $isFaq = collect($settings['items'])
                ->filter(fn ($item) => is_array($item))
                ->contains(function (array $item) {
                    return array_key_exists('q', $item) || array_key_exists('a', $item) || array_key_exists('question', $item) || array_key_exists('answer', $item);
                });

            if ($isFaq) {
                $safe['items'] = collect($settings['items'])
                    ->filter(fn ($item) => is_array($item))
                    ->take(50)
                    ->map(function (array $item) {
                        $q = mb_substr(trim((string) ($item['q'] ?? ($item['question'] ?? ''))), 0, 500);
                        $a = mb_substr(trim((string) ($item['a'] ?? ($item['answer'] ?? ''))), 0, 1000);
                        return ['q' => $q, 'a' => $a];
                    })
                    ->values()
                    ->all();
            } else {
                $safe['items'] = collect($settings['items'])
                    ->filter(fn ($item) => is_array($item))
                    ->take(50)
                    ->map(function (array $item) {
                        return [
                            'label' => mb_substr(trim((string) ($item['label'] ?? '')), 0, 200),
                            'url' => mb_substr(trim((string) ($item['url'] ?? '')), 0, 2048),
                            'newWindow' => (bool) filter_var($item['newWindow'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'hasSubmenu' => (bool) filter_var($item['hasSubmenu'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        ];
                    })
                    ->values()
                    ->all();
            }
        }

        if (isset($settings['features']) && is_array($settings['features'])) {
            $safe['features'] = collect($settings['features'])
                ->filter(fn ($item) => is_scalar($item))
                ->take(40)
                ->map(fn ($item) => mb_substr(trim((string) $item), 0, 200))
                ->filter(fn ($item) => $item !== '')
                ->values()
                ->all();
        }

        if (isset($settings['fields']) && is_array($settings['fields'])) {
            $allowedFieldTypes = ['first_name', 'last_name', 'email', 'phone_number', 'province', 'city_municipality', 'barangay', 'street', 'custom'];
            $safe['fields'] = collect($settings['fields'])
                ->filter(fn ($field) => is_array($field))
                ->take(30)
                ->map(function (array $field) use ($allowedFieldTypes) {
                    $type = trim((string) ($field['type'] ?? 'custom'));
                    if (!in_array($type, $allowedFieldTypes, true)) {
                        $type = 'custom';
                    }
                    $label = mb_substr(trim((string) ($field['label'] ?? '')), 0, 150);
                    if ($label === '') {
                        $label = match ($type) {
                            'phone_number' => 'Phone',
                            'city_municipality' => 'City / Municipality',
                            default => ucwords(str_replace('_', ' ', $type)),
                        };
                    }
                    $placeholder = mb_substr(trim((string) ($field['placeholder'] ?? '')), 0, 180);
                    if ($placeholder === '') {
                        $placeholder = match ($type) {
                            'phone_number' => '09XXXXXXXXX',
                            'email' => 'Email address',
                            default => $label,
                        };
                    }
                    $required = (bool) filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    if ($type === 'email') {
                        $required = true;
                    }
                    return ['type' => $type, 'label' => $label, 'placeholder' => $placeholder, 'required' => $required];
                })
                ->values()
                ->all();
            if (count($safe['fields']) === 0) {
                $safe['fields'] = [
                    ['type' => 'first_name', 'label' => 'First name', 'placeholder' => 'First name', 'required' => false],
                    ['type' => 'last_name', 'label' => 'Last name', 'placeholder' => 'Last name', 'required' => false],
                    ['type' => 'email', 'label' => 'Email', 'placeholder' => 'Email address', 'required' => true],
                    ['type' => 'phone_number', 'label' => 'Phone', 'placeholder' => '09XXXXXXXXX', 'required' => false],
                ];
            }
        }

        if (isset($settings['slides']) && is_array($settings['slides'])) {
            $safe['slides'] = $this->sanitizeCarouselSlides($settings['slides']);
        }

        if (isset($settings['media']) && is_array($settings['media'])) {
            $safe['media'] = collect($settings['media'])
                ->filter(fn ($item) => is_array($item) || is_string($item))
                ->take(20)
                ->map(function ($item, int $index) {
                    if (is_string($item)) {
                        $item = ['type' => 'image', 'src' => $item];
                    }
                    $item = is_array($item) ? $item : [];
                    $type = strtolower(trim((string) ($item['type'] ?? 'image')));
                    if (! in_array($type, ['image', 'video'], true)) {
                        $type = 'image';
                    }
                    return [
                        'type' => $type,
                        'src' => mb_substr(trim((string) ($item['src'] ?? '')), 0, 2048),
                        'alt' => mb_substr(trim((string) ($item['alt'] ?? ('Media ' . ($index + 1)))), 0, 300),
                        'poster' => mb_substr(trim((string) ($item['poster'] ?? '')), 0, 2048),
                    ];
                })
                ->values()
                ->all();
        }

        if (isset($settings['menuCollapsed']) && is_array($settings['menuCollapsed'])) {
            $safe['menuCollapsed'] = collect($settings['menuCollapsed'])
                ->take(100)
                ->mapWithKeys(function ($v, $k) {
                    $idx = (string) ((int) $k);
                    return [$idx => (bool) filter_var($v, FILTER_VALIDATE_BOOLEAN)];
                })
                ->all();
        }

        return $safe;
    }

    private function sanitizeCarouselSlides(array $slides): array
    {
        return collect($slides)
            ->filter(fn ($slide) => is_array($slide))
            ->take(50)
            ->map(function (array $slide, int $slideIndex) {
                $rows = collect($slide['rows'] ?? [])
                    ->filter(fn ($row) => is_array($row))
                    ->take(30)
                    ->map(function (array $row) {
                        $columns = collect($row['columns'] ?? [])
                            ->filter(fn ($col) => is_array($col))
                            ->take(24)
                            ->map(function (array $col) {
                                $elements = collect($col['elements'] ?? [])
                                    ->filter(fn ($el) => is_array($el))
                                    ->take(60)
                                    ->map(function (array $element) {
                                        $type = (string) ($element['type'] ?? 'text');
                                        $type = in_array($type, [
                                            'heading', 'text', 'image', 'button', 'icon', 'form', 'shipping_details', 'video', 'countdown', 'spacer', 'menu', 'carousel',
                                            'testimonial', 'faq', 'pricing', 'product_offer', 'checkout_summary', 'physical_checkout_summary', 'review_form', 'reviews',
                                        ], true) ? $type : 'text';

                                        $rawContent = (string) ($element['content'] ?? '');
                                        $content = in_array($type, ['heading', 'text', 'button'], true)
                                            ? $this->sanitizeRichText($rawContent)
                                            : mb_substr(trim($rawContent), 0, 6000);

                                        return [
                                            'id' => $this->sanitizeId($element['id'] ?? null, 'el'),
                                            'type' => $type,
                                            'content' => $content,
                                            'style' => $this->sanitizeStyle($element['style'] ?? []),
                                            'settings' => $this->sanitizeSettings($element['settings'] ?? []),
                                        ];
                                    })
                                    ->values()
                                    ->all();

                                return [
                                    'id' => $this->sanitizeId($col['id'] ?? null, 'col'),
                                    'style' => $this->sanitizeStyle($col['style'] ?? []),
                                    'elements' => $elements,
                                ];
                            })
                            ->values()
                            ->all();

                        return [
                            'id' => $this->sanitizeId($row['id'] ?? null, 'row'),
                            'style' => $this->sanitizeStyle($row['style'] ?? []),
                            'columns' => $columns,
                        ];
                    })
                    ->values()
                    ->all();

                $image = is_array($slide['image'] ?? null) ? $slide['image'] : [];
                $imageSrc = mb_substr(trim((string) ($image['src'] ?? '')), 0, 4000);
                $imageAlt = mb_substr(trim((string) ($image['alt'] ?? 'Image')), 0, 200);
                if ($imageSrc === '') {
                    foreach ($rows as $row) {
                        foreach (($row['columns'] ?? []) as $column) {
                            foreach (($column['elements'] ?? []) as $element) {
                                if (($element['type'] ?? '') !== 'image') {
                                    continue;
                                }
                                $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                $candidate = trim((string) ($settings['src'] ?? ''));
                                if ($candidate !== '') {
                                    $imageSrc = mb_substr($candidate, 0, 4000);
                                    $imageAlt = mb_substr(trim((string) ($settings['alt'] ?? $imageAlt)), 0, 200);
                                    break 3;
                                }
                            }
                        }
                    }
                }

                return [
                    'label' => mb_substr(trim((string) ($slide['label'] ?? ('Slide #' . ($slideIndex + 1)))), 0, 200),
                    'image' => [
                        'src' => $imageSrc,
                        'alt' => $imageAlt !== '' ? $imageAlt : 'Image',
                    ],
                    'rows' => $rows,
                ];
            })
            ->values()
            ->all();
    }

    private function sanitizeContainerSettings(mixed $settings): array
    {
        if (!is_array($settings)) {
            return [];
        }

        $safe = [];
        $cw = trim((string) ($settings['contentWidth'] ?? ''));
        if (in_array($cw, ['full', 'wide', 'medium', 'small', 'xsmall'], true)) {
            $safe['contentWidth'] = $cw;
        }

        $borderStyle = trim((string) ($settings['rowBorderStyle'] ?? ''));
        if (in_array($borderStyle, ['none', 'solid', 'dashed', 'dotted', 'double'], true)) {
            $safe['rowBorderStyle'] = $borderStyle;
        }

        if (array_key_exists('rowRadiusPerCorner', $settings)) {
            $safe['rowRadiusPerCorner'] = (bool) filter_var($settings['rowRadiusPerCorner'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('stretch', $settings)) {
            $safe['stretch'] = (bool) filter_var($settings['stretch'], FILTER_VALIDATE_BOOLEAN);
        }

        $stretchJustify = trim((string) ($settings['stretchJustify'] ?? ''));
        if (in_array($stretchJustify, ['flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly'], true)) {
            $safe['stretchJustify'] = $stretchJustify;
        }

        $stretchAlign = trim((string) ($settings['stretchAlign'] ?? ''));
        if (in_array($stretchAlign, ['stretch', 'flex-start', 'center', 'flex-end', 'baseline'], true)) {
            $safe['stretchAlign'] = $stretchAlign;
        }

        return $safe;
    }

    private function sanitizeSectionSettings(mixed $settings): array
    {
        if (!is_array($settings)) {
            return [];
        }

        $safe = [];
        $cw = trim((string) ($settings['contentWidth'] ?? ''));
        if (in_array($cw, ['full', 'wide', 'medium', 'small', 'xsmall'], true)) {
            $safe['contentWidth'] = $cw;
        }

        $stageWidth = (int) ($settings['stageWidth'] ?? 0);
        if ($stageWidth >= 1 && $stageWidth <= 5000) {
            $safe['stageWidth'] = $stageWidth;
        }

        $anchorId = trim((string) ($settings['anchorId'] ?? ''));
        $anchorId = ltrim($anchorId, '#');
        if ($anchorId !== '') {
            $anchorId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $anchorId) ?: '';
            $anchorId = mb_substr($anchorId, 0, 80);
            if ($anchorId !== '') {
                $safe['anchorId'] = $anchorId;
            }
        }

        return $safe;
    }

    private function sanitizeRichText(string $html): string
    {
        $html = mb_substr($html, 0, 5000);
        $clean = strip_tags($html, '<b><strong><i><em><u><br><span>');
        // Drop any attributes from allowed tags to prevent inline event/style injection.
        $clean = preg_replace('/<(b|strong|i|em|u|span)\s+[^>]*>/i', '<$1>', $clean) ?? $clean;
        return trim($clean);
    }

    private function nextStep($steps, int $currentStepId): ?FunnelStep
    {
        $ordered = collect($steps)->values();
        $index = $ordered->search(fn ($step) => (int) $step->id === (int) $currentStepId);
        if ($index === false) {
            return null;
        }

        return $ordered->get($index + 1);
    }

    private function previewSelectedPricingFromRequest(Request $request, $steps): ?array
    {
        $sourceStepSlug = strtolower(trim((string) $request->query('offer_step', '')));
        $pricingId = trim((string) $request->query('offer_pricing', ''));
        if ($sourceStepSlug === '' || $pricingId === '') {
            return null;
        }

        $sourceStep = collect($steps)->first(function ($candidate) use ($sourceStepSlug) {
            return strtolower(trim((string) ($candidate->slug ?? ''))) === $sourceStepSlug;
        });

        if ($sourceStep instanceof FunnelStep) {
            $selection = $this->previewPricingSelectionFromStep($sourceStep, $pricingId);
            if ($selection !== null) {
                return $selection;
            }
        }

        return $this->previewPricingSelectionSnapshotFromRequest($request, $pricingId, $sourceStepSlug);
    }

    private function previewPricingSelectionFromStep(FunnelStep $sourceStep, string $pricingId): ?array
    {
        $layout = $sourceStep->layout_json;
        if (! is_array($layout)) {
            return null;
        }

        $findInElements = function (array $elements) use (&$findInElements, $pricingId, $sourceStep): ?array {
            foreach ($elements as $element) {
                if (! is_array($element)) {
                    continue;
                }

                if (
                    strtolower(trim((string) ($element['type'] ?? ''))) === 'pricing'
                    && trim((string) ($element['id'] ?? '')) === $pricingId
                ) {
                    $settings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                    $features = [];
                    foreach ((is_array($settings['features'] ?? null) ? $settings['features'] : []) as $feature) {
                        if (! is_scalar($feature)) {
                            continue;
                        }
                        $featureText = mb_substr(trim((string) $feature), 0, 200);
                        if ($featureText !== '') {
                            $features[] = $featureText;
                        }
                    }

                    return [
                        'pricingId' => $pricingId,
                        'sourceStepSlug' => (string) $sourceStep->slug,
                        'plan' => mb_substr(trim((string) ($settings['plan'] ?? '')), 0, 200),
                        'price' => $this->normalizePreviewMoneyDisplay($settings['price'] ?? ''),
                        'regularPrice' => $this->normalizePreviewMoneyDisplay($settings['regularPrice'] ?? ''),
                        'period' => mb_substr(trim((string) ($settings['period'] ?? '')), 0, 60),
                        'subtitle' => mb_substr(trim((string) ($settings['subtitle'] ?? '')), 0, 300),
                        'badge' => mb_substr(trim((string) ($settings['badge'] ?? '')), 0, 80),
                        'features' => $features,
                    ];
                }

                $slides = is_array(data_get($element, 'settings.slides')) ? data_get($element, 'settings.slides') : [];
                foreach ($slides as $slide) {
                    $nested = $findInElements(is_array($slide['elements'] ?? null) ? $slide['elements'] : []);
                    if ($nested !== null) {
                        return $nested;
                    }
                }
            }

            return null;
        };

        $findInSections = function (array $sections) use ($findInElements): ?array {
            foreach ($sections as $section) {
                if (! is_array($section)) {
                    continue;
                }

                $sectionSelection = $findInElements(is_array($section['elements'] ?? null) ? $section['elements'] : []);
                if ($sectionSelection !== null) {
                    return $sectionSelection;
                }

                foreach ((is_array($section['rows'] ?? null) ? $section['rows'] : []) as $row) {
                    foreach ((is_array($row['columns'] ?? null) ? $row['columns'] : []) as $column) {
                        $columnSelection = $findInElements(is_array($column['elements'] ?? null) ? $column['elements'] : []);
                        if ($columnSelection !== null) {
                            return $columnSelection;
                        }
                    }
                }
            }

            return null;
        };

        $rootSelection = $findInSections(is_array($layout['root'] ?? null) ? $layout['root'] : []);
        if ($rootSelection !== null) {
            return $rootSelection;
        }

        return $findInSections(is_array($layout['sections'] ?? null) ? $layout['sections'] : []);
    }

    private function previewPricingSelectionSnapshotFromRequest(Request $request, string $pricingId = '', string $sourceStepSlug = ''): ?array
    {
        $pricingId = trim($pricingId) !== '' ? trim($pricingId) : trim((string) $request->query('offer_pricing', ''));
        $sourceStepSlug = trim($sourceStepSlug) !== '' ? strtolower(trim($sourceStepSlug)) : strtolower(trim((string) $request->query('offer_step', '')));
        $plan = mb_substr(trim((string) $request->query('offer_plan', '')), 0, 200);
        $price = $this->normalizePreviewMoneyDisplay($request->query('offer_price', ''));
        $regularPrice = $this->normalizePreviewMoneyDisplay($request->query('offer_regular_price', ''));
        $period = mb_substr(trim((string) $request->query('offer_period', '')), 0, 60);
        $subtitle = mb_substr(trim((string) $request->query('offer_subtitle', '')), 0, 300);
        $badge = mb_substr(trim((string) $request->query('offer_badge', '')), 0, 80);
        $features = [];
        $rawFeatures = trim((string) $request->query('offer_features', ''));
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
            'features' => $features,
        ];
    }

    private function normalizePreviewMoneyDisplay(mixed $raw): string
    {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^\s*\$/', $value) === 1) {
            $value = preg_replace('/^\s*\$/', '₱', $value) ?? $value;
        }

        return $value;
    }

    private function requiredStepTypesForPurpose(string $purpose): array
    {
        return match (Funnel::normalizePurpose($purpose)) {
            'single_page' => [],
            'digital_product', 'physical_product' => ['sales', 'checkout', 'thank_you'],
            'hybrid' => ['landing', 'sales', 'checkout', 'thank_you'],
            default => ['landing', 'opt_in', 'sales', 'checkout', 'thank_you'],
        };
    }

    private function validatePublishReadiness($steps, string $purpose = 'service'): array
    {
        $ordered = collect($steps)->values();
        $issues = [];
        $normalizedPurpose = Funnel::normalizePurpose($purpose);
        $isSinglePagePurpose = $normalizedPurpose === 'single_page';
        $requiredTypes = $this->requiredStepTypesForPurpose($purpose);
        $activeSlugs = $ordered
            ->map(fn ($step) => strtolower(trim((string) ($step->slug ?? ''))))
            ->filter()
            ->values();

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
            ! $isSinglePagePurpose
            && $lastStep
            && strtolower(trim((string) ($lastStep->type ?? ''))) !== 'thank_you'
        ) {
            $issues[] = 'The last active step must be a Thank You step so the flow resolves safely.';
        }

        foreach ($ordered as $idx => $step) {
            $slug = strtolower(trim((string) ($step->slug ?? '')));
            if ($slug === '') {
                $issues[] = 'Every active step must have a valid slug.';
                continue;
            }
            if ($activeSlugs->filter(fn (string $candidate) => $candidate === $slug)->count() > 1) {
                $issues[] = 'Active step slug "' . $slug . '" is duplicated.';
            }
        }

        foreach ($ordered as $idx => $step) {
            $type = strtolower(trim((string) ($step->type ?? '')));
            $title = trim((string) ($step->title ?? '')) !== '' ? trim((string) $step->title) : ('Step #' . ($idx + 1));
            $hasNext = $ordered->get($idx + 1) !== null;
            $stats = $this->collectStepActionStats(is_array($step->layout_json ?? null) ? $step->layout_json : [], $activeSlugs->all(), $type === 'checkout');

            if (($stats['invalidTargetCount'] ?? 0) > 0) {
                $issues[] = 'Step "' . $title . '" contains button or pricing actions that point to missing step slugs.';
            }

            if ($type === 'opt_in') {
                if (($stats['formCount'] ?? 0) <= 0) {
                    $issues[] = 'Step "' . $title . '" (Opt-in) must include at least one Form component.';
                }
                continue;
            }
            if ($type === 'checkout') {
                if (($stats['checkoutActionCount'] ?? 0) <= 0) {
                    $issues[] = 'Step "' . $title . '" (Checkout) must include at least one Button with action "Checkout submit".';
                }
                $stepAmount = (float) ($step->price ?? 0);
                if ($stepAmount <= 0 && ($stats['pricingAmountCount'] ?? 0) <= 0) {
                    $issues[] = 'Step "' . $title . '" (Checkout) must have a valid amount on the step or in a Pricing component.';
                }
                continue;
            }
            if ($type === 'upsell' || $type === 'downsell') {
                if (($stats['offerAcceptActionCount'] ?? 0) <= 0 || ($stats['offerDeclineActionCount'] ?? 0) <= 0) {
                    $issues[] = 'Step "' . $title . '" (' . ucfirst($type) . ') must include Buttons for both "Accept offer" and "Decline offer".';
                }
                continue;
            }
            if ($type === 'thank_you') {
                continue;
            }
            if (! $hasNext && ! $isSinglePagePurpose) {
                $issues[] = 'Step "' . $title . '" must route to another active step before the Thank You step.';
                continue;
            }
            if (($stats['navigateActionCount'] ?? 0) <= 0 && ! $isSinglePagePurpose) {
                $issues[] = 'Step "' . $title . '" must include at least one Button with action "Next step", "Specific step", or "Custom URL".';
            }
        }

        $ordered->values()->each(function ($step, int $index) use ($ordered, &$issues) {
            $type = strtolower(trim((string) ($step->type ?? '')));
            if ($type !== 'downsell') {
                return;
            }

            $previous = $ordered->get($index - 1);
            if (! $previous || strtolower(trim((string) ($previous->type ?? ''))) !== 'upsell') {
                $issues[] = 'Downsell steps must immediately follow an Upsell step so decline routing stays safe.';
            }
        });

        return array_values(array_unique($issues));
    }

    private function collectStepActionStats(array $layout, array $validStepSlugs = [], bool $isCheckoutStep = false): array
    {
        $stats = [
            'formCount' => 0,
            'navigateActionCount' => 0,
            'checkoutActionCount' => 0,
            'offerAcceptActionCount' => 0,
            'offerDeclineActionCount' => 0,
            'invalidTargetCount' => 0,
            'pricingAmountCount' => 0,
        ];
        $normalizedSlugs = collect($validStepSlugs)
            ->map(fn ($slug) => strtolower(trim((string) $slug)))
            ->filter()
            ->values();

        $visit = function ($node) use (&$visit, &$stats, $normalizedSlugs, $isCheckoutStep): void {
            if (!is_array($node)) {
                return;
            }

            if (isset($node['type']) && is_string($node['type'])) {
                $type = strtolower(trim((string) $node['type']));
                $settings = is_array($node['settings'] ?? null) ? $node['settings'] : [];
                if ($type === 'form') {
                    $stats['formCount']++;
                }
                if ($type === 'button') {
                    $actionType = strtolower(trim((string) ($settings['actionType'] ?? '')));
                    $link = trim((string) ($settings['link'] ?? ''));
                    $stepSlug = trim((string) ($settings['actionStepSlug'] ?? ''));
                    if ($actionType === '') {
                        $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
                    }
                    if ($actionType === 'next_step') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'step' && $stepSlug !== '') {
                        $stats['navigateActionCount']++;
                        if (! $normalizedSlugs->contains(strtolower($stepSlug))) {
                            $stats['invalidTargetCount']++;
                        }
                    } elseif ($actionType === 'link' && $link !== '' && $link !== '#') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'checkout') {
                        $stats['checkoutActionCount']++;
                    } elseif ($actionType === 'offer_accept') {
                        $stats['offerAcceptActionCount']++;
                    } elseif ($actionType === 'offer_decline') {
                        $stats['offerDeclineActionCount']++;
                    }
                }
                if ($type === 'pricing') {
                    $actionType = strtolower(trim((string) ($settings['ctaActionType'] ?? '')));
                    $link = trim((string) ($settings['ctaLink'] ?? ''));
                    $stepSlug = trim((string) ($settings['ctaActionStepSlug'] ?? ''));
                    foreach (['price', 'regularPrice'] as $amountKey) {
                        $amountText = trim((string) ($settings[$amountKey] ?? ''));
                        if ($amountText === '') {
                            continue;
                        }
                        $clean = preg_replace('/[^0-9,.\-]/', '', $amountText);
                        if (is_string($clean) && $clean !== '' && (float) str_replace(',', '', $clean) > 0) {
                            $stats['pricingAmountCount']++;
                            break;
                        }
                    }
                    if ($actionType === '') {
                        $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
                    }
                    if ($actionType === 'next_step') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'step' && $stepSlug !== '') {
                        $stats['navigateActionCount']++;
                        if (! $normalizedSlugs->contains(strtolower($stepSlug))) {
                            $stats['invalidTargetCount']++;
                        }
                    } elseif ($actionType === 'link' && $link !== '' && $link !== '#') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'checkout') {
                        $stats['checkoutActionCount']++;
                    } elseif ($isCheckoutStep) {
                        $stats['checkoutActionCount']++;
                    }
                }
                if ($type === 'product_offer') {
                    $actionType = strtolower(trim((string) ($settings['ctaActionType'] ?? '')));
                    $link = trim((string) ($settings['ctaLink'] ?? ''));
                    $stepSlug = trim((string) ($settings['ctaActionStepSlug'] ?? ''));
                    if ($actionType === '') {
                        $actionType = ($link !== '' && $link !== '#') ? 'link' : 'next_step';
                    }
                    if ($actionType === 'next_step') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'step' && $stepSlug !== '') {
                        $stats['navigateActionCount']++;
                        if (! $normalizedSlugs->contains(strtolower($stepSlug))) {
                            $stats['invalidTargetCount']++;
                        }
                    } elseif ($actionType === 'link' && $link !== '' && $link !== '#') {
                        $stats['navigateActionCount']++;
                    } elseif ($actionType === 'checkout') {
                        $stats['checkoutActionCount']++;
                    } elseif ($actionType === 'offer_accept') {
                        $stats['offerAcceptActionCount']++;
                    } elseif ($actionType === 'offer_decline') {
                        $stats['offerDeclineActionCount']++;
                    }
                }
                if ($type === 'checkout_summary' || $type === 'physical_checkout_summary') {
                    $stats['checkoutActionCount']++;
                }
                if ($type === 'carousel') {
                    $slides = is_array($settings['slides'] ?? null) ? $settings['slides'] : [];
                    foreach ($slides as $slide) {
                        $visit($slide);
                    }
                }
            }

            foreach (['root', 'sections', 'rows', 'columns', 'elements'] as $childrenKey) {
                $children = $node[$childrenKey] ?? null;
                if (!is_array($children)) {
                    continue;
                }
                foreach ($children as $child) {
                    $visit($child);
                }
            }
        };

        $visit($layout);
        return $stats;
    }

    private function generateUniqueFunnelSlug(string $name, int $tenantId): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'funnel';
        $slug = $base;
        $counter = 1;

        while (
            Funnel::where('tenant_id', $tenantId)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function normalizeTagsString(?string $raw): array
    {
        if ($raw === null) {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter(fn ($tag) => $tag !== '')
            ->map(function ($tag) {
                $clean = preg_replace('/[^a-z0-9\-_ ]/i', '', $tag) ?? '';
                return mb_substr(trim($clean), 0, 40);
            })
            ->filter(fn ($tag) => $tag !== '')
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }
}
