@extends('layouts.admin')

@section('title', 'Manage Tenants')

@section('styles')
        <link rel="stylesheet" href="{{ asset('css/extracted/admin-tenants-index-style1.css') }}">
@endsection

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
        <a href="{{ route('admin.tenants.create') }}" class="btn-create"><i class="fas fa-plus"></i> Add New Tenant</a>
        
        <!-- Live Search Input -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search tenants..." 
                   style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; width: 300px;">
        </div>
    </div>

    <div class="card">
        <h3>Tenant List</h3>
        <div class="sa-table-scroll">
        <table class="sa-table">
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
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $tenants->links('pagination::bootstrap-4') }} 
        </div>
    </div>

    <div id="deleteTenantModal" class="modal-overlay" style="display: none;">
        <div class="modal-box delete-tenant-modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteTenantModalTitle">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px;">
                <h3 id="deleteTenantModalTitle" style="margin:0;">Confirm Tenant Deletion</h3>
                <button type="button" id="closeDeleteTenantModal" class="modal-close-btn">&times;</button>
            </div>
            <p style="margin: 0 0 18px; color: var(--theme-muted, #6B7280); line-height: 1.6;">
                Are you sure you want to delete <strong id="deleteTenantName">this tenant</strong>?
            </p>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="cancelDeleteTenantBtn" class="btn-create" style="background:#64748B;">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteTenantBtn" class="btn-create" style="background:#DC2626;">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            const deleteTenantModal = document.getElementById('deleteTenantModal');
            const deleteTenantName = document.getElementById('deleteTenantName');
            const closeDeleteTenantModal = document.getElementById('closeDeleteTenantModal');
            const cancelDeleteTenantBtn = document.getElementById('cancelDeleteTenantBtn');
            const confirmDeleteTenantBtn = document.getElementById('confirmDeleteTenantBtn');

            let timeout = null;
            let pendingDeleteForm = null;

            const closeTenantDeleteModal = () => {
                if (!deleteTenantModal) return;
                deleteTenantModal.style.display = 'none';
                pendingDeleteForm = null;
            };

            const openTenantDeleteModal = (form) => {
                if (!deleteTenantModal) return;
                pendingDeleteForm = form;
                const tenantName = form.getAttribute('data-tenant-name') || 'this tenant';
                if (deleteTenantName) {
                    deleteTenantName.textContent = tenantName;
                }
                deleteTenantModal.style.display = 'flex';
            };

            document.addEventListener('submit', function(event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.matches('form[data-delete-tenant-form]')) {
                    return;
                }

                event.preventDefault();
                openTenantDeleteModal(form);
            });

            if (closeDeleteTenantModal) {
                closeDeleteTenantModal.addEventListener('click', closeTenantDeleteModal);
            }
            if (cancelDeleteTenantBtn) {
                cancelDeleteTenantBtn.addEventListener('click', closeTenantDeleteModal);
            }
            if (confirmDeleteTenantBtn) {
                confirmDeleteTenantBtn.addEventListener('click', function() {
                    if (pendingDeleteForm) {
                        pendingDeleteForm.submit();
                    }
                });
            }
            if (deleteTenantModal) {
                deleteTenantModal.addEventListener('click', function(event) {
                    if (event.target === deleteTenantModal) {
                        closeTenantDeleteModal();
                    }
                });
            }
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && deleteTenantModal && deleteTenantModal.style.display === 'flex') {
                    closeTenantDeleteModal();
                }
            });

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

