<?php

namespace App\Http\Controllers;

use App\Models\Funnel;
use App\Models\FunnelPage;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadCaptureController extends Controller
{
    /**
     * Public form submission: capture lead from a funnel page (opt-in, etc.).
     * No auth required. Tenant is determined by funnel.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'funnel_slug' => ['required', 'string'],
            'page_slug' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ]);

        $funnel = Funnel::where('slug', $validated['funnel_slug'])->where('is_active', true)->firstOrFail();
        $page = FunnelPage::where('funnel_id', $funnel->id)->where('slug', $validated['page_slug'])->firstOrFail();

        $lead = Lead::withoutGlobalScope('tenant')->create([
            'tenant_id' => $funnel->tenant_id,
            'source_funnel_id' => $funnel->id,
            'source_funnel_page_id' => $page->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'new',
            'score' => 0,
        ]);

        if (!empty($validated['tag_ids'])) {
            $validTagIds = \App\Models\Tag::where('tenant_id', $funnel->tenant_id)
                ->whereIn('id', $validated['tag_ids'])
                ->pluck('id');
            $lead->tags()->sync($validTagIds);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Thank you!']);
        }

        return redirect()->route('funnels.public.page', [$funnel->slug, $page->slug])
            ->with('success', 'Thank you! We have received your information.');
    }

    /**
     * Show a public funnel page (for embedding or redirect).
     */
    public function showPage(string $funnelSlug, string $pageSlug)
    {
        $funnel = Funnel::where('slug', $funnelSlug)->where('is_active', true)->firstOrFail();
        $page = FunnelPage::where('funnel_id', $funnel->id)->where('slug', $pageSlug)->firstOrFail();

        return view('funnels.public-page', [
            'funnel' => $funnel,
            'page' => $page,
        ]);
    }
}
