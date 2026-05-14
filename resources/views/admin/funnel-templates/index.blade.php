@extends('layouts.admin')

@section('title', 'Funnel Templates')

@section('styles')
    <style>
        .template-table-card {
            overflow: hidden;
        }

        .template-table-scroll {
            max-width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
        }

        .template-table {
            min-width: 760px;
            margin-bottom: 0;
        }

        .template-table th,
        .template-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        .template-table th:last-child,
        .template-table td:last-child {
            min-width: 0;
        }

        .template-table th:nth-child(1),
        .template-table td:nth-child(1) {
            min-width: 130px;
        }

        .template-table th:nth-child(2),
        .template-table td:nth-child(2) {
            min-width: 128px;
        }

        .template-table th:nth-child(5),
        .template-table td:nth-child(5) {
            min-width: 170px;
        }

        .template-table td:nth-child(5) {
            white-space: normal;
            word-break: break-word;
        }

        .template-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .template-action,
        .template-action-btn,
        .template-action-static {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            padding: 0;
            border-radius: 12px;
            border: 1px solid #D8DCE8;
            background: #fff;
            color: var(--theme-primary, #240E35);
            position: relative;
            text-decoration: none;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, color .16s ease;
        }

        .template-action:hover,
        .template-action-btn:hover,
        .template-action:focus-visible,
        .template-action-btn:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            border-color: #BFC9D8;
            outline: none;
        }

        .template-action i,
        .template-action-btn i,
        .template-action-static i {
            font-size: 16px;
        }

        .template-action::after,
        .template-action-btn::after,
        .template-action-static::after {
            content: attr(data-tooltip);
            position: absolute;
            left: 50%;
            bottom: calc(100% + 10px);
            transform: translateX(-50%) translateY(4px);
            padding: 6px 9px;
            border-radius: 8px;
            background: #240E35;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease, transform .14s ease;
            z-index: 5;
        }

        .template-action::before,
        .template-action-btn::before,
        .template-action-static::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: calc(100% + 4px);
            transform: translateX(-50%);
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #240E35;
            opacity: 0;
            pointer-events: none;
            transition: opacity .14s ease;
            z-index: 5;
        }

        .template-action:hover::after,
        .template-action:hover::before,
        .template-action:focus-visible::after,
        .template-action:focus-visible::before,
        .template-action-btn:hover::after,
        .template-action-btn:hover::before,
        .template-action-btn:focus-visible::after,
        .template-action-btn:focus-visible::before,
        .template-action-static:hover::after,
        .template-action-static:hover::before {
            opacity: 1;
        }

        .template-action:hover::after,
        .template-action:focus-visible::after,
        .template-action-btn:hover::after,
        .template-action-btn:focus-visible::after,
        .template-action-static:hover::after {
            transform: translateX(-50%) translateY(0);
        }

        .template-action--edit {
            color: var(--theme-primary, #240E35);
        }

        .template-action--replace {
            color: #0F766E;
        }

        .template-action--analytics {
            color: #2563EB;
        }

        .template-action--delete {
            color: #DC2626;
        }

        .template-action-static {
            cursor: default;
        }

        .template-action-static:hover {
            transform: none;
            box-shadow: none;
            border-color: #D8DCE8;
        }

        .template-pagination {
            margin-top: 18px;
        }

        @media (max-width: 768px) {
            .template-table-card {
                padding: 16px;
            }

            .template-table-scroll {
                padding-bottom: 2px;
            }

            .template-table {
                min-width: 720px;
                border-radius: 0;
            }

            .template-table th,
            .template-table td {
                padding: 15px 20px;
                line-height: 1.25;
            }

            .template-table th {
                font-weight: 700;
            }

            .template-table td:nth-child(2) {
                white-space: normal;
            }

            .template-pagination {
                display: flex;
                justify-content: flex-end;
                padding-top: 12px;
            }

            .template-pagination .pagination {
                flex-wrap: nowrap;
                gap: 6px;
                justify-content: flex-end;
            }

            .template-pagination .page-link {
                min-width: 46px;
                height: 46px;
                border-radius: 6px;
                font-size: 16px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Shared Funnel Templates</h1>
    </div>

    <div class="actions" style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('admin.funnel-templates.create') }}" class="btn-create btn-create--icon-expand" aria-label="New Template"><i class="fas fa-plus"></i><span class="btn-create__label">New Template</span></a>
            <a href="{{ route('admin.funnel-templates.import') }}" class="btn-create btn-create--icon-expand" style="background:#fff; color:var(--theme-primary, #240E35); border:1px solid var(--theme-border, #E6E1EF);" aria-label="Import JSON Template"><i class="fas fa-file-import"></i><span class="btn-create__label">Import JSON Template</span></a>
            <a href="{{ route('admin.funnel-templates.analytics') }}" class="btn-create btn-create--icon-expand" style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE;" aria-label="Template Analytics"><i class="fas fa-chart-line"></i><span class="btn-create__label">Template Analytics</span></a>
        </div>
        <form method="GET" action="{{ route('admin.funnel-templates.index') }}">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="🔍 Search templates..."
                style="width:min(320px, 100%); padding:10px 12px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff;">
        </form>
    </div>

    <div class="card template-table-card" style="margin-top: 16px;">
        <div style="margin-bottom: 12px; color:#64748b; font-size:13px;">
            Super admins can build, import, and publish Step-by-Step Page templates here. Published templates appear for users based on their subscribed plan.
        </div>

        <div class="template-table-scroll" data-template-table-scroll>
            <table class="template-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Pages</th>
                        <th>Slug</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.funnel-templates._rows', ['templates' => $templates])
                </tbody>
            </table>
        </div>

        <div class="template-pagination">
            {{ $templates->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <div id="deleteTemplateModal" class="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:20px;">
        <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="deleteTemplateModalTitle" style="width:100%;max-width:460px;background:#fff;border-radius:10px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.15);">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:8px;">
                <h3 id="deleteTemplateModalTitle" style="margin:0;">Confirm Template Deletion</h3>
                <button type="button" id="closeDeleteTemplateModal" class="modal-close-btn" style="background:none;border:none;font-size:28px;cursor:pointer;color:var(--theme-muted, #6B7280);line-height:1;padding:0 4px;">&times;</button>
            </div>
            <p style="margin:0 0 18px;color:var(--theme-muted, #6B7280);line-height:1.6;">
                Delete <strong id="deleteTemplateName">this template</strong>? This cannot be undone.
            </p>
            <div style="display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" id="cancelDeleteTemplateBtn" class="btn-create" style="background:#64748B;">Cancel</button>
                <button type="button" id="confirmDeleteTemplateBtn" class="btn-create" style="background:#DC2626;">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteTemplateModal = document.getElementById('deleteTemplateModal');
            const deleteTemplateName = document.getElementById('deleteTemplateName');
            const closeDeleteTemplateModal = document.getElementById('closeDeleteTemplateModal');
            const cancelDeleteTemplateBtn = document.getElementById('cancelDeleteTemplateBtn');
            const confirmDeleteTemplateBtn = document.getElementById('confirmDeleteTemplateBtn');
            let pendingDeleteForm = null;

            const closeTemplateDeleteModal = () => {
                if (!deleteTemplateModal) return;
                deleteTemplateModal.style.display = 'none';
                pendingDeleteForm = null;
            };

            const openTemplateDeleteModal = (form) => {
                if (!deleteTemplateModal) {
                    if (window.confirm('Delete this template? This cannot be undone.')) {
                        form.submit();
                    }
                    return;
                }
                pendingDeleteForm = form;
                const name = form.getAttribute('data-template-name') || 'this template';
                if (deleteTemplateName) {
                    deleteTemplateName.textContent = name;
                }
                deleteTemplateModal.style.display = 'flex';
            };

            document.addEventListener('submit', function(event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || !form.matches('form[data-delete-template-form]')) {
                    return;
                }

                event.preventDefault();
                openTemplateDeleteModal(form);
            });

            if (closeDeleteTemplateModal) {
                closeDeleteTemplateModal.addEventListener('click', closeTemplateDeleteModal);
            }
            if (cancelDeleteTemplateBtn) {
                cancelDeleteTemplateBtn.addEventListener('click', closeTemplateDeleteModal);
            }
            if (confirmDeleteTemplateBtn) {
                confirmDeleteTemplateBtn.addEventListener('click', function() {
                    if (pendingDeleteForm) {
                        pendingDeleteForm.submit();
                    }
                });
            }
            if (deleteTemplateModal) {
                deleteTemplateModal.addEventListener('click', function(event) {
                    if (event.target === deleteTemplateModal) {
                        closeTemplateDeleteModal();
                    }
                });
            }
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && deleteTemplateModal && deleteTemplateModal.style.display === 'flex') {
                    closeTemplateDeleteModal();
                }
            });

        });
    </script>
@endsection
