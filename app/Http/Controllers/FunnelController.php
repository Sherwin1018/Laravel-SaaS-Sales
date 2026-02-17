<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FunnelController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Funnel::where('tenant_id', $tenantId)
            ->withCount('pages')
            ->withCount('leads')
            ->withCount(['leads as closed_won_count' => fn ($q) => $q->where('status', 'closed_won')]);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status') && in_array($request->status, ['draft', 'published'], true)) {
            if ($request->status === 'draft') {
                $query->where('is_active', false);
            } else {
                $query->where('is_active', true);
            }
        }

        $funnels = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('funnels.index', compact('funnels'));
    }

    public function create()
    {
        return view('funnels.create');
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['slug'] = Funnel::makeSlug($validated['name'], $tenantId);
        $validated['is_active'] = $request->boolean('is_active');

        Funnel::create($validated);

        return redirect()->route('funnels.index')->with('success', 'Funnel created.');
    }

    public function show(Funnel $funnel)
    {
        $this->authorizeTenant($funnel);
        $funnel->load('pages');
        return view('funnels.show', compact('funnel'));
    }

    public function edit(Funnel $funnel)
    {
        $this->authorizeTenant($funnel);
        $funnel->load('pages');
        return view('funnels.edit', compact('funnel'));
    }

    public function update(Request $request, Funnel $funnel)
    {
        $this->authorizeTenant($funnel);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($funnel->name !== $validated['name']) {
            $validated['slug'] = Funnel::makeSlug($validated['name'], $funnel->tenant_id);
        }

        $funnel->update($validated);

        return redirect()->route('funnels.index')->with('success', 'Funnel updated.');
    }

    public function destroy(Funnel $funnel)
    {
        $this->authorizeTenant($funnel);
        $funnel->delete();
        return redirect()->route('funnels.index')->with('success', 'Funnel deleted.');
    }

    public function duplicate(Funnel $funnel)
    {
        $this->authorizeTenant($funnel);

        $newFunnel = $funnel->replicate();
        $newFunnel->name = $funnel->name . ' (Copy)';
        $newFunnel->slug = Funnel::makeSlug($newFunnel->name, $funnel->tenant_id);
        $newFunnel->save();

        foreach ($funnel->pages()->orderBy('sort_order')->get() as $page) {
            $newPage = $page->replicate();
            $newPage->funnel_id = $newFunnel->id;
            $newPage->slug = FunnelPage::makeSlug($page->title, $newFunnel->id);
            $newPage->sort_order = $page->sort_order;
            $newPage->save();
        }

        return redirect()->route('funnels.index')->with('success', 'Funnel duplicated.');
    }

    protected function authorizeTenant(Funnel $funnel): void
    {
        if ($funnel->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
