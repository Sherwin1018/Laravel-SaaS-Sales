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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Leads List</h3>
            <div style="position: relative; width: 100%; max-width: 400px;">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Search leads by name, email, tenant, status..." 
                    value="{{ request('search') }}"
                    maxlength="40"
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

    <script>
        // Live search functionality - filters table as you type
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const table = document.getElementById('leadsTable');
            const tbody = table.querySelector('tbody');
            const rows = tbody.querySelectorAll('tr');
            let searchTimeout;

            // Function to filter rows
            function filterRows(searchTerm) {
                const term = searchTerm.toLowerCase().trim();
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(term)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show "No results" message if all rows are hidden
                const visibleRows = Array.from(rows).filter(row => row.style.display !== 'none');
                let noResultsRow = tbody.querySelector('tr.no-results');
                
                if (term && visibleRows.length === 0) {
                    if (!noResultsRow) {
                        noResultsRow = document.createElement('tr');
                        noResultsRow.className = 'no-results';
                        noResultsRow.innerHTML = '<td colspan="6" style="text-align: center; padding: 20px; color: #6B7280;">No leads found matching "' + searchTerm + '"</td>';
                        tbody.appendChild(noResultsRow);
                    }
                } else {
                    if (noResultsRow) {
                        noResultsRow.remove();
                    }
                }
            }

            // Debounced search - waits 300ms after user stops typing
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const searchTerm = e.target.value;
                
                searchTimeout = setTimeout(() => {
                    filterRows(searchTerm);
                }, 300);
            });

            // Initial filter if there's a search term in URL
            if (searchInput.value) {
                filterRows(searchInput.value);
            }
        });
    </script>
@endsection
