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
        .fb-reviews-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1510;padding:18px}
        .fb-reviews-modal.open{display:flex}
        .fb-reviews-card{width:min(1120px,96vw);height:min(86vh,840px);background:#fff;border-radius:18px;border:1px solid #E6E1EF;box-shadow:0 28px 70px rgba(15,23,42,.24);display:grid;grid-template-rows:auto 1fr;overflow:hidden}
        .fb-reviews-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid #E6E1EF;background:#fff}
        .fb-reviews-title{font-size:18px;font-weight:900;color:#240E35;margin:0}
        .fb-reviews-close{width:38px;height:38px;border-radius:999px;border:1px solid #E6E1EF;background:#fff;color:#240E35;cursor:pointer;font-size:22px;line-height:1}
        .fb-reviews-body{position:relative;background:#F8FAFC;overflow:auto;padding:18px}
        .fb-reviews-loading{display:grid;place-items:center;min-height:220px;color:#64748b;font-weight:700}
        .fb-reviews-body .reviews-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .fb-reviews-body .reviews-filter{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .fb-reviews-body .reviews-filter select{padding:10px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff}
        .fb-reviews-body .reviews-list{display:grid;gap:14px}
        .fb-reviews-body .review-card,.fb-reviews-body .card{background:#fff;border:1px solid #E6E1EF;border-radius:16px;padding:18px;box-shadow:0 12px 24px rgba(15,23,42,.06)}
        .fb-reviews-body .review-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-bottom:10px}
        .fb-reviews-body .review-name{font-size:18px;font-weight:800;color:#240E35}
        .fb-reviews-body .review-meta{font-size:13px;color:#64748B}
        .fb-reviews-body .review-stars{color:#f59e0b;font-size:15px;letter-spacing:.06em}
        .fb-reviews-body .review-status{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
        .fb-reviews-body .review-status.pending{background:#fff7ed;color:#c2410c}
        .fb-reviews-body .review-status.approved{background:#ecfdf5;color:#047857}
        .fb-reviews-body .review-status.rejected{background:#fef2f2;color:#b91c1c}
        .fb-reviews-body .review-body{font-size:14px;line-height:1.6;color:#334155;white-space:pre-wrap}
        .fb-reviews-body .review-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
        .fb-reviews-body .review-btn{padding:9px 12px;border-radius:10px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;cursor:pointer}
        .fb-reviews-body .review-btn.approve{background:#047857;color:#fff;border-color:#047857}
        .fb-reviews-body .review-btn.reject{background:#b91c1c;color:#fff;border-color:#b91c1c}
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
    <div class="fb-reviews-modal" id="fbReviewsModal" aria-hidden="true">
        <div class="fb-reviews-card" role="dialog" aria-modal="true" aria-labelledby="fbReviewsModalTitle">
            <div class="fb-reviews-head">
                <h2 class="fb-reviews-title" id="fbReviewsModalTitle">Funnel Reviews</h2>
                <button type="button" class="fb-reviews-close" id="fbReviewsModalClose" aria-label="Close reviews modal">&times;</button>
            </div>
            <div class="fb-reviews-body" id="fbReviewsModalBody">
                <div class="fb-reviews-loading">Loading reviews...</div>
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
    var reviewsModal=document.getElementById("fbReviewsModal");
    var reviewsModalTitle=document.getElementById("fbReviewsModalTitle");
    var reviewsModalBody=document.getElementById("fbReviewsModalBody");
    var reviewsModalClose=document.getElementById("fbReviewsModalClose");
    var reviewsModalUrl="";
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
    function reviewsLoading(){
        if(reviewsModalBody)reviewsModalBody.innerHTML='<div class="fb-reviews-loading">Loading reviews...</div>';
    }
    function closeReviewsModal(){
        if(!reviewsModal)return;
        reviewsModal.classList.remove("open");
        reviewsModal.setAttribute("aria-hidden","true");
        reviewsModalUrl="";
        reviewsLoading();
    }
    function loadReviewsModal(url,options){
        if(!reviewsModalBody||!url)return Promise.resolve();
        reviewsLoading();
        var fetchOptions=options||{
            headers:{'X-Requested-With':'XMLHttpRequest'}
        };
        return fetch(url,fetchOptions)
            .then(function(response){ return response.text(); })
            .then(function(html){
                reviewsModalBody.innerHTML=html;
            })
            .catch(function(){
                reviewsModalBody.innerHTML='<div class="card" style="color:#b91c1c;">Unable to load reviews right now.</div>';
            });
    }
    function openReviewsModal(title,url){
        if(!reviewsModal||!reviewsModalBody)return;
        if(reviewsModalTitle)reviewsModalTitle.textContent=title||"Funnel Reviews";
        reviewsModalUrl=url||"";
        reviewsModal.classList.add("open");
        reviewsModal.setAttribute("aria-hidden","false");
        loadReviewsModal(reviewsModalUrl);
    }
    if(reviewsModalClose)reviewsModalClose.addEventListener("click",closeReviewsModal);
    if(reviewsModal){
        reviewsModal.addEventListener("click",function(e){
            if(e.target===reviewsModal)closeReviewsModal();
        });
    }
    document.addEventListener("change",function(e){
        var select=e.target&&e.target.closest?e.target.closest('#fbReviewsModalBody select[name="status"]'):null;
        if(!select)return;
        var form=select.form;
        if(!form)return;
        e.preventDefault();
        var params=new URLSearchParams(new FormData(form));
        loadReviewsModal(form.action+"?"+params.toString(),{
            headers:{'X-Requested-With':'XMLHttpRequest'}
        });
    });
    document.addEventListener("submit",function(e){
        var form=e.target&&e.target.closest?e.target.closest('#fbReviewsModalBody form'):null;
        if(!form)return;
        var method=String(form.getAttribute("method")||"GET").toUpperCase();
        if(method==="GET")return;
        e.preventDefault();
        var formData=new FormData(form);
        fetch(form.action,{
            method:'POST',
            headers:{
                'X-Requested-With':'XMLHttpRequest',
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Accept':'text/html'
            },
            body:formData
        })
        .then(function(response){ return response.text(); })
        .then(function(html){
            reviewsModalBody.innerHTML=html;
        })
        .catch(function(){
            reviewsModalBody.innerHTML='<div class="card" style="color:#b91c1c;">Unable to update review right now.</div>';
        });
    });
    document.addEventListener("click",function(e){
        var btn=e.target&&e.target.closest?e.target.closest("[data-reviews-modal-url]"):null;
        if(!btn)return;
        e.preventDefault();
        openReviewsModal(
            btn.getAttribute("data-reviews-modal-title")||"Funnel Reviews",
            btn.getAttribute("data-reviews-modal-url")||"about:blank"
        );
    });
    document.addEventListener("keydown",function(e){
        if(!reviewsModal||!reviewsModal.classList.contains("open"))return;
        var k=String(e.key||"").toLowerCase();
        if(k==="escape")closeReviewsModal();
    });
})();
</script>
@endsection
