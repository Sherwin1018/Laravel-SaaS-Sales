@extends('layouts.admin')

@section('title', 'Funnels')

@section('content')
    <div class="top-header">
        <h1>Funnels</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">Build and manage sales funnels</p>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('funnels.create') }}" class="btn-create">
            <button type="button"><i class="fas fa-plus"></i> Create Funnel</button>
        </a>
        <form method="GET" action="{{ route('funnels.index') }}" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search funnels..."
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 260px;">
            <select name="status" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
                <option value="">Status: All</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            <button type="submit" style="padding: 8px 16px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="margin-bottom: 20px;">
        <h3>Your Funnels</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;"></th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Leads</th>
                    <th>Conversion</th>
                    <th>Last updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funnels as $funnel)
                    @php
                        $conversion = $funnel->leads_count > 0
                            ? round(($funnel->closed_won_count / $funnel->leads_count) * 100, 1)
                            : 0;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #3B82F6;"><i class="fas fa-filter" style="font-size: 1.1rem;"></i></td>
                        <td>{{ $funnel->name }}</td>
                        <td><span style="font-size: 13px; color: #6b7280;">Full funnel</span></td>
                        <td>
                            @if($funnel->is_active)
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: #d1fae5; color: #065f46;">Published</span>
                            @else
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background-color: #f3f4f6; color: #6b7280;">Draft</span>
                            @endif
                        </td>
                        <td>{{ $funnel->leads_count }}</td>
                        <td>{{ $conversion }}%</td>
                        <td style="font-size: 13px; color: #6b7280;">{{ $funnel->updated_at->format('M j, Y') }}</td>
                        <td style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <a href="{{ route('funnels.edit', $funnel) }}" style="color: #2563EB; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                            <form action="{{ route('funnels.duplicate', $funnel) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: #2563EB; cursor: pointer; padding: 0; font-size: inherit;"><i class="fas fa-copy"></i> Duplicate</button>
                            </form>
                            <a href="{{ route('funnels.show', $funnel) }}" style="color: #2563EB; text-decoration: none;"><i class="fas fa-eye"></i> View</a>
                            <form action="{{ route('funnels.destroy', $funnel) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this funnel and all its pages?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px 20px; color: #6b7280;">
                            <p style="margin: 0 0 12px 0; font-size: 15px;">Create your first funnel.</p>
                            <a href="{{ route('funnels.create') }}" style="display: inline-block; padding: 8px 16px; background-color: #2563EB; color: white; text-decoration: none; border-radius: 6px;">Create Funnel</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 20px;">{{ $funnels->links('pagination::bootstrap-4') }}</div>
    </div>
@endsection
