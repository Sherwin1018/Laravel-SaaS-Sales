@forelse($templates as $template)
    <tr>
        <td>{{ $template->name }}</td>
        <td>{{ $template->templateTypeLabel() }}</td>
        <td>{{ ucfirst($template->status) }}</td>
        <td>{{ $template->steps_count }}</td>
        <td>{{ $template->slug }}</td>
        <td>
            <a href="{{ route('admin.funnel-templates.edit', $template) }}" class="action-link edit">
                <i class="fas fa-pen"></i> Edit
            </a>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" style="text-align:center; color:#64748b;">No funnel templates found.</td>
    </tr>
@endforelse
