@extends('layouts.admin')

@section('title', 'Manage Leads')

@section('content')
    <div class="top-header">
        <h1>Manage Leads</h1>
    </div>

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('leads.create') }}" class="btn-create"><i class="fas fa-plus"></i> Add New Lead</a>
                <button type="button" id="togglePipelineBtn" class="btn-create" style="background-color: #0EA5E9; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-columns"></i> View Lead Pipeline
                </button>
                <button type="button" id="toggleAssignBtn" class="btn-create" style="background-color: #14B8A6; color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-user-check"></i> Assign Lead
                </button>
            </div>
        @else
            <div></div>
        @endif

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search leads..."
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 300px;">
        </div>
    </div>

    {{-- Modal: View Lead Pipeline --}}
    <div id="pipelineModal" class="modal-overlay" style="display: none;">
        <div id="pipelineModalBox" class="modal-box pipeline-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-shrink: 0;">
                <h3 style="margin: 0;">Lead Pipeline</h3>
                <button type="button" id="closePipelineModal" class="modal-close-btn">&times;</button>
            </div>
            <div class="pipeline-modal-body">
    <div id="pipelineContainer" class="card pipeline-card">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 12px;">
            <span style="font-weight: 600;">Filter by lead or agent</span>
            <input type="text" id="pipelineSearchInput" placeholder="Search pipeline..."
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 260px;">
        </div>

        <p style="font-size: 12px; color: #64748B; margin-bottom: 12px; font-weight: 600;">
            Showing the latest 12 leads per stage. Search above to find any lead (searches all data).
        </p>

        <div id="pipelineGrid" class="pipeline-grid">
            @include('leads._pipeline_grid', ['pipelineStatuses' => $pipelineStatuses, 'pipelineLeads' => $pipelineLeads])
        </div>
    </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Leads List</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @include('leads._rows', ['leads' => $leads])
            </tbody>
        </table>

        <div style="margin-top: 20px;" id="paginationLinks">
            {{ $leads->links('pagination::bootstrap-4') }}
        </div>
    </div>

    @if(auth()->user()->hasRole('account-owner') || auth()->user()->hasRole('marketing-manager'))
        {{-- Modal: Assign Lead --}}
        <div id="assignModal" class="modal-overlay" style="display: none;">
            <div class="modal-box assign-modal-box">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0;">Assign Lead</h3>
                    <button type="button" id="closeAssignModal" class="modal-close-btn">&times;</button>
                </div>
                <form method="POST" id="quickAssignForm">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label for="leadSelect" style="display: block; margin-bottom: 6px; font-weight: 600;">Lead</label>
                        <div class="custom-dropdown" id="leadDropdownWrapper" style="position: relative;">
                            <input type="hidden" name="lead_id" id="leadSelectHidden" value="">
                            <div class="custom-dropdown-toggle" id="leadDropdownToggle" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px; background: #fff; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span id="leadDropdownText" style="color: #64748B;">Select a lead...</span>
                                <i class="fas fa-chevron-down" style="color: #64748B; font-size: 12px;"></i>
                            </div>
                            <div class="custom-dropdown-menu" id="leadDropdownMenu" style="display: none; position: absolute; width: 100%; background: #fff; border: 1px solid #DBEAFE; border-radius: 6px; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 10000; max-height: 300px; overflow-y: auto;">
                                <div style="padding: 8px; border-bottom: 1px solid #E2E8F0; position: sticky; top: 0; background: #fff; z-index: 10;">
                                    <input type="text" id="leadDropdownSearch" placeholder="Search leads..." style="width: 100%; padding: 8px; border: 1px solid #DBEAFE; border-radius: 4px; font-size: 14px;">
                                </div>
                                <div id="leadDropdownOptions" style="max-height: 250px; overflow-y: auto;">
                                    @forelse($leadsForDropdown ?? $leads as $lead)
                                        <div class="custom-dropdown-option" data-value="{{ $lead->id }}" data-text="{{ $lead->name }} ({{ $lead->assignedAgent->name ?? 'Unassigned' }})" style="padding: 10px; cursor: pointer; border-bottom: 1px solid #F1F5F9; transition: background 0.2s;">
                                            <strong style="display: block; font-size: 14px; color: #1E40AF;">{{ $lead->name }}</strong>
                                            <small style="color: #64748B; font-size: 12px;">{{ $lead->assignedAgent->name ?? 'Unassigned' }}</small>
                                        </div>
                                    @empty
                                        <div style="padding: 12px; text-align: center; color: #94A3B8;">No leads available</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label for="agentSelect">Sales Agent</label>
                        <select id="agentSelect" name="assigned_to" style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                            <option value="">Unassigned</option>
                            @foreach($assignableAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        style="padding: 8px 16px; background-color: #0EA5E9; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        Save Assignment
                    </button>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('tableBody');
            const paginationLinks = document.getElementById('paginationLinks');
            const quickAssignForm = document.getElementById('quickAssignForm');
            const leadSelect = document.getElementById('leadSelect');
            const pipelineModal = document.getElementById('pipelineModal');
            const assignModal = document.getElementById('assignModal');
            const closePipelineModal = document.getElementById('closePipelineModal');
            const closeAssignModal = document.getElementById('closeAssignModal');
            const pipelineContainer = document.getElementById('pipelineContainer');
            const togglePipelineBtn = document.getElementById('togglePipelineBtn');
            const toggleAssignBtn = document.getElementById('toggleAssignBtn');

            let timeout = null;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                const query = searchInput.value;
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    fetch(`{{ route('leads.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                        .then(response => response.json())
                        .then(data => {
                            tableBody.innerHTML = data.rows;
                            // Update custom dropdown when main search updates leads
                            if (leadDropdownOptions && data.leads) {
                                leadDropdownOptions.innerHTML = '';
                                allLeadOptions = [];
                                if (data.leads.length === 0) {
                                    leadDropdownOptions.innerHTML = '<div style="padding: 12px; text-align: center; color: #94A3B8;">No leads available</div>';
                                } else {
                                    data.leads.forEach(function(lead) {
                                        var opt = document.createElement('div');
                                        opt.className = 'custom-dropdown-option';
                                        opt.setAttribute('data-value', lead.id);
                                        opt.setAttribute('data-text', lead.name + ' (' + lead.assigned + ')');
                                        opt.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid #F1F5F9; transition: background 0.2s;';
                                        opt.innerHTML = '<strong style="display: block; font-size: 14px; color: #1E40AF;">' + lead.name + '</strong><small style="color: #64748B; font-size: 12px;">' + lead.assigned + '</small>';
                                        leadDropdownOptions.appendChild(opt);
                                        allLeadOptions.push(opt);
                                    });
                                }
                            }
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

            // Custom dropdown for Lead selection
            var leadDropdownWrapper = document.getElementById('leadDropdownWrapper');
            var leadDropdownToggle = document.getElementById('leadDropdownToggle');
            var leadDropdownMenu = document.getElementById('leadDropdownMenu');
            var leadDropdownSearch = document.getElementById('leadDropdownSearch');
            var leadDropdownOptions = document.getElementById('leadDropdownOptions');
            var leadDropdownText = document.getElementById('leadDropdownText');
            var leadSelectHidden = document.getElementById('leadSelectHidden');
            var allLeadOptions = [];

            if (leadDropdownOptions) {
                allLeadOptions = Array.from(leadDropdownOptions.querySelectorAll('.custom-dropdown-option'));
            }

            function filterLeadOptions(query) {
                var q = (query || '').toLowerCase().trim();
                var visible = 0;
                allLeadOptions.forEach(function(opt) {
                    var text = (opt.getAttribute('data-text') || '').toLowerCase();
                    var match = !q || text.indexOf(q) !== -1;
                    opt.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                if (visible === 0 && q) {
                    leadDropdownOptions.innerHTML = '<div style="padding: 12px; text-align: center; color: #94A3B8;">No leads found</div>';
                } else if (visible === 0 && !q && allLeadOptions.length > 0) {
                    leadDropdownOptions.innerHTML = '';
                    allLeadOptions.forEach(function(opt) { leadDropdownOptions.appendChild(opt); });
                }
            }

            if (leadDropdownSearch) {
                leadDropdownSearch.addEventListener('input', function() {
                    filterLeadOptions(this.value);
                });
            }

            if (leadDropdownToggle && leadDropdownMenu) {
                leadDropdownToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var isOpen = leadDropdownMenu.style.display !== 'none';
                    leadDropdownMenu.style.display = isOpen ? 'none' : 'block';
                    if (!isOpen && leadDropdownSearch) {
                        setTimeout(function() { leadDropdownSearch.focus(); }, 50);
                    }
                });

                if (leadDropdownOptions) {
                    leadDropdownOptions.addEventListener('click', function(e) {
                        var opt = e.target.closest('.custom-dropdown-option');
                        if (opt) {
                            var value = opt.getAttribute('data-value');
                            var text = opt.getAttribute('data-text');
                            if (leadSelectHidden) leadSelectHidden.value = value;
                            if (leadDropdownText) leadDropdownText.textContent = text;
                            if (leadDropdownText) leadDropdownText.style.color = '#1E40AF';
                            leadDropdownMenu.style.display = 'none';
                            if (leadDropdownSearch) leadDropdownSearch.value = '';
                            filterLeadOptions('');
                        }
                    });
                }
            }

            document.addEventListener('click', function(e) {
                if (leadDropdownWrapper && !leadDropdownWrapper.contains(e.target)) {
                    if (leadDropdownMenu) leadDropdownMenu.style.display = 'none';
                }
            });

            if (quickAssignForm && leadSelectHidden) {
                quickAssignForm.addEventListener('submit', function(event) {
                    if (!leadSelectHidden.value) {
                        event.preventDefault();
                        alert('Please select a lead first.');
                        return;
                    }

                    event.preventDefault();
                    quickAssignForm.action = `/leads/${leadSelectHidden.value}/assign`;
                    quickAssignForm.submit();
                });
            }

            if (togglePipelineBtn && pipelineModal) {
                togglePipelineBtn.addEventListener('click', function() { pipelineModal.style.display = 'flex'; });
            }
            if (closePipelineModal && pipelineModal) {
                closePipelineModal.addEventListener('click', function() { pipelineModal.style.display = 'none'; });
            }
            if (pipelineModal) {
                pipelineModal.addEventListener('click', function(e) { if (e.target === pipelineModal) pipelineModal.style.display = 'none'; });
            }

            if (toggleAssignBtn && assignModal) {
                toggleAssignBtn.addEventListener('click', function() { assignModal.style.display = 'flex'; });
            }
            if (closeAssignModal && assignModal) {
                closeAssignModal.addEventListener('click', function() { assignModal.style.display = 'none'; });
            }
            if (assignModal) {
                assignModal.addEventListener('click', function(e) { if (e.target === assignModal) assignModal.style.display = 'none'; });
            }

            var pipelineSearchInput = document.getElementById('pipelineSearchInput');
            var pipelineGrid = document.getElementById('pipelineGrid');
            var pipelineSearchTimeout = null;
            if (pipelineSearchInput && pipelineGrid) {
                pipelineSearchInput.addEventListener('input', function() {
                    var q = (this.value || '').trim();
                    clearTimeout(pipelineSearchTimeout);
                    pipelineSearchTimeout = setTimeout(function() {
                        var url = '{{ route("leads.index") }}?pipeline_only=1&pipeline_search=' + encodeURIComponent(q);
                        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                pipelineGrid.innerHTML = data.pipelineHtml || '';
                                if (data.totals) {
                                    pipelineGrid.querySelectorAll('.pipeline-column').forEach(function(col) {
                                        var status = col.getAttribute('data-status');
                                        var countEl = col.querySelector('.pipeline-column-count');
                                        if (countEl && status && data.totals[status] !== undefined) {
                                            countEl.textContent = data.totals[status];
                                            col.setAttribute('data-original-count', data.totals[status]);
                                        }
                                    });
                                }
                            })
                            .catch(function(err) { console.error('Pipeline search error:', err); });
                    }, 300);
                });
            }
        });
    </script>
    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #64748B; line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: #1E293B; }
        .pipeline-modal-box { width: 1200px; max-width: 95vw; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
        .pipeline-modal-body { flex: 1; overflow-y: auto; overflow-x: hidden; }
        .pipeline-card { margin-bottom: 0; overflow: visible; }
        .pipeline-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .pipeline-column { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px; max-height: 460px; overflow-y: auto; }
        .pipeline-lead-card { padding: 8px; border-radius: 6px; border: 1px solid #E5E7EB; background: white; margin-bottom: 8px; }
        .pipeline-empty { font-size: 12px; color: #94A3B8; margin: 0; font-weight: 700; }
        .assign-modal-box { max-width: 600px; width: 100%; }
        .custom-dropdown { position: relative; }
        .custom-dropdown-toggle:hover { border-color: #93C5FD; background: #F8FAFC; }
        .custom-dropdown-option:hover { background: #F1F5F9 !important; }
        .custom-dropdown-menu::-webkit-scrollbar { width: 8px; }
        .custom-dropdown-menu::-webkit-scrollbar-track { background: #F1F5F9; border-radius: 4px; }
        .custom-dropdown-menu::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        .custom-dropdown-menu::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
    </style>
@endsection
