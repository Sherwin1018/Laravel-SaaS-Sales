@forelse($templates as $template)
    <tr>
        <td>{{ $template->name }}</td>
        <td>{{ $template->templateTypeLabel() }}</td>
        <td>{{ ucfirst($template->status) }}</td>
        <td>{{ $template->steps_count }}</td>
        <td>{{ $template->slug }}</td>
        <td style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('admin.funnel-templates.edit', $template) }}" style="color: var(--theme-primary, #240E35); text-decoration: none; font-weight: 600;">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="{{ route('admin.funnel-templates.replace-json', $template) }}" style="color: #0F766E; text-decoration: none; font-weight: 600;">
                <i class="fas fa-file-code"></i> Replace JSON
            </a>
            <form method="POST" action="{{ route('admin.funnel-templates.destroy', $template) }}" style="display:inline;" data-delete-template-form data-template-name="{{ $template->name }}">
                @csrf
                @method('DELETE')
                <button type="submit" style="background: none; border: none; color: #DC2626; cursor: pointer; padding: 0; font-weight: 600;">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center; color:#64748b;">No funnel templates found.</td>
    </tr>
@endforelse
