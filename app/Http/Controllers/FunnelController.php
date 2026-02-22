<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class FunnelController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $funnels = Funnel::where('tenant_id', $tenantId)->withCount('steps')->latest()->paginate(10);

        return view('funnels.index', compact('funnels'));
    }

    public function create()
    {
        return view('funnels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
        ]);

        $user = auth()->user();

        try {
            $funnel = Funnel::create([
                'tenant_id' => $user->tenant_id,
                'created_by' => $user->id,
                'name' => $validated['name'],
                'slug' => $this->generateUniqueFunnelSlug($validated['name'], $user->tenant_id),
                'description' => $validated['description'] ?? null,
                'status' => 'draft',
            ]);

            // Starter flow: Landing -> Opt-in -> Sales -> Checkout -> Thank You
            $starterSteps = [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Welcome to our funnel.', 'cta_label' => 'Continue'],
                ['title' => 'Opt-in', 'slug' => 'opt-in', 'type' => 'opt_in', 'content' => 'Fill out the form to continue.', 'cta_label' => 'Submit'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present your offer details here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete your order.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank you for your purchase.', 'cta_label' => null],
            ];

            foreach ($starterSteps as $index => $step) {
                FunnelStep::create([
                    'funnel_id' => $funnel->id,
                    'title' => $step['title'],
                    'slug' => $step['slug'],
                    'type' => $step['type'],
                    'content' => $step['content'],
                    'cta_label' => $step['cta_label'] ?? null,
                    'price' => $step['price'] ?? null,
                    'position' => $index + 1,
                    'is_active' => true,
                ]);
            }

            return redirect()->route('funnels.edit', $funnel)->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Added Failed');
        }
    }

    public function edit(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $funnel->load(['steps']);
        $defaultStep = $funnel->steps->sortBy('position')->first();

        return view('funnels.edit', [
            'funnel' => $funnel,
            'stepTypes' => FunnelStep::TYPES,
            'stepLayouts' => FunnelStep::LAYOUTS,
            'stepTemplates' => FunnelStep::TEMPLATES,
            'defaultStepId' => $defaultStep?->id,
        ]);
    }

    public function preview(Funnel $funnel, ?FunnelStep $step = null)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $steps = $funnel->steps()->where('is_active', true)->orderBy('position')->get()->values();
        abort_if($steps->isEmpty(), 404);

        if ($step && (int) $step->funnel_id !== (int) $funnel->id) {
            abort(404);
        }

        $resolvedStep = $step ?: $steps->first();
        abort_if(!$resolvedStep, 404);

        return view('funnels.portal.step', [
            'funnel' => $funnel->load('tenant'),
            'step' => $resolvedStep,
            'nextStep' => $this->nextStep($steps, $resolvedStep->id),
            'isFirstStep' => (int) $steps->first()->id === (int) $resolvedStep->id,
            'isPreview' => true,
        ]);
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
            'layout_json' => 'required|array',
        ]);

        $step = $funnel->steps()->where('id', $validated['step_id'])->firstOrFail();
        $layout = $this->sanitizeLayoutJson($validated['layout_json']);

        $step->update(['layout_json' => $layout]);

        return response()->json([
            'message' => 'Layout saved successfully.',
            'step_id' => $step->id,
            'layout_json' => $layout,
        ]);
    }

    public function uploadBuilderImage(Request $request, Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $validated = $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,mov,avi,wmv,mkv,webm,m4v,3gp,ogv|max:51200',
        ]);

        $path = $validated['image']->store('funnel-builder', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    public function publish(Funnel $funnel)
    {
        $this->ensureTenantFunnelAccess($funnel);

        $hasActiveStep = $funnel->steps()->where('is_active', true)->exists();
        if (!$hasActiveStep) {
            return redirect()->back()->with('error', 'Publishing failed: add at least one active step.');
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

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:2000',
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        try {
            $funnel->update($validated);
            return redirect()->back()->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
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
        ]);

        try {
            $position = (int) $funnel->steps()->max('position') + 1;
            $heroUrl = null;
            if ($request->hasFile('hero_image')) {
                $path = $request->file('hero_image')->store('funnel-heroes', 'public');
                $heroUrl = Storage::url($path);
            }

            $funnel->steps()->create([
                'title' => $validated['title'],
                'subtitle' => $validated['subtitle'] ?? null,
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'template' => $validated['template'] ?? 'simple',
                'template_data' => $validated['template_data'] ?? null,
                'content' => $validated['content'] ?? null,
                'hero_image_url' => $heroUrl,
                'layout_style' => $validated['layout_style'] ?? 'centered',
                'background_color' => $validated['background_color'] ?? null,
                'button_color' => $validated['button_color'] ?? null,
                'cta_label' => $validated['cta_label'] ?? null,
                'price' => $validated['price'] ?? null,
                'position' => $position,
                'is_active' => true,
            ]);

            return redirect()->back()->with('success', 'Added Successfully');
        } catch (\Throwable $e) {
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
                'content' => $validated['content'] ?? null,
                'hero_image_url' => $heroUrl,
                'layout_style' => $validated['layout_style'] ?? 'centered',
                'background_color' => $validated['background_color'] ?? null,
                'button_color' => $validated['button_color'] ?? null,
                'cta_label' => $validated['cta_label'] ?? null,
                'price' => $validated['price'] ?? null,
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);

            return redirect()->back()->with('success', 'Edited Successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Edited Failed');
        }
    }

    public function destroyStep(Funnel $funnel, FunnelStep $step)
    {
        $this->ensureTenantFunnelAccess($funnel);
        if ((int) $step->funnel_id !== (int) $funnel->id) {
            abort(404);
        }

        try {
            $step->delete();
            return redirect()->back()->with('success', 'Deleted Successfully');
        } catch (\Throwable $e) {
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

        return redirect()->back()->with('success', 'Edited Successfully');
    }


    private function ensureTenantFunnelAccess(Funnel $funnel): void
    {
        if ((int) $funnel->tenant_id !== (int) auth()->user()->tenant_id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function sanitizeLayoutJson(array $layout): array
    {
        $sections = collect($layout['sections'] ?? [])
            ->filter(fn ($section) => is_array($section))
            ->take(50)
            ->map(function (array $section) {
                $rows = collect($section['rows'] ?? [])
                    ->filter(fn ($row) => is_array($row))
                    ->take(12)
                    ->map(function (array $row) {
                        $columns = collect($row['columns'] ?? [])
                            ->filter(fn ($column) => is_array($column))
                            ->take(4)
                            ->map(function (array $column) {
                                $elements = collect($column['elements'] ?? [])
                                    ->filter(fn ($element) => is_array($element))
                                    ->take(60)
                                    ->map(function (array $element) {
                                        $type = (string) ($element['type'] ?? 'text');
                                        $type = in_array($type, [
                                            'heading', 'text', 'image', 'button', 'form', 'video', 'countdown', 'spacer',
                                        ], true) ? $type : 'text';

                                        $rawContent = (string) ($element['content'] ?? '');
                                        $content = in_array($type, ['heading', 'text', 'button'], true)
                                            ? $this->sanitizeRichText($rawContent)
                                            : mb_substr(trim($rawContent), 0, 5000);

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
                                    'id' => $this->sanitizeId($column['id'] ?? null, 'col'),
                                    'style' => $this->sanitizeStyle($column['style'] ?? []),
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

                return [
                    'id' => $this->sanitizeId($section['id'] ?? null, 'sec'),
                    'style' => $this->sanitizeStyle($section['style'] ?? []),
                    'rows' => $rows,
                ];
            })
            ->values()
            ->all();

        return ['sections' => $sections];
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
            'color',
            'fontSize',
            'fontWeight',
            'fontFamily',
            'padding',
            'margin',
            'textAlign',
            'borderRadius',
            'border',
            'boxShadow',
            'width',
            'height',
            'backgroundImage',
            'backgroundSize',
            'backgroundPosition',
            'justifyContent',
            'alignItems',
            'gap',
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

        $allowedKeys = [
            'link',
            'src',
            'alt',
            'placeholder',
            'alignment',
            'targetDate',
            'platform',
        ];

        return collect($settings)
            ->only($allowedKeys)
            ->map(function ($value) {
                if (!is_scalar($value) && $value !== null) {
                    return '';
                }
                return mb_substr(trim((string) $value), 0, 1024);
            })
            ->filter(fn ($value) => $value !== '')
            ->all();
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
}
