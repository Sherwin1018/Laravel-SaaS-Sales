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
            'layout_json' => 'required',
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

        $step = $funnel->steps()->where('id', $validated['step_id'])->firstOrFail();
        $layout = $this->sanitizeLayoutJson($rawLayout);
        $this->mergeElementSizeFromRaw($layout, $rawLayout);

        $step->update(['layout_json' => $layout]);

        return response()->json([
            'message' => 'Layout saved successfully.',
            'step_id' => $step->id,
            'layout_json' => $layout,
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

            $path = $validated['image']->store('funnel-builder', 'public');
            $relativeUrl = Storage::url($path);
            $fullUrl = str_starts_with($relativeUrl, 'http') ? $relativeUrl : url($relativeUrl);

            return response()->json([
                'url' => $fullUrl,
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
        $sanitizeElement = function (array $element): array {
            $type = (string) ($element['type'] ?? 'text');
            $type = in_array($type, [
                'heading', 'text', 'image', 'button', 'form', 'video', 'countdown', 'spacer', 'menu', 'carousel',
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

        return ['root' => $root, 'sections' => $sections];
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
            'gap',
            'lineHeight',
            'letterSpacing',
            'textDecorationColor',
            'textDecoration',
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

            // Persist CSS size values (50%, 100%, 400px, etc.) so editor width/height survive refresh
            $sizeKeys = ['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight'];
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

        foreach (['link' => 2048, 'src' => 2048, 'alt' => 1024, 'placeholder' => 1024, 'targetDate' => 120, 'platform' => 120, 'width' => 60] as $k => $maxLen) {
            $v = $readString($k, $maxLen);
            if ($v !== null && $v !== '') {
                $safe[$k] = $v;
            }
        }

        foreach ([
            'alignment' => ['left', 'center', 'right'],
            'widthBehavior' => ['fluid', 'fill'],
            'imageSourceType' => ['direct', 'upload'],
            'videoSourceType' => ['direct', 'upload'],
            'menuAlign' => ['left', 'center', 'right'],
            'vAlign' => ['top', 'center', 'bottom'],
        ] as $k => $allowed) {
            $v = $readEnum($k, $allowed);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }

        foreach (['autoplay', 'controls', 'showArrows', 'imageRadiusLinked', 'videoRadiusLinked'] as $k) {
            $v = $readBool($k);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }

        foreach (['itemGap' => [0, 300], 'activeIndex' => [0, 500], 'activeSlide' => [0, 500], 'carouselActiveRow' => [0, 500], 'carouselActiveCol' => [0, 500], 'fixedWidth' => [50, 2400], 'fixedHeight' => [50, 1600]] as $k => $range) {
            $v = $readInt($k, $range[0], $range[1]);
            if ($v !== null) {
                $safe[$k] = $v;
            }
        }

        foreach (['textColor', 'activeColor', 'controlsColor', 'arrowColor', 'bodyBgColor'] as $k) {
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
                            'phone_number' => 'Phone (09XXXXXXXXX)',
                            'city_municipality' => 'City / Municipality',
                            default => ucwords(str_replace('_', ' ', $type)),
                        };
                    }
                    return ['type' => $type, 'label' => $label];
                })
                ->values()
                ->all();
            if (count($safe['fields']) === 0) {
                $safe['fields'] = [
                    ['type' => 'first_name', 'label' => 'First name'],
                    ['type' => 'last_name', 'label' => 'Last name'],
                    ['type' => 'email', 'label' => 'Email'],
                    ['type' => 'phone_number', 'label' => 'Phone (09XXXXXXXXX)'],
                ];
            }
        }

        if (isset($settings['slides']) && is_array($settings['slides'])) {
            $safe['slides'] = $this->sanitizeCarouselSlides($settings['slides']);
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
                                            'heading', 'text', 'image', 'button', 'form', 'video', 'countdown', 'spacer', 'menu', 'carousel',
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
