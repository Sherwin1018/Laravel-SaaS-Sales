@extends('layouts.admin')
@section('title', 'Funnel Builder')
@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Manrope:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600;700;800&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;600;700;800&family=Raleway:wght@400;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
@endsection

@section('content')
<style>
.fb-top{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;background:#0f172a;color:#fff;padding:12px;border-radius:12px}
.fb-actions{display:flex;gap:8px;flex-wrap:wrap}
.fb-actions form{margin:0}
.fb-actions .fb-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:42px;padding:0 14px;line-height:1;white-space:nowrap}
.fb-btn{padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-weight:700;text-decoration:none;cursor:pointer}
.fb-btn.primary{background:#2563eb;color:#fff;border-color:#1d4ed8}.fb-btn.success{background:#16a34a;color:#fff;border-color:#15803d}.fb-btn.danger{background:#dc2626;color:#fff;border-color:#b91c1c}
.fb-grid{margin-top:12px;display:grid;grid-template-columns:260px 1fr;gap:12px;transition:grid-template-columns .2s ease}
.fb-grid.components-hidden{grid-template-columns:0 1fr}
.fb-grid.components-hidden .fb-components-col{overflow:visible;pointer-events:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-tab{pointer-events:auto}
.fb-components-col{position:sticky;top:12px;align-self:start;min-width:0;overflow-x:visible}
.fb-components-col .fb-panel-toggle{position:fixed;top:50%;transform:translateY(-50%);z-index:100;width:28px;height:28px;border-radius:50%;border:1px solid #93c5fd;background:#fff;color:#1e40af;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.fb-components-col .fb-panel-toggle:hover{background:#dbeafe;color:#1e40af}
.fb-components-col .fb-panel-toggle--hide{left:288px;margin-left:-14px}
.fb-components-col .fb-panel-tab{display:none;left:8px;width:28px;height:28px;border-radius:50%;padding:0}
.fb-grid.components-hidden .fb-components-col .fb-left-tabs{display:none}
.fb-grid.components-hidden .fb-components-col .fb-card{display:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-toggle--hide{display:none}
.fb-grid.components-hidden .fb-components-col .fb-left-panel{display:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-tab{display:flex;align-items:center;justify-content:center;left:8px;top:50%;transform:translateY(-50%);position:fixed;z-index:100}
.fb-left-tabs{display:flex;gap:2px;margin-bottom:8px}
.fb-left-tabs .fb-tab{padding:8px 12px;border:1px solid #dbeafe;background:#f8fafc;color:#1e40af;font-weight:700;font-size:12px;cursor:pointer;border-radius:8px;flex:1}
.fb-left-tabs .fb-tab:hover{background:#e0f2fe}
.fb-left-tabs .fb-tab.active{background:#2563eb;color:#fff;border-color:#1d4ed8}
.fb-left-panel{display:block}
.fb-left-panel.hidden{display:none}
.fb-card{background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:10px;min-width:0;max-width:100%;box-sizing:border-box}
#fbLeftPanelComponents .fb-card{max-height:calc(100vh - 120px);overflow-y:auto;overflow-x:hidden}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar{width:8px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-track{background:#f1f5f9;border-radius:4px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-grid > *{min-width:0}
.fb-grid .fb-card{overflow-x:hidden}
#canvas{overflow-x:hidden;overflow-y:auto;max-width:100%;box-sizing:border-box}
#canvas .sec,#canvas .row,#canvas .col,#canvas .el,#canvas .sec-inner,#canvas .row-inner,#canvas .col-inner{max-width:100%;box-sizing:border-box}
#canvas img,#canvas video,#canvas iframe{max-width:100%;height:auto}
#fbSettingsCard{display:flex;flex-direction:column;max-height:calc(100vh - 120px);min-height:0}
#fbSettingsCard .fb-h{flex-shrink:0}
#fbSettingsCard #settings{overflow-y:auto;overflow-x:hidden;flex:1;min-height:0;padding-right:4px}
#fbSettingsCard #settings::-webkit-scrollbar{width:8px}
#fbSettingsCard #settings::-webkit-scrollbar-track{background:#f1f5f9;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-h{font-size:16px;font-weight:900;color:#1e40af;margin:2px 0 10px}
.fb-lib button{display:block;width:100%;text-align:left;margin-bottom:7px;padding:9px;border:1px solid #dbeafe;border-radius:8px;background:#f8fafc;font-weight:700;cursor:grab}
.fb-lib i{width:18px;margin-right:6px;color:#1d4ed8}
.fb-lib-group{margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #e2e8f0}
.fb-lib-group:last-child{margin-bottom:0;padding-bottom:0;border-bottom:0}
.fb-lib-group-title{font-size:12px;font-weight:900;letter-spacing:.02em;text-transform:uppercase;color:#1e3a8a;margin:0 0 8px}
#canvas{min-height:60vh;border:2px dashed #93c5fd;border-radius:12px;padding:10px;background:linear-gradient(180deg,#f8fafc,#e0f2fe);overflow-x:hidden;overflow-y:auto}
.sec{border:1px dashed #64748b;border-radius:10px;padding:8px;margin-bottom:9px;background:#fff}
.sec.sec--bare-carousel{border:0;background:transparent;padding:0}
.sec.sec--bare-wrap{border:0;background:transparent;padding:0}
.row{display:flex;flex-wrap:wrap;gap:8px;border:1px dashed #cbd5e1;border-radius:8px;padding:6px}
.row.row--bare-wrap{border:0;background:transparent;padding:0}
.row-inner{display:flex;flex-wrap:wrap;gap:8px}
.col{flex:1 1 240px;min-height:58px;min-width:0;border:1px dashed #bfdbfe;border-radius:7px;padding:6px;background:#f8fafc}
.el{border:1px solid #e2e8f0;border-radius:7px;padding:7px;background:#fff;margin-bottom:6px;min-width:0;overflow-wrap:break-word;word-break:break-word}
.el.el--carousel{border:0 !important;background:transparent !important;padding:0 !important}
.el.el--form{border:0 !important;background:transparent !important;padding:0 !important}
.el h2,.el p,.el button{overflow-wrap:break-word;word-break:break-word;min-width:0;max-width:100%}
#canvas .el.el--button{width:100%;box-sizing:border-box}
#canvas .el.el--button>button{display:inline-flex;width:auto;align-items:center;justify-content:center}
#canvas .el.el--button-fill{display:flex;width:100%;box-sizing:border-box}
#canvas .el.el--button-fill>button{display:flex;width:100%;align-items:center;justify-content:center;box-sizing:border-box}
.sel{outline:2px solid #2563eb;outline-offset:2px}
.settings label{font-size:12px;font-weight:800;color:#1e3a8a;display:block;margin:0 0 4px}
.settings input,.settings select,.settings textarea{width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px}
.settings input[type="checkbox"]{width:auto;padding:0;margin:0;border:1px solid #cbd5e1;border-radius:4px}
.settings label.inline-check{display:flex;align-items:center;gap:8px;font-weight:700;color:#0f172a;margin:0 0 10px}
.settings .meta{font-size:12px;color:#475569;font-weight:700;margin-bottom:8px}
.settings-delete-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #e2e8f0}
.settings-delete-wrap .fb-btn{width:100%;justify-content:center;gap:6px}
.px-wrap{display:flex;align-items:center;gap:6px;margin-bottom:8px}
.px-wrap input[type="number"]{margin-bottom:0}
.px-unit{font-size:12px;font-weight:800;color:#334155;min-width:18px}
.size-position{margin-bottom:12px}
.size-position .size-label{font-size:12px;font-weight:800;color:#1e3a8a;display:block;margin:0 0 6px}
.size-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;margin-bottom:8px;align-items:center}
.size-grid .fld{display:flex;align-items:center;gap:4px}
.size-grid .fld label{font-size:11px;color:#64748b;min-width:18px}
.size-grid .fld input{width:100%;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px}
.size-link{grid-column:1/-1;display:flex;align-items:center;gap:6px;margin-top:2px}
.size-link button{width:34px;height:34px;padding:0;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;color:#64748b}
.size-link button.linked{background:#e0e7ff;border-color:#6366f1}
.size-link button:hover{background:#e2e8f0}
.size-link span{font-size:12px;color:#64748b}
.row-border-card{border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin:10px 0;background:#fff}
.row-border-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.row-border-head strong{font-size:13px;color:#0f172a}
.row-border-head button{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:4px 8px;font-size:12px;color:#475569;cursor:pointer}
.row-radius-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.row-radius-field{display:flex;align-items:center;gap:6px}
.row-radius-field span{font-size:11px;color:#64748b;min-width:16px}
.row-radius-field input{margin-bottom:0}
.img-radius-panel{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.img-radius-link{width:34px;height:34px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc;color:#64748b;display:flex;align-items:center;justify-content:center;cursor:pointer}
.img-radius-link.linked{border-color:#3b82f6;background:#dbeafe;color:#1d4ed8}
.img-radius-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;flex:1}
.img-radius-row input{margin-bottom:0;text-align:center}
.col-layout-wrap{border:1px solid #e2e8f0;border-radius:10px;padding:10px;margin:10px 0;background:#fff}
.col-layout-title{font-size:13px;font-weight:800;color:#0f172a;margin:0 0 8px}
.col-layout-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
.col-layout-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:10px 6px;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;color:#334155;font-weight:700;cursor:pointer}
.col-layout-btn i{font-size:13px;color:#64748b}
.col-layout-btn.active{border-color:#3b82f6;background:#dbeafe;color:#1e3a8a}
.col-layout-btn:hover{background:#eef2ff}
.menu-panel-title{font-size:16px;font-weight:900;color:#0f172a;margin:2px 0 10px}
.menu-section-title{font-size:13px;font-weight:900;color:#0f172a;margin:8px 0}
.menu-item-card{border:1px solid #e2e8f0;border-radius:10px;padding:10px;background:#fff;margin-bottom:10px}
.menu-item-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.menu-item-head strong{font-size:13px;color:#0f172a}
.menu-item-actions{display:flex;align-items:center;gap:6px}
.menu-item-actions button{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:4px 8px;color:#64748b;cursor:pointer}
.menu-item-actions .menu-del{color:#ef4444}
.menu-item-card label{display:flex;align-items:center;gap:8px;margin:0 0 8px;font-weight:700}
.menu-item-card label input[type="checkbox"]{width:auto;margin:0}
.menu-split{height:1px;background:#e2e8f0;margin:12px 0}
.menu-typo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
.menu-align-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin-bottom:8px}
.menu-align-btn{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:7px 8px;color:#64748b;cursor:pointer}
.menu-align-btn.active{border-color:#3b82f6;background:#dbeafe;color:#1d4ed8}
.menu-slider-row{display:grid;grid-template-columns:1fr 96px;gap:8px;align-items:center;margin-bottom:8px}
.menu-slider-row input{margin-bottom:0}
.carousel-slide-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.carousel-slide-btn{flex:1;border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:8px 10px;text-align:left;font-weight:700;color:#334155;cursor:pointer}
.carousel-slide-btn.active{background:#0ea5e9;border-color:#0284c7;color:#fff}
.carousel-icon-btn{width:34px;height:34px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center}
.carousel-icon-btn.active{border-color:#3b82f6;background:#dbeafe;color:#1d4ed8}
.carousel-icon-btn.danger{color:#ef4444}
.carousel-comp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-bottom:10px}
.carousel-comp-btn{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:8px 10px;color:#334155;font-weight:700;cursor:pointer}
.carousel-comp-btn:hover{background:#eef2ff}
.carousel-comp-btn[draggable="true"]{cursor:grab}
.carousel-group-title{font-size:11px;font-weight:900;color:#475569;letter-spacing:.02em;text-transform:uppercase;margin:6px 0}
.rt-box{border:1px solid #cbd5e1;border-radius:8px;background:#fff;margin-bottom:8px}
.rt-tools{display:flex;gap:6px;padding:6px;border-bottom:1px solid #e2e8f0}
.rt-tools button{padding:5px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;font-weight:800;cursor:pointer}
.rt-editor{min-height:90px;padding:8px;outline:none}
@media(max-width:1080px){.fb-grid,.fb-grid.components-hidden{grid-template-columns:1fr}.fb-components-col .fb-panel-toggle{display:none!important}.fb-grid.components-hidden .fb-components-col .fb-left-panel{display:block!important}.fb-left-panel.hidden{display:none!important}}
</style>

<div class="fb-top">
    <div><strong>{{ $funnel->name }}</strong> <span style="font-size:12px;opacity:.9;">({{ ucfirst($funnel->status) }})</span></div>
    <div class="fb-actions">
        <button id="saveBtn" class="fb-btn primary" type="button"><i class="fas fa-save"></i> Save</button>
        <button id="previewBtn" class="fb-btn" type="button"><i class="fas fa-eye"></i> Preview</button>
        @if($funnel->status === 'published')
            <form method="POST" action="{{ route('funnels.unpublish', $funnel) }}">@csrf<button class="fb-btn danger" type="submit"><i class="fas fa-ban"></i> Unpublish</button></form>
        @else
            <form method="POST" action="{{ route('funnels.publish', $funnel) }}">@csrf<button class="fb-btn success" type="submit"><i class="fas fa-upload"></i> Publish</button></form>
        @endif
        <a href="{{ route('funnels.index') }}" class="fb-btn"><i class="fas fa-door-open"></i> Exit Builder</a>
    </div>
</div>

<div class="fb-grid" id="fbGrid">
    <div class="fb-components-col" id="fbComponentsCol">
        <div class="fb-left-tabs">
            <button type="button" class="fb-tab active" id="fbTabComponents" title="Components"><i class="fas fa-th-large"></i></button>
            <button type="button" class="fb-tab" id="fbTabSettings" title="Settings"><i class="fas fa-cog"></i></button>
        </div>
        <div class="fb-left-panel" id="fbLeftPanelComponents">
            <div class="fb-card fb-lib">
                <h3 class="fb-h">Components</h3>
                <div class="fb-lib-group">
                    <div class="fb-lib-group-title">Layout & Structure</div>
                    <button draggable="true" data-c="section"><i class="fas fa-square"></i>Section</button>
                    <button draggable="true" data-c="row"><i class="fas fa-grip-lines"></i>Row</button>
                    <button draggable="true" data-c="column"><i class="fas fa-columns"></i>Column</button>
                    <button draggable="true" data-c="spacer"><i class="fas fa-arrows-up-down"></i>Spacer</button>
                </div>
                <div class="fb-lib-group">
                    <div class="fb-lib-group-title">Basic Content</div>
                    <button draggable="true" data-c="heading"><i class="fas fa-heading"></i>Heading</button>
                    <button draggable="true" data-c="text"><i class="fas fa-font"></i>Text</button>
                    <button draggable="true" data-c="button"><i class="fas fa-square-plus"></i>Button</button>
                </div>
                <div class="fb-lib-group">
                    <div class="fb-lib-group-title">Media & Visuals</div>
                    <button draggable="true" data-c="image"><i class="fas fa-image"></i>Image</button>
                    <button draggable="true" data-c="video"><i class="fas fa-video"></i>Video</button>
                    <button draggable="true" data-c="carousel"><i class="fas fa-images"></i>Carousel</button>
                </div>
                <div class="fb-lib-group">
                    <div class="fb-lib-group-title">Interaction & Navigation</div>
                    <button draggable="true" data-c="menu"><i class="fas fa-bars"></i>Menu</button>
                    <button draggable="true" data-c="form"><i class="fas fa-file-lines"></i>Form</button>
                </div>
            </div>
        </div>
        <div class="fb-left-panel hidden" id="fbLeftPanelSettings">
            <div class="fb-card settings" id="fbSettingsCard">
                <h3 id="settingsTitle" class="fb-h" style="margin-bottom:4px;">Settings Panel</h3>
                <div id="settings"><p class="meta">Select a component to edit.</p></div>
            </div>
        </div>
        <button type="button" class="fb-panel-toggle fb-panel-toggle--hide" id="fbComponentsHide" title="Hide left panel"><i class="fas fa-chevron-left"></i></button>
        <button type="button" class="fb-panel-toggle fb-panel-tab" id="fbComponentsShow" title="Show left panel"><i class="fas fa-chevron-right"></i></button>
    </div>

    <div class="fb-card">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
            <label for="stepSel" style="font-weight:800;">Step</label>
            <select id="stepSel"></select>
            <span id="saveMsg" style="font-size:12px;color:#475569;font-weight:700;">Not saved yet</span>
        </div>
        <div id="canvas"></div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $builderSteps = $funnel->steps->sortBy('position')->values()->map(function ($step) {
        return [
            'id' => $step->id,
            'title' => $step->title,
            'type' => $step->type,
            'layout_json' => $step->layout_json,
        ];
    })->all();
@endphp
<script>
(function(){
const saveUrl="{{ route('funnels.builder.layout.save',$funnel) }}";
const uploadUrl="{{ route('funnels.builder.image.upload',$funnel) }}";
const previewTpl="{{ route('funnels.preview',['funnel'=>$funnel,'step'=>'__STEP__']) }}";
const csrf="{{ csrf_token() }}";
const steps=@json($builderSteps);
const state={sid:{{ (int)($defaultStepId??0) }}||((steps[0]&&steps[0].id)||null),layout:null,sel:null,carouselSel:null};
const fonts=[
    {value:"Inter, sans-serif",label:"Inter"},
    {value:"Poppins, sans-serif",label:"Poppins"},
    {value:"Roboto, sans-serif",label:"Roboto"},
    {value:"Open Sans, sans-serif",label:"Open Sans"},
    {value:"Montserrat, sans-serif",label:"Montserrat"},
    {value:"Nunito, sans-serif",label:"Nunito"},
    {value:"Raleway, sans-serif",label:"Raleway"},
    {value:"Manrope, sans-serif",label:"Manrope"},
    {value:"Playfair Display, serif",label:"Playfair Display"},
    {value:"Georgia, serif",label:"Georgia"},
    {value:"Times New Roman, serif",label:"Times New Roman"},
    {value:"Arial, sans-serif",label:"Arial"},
];

const stepSel=document.getElementById("stepSel"),canvas=document.getElementById("canvas"),settings=document.getElementById("settings"),saveMsg=document.getElementById("saveMsg"),settingsTitle=document.getElementById("settingsTitle");
if(!steps.length){canvas.textContent="No steps found.";return;}

steps.forEach(s=>{const o=document.createElement("option");o.value=s.id;o.textContent=s.title;stepSel.appendChild(o);});
stepSel.value=state.sid;

const uid=p=>p+"_"+Math.random().toString(36).slice(2,10),clone=o=>JSON.parse(JSON.stringify(o));
const defaults=()=>({
    root:[{
        kind:"section",
        id:uid("sec"),
        style:{padding:"20px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"full"},
        elements:[],
        rows:[{
            id:uid("row"),
            style:{gap:"8px"},
            columns:[{
                id:uid("col"),
                style:{},
                elements:[{
                    id:uid("el"),
                    type:"heading",
                    content:"Welcome to Our Offer",
                    style:{fontSize:"32px",color:"#0f172a"},
                    settings:{}
                }]
            }]
        }]
    }],
    sections:[]
});
const cur=()=>steps.find(s=>+s.id===+state.sid);
const sec=id=>(state.layout.sections||[]).find(x=>x.id===id);
const row=(s,r)=>(sec(s)?.rows||[]).find(x=>x.id===r);
const col=(s,r,c)=>(row(s,r)?.columns||[]).find(x=>x.id===c);
const el=(s,r,c,e)=>(col(s,r,c)?.elements||[]).find(x=>x.id===e);
const secEl=(s,e)=>(sec(s)?.elements||[]).find(x=>x.id===e);
const rootItems=()=>{state.layout=state.layout||{};state.layout.root=Array.isArray(state.layout.root)?state.layout.root:[];return state.layout.root;};
function rootIndexByRef(ref){
    const rs=rootItems();
    return rs.findIndex(it=>it===ref||((it&&ref)&&String((it.kind||"")).toLowerCase()===String((ref.kind||"")).toLowerCase()&&String(it.id||"")===String(ref.id||"")));
}
function sectionRootContext(sectionId){
    const s=sec(sectionId);
    if(!s)return {section:null,root:null,index:-1,isWrap:false};
    if(s.__rootWrap&&s.__rootRef){
        const idx=rootIndexByRef(s.__rootRef);
        return {section:s,root:s.__rootRef,index:idx,isWrap:true};
    }
    const idx=rootItems().findIndex(it=>String((it&&it.kind)||"").toLowerCase()==="section"&&String(it.id||"")===String(s.id||""));
    return {section:s,root:idx>=0?rootItems()[idx]:null,index:idx,isWrap:false};
}

const undoHistory=[];const maxUndo=40;
function saveToHistory(){if(!state.layout)return;undoHistory.push(clone(state.layout));if(undoHistory.length>maxUndo)undoHistory.shift();}
function undo(){if(!undoHistory.length)return;state.layout=undoHistory.pop();render();}

function syncSectionsFromRoot(){
    state.layout=state.layout||{};
    state.layout.root=Array.isArray(state.layout.root)?state.layout.root:[];
    const bareRootElementTypes={
        carousel:true,
        heading:true,
        text:true,
        spacer:true,
        button:true,
        image:true,
        video:true,
        menu:true,
        form:true
    };
    const out=[];
    state.layout.root.forEach((it,idx)=>{
        const kind=String((it&&it.kind)||"section").toLowerCase();
        if(kind==="section"){
            it.elements=Array.isArray(it.elements)?it.elements:[];
            it.rows=Array.isArray(it.rows)?it.rows:[];
            out.push(it);
            return;
        }
        if(kind==="row"){
            const wrap={id:"sec_wrap_row_"+String(it.id||idx),style:{},settings:{contentWidth:"full"},elements:[],rows:[it],__rootWrap:true,__rootKind:"row",__rootRef:it,__bareRootWrap:true};
            out.push(wrap);
            return;
        }
        if(kind==="column"){
            const rw={id:"row_wrap_col_"+String(it.id||idx),style:{gap:"8px"},settings:{},columns:[it]};
            const wrap={id:"sec_wrap_col_"+String(it.id||idx),style:{},settings:{contentWidth:"full"},elements:[],rows:[rw],__rootWrap:true,__rootKind:"column",__rootRef:it,__bareRootWrap:true};
            out.push(wrap);
            return;
        }
        if(kind==="el"){
            const elType=String((it&&it.type)||"").toLowerCase();
            const isCarouselEl=elType==="carousel";
            const isBareRootEl=!!bareRootElementTypes[elType];
            const wrap={id:"sec_wrap_el_"+String(it.id||idx),style:{},settings:{contentWidth:"full"},elements:[it],rows:[],__rootWrap:true,__rootKind:"el",__rootRef:it,__bareCarouselWrap:isCarouselEl,__bareRootWrap:isBareRootEl};
            out.push(wrap);
            return;
        }
        it.elements=Array.isArray(it.elements)?it.elements:[];
        it.rows=Array.isArray(it.rows)?it.rows:[];
        out.push(it);
    });
    state.layout.sections=out;
}

function ensureRootModel(){
    state.layout=state.layout||{};
    if(!Array.isArray(state.layout.root)){
        const secs=Array.isArray(state.layout.sections)?state.layout.sections:[];
        state.layout.root=secs.map(s=>Object.assign({kind:"section"},s));
    }
    syncSectionsFromRoot();
}

function pxIfNumber(v){const t=(v||"").trim();return /^\d+(\.\d+)?$/.test(t)?t+"px":t;}
function pxToNumber(v){const t=(v||"").toString().trim();const m=t.match(/^(-?\d+(\.\d+)?)px$/i);if(m)return m[1];if(/^-?\d+(\.\d+)?$/.test(t))return t;return "";}
function parseSpacing(str,def){if(!str||typeof str!=="string")return def||[0,0,0,0];var parts=str.trim().split(/\s+/).map(s=>{var n=parseFloat(String(s).replace(/px$/i,""));return isNaN(n)?0:n;});if(parts.length===1)return [parts[0],parts[0],parts[0],parts[0]];if(parts.length===2)return [parts[0],parts[1],parts[0],parts[1]];if(parts.length>=4)return [parts[0],parts[1],parts[2],parts[3]];return def||[0,0,0,0];}
function spacingToCss(arr){if(!arr||arr.length!==4)return "";return arr.map(v=>v+"px").join(" ");}
function styleApply(node,s){if(!s)return;Object.keys(s).forEach(k=>{if(s[k]!==""&&s[k]!=null)node.style[k]=s[k];});}
function onRichTextKeys(node,onUpdate){
    node.addEventListener("keydown",e=>{
        if(!(e.ctrlKey||e.metaKey))return;
        const key=(e.key||"").toLowerCase();
        if(key==="b"){e.preventDefault();document.execCommand("bold");onUpdate();return;}
        if(key==="i"){e.preventDefault();document.execCommand("italic");onUpdate();return;}
        if(key==="u"){e.preventDefault();document.execCommand("underline");onUpdate();return;}
    });
}

function normalizeElementStyle(layout){
    (layout.sections||[]).forEach(sec=>{
        (sec.elements||[]).forEach(el=>{
            if(el.type==="video"||el.type==="image"){
                if(!el.style||typeof el.style!=="object")el.style={};
                var sw=(el.settings&&el.settings.width)||"";
                if(sw&&!el.style.width)el.style.width=sw;
            }
        });
        (sec.rows||[]).forEach(row=>{
            (row.columns||[]).forEach(col=>{
                (col.elements||[]).forEach(el=>{
                    if(el.type==="video"||el.type==="image"){
                        if(!el.style||typeof el.style!=="object")el.style={};
                        var sw=(el.settings&&el.settings.width)||"";
                        if(sw&&!el.style.width)el.style.width=sw;
                    }
                });
            });
        });
    });
}
function loadStep(id){
    state.sid=+id;
    const s=cur();
    state.layout=(s&&s.layout_json&&((Array.isArray(s.layout_json.root)&&s.layout_json.root.length)||(Array.isArray(s.layout_json.sections)&&s.layout_json.sections.length)))?clone(s.layout_json):defaults();
    ensureRootModel();
    normalizeElementStyle(state.layout);
    state.sel=null;
    state.carouselSel=null;
    undoHistory.length=0;
    saveMsg.textContent="Loaded "+s.title;
    render();
}

function selectedCarouselParent(){
    const cs=state.carouselSel;
    if(!cs||!cs.parent)return null;
    const p=cs.parent;
    if(p.scope==="section")return secEl(p.s,p.e);
    return el(p.s,p.r,p.c,p.e);
}
function selectedCarouselTarget(){
    const cs=state.carouselSel;
    const parent=selectedCarouselParent();
    if(!cs||!parent||parent.type!=="carousel")return null;
    parent.settings=parent.settings||{};
    const slides=ensureCarouselSlides(parent.settings);
    let slide=slides.find(s=>s.id===cs.slideId);
    if(!slide){
        const active=Number(parent.settings.activeSlide)||0;
        slide=slides[active]||slides[0]||null;
    }
    if(!slide)return null;
    if(cs.k==="row"){
        return (slide.rows||[]).find(r=>r.id===cs.rowId)||null;
    }
    if(cs.k==="col"){
        const rw=(slide.rows||[]).find(r=>r.id===cs.rowId);
        return rw?((rw.columns||[]).find(c=>c.id===cs.colId)||null):null;
    }
    if(cs.k==="el"){
        const rw=(slide.rows||[]).find(r=>r.id===cs.rowId);
        const cl=rw?((rw.columns||[]).find(c=>c.id===cs.colId)):null;
        return cl?((cl.elements||[]).find(e=>e.id===cs.elId)||null):null;
    }
    return null;
}
function selectedTarget(){
    if(state.carouselSel)return selectedCarouselTarget();
    const x=state.sel;
    if(!x)return null;
    if(x.k==="sec")return sec(x.s);
    if(x.k==="row")return row(x.s,x.r);
    if(x.k==="col")return col(x.s,x.r,x.c);
    if(x.k==="el"){
        if(x.scope==="section")return secEl(x.s,x.e);
        return el(x.s,x.r,x.c,x.e);
    }
    return null;
}
function selectedType(){
    const t=selectedTarget();
    if(!t)return "None";
    if(state.carouselSel){
        if(state.carouselSel.k==="el")return t.type||"element";
        if(state.carouselSel.k==="row")return "row";
        if(state.carouselSel.k==="col")return "column";
    }
    const x=state.sel;
    if(!x)return "None";
    if(x.k==="el")return (t.type||"element");
    if(x.k==="sec")return "section";
    if(x.k==="row")return "row";
    if(x.k==="col")return "column";
    return "None";
}
function titleCase(v){return (v||"").replace(/[_-]/g," ").replace(/\b\w/g,m=>m.toUpperCase());}
function isSelectedElementMedia(){
    const t=selectedTarget();
    if(!t)return false;
    if(state.carouselSel){
        return state.carouselSel.k==="el" && (t.type==="image" || t.type==="video");
    }
    return !!(state.sel && state.sel.k==="el" && (t.type==="image" || t.type==="video"));
}
function clearSelectedMediaContent(){
    if(!isSelectedElementMedia())return false;
    const t=selectedTarget();
    if(!t)return false;
    saveToHistory();
    t.settings=t.settings||{};
    t.settings.src="";
    if(t.type==="video")t.content="";
    render();
    return true;
}
function defaultCarouselSlide(label){return {id:uid("sld"),label:label||"Slide #1",image:{src:"",alt:"Image"}};}
function ensureCarouselSlides(settings){
    settings=settings||{};
    if(!Array.isArray(settings.slides)||!settings.slides.length)settings.slides=[defaultCarouselSlide("Slide #1")];
    settings.slides=settings.slides.map((sl,idx)=>{
        var s=(sl&&typeof sl==="object")?sl:{};
        s.id=s.id||uid("sld");
        var imgSrc="",imgAlt="Image";
        if(s.image&&typeof s.image==="object"){
            imgSrc=String(s.image.src||"").trim();
            imgAlt=String(s.image.alt||"Image").trim()||"Image";
        }
        if(imgSrc===""){
            var rows=Array.isArray(s.rows)?s.rows:[];
            outer: for(var ri=0;ri<rows.length;ri++){
                var cols=Array.isArray(rows[ri]&&rows[ri].columns)?rows[ri].columns:[];
                for(var ci=0;ci<cols.length;ci++){
                    var els=Array.isArray(cols[ci]&&cols[ci].elements)?cols[ci].elements:[];
                    for(var ei=0;ei<els.length;ei++){
                        var e=els[ei]||{};
                        if(String(e.type||"")==="image"){
                            var st=e.settings||{};
                            var src=String(st.src||"").trim();
                            if(src!==""){
                                imgSrc=src;
                                imgAlt=String(st.alt||"Image").trim()||"Image";
                                break outer;
                            }
                        }
                    }
                }
            }
        }
        s.image={src:imgSrc,alt:imgAlt};
        delete s.rows;
        s.label=String(s.label||("Slide #"+(idx+1)));
        return s;
    });
    return settings.slides;
}
function carouselElementDefaults(type){
    if(type==="section"||type==="row"||type==="column")return null;
    return createDefaultElement(type);
}
function carouselAllowsDropType(type){
    return ["section","heading","text","image","video","button","spacer","menu","form","row","column"].indexOf(type)>=0;
}
function normalizeCarouselDropType(type){
    var t=String(type||"").toLowerCase();
    if(t==="col")return "column";
    if(t==="countdown"||t==="carousel")return "";
    return t;
}
function renderCarouselPreviewItem(item,onDelete,onSelect,isSelected){
    var type=(item&&item.type)||"text";
    var wrap=document.createElement("div");
    wrap.className="builder-carousel-item";
    wrap.style.position="relative";
    if(isSelected){
        wrap.style.outline="2px solid #2563eb";
        wrap.style.outlineOffset="2px";
        wrap.style.borderRadius="8px";
    }
    wrap.addEventListener("click",e=>{e.stopPropagation();if(typeof onSelect==="function")onSelect();});
    wrap.addEventListener("mousedown",e=>e.stopPropagation());
    wrap.addEventListener("dragover",e=>{e.preventDefault();e.stopPropagation();});
    wrap.addEventListener("drop",e=>{e.preventDefault();e.stopPropagation();});
    if(typeof onDelete==="function"){
        var del=document.createElement("button");
        del.type="button";
        del.innerHTML='<i class="fas fa-trash"></i>';
        del.title="Delete component";
        del.style.position="absolute";
        del.style.top="6px";
        del.style.right="6px";
        del.style.width="24px";
        del.style.height="24px";
        del.style.border="1px solid rgba(255,255,255,0.45)";
        del.style.borderRadius="999px";
        del.style.background="rgba(239,68,68,0.95)";
        del.style.color="#fff";
        del.style.cursor="pointer";
        del.style.display="flex";
        del.style.alignItems="center";
        del.style.justifyContent="center";
        del.style.fontSize="11px";
        del.style.zIndex="2";
        del.style.opacity="0";
        del.style.pointerEvents="none";
        del.style.transition="opacity .15s ease";
        del.onclick=(e)=>{e.preventDefault();e.stopPropagation();onDelete();};
        wrap.appendChild(del);
        wrap.addEventListener("mouseenter",()=>{del.style.opacity="1";del.style.pointerEvents="auto";});
        wrap.addEventListener("mouseleave",()=>{del.style.opacity="0";del.style.pointerEvents="none";});
    }
    if(type==="heading"){
        var h=document.createElement("h3");
        h.contentEditable="true";
        h.textContent=(item&&item.content)||"Heading";
        h.style.margin="0";
        h.style.fontSize=((item&&item.style&&item.style.fontSize)||"24px");
        h.style.color=((item&&item.style&&item.style.color)||"inherit");
        h.oninput=()=>{item.content=h.innerHTML||"";};
        wrap.appendChild(h);
    }else if(type==="text"){
        var p=document.createElement("p");
        p.contentEditable="true";
        p.textContent=(item&&item.content)||"Text";
        p.style.margin="0";
        p.style.color=((item&&item.style&&item.style.color)||"inherit");
        p.oninput=()=>{item.content=p.innerHTML||"";};
        wrap.appendChild(p);
    }else if(type==="image"){
        var imgBody=document.createElement("div");
        imgBody.innerHTML=(item.settings&&item.settings.src)?'<img src="'+item.settings.src+'" alt="'+(item.settings.alt||"Image")+'" style="max-width:100%;height:auto;display:block;">':'<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;">Image placeholder</div>';
        wrap.appendChild(imgBody);
    }else if(type==="video"){
        const raw=(item.settings&&item.settings.src)||"";
        const vurl=raw?(raw.startsWith("http")?raw:"https://"+raw.replace(/^\/*/,"")):"";
        const wrapStyle="position:relative;width:100%;min-height:200px;padding-top:56.25%;background:#0f172a;border-radius:8px;overflow:hidden;box-sizing:border-box;display:flex;align-items:center;justify-content:center;pointer-events:none;";
        var vidBody=document.createElement("div");
        if(vurl){
            const label=vurl.length>50?vurl.slice(0,47)+"...":vurl;
            vidBody.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.9);padding:12px;text-align:center;"><span style="font-size:32px;margin-bottom:8px;opacity:0.9;">▶</span><span style="font-size:12px;font-weight:700;">Video</span><span style="font-size:11px;opacity:0.8;word-break:break-all;max-width:100%;">'+label.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span></div></div>';
        } else {
            vidBody.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.8);padding:12px;"><span style="font-size:28px;margin-bottom:6px;">▶</span><span style="font-size:12px;">Video URL placeholder</span><span style="font-size:11px;margin-top:4px;">Paste link or upload</span></div></div>';
        }
        wrap.appendChild(vidBody);
    }else if(type==="button"){
        var b=document.createElement("button");
        b.type="button";
        b.contentEditable="true";
        b.textContent=(item&&item.content)||"Button";
        b.style.border="none";
        b.style.borderRadius=((item&&item.style&&item.style.borderRadius)||"999px");
        b.style.padding=((item&&item.style&&item.style.padding)||"8px 14px");
        b.style.background=((item&&item.style&&item.style.backgroundColor)||"#2563eb");
        b.style.color=((item&&item.style&&item.style.color)||"#fff");
        b.oninput=()=>{item.content=b.innerHTML||"";};
        wrap.appendChild(b);
    }else if(type==="spacer"){
        var sp=document.createElement("div");
        sp.style.height=((item&&item.style&&item.style.height)||"24px");
        sp.style.background="repeating-linear-gradient(90deg,#f1f5f9,#f1f5f9 8px,#e2e8f0 8px,#e2e8f0 16px)";
        sp.style.borderRadius="6px";
        wrap.appendChild(sp);
    }else if(type==="menu"){
        var ms=(item&&item.settings)||{};
        var menuItems=Array.isArray(ms.items)&&ms.items.length?ms.items:[{label:"Menu item",url:"#",newWindow:false,hasSubmenu:false}];
        var menuUl=document.createElement("ul");
        menuUl.style.listStyle="none";
        menuUl.style.margin="0";
        menuUl.style.padding="0";
        menuUl.style.display="flex";
        menuUl.style.flexWrap="wrap";
        menuUl.style.gap=String(Number(ms.itemGap)||13)+"px";
        menuItems.forEach((mi,idx)=>{
            var li=document.createElement("li");
            var a=document.createElement("a");
            a.href="#";
            a.textContent=(mi&&mi.label)||("Menu item "+(idx+1));
            a.style.textDecoration="none";
            a.style.textUnderlineOffset="3px";
            li.appendChild(a);
            menuUl.appendChild(li);
        });
        wrap.appendChild(menuUl);
    }else if(type==="form"){
        var fm=document.createElement("div");
        var formFields=[["First name","First name"],["Last name","Last name"],["Email","Email"],["Phone (09XXXXXXXXX)","09XXXXXXXXX"],["Province","Province"],["City / Municipality","City / Municipality"],["Barangay","Barangay"],["Street","Street"]];
        var html="";
        formFields.forEach(function(ff){
            html+='<label style="display:block;margin-bottom:4px;">'+ff[0]+'</label>';
            html+='<input type="text" placeholder="'+ff[1]+'" style="width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px;">';
        });
        html+='<button type="button" style="padding:8px 12px;border:0;border-radius:8px;background:#2563eb;color:#fff;">'+(((item&&item.content)||"Submit"))+'</button>';
        fm.innerHTML=html;
        wrap.appendChild(fm);
    }else if(type==="carousel"){
        var nested=document.createElement("div");
        nested.style.padding="12px";
        nested.style.border="1px dashed #93c5fd";
        nested.style.borderRadius="8px";
        nested.style.color="#1e40af";
        nested.style.fontWeight="700";
        nested.textContent="Nested Carousel";
        wrap.appendChild(nested);
    }else{
        var t=document.createElement("div");
        t.textContent=type;
        wrap.appendChild(t);
    }
    return wrap;
}

function createDefaultRow(){return {id:uid("row"),style:{gap:"8px"},columns:[]};}
function createDefaultColumn(){return {id:uid("col"),style:{flex:"1 1 240px"},elements:[]};}
function createDefaultSection(){return {id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff"},settings:{contentWidth:"full"},elements:[],rows:[]};}
function createRootItem(type){
    if(type==="section")return Object.assign({kind:"section"},createDefaultSection());
    if(type==="row")return Object.assign({kind:"row"},createDefaultRow());
    if(type==="column")return Object.assign({kind:"column"},createDefaultColumn());
    const it=createDefaultElement(type);
    return it?Object.assign({kind:"el"},it):null;
}
function createDefaultElement(type){
    const d={heading:{content:"Heading",style:{fontSize:"32px"},settings:{}},text:{content:"Text",style:{fontSize:"16px"},settings:{}},menu:{content:"",style:{fontSize:"16px"},settings:{items:[{label:"Home",url:"#",newWindow:false,hasSubmenu:false},{label:"Contact",url:"/contact",newWindow:false,hasSubmenu:false}],itemGap:13,activeIndex:0,menuAlign:"left",underlineColor:""}},carousel:{content:"",style:{padding:"10px 10px 10px 10px"},settings:{slides:[defaultCarouselSlide("Slide #1")],activeSlide:0,vAlign:"center",alignment:"left",showArrows:true,controlsColor:"#64748b",arrowColor:"#ffffff",fixedWidth:500,fixedHeight:500}},image:{content:"",style:{width:"100%"},settings:{src:"",alt:"Image",alignment:"left"}},button:{content:"Click Me",style:{backgroundColor:"#2563eb",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center"},settings:{link:"#"}},form:{content:"Submit",style:{},settings:{alignment:"left",width:"100%",fields:[{type:"first_name",label:"First name"},{type:"last_name",label:"Last name"},{type:"email",label:"Email"},{type:"phone_number",label:"Phone (09XXXXXXXXX)"}]}},video:{content:"",style:{},settings:{src:"",alignment:"left"}},spacer:{content:"",style:{height:"24px"},settings:{}}}[type]||null;
    if(!d)return null;
    return {id:uid("el"),type:type,content:d.content,style:clone(d.style),settings:clone(d.settings)};
}

function addComponent(type){
    saveToHistory();
    const p=state.sel||{};
    ensureRootModel();
    const rs=rootItems();
    if(!p||!p.k){
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    const pRootCtx=sectionRootContext(p.s);
    if(pRootCtx.isWrap){
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    if(type==="section"){rs.push(createRootItem("section"));syncSectionsFromRoot();return;}
    let s=sec(p.s)||state.layout.sections[0];
    if(!s){
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    s.elements=Array.isArray(s.elements)?s.elements:[];
    if(type==="carousel"){
        const car=createDefaultElement("carousel");
        if(!car)return;
        s.elements.push(car);
        state.sel={k:"el",scope:"section",s:s.id,e:car.id};
        return;
    }
    if(type==="row"){(s.rows=s.rows||[]).push(createDefaultRow());return;}
    let r=row(p.s,p.r)||(s?.rows||[])[0];
    if(!r){
        if(type==="column"){
            r=createDefaultRow();
            s.rows=s.rows||[];
            s.rows.push(r);
        }else{
            const itNoGrid=createDefaultElement(type);
            if(!itNoGrid)return;
            s.elements.push(itNoGrid);
            state.sel={k:"el",scope:"section",s:s.id,e:itNoGrid.id};
            return;
        }
    }
    if(type==="column"){
        r.columns=r.columns||[];
        if(r.columns.length>=4)return;
        r.columns.push(createDefaultColumn());
        return;
    }
    let c=col(p.s,p.r,p.c)||(r?.columns||[])[0];
    if(!c){
        const itNoGrid=createDefaultElement(type);
        if(!itNoGrid)return;
        s.elements.push(itNoGrid);
        state.sel={k:"el",scope:"section",s:s.id,e:itNoGrid.id};
        return;
    }
    const it=createDefaultElement(type);
    if(!it)return;
    c.elements.push(it);
}

function dropPlacement(ev,node){
    var rect=node.getBoundingClientRect();
    var y=Number(ev.clientY)||0;
    return y<(rect.top+rect.height/2)?"before":"after";
}

function addComponentAt(type,target,place){
    place=place==="before"?"before":"after";
    saveToHistory();
    var t=target||{};
    ensureRootModel();
    const rs=rootItems();
    state.layout.sections=Array.isArray(state.layout.sections)?state.layout.sections:[];
    if(!t||!t.k){
        var rootNew=createRootItem(type);
        if(!rootNew)return false;
        rs.push(rootNew);
        syncSectionsFromRoot();
        return true;
    }
    var tRootCtx=sectionRootContext(t.s);
    if(tRootCtx.isWrap&&tRootCtx.index>=0){
        var wrapInsert=createRootItem(type);
        if(!wrapInsert)return false;
        var wrapIdx=(place==="before"?tRootCtx.index:tRootCtx.index+1);
        rs.splice(Math.max(0,Math.min(wrapIdx,rs.length)),0,wrapInsert);
        syncSectionsFromRoot();
        state.sel=(type==="section")?{k:"sec",s:wrapInsert.id}:null;
        return true;
    }
    if(type==="section"){
        var sIdx=state.layout.sections.length;
        if(t.s){
            var curS=state.layout.sections.findIndex(x=>x.id===t.s);
            if(curS>=0)sIdx=(place==="before"?curS:curS+1);
        }
        var ns=createRootItem("section");
        rs.splice(Math.max(0,Math.min(sIdx,rs.length)),0,ns);
        syncSectionsFromRoot();
        state.sel={k:"sec",s:ns.id};
        return true;
    }

    var s=sec(t.s)||state.layout.sections[0];
    if(!s){
        var rootFallback=createRootItem(type);
        if(!rootFallback)return false;
        rs.push(rootFallback);
        syncSectionsFromRoot();
        return true;
    }
    s.elements=Array.isArray(s.elements)?s.elements:[];
    s.rows=Array.isArray(s.rows)?s.rows:[];
    if(type==="carousel"){
        var secInsertIdx=s.elements.length;
        if(t.k==="el" && t.scope==="section"){
            var sf=s.elements.findIndex(x=>x.id===t.e);
            if(sf>=0)secInsertIdx=(place==="before"?sf:sf+1);
        }else if(t.k==="sec"){
            secInsertIdx=(place==="before"?0:s.elements.length);
        }
        var secCarousel=createDefaultElement("carousel");
        if(!secCarousel)return false;
        s.elements.splice(Math.max(0,Math.min(secInsertIdx,s.elements.length)),0,secCarousel);
        state.sel={k:"el",scope:"section",s:s.id,e:secCarousel.id};
        return true;
    }

    if(type==="row"){
        var rIdx=s.rows.length;
        if(t.k==="row"){
            var ri=s.rows.findIndex(x=>x.id===t.r);
            if(ri>=0)rIdx=(place==="before"?ri:ri+1);
        }else if(t.k==="col"||t.k==="el"){
            var rr=s.rows.findIndex(x=>x.id===t.r);
            if(rr>=0)rIdx=(place==="before"?rr:rr+1);
        }else if(t.k==="sec"){
            rIdx=(place==="before"?0:s.rows.length);
        }
        var nr=createDefaultRow();
        s.rows.splice(Math.max(0,Math.min(rIdx,s.rows.length)),0,nr);
        state.sel={k:"row",s:s.id,r:nr.id};
        return true;
    }

    if(type!=="column"){
        if((t.k==="sec"||t.k==="row"||t.k==="col") && !col(t.s,t.r,t.c)){
            var secIdx=(place==="before"?0:s.elements.length);
            var secItem=createDefaultElement(type);
            if(!secItem)return false;
            s.elements.splice(Math.max(0,Math.min(secIdx,s.elements.length)),0,secItem);
            state.sel={k:"el",scope:"section",s:s.id,e:secItem.id};
            return true;
        }
        if(t.k==="el" && t.scope==="section"){
            var sFound=s.elements.findIndex(x=>x.id===t.e);
            var sIns=sFound>=0?(place==="before"?sFound:sFound+1):s.elements.length;
            var sItem=createDefaultElement(type);
            if(!sItem)return false;
            s.elements.splice(Math.max(0,Math.min(sIns,s.elements.length)),0,sItem);
            state.sel={k:"el",scope:"section",s:s.id,e:sItem.id};
            return true;
        }
    }

    var r=row(t.s,t.r);
    if(!r && type==="column"){
        var rAutoIdx=s.rows.length;
        if(t.k==="sec")rAutoIdx=(place==="before"?0:s.rows.length);
        var rAuto=createDefaultRow();
        s.rows.splice(Math.max(0,Math.min(rAutoIdx,s.rows.length)),0,rAuto);
        r=rAuto;
    }
    if(!r)return false;
    r.columns=Array.isArray(r.columns)?r.columns:[];

    if(type==="column"){
        if(r.columns.length>=4)return false;
        var cIdx=r.columns.length;
        if(t.k==="col"){
            var ci=r.columns.findIndex(x=>x.id===t.c);
            if(ci>=0)cIdx=(place==="before"?ci:ci+1);
        }else if(t.k==="el"){
            var cFromEl=r.columns.findIndex(x=>x.id===t.c);
            if(cFromEl>=0)cIdx=(place==="before"?cFromEl:cFromEl+1);
        }else if(t.k==="row"){
            cIdx=(place==="before"?0:r.columns.length);
        }else if(t.k==="sec"){
            cIdx=(place==="before"?0:r.columns.length);
        }
        var nc=createDefaultColumn();
        r.columns.splice(Math.max(0,Math.min(cIdx,r.columns.length)),0,nc);
        state.sel={k:"col",s:s.id,r:r.id,c:nc.id};
        return true;
    }

    var c=col(t.s,t.r,t.c);
    if(!c)return false;
    c.elements=Array.isArray(c.elements)?c.elements:[];
    var eIdx=(place==="before"?0:c.elements.length);
    if(t.k==="el"){
        var ei=c.elements.findIndex(x=>x.id===t.e);
        if(ei>=0)eIdx=(place==="before"?ei:ei+1);
    }else if(t.k==="col"||t.k==="row"||t.k==="sec"){
        eIdx=(place==="before"?0:c.elements.length);
    }
    var it=createDefaultElement(type);
    if(!it)return false;
    c.elements.splice(Math.max(0,Math.min(eIdx,c.elements.length)),0,it);
    state.sel={k:"el",s:s.id,r:r.id,c:c.id,e:it.id};
    return true;
}

function removeSelected(){
    if(state.carouselSel){
        const cs=state.carouselSel;
        const parent=selectedCarouselParent();
        if(!parent||parent.type!=="carousel"){state.carouselSel=null;render();return;}
        saveToHistory();
        parent.settings=parent.settings||{};
        const slides=ensureCarouselSlides(parent.settings);
        let slide=slides.find(s=>s.id===cs.slideId);
        if(!slide){
            const active=Number(parent.settings.activeSlide)||0;
            slide=slides[active]||slides[0]||null;
        }
        if(!slide){state.carouselSel=null;render();return;}
        if(cs.k==="el"){
            const rw=(slide.rows||[]).find(r=>r.id===cs.rowId);
            const cl=rw?((rw.columns||[]).find(c=>c.id===cs.colId)):null;
            if(cl)cl.elements=(cl.elements||[]).filter(i=>i.id!==cs.elId);
        }else if(cs.k==="col"){
            const rw=(slide.rows||[]).find(r=>r.id===cs.rowId);
            if(rw && Array.isArray(rw.columns)){
                rw.columns=rw.columns.filter(i=>i.id!==cs.colId);
            }
        }else if(cs.k==="row"){
            if(Array.isArray(slide.rows)){
                slide.rows=slide.rows.filter(i=>i.id!==cs.rowId);
            }
        }
        state.carouselSel=null;
        render();
        return;
    }
    const x=state.sel;if(!x)return;
    saveToHistory();
    var didRootDelete=false;
    const xRootCtx=sectionRootContext(x.s);
    if(x.k==="el"){
        if(x.scope==="section"){
            if(xRootCtx.isWrap&&xRootCtx.root&&String(xRootCtx.root.kind||"").toLowerCase()==="el"&&xRootCtx.index>=0){
                rootItems().splice(xRootCtx.index,1);
                didRootDelete=true;
            } else {
                const s=sec(x.s);if(!s)return;
                s.elements=(s.elements||[]).filter(i=>i.id!==x.e);
            }
        } else {
            const s=sec(x.s);if(!s)return;
            const c=col(x.s,x.r,x.c);if(!c)return;c.elements=(c.elements||[]).filter(i=>i.id!==x.e);
        }
    }
    else if(x.k==="col"){
        if(xRootCtx.isWrap&&xRootCtx.root&&String(xRootCtx.root.kind||"").toLowerCase()==="column"&&xRootCtx.index>=0){
            rootItems().splice(xRootCtx.index,1);
            didRootDelete=true;
        } else {
            const r=row(x.s,x.r);if(!r)return;r.columns=(r.columns||[]).filter(i=>i.id!==x.c);
        }
    }
    else if(x.k==="row"){
        if(xRootCtx.isWrap&&xRootCtx.root&&String(xRootCtx.root.kind||"").toLowerCase()==="row"&&xRootCtx.index>=0){
            rootItems().splice(xRootCtx.index,1);
            didRootDelete=true;
        } else {
            const s=sec(x.s);if(!s)return;s.rows=(s.rows||[]).filter(i=>i.id!==x.r);
        }
    }
    else if(x.k==="sec"){
        if(xRootCtx.index>=0){
            rootItems().splice(xRootCtx.index,1);
            didRootDelete=true;
        } else {
            state.layout.sections=(state.layout.sections||[]).filter(i=>i.id!==x.s);
        }
    }
    if(didRootDelete)syncSectionsFromRoot();
    state.sel=null;
    state.carouselSel=null;
    render();
}

function renderElement(item,ctx){
    const w=document.createElement("div");w.className="el";
    if(item.type==="carousel")w.classList.add("el--carousel");
    if(item.type==="form")w.classList.add("el--form");
    if(item.type!=="button")styleApply(w,item.style||{});
    else if(item.style&&item.style.margin)w.style.margin=item.style.margin;
    if(state.sel&&state.sel.k==="el"&&state.sel.e===item.id)w.classList.add("sel");
    w.onclick=e=>{e.stopPropagation();state.carouselSel=null;state.sel=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};render();};
    w.ondragover=e=>{e.preventDefault();e.stopPropagation();};
    w.ondrop=e=>{
        e.preventDefault();
        e.stopPropagation();
        if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
        const t=e.dataTransfer.getData("c");
        if(!t)return;
        state.carouselSel=null;
        if(addComponentAt(t,ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id},dropPlacement(e,w)))render();
    };
    if(item.type==="heading"||item.type==="text"){const n=document.createElement(item.type==="heading"?"h2":"p");n.contentEditable="true";n.style.margin="0";n.innerHTML=item.content||"";styleApply(n,item.style||{});n.oninput=()=>{item.content=n.innerHTML||"";};onRichTextKeys(n,()=>{item.content=n.innerHTML||"";});w.appendChild(n);}
    else if(item.type==="button"){
        var wb=(item.settings&&item.settings.widthBehavior)||"fluid",al=(item.settings&&item.settings.alignment)||((item.style&&item.style.textAlign)||"center");
        w.classList.add(wb==="fill"?"el--button-fill":"el--button");
        w.style.display="flex";w.style.justifyContent=al==="right"?"flex-end":al==="center"?"center":"flex-start";
        const b=document.createElement("button");b.type="button";b.contentEditable="true";b.innerHTML=item.content||"Button";
        styleApply(b,item.style||{});b.style.border="none";b.style.display=wb==="fill"?"flex":"inline-flex";b.style.width=wb==="fill"?"100%":"auto";b.style.alignItems="center";b.style.justifyContent="center";if(!(item.style&&item.style.backgroundColor))b.style.backgroundColor="#2563eb";if(!(item.style&&item.style.color))b.style.color="#fff";if(!(item.style&&item.style.padding))b.style.padding="10px 18px";if(!(item.style&&item.style.borderRadius))b.style.borderRadius="999px";
        b.oninput=()=>{item.content=b.innerHTML||"";};onRichTextKeys(b,()=>{item.content=b.innerHTML||"";});w.appendChild(b);
    }
    else if(item.type==="image"){w.innerHTML=(item.settings&&item.settings.src)?'<img src="'+item.settings.src+'" alt="'+(item.settings.alt||"Image")+'" style="max-width:100%;height:auto;display:block;">':'<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;">Image placeholder</div>';}
    else if(item.type==="form"){
        item.settings=item.settings||{};
        var fal=(item.settings.alignment)||"left";
        var fw=(item.style&&item.style.width)||(item.settings&&item.settings.width)||"100%";
        w.style.display="block";
        w.style.boxSizing="border-box";
        w.style.width=fw;
        w.style.maxWidth="100%";
        if(fal==="center"){w.style.marginLeft="auto";w.style.marginRight="auto";}
        else if(fal==="right"){w.style.marginLeft="auto";w.style.marginRight="0";}
        else{w.style.marginLeft="0";w.style.marginRight="auto";}
        var flist=[{type:"first_name",label:"First name"},{type:"last_name",label:"Last name"},{type:"email",label:"Email"},{type:"phone_number",label:"Phone (09XXXXXXXXX)"},{type:"province",label:"Province"},{type:"city_municipality",label:"City / Municipality"},{type:"barangay",label:"Barangay"},{type:"street",label:"Street"}];
        var formBox=document.createElement("div");
        formBox.style.width="100%";
        formBox.style.maxWidth="100%";
        formBox.style.boxSizing="border-box";
        flist.forEach(function(f){
            var lbl=(f&&f.label)?String(f.label):String((f&&f.type)||"Field");
            var lab=document.createElement("label");
            lab.style.display="block";
            lab.style.marginBottom="4px";
            lab.textContent=lbl;
            var inp=document.createElement("input");
            inp.disabled=true;
            inp.placeholder=lbl;
            inp.style.width="100%";
            inp.style.padding="8px";
            inp.style.border="1px solid #cbd5e1";
            inp.style.borderRadius="8px";
            inp.style.marginBottom="8px";
            formBox.appendChild(lab);
            formBox.appendChild(inp);
        });
        var btn=document.createElement("button");
        btn.type="button";
        btn.className="fb-btn primary";
        btn.disabled=true;
        btn.textContent=(item.content||"Submit");
        formBox.appendChild(btn);
        w.innerHTML="";
        w.appendChild(formBox);
    }
    else if(item.type==="menu"){
        var ms=item.settings||{};
        var items=Array.isArray(ms.items)&&ms.items.length?ms.items:[{label:"Menu item",url:"#",newWindow:false,hasSubmenu:false}];
        var gap=Number(ms.itemGap);if(isNaN(gap))gap=13;
        var activeIdx=Number(ms.activeIndex);if(isNaN(activeIdx))activeIdx=0;
        var align=(ms.menuAlign||"left");
        const ul=document.createElement("ul");
        ul.style.listStyle="none";ul.style.margin="0";ul.style.padding="0";
        ul.style.display="flex";ul.style.flexWrap="wrap";ul.style.gap=gap+"px";
        ul.style.justifyContent=align==="right"?"flex-end":align==="center"?"center":"flex-start";
        items.forEach((mi,idx)=>{
            const li=document.createElement("li");
            const a=document.createElement("a");
            a.href=(mi&&mi.url)||"#";
            a.textContent=(mi&&mi.label)||("Menu item "+(idx+1));
            if(mi&&mi.newWindow)a.target="_blank";
            a.style.color=idx===activeIdx?((ms.activeColor)||"#a89c76"):((ms.textColor)||"#374151");
            a.style.textDecoration=ms.underlineColor?"underline":"none";
            if(ms.underlineColor)a.style.textDecorationColor=ms.underlineColor;
            a.style.textUnderlineOffset="3px";
            a.style.font="inherit";
            a.addEventListener("click",e=>e.preventDefault());
            li.appendChild(a);ul.appendChild(li);
        });
        w.innerHTML="";w.appendChild(ul);
    }
    else if(item.type==="carousel"){
        var cs=item.settings||{};
        var slides=ensureCarouselSlides(cs);
        var active=Number(cs.activeSlide);if(isNaN(active)||active<0||active>=slides.length)active=0;
        var curSlide=slides[active]||slides[0]||defaultCarouselSlide("Slide #1");
        var parentSel={scope:ctx.scope||"column",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};
        function slideHasContent(slide){
            if(!slide||typeof slide!=="object")return false;
            var img=slide.image&&typeof slide.image==="object"?slide.image:{};
            return String(img.src||"").trim()!=="";
        }
        function getTargetSlideForImageInsert(){
            if(slides.length===1 && !slideHasContent(slides[0])){
                return slides[0];
            }
            var s={id:uid("sld"),label:"Slide #"+(slides.length+1),image:{src:"",alt:"Image"}};
            slides.push(s);
            return s;
        }
        function addImageSlide(imageUrl,skipRender){
            var slide=getTargetSlideForImageInsert();
            if(!slide.id)slide.id=uid("sld");
            if(!slide.label||String(slide.label).trim()==="")slide.label="Slide #"+(slides.indexOf(slide)+1);
            slide.image={src:String(imageUrl||"").trim(),alt:"Image"};
            cs.activeSlide=slides.length-1;
            cs.carouselActiveRow=0;
            cs.carouselActiveCol=0;
            state.carouselSel=null;
            if(!skipRender){
                renderCanvas();
                renderSettings();
            }
        }
        function promptImageFilesForSlides(){
            var picker=document.createElement("input");
            picker.type="file";
            picker.accept="image/*";
            picker.multiple=true;
            picker.style.display="none";
            document.body.appendChild(picker);
            picker.onchange=()=>{
                var files=Array.from(picker.files||[]);
                if(!files.length){
                    document.body.removeChild(picker);
                    return;
                }
                var i=0;
                var added=0;
                var next=()=>{
                    if(i>=files.length){
                        renderCanvas();
                        renderSettings();
                        return;
                    }
                    var file=files[i++];
                    uploadImage(file,url=>{
                        addImageSlide(url,true);
                        added++;
                        next();
                    },"Image upload",()=>{next();});
                };
                next();
                picker.value="";
                document.body.removeChild(picker);
            };
            picker.click();
        }
        var simpleWrap=document.createElement("div");
        simpleWrap.className="carousel-live-editor";
        simpleWrap.style.position="relative";
        var fixedW=Number(cs.fixedWidth);
        var fixedH=Number(cs.fixedHeight);
        if(isNaN(fixedW)||fixedW<50)fixedW=500;
        if(isNaN(fixedH)||fixedH<50)fixedH=500;
        w.style.setProperty("width",fixedW+"px","important");
        w.style.setProperty("min-width",fixedW+"px","important");
        w.style.setProperty("max-width",fixedW+"px","important");
        w.style.setProperty("height",fixedH+"px","important");
        w.style.setProperty("min-height",fixedH+"px","important");
        w.style.setProperty("padding","0","important");
        var carAlign=String(cs.alignment||"left").toLowerCase();
        if(["left","center","right"].indexOf(carAlign)<0)carAlign="left";
        w.style.display="block";
        w.style.boxSizing="border-box";
        w.style.overflow="hidden";
        w.style.marginLeft=carAlign==="right"?"auto":(carAlign==="center"?"auto":"0");
        w.style.marginRight=carAlign==="left"?"auto":(carAlign==="center"?"auto":"0");
        simpleWrap.style.minHeight=(fixedH+"px");
        simpleWrap.style.borderRadius="8px";
        simpleWrap.style.background="#ffffff";
        simpleWrap.style.color="#0f172a";
        simpleWrap.style.border="1px solid #e2e8f0";
        simpleWrap.style.overflow="hidden";
        simpleWrap.style.display="flex";
        simpleWrap.style.alignItems="center";
        simpleWrap.style.justifyContent="center";
        simpleWrap.style.padding="16px";
        simpleWrap.ondragover=e=>{e.preventDefault();e.stopPropagation();};
        simpleWrap.ondrop=e=>{
            e.preventDefault();
            e.stopPropagation();
            var tp=normalizeCarouselDropType(e.dataTransfer.getData("c")||"");
            if(tp==="image"){
                saveToHistory();
                promptImageFilesForSlides();
            }
        };
        simpleWrap.style.width="100%";
        simpleWrap.style.maxWidth="100%";
        simpleWrap.style.margin="0";
        simpleWrap.style.height="100%";
        var activeSlide=slides[active]||slides[0]||defaultCarouselSlide("Slide #1");
        var activeImage=(activeSlide&&activeSlide.image&&typeof activeSlide.image==="object")?activeSlide.image:{src:"",alt:"Image"};
        var src=String(activeImage.src||"").trim();
        if(src!==""){
            simpleWrap.innerHTML='<img src="'+src.replace(/"/g,"&quot;")+'" alt="'+String(activeImage.alt||"Image").replace(/"/g,"&quot;")+'" style="width:100%;height:100%;object-fit:cover;display:block;border-radius:8px;">';
        }else{
            var emptyPickBtn=document.createElement("button");
            emptyPickBtn.type="button";
            emptyPickBtn.innerHTML='<span style="font-size:20px;line-height:1;">+</span><span style="font-size:12px;font-weight:700;">Select images</span>';
            emptyPickBtn.style.display="inline-flex";
            emptyPickBtn.style.alignItems="center";
            emptyPickBtn.style.gap="8px";
            emptyPickBtn.style.padding="10px 14px";
            emptyPickBtn.style.borderRadius="999px";
            emptyPickBtn.style.border="1px solid #93c5fd";
            emptyPickBtn.style.background="#ffffff";
            emptyPickBtn.style.color="#1d4ed8";
            emptyPickBtn.style.cursor="pointer";
            emptyPickBtn.style.zIndex="4";
            emptyPickBtn.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();promptImageFilesForSlides();};
            simpleWrap.appendChild(emptyPickBtn);
        }
        if(slides.length>1){
            var prevBtn=document.createElement("button");
            prevBtn.type="button";
            prevBtn.innerHTML='<i class="fas fa-chevron-left" aria-hidden="true"></i>';
            prevBtn.style.position="absolute";
            prevBtn.style.left="10px";
            prevBtn.style.top="50%";
            prevBtn.style.transform="translateY(-50%)";
            prevBtn.style.width="30px";
            prevBtn.style.height="30px";
            prevBtn.style.borderRadius="999px";
            prevBtn.style.border="1px solid #cbd5e1";
            prevBtn.style.background="#fff";
            prevBtn.style.display="flex";
            prevBtn.style.alignItems="center";
            prevBtn.style.justifyContent="center";
            prevBtn.style.padding="0";
            prevBtn.style.lineHeight="1";
            prevBtn.style.cursor="pointer";
            prevBtn.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();cs.activeSlide=(active-1+slides.length)%slides.length;renderCanvas();renderSettings();};
            simpleWrap.appendChild(prevBtn);
            var nextBtn=document.createElement("button");
            nextBtn.type="button";
            nextBtn.innerHTML='<i class="fas fa-chevron-right" aria-hidden="true"></i>';
            nextBtn.style.position="absolute";
            nextBtn.style.right="10px";
            nextBtn.style.top="50%";
            nextBtn.style.transform="translateY(-50%)";
            nextBtn.style.width="30px";
            nextBtn.style.height="30px";
            nextBtn.style.borderRadius="999px";
            nextBtn.style.border="1px solid #cbd5e1";
            nextBtn.style.background="#fff";
            nextBtn.style.display="flex";
            nextBtn.style.alignItems="center";
            nextBtn.style.justifyContent="center";
            nextBtn.style.padding="0";
            nextBtn.style.lineHeight="1";
            nextBtn.style.cursor="pointer";
            nextBtn.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();cs.activeSlide=(active+1)%slides.length;renderCanvas();renderSettings();};
            simpleWrap.appendChild(nextBtn);
        }
        w.innerHTML="";
        w.appendChild(simpleWrap);
        return w;
        function addDropToCarousel(type,rowIndex,colIndex){
            type=normalizeCarouselDropType(type);
            if(!carouselAllowsDropType(type))return false;
            var sIdx=Number(cs.activeSlide);if(isNaN(sIdx)||sIdx<0||sIdx>=slides.length)sIdx=0;
            var sl=slides[sIdx];if(!sl)return false;
            if(type==="section"||type==="row"){
                sl.rows=Array.isArray(sl.rows)?sl.rows:[];
                sl.rows.push({id:uid("row"),style:{gap:"8px"},columns:[]});
                cs.carouselActiveRow=sl.rows.length-1;
                cs.carouselActiveCol=0;
                return true;
            }
            sl.rows=Array.isArray(sl.rows)?sl.rows:[];
            if(!sl.rows.length){
                sl.rows.push({id:uid("row"),style:{gap:"8px"},columns:[]});
            }
            var rIdx=Number(rowIndex);if(isNaN(rIdx)||rIdx<0||rIdx>=sl.rows.length)rIdx=0;
            var rw=sl.rows[rIdx];rw.columns=Array.isArray(rw.columns)?rw.columns:[];
            if(type==="column"){
                rw.columns.push({id:uid("col"),style:{},elements:[]});
                cs.carouselActiveRow=rIdx;
                cs.carouselActiveCol=rw.columns.length-1;
                return true;
            }
            if(!rw.columns.length){
                rw.columns.push({id:uid("col"),style:{},elements:[]});
            }
            var cIdx=Number(colIndex);if(isNaN(cIdx)||cIdx<0||cIdx>=rw.columns.length)cIdx=0;
            var target=rw.columns[cIdx];
            target.elements=Array.isArray(target.elements)?target.elements:[];
            var newEl=carouselElementDefaults(type);
            if(!newEl)return false;
            target.elements.push(newEl);
            cs.carouselActiveRow=rIdx;
            cs.carouselActiveCol=cIdx;
            return true;
        }
        var wrap=document.createElement("div");
        wrap.className="carousel-live-editor";
        var bodyJustify=(cs.vAlign==="top"?"flex-start":(cs.vAlign==="bottom"?"flex-end":"center"));
        wrap.style.position="relative";
        wrap.style.minHeight="180px";
        wrap.style.borderRadius="8px";
        wrap.style.background="#ffffff";
        wrap.style.color="#0f172a";
        wrap.style.border="1px solid #e2e8f0";
        wrap.style.overflow="hidden";
        wrap.style.display="flex";
        wrap.style.flexDirection="column";
        wrap.style.alignItems="stretch";
        wrap.style.justifyContent=bodyJustify;
        wrap.style.padding="16px";
        var addImageSlideBtn=document.createElement("button");
        addImageSlideBtn.type="button";
        addImageSlideBtn.textContent="+";
        addImageSlideBtn.title="Add image slide";
        addImageSlideBtn.style.position="absolute";
        addImageSlideBtn.style.top="10px";
        addImageSlideBtn.style.right="10px";
        addImageSlideBtn.style.width="30px";
        addImageSlideBtn.style.height="30px";
        addImageSlideBtn.style.borderRadius="999px";
        addImageSlideBtn.style.border="1px solid #93c5fd";
        addImageSlideBtn.style.background="#ffffff";
        addImageSlideBtn.style.color="#1d4ed8";
        addImageSlideBtn.style.fontSize="20px";
        addImageSlideBtn.style.fontWeight="800";
        addImageSlideBtn.style.cursor="pointer";
        addImageSlideBtn.style.zIndex="4";
        addImageSlideBtn.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();promptImageFilesForSlides();};
        wrap.appendChild(addImageSlideBtn);
        wrap.ondragover=e=>{e.preventDefault();e.stopPropagation();};
        wrap.ondrop=e=>{
            e.preventDefault();
            e.stopPropagation();
            var tp=normalizeCarouselDropType(e.dataTransfer.getData("c")||"");
            if(!tp)return;
            saveToHistory();
            if(addDropToCarousel(tp,0,0)){renderCanvas();renderSettings();}
        };
        var body=document.createElement("div");
        body.className="carousel-live-body";
        body.style.width="100%";
        body.style.display="flex";
        body.style.flexDirection="column";
        body.style.gap="10px";
        body.style.flex="1 1 auto";
        body.style.height="100%";
        body.style.minHeight="0";
        body.style.background=(cs.bodyBgColor||"#ffffff");
        body.style.borderRadius="8px";
        body.style.padding="8px";
        body.ondragover=e=>{e.preventDefault();};
        body.ondrop=e=>{
            e.preventDefault();
            e.stopPropagation();
            var tp=normalizeCarouselDropType(e.dataTransfer.getData("c")||"");
            if(!tp)return;
            saveToHistory();
            if(addDropToCarousel(tp,0,0)){renderCanvas();renderSettings();}
        };
        (curSlide.rows||[]).forEach((rw,ri)=>{
            var rowBox=document.createElement("div");
            var rowHasAnyElements=Array.isArray(rw.columns)&&rw.columns.some(c=>Array.isArray(c.elements)&&c.elements.length>0);
            rowBox.style.display="flex";
            rowBox.style.flexWrap="wrap";
            rowBox.style.gap=((rw&&rw.style&&rw.style.gap)||"8px");
            rowBox.style.borderRadius="8px";
            rowBox.style.padding="6px";
            rowBox.style.position="relative";
            rowBox.style.minHeight=rowHasAnyElements?"auto":"54px";
            rowBox.style.border=rowHasAnyElements?"0":"1px dashed #dbeafe";
            rowBox.style.background=rowHasAnyElements?"transparent":"#ffffff";
            if(state.carouselSel && state.carouselSel.k==="row" && state.carouselSel.slideId===curSlide.id && state.carouselSel.rowId===rw.id){
                rowBox.style.outline="2px solid #2563eb";
                rowBox.style.outlineOffset="1px";
            }
            rowBox.onclick=e=>{if(e.target!==rowBox)return;e.stopPropagation();state.carouselSel={parent:parentSel,slideId:curSlide.id,k:"row",rowId:rw.id};render();};
            rowBox.ondragover=e=>{e.preventDefault();};
            rowBox.ondrop=e=>{
                e.preventDefault();
                e.stopPropagation();
                var tp=normalizeCarouselDropType(e.dataTransfer.getData("c")||"");
                if(!tp)return;
                saveToHistory();
                if(addDropToCarousel(tp,ri,0)){renderCanvas();renderSettings();}
            };
            var delRow=document.createElement("button");
            delRow.type="button";
            delRow.innerHTML='<i class="fas fa-trash"></i>';
            delRow.title="Delete row";
            delRow.style.position="absolute";
            delRow.style.top="6px";
            delRow.style.right="6px";
            delRow.style.width="24px";
            delRow.style.height="24px";
            delRow.style.border="1px solid rgba(255,255,255,0.45)";
            delRow.style.borderRadius="999px";
            delRow.style.background="rgba(239,68,68,0.95)";
            delRow.style.color="#fff";
            delRow.style.cursor="pointer";
            delRow.style.display="flex";
            delRow.style.alignItems="center";
            delRow.style.justifyContent="center";
            delRow.style.fontSize="11px";
            delRow.style.zIndex="3";
            delRow.style.opacity="0";
            delRow.style.pointerEvents="none";
            delRow.style.transition="opacity .15s ease";
            delRow.onclick=(e)=>{e.preventDefault();e.stopPropagation();saveToHistory();curSlide.rows.splice(ri,1);cs.carouselActiveRow=0;cs.carouselActiveCol=0;renderCanvas();renderSettings();};
            rowBox.appendChild(delRow);
            rowBox.addEventListener("mousemove",(e)=>{
                var isDirectHover=e.target===rowBox;
                delRow.style.opacity=isDirectHover?"1":"0";
                delRow.style.pointerEvents=isDirectHover?"auto":"none";
            });
            rowBox.addEventListener("mouseleave",()=>{delRow.style.opacity="0";delRow.style.pointerEvents="none";});
            (rw.columns||[]).forEach((cl,ci)=>{
                var colBox=document.createElement("div");
                var hasColElements=Array.isArray(cl.elements)&&cl.elements.length>0;
                colBox.style.flex=(cl&&cl.style&&cl.style.flex)||"1 1 220px";
                colBox.style.minWidth="180px";
                colBox.style.display="flex";
                colBox.style.flexDirection="column";
                colBox.style.gap="8px";
                colBox.style.minHeight=hasColElements?"60px":"44px";
                colBox.style.background="#ffffff";
                colBox.style.border="1px dashed #dbeafe";
                colBox.style.borderRadius="8px";
                colBox.style.padding="8px";
                colBox.style.position="relative";
                if(state.carouselSel && state.carouselSel.k==="col" && state.carouselSel.slideId===curSlide.id && state.carouselSel.rowId===rw.id && state.carouselSel.colId===cl.id){
                    colBox.style.outline="2px solid #2563eb";
                    colBox.style.outlineOffset="1px";
                }
                colBox.onclick=e=>{if(e.target!==colBox)return;e.stopPropagation();state.carouselSel={parent:parentSel,slideId:curSlide.id,k:"col",rowId:rw.id,colId:cl.id};render();};
                colBox.ondragover=e=>{e.preventDefault();};
                colBox.ondrop=e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    var tp=normalizeCarouselDropType(e.dataTransfer.getData("c")||"");
                    if(!tp)return;
                    saveToHistory();
                    if(addDropToCarousel(tp,ri,ci)){renderCanvas();renderSettings();}
                };
                var delCol=document.createElement("button");
                delCol.type="button";
                delCol.innerHTML='<i class="fas fa-trash"></i>';
                delCol.title="Delete column";
                delCol.style.position="absolute";
                delCol.style.top="6px";
                delCol.style.right="6px";
                delCol.style.width="24px";
                delCol.style.height="24px";
                delCol.style.border="1px solid rgba(255,255,255,0.45)";
                delCol.style.borderRadius="999px";
                delCol.style.background="rgba(239,68,68,0.95)";
                delCol.style.color="#fff";
                delCol.style.cursor="pointer";
                delCol.style.display="flex";
                delCol.style.alignItems="center";
                delCol.style.justifyContent="center";
                delCol.style.fontSize="11px";
                delCol.style.zIndex="3";
                delCol.style.opacity="0";
                delCol.style.pointerEvents="none";
                delCol.style.transition="opacity .15s ease";
                delCol.onclick=(e)=>{e.preventDefault();e.stopPropagation();saveToHistory();rw.columns.splice(ci,1);cs.carouselActiveCol=0;renderCanvas();renderSettings();};
                colBox.appendChild(delCol);
                colBox.addEventListener("mousemove",(e)=>{
                    var isDirectHover=e.target===colBox;
                    delCol.style.opacity=isDirectHover?"1":"0";
                    delCol.style.pointerEvents=isDirectHover?"auto":"none";
                });
                colBox.addEventListener("mouseleave",()=>{delCol.style.opacity="0";delCol.style.pointerEvents="none";});
                (cl.elements||[]).forEach((it,ei)=>colBox.appendChild(renderCarouselPreviewItem(it,()=>{
                    saveToHistory();
                    var list=Array.isArray(cl.elements)?cl.elements:[];
                    if(ei>=0&&ei<list.length)list.splice(ei,1);
                    cl.elements=list;
                    renderCanvas();
                    renderSettings();
                },()=>{
                    state.carouselSel={parent:parentSel,slideId:curSlide.id,k:"el",rowId:rw.id,colId:cl.id,elId:it.id};
                    render();
                },!!(state.carouselSel&&state.carouselSel.k==="el"&&state.carouselSel.slideId===curSlide.id&&state.carouselSel.rowId===rw.id&&state.carouselSel.colId===cl.id&&state.carouselSel.elId===it.id))));
                rowBox.appendChild(colBox);
            });
            body.appendChild(rowBox);
        });
        wrap.appendChild(body);
        var hasElements=(curSlide.rows||[]).some(rw=>Array.isArray(rw.columns)&&rw.columns.some(cl=>Array.isArray(cl.elements)&&cl.elements.length));
        var hasStructuralContent=(curSlide.rows||[]).length>0 || (curSlide.rows||[]).some(rw=>Array.isArray(rw.columns) && rw.columns.length>0);
        if(!(hasElements||hasStructuralContent)){
            body.style.display="none";
            wrap.style.alignItems="center";
            wrap.style.justifyContent="center";
            addImageSlideBtn.style.display="none";
            var emptyAdd=document.createElement("button");
            emptyAdd.type="button";
            emptyAdd.innerHTML='<span style="font-size:24px;line-height:1;">+</span><span style="font-size:12px;font-weight:700;">Add image slide</span>';
            emptyAdd.style.display="inline-flex";
            emptyAdd.style.alignItems="center";
            emptyAdd.style.gap="8px";
            emptyAdd.style.padding="10px 14px";
            emptyAdd.style.borderRadius="999px";
            emptyAdd.style.border="1px solid #93c5fd";
            emptyAdd.style.background="#ffffff";
            emptyAdd.style.color="#1d4ed8";
            emptyAdd.style.cursor="pointer";
            emptyAdd.style.zIndex="4";
            emptyAdd.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();promptImageFilesForSlides();};
            wrap.appendChild(emptyAdd);
        }
        w.innerHTML="";w.appendChild(wrap);
    }
    else if(item.type==="video"){
        const raw=(item.settings&&item.settings.src)||"";
        const vurl=raw?(raw.startsWith("http")?raw:"https://"+raw.replace(/^\/*/,"")):"";
        const wrapStyle="position:relative;width:100%;min-height:200px;padding-top:56.25%;background:#0f172a;border-radius:8px;overflow:hidden;box-sizing:border-box;display:flex;align-items:center;justify-content:center;pointer-events:none;";
        if(vurl){
            const label=vurl.length>50?vurl.slice(0,47)+"...":vurl;
            w.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.9);padding:12px;text-align:center;"><span style="font-size:32px;margin-bottom:8px;opacity:0.9;">▶</span><span style="font-size:12px;font-weight:700;">Video</span><span style="font-size:11px;opacity:0.8;word-break:break-all;max-width:100%;">'+label.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span></div></div>';
        } else w.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.8);padding:12px;"><span style="font-size:28px;margin-bottom:6px;">▶</span><span style="font-size:12px;">Video URL placeholder</span><span style="font-size:11px;margin-top:4px;">Paste link or upload</span></div></div>';
    }
    else if(item.type==="spacer"){const h=((item.style&&item.style.height)||"24px");const isSel=!!(state.sel&&state.sel.k==="el"&&state.sel.e===item.id);const bg=isSel?'repeating-linear-gradient(90deg,#f1f5f9,#f1f5f9 8px,#e2e8f0 8px,#e2e8f0 16px)':'transparent';w.innerHTML='<div style="height:'+h+';background:'+bg+'"></div>';}
    if(item.type==="image"||item.type==="video"){var a=(item.settings&&item.settings.alignment)||"left";w.style.setProperty("display","flex");w.style.setProperty("justify-content",a==="right"?"flex-end":a==="center"?"center":"flex-start");w.style.setProperty("margin-left",a==="left"?"0":"auto");w.style.setProperty("margin-right",a==="right"?"0":"auto");}
    return w;
}

function renderCanvas(){
    ensureRootModel();
    canvas.innerHTML="";
    var widthMap={full:"",wide:"1200px",medium:"992px",small:"768px",xsmall:"576px"};
    (state.layout.sections||[]).forEach(s=>{
        var contentWidth=((s.settings&&s.settings.contentWidth)||"full");
        var secElements=Array.isArray(s.elements)?s.elements:[];
        var secRows=Array.isArray(s.rows)?s.rows:[];
        var isBareCarouselSection=!!(s.__bareCarouselWrap||(!s.__rootWrap&&secRows.length===0&&secElements.length===1&&secElements[0]&&secElements[0].type==="carousel"));
        var isBareRootWrap=!!s.__bareRootWrap;
        var isBareSection=!!(isBareCarouselSection||isBareRootWrap);
        const sn=document.createElement("section");sn.className="sec";styleApply(sn,s.style||{});
        if(isBareCarouselSection)sn.classList.add("sec--bare-carousel");
        if(isBareRootWrap)sn.classList.add("sec--bare-wrap");
        const inner=document.createElement("div");inner.className="sec-inner";
        inner.style.width="100%";
        inner.style.boxSizing="border-box";
        if(widthMap[contentWidth]){
            inner.style.maxWidth=widthMap[contentWidth];
            inner.style.margin="0 auto";
        }
        if(state.sel&&state.sel.k==="sec"&&state.sel.s===s.id)sn.classList.add("sel");
        sn.onclick=e=>{
            e.stopPropagation();
            if(isBareSection)return;
            state.carouselSel=null;
            state.sel={k:"sec",s:s.id};
            render();
        };
        sn.ondragover=e=>e.preventDefault();
        sn.ondrop=e=>{
            e.preventDefault();
            e.stopPropagation();
            if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
            const t=e.dataTransfer.getData("c");
            if(!t)return;
            state.carouselSel=null;
            if(addComponentAt(t,{k:"sec",s:s.id},dropPlacement(e,sn)))render();
        };
        s.elements=Array.isArray(s.elements)?s.elements:[];
        (s.elements||[]).forEach(it=>inner.appendChild(renderElement(it,{s:s.id,scope:"section"})));
        (s.rows||[]).forEach(r=>{
            var isAutoWrapColumnRow=!!(s.__rootWrap&&s.__rootKind==="column"&&String(r.id||"").indexOf("row_wrap_col_")===0);
            const rn=document.createElement("div");rn.className="row";styleApply(rn,r.style||{});
            if(isAutoWrapColumnRow)rn.classList.add("row--bare-wrap");
            const rowInner=document.createElement("div");rowInner.className="row-inner";rowInner.style.width="100%";rowInner.style.boxSizing="border-box";rowInner.style.display="flex";rowInner.style.flexWrap="wrap";rowInner.style.gap=((r&&r.style&&r.style.gap)||"8px");
            var rowCw=((r.settings&&r.settings.contentWidth)||"full");
            if(widthMap[rowCw]){rowInner.style.maxWidth=widthMap[rowCw];rowInner.style.margin="0 auto";}
            if(!isAutoWrapColumnRow && state.sel&&state.sel.k==="row"&&state.sel.r===r.id)rn.classList.add("sel");
            rn.onclick=e=>{if(isAutoWrapColumnRow)return;e.stopPropagation();state.carouselSel=null;state.sel={k:"row",s:s.id,r:r.id};render();};
            rn.ondragover=e=>e.preventDefault();
            rn.ondrop=e=>{
                e.preventDefault();
                e.stopPropagation();
                if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
                const t=e.dataTransfer.getData("c");
                if(!t)return;
                state.carouselSel=null;
                if(addComponentAt(t,{k:"row",s:s.id,r:r.id},dropPlacement(e,rn)))render();
            };
            (r.columns||[]).forEach(c=>{
                const cn=document.createElement("div");cn.className="col";styleApply(cn,c.style||{});
                const colInner=document.createElement("div");colInner.className="col-inner";colInner.style.width="100%";colInner.style.boxSizing="border-box";
                var colCw=((c.settings&&c.settings.contentWidth)||"full");
                if(widthMap[colCw]){colInner.style.maxWidth=widthMap[colCw];colInner.style.margin="0 auto";}
                if(state.sel&&state.sel.k==="col"&&state.sel.c===c.id)cn.classList.add("sel");
                cn.onclick=e=>{e.stopPropagation();state.carouselSel=null;state.sel={k:"col",s:s.id,r:r.id,c:c.id};render();};
                cn.ondragover=e=>e.preventDefault();
                cn.ondrop=e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
                    const t=e.dataTransfer.getData("c");
                    if(!t)return;
                    state.carouselSel=null;
                    if(addComponentAt(t,{k:"col",s:s.id,r:r.id,c:c.id},dropPlacement(e,cn)))render();
                };
                (c.elements||[]).forEach(it=>colInner.appendChild(renderElement(it,{s:s.id,r:r.id,c:c.id})));
                cn.appendChild(colInner);
                rowInner.appendChild(cn);
            });
            rn.appendChild(rowInner);
            inner.appendChild(rn);
        });
        sn.appendChild(inner);
        canvas.appendChild(sn);
    });
    if(!(state.layout.sections||[]).length)canvas.innerHTML='<p style="font-size:13px;color:#475569;">Drag any component to start.</p>';
    canvas.ondragover=e=>e.preventDefault();
    canvas.ondrop=e=>{e.preventDefault();if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;const t=e.dataTransfer.getData("c");if(t){if(addComponentAt(t,null,"after"))render();}};
}

function refreshAfterSetting(){
    if(state.carouselSel)render();
    else renderCanvas();
}

function bind(id,val,cb,opts){
    const n=document.getElementById(id);if(!n)return;
    n.value=val||"";
    const fire=()=>{if(opts&&opts.undo)saveToHistory();let v=n.value;if(opts&&opts.px)v=pxIfNumber(v);cb(v);refreshAfterSetting();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function fontSelectHtml(id){
    return '<label>Font family</label><select id="'+id+'">'+
        fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+
    '</select>';
}

function fileNameFromUrl(url){
    var u=String(url||"").trim();
    if(!u)return "";
    try{
        var clean=u.split("?")[0].split("#")[0];
        var parts=clean.split("/");
        return decodeURIComponent(parts[parts.length-1]||"");
    }catch(_e){
        return u;
    }
}

function bindRichEditor(id,val,cb){
    const n=document.getElementById(id);if(!n)return;
    n.innerHTML=val||"";
    const sync=()=>{saveToHistory();cb(n.innerHTML||"");refreshAfterSetting();};
    n.addEventListener("input",sync);
    n.addEventListener("keydown",e=>{
        if(!(e.ctrlKey||e.metaKey))return;
        const k=(e.key||"").toLowerCase();
        if(k==="b"){e.preventDefault();document.execCommand("bold");sync();}
        if(k==="i"){e.preventDefault();document.execCommand("italic");sync();}
        if(k==="u"){e.preventDefault();document.execCommand("underline");sync();}
    });
}

function bindPx(id,val,cb,opts){
    const n=document.getElementById(id);if(!n)return;
    n.value=pxToNumber(val);
    const fire=()=>{if(opts&&opts.undo)saveToHistory();const raw=(n.value||"").trim();cb(raw===""?"":raw+"px");refreshAfterSetting();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function uploadImage(file,done,label,onFail){
    const fd=new FormData();fd.append("image",file);
    const msg=label||"Upload";
    fetch(uploadUrl,{method:"POST",headers:{"X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:fd})
        .then(r=>r.json().then(p=>({ok:r.ok,body:p})).catch(()=>({ok:false,body:null})))
        .then(({ok,body})=>{
            if(ok&&body&&body.url){done(body.url);return;}
            const err=body&&(body.message||(body.errors&&body.errors.image&&(Array.isArray(body.errors.image)?body.errors.image[0]:body.errors.image)));
            const reason=(err&&String(err).trim())?""+err:"Please check file type and size (max 100 MB).";
            if(typeof onFail==="function")onFail(reason);
            alert(msg+" failed: "+reason);
        })
        .catch(()=>{
            if(typeof onFail==="function")onFail("Check your connection and try again.");
            alert(msg+" failed. Check your connection and try again.");
        });
}

function renderSettings(){
    settingsTitle.textContent="Settings Panel";
    const inCarousel=!!state.carouselSel;
    const selKind=inCarousel?(state.carouselSel&&state.carouselSel.k):(state.sel&&state.sel.k);
    const t=selectedTarget();
    if((!state.sel&&!inCarousel)||!t){settings.innerHTML='<p class="meta">Select a component to edit.</p>';return;}
    settingsTitle.textContent=titleCase(selectedType())+" Settings";
    const sty=()=>{t.style=t.style||{};return t.style;};
    const remove='<div class="settings-delete-wrap"><button type="button" id="btnDeleteSelected" class="fb-btn danger"><i class="fas fa-trash-alt"></i> Delete</button></div>';
    function mountBackgroundImageDisplayControl(){
        var s=t&&t.style;
        if(!s||!s.backgroundImage||String(s.backgroundImage).trim()==="")return;
        var panel=document.createElement("div");
        panel.className="menu-split";
        var wrap=document.createElement("div");
        wrap.innerHTML='<label>Display image</label><select id="bgImgDisplayMode"><option value="default">Default</option><option value="full-center-fixed">Full Center (Fixed)</option><option value="repeat">Repeat</option><option value="fill-100">Fill 100% Width</option></select>';
        var delWrap=settings.querySelector(".settings-delete-wrap");
        if(delWrap)settings.insertBefore(wrap,delWrap);else settings.appendChild(wrap);
        if(delWrap)settings.insertBefore(panel,wrap);else settings.appendChild(panel);
        var sel=document.getElementById("bgImgDisplayMode");
        if(!sel)return;
        var cs=sty(),rep=(cs.backgroundRepeat||"").toLowerCase(),sz=(cs.backgroundSize||"").toLowerCase(),pos=(cs.backgroundPosition||"").toLowerCase(),att=(cs.backgroundAttachment||"").toLowerCase();
        if(att==="fixed"&&sz==="cover"&&rep==="no-repeat"&&pos.indexOf("center")>=0)sel.value="full-center-fixed";
        else if(rep==="repeat")sel.value="repeat";
        else if(sz.indexOf("100%")===0)sel.value="fill-100";
        else sel.value="default";
        sel.onchange=()=>{
            saveToHistory();
            var st=sty(),m=sel.value;
            if(m==="full-center-fixed"){
                st.backgroundSize="cover";
                st.backgroundPosition="center center";
                st.backgroundRepeat="no-repeat";
                st.backgroundAttachment="fixed";
            }else if(m==="repeat"){
                st.backgroundSize="auto";
                st.backgroundPosition="top left";
                st.backgroundRepeat="repeat";
                st.backgroundAttachment="scroll";
            }else if(m==="fill-100"){
                st.backgroundSize="100% auto";
                st.backgroundPosition="top center";
                st.backgroundRepeat="no-repeat";
                st.backgroundAttachment="scroll";
            }else{
                st.backgroundSize="";
                st.backgroundPosition="";
                st.backgroundRepeat="";
                st.backgroundAttachment="";
            }
            renderCanvas();
        };
    }
    function readBgImageUrl(){
        var bg=(t&&t.style&&t.style.backgroundImage)||"";
        var m=String(bg).match(/^url\((['"]?)(.*?)\1\)$/i);
        return m?m[2]:"";
    }
    if(selKind==="sec"){
        t.settings=t.settings||{};
        var padDef=[20,20,20,20],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var cw=(t.settings&&t.settings.contentWidth)||"full";
        settings.innerHTML='<div class="menu-section-title">Layout</div><label>Content width</label><select id="secCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("secCw",cw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        var bgUp=document.getElementById("bgUp");
        if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
    } else if(selKind==="el"&&t.type==="image"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0];
        var mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var radiusLinked=t.settings.imageRadiusLinked!==false;
        var imageSourceType=(t.settings&&t.settings.imageSourceType)||"direct";
        var imageSourceFields=imageSourceType==="upload"
            ? '<label>Upload file</label><input id="up" type="file" accept="image/*"><div class="meta" id="imgCurrentFile"></div>'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Image type</label><select id="imgSourceType"><option value="direct"'+(imageSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(imageSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+imageSourceFields+'<label>Alt</label><input id="alt"><div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b"><label>Border radius</label><div class="img-radius-panel"><button type="button" id="imgRadiusLink" class="img-radius-link'+(radiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="imgRadTl" type="number" value="'+rad[0]+'"><input id="imgRadTr" type="number" value="'+rad[1]+'"><input id="imgRadBr" type="number" value="'+rad[2]+'"><input id="imgRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh">'+remove;
        var imgSourceType=document.getElementById("imgSourceType");
        if(imgSourceType)imgSourceType.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.imageSourceType=imgSourceType.value;renderSettings();};
        if(imageSourceType==="direct"){
            bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=String(v||"").trim();},{undo:true});
        } else {
            var curImg=document.getElementById("imgCurrentFile");
            if(curImg){
                var imgName=fileNameFromUrl((t.settings&&t.settings.src)||"");
                curImg.textContent=imgName?("Current file: "+imgName):"Current file: none";
            }
            const up=document.getElementById("up");
            if(up)up.onchange=()=>{if(up.files&&up.files[0]){saveToHistory();uploadImage(up.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;render();},"Image upload");}};
        }
        bind("alt",(t.settings&&t.settings.alt)||"",v=>{t.settings=t.settings||{};t.settings.alt=v;},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        var marginLinked=false;
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkMar=document.getElementById("linkMar");
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        var imgRadTl=document.getElementById("imgRadTl"),imgRadTr=document.getElementById("imgRadTr"),imgRadBr=document.getElementById("imgRadBr"),imgRadBl=document.getElementById("imgRadBl"),imgRadiusLink=document.getElementById("imgRadiusLink");
        function applyImgRadius(vals){sty().borderRadius=spacingToCss(vals);renderCanvas();}
        function readRadius(){return [Number((imgRadTl&&imgRadTl.value)||0)||0,Number((imgRadTr&&imgRadTr.value)||0)||0,Number((imgRadBr&&imgRadBr.value)||0)||0,Number((imgRadBl&&imgRadBl.value)||0)||0];}
        function syncImgRadius(from){
            saveToHistory();
            if(radiusLinked){
                var v=Number((from&&from.value)||0)||0;
                if(imgRadTl)imgRadTl.value=v;
                if(imgRadTr)imgRadTr.value=v;
                if(imgRadBr)imgRadBr.value=v;
                if(imgRadBl)imgRadBl.value=v;
                applyImgRadius([v,v,v,v]);
            }else{
                applyImgRadius(readRadius());
            }
        }
        [imgRadTl,imgRadTr,imgRadBr,imgRadBl].forEach(n=>{if(n)n.addEventListener("input",()=>syncImgRadius(n));});
        if(imgRadiusLink)imgRadiusLink.onclick=()=>{saveToHistory();radiusLinked=!radiusLinked;t.settings=t.settings||{};t.settings.imageRadiusLinked=radiusLinked;imgRadiusLink.classList.toggle("linked",radiusLinked);if(radiusLinked){var v=Number((imgRadTl&&imgRadTl.value)||0)||0;if(imgRadTr)imgRadTr.value=v;if(imgRadBr)imgRadBr.value=v;if(imgRadBl)imgRadBl.value=v;applyImgRadius([v,v,v,v]);}};
        bind("b",(t.style&&t.style.border)||"",v=>sty().border=v,{undo:true});bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v,{undo:true});
    } else if(selKind==="el"&&t.type==="video"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0],mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var videoRadiusLinked=t.settings.videoRadiusLinked!==false;
        var videoSourceType=(t.settings&&t.settings.videoSourceType)||"direct";
        var videoSourceFields=videoSourceType==="upload"
            ? '<label>Upload file</label><input id="upv" type="file" accept="video/*"><div class="meta" id="vidCurrentFile"></div>'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Video type</label><select id="vidSourceType"><option value="direct"'+(videoSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(videoSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+videoSourceFields+'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b"><label>Border radius</label><div class="img-radius-panel"><button type="button" id="vidRadiusLink" class="img-radius-link'+(videoRadiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="vidRadTl" type="number" value="'+rad[0]+'"><input id="vidRadTr" type="number" value="'+rad[1]+'"><input id="vidRadBr" type="number" value="'+rad[2]+'"><input id="vidRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh"><div class="menu-split"></div><div class="menu-section-title">Behavior</div><label>Auto play</label><select id="vAutoplay"><option value="off">Off</option><option value="on">On</option></select><label>Controls</label><select id="vControls"><option value="on">On</option><option value="off">Off</option></select>'+remove;
        var vidSourceType=document.getElementById("vidSourceType");
        if(vidSourceType)vidSourceType.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.videoSourceType=vidSourceType.value;renderSettings();};
        if(videoSourceType==="direct"){
            bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};var next=String(v||"").trim();t.settings.src=next;if(next==="")t.content="";},{undo:true});
        } else {
            var curVid=document.getElementById("vidCurrentFile");
            if(curVid){
                var vidName=fileNameFromUrl((t.settings&&t.settings.src)||"");
                curVid.textContent=vidName?("Current file: "+vidName):"Current file: none";
            }
            const upv=document.getElementById("upv");
            if(upv)upv.onchange=()=>{if(upv.files&&upv.files[0]){saveToHistory();uploadImage(upv.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;render();},"Video upload");}};
        }
        bind("vAutoplay",(t.settings&&t.settings.autoplay)?"on":"off",v=>{t.settings=t.settings||{};t.settings.autoplay=(v==="on");},{undo:true});
        bind("vControls",(t.settings&&typeof t.settings.controls==="boolean")?(t.settings.controls?"on":"off"):"on",v=>{t.settings=t.settings||{};t.settings.controls=(v!=="off");},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        var marginLinked=false;
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkMar=document.getElementById("linkMar");
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        var vidRadTl=document.getElementById("vidRadTl"),vidRadTr=document.getElementById("vidRadTr"),vidRadBr=document.getElementById("vidRadBr"),vidRadBl=document.getElementById("vidRadBl"),vidRadiusLink=document.getElementById("vidRadiusLink");
        function applyVidRadius(vals){sty().borderRadius=spacingToCss(vals);renderCanvas();}
        function readVidRadius(){return [Number((vidRadTl&&vidRadTl.value)||0)||0,Number((vidRadTr&&vidRadTr.value)||0)||0,Number((vidRadBr&&vidRadBr.value)||0)||0,Number((vidRadBl&&vidRadBl.value)||0)||0];}
        function syncVidRadius(from){
            saveToHistory();
            if(videoRadiusLinked){
                var v=Number((from&&from.value)||0)||0;
                if(vidRadTl)vidRadTl.value=v;
                if(vidRadTr)vidRadTr.value=v;
                if(vidRadBr)vidRadBr.value=v;
                if(vidRadBl)vidRadBl.value=v;
                applyVidRadius([v,v,v,v]);
            }else{
                applyVidRadius(readVidRadius());
            }
        }
        [vidRadTl,vidRadTr,vidRadBr,vidRadBl].forEach(n=>{if(n)n.addEventListener("input",()=>syncVidRadius(n));});
        if(vidRadiusLink)vidRadiusLink.onclick=()=>{saveToHistory();videoRadiusLinked=!videoRadiusLinked;t.settings=t.settings||{};t.settings.videoRadiusLinked=videoRadiusLinked;vidRadiusLink.classList.toggle("linked",videoRadiusLinked);if(videoRadiusLinked){var v=Number((vidRadTl&&vidRadTl.value)||0)||0;if(vidRadTr)vidRadTr.value=v;if(vidRadBr)vidRadBr.value=v;if(vidRadBl)vidRadBl.value=v;applyVidRadius([v,v,v,v]);}};
        bind("b",(t.style&&t.style.border)||"",v=>sty().border=v,{undo:true});bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v,{undo:true});
    } else if(selKind==="row"){
        var padDef=[0,0,0,0],marDef=[0,0,0,0],radDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        t.settings=t.settings||{};
        var borderStyle=(t.settings.rowBorderStyle)||((((t.style&&t.style.border)||"").match(/(solid|dashed|dotted|double)/)||[])[1])||"none";
        var rowCw=(t.settings&&t.settings.contentWidth)||"full";
        if(["none","solid","dashed","dotted","double"].indexOf(borderStyle)===-1)borderStyle="none";
        var perCorner=!!t.settings.rowRadiusPerCorner;
        var radiusBlock=perCorner
            ? '<div class="row-radius-grid"><div class="row-radius-field"><span>TL</span><input id="rowRadTl" type="number" value="'+rad[0]+'"></div><div class="row-radius-field"><span>TR</span><input id="rowRadTr" type="number" value="'+rad[1]+'"></div><div class="row-radius-field"><span>BL</span><input id="rowRadBl" type="number" value="'+rad[3]+'"></div><div class="row-radius-field"><span>BR</span><input id="rowRadBr" type="number" value="'+rad[2]+'"></div></div>'
            : '<div class="row-radius-field"><span>R</span><input id="rowRadAll" type="number" value="'+rad[0]+'"></div>';
        settings.innerHTML='<div class="menu-section-title">Layout</div><label>Content width</label><select id="rowCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><div class="row-border-card"><div class="row-border-head"><strong>Border</strong></div><select id="rowBorderStyle"><option value="none">None</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option><option value="double">Double</option></select><label>Corner radius</label>'+radiusBlock+'<div class="size-link"><button type="button" id="rowRadiusToggle" title="Toggle radius mode"><i class="fas fa-expand"></i></button><span>'+(perCorner?'Per corner':'Single value')+'</span></div></div><div class="menu-split"></div><div class="menu-section-title">Behavior</div><button type="button" id="rowBorderReset" class="fb-btn" style="width:100%;"><i class="fas fa-rotate-right"></i> Reset row border</button>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("rowCw",rowCw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        var bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        bindPx("g",(t.style&&t.style.gap)||"",v=>sty().gap=v,{undo:true});
        var bs=document.getElementById("rowBorderStyle");
        function applyBorderStyle(v){t.settings=t.settings||{};t.settings.rowBorderStyle=v;if(v==="none")sty().border="none";else sty().border="1px "+v+" #cbd5e1";renderCanvas();}
        if(bs){bs.value=borderStyle;bs.onchange=()=>{saveToHistory();applyBorderStyle(bs.value);};}
        function applyRadius(vals){sty().borderRadius=spacingToCss(vals);renderCanvas();}
        var rowRadAll=document.getElementById("rowRadAll"),rowRadTl=document.getElementById("rowRadTl"),rowRadTr=document.getElementById("rowRadTr"),rowRadBr=document.getElementById("rowRadBr"),rowRadBl=document.getElementById("rowRadBl");
        if(rowRadAll)rowRadAll.addEventListener("input",()=>{saveToHistory();var v=Number(rowRadAll.value)||0;applyRadius([v,v,v,v]);});
        function syncCornerRadius(){saveToHistory();var tl=Number((rowRadTl&&rowRadTl.value)||0)||0,tr=Number((rowRadTr&&rowRadTr.value)||0)||0,br=Number((rowRadBr&&rowRadBr.value)||0)||0,bl=Number((rowRadBl&&rowRadBl.value)||0)||0;applyRadius([tl,tr,br,bl]);}
        [rowRadTl,rowRadTr,rowRadBr,rowRadBl].forEach(n=>{if(n)n.addEventListener("input",syncCornerRadius);});
        var rowRadiusToggle=document.getElementById("rowRadiusToggle");
        if(rowRadiusToggle)rowRadiusToggle.onclick=()=>{saveToHistory();t.settings=t.settings||{};t.settings.rowRadiusPerCorner=!t.settings.rowRadiusPerCorner;renderSettings();};
        var rowBorderReset=document.getElementById("rowBorderReset");
        if(rowBorderReset)rowBorderReset.onclick=()=>{saveToHistory();t.settings=t.settings||{};t.settings.rowBorderStyle="none";t.settings.rowRadiusPerCorner=false;sty().border="none";sty().borderRadius="0px";renderSettings();renderCanvas();};
    } else if(selKind==="col"){
        var parentRow=null;
        var colRootCtx=null;
        if(inCarousel && state.carouselSel){
            var csp=selectedCarouselParent();
            if(csp && csp.type==="carousel"){
                csp.settings=csp.settings||{};
                var cslides=ensureCarouselSlides(csp.settings);
                var cslide=cslides.find(s=>s.id===state.carouselSel.slideId);
                if(!cslide){
                    var cactive=Number(csp.settings.activeSlide)||0;
                    cslide=cslides[cactive]||cslides[0]||null;
                }
                if(cslide){
                    parentRow=(cslide.rows||[]).find(rw=>rw.id===state.carouselSel.rowId)||null;
                }
            }
        } else if(state.sel){
            parentRow=row(state.sel.s,state.sel.r);
            colRootCtx=sectionRootContext(state.sel.s);
        }
        var currentCols=((parentRow&&parentRow.columns)||[]).length||1;
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        t.settings=t.settings||{};
        var colCw=(t.settings&&t.settings.contentWidth)||"full";
        var layoutHtml='<div class="col-layout-wrap"><div class="col-layout-title">Column layout</div><div class="col-layout-grid"><button type="button" class="col-layout-btn'+(currentCols===1?' active':'')+'" data-cols="1"><i class="fas fa-square"></i><span>1</span></button><button type="button" class="col-layout-btn'+(currentCols===2?' active':'')+'" data-cols="2"><i class="fas fa-columns"></i><span>2</span></button><button type="button" class="col-layout-btn'+(currentCols===3?' active':'')+'" data-cols="3"><i class="fas fa-table-columns"></i><span>3</span></button><button type="button" class="col-layout-btn'+(currentCols===4?' active':'')+'" data-cols="4"><i class="fas fa-grip"></i><span>4</span></button></div></div>';
        settings.innerHTML='<div class="menu-section-title">Layout</div>'+layoutHtml+'<label>Content width</label><select id="colCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#f8fafc",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("colCw",colCw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        var bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};

        function applyColumnLayout(count){
            // Root-level column is rendered through a synthetic row wrapper.
            // Mutate layout.root directly so 1/2/3/4 persists after re-render/save.
            if(!inCarousel && colRootCtx && colRootCtx.isWrap && colRootCtx.root && String(colRootCtx.root.kind||"").toLowerCase()==="column" && colRootCtx.index>=0){
                saveToHistory();
                var rs=rootItems();
                var baseCol=colRootCtx.root;
                var cols=[baseCol];
                while(cols.length<count){cols.push(createDefaultColumn());}
                if(cols.length>count){
                    var keptRoot=cols.slice(0,count);
                    var removedRoot=cols.slice(count);
                    var targetRoot=keptRoot[keptRoot.length-1]||keptRoot[0];
                    if(targetRoot){
                        targetRoot.elements=targetRoot.elements||[];
                        removedRoot.forEach(rc=>{if(rc&&Array.isArray(rc.elements)&&rc.elements.length)targetRoot.elements=targetRoot.elements.concat(rc.elements);});
                    }
                    cols=keptRoot;
                }
                cols.forEach(c=>{c.style=c.style||{};c.style.flex="1 1 240px";});

                if(count<=1){
                    rs[colRootCtx.index]=Object.assign({kind:"column"},cols[0]);
                    syncSectionsFromRoot();
                    state.sel={k:"col",s:"sec_wrap_col_"+String(cols[0].id),r:"row_wrap_col_"+String(cols[0].id),c:cols[0].id};
                    render();
                    return;
                }

                var newRootRow=createRootItem("row");
                if(!newRootRow)return;
                newRootRow.columns=cols;
                rs[colRootCtx.index]=newRootRow;
                syncSectionsFromRoot();
                state.sel={k:"col",s:"sec_wrap_row_"+String(newRootRow.id),r:newRootRow.id,c:cols[0].id};
                render();
                return;
            }
            if(!parentRow)return;
            saveToHistory();
            parentRow.columns=parentRow.columns||[];
            var cols=parentRow.columns.slice();
            while(cols.length<count){cols.push({id:uid("col"),style:{},elements:[]});}
            if(cols.length>count){
                var kept=cols.slice(0,count);
                var removed=cols.slice(count);
                var target=kept[kept.length-1]||kept[0];
                if(target){
                    target.elements=target.elements||[];
                    removed.forEach(rc=>{if(rc&&Array.isArray(rc.elements)&&rc.elements.length)target.elements=target.elements.concat(rc.elements);});
                }
                cols=kept;
            }
            cols.forEach(c=>{c.style=c.style||{};c.style.flex="1 1 240px";});
            parentRow.columns=cols;
            if(inCarousel && state.carouselSel){
                if(!parentRow.columns.find(c=>c.id===state.carouselSel.colId) && parentRow.columns[0]){
                    state.carouselSel.colId=parentRow.columns[0].id;
                    state.carouselSel.k="col";
                }
            } else if(state.sel){
                if(!parentRow.columns.find(c=>c.id===state.sel.c)&&parentRow.columns[0])state.sel={k:"col",s:state.sel.s,r:state.sel.r,c:parentRow.columns[0].id};
            }
            render();
        }
        settings.querySelectorAll(".col-layout-btn").forEach(btn=>{
            btn.addEventListener("click",()=>{var n=Number(btn.getAttribute("data-cols"))||1;applyColumnLayout(n);});
        });
    } else if(selKind==="el"&&t.type==="carousel"){
        t.settings=t.settings||{};
        if(!t.settings.fixedWidth || Number(t.settings.fixedWidth)<50)t.settings.fixedWidth=500;
        if(!t.settings.fixedHeight || Number(t.settings.fixedHeight)<50)t.settings.fixedHeight=500;
        ensureCarouselSlides(t.settings);
        function renderCarouselEditor(){
            ensureCarouselSlides(t.settings);
            var slides=t.settings.slides||[];
            var active=Number(t.settings.activeSlide);if(isNaN(active)||active<0||active>=slides.length)active=0;
            t.settings.activeSlide=active;
            var slidesHtml=slides.map((s,idx)=>{
                var lab=String((s&&s.label)||("Slide #"+(idx+1))).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
                var sid=String((s&&s.id)||"");
                return '<div class="carousel-slide-row"><button type="button" class="carousel-slide-btn'+(idx===active?' active':'')+'" data-idx="'+idx+'" data-sid="'+sid+'">'+(idx+1)+' '+lab+'</button><button type="button" class="carousel-icon-btn slide-vis'+(idx===active?' active':'')+'" data-idx="'+idx+'" data-sid="'+sid+'" title="View slide"><i class="fas fa-eye"></i></button><button type="button" class="carousel-icon-btn danger slide-del" data-idx="'+idx+'" data-sid="'+sid+'" title="Delete"><i class="fas fa-trash"></i></button></div>';
            }).join("");
            var aSlide=slides[active]||slides[0]||defaultCarouselSlide("Slide #1");
            var aRows=Array.isArray(aSlide.rows)?aSlide.rows:[];
            var activeRow=Number(t.settings.carouselActiveRow);if(isNaN(activeRow)||activeRow<0||activeRow>=aRows.length)activeRow=0;
            var rowObj=aRows[activeRow]||aRows[0]||{columns:[{elements:[]}]};
            var aCols=Array.isArray(rowObj.columns)?rowObj.columns:[];
            var activeCol=Number(t.settings.carouselActiveCol);if(isNaN(activeCol)||activeCol<0||activeCol>=aCols.length)activeCol=0;
            t.settings.carouselActiveRow=activeRow;
            t.settings.carouselActiveCol=activeCol;
            var carouselComponentsHtml='';
            settings.innerHTML='<div class="menu-section-title">Content</div><div class="menu-section-title">Slides</div>'+slidesHtml
                +'<label>Slide label</label><input id="carSlideLabel" value="'+String((aSlide&&aSlide.label)||"").replace(/"/g,'&quot;')+'" placeholder="Slide title">'
                +'<div class="meta" style="margin:6px 0 10px;">Use the <strong>+</strong> button inside carousel to add an image slide.</div>'
                +'<button type="button" id="addImageSlideFromSettings" class="fb-btn primary" style="width:100%;margin:0 0 10px;">Add image slide</button>'
                +'<input id="carImageSlideFiles" type="file" accept="image/*" multiple style="display:none;">'
                +carouselComponentsHtml
                +'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Carousel alignment</label><div class="menu-align-row"><button type="button" class="menu-align-btn car-align-btn" data-ca="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn car-align-btn" data-ca="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn car-align-btn" data-ca="right"><i class="fas fa-align-right"></i></button></div>'
                +'<label>Fixed width</label><div class="px-wrap"><input id="carFixedW" type="number" min="50" step="1"><span class="px-unit">px</span></div><label>Fixed height</label><div class="px-wrap"><input id="carFixedH" type="number" min="50" step="1"><span class="px-unit">px</span></div>'
                +'<div class="menu-split"></div><div class="menu-section-title">Behavior</div><div class="meta">Slide selection, view, and ordering controls are in the Content section above.</div>'
                +remove;

            settings.querySelectorAll(".carousel-slide-btn").forEach(btn=>btn.addEventListener("click",()=>{
                var sid=String(btn.getAttribute("data-sid")||"");
                var liveSlides=t.settings.slides||[];
                var i=sid?liveSlides.findIndex(sl=>String((sl&&sl.id)||"")===sid):Number(btn.getAttribute("data-idx"));
                if(isNaN(i)||i<0||i>=liveSlides.length)return;
                saveToHistory();
                t.settings.activeSlide=i;
                renderCarouselEditor();
                renderCanvas();
            }));
            settings.querySelectorAll(".slide-vis").forEach(btn=>btn.addEventListener("click",()=>{
                var sid=String(btn.getAttribute("data-sid")||"");
                var liveSlides=t.settings.slides||[];
                var i=sid?liveSlides.findIndex(sl=>String((sl&&sl.id)||"")===sid):Number(btn.getAttribute("data-idx"));
                if(isNaN(i)||i<0||i>=liveSlides.length)return;
                saveToHistory();
                t.settings.activeSlide=i;
                renderCarouselEditor();
                renderCanvas();
            }));
            settings.querySelectorAll(".slide-del").forEach(btn=>btn.addEventListener("click",(e)=>{
                e.preventDefault();
                e.stopPropagation();
                ensureCarouselSlides(t.settings);
                var liveSlides=t.settings.slides||[];
                if(liveSlides.length<=1)return;
                var sid=String(btn.getAttribute("data-sid")||"");
                var i=sid?liveSlides.findIndex(sl=>String((sl&&sl.id)||"")===sid):Number(btn.getAttribute("data-idx"));
                if(isNaN(i)||i<0||i>=liveSlides.length)return;
                saveToHistory();
                var next=liveSlides.filter((_,idx)=>idx!==i);
                t.settings.slides=next;
                if((t.settings.activeSlide||0)>=next.length)t.settings.activeSlide=Math.max(0,next.length-1);
                t.settings.carouselActiveRow=0;
                t.settings.carouselActiveCol=0;
                ensureCarouselSlides(t.settings);
                renderCarouselEditor();
                renderCanvas();
            }));
            var labelInput=document.getElementById("carSlideLabel");
            if(labelInput)labelInput.addEventListener("input",()=>{var i=Number(t.settings.activeSlide)||0;slides[i].label=labelInput.value||"";renderCanvas();});
            var addImageSlideFromSettings=document.getElementById("addImageSlideFromSettings");
            var carImageSlideFiles=document.getElementById("carImageSlideFiles");
            if(addImageSlideFromSettings&&carImageSlideFiles)addImageSlideFromSettings.onclick=()=>{carImageSlideFiles.click();};
            if(carImageSlideFiles)carImageSlideFiles.onchange=()=>{
                var files=Array.from(carImageSlideFiles.files||[]);
                if(!files.length)return;
                saveToHistory();
                var parentRef=(state.carouselSel&&state.carouselSel.parent)?state.carouselSel.parent:{scope:(state.sel&&state.sel.scope)||"column",s:state.sel&&state.sel.s,r:state.sel&&state.sel.r,c:state.sel&&state.sel.c,e:t.id};
                var idx=0;
                var addNext=()=>{
                    if(idx>=files.length){
                        renderCarouselEditor();
                        renderCanvas();
                        return;
                    }
                    var file=files[idx++];
                    uploadImage(file,url=>{
                        var isDefaultEmpty=(slides.length===1 && (!slides[0] || !slides[0].image || String(slides[0].image.src||"").trim()===""));
                        var sld=isDefaultEmpty?slides[0]:{id:uid("sld"),label:"Slide #"+(slides.length+1),image:{src:"",alt:"Image"}};
                        if(!isDefaultEmpty)slides.push(sld);
                        sld.image={src:String(url||"").trim(),alt:"Image"};
                        t.settings.activeSlide=slides.length-1;
                        t.settings.carouselActiveRow=0;
                        t.settings.carouselActiveCol=0;
                        state.carouselSel=null;
                        addNext();
                    },"Image upload",()=>{addNext();});
                };
                addNext();
                carImageSlideFiles.value="";
            };

            settings.querySelectorAll(".carousel-comp-btn").forEach(btn=>{
                btn.addEventListener("dragstart",e=>{if(e&&e.dataTransfer)e.dataTransfer.setData("c",btn.getAttribute("data-add")||"");});
                btn.addEventListener("click",()=>{
                    var tp=btn.getAttribute("data-add")||"";
                    saveToHistory();
                    var sIdx=Number(t.settings.activeSlide)||0;
                    var sl=slides[sIdx];
                    if(!sl)return;
                    var rows=Array.isArray(sl.rows)?sl.rows:[];
                    sl.rows=rows;
                    if(tp==="section"||tp==="row"){
                        rows.push({id:uid("row"),style:{gap:"8px"},columns:[]});
                        t.settings.carouselActiveRow=rows.length-1;
                        t.settings.carouselActiveCol=0;
                        renderCarouselEditor();renderCanvas();
                        return;
                    }
                    if(!rows.length){
                        rows.push({id:uid("row"),style:{gap:"8px"},columns:[]});
                    }
                    var rw=rows[rows.length-1]||rows[0];
                    rw.columns=Array.isArray(rw.columns)?rw.columns:[];
                    if(tp==="column"){
                        rw.columns.push({id:uid("col"),style:{},elements:[]});
                        t.settings.carouselActiveCol=Math.max(0,rw.columns.length-1);
                        renderCarouselEditor();renderCanvas();
                        return;
                    }
                    if(!rw.columns.length){
                        rw.columns.push({id:uid("col"),style:{},elements:[]});
                    }
                    var it=carouselElementDefaults(tp);
                    if(!it)return;
                    var lastCol=rw.columns[rw.columns.length-1]||rw.columns[0];
                    if(!lastCol)return;
                    lastCol.elements=Array.isArray(lastCol.elements)?lastCol.elements:[];
                    lastCol.elements.push(it);
                    renderCarouselEditor();renderCanvas();
                });
            });

            var carAlignment=(t.settings&&t.settings.alignment)||"left";
            settings.querySelectorAll(".car-align-btn").forEach(btn=>{if(btn.getAttribute("data-ca")===carAlignment)btn.classList.add("active");btn.addEventListener("click",()=>{saveToHistory();t.settings=t.settings||{};t.settings.alignment=btn.getAttribute("data-ca")||"left";renderCarouselEditor();renderCanvas();});});
            bind("carFixedW",(t.settings&&t.settings.fixedWidth)||"",v=>{t.settings=t.settings||{};var n=Number(v);t.settings.fixedWidth=(!isNaN(n)&&n>=50)?Math.round(n):"";renderCanvas();},{undo:true});
            bind("carFixedH",(t.settings&&t.settings.fixedHeight)||"",v=>{t.settings=t.settings||{};var n=Number(v);t.settings.fixedHeight=(!isNaN(n)&&n>=50)?Math.round(n):"";renderCanvas();},{undo:true});
        }
        renderCarouselEditor();
    } else if(selKind==="el"&&t.type==="menu"){
        t.settings=t.settings||{};
        if(!Array.isArray(t.settings.items)||!t.settings.items.length)t.settings.items=[{label:"Home",url:"#",newWindow:false,hasSubmenu:false},{label:"Contact",url:"/contact",newWindow:false,hasSubmenu:false}];
        if(!t.settings.menuCollapsed||typeof t.settings.menuCollapsed!=="object")t.settings.menuCollapsed={};
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);

        function renderMenuEditor(){
            var items=t.settings.items||[];
            var cards=items.map((it,idx)=>{
                var collapsed=!!t.settings.menuCollapsed[idx];
                return '<div class="menu-item-card"><div class="menu-item-head"><strong>Menu item '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button><button type="button" class="menu-toggle" data-idx="'+idx+'" title="Toggle"><i class="fas '+(collapsed?'fa-chevron-down':'fa-chevron-up')+'"></i></button></div></div>'+(collapsed?'':'<input id="miLabel_'+idx+'" value="'+String((it&&it.label)||"").replace(/"/g,'&quot;')+'" placeholder="Label"><input id="miUrl_'+idx+'" value="'+String((it&&it.url)||"").replace(/"/g,'&quot;')+'" placeholder="Link"><label><input id="miNew_'+idx+'" type="checkbox"'+((it&&it.newWindow)?' checked':'')+'> Open in a new window</label><label><input id="miSub_'+idx+'" type="checkbox"'+((it&&it.hasSubmenu)?' checked':'')+'> Has submenu</label>')+'</div>';
            }).join("");
            settings.innerHTML='<div class="menu-panel-title">Menu</div><div class="menu-section-title">Content</div>'+cards+'<button type="button" id="addMenuItem" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add menu item</button><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Font family</label><select id="mFont"><option value="">Same font as the page</option>'+fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+'</select><div class="menu-typo-grid"><div class="px-wrap"><input id="mFs" type="number" step="1"><span class="px-unit">px</span></div><div class="px-wrap"><input id="mLh" type="number" step="0.1"><span class="px-unit">lh</span></div></div><div class="menu-split"></div><div class="menu-section-title">Layout</div><div class="menu-align-row"><button type="button" class="menu-align-btn" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn" data-align="right"><i class="fas fa-align-right"></i></button></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Letter spacing</label><div class="menu-slider-row"><input id="mLsRange" type="range" min="0" max="20" step="0.1"><input id="mLsNum" type="number" min="0" max="20" step="0.1"></div><label>Menu items text color</label><input id="mTextColor" type="color"><label>Active menu item color</label><input id="mActiveColor" type="color"><label>Menu items underline color</label><input id="mUnderlineColor" type="color"><label>Background color</label><input id="mBgColor" type="color"><label>Background image URL</label><input id="mBgImg" placeholder="https://..."><label>Upload background image</label><input id="mBgUp" type="file" accept="image/*"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><label>Spacing between menu items</label><div class="menu-slider-row"><input id="mGapRange" type="range" min="0" max="64" step="1"><input id="mGapNum" type="number" min="0" max="64" step="1"></div><label>Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label>Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div>'+remove;

            items.forEach((it,idx)=>{
                var lab=document.getElementById("miLabel_"+idx),url=document.getElementById("miUrl_"+idx),nw=document.getElementById("miNew_"+idx),sm=document.getElementById("miSub_"+idx);
                if(lab)lab.addEventListener("input",()=>{it.label=lab.value||"";renderCanvas();});
                if(url)url.addEventListener("input",()=>{it.url=url.value||"";renderCanvas();});
                if(nw)nw.addEventListener("change",()=>{it.newWindow=!!nw.checked;renderCanvas();});
                if(sm)sm.addEventListener("change",()=>{it.hasSubmenu=!!sm.checked;renderCanvas();});
            });
            settings.querySelectorAll(".menu-del").forEach(btn=>btn.addEventListener("click",()=>{var i=Number(btn.getAttribute("data-idx"));if(items.length<=1)return;saveToHistory();items.splice(i,1);delete t.settings.menuCollapsed[i];renderMenuEditor();renderCanvas();}));
            settings.querySelectorAll(".menu-toggle").forEach(btn=>btn.addEventListener("click",()=>{var i=Number(btn.getAttribute("data-idx"));t.settings.menuCollapsed[i]=!t.settings.menuCollapsed[i];renderMenuEditor();}));
            var addBtn=document.getElementById("addMenuItem");if(addBtn)addBtn.onclick=()=>{saveToHistory();items.push({label:"Menu item "+(items.length+1),url:"#",newWindow:false,hasSubmenu:false});renderMenuEditor();renderCanvas();};

            var mFont=document.getElementById("mFont");if(mFont){mFont.value=(t.style&&t.style.fontFamily)||"";mFont.addEventListener("change",()=>{saveToHistory();sty().fontFamily=mFont.value||"";renderCanvas();});}
            bindPx("mFs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v,{undo:true});
            bind("mLh",(t.style&&t.style.lineHeight)||"",v=>sty().lineHeight=v,{undo:true});
            var curAlign=(t.settings&&t.settings.menuAlign)||"left";
            settings.querySelectorAll(".menu-align-btn").forEach(btn=>{if(btn.getAttribute("data-align")===curAlign)btn.classList.add("active");btn.addEventListener("click",()=>{saveToHistory();t.settings=t.settings||{};t.settings.menuAlign=btn.getAttribute("data-align");renderMenuEditor();renderCanvas();});});

            var lsVal=Number(pxToNumber((t.style&&t.style.letterSpacing)||""));if(isNaN(lsVal))lsVal=0;
            var lsRange=document.getElementById("mLsRange"),lsNum=document.getElementById("mLsNum");
            function syncLs(v,skipR,skipN){var n=Number(v);if(isNaN(n))n=0;if(n<0)n=0;if(n>20)n=20;if(lsRange&&!skipR)lsRange.value=String(n);if(lsNum&&!skipN)lsNum.value=String(n);sty().letterSpacing=n+"px";renderCanvas();}
            if(lsRange)lsRange.oninput=()=>{saveToHistory();syncLs(lsRange.value,true,false);};
            if(lsNum){lsNum.oninput=()=>{saveToHistory();syncLs(lsNum.value,false,true);};lsNum.onchange=()=>{saveToHistory();syncLs(lsNum.value,false,true);};}
            syncLs(lsVal,false,false);

            bind("mTextColor",(t.settings&&t.settings.textColor)||"#374151",v=>{t.settings=t.settings||{};t.settings.textColor=v;renderCanvas();},{undo:true});
            bind("mActiveColor",(t.settings&&t.settings.activeColor)||"#a89c76",v=>{t.settings=t.settings||{};t.settings.activeColor=v;renderCanvas();},{undo:true});
            bind("mUnderlineColor",(t.settings&&t.settings.underlineColor)||"#000000",v=>{t.settings=t.settings||{};t.settings.underlineColor=v;renderCanvas();},{undo:true});
            bind("mBgColor",(t.style&&t.style.backgroundColor)||"#ffffff",v=>{sty().backgroundColor=v;renderCanvas();},{undo:true});
            bind("mBgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
            var mBgUp=document.getElementById("mBgUp");
            if(mBgUp)mBgUp.onchange=()=>{if(mBgUp.files&&mBgUp.files[0]){saveToHistory();var mBgImg=document.getElementById("mBgImg");uploadImage(mBgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(mBgImg)mBgImg.value=url;renderCanvas();},"Menu background image upload");}};

            var gapVal=Number((t.settings&&t.settings.itemGap)||13);if(isNaN(gapVal))gapVal=13;
            var gRange=document.getElementById("mGapRange"),gNum=document.getElementById("mGapNum");
            function syncGap(v,skipR,skipN){var n=Number(v);if(isNaN(n))n=0;if(n<0)n=0;if(n>64)n=64;if(gRange&&!skipR)gRange.value=String(n);if(gNum&&!skipN)gNum.value=String(n);t.settings=t.settings||{};t.settings.itemGap=n;renderCanvas();}
            if(gRange)gRange.oninput=()=>{saveToHistory();syncGap(gRange.value,true,false);};
            if(gNum){gNum.oninput=()=>{saveToHistory();syncGap(gNum.value,false,true);};gNum.onchange=()=>{saveToHistory();syncGap(gNum.value,false,true);};}
            syncGap(gapVal,false,false);

            var paddingLinked=false,marginLinked=false;
            function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
            function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
            ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
            ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
            var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
            if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
            if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        }
        renderMenuEditor();
    } else if(selKind==="el"&&t.type==="form"){
        t.settings=t.settings||{};
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Submit button text</label><input id="formSubmitText" placeholder="Submit"><div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="formAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Form width</label><input id="formWidth" placeholder="100%"><div class="meta" style="margin-top:8px;">Set width in % (example: 50%) and place using alignment only.</div>'+remove;
        bind("formSubmitText",t.content||"Submit",v=>{t.content=v||"Submit";},{undo:true});
        bind("formAlign",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v||"left";sty().textAlign=v||"left";},{undo:true});
        bind("formWidth",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{var w=v||"100%";sty().width=w;sty().height="";sty().maxWidth="";sty().minHeight="";t.settings=t.settings||{};t.settings.width=w;t.settings.formWidth=w;t.settings.height="";t.settings.maxWidth="";t.settings.minHeight="";},{undo:true});
    } else if(selKind==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var textTypographyControls=(t.type==="text"||t.type==="heading")
            ? '<label>Line height</label><input id="lh" placeholder="1.5"><label>Letter spacing</label><div class="px-wrap"><input id="ls" type="number" step="0.1"><span class="px-unit">px</span></div>'
            : '';
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>↔</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>↔</span></button><span>Link</span></div></div></div>';
        settings.innerHTML='<div class="menu-section-title">Content</div>'+(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div>'+sizeBlock+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+textTypographyControls+fontSelectHtml('ff')+remove;
        if(rich){
            bindRichEditor("contentRt",t.content,v=>t.content=v);
            const rt=document.getElementById("contentRt");
            const b=document.getElementById("rtBold"),i=document.getElementById("rtItalic"),u=document.getElementById("rtUnderline");
            if(b)b.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("bold");t.content=rt.innerHTML||"";renderCanvas();};
            if(i)i.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("italic");t.content=rt.innerHTML||"";renderCanvas();};
            if(u)u.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("underline");t.content=rt.innerHTML||"";renderCanvas();};
        } else {
            bind("content",t.content,v=>t.content=v,{undo:true});
        }
        bind("co",(t.style&&t.style.color)||"#334155",v=>sty().color=v,{undo:true});bindPx("fs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v,{undo:true});bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v,{undo:true});
        if(t.type==="button"){
            bind("a",(t.settings&&t.settings.alignment)||(t.style&&t.style.textAlign)||"center",v=>{t.settings=t.settings||{};t.settings.alignment=v||"center";sty().textAlign=v||"center";},{undo:true});
        } else {
            bind("a",(t.style&&t.style.textAlign)||"",v=>sty().textAlign=v,{undo:true});
        }
        if(t.type==="text"||t.type==="heading"){
            bind("lh",(t.style&&t.style.lineHeight)||"",v=>sty().lineHeight=v,{undo:true});
            bindPx("ls",(t.style&&t.style.letterSpacing)||"",v=>sty().letterSpacing=v,{undo:true});
        }
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
    } else {
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>↔</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>↔</span></button><span>Link</span></div></div></div><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        bindPx("g",(t.style&&t.style.gap)||"",v=>sty().gap=v,{undo:true});
    }
    mountBackgroundImageDisplayControl();
    const btnDel=document.getElementById("btnDeleteSelected");if(btnDel)btnDel.onclick=()=>removeSelected();
}

function render(){renderCanvas();renderSettings();if(state.sel||state.carouselSel)showLeftPanel("settings");}

document.querySelectorAll(".fb-lib button").forEach(b=>{
    b.ondragstart=e=>e.dataTransfer.setData("c",b.dataset.c||"");
    b.onclick=()=>{addComponent(b.dataset.c||"");render();};
});

stepSel.onchange=()=>loadStep(stepSel.value);
var fbGrid=document.getElementById("fbGrid"),fbComponentsHide=document.getElementById("fbComponentsHide"),fbComponentsShow=document.getElementById("fbComponentsShow");
var fbTabComponents=document.getElementById("fbTabComponents"),fbTabSettings=document.getElementById("fbTabSettings"),fbLeftPanelComponents=document.getElementById("fbLeftPanelComponents"),fbLeftPanelSettings=document.getElementById("fbLeftPanelSettings");
function showLeftPanel(panel){
    if(panel==="settings"){
        if(fbLeftPanelSettings)fbLeftPanelSettings.classList.remove("hidden");
        if(fbLeftPanelComponents)fbLeftPanelComponents.classList.add("hidden");
        if(fbTabComponents)fbTabComponents.classList.remove("active");if(fbTabSettings)fbTabSettings.classList.add("active");
    }else{
        if(fbLeftPanelSettings)fbLeftPanelSettings.classList.add("hidden");
        if(fbLeftPanelComponents)fbLeftPanelComponents.classList.remove("hidden");
        if(fbTabComponents)fbTabComponents.classList.add("active");if(fbTabSettings)fbTabSettings.classList.remove("active");
    }
}
if(fbTabComponents)fbTabComponents.onclick=()=>showLeftPanel("components");
if(fbTabSettings)fbTabSettings.onclick=()=>showLeftPanel("settings");
if(fbComponentsHide)fbComponentsHide.onclick=()=>{if(fbGrid)fbGrid.classList.add("components-hidden");};
if(fbComponentsShow)fbComponentsShow.onclick=()=>{if(fbGrid)fbGrid.classList.remove("components-hidden");};
function persistCurrentStep(){
    const s=cur();if(!s)return;
    if(document.activeElement&&typeof document.activeElement.blur==="function")document.activeElement.blur();
    var t=selectedTarget();
    if(t&&(t.type==="video"||t.type==="image")){
        var wIn=document.getElementById("w");
        if(wIn){var v=(wIn.value||"").trim();if(v){var w=pxIfNumber(v);t.style=t.style||{};t.style.width=w;t.settings=t.settings||{};t.settings.width=w;}}
    } else if(t&&t.type==="form"){
        var fw=document.getElementById("formWidth");
        if(fw){var fv=(fw.value||"").trim();var fwv=pxIfNumber(fv||"100%");t.style=t.style||{};t.style.width=fwv;t.settings=t.settings||{};t.settings.width=fwv;t.settings.formWidth=fwv;}
        t.style=t.style||{};t.settings=t.settings||{};
        t.style.height="";t.style.maxWidth="";t.style.minHeight="";
        t.settings.height="";t.settings.maxWidth="";t.settings.minHeight="";
        var fa=document.getElementById("formAlign");
        if(fa){var aval=(fa.value||"left");t.settings=t.settings||{};t.settings.alignment=aval;t.style=t.style||{};t.style.textAlign=aval;}
    }
    saveMsg.textContent="Saving...";
    return fetch(saveUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:JSON.stringify({step_id:s.id,layout_json:state.layout})})
        .then(r=>{if(!r.ok)throw 1;return r.json();})
        .then(p=>{s.layout_json=p.layout_json||clone(state.layout);saveMsg.textContent="Saved "+new Date().toLocaleTimeString();});
}
document.getElementById("saveBtn").onclick=()=>{
    persistCurrentStep().catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
document.getElementById("previewBtn").onclick=()=>{
    const s=cur();if(!s)return;
    persistCurrentStep()
        .then(()=>{window.open(previewTpl.replace("__STEP__",String(s.id)),"_blank");})
        .catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
document.addEventListener("keydown",e=>{
    const key=String(e.key||"").toLowerCase();
    const ae=document.activeElement;
    const isTextField=!!(ae && (ae.tagName==="INPUT" || ae.tagName==="TEXTAREA" || ae.isContentEditable));

    if((e.ctrlKey||e.metaKey)&&key==="s"){e.preventDefault();document.getElementById("saveBtn").click();return;}

    if((e.ctrlKey||e.metaKey)&&(key==="b"||key==="i"||key==="u")){
        if(ae && ae.isContentEditable){
            e.preventDefault();
            if(key==="b")document.execCommand("bold");
            if(key==="i")document.execCommand("italic");
            if(key==="u")document.execCommand("underline");
            return;
        }
    }

    if(key==="delete" && (state.sel||state.carouselSel) && !isTextField){
        e.preventDefault();
        if(e.shiftKey && clearSelectedMediaContent())return;
        removeSelected();
    }

    if(key==="z"&&(e.ctrlKey||e.metaKey)&&!e.shiftKey&&!isTextField){
        e.preventDefault();
        undo();
    }
});

loadStep(state.sid);
})();
</script>
@endsection
