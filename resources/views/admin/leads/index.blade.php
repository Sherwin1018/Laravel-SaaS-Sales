@extends('layouts.admin')

@section('title', 'All Leads')

@section('content')
    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div></div> <!-- Placeholder for layout consistency -->
        
        <!-- Live Search Input -->
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

    <div class="card">
        <h3>Leads List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Tenant</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('admin.leads._rows', ['leads' => $leads])
            </tbody>
        </table>

         <div style="margin-top: 20px;" id="paginationLinks">
            {{ $leads->links('pagination::bootstrap-4') }}
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
                    fetch(`{{ route('admin.leads.index') }}?search=${encodeURIComponent(query)}`, {
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
