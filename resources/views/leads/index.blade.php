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
                <a href="{{ route('crm.custom-fields.index') }}" class="btn-create" style="background-color: var(--theme-primary-dark, #2E1244);">
                    <i class="fas fa-sliders-h"></i> Manage Custom Fields
                </a>
                <button type="button" id="togglePipelineBtn" class="btn-create" style="background-color: var(--theme-accent, var(--theme-accent, #6B4A7A)); color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-columns"></i> View Lead Pipeline
                </button>
                <button type="button" id="toggleAssignBtn" class="btn-create" style="background-color: var(--theme-primary, var(--theme-primary, #240E35)); color: #fff; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-user-check"></i> Assign Lead
                </button>
            </div>
        @endif

        <div class="search-box" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <input type="text" id="searchInput" placeholder="Search leads..."
                style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; width: 240px;">
            <select id="tagFilterInput" style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; min-width: 180px;">
                <option value="">All tags</option>
                @foreach(($availableTags ?? []) as $tagOption)
                    <option value="{{ $tagOption }}" {{ ($tagFilter ?? '') === $tagOption ? 'selected' : '' }}>{{ $tagOption }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @include('partials.plan-usage-summary', [
        'planUsage' => $planUsage ?? [],
        'resourceKey' => 'leads',
        'title' => 'Lead Limit',
    ])

    @include('leads._pipeline_reports', ['pipelineReports' => $pipelineReports])

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
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <input type="text" id="pipelineSearchInput" placeholder="Search pipeline..."
                    style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; width: 220px;">
                <select id="pipelineTagFilterInput" style="padding: 8px 12px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; min-width: 180px;">
                    <option value="">All tags</option>
                    @foreach(($availableTags ?? []) as $tagOption)
                        <option value="{{ $tagOption }}" {{ ($pipelineTagFilter ?? '') === $tagOption ? 'selected' : '' }}>{{ $tagOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <p style="font-size: 12px; color: var(--theme-muted, #6B7280); margin-bottom: 12px; font-weight: 600;">
            Showing the latest 12 leads per stage. Search above to find any lead (searches all data).
        </p>

        <div id="pipelineGrid" class="pipeline-grid">
            @include('leads._pipeline_grid', ['pipelineStatuses' => $pipelineStatuses, 'pipelineLeads' => $pipelineLeads])
        </div>
    </div>
            </div>
        </div>
    </div>

    <div class="card leads-list-card" style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px;">
            <h3 style="margin: 0;">Leads List</h3>
            <button type="button" id="toggleLeadsListBtn"
                style="padding: 10px 16px; background: var(--theme-primary, #240E35); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; min-width: 88px;"
                aria-expanded="false">
                Show
            </button>
        </div>
        <div id="leadsListContent" style="display: none;">
            <div class="leads-table-wrap">
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Assigned To</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('leads._rows', ['leads' => $leads])
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 20px;" id="paginationLinks">
                {{ $leads->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <div id="deleteLeadModal" class="modal-overlay" style="display: none;">
        <div class="modal-box delete-lead-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0;">Delete Lead</h3>
                <button type="button" id="closeDeleteLeadModal" class="modal-close-btn">&times;</button>
            </div>
            <p style="margin: 0 0 18px 0; color: var(--theme-body-text, #111827); line-height: 1.5;">
                Are you sure you want to delete <strong id="deleteLeadName">this lead</strong>?
                This action cannot be undone.
            </p>
            <form method="POST" id="deleteLeadModalForm" style="display: flex; justify-content: flex-end; gap: 10px;">
                @csrf
                @method('DELETE')
                <button type="button" id="cancelDeleteLeadBtn"
                    style="padding: 10px 16px; background: var(--theme-surface-softer, #F7F7FB); color: var(--theme-body-text, #111827); border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Cancel
                </button>
                <button type="submit"
                    style="padding: 10px 16px; background: #DC2626; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">
                    Delete Lead
                </button>
            </form>
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
                            <div class="custom-dropdown-toggle" id="leadDropdownToggle" style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; background: var(--theme-surface, #FFFFFF); cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                                <span id="leadDropdownText" style="color: var(--theme-muted, #6B7280);">Select a lead...</span>
                                <i class="fas fa-chevron-down" style="color: var(--theme-muted, #6B7280); font-size: 12px;"></i>
                            </div>
                            <div class="custom-dropdown-menu" id="leadDropdownMenu" style="display: none; position: absolute; width: 100%; background: var(--theme-surface, #FFFFFF); border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 10000; max-height: 300px; overflow-y: auto;">
                                <div style="padding: 8px; border-bottom: 1px solid var(--theme-border, #E6E1EF); position: sticky; top: 0; background: var(--theme-surface, #FFFFFF); z-index: 10;">
                                    <input type="text" id="leadDropdownSearch" placeholder="Search leads..." style="width: 100%; padding: 8px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 4px; font-size: 14px;">
                                </div>
                                <div id="leadDropdownOptions" style="max-height: 250px; overflow-y: auto;">
                                    @forelse($leadsForDropdown ?? $leads as $lead)
                                        <div class="custom-dropdown-option" data-value="{{ $lead->id }}" data-text="{{ $lead->name }} ({{ $lead->assignedAgent->name ?? 'Unassigned' }})" style="padding: 10px; cursor: pointer; border-bottom: 1px solid var(--theme-surface-softer, #F7F7FB); transition: background 0.2s;">
                                            <strong style="display: block; font-size: 14px; color: var(--theme-primary-dark, #2E1244);">{{ $lead->name }}</strong>
                                            <small style="color: var(--theme-muted, #6B7280); font-size: 12px;">{{ $lead->assignedAgent->name ?? 'Unassigned' }}</small>
                                        </div>
                                    @empty
                                        <div style="padding: 12px; text-align: center; color: var(--theme-muted, #6B7280);">No leads available</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <label for="agentSelect">Sales Agent</label>
                        <select id="agentSelect" name="assigned_to" style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                            <option value="">Unassigned</option>
                            @foreach($assignableAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        style="padding: 8px 16px; background-color: var(--theme-primary, var(--theme-primary, #240E35)); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Save Assignment
                    </button>
                </form>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tagFilterInput = document.getElementById('tagFilterInput');
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
            const leadsTableWrap = document.querySelector('.leads-table-wrap');
            const deleteLeadModal = document.getElementById('deleteLeadModal');
            const deleteLeadModalForm = document.getElementById('deleteLeadModalForm');
            const deleteLeadName = document.getElementById('deleteLeadName');
            const closeDeleteLeadModal = document.getElementById('closeDeleteLeadModal');
            const cancelDeleteLeadBtn = document.getElementById('cancelDeleteLeadBtn');
            const togglePipelineReportsBtn = document.getElementById('togglePipelineReportsBtn');
            const pipelineReportsContent = document.getElementById('pipelineReportsContent');
            const toggleLeadsListBtn = document.getElementById('toggleLeadsListBtn');
            const leadsListContent = document.getElementById('leadsListContent');

            let timeout = null;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                const query = searchInput.value;
                if (query.length > 0 && query.length < 2) return;

                timeout = setTimeout(() => {
                    const tagValue = tagFilterInput ? (tagFilterInput.value || '') : '';
                    fetch(`{{ route('leads.index') }}?search=${encodeURIComponent(query)}&tag=${encodeURIComponent(tagValue)}`, {
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
                                    leadDropdownOptions.innerHTML = '<div style="padding: 12px; text-align: center; color: var(--theme-muted, #6B7280);">No leads available</div>';
                                } else {
                                    data.leads.forEach(function(lead) {
                                        var opt = document.createElement('div');
                                        opt.className = 'custom-dropdown-option';
                                        opt.setAttribute('data-value', lead.id);
                                        opt.setAttribute('data-text', lead.name + ' (' + lead.assigned + ')');
                                        opt.style.cssText = 'padding: 10px; cursor: pointer; border-bottom: 1px solid var(--theme-surface-softer, #F7F7FB); transition: background 0.2s;';
                                        opt.innerHTML = '<strong style="display: block; font-size: 14px; color: var(--theme-primary-dark, #2E1244);">' + lead.name + '</strong><small style="color: var(--theme-muted, #6B7280); font-size: 12px;">' + lead.assigned + '</small>';
                                        leadDropdownOptions.appendChild(opt);
                                        allLeadOptions.push(opt);
                                    });
                                }
                            }
                            const hasFilter = (query || '').trim().length > 0 || (tagValue || '').trim().length > 0;
                            if (hasFilter) {
                                paginationLinks.style.display = 'none';
                            } else {
                                paginationLinks.style.display = 'block';
                            }
                        })
                        .catch(error => console.error('Search error:', error));
                }, 300);
            });

            if (tagFilterInput) {
                tagFilterInput.addEventListener('change', function() {
                    if (searchInput) {
                        searchInput.dispatchEvent(new Event('keyup'));
                    }
                });
            }

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
                    leadDropdownOptions.innerHTML = '<div style="padding: 12px; text-align: center; color: var(--theme-muted, #6B7280);">No leads found</div>';
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
                            if (leadDropdownText) leadDropdownText.style.color = 'var(--theme-primary-dark, #2E1244)';
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

                var deleteTrigger = e.target.closest('.lead-delete-trigger');
                if (deleteTrigger) {
                    e.preventDefault();
                    if (deleteLeadModalForm) {
                        deleteLeadModalForm.action = deleteTrigger.getAttribute('data-delete-action') || '';
                    }
                    if (deleteLeadName) {
                        deleteLeadName.textContent = deleteTrigger.getAttribute('data-lead-name') || 'this lead';
                    }
                    if (deleteLeadModal) {
                        deleteLeadModal.style.display = 'flex';
                    }
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

            if (closeDeleteLeadModal && deleteLeadModal) {
                closeDeleteLeadModal.addEventListener('click', function() {
                    deleteLeadModal.style.display = 'none';
                });
            }

            if (cancelDeleteLeadBtn && deleteLeadModal) {
                cancelDeleteLeadBtn.addEventListener('click', function() {
                    deleteLeadModal.style.display = 'none';
                });
            }

            if (deleteLeadModal) {
                deleteLeadModal.addEventListener('click', function(e) {
                    if (e.target === deleteLeadModal) {
                        deleteLeadModal.style.display = 'none';
                    }
                });
            }

            if (togglePipelineReportsBtn && pipelineReportsContent) {
                togglePipelineReportsBtn.addEventListener('click', function() {
                    var isHidden = pipelineReportsContent.style.display === 'none';
                    pipelineReportsContent.style.display = isHidden ? 'block' : 'none';
                    togglePipelineReportsBtn.textContent = isHidden ? 'Hide' : 'Show';
                    togglePipelineReportsBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });
            }

            if (toggleLeadsListBtn && leadsListContent) {
                toggleLeadsListBtn.addEventListener('click', function() {
                    var isHidden = leadsListContent.style.display === 'none';
                    leadsListContent.style.display = isHidden ? 'block' : 'none';
                    toggleLeadsListBtn.textContent = isHidden ? 'Hide' : 'Show';
                    toggleLeadsListBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                });
            }

            var pipelineSearchInput = document.getElementById('pipelineSearchInput');
            var pipelineTagFilterInput = document.getElementById('pipelineTagFilterInput');
            var pipelineGrid = document.getElementById('pipelineGrid');
            var pipelineSearchTimeout = null;
            if (pipelineSearchInput && pipelineGrid) {
                var triggerPipelineSearch = function() {
                    var q = (this.value || '').trim();
                    var tagQ = pipelineTagFilterInput ? (pipelineTagFilterInput.value || '').trim() : '';
                    clearTimeout(pipelineSearchTimeout);
                    pipelineSearchTimeout = setTimeout(function() {
                        var url = '{{ route("leads.index") }}?pipeline_only=1&pipeline_search=' + encodeURIComponent(q) + '&pipeline_tag=' + encodeURIComponent(tagQ);
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
                };

                pipelineSearchInput.addEventListener('input', triggerPipelineSearch);
                if (pipelineTagFilterInput) {
                    pipelineTagFilterInput.addEventListener('change', function() {
                        triggerPipelineSearch.call(pipelineSearchInput);
                    });
                }
            }

            if (leadsTableWrap) {
                // Always start at the first column after page load.
                leadsTableWrap.scrollLeft = 0;
            }
        });
    </script>
    <style>
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px; }
        .modal-box { background: var(--theme-surface, #FFFFFF); border-radius: 8px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        .modal-close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: var(--theme-muted, #6B7280); line-height: 1; padding: 0 4px; }
        .modal-close-btn:hover { color: var(--theme-body-text, #111827); }
        .pipeline-modal-box { width: 1200px; max-width: 95vw; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
        .pipeline-modal-body { flex: 1; overflow-y: auto; overflow-x: hidden; }
        .pipeline-card { margin-bottom: 0; overflow: visible; }
        .pipeline-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; }
        .pipeline-column { background: var(--theme-surface-softer, #F7F7FB); border: 1px solid var(--theme-border, #E6E1EF); border-radius: 8px; padding: 10px; max-height: 460px; overflow-y: auto; }
        .pipeline-lead-card { padding: 8px; border-radius: 6px; border: 1px solid var(--theme-border, #E6E1EF); background: var(--theme-surface, #FFFFFF); margin-bottom: 8px; }
        .pipeline-empty { font-size: 12px; color: var(--theme-muted, #6B7280); margin: 0; font-weight: 700; }
        .assign-modal-box { max-width: 600px; width: 100%; }
        .delete-lead-modal-box { max-width: 460px; width: 100%; }
        .custom-dropdown { position: relative; }
        .custom-dropdown-toggle:hover { border-color: var(--theme-border, #E6E1EF); background: var(--theme-surface-softer, #F7F7FB); }
        .custom-dropdown-option:hover { background: var(--theme-surface-softer, #F7F7FB) !important; }
        .custom-dropdown-menu::-webkit-scrollbar { width: 8px; }
        .custom-dropdown-menu::-webkit-scrollbar-track { background: var(--theme-surface-softer, #F7F7FB); border-radius: 4px; }
        .custom-dropdown-menu::-webkit-scrollbar-thumb { background: var(--theme-border, #E6E1EF); border-radius: 4px; }
        .custom-dropdown-menu::-webkit-scrollbar-thumb:hover { background: var(--theme-muted, #6B7280); }
        .actions .search-box {
            margin-left: 0 !important;
            justify-content: flex-start !important;
            padding-right: 0 !important;
            max-width: 100%;
        }
        .pipeline-report-card {
            overflow: visible;
        }
        .pipeline-report-toggle-btn {
            padding: 10px 16px;
            background: var(--theme-primary, #240E35);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            min-width: 88px;
        }
        .pipeline-report-toggle-btn:hover {
            background: var(--theme-primary-dark, #2E1244);
        }
        .pipeline-report-summary {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .pipeline-report-metric {
            background: var(--theme-surface-softer, #F7F7FB);
            border: 1px solid var(--theme-border, #E6E1EF);
            border-radius: 10px;
            padding: 14px;
        }
        .pipeline-report-metric strong {
            display: block;
            font-size: 26px;
            color: var(--theme-primary-dark, #2E1244);
        }
        .pipeline-report-label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--theme-muted, #6B7280);
        }
        .pipeline-report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .pipeline-report-panel {
            background: var(--theme-surface-softer, #F7F7FB);
            border: 1px solid var(--theme-border, #E6E1EF);
            border-radius: 10px;
            padding: 16px;
        }
        .pipeline-report-panel h4 {
            margin: 0 0 14px 0;
            color: var(--theme-primary-dark, #2E1244);
        }
        .pipeline-report-list {
            display: grid;
            gap: 10px;
        }
        .pipeline-report-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            background: var(--theme-surface, #FFFFFF);
        }
        .pipeline-report-row-stack {
            display: grid;
            justify-content: stretch;
        }
        .pipeline-report-table-wrap {
            overflow-x: auto;
        }
        .pipeline-report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pipeline-report-table th,
        .pipeline-report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--theme-border, #E6E1EF);
            text-align: left;
            white-space: nowrap;
        }
        .pipeline-report-table th {
            color: var(--theme-muted, #6B7280);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .leads-table-wrap {
            display: block;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            scrollbar-gutter: stable;
        }
        .leads-table {
            table-layout: auto;
            width: 100%;
            min-width: 0;
            margin-bottom: 0;
        }
        .leads-list-card {
            overflow: visible;
        }
        .leads-table th,
        .leads-table td {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            vertical-align: middle;
            height: 64px;
            line-height: 1.3;
        }
        .leads-table td .cell-text {
            display: inline;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .leads-table th:nth-child(1),
        .leads-table td:nth-child(1),
        .leads-table th:nth-child(2),
        .leads-table td:nth-child(2),
        .leads-table th:nth-child(3),
        .leads-table td:nth-child(3) {
            white-space: nowrap;
        }
        .leads-table td:nth-child(1) .cell-text,
        .leads-table td:nth-child(2) .cell-text,
        .leads-table td:nth-child(3) .cell-text {
            display: inline-block;
            white-space: nowrap;
            overflow-wrap: normal;
            word-break: normal;
        }
        .leads-table td:nth-child(8) {
            overflow: visible;
            white-space: nowrap;
            min-width: 140px;
        }
        .leads-table th:nth-child(8) {
            white-space: nowrap;
            min-width: 140px;
        }
        .leads-table .lead-actions {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
            flex-wrap: nowrap;
        }
        .leads-table .lead-actions form {
            margin: 0;
            display: inline-flex;
            align-items: center;
        }
        .leads-table .lead-actions a,
        .leads-table .lead-actions button {
            white-space: nowrap;
        }
        .leads-table .lead-tags {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            min-width: 0;
        }
        .leads-table .lead-tag {
            white-space: nowrap;
            flex: 0 1 auto;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .leads-table .lead-no-tags {
            display: inline-block;
            white-space: nowrap;
        }
        .leads-table th:nth-child(4),
        .leads-table td:nth-child(4),
        .leads-table th:nth-child(5),
        .leads-table td:nth-child(5),
        .leads-table th:nth-child(6),
        .leads-table td:nth-child(6),
        .leads-table th:nth-child(7),
        .leads-table td:nth-child(7) {
            white-space: nowrap;
        }
        .leads-table th:nth-child(4), .leads-table td:nth-child(4) { min-width: 110px; }
        .leads-table th:nth-child(5), .leads-table td:nth-child(5) { min-width: 140px; }
        .leads-table th:nth-child(6), .leads-table td:nth-child(6) { min-width: 120px; }
        .leads-table th:nth-child(7), .leads-table td:nth-child(7) { min-width: 70px; text-align: center; }
        .leads-table .lead-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }
        @media (max-width: 1100px) {
            .actions {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px;
            }
            .actions > div {
                width: 100%;
            }
            .actions .search-box {
                width: 100%;
                display: flex;
                flex-direction: column;
                gap: 10px !important;
                align-items: stretch !important;
                margin-left: 0 !important;
            }
            .actions .search-box input,
            .actions .search-box select {
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100%;
            }
            .actions .search-box { flex-wrap: nowrap !important; }
            .leads-table th, .leads-table td { width: auto !important; }
            .pipeline-report-summary,
            .pipeline-report-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
