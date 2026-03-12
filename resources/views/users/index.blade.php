@extends('layouts.admin')

@section('title', 'Team Management')

@section('styles')
    <style>
        .team-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .team-table {
            min-width: 760px;
        }
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Team Management</h1>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('users.create') }}" class="btn-create"><i class="fas fa-plus"></i> Add Team Member</a>

        <!-- Live Search Input -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search team members..." 
                   style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; width: 300px;">
        </div>
    </div>

    <div class="card">
        <h3>Team Members</h3>
        <div class="team-table-scroll">
        <table class="team-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('users._rows', ['users' => $users])
            </tbody>
        </table>
        </div>
        
        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');

            let timeout = null;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                const query = searchInput.value;
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    fetch(`{{ route('users.index') }}?search=${encodeURIComponent(query)}`, {
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
        });
    </script>
@endsection
