@extends('layouts.admin')

@section('title', 'All Leads')

@section('content')
    <div class="top-header">
        <h1>All Leads</h1>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <h3>Leads List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Tenant</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leads as $lead)
                    <tr>
                        <td>{{ $lead->name }}</td>
                        <td>{{ $lead->email }}</td>
                        <td>
                            @if($lead->tenant)
                                <span style="background-color: #F3F4F6; color: #374151; padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                                    {{ $lead->tenant->company_name }}
                                </span>
                            @else
                                <span style="color: #9CA3AF; font-size: 12px;">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; 
                                @if($lead->status == 'new') background-color: #DBEAFE; color: #1E40AF;
                                @elseif($lead->status == 'contacted') background-color: #FEF3C7; color: #92400E;
                                @elseif($lead->status == 'qualified') background-color: #D1FAE5; color: #065F46;
                                @elseif($lead->status == 'lost') background-color: #FEE2E2; color: #991B1B;
                                @endif">
                                {{ ucfirst($lead->status) }}
                            </span>
                        </td>
                        <td>{{ $lead->score }}</td>
                        <td style="display: flex; gap: 10px;">
                             <!-- Super Admin can delete, but maybe not edit heavily? keeping delete for now -->
                             <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No leads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

         <div style="margin-top: 20px;">
            {{ $leads->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
