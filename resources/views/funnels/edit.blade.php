    @extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('content')
    <style>
        /* Small animations for builder layout toggles */
        #builderTopGrid {
            transition: grid-template-columns 320ms ease;
            grid-template-columns: 1fr 1fr;
        }
        #builderTopGrid.is-fullwidth {
            grid-template-columns: 0fr 1fr;
        }
        #funnelSettingsCard {
            transition: max-height 240ms ease, opacity 180ms ease, transform 240ms ease, margin 240ms ease, padding 240ms ease, border-width 240ms ease;
            max-height: 900px;
            opacity: 1;
            transform: translateY(0);
        }
        #funnelSettingsCard.is-collapsed {
            max-height: 0 !important;
            opacity: 0 !important;
            transform: translateY(-10px) !important;
            overflow: hidden !important;
            pointer-events: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border-width: 0 !important;
        }
        #addNewStepCard {
            transition: transform 220ms ease, box-shadow 220ms ease;
        }
        #builderTopGrid.is-fullwidth #addNewStepCard {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.10);
        }

        /* Make form labels clearer/darker inside builder cards */
        #builderTopGrid label {
            color: #0F172A;
        }

        /* Consistent select styling */
        .funnel-select {
            padding: 8px 10px;
            border: 1px solid #DBEAFE;
            border-radius: 6px;
            height: 40px;
            background-color: #ffffff;
            display: block;
            margin: 0;
        }

        /* Consistent file input height (align with selects) */
        .funnel-file {
            height: 40px;
            padding: 7px 10px;
            border: 1px solid #DBEAFE;
            border-radius: 6px;
            background: #ffffff;
            width: 100%;
            display: block;
            margin: 0;
            line-height: 24px;
            vertical-align: middle;
        }
    </style>

    <div class="top-header">
        <h1>Builder: {{ $funnel->name }}</h1>
        @if($funnel->status === 'published')
            <a class="btn-create" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}" target="_blank">
                <i class="fas fa-up-right-from-square"></i> Open Public Funnel
            </a>
        @endif
    </div>

    <div id="builderTopGrid" style="display:grid; gap: 16px;">
        <div id="funnelSettingsCard" class="card">
            <h3>Funnel Settings</h3>
            <form method="POST" action="{{ route('funnels.update', $funnel) }}">
                @csrf
                @method('PUT')
                <div style="margin-bottom:12px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Name</label>
                    <input type="text" name="name" value="{{ old('name', $funnel->name) }}" required
                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Description</label>
                    <textarea name="description" rows="3"
                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">{{ old('description', $funnel->description) }}</textarea>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Status</label>
                    <select name="status" class="funnel-select" style="width:100%;">
                        <option value="draft" {{ old('status', $funnel->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $funnel->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <button type="submit" class="btn-create">Save Funnel</button>
            </form>
        </div>

        <div id="addNewStepCard" class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                <h3 style="margin:0;">Add New Step</h3>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button type="button"
                        onclick="openNewStepPreviewModal()"
                        style="padding:8px 12px; border:1px solid #CBD5E1; border-radius:6px; cursor:pointer; font-weight:700; background:#FFFFFF; color:#0F172A;">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button id="btnAddStepFullWidth" type="button" class="btn-create" style="padding:8px 12px; font-weight:700;">
                        Full width
                    </button>
                    <button id="btnAddStepSplitView" type="button"
                        style="display:none; padding:8px 12px; border:none; border-radius:6px; cursor:pointer; font-weight:700; background:#334155; color:#fff;">
                        Back to split view
                    </button>
                </div>
            </div>
            <form id="newStepForm" method="POST" action="{{ route('funnels.steps.store', $funnel) }}" enctype="multipart/form-data">
                @csrf
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Title</label>
                        <input type="text" name="title" required style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Slug</label>
                        <input type="text" name="slug" required placeholder="step-slug" style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Subtitle (optional)</label>
                    <input type="text" name="subtitle" maxlength="160" placeholder="One short line under the title"
                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Type</label>
                        <select name="type" required class="funnel-select" style="width:100%;">
                            @foreach($stepTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Price (checkout/upsell/downsell)</label>
                        <input type="number" name="price" step="0.01" min="0.01" placeholder="Optional"
                            style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Template</label>
                    <select name="template" data-template-select="new" class="funnel-select"
                        style="width:100%;">
                        @foreach($stepTemplates as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Hero image (optional)</label>
                        <input type="file" name="hero_image" accept="image/*" class="funnel-file">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Layout</label>
                        <select name="layout_style" class="funnel-select" style="width:100%;">
                            @foreach($stepLayouts as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:10px; padding:10px; border:1px solid #DBEAFE; border-radius:8px; background:#F8FAFC;">
                    <div style="font-weight:800; color:#0F172A; margin-bottom:8px;">Design (optional)</div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div>
                            <label style="display:block; margin-bottom:6px; font-weight:700;">Background color</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="color" value="#ffffff" data-color-proxy="newStepBg"
                                    style="width:44px; height:40px; padding:0; border:1px solid #DBEAFE; border-radius:6px; background:#fff;">
                                <input type="text" name="background_color" placeholder="#RRGGBB (blank = default)" maxlength="7"
                                    style="flex:1; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                            </div>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:6px; font-weight:700;">Button color</label>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="color" value="#2563eb" data-color-proxy="newStepBtn"
                                    style="width:44px; height:40px; padding:0; border:1px solid #DBEAFE; border-radius:6px; background:#fff;">
                                <input type="text" name="button_color" placeholder="#RRGGBB (blank = theme)" maxlength="7"
                                    style="flex:1; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">CTA Label</label>
                    <input type="text" name="cta_label" placeholder="Optional button text"
                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                </div>
                <div style="margin-top:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Content</label>
                    <textarea name="content" rows="4" style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;"></textarea>
                </div>

                {{-- Template sections for Add New Step --}}
                <div data-template-sections="new" style="margin-top:10px;">
                    <div data-template-show="hero_features,sales_long" style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                        <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Features (3)</div>
                        @for($i=0;$i<3;$i++)
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                                <input type="text" name="template_data[features][{{ $i }}][title]" placeholder="Feature {{ $i+1 }} title"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                <input type="text" name="template_data[features][{{ $i }}][body]" placeholder="Feature {{ $i+1 }} description"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                            </div>
                        @endfor
                    </div>

                    <div data-template-show="lead_capture" style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                        <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Lead capture bullets</div>
                        @for($i=0;$i<3;$i++)
                            <input type="text" name="template_data[bullets][{{ $i }}]" placeholder="Bullet {{ $i+1 }}"
                                style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px; margin-bottom:8px;">
                        @endfor
                        <input type="text" name="template_data[form_title]" placeholder="Form title (optional)"
                            style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                    </div>

                    <div data-template-show="sales_long" style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC; margin-top:10px;">
                        <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">FAQ (3)</div>
                        @for($i=0;$i<3;$i++)
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                                <input type="text" name="template_data[faq][{{ $i }}][q]" placeholder="Question {{ $i+1 }}"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                <input type="text" name="template_data[faq][{{ $i }}][a]" placeholder="Answer {{ $i+1 }}"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                            </div>
                        @endfor
                        <div style="font-weight:900; color:#0F172A; margin:10px 0 8px;">Testimonials (2)</div>
                        @for($i=0;$i<2;$i++)
                            <div style="display:grid; grid-template-columns:1fr 220px; gap:10px; margin-bottom:8px;">
                                <input type="text" name="template_data[testimonials][{{ $i }}][quote]" placeholder="Quote {{ $i+1 }}"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                <input type="text" name="template_data[testimonials][{{ $i }}][name]" placeholder="Name {{ $i+1 }}"
                                    style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                            </div>
                        @endfor
                    </div>

                    <div data-template-show="thank_you_next" style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                        <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Next steps (3)</div>
                        @for($i=0;$i<3;$i++)
                            <input type="text" name="template_data[next_steps][{{ $i }}]" placeholder="Next step {{ $i+1 }}"
                                style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px; margin-bottom:8px;">
                        @endfor
                    </div>
                </div>
                <button type="submit" class="btn-create" style="margin-top:10px;">Add Step</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <h3>Step Order (Drag and Drop)</h3>
            <form id="reorderForm" method="POST" action="{{ route('funnels.steps.reorder', $funnel) }}">
                @csrf
                <button type="submit" class="btn-create">Save Order</button>
            </form>
        </div>
        <p style="font-size:12px; color:#475569; font-weight:700; margin-bottom:12px;">
            Drag cards by handle to reorder. Flow logic uses this sequence. Upsell decline routes to immediate next downsell if present.
        </p>

        <div id="stepList" style="display:grid; gap:12px;">
            @forelse($funnel->steps as $step)
                <div class="step-card" draggable="true" data-step-id="{{ $step->id }}"
                    style="border:1px solid #DBEAFE; border-radius:8px; padding:12px; background:#fff;">
                    <div style="display:flex; justify-content:space-between; gap:12px;">
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                                <span class="drag-handle" title="Drag" style="cursor:grab; color:#64748B;"><i class="fas fa-grip-vertical"></i></span>
                                <strong>{{ $step->title }}</strong>
                                <span style="font-size:11px; padding:2px 8px; border-radius:999px; background:#EFF6FF; color:#1E40AF; font-weight:700;">{{ $stepTypes[$step->type] ?? $step->type }}</span>
                                @if(!$step->is_active)
                                    <span style="font-size:11px; padding:2px 8px; border-radius:999px; background:#FEF2F2; color:#B91C1C; font-weight:700;">Inactive</span>
                                @endif
                            </div>
                            <div style="font-size:12px; color:#64748B; font-weight:700; margin-bottom:8px;">
                                Slug: {{ $step->slug }} | Position: {{ $step->position }} | Price: {{ $step->price ? number_format((float) $step->price, 2) : 'N/A' }}
                            </div>
                            <div style="font-size:13px; color:#334155;">{{ $step->content ?: 'No content yet.' }}</div>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <button type="button"
                                onclick="openStepPreviewModal({{ $step->id }})"
                                style="background:none;border:none;color:#2563EB;cursor:pointer;font-weight:700;text-align:left;padding:0;">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" onclick="toggleStepForm({{ $step->id }})"
                                style="background:none;border:none;color:#1E40AF;cursor:pointer;font-weight:700;text-align:left;">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                        </div>
                    </div>

                    <form id="stepForm{{ $step->id }}" method="POST" action="{{ route('funnels.steps.update', [$funnel, $step]) }}"
                        enctype="multipart/form-data"
                        style="display:none; margin-top:10px; padding-top:10px; border-top:1px dashed #DBEAFE;">
                        @csrf
                        @method('PUT')
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <input type="text" name="title" value="{{ $step->title }}" required style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                            <input type="text" name="slug" value="{{ $step->slug }}" required style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                        </div>
                        <input type="text" name="subtitle" value="{{ $step->subtitle }}" maxlength="160" placeholder="Subtitle (optional)"
                            style="margin-top:8px; width:100%; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <select name="type" required class="funnel-select">
                                @foreach($stepTypes as $value => $label)
                                    <option value="{{ $value }}" {{ $step->type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="price" value="{{ $step->price }}" step="0.01" min="0.01" placeholder="Optional" style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                        </div>
                        <div style="margin-top:8px;">
                            <div style="font-size:12px; font-weight:800; color:#334155; margin-bottom:6px;">Template</div>
                            <select name="template" data-template-select="{{ $step->id }}"
                                class="funnel-select" style="width:100%;">
                                @foreach($stepTemplates as $value => $label)
                                    <option value="{{ $value }}" {{ ($step->template ?: 'simple') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <div>
                                <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700; color:#334155;">Hero image</label>
                                <input type="file" name="hero_image" accept="image/*" class="funnel-file">
                                <input type="hidden" name="hero_image_url" value="{{ $step->hero_image_url }}">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:4px; font-size:12px; font-weight:700; color:#334155;">Layout</label>
                                <select name="layout_style" class="funnel-select" style="width:100%;">
                                    @foreach($stepLayouts as $value => $label)
                                        <option value="{{ $value }}" {{ ($step->layout_style ?: 'centered') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <div>
                                <div style="font-size:12px; font-weight:800; color:#334155; margin-bottom:6px;">Background</div>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="color" value="{{ $step->background_color ?: '#ffffff' }}" data-color-proxy="bg{{ $step->id }}"
                                        style="width:44px; height:38px; padding:0; border:1px solid #DBEAFE; border-radius:6px; background:#fff;">
                                    <input type="text" name="background_color" value="{{ $step->background_color }}" placeholder="#RRGGBB (blank = default)" maxlength="7"
                                        style="flex:1; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">
                                </div>
                            </div>
                            <div>
                                <div style="font-size:12px; font-weight:800; color:#334155; margin-bottom:6px;">Button</div>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <input type="color" value="{{ $step->button_color ?: '#2563eb' }}" data-color-proxy="btn{{ $step->id }}"
                                        style="width:44px; height:38px; padding:0; border:1px solid #DBEAFE; border-radius:6px; background:#fff;">
                                    <input type="text" name="button_color" value="{{ $step->button_color }}" placeholder="#RRGGBB (blank = theme)" maxlength="7"
                                        style="flex:1; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">
                                </div>
                            </div>
                        </div>
                        <input type="text" name="cta_label" value="{{ $step->cta_label }}" placeholder="CTA Label"
                            style="margin-top:8px; width:100%; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">
                        <textarea name="content" rows="3" style="margin-top:8px; width:100%; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">{{ $step->content }}</textarea>

                        {{-- Template sections for Edit Step --}}
                        @php $td = $step->template_data ?? []; @endphp
                        <div data-template-sections="{{ $step->id }}" style="margin-top:10px;">
                            <div data-template-show="hero_features,sales_long"
                                 style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                                <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Features (3)</div>
                                @for($i=0;$i<3;$i++)
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                                        <input type="text" name="template_data[features][{{ $i }}][title]" value="{{ $td['features'][$i]['title'] ?? '' }}" placeholder="Feature {{ $i+1 }} title"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                        <input type="text" name="template_data[features][{{ $i }}][body]" value="{{ $td['features'][$i]['body'] ?? '' }}" placeholder="Feature {{ $i+1 }} description"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                    </div>
                                @endfor
                            </div>

                            <div data-template-show="lead_capture"
                                 style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                                <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Lead capture bullets</div>
                                @for($i=0;$i<3;$i++)
                                    <input type="text" name="template_data[bullets][{{ $i }}]" value="{{ $td['bullets'][$i] ?? '' }}" placeholder="Bullet {{ $i+1 }}"
                                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px; margin-bottom:8px;">
                                @endfor
                                <input type="text" name="template_data[form_title]" value="{{ $td['form_title'] ?? '' }}" placeholder="Form title (optional)"
                                    style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                            </div>

                            <div data-template-show="sales_long"
                                 style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC; margin-top:10px;">
                                <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">FAQ (3)</div>
                                @for($i=0;$i<3;$i++)
                                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:8px;">
                                        <input type="text" name="template_data[faq][{{ $i }}][q]" value="{{ $td['faq'][$i]['q'] ?? '' }}" placeholder="Question {{ $i+1 }}"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                        <input type="text" name="template_data[faq][{{ $i }}][a]" value="{{ $td['faq'][$i]['a'] ?? '' }}" placeholder="Answer {{ $i+1 }}"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                    </div>
                                @endfor
                                <div style="font-weight:900; color:#0F172A; margin:10px 0 8px;">Testimonials (2)</div>
                                @for($i=0;$i<2;$i++)
                                    <div style="display:grid; grid-template-columns:1fr 220px; gap:10px; margin-bottom:8px;">
                                        <input type="text" name="template_data[testimonials][{{ $i }}][quote]" value="{{ $td['testimonials'][$i]['quote'] ?? '' }}" placeholder="Quote {{ $i+1 }}"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                        <input type="text" name="template_data[testimonials][{{ $i }}][name]" value="{{ $td['testimonials'][$i]['name'] ?? '' }}" placeholder="Name {{ $i+1 }}"
                                            style="padding:10px; border:1px solid #DBEAFE; border-radius:8px;">
                                    </div>
                                @endfor
                            </div>

                            <div data-template-show="thank_you_next"
                                 style="display:none; padding:10px; border:1px solid #DBEAFE; border-radius:10px; background:#F8FAFC;">
                                <div style="font-weight:900; color:#0F172A; margin-bottom:8px;">Next steps (3)</div>
                                @for($i=0;$i<3;$i++)
                                    <input type="text" name="template_data[next_steps][{{ $i }}]" value="{{ $td['next_steps'][$i] ?? '' }}" placeholder="Next step {{ $i+1 }}"
                                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:8px; margin-bottom:8px;">
                                @endfor
                            </div>
                        </div>
                        <label style="display:inline-flex; align-items:center; gap:8px; margin-top:8px; font-size:12px; font-weight:700; color:#475569;">
                            <input type="checkbox" name="is_active" value="1" {{ $step->is_active ? 'checked' : '' }}> Active step
                        </label>
                        <div style="display:flex; gap:10px; margin-top:8px;">
                            <button type="submit" class="btn-create">Save Step</button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('funnels.steps.destroy', [$funnel, $step]) }}"
                        onsubmit="return confirm('Delete this step?')" style="display:none; margin-top:8px;" id="stepDeleteForm{{ $step->id }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="padding:10px 14px; border:none; border-radius:6px; background:#DC2626; color:#fff; cursor:pointer; font-weight:700;">Delete</button>
                    </form>
                </div>
            @empty
                <div style="color:#64748B; font-weight:700;">No steps yet. Add one above.</div>
            @endforelse
        </div>
    </div>

    {{-- Fullscreen step preview modal (shows unsaved edits) --}}
    <div id="stepPreviewOverlay"
         style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.92); z-index:9999; align-items:stretch; justify-content:stretch; opacity:0; transition:opacity 200ms ease;">
        <div class="preview-shell" style="background:#020617; border-radius:0; width:100%; height:100%; overflow:auto; padding:16px 18px 20px; box-shadow:none; position:relative; transform:translateY(16px); opacity:0; transition:transform 220ms ease, opacity 220ms ease;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div style="color:#e5e7eb; font-weight:700; font-size:14px;">
                    Step preview (unsaved changes)
                </div>
                <button type="button" onclick="closeStepPreviewModal()"
                        style="background:transparent; border:none; color:#e5e7eb; font-size:20px; cursor:pointer; padding:4px 8px;">
                    &times;
                </button>
            </div>
            <div id="stepPreviewModalBody" style="background:#020617; border-radius:18px; padding:20px 20px 24px; border:1px solid rgba(148,163,184,0.35);">
                {{-- Filled by JS --}}
            </div>
        </div>
    </div>

    <script>
        // Top grid: expand "Add New Step" to full width (hide Funnel Settings)
        (function () {
            var grid = document.getElementById('builderTopGrid');
            var funnelCard = document.getElementById('funnelSettingsCard');
            var btnFull = document.getElementById('btnAddStepFullWidth');
            var btnSplit = document.getElementById('btnAddStepSplitView');
            if (!grid || !funnelCard || !btnFull || !btnSplit) return;

            function setFullWidth(on) {
                if (on) {
                    grid.classList.add('is-fullwidth');
                    funnelCard.classList.add('is-collapsed');
                    btnFull.style.display = 'none';
                    btnSplit.style.display = 'inline-flex';

                    // keep focus on the Add Step form for better UX
                    try { btnSplit.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
                } else {
                    grid.classList.remove('is-fullwidth');
                    funnelCard.classList.remove('is-collapsed');
                    btnFull.style.display = 'inline-flex';
                    btnSplit.style.display = 'none';
                }
            }

            btnFull.addEventListener('click', function () { setFullWidth(true); });
            btnSplit.addEventListener('click', function () { setFullWidth(false); });
        })();

        function toggleStepForm(stepId) {
            var el = document.getElementById('stepForm' + stepId);
            var deleteEl = document.getElementById('stepDeleteForm' + stepId);
            if (!el) return;
            var show = el.style.display === 'none';
            el.style.display = show ? 'block' : 'none';
            if (deleteEl) deleteEl.style.display = show ? 'block' : 'none';
        }

        (function () {
            var list = document.getElementById('stepList');
            var reorderForm = document.getElementById('reorderForm');
            if (!list || !reorderForm) return;

            var dragged = null;
            list.querySelectorAll('.step-card').forEach(function (card) {
                card.addEventListener('dragstart', function () {
                    dragged = card;
                    card.style.opacity = '0.5';
                });
                card.addEventListener('dragend', function () {
                    card.style.opacity = '1';
                    dragged = null;
                });
                card.addEventListener('dragover', function (e) {
                    e.preventDefault();
                });
                card.addEventListener('drop', function (e) {
                    e.preventDefault();
                    if (!dragged || dragged === card) return;
                    var rect = card.getBoundingClientRect();
                    var before = (e.clientY - rect.top) < (rect.height / 2);
                    list.insertBefore(dragged, before ? card : card.nextSibling);
                });
            });

            reorderForm.addEventListener('submit', function () {
                reorderForm.querySelectorAll('input[name="order[]"]').forEach(function (node) { node.remove(); });
                list.querySelectorAll('.step-card').forEach(function (card) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'order[]';
                    input.value = card.getAttribute('data-step-id');
                    reorderForm.appendChild(input);
                });
            });
        })();

        // Add-step color pickers (proxy -> text input)
        (function () {
            var addForm = document.querySelector('form[action="{{ route('funnels.steps.store', $funnel) }}"]');
            if (!addForm) return;
            var bgProxy = addForm.querySelector('input[type="color"][data-color-proxy="newStepBg"]');
            var btnProxy = addForm.querySelector('input[type="color"][data-color-proxy="newStepBtn"]');
            var bgText = addForm.querySelector('input[name="background_color"]');
            var btnText = addForm.querySelector('input[name="button_color"]');

            function wire(proxy, text) {
                if (!proxy || !text) return;
                proxy.addEventListener('input', function () { text.value = proxy.value || ''; });
                text.addEventListener('input', function () {
                    var v = (text.value || '').trim();
                    if (/^#[0-9A-Fa-f]{6}$/.test(v)) proxy.value = v;
                });
            }
            wire(bgProxy, bgText);
            wire(btnProxy, btnText);
        })();

        // Template sections show/hide based on template select
        (function () {
            function applyTemplate(scopeId, templateVal) {
                var wrap = document.querySelector('[data-template-sections="' + scopeId + '"]');
                if (!wrap) return;
                wrap.querySelectorAll('[data-template-show]').forEach(function (sec) {
                    var list = (sec.getAttribute('data-template-show') || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                    sec.style.display = list.includes(templateVal) ? 'block' : 'none';
                });
            }

            document.querySelectorAll('[data-template-select]').forEach(function (sel) {
                var scopeId = sel.getAttribute('data-template-select');
                var fn = function () { applyTemplate(scopeId, sel.value || 'simple'); };
                sel.addEventListener('change', fn);
                fn();
            });
        })();

        // Shared: fill preview modal body based on a form's current values
        function fillStepPreviewFromForm(form, body) {
            if (!form || !body) return;

            function get(name) {
                var el = form.querySelector('[name="' + name + '"]');
                return el ? el.value || '' : '';
            }

            var type = get('type') || 'landing';
            var title = get('title') || 'Untitled step';
            var subtitle = get('subtitle') || '';
            var content = get('content') || 'No content yet.';
            var cta = get('cta_label') || 'Continue';
            var price = get('price') || '';
            var layout = get('layout_style') || 'centered';
            var heroUrl = get('hero_image_url') || '';
            var template = get('template') || 'simple';

            // If user picked a local file, show it immediately in preview
            var heroFileEl = form.querySelector('input[type="file"][name="hero_image"]');
            if (heroFileEl && heroFileEl.files && heroFileEl.files[0]) {
                try {
                    heroUrl = URL.createObjectURL(heroFileEl.files[0]);
                } catch (e) {
                    // ignore file preview errors
                }
            }

            // Read colors from text input OR color picker so modal sees what you see
            var bgTextEl = form.querySelector('input[name="background_color"]');
            var bgPickerEl = form.querySelector('input[type="color"][data-color-proxy^="bg"]');
            var rawBg = (bgTextEl && bgTextEl.value.trim()) || (bgPickerEl && bgPickerEl.value.trim()) || '';

            var btnTextEl = form.querySelector('input[name="button_color"]');
            var btnPickerEl = form.querySelector('input[type="color"][data-color-proxy^="btn"]');
            var rawBtn = (btnTextEl && btnTextEl.value.trim()) || (btnPickerEl && btnPickerEl.value.trim()) || '';

            var btnColor = rawBtn && /^#[0-9A-Fa-f]{6}$/.test(rawBtn) ? rawBtn : 'var(--theme-primary, #2563EB)';
            var bgColor = rawBg && /^#[0-9A-Fa-f]{6}$/.test(rawBg) ? rawBg : '#ffffff';

            var actionHtml = '';
            if (type === 'opt_in') {
                actionHtml = '' +
                    '<form onsubmit="return false;" style="margin-top:6px;">' +
                    '  <label>Name</label>' +
                    '  <input type="text" placeholder="Name">' +
                    '  <label>Email</label>' +
                    '  <input type="email" placeholder="Email">' +
                    '  <label>Phone (PH 09XXXXXXXXX)</label>' +
                    '  <input type="text" placeholder="09XXXXXXXXX">' +
                    '  <button type="button" class="btn-main">' + cta + '</button>' +
                    '</form>';
            } else if (type === 'checkout') {
                var priceText = price ? parseFloat(price || 0).toFixed(2) : '0.00';
                actionHtml = '' +
                    '<p class="price">PHP ' + priceText + '</p>' +
                    '<button type="button" class="btn-main">' + cta + '</button>';
            } else if (type === 'upsell' || type === 'downsell') {
                var upsellPrice = price ? parseFloat(price || 0).toFixed(2) : '0.00';
                actionHtml = '' +
                    '<p class="price">Additional Offer: PHP ' + upsellPrice + '</p>' +
                    '<div class="row">' +
                    '  <button type="button" class="btn-main">' + cta + '</button>' +
                    '  <button type="button" class="btn-secondary">No Thanks</button>' +
                    '</div>';
            } else if (type === 'thank_you') {
                actionHtml = '' +
                    '<p style="font-weight:700; color:#22c55e; margin:0 0 10px;">Flow completed successfully.</p>' +
                    '<button type="button" class="btn-secondary">Back to start</button>';
            } else {
                actionHtml = '<button type="button" class="btn-main">' + cta + '</button>';
            }

            var heroImgHtml = heroUrl
                ? '<img src="' + heroUrl + '" alt="Hero" style="width:100%; max-height:320px; object-fit:cover; border-radius:18px; border:1px solid rgba(148,163,184,0.6); margin-bottom:18px;">'
                : '';

            function getAll(selector) {
                return Array.prototype.slice.call(form.querySelectorAll(selector));
            }

            function templateExtrasHtml() {
                var html = '';

                if (template === 'hero_features' || template === 'sales_long') {
                    var feats = getAll('input[name^="template_data[features]"][name$="[title]"]').map(function (tEl) {
                        var idx = (tEl.name.match(/\[(\d+)\]\[title\]$/) || [])[1];
                        var bodyEl = idx !== undefined ? form.querySelector('input[name="template_data[features][' + idx + '][body]"]') : null;
                        return { title: (tEl.value || '').trim(), body: (bodyEl ? bodyEl.value : '').trim() };
                    }).filter(function (f) { return f.title || f.body; });

                    if (feats.length) {
                        html += '<div class="section"><div class="section-title">Features</div><div class="features">' +
                            feats.map(function (f) {
                                return '<div class="feature"><h4>' + (f.title || '') + '</h4><p>' + (f.body || '') + '</p></div>';
                            }).join('') +
                            '</div></div>';
                    }
                }

                if (template === 'lead_capture') {
                    var bullets = getAll('input[name^="template_data[bullets]"]').map(function (el) { return (el.value || '').trim(); }).filter(Boolean);
                    var formTitleEl = form.querySelector('input[name="template_data[form_title]"]');
                    var formTitle = formTitleEl ? (formTitleEl.value || '').trim() : '';
                    if (bullets.length) {
                        html += '<div class="section"><div class="section-title">' + (formTitle || 'What you’ll get') + '</div><ul class="bullets">' +
                            bullets.map(function (b) { return '<li>' + b + '</li>'; }).join('') +
                            '</ul></div>';
                    }
                }

                if (template === 'thank_you_next') {
                    var steps = getAll('input[name^="template_data[next_steps]"]').map(function (el) { return (el.value || '').trim(); }).filter(Boolean);
                    if (steps.length) {
                        html += '<div class="section"><div class="section-title">Next steps</div><ol class="next-steps">' +
                            steps.map(function (s) { return '<li>' + s + '</li>'; }).join('') +
                            '</ol></div>';
                    }
                }

                if (template === 'sales_long') {
                    var faqQ = getAll('input[name^="template_data[faq]"][name$="[q]"]').map(function (qEl) {
                        var idx = (qEl.name.match(/\[(\d+)\]\[q\]$/) || [])[1];
                        var aEl = idx !== undefined ? form.querySelector('input[name="template_data[faq][' + idx + '][a]"]') : null;
                        return { q: (qEl.value || '').trim(), a: (aEl ? aEl.value : '').trim() };
                    }).filter(function (x) { return x.q; });

                    if (faqQ.length) {
                        html += '<div class="section faq"><div class="section-title">FAQ</div>' +
                            faqQ.map(function (it) {
                                return '<details><summary>' + it.q + '</summary><div class="ans">' + (it.a || '') + '</div></details>';
                            }).join('') +
                            '</div>';
                    }

                    var quotes = getAll('input[name^="template_data[testimonials]"][name$="[quote]"]').map(function (qEl) {
                        var idx = (qEl.name.match(/\[(\d+)\]\[quote\]$/) || [])[1];
                        var nEl = idx !== undefined ? form.querySelector('input[name="template_data[testimonials][' + idx + '][name]"]') : null;
                        return { quote: (qEl.value || '').trim(), name: (nEl ? nEl.value : '').trim() };
                    }).filter(function (t) { return t.quote; });

                    if (quotes.length) {
                        html += '<div class="section"><div class="section-title">Testimonials</div><div class="testimonials">' +
                            quotes.map(function (t) {
                                return '<div class="quote"><p>“' + t.quote + '”</p><div class="by">' + (t.name || 'Customer') + '</div></div>';
                            }).join('') +
                            '</div></div>';
                    }
                }

                return html;
            }

            var inner;
            if (layout === 'split_left' || layout === 'split_right') {
                var dir = layout === 'split_right' ? 'row-reverse' : 'row';
                inner = '' +
                    '<div class="wrap">' +
                    '  <div class="card" style="background:' + bgColor + ';">' +
                    '    <div class="layout-split" style="flex-direction:' + dir + ';">' +
                    (heroUrl ? (
                        '      <div class="col-image">' +
                        '        <img src="' + heroUrl + '" alt="Hero">' +
                        '      </div>'
                    ) : '') +
                    '      <div class="col-text">' +
                    '        <h2>' + title + '</h2>' +
                    (subtitle ? '<h3 class="subtitle">' + subtitle + '</h3>' : '') +
                    '        <div class="content">' + content + '</div>' +
                    templateExtrasHtml() +
                    '        <div>' + actionHtml + '</div>' +
                    '      </div>' +
                    '    </div>' +
                    '  </div>' +
                    '</div>';
            } else {
                inner = '' +
                    '<div class="wrap">' +
                    '  <div class="card" style="background:' + bgColor + ';">' +
                    (heroImgHtml || '') +
                    '    <h2>' + title + '</h2>' +
                    (subtitle ? '<h3 class="subtitle">' + subtitle + '</h3>' : '') +
                    '    <div class="content">' + content + '</div>' +
                    templateExtrasHtml() +
                    '    <div>' + actionHtml + '</div>' +
                    '  </div>' +
                    '</div>';
            }

            body.innerHTML =
                '<style>' +
                '  * { box-sizing:border-box; }' +
                '  .wrap { width:min(1040px, 100%); margin:0 auto; }' +
                '  .card { border-radius:18px; border:1px solid #1d4ed8; padding:24px; box-shadow:0 18px 45px rgba(15,23,42,0.4); }' +
                '  h2 { margin:0 0 6px; font-size:26px; color:#0f172a; }' +
                '  .subtitle { margin:0 0 14px; font-size:16px; color:#64748b; font-weight:600; }' +
                '  .content { margin-bottom:18px; color:#334155; font-size:14px; line-height:1.6; white-space:pre-wrap; }' +
                '  .price { font-size:32px; font-weight:800; color:#047857; margin:0 0 12px; }' +
                '  .row { display:flex; gap:10px; flex-wrap:wrap; }' +
                '  input { width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1; margin-bottom:8px; }' +
                '  label { display:block; font-size:13px; font-weight:700; color:#e5e7eb; margin:4px 0; }' +
                '  .btn-main, .btn-secondary { display:inline-flex; align-items:center; justify-content:center; padding:10px 20px; border-radius:999px; border:none; font-weight:700; cursor:default; }' +
                '  .btn-main { background:' + btnColor + '; color:#fff; box-shadow:0 10px 25px rgba(37,99,235,0.55); }' +
                '  .btn-secondary { background:#0f172a; color:#e5e7eb; border:1px solid rgba(148,163,184,0.6); }' +
                '  .layout-split { display:flex; flex-wrap:wrap; gap:24px; align-items:flex-start; }' +
                '  .layout-split .col-text { flex:1 1 260px; min-width:0; }' +
                '  .layout-split .col-image { flex:0 0 320px; max-width:360px; }' +
                '  .layout-split .col-image img { width:100%; max-height:320px; object-fit:cover; border-radius:18px; border:1px solid rgba(148,163,184,0.6); }' +
                '  .section { margin-top: 18px; }' +
                '  .section-title { font-weight: 900; font-size: 18px; color:#0f172a; margin: 0 0 10px; }' +
                '  .features { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }' +
                '  .feature { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 14px; }' +
                '  .feature h4 { margin: 0 0 6px; font-size: 14px; font-weight: 900; color:#0f172a; }' +
                '  .feature p { margin: 0; color:#475569; font-size: 13px; line-height:1.5; }' +
                '  .bullets { margin: 0; padding-left: 18px; color:#334155; }' +
                '  .bullets li { margin: 6px 0; }' +
                '  .faq details { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 12px 14px; }' +
                '  .faq details + details { margin-top: 10px; }' +
                '  .faq summary { cursor:pointer; font-weight:900; color:#0f172a; }' +
                '  .faq .ans { margin-top: 8px; color:#475569; white-space: pre-wrap; }' +
                '  .testimonials { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }' +
                '  .quote { background:#fff; border:1px solid rgba(219,234,254,0.95); border-radius: 14px; padding: 14px; }' +
                '  .quote p { margin: 0 0 10px; color:#334155; line-height:1.6; }' +
                '  .quote .by { font-size: 12px; font-weight: 900; color:#0f172a; }' +
                '  .next-steps { margin: 0; padding-left: 18px; color:#334155; }' +
                '  @media (max-width: 900px) { .features { grid-template-columns: 1fr; } .testimonials { grid-template-columns: 1fr; } }' +
                '</style>' +
                inner;
        }

        // Existing step preview (uses step form)
        function openStepPreviewModal(stepId) {
            var form = document.getElementById('stepForm' + stepId);
            var overlay = document.getElementById('stepPreviewOverlay');
            var body = document.getElementById('stepPreviewModalBody');
            if (!overlay || !body) return;

            if (!form) {
                alert('Open the step editor first to preview.');
                return;
            }

            fillStepPreviewFromForm(form, body);
            overlay.style.display = 'flex';
            // smooth fade + slide
            requestAnimationFrame(function () {
                overlay.style.opacity = '1';
                var shell = overlay.querySelector('.preview-shell');
                if (shell) {
                    shell.style.transform = 'translateY(0)';
                    shell.style.opacity = '1';
                }
            });
        }

        // Add New Step preview (uses the top create form)
        function openNewStepPreviewModal() {
            var form = document.getElementById('newStepForm');
            var overlay = document.getElementById('stepPreviewOverlay');
            var body = document.getElementById('stepPreviewModalBody');
            if (!overlay || !body || !form) return;

            fillStepPreviewFromForm(form, body);
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                overlay.style.opacity = '1';
                var shell = overlay.querySelector('.preview-shell');
                if (shell) {
                    shell.style.transform = 'translateY(0)';
                    shell.style.opacity = '1';
                }
            });
        }

        function closeStepPreviewModal() {
            var overlay = document.getElementById('stepPreviewOverlay');
            if (!overlay) return;

            overlay.style.opacity = '0';
            var shell = overlay.querySelector('.preview-shell');
            if (shell) {
                shell.style.transform = 'translateY(16px)';
                shell.style.opacity = '0';
            }
            setTimeout(function () {
                overlay.style.display = 'none';
            }, 230);
        }
    </script>
@endsection
