@extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('styles')
        <link rel="stylesheet" href="{{ asset('css/extracted/funnels-index-style1.css') }}">
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
        .funnel-payout-notice {
            margin: 16px 0 18px;
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #E6E1EF;
            background: linear-gradient(135deg, #FFF8ED 0%, #FFFFFF 100%);
            box-shadow: 0 10px 28px rgba(36, 14, 53, 0.06);
        }
        .funnel-payout-notice__title {
            margin: 0 0 6px;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #9A3412;
        }
        .funnel-payout-notice__copy {
            margin: 0;
            font-size: 14px;
            line-height: 1.55;
            color: #475569;
        }
        .plan-usage-card {
            margin-bottom: 20px;
            overflow: visible;
            border: 1px solid #ece2f5;
            background:
                radial-gradient(circle at top left, rgba(124, 58, 237, 0.08), transparent 34%),
                linear-gradient(135deg, #fff 0%, #fbf9fd 100%);
            box-shadow: 0 16px 34px rgba(36, 14, 53, 0.08);
        }
        .plan-usage-shell {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, auto);
            gap: 18px;
            align-items: start;
        }
        .plan-usage-copy {
            min-width: 0;
        }
        .plan-usage-eyebrow,
        .funnel-summary-eyebrow {
            display: inline-block;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #2E1244;
        }
        .plan-usage-description,
        .funnel-summary-description {
            margin: 0;
            font-size: clamp(19px, 3vw, 34px);
            line-height: 1.25;
            font-weight: 900;
            color: #61748f;
        }
        .plan-usage-stat-grid,
        .funnel-summary-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(118px, 1fr));
            gap: 12px;
        }
        .plan-usage-stat-card,
        .funnel-summary-stat-card {
            min-width: 0;
            padding: 18px 18px 16px;
            border: 1px solid #ddd3ea;
            border-radius: 16px;
            background: rgba(247, 247, 251, 0.88);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        }
        .plan-usage-stat-label,
        .funnel-summary-stat-label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .plan-usage-stat-value,
        .funnel-summary-stat-value {
            display: block;
            margin-top: 10px;
            font-size: clamp(26px, 3vw, 38px);
            line-height: 1;
            color: #2E1244;
        }
        .plan-usage-callout {
            margin-top: 2px;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid transparent;
            max-width: 760px;
        }
        .plan-usage-callout.is-positive {
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.08) 0%, rgba(240, 253, 244, 0.86) 100%);
            color: #166534;
            border-color: rgba(22, 163, 74, 0.18);
        }
        .plan-usage-callout.is-warning {
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1) 0%, rgba(255, 247, 237, 0.92) 100%);
            color: #92400E;
            border-color: rgba(217, 119, 6, 0.18);
        }
        .plan-usage-callout-title {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }
        .plan-usage-callout-copy {
            font-size: 13px;
            line-height: 1.5;
        }
        .funnel-summary-card {
            margin-bottom: 20px;
            border: 1px solid #ece2f5;
            background:
                linear-gradient(135deg, rgba(36, 14, 53, 0.04) 0%, rgba(255,255,255,0.98) 26%),
                linear-gradient(180deg, #fff 0%, #fcfbfe 100%);
            box-shadow: 0 16px 34px rgba(36, 14, 53, 0.08);
        }
        .funnel-summary-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(280px, 390px);
            gap: 18px;
            align-items: center;
        }
        .funnel-summary-copy {
            min-width: 0;
            display: grid;
            gap: 10px;
            align-content: center;
        }
        .funnel-summary-description {
            max-width: 680px;
        }
        .funnel-summary-note {
            margin: 0;
            padding: 12px 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(36, 14, 53, 0.05) 0%, rgba(247, 247, 251, 0.96) 100%);
            border: 1px solid #e6e1ef;
            font-size: 13px;
            line-height: 1.5;
            color: #64748b;
        }
        .funnel-summary-note strong {
            display: inline-flex;
            align-items: center;
            margin-right: 8px;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(36, 14, 53, 0.08);
            color: #240E35;
            font-size: 11px;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .funnel-summary-card.card {
            padding: 16px 18px;
        }
        .funnel-summary-card .funnel-summary-eyebrow {
            margin-bottom: 8px;
        }
        .funnel-summary-card .funnel-summary-description {
            font-size: clamp(15px, 1.8vw, 22px);
            line-height: 1.32;
        }
        .funnel-summary-card .funnel-summary-stat-grid {
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: 10px;
            align-self: center;
        }
        .funnel-summary-card .funnel-summary-stat-card {
            padding: 14px 16px;
            border-radius: 14px;
        }
        .funnel-summary-card .funnel-summary-stat-value {
            font-size: clamp(24px, 2.4vw, 34px);
            margin-top: 8px;
        }
        @media (max-width: 980px) {
            .plan-usage-shell,
            .funnel-summary-shell {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .plan-usage-card,
            .funnel-summary-card {
                border-radius: 18px;
            }
            .plan-usage-description,
            .funnel-summary-description {
                font-size: 24px;
            }
            .funnel-summary-note {
                padding: 10px 12px;
            }
            .plan-usage-stat-grid,
            .funnel-summary-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .plan-usage-stat-card:last-child:nth-child(odd),
            .funnel-summary-stat-card:last-child:nth-child(odd) {
                grid-column: 1 / -1;
            }
        }
        @media (max-width: 520px) {
            .plan-usage-description,
            .funnel-summary-description {
                font-size: 18px;
            }
            .plan-usage-stat-grid,
            .funnel-summary-stat-grid {
                grid-template-columns: 1fr;
            }
            .plan-usage-stat-card:last-child:nth-child(odd),
            .funnel-summary-stat-card:last-child:nth-child(odd) {
                grid-column: auto;
            }
            .plan-usage-stat-card,
            .funnel-summary-stat-card {
                padding: 14px;
            }
            .plan-usage-stat-value,
            .funnel-summary-stat-value {
                font-size: 26px;
            }
            .funnel-summary-note strong {
                margin-bottom: 6px;
            }
        }
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
                placeholder="🔍 Search funnels...">
        </form>
    </div>

    @include('partials.plan-usage-summary', [
        'planUsage' => $planUsage ?? [],
        'resourceKey' => 'funnels',
        'title' => 'Funnel Limit',
        'compact' => true,
    ])

    @if(!empty($templateAccessSummary))
        <div class="card funnel-summary-card">
            <div class="funnel-summary-shell">
                <div class="funnel-summary-copy">
                    <span class="funnel-summary-eyebrow">Shared Funnel Templates</span>
                    <p class="funnel-summary-description">
                        @if(($templateAccessSummary['available_count'] ?? 0) > 0)
                            Your workspace can start from curated Super Admin templates right now.
                        @else
                            No published shared templates available yet.
                        @endif
                    </p>
                    <div class="funnel-summary-note">
                        <strong>Note</strong>
                        Published Super Admin templates will appear here and on the Create Funnel screen as they become available for your plan. When you select one, your workspace gets its own private draft copy that you can customize and publish separately.
                    </div>
                </div>
                <div class="funnel-summary-stat-grid">
                    <div class="funnel-summary-stat-card">
                        <span class="funnel-summary-stat-label">Available Now</span>
                        <strong class="funnel-summary-stat-value">{{ $templateAccessSummary['available_count'] ?? 0 }}</strong>
                    </div>
                    <div class="funnel-summary-stat-card">
                        <span class="funnel-summary-stat-label">Library Access</span>
                        <strong class="funnel-summary-stat-value">{{ !empty($templateAccessSummary['is_unlimited']) ? 'All' : ($templateAccessSummary['limit'] ?? 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($payoutReadiness['workspace_notice']))
        <div class="funnel-payout-notice">
            <p class="funnel-payout-notice__title">{{ $payoutReadiness['label'] ?? 'Payout status update' }}</p>
            <p class="funnel-payout-notice__copy">{{ $payoutReadiness['workspace_notice'] }}</p>
        </div>
    @endif

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px;">
            <h3 style="margin: 0;">Funnels</h3>
            <button type="button" id="toggleFunnelsListBtn" class="ui-show-hide-toggle"
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
    function setFunnelsListVisibility(visible){
        if(!funnelsListContent||!toggleFunnelsListBtn)return;
        funnelsListContent.style.display=visible?"block":"none";
        toggleFunnelsListBtn.textContent=visible?"Hide":"Show";
        toggleFunnelsListBtn.setAttribute("aria-expanded",visible?"true":"false");
    }
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
                    setFunnelsListVisibility(true);
                    if(paginationLinks){
                        if(query.length>0){
                            paginationLinks.style.display='none';
                        }else{
                            paginationLinks.style.display='block';
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
            setFunnelsListVisibility(isHidden);
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
