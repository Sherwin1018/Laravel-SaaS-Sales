@extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('styles')
    <style>
        .funnels-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .funnels-table {
            min-width: 760px;
        }
        .fb-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1500;padding:18px}
        .fb-modal.open{display:flex}
        .fb-modal-card{width:min(520px,92vw);background:#fff;border-radius:16px;border:1px solid #E6E1EF;box-shadow:0 24px 60px rgba(15,23,42,.2);padding:18px}
        .fb-modal-title{font-size:16px;font-weight:900;color:#240E35;margin:0 0 8px}
        .fb-modal-desc{font-size:13px;color:#475569;line-height:1.5;margin:0 0 16px}
        .fb-modal-actions{display:flex;justify-content:flex-end;gap:8px}
        .fb-btn{padding:8px 12px;border-radius:8px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;cursor:pointer}
        .fb-btn.danger{background:#dc2626;color:#fff;border-color:#b91c1c}
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Funnel Builder</h1>
    </div>

    <div class="actions" style="justify-content: space-between; align-items: center;">
        <a href="{{ route('funnels.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Funnel</a>
    </div>

    <div class="card">
        <h3>Funnels</h3>
        <div class="funnels-table-scroll">
        <table class="funnels-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Steps</th>
                    <th>Public URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funnels as $funnel)
                    <tr>
                        <td>{{ $funnel->name }}</td>
                        <td>{{ ucfirst($funnel->status) }}</td>
                        <td>{{ $funnel->steps_count }}</td>
                        <td>
                            @if($funnel->status === 'published')
                                <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}" target="_blank">
                                    {{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug]) }}
                                </a>
                            @else
                                <span style="color: var(--theme-muted, #6B7280);">Publish to enable</span>
                            @endif
                        </td>
                        <td style="display:flex; gap: 10px;">
                            <a href="{{ route('funnels.edit', $funnel) }}" style="color:var(--theme-primary, #240E35); text-decoration:none; font-weight:700;">
                                <i class="fas fa-pen"></i> Builder
                            </a>
                            <a href="{{ route('funnels.analytics', $funnel) }}" style="color:#0F766E; text-decoration:none; font-weight:700;">
                                <i class="fas fa-chart-line"></i> Analytics
                            </a>
                            <form method="POST" action="{{ route('funnels.destroy', $funnel) }}" data-confirm-message="Delete this funnel?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;font-weight:700;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">No funnels found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $funnels->links('pagination::bootstrap-4') }}
        </div>
    </div>
    <div class="fb-modal" id="fbDeleteConfirm" aria-hidden="true">
        <div class="fb-modal-card" role="dialog" aria-modal="true" aria-labelledby="fbDeleteConfirmTitle">
            <div class="fb-modal-title" id="fbDeleteConfirmTitle">Confirm delete</div>
            <p class="fb-modal-desc" id="fbDeleteConfirmDesc">Delete this item?</p>
            <div class="fb-modal-actions">
                <button type="button" class="fb-btn" id="fbDeleteConfirmCancel">Cancel</button>
                <button type="button" class="fb-btn danger" id="fbDeleteConfirmOk">Delete</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function(){
    var modal=document.getElementById("fbDeleteConfirm");
    if(!modal)return;
    var desc=document.getElementById("fbDeleteConfirmDesc");
    var btnOk=document.getElementById("fbDeleteConfirmOk");
    var btnCancel=document.getElementById("fbDeleteConfirmCancel");
    var pendingForm=null;
    function closeModal(){
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden","true");
        pendingForm=null;
    }
    function openModal(message,form){
        desc.textContent=message||"Delete this item?";
        pendingForm=form;
        modal.classList.add("open");
        modal.setAttribute("aria-hidden","false");
    }
    btnOk.addEventListener("click",function(){
        var form=pendingForm;
        closeModal();
        if(form)form.submit();
    });
    btnCancel.addEventListener("click",closeModal);
    modal.addEventListener("click",function(e){
        if(e.target===modal)closeModal();
    });
    document.addEventListener("keydown",function(e){
        if(!modal.classList.contains("open"))return;
        var k=String(e.key||"").toLowerCase();
        if(k==="escape")closeModal();
    });
    document.querySelectorAll("form[data-confirm-message]").forEach(function(form){
        form.addEventListener("submit",function(e){
            e.preventDefault();
            var msg=form.getAttribute("data-confirm-message")||"Delete this item?";
            openModal(msg,form);
        });
    });
})();
</script>
@endsection
