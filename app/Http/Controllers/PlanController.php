<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $query = Plan::query()->orderBy('sort_order')->orderBy('id');

        if ($request->filled('search')) {
            $search = (string) $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $plans = $query->paginate(10);

        if ($request->ajax()) {
            return view('admin.plans._rows', compact('plans'))->render();
        }

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        $validated['features'] = $this->normalizeFeatures($validated['features']);

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request, $plan->id);
        $validated['features'] = $this->normalizeFeatures($validated['features']);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePlan(Request $request, ?int $planId = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:50|alpha_dash|unique:plans,code,'.($planId ?? 'NULL').',id',
            'name' => 'required|string|max:120',
            'price' => 'required|numeric|min:0',
            'period' => 'required|string|max:60',
            'summary' => 'required|string',
            'features' => 'required|string',
            'spotlight' => 'nullable|string|max:120',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeFeatures(string $features): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $features) ?: [])
            ->map(fn ($feature) => trim($feature))
            ->filter()
            ->values()
            ->all();
    }
}
