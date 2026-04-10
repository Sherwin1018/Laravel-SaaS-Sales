@extends('layouts.admin')

@section('title', 'Team Management')

@section('styles')
        <link rel="stylesheet" href="{{ asset('css/extracted/users-index-style1.css') }}">
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

    @include('partials.plan-usage-summary', [
        'planUsage' => $planUsage ?? [],
        'resourceKey' => 'users',
        'title' => 'Team Member Limit',
    ])

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px;">
            <h3 style="margin: 0;">Team Members</h3>
            <button type="button" id="toggleTeamMembersBtn"
                style="padding: 10px 16px; background: var(--theme-primary, #240E35); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; min-width: 88px;"
                aria-expanded="false">
                Show
            </button>
        </div>
        <div id="teamMembersContent" style="display: none;">
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
    </div>

    <script>
        function resendVerification(userId, userName) {
            if (confirm('Send verification email to ' + userName + '?')) {
                fetch(`/users/${userId}/resend-verification`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showMessage(data.message, 'success');
                    } else {
                        // Show error message
                        showMessage(data.message, 'warning');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error sending verification email', 'error');
                });
            }
        }

        function showMessage(message, type) {
            // Remove any existing toasts
            const existingToast = document.getElementById('statusToastContainer');
            if (existingToast) {
                existingToast.remove();
            }

            // Create toast container
            const toastContainer = document.createElement('div');
            toastContainer.id = 'statusToastContainer';
            toastContainer.style.cssText = 'position:fixed;top:18px;right:18px;z-index:9999;';
            
            // Create toast
            const toast = document.createElement('div');
            toast.style.cssText = 'display:flex;gap:12px;align-items:center;background:#fff;border-radius:14px;width:min(90vw,380px);padding:12px 38px 12px 12px;border:1px solid #E6E1EF;box-shadow:0 18px 38px rgba(15,23,42,.2);position:relative;';
            
            const icon = document.createElement('i');
            icon.className = type === 'success' ? 'fas fa-check' : type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-times';
            icon.style.cssText = `font-size:24px;color:${type === 'success' ? '#65A30D' : type === 'warning' ? '#F59E0B' : '#B91C1C'};`;
            
            const content = document.createElement('div');
            content.innerHTML = `
                <h4 style="margin:0 0 6px 0;font-size:15px;font-weight:800;color:${type === 'success' ? '#65A30D' : type === 'warning' ? '#F59E0B' : '#B91C1C'};">${type === 'success' ? 'Success!' : type === 'warning' ? 'Notice!' : 'Error!'}</h4>
                <p style="margin:0;color:#334155;font-size:14px;">${message}</p>
            `;
            
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '<i class="fas fa-times-circle"></i>';
            closeBtn.style.cssText = 'position:absolute;top:8px;right:8px;border:none;background:transparent;cursor:pointer;';
            closeBtn.onclick = () => toastContainer.remove();
            
            toast.appendChild(icon);
            toast.appendChild(content);
            toast.appendChild(closeBtn);
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            // Auto-remove after 4 seconds
            setTimeout(() => {
                if (toastContainer.parentNode) {
                    toastContainer.remove();
                }
            }, 4000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            const toggleTeamMembersBtn = document.getElementById('toggleTeamMembersBtn');
            const teamMembersContent = document.getElementById('teamMembersContent');

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

            if (toggleTeamMembersBtn && teamMembersContent) {
                toggleTeamMembersBtn.addEventListener('click', function() {
                    const isHidden = teamMembersContent.style.display === 'none';
                    teamMembersContent.style.display = isHidden ? 'block' : 'none';
                    toggleTeamMembersBtn.textContent = isHidden ? 'Hide' : 'Show';
                    toggleTeamMembersBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });
            }
        });
    </script>
@endsection

