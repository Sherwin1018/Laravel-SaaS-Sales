@extends('layouts.admin')

@section('title', 'Edit Funnel: ' . $funnel->name)

@section('content')
    <div class="top-header">
        <h1>Edit Funnel: {{ $funnel->name }}</h1>
        <p style="margin: 0; color: #6b7280; font-size: 14px;">Update funnel and manage pages</p>
    </div>

    @if(session('success'))
        <div style="background-color: #d1fae5; color: #065f46; padding: 10px; border-radius: 6px; margin-bottom: 20px;">{{ session('success') }}</div>
    @endif

    <div class="card" style="max-width: 700px; margin-bottom: 20px;">
        <h3>Funnel Details</h3>
        <form method="POST" action="{{ route('funnels.update', $funnel) }}">
            @csrf
            @method('PUT')
            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $funnel->name) }}" required maxlength="255"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                @error('name')<span style="color: red; font-size: 12px;">{{ $message }}</span>@enderror
            </div>
            <div style="margin-bottom: 20px;">
                <label for="description" style="display: block; margin-bottom: 8px; font-weight: bold;">Description (optional)</label>
                <textarea name="description" id="description" rows="3"
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">{{ old('description', $funnel->description) }}</textarea>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $funnel->is_active) ? 'checked' : '' }}>
                    Active
                </label>
            </div>
            <button type="submit" style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer;">Save</button>
        </form>
    </div>

    <div class="card">
        <h3>Pages</h3>
        <p style="margin-bottom: 12px;">Public URL: <code>{{ url('/f/' . $funnel->slug . '/{page-slug}') }}</code></p>
        <p style="margin-bottom: 12px; font-size: 13px; color: #6b7280;">Drag rows to reorder pages in the funnel.</p>
        <table>
            <thead>
                <tr>
                    <th style="width: 36px;"></th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="pagesSortable">
                @forelse($funnel->pages as $page)
                    <tr class="page-row" draggable="true" data-page-id="{{ $page->id }}">
                        <td class="drag-handle" style="cursor: grab; color: #9ca3af; text-align: center;"><i class="fas fa-grip-vertical"></i></td>
                        <td>{{ \App\Models\FunnelPage::TYPES[$page->type] ?? $page->type }}</td>
                        <td>{{ $page->title }}</td>
                        <td><code>{{ $page->slug }}</code></td>
                        <td style="display: flex; gap: 10px;">
                            <a href="{{ route('funnels.pages.edit', [$funnel, $page]) }}" style="color: #2563EB; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                            <form method="POST" action="{{ route('funnels.pages.destroy', [$funnel, $page]) }}" style="display: inline;" onsubmit="return confirm('Remove this page?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0;"><i class="fas fa-trash"></i> Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No pages yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top: 16px;">
            <a href="{{ route('funnels.pages.create', $funnel) }}" style="display: inline-block; padding: 8px 16px; background-color: #2563EB; color: white; text-decoration: none; border-radius: 6px;"><i class="fas fa-plus"></i> Add Page</a>
        </div>
        <div id="reorderStatus" style="margin-top: 8px; font-size: 13px; display: none;"></div>
    </div>

    <p style="margin-top: 1rem;"><a href="{{ route('funnels.index') }}" style="color: #6b7280; text-decoration: none;">&larr; Back to Funnels</a></p>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tbody = document.getElementById('pagesSortable');
    if (!tbody) return;
    var rows = tbody.querySelectorAll('.page-row');
    if (rows.length === 0) return;

    var draggedRow = null;

    rows.forEach(function(row) {
        row.addEventListener('dragstart', function(e) {
            draggedRow = row;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', row.dataset.pageId);
            row.style.opacity = '0.5';
        });
        row.addEventListener('dragend', function() {
            row.style.opacity = '1';
            draggedRow = null;
        });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (draggedRow && draggedRow !== row) {
                var rect = row.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    tbody.insertBefore(draggedRow, row);
                } else {
                    tbody.insertBefore(draggedRow, row.nextSibling);
                }
            }
        });
    });

    tbody.addEventListener('drop', function(e) {
        e.preventDefault();
        var order = [];
        tbody.querySelectorAll('.page-row').forEach(function(r) {
            order.push(parseInt(r.dataset.pageId, 10));
        });
        var statusEl = document.getElementById('reorderStatus');
        statusEl.style.display = 'block';
        statusEl.textContent = 'Saving order...';
        statusEl.style.color = '#6b7280';

        fetch('{{ route("funnels.pages.reorder", $funnel) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ order: order })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            statusEl.textContent = 'Order saved.';
            statusEl.style.color = '#059669';
            setTimeout(function() { statusEl.style.display = 'none'; }, 2000);
        })
        .catch(function() {
            statusEl.textContent = 'Failed to save order.';
            statusEl.style.color = '#dc2626';
        });
    });
    tbody.addEventListener('dragover', function(e) { e.preventDefault(); });
});
</script>
@endsection
