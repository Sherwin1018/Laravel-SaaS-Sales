@extends('layouts.admin')

@section('title', 'Manage Leads')

@section('content')
    <div class="top-header">
        <h1>Manage Leads</h1>
    </div>

    <!-- Only show Add button if allowed -->
    @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
    <div class="actions">
        <a href="{{ route('leads.create') }}" class="btn-create">
            <button><i class="fas fa-plus"></i> Add New Lead</button>
        </a>
    </div>
    @endif

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Leads List</h3>
            <div style="position: relative; width: 100%; max-width: 400px;">
                <input
                    type="text"
                    id="leadsSearchInput"
                    placeholder="Search leads by name, email, phone, status..."
                    style="width: 100%; padding: 10px 40px 10px 15px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#3B82F6';"
                    onblur="this.style.borderColor='#D1D5DB';"
                >
                <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
            </div>
        </div>
        <table id="leadsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
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
                        <td>{{ $lead->phone }}</td>
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
                            <a href="{{ route('leads.edit', $lead->id) }}" style="color: #2563EB; text-decoration: none;">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            @if(auth()->user()->hasRole('account-owner'))
                                <form action="{{ route('leads.destroy', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="empty-state">
                        <td colspan="6" style="text-align: center;">No leads found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        // Live search functionality - filters leads table as you type
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('leadsSearchInput');
            const table = document.getElementById('leadsTable');
            const tbody = table.querySelector('tbody');
            const initialRows = Array.from(tbody.querySelectorAll('tr')).filter((row) => {
                return !row.classList.contains('empty-state');
            });

            let searchTimeout;

            function filterRows(searchTerm) {
                const term = (searchTerm || '').toLowerCase().trim();

                // Remove any previous "no results" row
                const existingNoResults = tbody.querySelector('tr.no-results');
                if (existingNoResults) existingNoResults.remove();

                if (!term) {
                    // Show all rows if search is empty
                    initialRows.forEach((row) => (row.style.display = ''));
                    return;
                }

                let visibleCount = 0;
                initialRows.forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    const match = text.includes(term);
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount += 1;
                });

                if (visibleCount === 0) {
                    const noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results';
                    noResultsRow.innerHTML =
                        '<td colspan="6" style="text-align: center; padding: 20px; color: #6B7280;">No leads found matching "' +
                        searchTerm +
                        '"</td>';
                    tbody.appendChild(noResultsRow);
                }
            }

            searchInput.addEventListener('input', function (e) {
                clearTimeout(searchTimeout);
                const searchTerm = e.target.value;
                searchTimeout = setTimeout(() => filterRows(searchTerm), 250);
            });
        });
    </script>
@endsection
