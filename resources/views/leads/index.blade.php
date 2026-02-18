@extends('layouts.admin')

@section('title', 'Manage Leads')

@section('content')
    <div class="top-header">
        <h1>Manage Leads</h1>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
            <a href="{{ route('leads.create') }}" class="btn-create">
                <button><i class="fas fa-plus"></i> Add New Lead</button>
            </a>
        @else
            <div></div>
        @endif

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search leads..."
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
        </div>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="overflow-x: auto; margin-bottom: 20px;">
        <h3>Lead Pipeline</h3>
        <div style="display: grid; grid-template-columns: repeat(5, minmax(190px, 1fr)); gap: 12px;">
            @foreach($pipelineStatuses as $status => $label)
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px;">
                    <h4 style="margin: 0 0 8px; font-size: 13px; color: #1E3A8A;">
                        {{ $label }} ({{ $pipelineLeads[$status]->count() }})
                    </h4>
                    @forelse($pipelineLeads[$status] as $pipelineLead)
                        <div style="padding: 8px; border-radius: 6px; border: 1px solid #E5E7EB; background: white; margin-bottom: 8px;">
                            <strong style="display: block; font-size: 13px;">{{ $pipelineLead->name }}</strong>
                            <small style="color: #64748B;">{{ $pipelineLead->assignedAgent->name ?? 'Unassigned' }}</small>
                        </div>
                    @empty
                        <p style="font-size: 12px; color: #94A3B8; margin: 0;">No leads</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Leads List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('leads._rows', ['leads' => $leads])
            </tbody>
        </table>

        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $leads->links('pagination::bootstrap-4') }}
        </div>
    </div>

    @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
        <div class="card" style="max-width: 500px;">
            <h3>Quick Assignment</h3>
            <form method="POST" id="quickAssignForm">
                @csrf
                <div style="margin-bottom: 10px;">
                    <label for="leadSelect">Lead</label>
                    <select id="leadSelect" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        @foreach($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->assignedAgent->name ?? 'Unassigned' }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom: 10px;">
                    <label for="agentSelect">Sales Agent</label>
                    <select id="agentSelect" name="assigned_to" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                        <option value="">Unassigned</option>
                        @foreach($assignableAgents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    style="padding: 8px 16px; background-color: #0EA5E9; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Save Assignment
                </button>
            </form>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            const quickAssignForm = document.getElementById('quickAssignForm');
            const leadSelect = document.getElementById('leadSelect');

            let timeout = null;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                const query = searchInput.value;
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    fetch(`{{ route('leads.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(response => response.text())
                        .then(html => {
                            tableBody.innerHTML = html;
                            if (query.length > 0) {
                                paginationLinks.style.display = 'none';
                            } else {
                                paginationLinks.style.display = 'block';
                                if (query === '') window.location.reload();
                            }
                        })
                        .catch(error => console.error('Search error:', error));
                }, 300);
            });

            if (quickAssignForm && leadSelect) {
                quickAssignForm.addEventListener('submit', function(event) {
                    event.preventDefault();
                    quickAssignForm.action = `/leads/${leadSelect.value}/assign`;
                    quickAssignForm.submit();
                });
            }
        });
    </script>
@endsection
