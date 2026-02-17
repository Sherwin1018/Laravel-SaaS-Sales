@extends('layouts.admin')

@section('title', 'Team Management')

@section('content')
    <div class="top-header">
        <h1>Team Management</h1>
    </div>

    <div class="actions">
        <a href="{{ route('users.create') }}" class="btn-create">
            <button><i class="fas fa-plus"></i> Add Team Member</button>
        </a>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Team Members</h3>
            <div style="position: relative; width: 100%; max-width: 400px;">
                <input
                    type="text"
                    id="teamSearchInput"
                    placeholder="Search team by name, email, role..."
                    maxlength="40"
                    style="width: 100%; padding: 10px 40px 10px 15px; border: 1px solid #D1D5DB; border-radius: 6px; font-size: 14px; outline: none; transition: border-color 0.2s;"
                    onfocus="this.style.borderColor='#3B82F6';"
                    onblur="this.style.borderColor='#D1D5DB';"
                >
                <i class="fas fa-search" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #9CA3AF;"></i>
            </div>
        </div>
        <table id="teamTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span style="background-color: #EFF6FF; color: #1E40AF; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-right: 4px;">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                        <td>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            @else
                                <span style="color: #9CA3AF; font-size: 12px;">(You)</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="empty-state">
                        <td colspan="5" style="text-align: center;">No team members found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <script>
        // Live search for team members table
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('teamSearchInput');
            const table = document.getElementById('teamTable');
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
                        '<td colspan="5" style="text-align: center; padding: 20px; color: #6B7280;">No team members found matching \"' +
                        searchTerm +
                        '\"</td>';
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
