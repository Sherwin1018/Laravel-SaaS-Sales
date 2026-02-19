@extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('content')
    <div class="top-header">
        <h1>Builder: {{ $funnel->name }}</h1>
        @if($funnel->status === 'published')
            <a class="btn-create" href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}" target="_blank">
                <i class="fas fa-up-right-from-square"></i> Open Public Funnel
            </a>
        @endif
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="card">
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
                    <select name="status" style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                        <option value="draft" {{ old('status', $funnel->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $funnel->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <button type="submit" class="btn-create">Save Funnel</button>
            </form>
        </div>

        <div class="card">
            <h3>Add New Step</h3>
            <form method="POST" action="{{ route('funnels.steps.store', $funnel) }}">
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
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px;">
                    <div>
                        <label style="display:block; margin-bottom:6px; font-weight:700;">Type</label>
                        <select name="type" required style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
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
                    <label style="display:block; margin-bottom:6px; font-weight:700;">CTA Label</label>
                    <input type="text" name="cta_label" placeholder="Optional button text"
                        style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;">
                </div>
                <div style="margin-top:10px;">
                    <label style="display:block; margin-bottom:6px; font-weight:700;">Content</label>
                    <textarea name="content" rows="4" style="width:100%; padding:10px; border:1px solid #DBEAFE; border-radius:6px;"></textarea>
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
                            <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => $step->slug]) }}"
                                target="_blank" style="text-decoration:none; color:#2563EB; font-weight:700;">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                            <button type="button" onclick="toggleStepForm({{ $step->id }})"
                                style="background:none;border:none;color:#1E40AF;cursor:pointer;font-weight:700;text-align:left;">
                                <i class="fas fa-pen"></i> Edit
                            </button>
                        </div>
                    </div>

                    <form id="stepForm{{ $step->id }}" method="POST" action="{{ route('funnels.steps.update', [$funnel, $step]) }}"
                        style="display:none; margin-top:10px; padding-top:10px; border-top:1px dashed #DBEAFE;">
                        @csrf
                        @method('PUT')
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <input type="text" name="title" value="{{ $step->title }}" required style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                            <input type="text" name="slug" value="{{ $step->slug }}" required style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                            <select name="type" required style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                                @foreach($stepTypes as $value => $label)
                                    <option value="{{ $value }}" {{ $step->type === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="price" value="{{ $step->price }}" step="0.01" min="0.01" placeholder="Optional" style="padding:8px;border:1px solid #DBEAFE;border-radius:6px;">
                        </div>
                        <input type="text" name="cta_label" value="{{ $step->cta_label }}" placeholder="CTA Label"
                            style="margin-top:8px; width:100%; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">
                        <textarea name="content" rows="3" style="margin-top:8px; width:100%; padding:8px; border:1px solid #DBEAFE; border-radius:6px;">{{ $step->content }}</textarea>
                        <label style="display:flex; align-items:center; gap:8px; margin-top:8px; font-size:12px; font-weight:700; color:#475569;">
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

    <script>
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
    </script>
@endsection
