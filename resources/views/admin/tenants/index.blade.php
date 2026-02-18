@extends('layouts.admin')

@section('title', 'Manage Tenants')

@section('content')
    <div class="top-header">
        <h1>Manage Tenants</h1>
        <div class="header-right">
            <div class="notification-bell">
                <i class="fas fa-bell"></i>
                <span class="badge">3</span>
            </div>
        </div>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('admin.tenants.create') }}" class="btn-create">
            <button><i class="fas fa-plus"></i> Add New Tenant</button>
        </a>
        
        <!-- Live Search Input -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search tenants..." 
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
        </div>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <h3>Tenant List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Subscription Plan</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('admin.tenants._rows', ['tenants' => $tenants])
            </tbody>
        </table>

        <!-- Pagination -->
        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $tenants->links('pagination::bootstrap-4') }} 
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
                
                // Only search if 2+ characters or cleared
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    fetch(`{{ route('admin.tenants.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                        
                        // Hide pagination when searching to avoid confusion with static links
                        if (query.length > 0) {
                            paginationLinks.style.display = 'none';
                        } else {
                            paginationLinks.style.display = 'block';
                            // If cleared, we might want to reload to restore full pagination state
                            if (query === '') {
                                window.location.reload();
                            }
                        }
                    })
                    .catch(error => console.error('Search error:', error));
                }, 300);
            });
        });
    </script>
@endsection
