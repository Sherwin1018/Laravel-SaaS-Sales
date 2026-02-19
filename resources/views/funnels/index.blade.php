@extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('content')
    <div class="top-header">
        <h1>Funnel Builder</h1>
    </div>

    <div class="actions" style="justify-content: space-between; align-items: center;">
        <a href="{{ route('funnels.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Funnel</a>
        <p style="font-size: 12px; font-weight: 700; color: #475569;">Build landing -> opt-in -> sales -> checkout -> upsell/downsell flows.</p>
    </div>

    <div class="card">
        <h3>Funnels</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Steps</th>
                    <th>Public URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funnels as $funnel)
                    <tr>
                        <td>{{ $funnel->name }}</td>
                        <td>{{ ucfirst($funnel->status) }}</td>
                        <td>{{ $funnel->steps_count }}</td>
                        <td>
                            @if($funnel->status === 'published')
                                <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}" target="_blank">
                                    {{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}
                                </a>
                            @else
                                <span style="color: #64748B;">Publish to enable</span>
                            @endif
                        </td>
                        <td style="display:flex; gap: 10px;">
                            <a href="{{ route('funnels.edit', $funnel) }}" style="color:#2563EB; text-decoration:none; font-weight:700;">
                                <i class="fas fa-pen"></i> Builder
                            </a>
                            <form method="POST" action="{{ route('funnels.destroy', $funnel) }}" onsubmit="return confirm('Delete this funnel?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;font-weight:700;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">No funnels found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 18px;">
            {{ $funnels->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
