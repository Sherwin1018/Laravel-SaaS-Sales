@forelse($templates as $template)
    <tr>
        <td>{{ $template->name }}</td>
        <td>{{ \App\Models\FunnelTemplate::FUNNEL_PURPOSE_OPTIONS[$template->resolvedFunnelPurpose()] ?? 'Services' }}</td>
        <td>{{ ucfirst($template->status) }}</td>
        <td>{{ $template->steps_count }}</td>
        <td>{{ $template->slug }}</td>
        <td>
            <div class="template-actions">
            <a
                href="{{ route('admin.funnel-templates.edit', $template) }}"
                class="template-action template-action--edit"
                data-tooltip="Edit"
                aria-label="Edit template"
            >
                <i class="fas fa-pen"></i>
            </a>
            <a
                href="{{ route('admin.funnel-templates.replace-json', $template) }}"
                class="template-action template-action--replace"
                data-tooltip="Replace JSON"
                aria-label="Replace template JSON"
            >
                <i class="fas fa-file-code"></i>
            </a>
            <a
                href="{{ route('admin.funnel-templates.analytics', ['template' => $template->id]) }}"
                class="template-action template-action--analytics"
                data-tooltip="Analytics"
                aria-label="Open template analytics"
            >
                <i class="fas fa-chart-line"></i>
            </a>
            <form method="POST" action="{{ route('admin.funnel-templates.destroy', $template) }}" style="display:inline;" data-delete-template-form data-template-name="{{ $template->name }}">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="template-action-btn template-action--delete"
                    data-tooltip="Delete"
                    aria-label="Delete template"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center; color:#64748b;">No funnel templates found.</td>
    </tr>
@endforelse
