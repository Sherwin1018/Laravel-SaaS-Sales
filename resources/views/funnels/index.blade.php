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
        .funnels-search-form {
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap:wrap;
        }
        .funnels-search-input {
            width:min(320px, 100%);
            padding:10px 12px;
            border:1px solid var(--theme-border, #E6E1EF);
            border-radius:10px;
            background:#fff;
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

    <div class="actions" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('funnels.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Funnel</a>
        <form method="GET" action="{{ route('funnels.index') }}" class="funnels-search-form">
            <input
                type="text"
                id="searchInput"
                name="search"
                value="{{ $search ?? '' }}"
                class="funnels-search-input"
                placeholder="Search funnels...">
        </form>
    </div>

    @include('partials.plan-usage-summary', [
        'planUsage' => $planUsage ?? [],
        'resourceKey' => 'funnels',
        'title' => 'Funnel Limit',
    ])

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px;">
            <h3 style="margin: 0;">Funnels</h3>
            <button type="button" id="toggleFunnelsListBtn"
                style="padding: 10px 16px; background: var(--theme-primary, #240E35); color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; min-width: 88px;"
                aria-expanded="false">
                Show
            </button>
        </div>
        <div id="funnelsListContent" style="display: none;">
            <div class="funnels-table-scroll">
            <table class="funnels-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>Steps</th>
                        <th>Public URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('funnels._rows', ['funnels' => $funnels])
                </tbody>
            </table>
            </div>

            <div style="margin-top: 18px;" id="paginationLinks">
                {{ $funnels->links('pagination::bootstrap-4') }}
            </div>
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
    var searchInput=document.getElementById("searchInput");
    var tableBody=document.getElementById("tableBody");
    var paginationLinks=document.getElementById("paginationLinks");
    var toggleFunnelsListBtn=document.getElementById("toggleFunnelsListBtn");
    var funnelsListContent=document.getElementById("funnelsListContent");
    var timeout=null;
    var modal=document.getElementById("fbDeleteConfirm");
    if(searchInput&&tableBody){
        searchInput.addEventListener("keyup",function(){
            clearTimeout(timeout);
            var query=searchInput.value;
            if(query.length>0&&query.length<2)return;
            timeout=setTimeout(function(){
                fetch(`{{ route('funnels.index') }}?search=${encodeURIComponent(query)}`,{
                    headers:{'X-Requested-With':'XMLHttpRequest'}
                })
                .then(function(response){return response.text();})
                .then(function(html){
                    tableBody.innerHTML=html;
                    if(paginationLinks){
                        if(query.length>0){
                            paginationLinks.style.display='none';
                        }else{
                            paginationLinks.style.display='block';
                            if(query==='')window.location.reload();
                        }
                    }
                })
                .catch(function(error){console.error('Search error:',error);});
            },300);
        });
    }
    if(toggleFunnelsListBtn&&funnelsListContent){
        toggleFunnelsListBtn.addEventListener("click",function(){
            var isHidden=funnelsListContent.style.display==="none";
            funnelsListContent.style.display=isHidden?"block":"none";
            toggleFunnelsListBtn.textContent=isHidden?"Hide":"Show";
            toggleFunnelsListBtn.setAttribute("aria-expanded",isHidden?"true":"false");
        });
    }
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
    document.addEventListener("submit",function(e){
        var form=e.target.closest("form[data-confirm-message]");
        if(form){
            e.preventDefault();
            var msg=form.getAttribute("data-confirm-message")||"Delete this item?";
            openModal(msg,form);
        }
    });
})();
</script>
@endsection
