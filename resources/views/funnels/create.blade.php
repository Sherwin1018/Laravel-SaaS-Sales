@extends('layouts.admin')

@section('title', 'Create Funnel')

@section('styles')
    <style>
        .template-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(5px);
        }
        .template-modal-box {
            width: min(980px, calc(100vw - 32px));
            max-height: calc(100vh - 40px);
            overflow: hidden;
            border-radius: 22px;
            background: #fff;
            border: 1px solid #e6e1ef;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
        }
        .template-modal-scroll {
            max-height: calc(100vh - 40px);
            overflow-y: auto;
        }
        .template-picker-shell {
            display: grid;
            gap: 18px;
            padding: 20px;
            background: linear-gradient(180deg, #fcfbfe 0%, #ffffff 100%);
        }
        .template-picker-shell-copy {
            padding: 18px 20px;
            border: 1px solid #ece2f5;
            border-radius: 18px;
            background: linear-gradient(135deg, #fbf9fd 0%, #ffffff 100%);
            color: #475569;
            line-height: 1.6;
        }
        .template-picker-grid {
            display: grid;
            gap: 14px;
        }
        .template-picker-card {
            display: grid;
            grid-template-columns: minmax(180px, 220px) minmax(0, 1fr);
            gap: 18px;
            padding: 16px 18px;
            border: 1px solid #d9c8ed;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(36, 14, 53, 0.06);
            transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        .template-picker-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(36, 14, 53, 0.1);
        }
        .template-picker-card.is-selected {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.14), 0 16px 32px rgba(36, 14, 53, 0.1);
        }
        .template-picker-thumb {
            width: 100%;
            min-height: 148px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #ece2f5;
            background: linear-gradient(135deg, #f8f5fb 0%, #fbfdff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .template-picker-thumb img {
            width: 100%;
            height: 100%;
            max-height: 220px;
            object-fit: cover;
            display: block;
        }
        .template-picker-thumb-placeholder {
            padding: 18px;
            text-align: center;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.5;
        }
        .template-picker-card-copy {
            min-width: 0;
            display: grid;
            gap: 12px;
            align-content: start;
        }
        .template-picker-card-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .template-purpose-switch {
            display: inline-flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .template-purpose-chip {
            padding: 10px 14px;
            border: 1px solid #d9c8ed;
            border-radius: 999px;
            background: #fff;
            color: #5b4a74;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .template-purpose-chip:hover {
            border-color: #b89cdb;
            color: #240E35;
        }
        .template-purpose-chip.is-active {
            border-color: #240E35;
            background: #240E35;
            color: #fff;
            box-shadow: 0 10px 22px rgba(36, 14, 53, 0.18);
        }
        @media (max-width: 720px) {
            .template-modal-overlay {
                padding: 14px;
            }
            .template-modal-box {
                width: min(100vw - 12px, 980px);
                max-height: calc(100vh - 20px);
                border-radius: 18px;
            }
            .template-picker-card {
                grid-template-columns: 1fr;
            }
            .template-picker-thumb {
                min-height: 180px;
            }
            .template-picker-shell {
                padding: 14px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Create Funnel</h1>
    </div>

    <div class="card" style="max-width: 820px; margin: 0 auto;">
        <form method="POST" action="{{ route('funnels.store') }}">
            @csrf

            @php
                $selectedTemplateId = (string) old('template_id', '');
                $templateCount = is_countable($availableTemplates ?? null) ? count($availableTemplates) : 0;
            @endphp

            @if(!empty($templateAccessSummary))
                <div style="margin-bottom: 18px; padding: 14px 16px; border-radius: 12px; background: #fbf9fd; border: 1px solid #ece2f5; color: #475569; line-height: 1.55;">
                    <strong style="display:block; margin-bottom:6px; color:#240E35;">Shared template access</strong>
                    @if(!empty($templateAccessSummary['is_unlimited']))
                        Your {{ $templateAccessSummary['plan_name'] }} plan can use the full Super Admin template library.
                    @else
                        Your {{ $templateAccessSummary['plan_name'] }} plan can use up to {{ $templateAccessSummary['limit'] }} Super Admin shared template{{ (int) ($templateAccessSummary['limit'] ?? 0) === 1 ? '' : 's' }}.
                    @endif
                    Templates shown below already reflect what your current subscription can access.
                </div>
            @endif

            <div style="margin-bottom: 16px;">
                <label for="name" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Name</label>
                <input id="name" name="name" type="text" required value="{{ old('name') }}"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">
                @error('name')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="description" style="display:block; margin-bottom:8px; font-weight:700;">Description</label>
                <textarea id="description" name="description" rows="4"
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px;">{{ old('description') }}</textarea>
                @error('description')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Funnel Style</label>
                <input type="hidden" name="template_type" value="step_by_step">
                <div style="padding:12px 14px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fbf9fd; font-weight:700; color:#240E35;">
                    Step-by-Step Page
                </div>
                @error('template_type')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 16px;">
                <label for="funnel_purpose" style="display:block; margin-bottom:8px; font-weight:700;">Funnel Purpose</label>
                <select id="funnel_purpose" name="funnel_purpose" required
                    style="width:100%; padding:10px; border:1px solid var(--theme-border, #E6E1EF); border-radius:6px; background:#fff;">
                    @foreach(($funnelPurposeOptions ?? []) as $value => $label)
                        <option value="{{ $value }}" {{ old('funnel_purpose', 'service') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <div style="margin-top:6px; color:#64748b; font-size:12px;">
                    Choose whether this funnel is for Services or Physical Product sales.
                </div>
                @error('funnel_purpose')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <input type="hidden" id="template_id" name="template_id" value="{{ $selectedTemplateId }}">

            <div style="margin-bottom: 18px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Starting Point</label>
                <div style="margin-bottom: 12px; color:#64748b; font-size:13px; line-height:1.6;">
                    Use `Create Funnel` to start from scratch, or open the published Super Admin template library, choose a matching template, then click `Create Funnel` to build your private draft from it.
                </div>

                <div id="templateSelectionNotice" style="display:none; margin-bottom:12px; padding:10px 12px; border-radius:10px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; font-size:13px; font-weight:600;">
                    The selected template does not match the current funnel purpose, so the selection was cleared.
                </div>

                <div style="display:grid; gap:12px;">
                    <button type="button"
                        id="openTemplatePickerButton"
                        aria-controls="templatePickerModal"
                        aria-haspopup="dialog"
                        style="justify-self:start; padding:14px 18px; border:1px solid #d9c8ed; border-radius:12px; background:#fff; color:#240E35; font-weight:800; cursor:pointer;">
                        Select Published Template
                    </button>

                    <div id="selectedTemplateSummary" style="display:{{ $selectedTemplateId !== '' ? 'block' : 'none' }}; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; line-height:1.6;">
                        <strong style="display:block; margin-bottom:6px; color:#240E35;">Selected template</strong>
                        <span id="selectedTemplateSummaryText">No template selected yet.</span>
                    </div>

                    @if($templateCount < 1)
                        <div style="padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px dashed #d9c8ed; color:#64748b; font-size:13px; line-height:1.6;">
                            No published shared templates are currently available on your plan. You can still create a funnel from scratch.
                        </div>
                    @endif
                </div>

                @error('template_id')
                    <span style="color:red; font-size:12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin:18px 0; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px solid #ece2f5; color:#475569; font-size:13px; line-height:1.55;">
                Build setup:
                <br><strong>Funnel Style</strong>: Step-by-Step Page.
                <br><strong>Funnel Purpose</strong>: Services or Physical Product.
                <br><strong>Starting Point</strong>: Use `Create Funnel` for a blank funnel, or open the template picker, preview a matching published template, select it, then click `Create Funnel` to create your own draft funnel copy.
                <br><strong>Go Live</strong>: After you customize your draft, publish your own funnel to make it live for leads and customers.
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn-create">Create Funnel</button>
                <a href="{{ route('funnels.index') }}" style="padding:10px 16px; border-radius:6px; text-decoration:none; background:var(--theme-primary-dark, #2E1244); color:#fff; font-weight:700;">Cancel</a>
            </div>
        </form>
    </div>

    @if($templateCount > 0)
        <div id="templatePickerModal" class="template-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="templatePickerTitle">
            <div class="template-modal-box" style="padding:0;">
                <div class="template-modal-scroll">
                <div style="padding:18px 20px; border-bottom:1px solid var(--theme-border, #E6E1EF); display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <div>
                        <h3 id="templatePickerTitle" style="margin:0; color:#240E35;">Published Templates</h3>
                        <p style="margin:6px 0 0; color:#64748b; line-height:1.6;">
                            Choose a published template in this popup, then return to the form and click `Create Funnel` when you are ready.
                        </p>
                    </div>
                    <button type="button" id="closeTemplatePickerButton"
                        style="padding:10px 14px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; color:#240E35; font-weight:700; cursor:pointer;">
                        Close
                    </button>
                </div>

                <div class="template-picker-shell">
                    <div class="template-picker-shell-copy">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;">
                            <strong style="color:#240E35;">Browse step-by-step templates by funnel purpose</strong>
                            <span id="templatePickerMeta" style="color:#64748b; font-size:12px; font-weight:700;">
                                {{ $templateCount }} template{{ $templateCount === 1 ? '' : 's' }} available on your current plan
                            </span>
                        </div>
                        <div style="margin-top:8px; font-size:13px;">
                            Use the filter below to switch between Services and Physical Product templates. Each card includes the thumbnail, template name, preview, and select action.
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-top:14px;">
                            <span style="padding:7px 10px; border-radius:999px; background:#f5effb; color:#4c1d95; font-size:11px; font-weight:800; letter-spacing:.06em;">
                                Step-by-Step Page
                            </span>
                            <div class="template-purpose-switch" role="tablist" aria-label="Filter templates by funnel purpose">
                                @foreach(($funnelPurposeOptions ?? []) as $value => $label)
                                    <button type="button"
                                        data-template-purpose-filter
                                        data-purpose="{{ $value }}"
                                        class="template-purpose-chip{{ old('funnel_purpose', 'service') === $value ? ' is-active' : '' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="templatePickerGrid" class="template-picker-grid">
                        @foreach(($availableTemplates ?? []) as $template)
                            @php
                                $templateId = (string) ($template['id'] ?? '');
                                $templatePurpose = (string) ($template['funnel_purpose'] ?? 'service');
                                $templateTags = is_array($template['tags'] ?? null) ? $template['tags'] : [];
                                $templateSteps = is_array($template['steps'] ?? null) ? $template['steps'] : [];
                                $previewImage = (string) ($template['preview_image'] ?? '');
                            @endphp
                            <div
                                data-template-picker-card
                                data-template-id="{{ $templateId }}"
                                data-template-name="{{ e((string) ($template['name'] ?? 'Shared Template')) }}"
                                data-template-purpose="{{ $templatePurpose }}"
                                data-template-description="{{ e((string) ($template['description'] ?? 'Published Super Admin funnel template.')) }}"
                                class="template-picker-card">
                                <div class="template-picker-thumb">
                                    @if($previewImage !== '')
                                        <img src="{{ $previewImage }}" alt="{{ $template['name'] ?? 'Template preview' }}">
                                    @else
                                        <div class="template-picker-thumb-placeholder">
                                            No thumbnail uploaded yet for this published template.
                                        </div>
                                    @endif
                                </div>
                                <div class="template-picker-card-copy">
                                    <div>
                                        <strong style="display:block; color:#240E35; font-size:16px;">{{ $template['name'] ?? 'Shared Template' }}</strong>
                                        <span style="display:block; margin-top:6px; color:#64748b; font-size:13px; line-height:1.55;">
                                            {{ $template['description'] ?: 'Published Super Admin funnel template.' }}
                                        </span>
                                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
                                            <span style="padding:6px 10px; border-radius:999px; background:#f5effb; color:#4c1d95; font-size:11px; font-weight:800; letter-spacing:.06em;">
                                                {{ ($funnelPurposeOptions[$templatePurpose] ?? 'Services') }}
                                            </span>
                                            <span style="padding:6px 10px; border-radius:999px; background:#eef2ff; color:#3730a3; font-size:11px; font-weight:800; letter-spacing:.06em;">
                                                {{ (int) ($template['steps_count'] ?? 0) }} page{{ (int) ($template['steps_count'] ?? 0) === 1 ? '' : 's' }}
                                            </span>
                                            @foreach($templateTags as $tag)
                                                <span style="padding:6px 10px; border-radius:999px; background:#f8fafc; color:#475569; font-size:11px; font-weight:700;">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="template-picker-card-actions">
                                        <button type="button"
                                            data-template-preview-button
                                            data-template-id="{{ $templateId }}"
                                            style="padding:10px 14px; border:1px solid #d9c8ed; border-radius:10px; background:#fff; color:#240E35; font-weight:700; cursor:pointer;">
                                            Preview
                                        </button>
                                        <button type="button"
                                            data-template-select-button
                                            data-template-id="{{ $templateId }}"
                                            style="padding:10px 14px; border:none; border-radius:10px; background:var(--theme-primary, #240E35); color:#fff; font-weight:800; cursor:pointer;">
                                            Select Template
                                        </button>
                                    </div>
                                </div>

                                <div style="display:none;">
                                    <div data-template-preview-image>{{ $previewImage }}</div>
                                    <div data-template-preview-steps>@json($templateSteps)</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="templatePickerEmptyState" style="display:none; padding:14px 16px; border-radius:12px; background:#fbf9fd; border:1px dashed #d9c8ed; color:#64748b; font-size:13px; line-height:1.6;">
                        No published shared templates currently match this funnel purpose on your plan. Try switching the filter above.
                    </div>
                </div>
                </div>
            </div>
        </div>

        <div id="templatePreviewModal" class="template-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="templatePreviewTitle">
            <div class="template-modal-box" style="max-width:860px; width:min(860px, calc(100vw - 32px)); padding:0;">
                <div style="padding:18px 20px; border-bottom:1px solid var(--theme-border, #E6E1EF); display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <div>
                        <h3 id="templatePreviewTitle" style="margin:0; color:#240E35;">Template Preview</h3>
                        <p id="templatePreviewDescription" style="margin:6px 0 0; color:#64748b; line-height:1.6;"></p>
                    </div>
                    <button type="button" id="closeTemplatePreviewButton"
                        style="padding:10px 14px; border:1px solid var(--theme-border, #E6E1EF); border-radius:10px; background:#fff; color:#240E35; font-weight:700; cursor:pointer;">
                        Close
                    </button>
                </div>

                <div style="padding:20px; display:grid; gap:18px;">
                    <div id="templatePreviewImageWrap" style="display:none; border:1px solid var(--theme-border, #E6E1EF); border-radius:16px; overflow:hidden; background:#f8fafc;">
                        <img id="templatePreviewImage" src="" alt="Template preview" style="display:block; width:100%; height:auto; max-height:420px; object-fit:cover;">
                    </div>

                    <div>
                        <strong style="display:block; margin-bottom:10px; color:#240E35;">Included pages</strong>
                        <div id="templatePreviewSteps" style="display:grid; gap:10px;"></div>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:10px; flex-wrap:wrap;">
                        <span style="margin-right:auto; color:#64748b; font-size:12px; line-height:1.5;">
                            Selecting this template adds it to the form. Click `Create Funnel` after this to create your private draft copy.
                        </span>
                        <button type="button" id="templatePreviewSelectButton"
                            style="padding:12px 16px; border:none; border-radius:10px; background:var(--theme-primary, #240E35); color:#fff; font-weight:800; cursor:pointer;">
                            Use This Template
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    <script>
        (function () {
            var form = document.querySelector('form[action="{{ route('funnels.store') }}"]');
            var nameInput = document.getElementById('name');
            var descriptionInput = document.getElementById('description');
            var purposeSelect = document.getElementById('funnel_purpose');
            var templateInput = document.getElementById('template_id');
            var openTemplatePickerButton = document.getElementById('openTemplatePickerButton');
            var selectedTemplateSummary = document.getElementById('selectedTemplateSummary');
            var selectedTemplateSummaryText = document.getElementById('selectedTemplateSummaryText');
            var notice = document.getElementById('templateSelectionNotice');
            var templatePickerModal = document.getElementById('templatePickerModal');
            var closeTemplatePickerButton = document.getElementById('closeTemplatePickerButton');
            var templatePickerCards = Array.prototype.slice.call(document.querySelectorAll('[data-template-picker-card]'));
            var templatePreviewButtons = Array.prototype.slice.call(document.querySelectorAll('[data-template-preview-button]'));
            var templateSelectButtons = Array.prototype.slice.call(document.querySelectorAll('[data-template-select-button]'));
            var templatePurposeFilterButtons = Array.prototype.slice.call(document.querySelectorAll('[data-template-purpose-filter]'));
            var templatePickerEmptyState = document.getElementById('templatePickerEmptyState');
            var templatePickerMeta = document.getElementById('templatePickerMeta');
            var templatePreviewModal = document.getElementById('templatePreviewModal');
            var closeTemplatePreviewButton = document.getElementById('closeTemplatePreviewButton');
            var templatePreviewTitle = document.getElementById('templatePreviewTitle');
            var templatePreviewDescription = document.getElementById('templatePreviewDescription');
            var templatePreviewImageWrap = document.getElementById('templatePreviewImageWrap');
            var templatePreviewImage = document.getElementById('templatePreviewImage');
            var templatePreviewSteps = document.getElementById('templatePreviewSteps');
            var templatePreviewSelectButton = document.getElementById('templatePreviewSelectButton');
            var previewTemplateId = '';

            if (!purposeSelect || !templateInput || !form) {
                return;
            }

            function templateCardById(templateId) {
                var id = String(templateId || '');
                return templatePickerCards.find(function (card) {
                    return String(card.getAttribute('data-template-id') || '') === id;
                });
            }

            function updateSelectedTemplateSummary() {
                var selectedId = String(templateInput.value || '');
                if (!selectedTemplateSummary || !selectedTemplateSummaryText) {
                    return;
                }

                if (!selectedId) {
                    selectedTemplateSummary.style.display = 'none';
                    selectedTemplateSummaryText.textContent = 'No template selected yet.';
                    return;
                }

                var card = templateCardById(selectedId);
                if (!card) {
                    selectedTemplateSummary.style.display = 'none';
                    selectedTemplateSummaryText.textContent = 'No template selected yet.';
                    return;
                }

                selectedTemplateSummary.style.display = 'block';
                selectedTemplateSummaryText.textContent = String(card.getAttribute('data-template-name') || 'Selected template') + ' is selected for ' + currentPurpose().replace('_', ' ') + '. Click Create Funnel to build your draft from this template.';
            }

            function syncTemplateCardSelectionState() {
                var selectedId = String(templateInput.value || '');
                templatePickerCards.forEach(function (card) {
                    var isSelected = String(card.getAttribute('data-template-id') || '') === selectedId;
                    card.classList.toggle('is-selected', isSelected);
                });
            }

            function syncPurposeFilterState() {
                var purpose = currentPurpose();
                templatePurposeFilterButtons.forEach(function (button) {
                    var isActive = String(button.getAttribute('data-purpose') || '') === purpose;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            }

            function selectTemplate(templateId, options) {
                options = options || {};
                templateInput.value = String(templateId || '');
                updateSelectedTemplateSummary();
                syncTemplateCardSelectionState();

                if (!options.silent && notice) {
                    notice.style.display = 'none';
                }
            }

            function currentPurpose() {
                return String(purposeSelect.value || 'service');
            }

            function selectedTemplateMatchesPurpose() {
                var selectedId = String(templateInput.value || '');
                if (!selectedId) {
                    return true;
                }

                var selectedCard = templateCardById(selectedId);

                if (!selectedCard) {
                    return false;
                }

                return String(selectedCard.getAttribute('data-template-purpose') || '') === currentPurpose();
            }

            function updateTemplateVisibility() {
                var purpose = currentPurpose();
                var visibleCount = 0;

                syncPurposeFilterState();

                templatePickerCards.forEach(function (card) {
                    var matches = String(card.getAttribute('data-template-purpose') || '') === purpose;
                    card.style.display = matches ? '' : 'none';
                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (templatePickerEmptyState) {
                    templatePickerEmptyState.style.display = visibleCount > 0 ? 'none' : '';
                }
                if (templatePickerMeta) {
                    templatePickerMeta.textContent = visibleCount + ' matching template' + (visibleCount === 1 ? '' : 's') + ' for this funnel purpose';
                }

                if (!selectedTemplateMatchesPurpose()) {
                    selectTemplate('', { silent: true });
                    if (notice) {
                        notice.style.display = '';
                    }
                } else if (notice) {
                    notice.style.display = 'none';
                }
            }

            function openModal(modal) {
                if (modal) {
                    document.body.style.overflow = 'hidden';
                    modal.style.display = 'flex';
                }
            }

            function closeModal(modal) {
                if (modal) {
                    modal.style.display = 'none';
                    if (!templatePickerModal || templatePickerModal.style.display === 'none') {
                        if (!templatePreviewModal || templatePreviewModal.style.display === 'none') {
                            document.body.style.overflow = '';
                        }
                    }
                }
            }

            function humanizeStepType(value) {
                return String(value || '')
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, function (char) { return char.toUpperCase(); });
            }

            function renderPreview(templateId) {
                var card = templateCardById(templateId);
                if (!card || !templatePreviewTitle || !templatePreviewDescription || !templatePreviewSteps) {
                    return;
                }

                previewTemplateId = String(templateId || '');
                templatePreviewTitle.textContent = String(card.getAttribute('data-template-name') || 'Template Preview');
                templatePreviewDescription.textContent = String(card.getAttribute('data-template-description') || '');

                var previewImageValue = '';
                var previewImageNode = card.querySelector('[data-template-preview-image]');
                if (previewImageNode) {
                    previewImageValue = String(previewImageNode.textContent || '').trim();
                }

                if (templatePreviewImageWrap && templatePreviewImage) {
                    if (previewImageValue) {
                        templatePreviewImage.src = previewImageValue;
                        templatePreviewImageWrap.style.display = '';
                    } else {
                        templatePreviewImage.removeAttribute('src');
                        templatePreviewImageWrap.style.display = 'none';
                    }
                }

                var steps = [];
                var stepsNode = card.querySelector('[data-template-preview-steps]');
                if (stepsNode) {
                    try {
                        steps = JSON.parse(stepsNode.textContent || '[]');
                    } catch (_error) {
                        steps = [];
                    }
                }

                templatePreviewSteps.innerHTML = steps.map(function (step, index) {
                    return '<div style="padding:12px 14px;border:1px solid var(--theme-border, #E6E1EF);border-radius:12px;background:#fff;">'
                        + '<strong style="display:block;color:#240E35;">Page ' + String(index + 1) + ': ' + String(step && step.title || 'Untitled') + '</strong>'
                        + '<span style="display:block;margin-top:4px;color:#64748b;font-size:12px;font-weight:700;">'
                        + humanizeStepType(step && step.type || 'custom')
                        + '</span>'
                        + '</div>';
                }).join('') || '<div style="padding:12px 14px;border:1px dashed var(--theme-border, #E6E1EF);border-radius:12px;background:#fff;color:#64748b;">No preview pages available.</div>';

                openModal(templatePreviewModal);
            }

            function applySelectedTemplate(templateId) {
                var card = templateCardById(templateId);
                if (!card) {
                    return;
                }

                selectTemplate(templateId);

                if (nameInput && !String(nameInput.value || '').trim()) {
                    nameInput.value = String(card.getAttribute('data-template-name') || 'New Funnel');
                }

                if (descriptionInput && !String(descriptionInput.value || '').trim()) {
                    descriptionInput.value = String(card.getAttribute('data-template-description') || '');
                }

                closeModal(templatePreviewModal);
                closeModal(templatePickerModal);
            }

            if (openTemplatePickerButton) {
                openTemplatePickerButton.addEventListener('click', function () {
                    updateTemplateVisibility();
                    openModal(templatePickerModal);
                });
            }

            if (closeTemplatePickerButton) {
                closeTemplatePickerButton.addEventListener('click', function () {
                    closeModal(templatePickerModal);
                });
            }

            if (closeTemplatePreviewButton) {
                closeTemplatePreviewButton.addEventListener('click', function () {
                    closeModal(templatePreviewModal);
                });
            }

            if (templatePreviewSelectButton) {
                templatePreviewSelectButton.addEventListener('click', function () {
                    if (previewTemplateId) {
                        applySelectedTemplate(previewTemplateId);
                    }
                });
            }

            templatePreviewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    renderPreview(button.getAttribute('data-template-id') || '');
                });
            });

            templateSelectButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    applySelectedTemplate(button.getAttribute('data-template-id') || '');
                });
            });

            templatePurposeFilterButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var purpose = String(button.getAttribute('data-purpose') || '');
                    if (!purpose || purposeSelect.value === purpose) {
                        return;
                    }

                    purposeSelect.value = purpose;
                    updateTemplateVisibility();
                });
            });

            [templatePickerModal, templatePreviewModal].forEach(function (modal) {
                if (!modal) {
                    return;
                }

                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        closeModal(modal);
                    }
                });
            });

            purposeSelect.addEventListener('change', updateTemplateVisibility);

            updateTemplateVisibility();
            updateSelectedTemplateSummary();
            syncTemplateCardSelectionState();
        })();
    </script>
@endsection
