<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelPage;
use Illuminate\Http\Request;

class FunnelPageController extends Controller
{
    public function create(Funnel $funnel)
    {
        $this->authorizeTenant($funnel);
        return view('funnels.pages.create', compact('funnel'));
    }

    public function store(Request $request, Funnel $funnel)
    {
        $this->authorizeTenant($funnel);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:landing,opt-in,sales,checkout'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        $validated['funnel_id'] = $funnel->id;
        $validated['slug'] = FunnelPage::makeSlug($validated['title'], $funnel->id);
        $validated['sort_order'] = $funnel->pages()->max('sort_order') + 1;

        if ($validated['type'] === 'opt-in') {
            $validated['form_fields'] = [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ];
        }

        FunnelPage::create($validated);

        return redirect()->route('funnels.edit', $funnel)->with('success', 'Page added.');
    }

    public function edit(Funnel $funnel, FunnelPage $page)
    {
        $this->authorizeTenant($funnel);
        if ($page->funnel_id !== $funnel->id) {
            abort(404);
        }
        return view('funnels.pages.edit', compact('funnel', 'page'));
    }

    public function update(Request $request, Funnel $funnel, FunnelPage $page)
    {
        $this->authorizeTenant($funnel);
        if ($page->funnel_id !== $funnel->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
        ]);

        if ($page->title !== $validated['title']) {
            $validated['slug'] = FunnelPage::makeSlug($validated['title'], $funnel->id);
        }

        $page->update($validated);

        return redirect()->route('funnels.edit', $funnel)->with('success', 'Page updated.');
    }

    public function destroy(Funnel $funnel, FunnelPage $page)
    {
        $this->authorizeTenant($funnel);
        if ($page->funnel_id !== $funnel->id) {
            abort(404);
        }
        $page->delete();
        return redirect()->route('funnels.edit', $funnel)->with('success', 'Page removed.');
    }

    public function reorder(Request $request, Funnel $funnel)
    {
        $this->authorizeTenant($funnel);

        $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:funnel_pages,id'],
        ]);

        $pageIds = $funnel->pages()->pluck('id')->toArray();
        foreach ($request->order as $position => $id) {
            if (in_array($id, $pageIds, true)) {
                FunnelPage::where('id', $id)->where('funnel_id', $funnel->id)->update(['sort_order' => $position]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('funnels.edit', $funnel)->with('success', 'Page order saved.');
    }

    protected function authorizeTenant(Funnel $funnel): void
    {
        if ($funnel->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized.');
        }
    }
}
