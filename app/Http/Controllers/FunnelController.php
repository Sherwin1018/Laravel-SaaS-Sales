<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelStep;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FunnelController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $funnels = Funnel::where('tenant_id', $tenantId)->withCount('steps')->latest()->paginate(12);

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

        return view('funnels.edit', [
            'funnel' => $funnel->load('steps'),
            'stepTypes' => FunnelStep::TYPES,
        ]);
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
            'slug' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('funnel_steps', 'slug')->where(fn ($q) => $q->where('funnel_id', $funnel->id)),
            ],
            'type' => ['required', Rule::in(array_keys(FunnelStep::TYPES))],
            'content' => 'nullable|string|max:6000',
            'cta_label' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0.01',
        ]);

        try {
            $position = (int) $funnel->steps()->max('position') + 1;

            $funnel->steps()->create([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'content' => $validated['content'] ?? null,
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
            'content' => 'nullable|string|max:6000',
            'cta_label' => 'nullable|string|max:120',
            'price' => 'nullable|numeric|min:0.01',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $step->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'],
                'type' => $validated['type'],
                'content' => $validated['content'] ?? null,
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
