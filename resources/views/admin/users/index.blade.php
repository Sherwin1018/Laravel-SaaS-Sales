@extends('layouts.admin')

@section('title', 'All System Users')

@section('content')
    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        <div></div> <!-- Placeholder for layout consistency -->
        
        <!-- Live Search Input -->
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search users..." 
                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
        </div>
    </div>

    <div class="card">
        <h3>Users List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Tenant</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('admin.users._rows', ['users' => $users])
            </tbody>
        </table>
        
        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>

    {{-- Modal: Suspend / Activate Account Owner --}}
    <div id="statusModal" class="modal-overlay" style="display: none;">
        <div class="modal-box status-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 id="statusModalTitle" style="margin: 0;">Suspend Account</h3>
                <button type="button" id="closeStatusModal" class="modal-close-btn">&times;</button>
            </div>
            <form id="statusModalForm" method="POST" action="">
                @csrf
                @method('PATCH')
                <input type="hidden" name="suspension_reason" id="suspensionReasonHidden" value="">
                <div id="statusModalReasonWrap" style="margin-bottom: 16px;">
                    <label for="modalSuspensionReason" style="display: block; margin-bottom: 6px; font-weight: 600;">Reason</label>
                    <select id="modalSuspensionReason" style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                        <option value="">Select reason...</option>
                        <option value="Policy violation">Policy violation</option>
                        <option value="Payment issue">Payment issue</option>
                        <option value="Requested by user">Requested by user</option>
                        <option value="Inactive account">Inactive account</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="statusModalReasonOtherWrap" style="margin-top: 12px; display: none;">
                        <label for="modalSuspensionReasonOther" style="display: block; margin-bottom: 6px; font-weight: 600;">Please specify</label>
                        <input type="text" id="modalSuspensionReasonOther" placeholder="Enter reason..." maxlength="255"
                            style="width: 100%; padding: 10px; border: 1px solid #E2E8F0; border-radius: 6px;">
                    </div>
                </div>
                <p id="statusModalConfirmText" style="display: none; margin-bottom: 16px; color: #475569;"></p>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" id="statusModalSubmit" style="padding: 8px 16px; background-color: #B91C1C; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Suspend</button>
                    <button type="button" id="statusModalCancel" style="padding: 8px 16px; background-color: #E2E8F0; color: #475569; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .status-modal-box { width: 100%; max-width: 520px; }
        .modal-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #64748B; line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: #1E293B; }
    </style>

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
                    fetch(`{{ route('admin.users.index') }}?search=${encodeURIComponent(query)}`, {
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

            var statusModal = document.getElementById('statusModal');
            var statusModalForm = document.getElementById('statusModalForm');
            var statusModalTitle = document.getElementById('statusModalTitle');
            var statusModalReasonWrap = document.getElementById('statusModalReasonWrap');
            var statusModalConfirmText = document.getElementById('statusModalConfirmText');
            var statusModalSubmit = document.getElementById('statusModalSubmit');
            var modalSuspensionReason = document.getElementById('modalSuspensionReason');
            var reasonOtherWrap = document.getElementById('statusModalReasonOtherWrap');
            var modalSuspensionReasonOther = document.getElementById('modalSuspensionReasonOther');
            var suspensionReasonHidden = document.getElementById('suspensionReasonHidden');
            var closeBtn = document.getElementById('closeStatusModal');
            var cancelBtn = document.getElementById('statusModalCancel');
            var statusModalBaseUrl = '{{ url("admin/users") }}';

            if (modalSuspensionReason) {
                modalSuspensionReason.addEventListener('change', function() {
                    if (reasonOtherWrap) reasonOtherWrap.style.display = this.value === 'Other' ? 'block' : 'none';
                    if (modalSuspensionReasonOther) modalSuspensionReasonOther.value = '';
                });
            }

            function openStatusModal(userId, isActive) {
                statusModalForm.action = statusModalBaseUrl + '/' + userId + '/status';
                if (modalSuspensionReason) { modalSuspensionReason.value = ''; }
                if (modalSuspensionReasonOther) modalSuspensionReasonOther.value = '';
                if (suspensionReasonHidden) suspensionReasonHidden.value = '';
                if (reasonOtherWrap) reasonOtherWrap.style.display = 'none';
                if (isActive) {
                    statusModalTitle.textContent = 'Suspend Account';
                    statusModalReasonWrap.style.display = 'block';
                    statusModalConfirmText.style.display = 'none';
                    statusModalSubmit.textContent = 'Suspend';
                    statusModalSubmit.style.backgroundColor = '#B91C1C';
                } else {
                    statusModalTitle.textContent = 'Activate Account';
                    statusModalReasonWrap.style.display = 'none';
                    statusModalConfirmText.style.display = 'block';
                    statusModalConfirmText.textContent = 'Are you sure you want to unsuspend this account?';
                    statusModalSubmit.textContent = 'Activate';
                    statusModalSubmit.style.backgroundColor = '#047857';
                }
                statusModal.style.display = 'flex';
            }

            function closeStatusModal() {
                statusModal.style.display = 'none';
            }

            document.body.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest ? e.target.closest('.open-status-modal') : null;
                if (btn) {
                    var uid = btn.getAttribute('data-user-id');
                    var active = btn.getAttribute('data-user-status') === 'active';
                    openStatusModal(uid, active);
                }
            });

            if (closeBtn) closeBtn.addEventListener('click', closeStatusModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeStatusModal);
            if (statusModal) statusModal.addEventListener('click', function(e) { if (e.target === statusModal) closeStatusModal(); });

            statusModalForm.addEventListener('submit', function(e) {
                var reasonWrap = document.getElementById('statusModalReasonWrap');
                if (reasonWrap && reasonWrap.style.display !== 'none') {
                    var sel = document.getElementById('modalSuspensionReason');
                    var otherInput = document.getElementById('modalSuspensionReasonOther');
                    var hidden = document.getElementById('suspensionReasonHidden');
                    if (!sel || !sel.value) { e.preventDefault(); return false; }
                    if (sel.value === 'Other') {
                        var custom = otherInput ? otherInput.value.trim() : '';
                        if (!custom) { e.preventDefault(); alert('Please specify the reason.'); return false; }
                        if (hidden) hidden.value = custom;
                    } else {
                        if (hidden) hidden.value = sel.value;
                    }
                }
            });
        });
    </script>
@endsection
