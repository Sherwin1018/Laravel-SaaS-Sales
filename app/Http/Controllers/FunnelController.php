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
        if (! is_array($rawLayout) || ! isset($rawLayout['sections'])) {
            $rawLayout = ['sections' => []];
        }

        // Decode again from raw body so we never lose form/video data (Laravel input can alter nested JSON)
        $content = $request->getContent();
        if (is_string($content) && $content !== '') {
            $body = json_decode($content, true);
            if (is_array($body) && isset($body['layout_json'])) {
                $fromBody = $body['layout_json'];
                if (is_array($fromBody) && isset($fromBody['sections'])) {
                    $rawLayout = $fromBody;
                }
            }
        }

        $step = $funnel->steps()->where('id', $validated['step_id'])->firstOrFail();
        $layout = $this->sanitizeLayoutJson($rawLayout);
        $this->mergeElementSizeFromRaw($layout, $rawLayout);
        $this->mergeFormElementFromRaw($layout, $rawLayout);

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
        $rawSections = $rawLayout['sections'] ?? [];
        $sections = &$layout['sections'];
        foreach ($sections as $si => $section) {
            $rawRows = $rawSections[$si]['rows'] ?? [];
            foreach (($section['rows'] ?? []) as $ri => $row) {
                $rawCols = $rawRows[$ri]['columns'] ?? [];
                foreach (($row['columns'] ?? []) as $ci => $column) {
                    $rawElements = $rawCols[$ci]['elements'] ?? [];
                    foreach (($column['elements'] ?? []) as $ei => $element) {
                        $rawEl = $rawElements[$ei] ?? [];
                        $rawStyle = is_array($rawEl['style'] ?? null) ? $rawEl['style'] : [];
                        $type = (string) ($rawEl['type'] ?? $element['type'] ?? '');
                        if ($type !== 'video' && $type !== 'image') {
                            continue;
                        }
                        foreach (['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight'] as $key) {
                            if (! isset($rawStyle[$key])) {
                                continue;
                            }
                            $v = trim((string) $rawStyle[$key]);
                            if ($v === '') {
                                continue;
                            }
                            $v = mb_substr($v, 0, 60);
                            $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'] =
                                $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'] ?? [];
                            $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'][$key] = $v;
                        }
                    }
                }
            }
        }
    }

    /**
     * Copy form element style and input settings from raw request so they never get dropped on save.
     * Match by element id so we find the right form even if indices differ.
     */
    private function mergeFormElementFromRaw(array &$layout, array $rawLayout): void
    {
        $rawFormData = [];
        foreach ($rawLayout['sections'] ?? [] as $section) {
            foreach ($section['rows'] ?? [] as $row) {
                foreach ($row['columns'] ?? [] as $column) {
                    foreach ($column['elements'] ?? [] as $el) {
                        if ((string) ($el['type'] ?? '') !== 'form') {
                            continue;
                        }
                        $id = trim((string) ($el['id'] ?? ''));
                        if ($id === '') {
                            continue;
                        }
                        $rawFormData[$id] = [
                            'style' => is_array($el['style'] ?? null) ? $el['style'] : [],
                            'settings' => is_array($el['settings'] ?? null) ? $el['settings'] : [],
                        ];
                    }
                }
            }
        }
        foreach ($layout['sections'] ?? [] as $si => $section) {
            foreach (($section['rows'] ?? []) as $ri => $row) {
                foreach (($row['columns'] ?? []) as $ci => $column) {
                    foreach (($column['elements'] ?? []) as $ei => $element) {
                        if ((string) ($element['type'] ?? '') !== 'form') {
                            continue;
                        }
                        $id = trim((string) ($element['id'] ?? ''));
                        if ($id === '' || ! isset($rawFormData[$id])) {
                            continue;
                        }
                        $raw = $rawFormData[$id];
                        $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'] =
                            $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'] ?? [];
                        $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['settings'] =
                            $layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['settings'] ?? [];
                        $elStyle = &$layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['style'];
                        $elSettings = &$layout['sections'][$si]['rows'][$ri]['columns'][$ci]['elements'][$ei]['settings'];
                        foreach (['width', 'padding', 'margin', 'gap'] as $key) {
                            $v = isset($raw['style'][$key]) ? trim((string) $raw['style'][$key]) : '';
                            if ($v !== '') {
                                $elStyle[$key] = mb_substr($v, 0, 260);
                            }
                        }
                        $rawFormWidth = isset($raw['settings']['width']) ? trim((string) $raw['settings']['width']) : '';
                        if ($rawFormWidth !== '' && empty($elStyle['width'])) {
                            $elStyle['width'] = mb_substr($rawFormWidth, 0, 60);
                        }
                        if ($rawFormWidth !== '') {
                            $elSettings['width'] = mb_substr($rawFormWidth, 0, 60);
                        }
                        foreach (['inputWidth', 'inputPadding', 'inputFontSize'] as $key) {
                            $v = isset($raw['settings'][$key]) ? trim((string) $raw['settings'][$key]) : '';
                            if ($v !== '') {
                                $elSettings[$key] = mb_substr($v, 0, 60);
                            }
                        }
                    }
                }
            }
        }
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

                                        $rawStyle = is_array($element['style'] ?? null) ? $element['style'] : [];
                                        $rawSettings = is_array($element['settings'] ?? null) ? $element['settings'] : [];
                                        $style = $this->sanitizeStyle($rawStyle);
                                        $settings = $this->sanitizeSettings($rawSettings);

                                        if ($type === 'form') {
                                            foreach (['width', 'padding', 'margin', 'gap'] as $k) {
                                                $v = isset($rawStyle[$k]) ? trim((string) $rawStyle[$k]) : '';
                                                if ($v !== '') {
                                                    $style[$k] = mb_substr($v, 0, 260);
                                                }
                                            }
                                            if (empty($style['width'])) {
                                                $rawFormWidth = isset($rawSettings['width']) ? trim((string) $rawSettings['width']) : '';
                                                if ($rawFormWidth !== '') {
                                                    $style['width'] = mb_substr($rawFormWidth, 0, 60);
                                                }
                                            }
                                            foreach (['inputWidth', 'inputPadding', 'inputFontSize'] as $k) {
                                                $v = isset($rawSettings[$k]) ? trim((string) $rawSettings[$k]) : '';
                                                if ($v !== '') {
                                                    $settings[$k] = mb_substr($v, 0, 60);
                                                }
                                            }
                                        }

                                        if ($type === 'video' || $type === 'image') {
                                            foreach (['width', 'height', 'maxWidth', 'minWidth', 'maxHeight', 'minHeight'] as $sizeKey) {
                                                $fromStyle = isset($rawStyle[$sizeKey]) && trim((string) $rawStyle[$sizeKey]) !== '';
                                                $fromSettings = isset($rawSettings[$sizeKey]) && trim((string) $rawSettings[$sizeKey]) !== '';
                                                if ($fromStyle) {
                                                    $style[$sizeKey] = mb_substr(trim((string) $rawStyle[$sizeKey]), 0, 60);
                                                } elseif ($fromSettings && $sizeKey === 'width') {
                                                    $style['width'] = mb_substr(trim((string) $rawSettings[$sizeKey]), 0, 60);
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
            'maxWidth',
            'minWidth',
            'maxHeight',
            'minHeight',
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

        $allowedKeys = [
            'link',
            'src',
            'alt',
            'placeholder',
            'alignment',
            'targetDate',
            'platform',
            'width',
            'widthBehavior',
            'fields',
            'inputWidth',
            'inputPadding',
            'inputFontSize',
        ];

        $allowedFieldTypes = ['first_name', 'last_name', 'email', 'phone_number', 'country', 'city', 'custom'];

        return collect($settings)
            ->only($allowedKeys)
            ->map(function ($value, $key) use ($allowedFieldTypes) {
                if ($key === 'fields') {
                    if (! is_array($value)) {
                        return [];
                    }
                    $out = [];
                    foreach (array_slice($value, 0, 20) as $item) {
                        if (! is_array($item)) {
                            continue;
                        }
                        $t = (string) ($item['type'] ?? 'custom');
                        $t = in_array($t, $allowedFieldTypes, true) ? $t : 'custom';
                        $l = mb_substr(trim((string) ($item['label'] ?? '')), 0, 150);
                        $out[] = ['type' => $t, 'label' => $l];
                    }
                    return $out;
                }
                if (! is_scalar($value) && $value !== null) {
                    return '';
                }
                $v = trim((string) $value);
                if ($key === 'widthBehavior' && ! in_array($v, ['fluid', 'fill'], true)) {
                    return 'fluid';
                }
                $max = in_array($key, ['src', 'link'], true) ? 2048 : (in_array($key, ['width', 'inputWidth', 'inputPadding', 'inputFontSize'], true) ? 60 : 1024);

                return mb_substr($v, 0, $max);
            })
            ->filter(fn ($value) => $value !== '' || is_array($value))
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
