@extends('layouts.admin')

@section('title', 'Manage Plans')

@section('styles')
    <style>
        .sa-table-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .sa-table { min-width: 980px; }
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Manage Pricing Plans</h1>
    </div>

    <div class="actions" style="display:flex;justify-content:space-between;align-items:center;">
        <a href="{{ route('admin.plans.create') }}" class="btn-create"><i class="fas fa-plus"></i> Add New Plan</a>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search plans..."
                style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; width: 300px;">
        </div>
    </div>

    <div class="card">
        <h3>Pricing Plans</h3>
        <div class="sa-table-scroll">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Period</th>
                        <th>Spotlight</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('admin.plans._rows', ['plans' => $plans])
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $plans->links('pagination::bootstrap-4') }}
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
                    fetch(`{{ route('admin.plans.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                        if (query.length > 0) {
                            paginationLinks.style.display = 'none';
                        } else {
                            paginationLinks.style.display = 'block';
                            if (query === '') {
                                window.location.reload();
                            }
                        }
                    });
                }, 300);
            });
        });
    </script>
@endsection
