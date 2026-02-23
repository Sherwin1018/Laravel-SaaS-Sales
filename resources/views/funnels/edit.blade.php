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
.fb-card{background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:10px}
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
#canvas{min-height:60vh;border:2px dashed #93c5fd;border-radius:12px;padding:10px;background:linear-gradient(180deg,#f8fafc,#e0f2fe);overflow:auto}
.sec{border:1px dashed #64748b;border-radius:10px;padding:8px;margin-bottom:9px;background:#fff}
.row{display:flex;flex-wrap:wrap;gap:8px;border:1px dashed #cbd5e1;border-radius:8px;padding:6px}
.col{flex:1 1 240px;min-height:58px;min-width:0;border:1px dashed #bfdbfe;border-radius:7px;padding:6px;background:#f8fafc}
.el{border:1px solid #e2e8f0;border-radius:7px;padding:7px;background:#fff;margin-bottom:6px;min-width:0;overflow-wrap:break-word;word-break:break-word}
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
.carousel-align-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin-bottom:10px}
.carousel-align-btn{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:8px 6px;color:#64748b;cursor:pointer}
.carousel-align-btn.active{border-color:#3b82f6;background:#dbeafe;color:#1d4ed8}
.carousel-comp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-bottom:10px}
.carousel-comp-btn{border:1px solid #cbd5e1;background:#f8fafc;border-radius:8px;padding:8px 10px;color:#334155;font-weight:700;cursor:pointer}
.carousel-comp-btn:hover{background:#eef2ff}
.carousel-comp-btn[draggable="true"]{cursor:grab}
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
                <button draggable="true" data-c="section"><i class="fas fa-square"></i>Section</button>
                <button draggable="true" data-c="row"><i class="fas fa-grip-lines"></i>Row</button>
                <button draggable="true" data-c="column"><i class="fas fa-columns"></i>Column</button>
                <button draggable="true" data-c="heading"><i class="fas fa-heading"></i>Heading</button>
                <button draggable="true" data-c="text"><i class="fas fa-font"></i>Text</button>
                <button draggable="true" data-c="menu"><i class="fas fa-bars"></i>Menu</button>
                <button draggable="true" data-c="carousel"><i class="fas fa-images"></i>Carousel</button>
                <button draggable="true" data-c="image"><i class="fas fa-image"></i>Image</button>
                <button draggable="true" data-c="button"><i class="fas fa-square-plus"></i>Button</button>
                <button draggable="true" data-c="form"><i class="fas fa-file-lines"></i>Form</button>
                <button draggable="true" data-c="video"><i class="fas fa-video"></i>Video</button>
                <button draggable="true" data-c="spacer"><i class="fas fa-arrows-up-down"></i>Spacer</button>
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
const state={sid:{{ (int)($defaultStepId??0) }}||((steps[0]&&steps[0].id)||null),layout:null,sel:null};
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

steps.forEach(s=>{const o=document.createElement("option");o.value=s.id;o.textContent=s.title+" ("+s.type+")";stepSel.appendChild(o);});
stepSel.value=state.sid;

const uid=p=>p+"_"+Math.random().toString(36).slice(2,10),clone=o=>JSON.parse(JSON.stringify(o));
const defaults=()=>({sections:[{id:uid("sec"),style:{padding:"20px",backgroundColor:"#ffffff"},settings:{contentWidth:"full"},rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[{id:uid("el"),type:"heading",content:"Welcome to Our Offer",style:{fontSize:"32px",color:"#0f172a"},settings:{}}]}]}]}]});
const cur=()=>steps.find(s=>+s.id===+state.sid);
const sec=id=>(state.layout.sections||[]).find(x=>x.id===id);
const row=(s,r)=>(sec(s)?.rows||[]).find(x=>x.id===r);
const col=(s,r,c)=>(row(s,r)?.columns||[]).find(x=>x.id===c);
const el=(s,r,c,e)=>(col(s,r,c)?.elements||[]).find(x=>x.id===e);

const undoHistory=[];const maxUndo=40;
function saveToHistory(){if(!state.layout)return;undoHistory.push(clone(state.layout));if(undoHistory.length>maxUndo)undoHistory.shift();}
function undo(){if(!undoHistory.length)return;state.layout=undoHistory.pop();render();}

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
    state.layout=(s&&s.layout_json&&Array.isArray(s.layout_json.sections)&&s.layout_json.sections.length)?clone(s.layout_json):defaults();
    normalizeElementStyle(state.layout);
    state.sel=null;
    undoHistory.length=0;
    saveMsg.textContent="Loaded "+s.title;
    render();
}

function selectedTarget(){const x=state.sel;if(!x)return null;if(x.k==="sec")return sec(x.s);if(x.k==="row")return row(x.s,x.r);if(x.k==="col")return col(x.s,x.r,x.c);if(x.k==="el")return el(x.s,x.r,x.c,x.e);return null;}
function selectedType(){const x=state.sel,t=selectedTarget();if(!x||!t)return "None";if(x.k==="el")return (t.type||"element");if(x.k==="sec")return "section";if(x.k==="row")return "row";if(x.k==="col")return "column";return "None";}
function titleCase(v){return (v||"").replace(/[_-]/g," ").replace(/\b\w/g,m=>m.toUpperCase());}
function defaultCarouselSlide(label){return {id:uid("sld"),label:label||"Slide #1",rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}]};}
function ensureCarouselSlides(settings){
    settings=settings||{};
    if(!Array.isArray(settings.slides)||!settings.slides.length)settings.slides=[defaultCarouselSlide("Slide #1")];
    settings.slides=settings.slides.map((sl,idx)=>{
        var s=(sl&&typeof sl==="object")?sl:{};
        s.id=s.id||uid("sld");
        if(!Array.isArray(s.rows)||!s.rows.length)s.rows=[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}];
        s.rows=s.rows.map(r=>{
            var rowObj=(r&&typeof r==="object")?r:{};
            rowObj.id=rowObj.id||uid("row");
            rowObj.style=(rowObj.style&&typeof rowObj.style==="object")?rowObj.style:{gap:"8px"};
            if(!Array.isArray(rowObj.columns)||!rowObj.columns.length)rowObj.columns=[{id:uid("col"),style:{},elements:[]}];
            rowObj.columns=rowObj.columns.map(c=>{
                var colObj=(c&&typeof c==="object")?c:{};
                colObj.id=colObj.id||uid("col");
                colObj.style=(colObj.style&&typeof colObj.style==="object")?colObj.style:{};
                colObj.elements=Array.isArray(colObj.elements)?colObj.elements:[];
                return colObj;
            });
            return rowObj;
        });
        s.label=String(s.label||("Slide #"+(idx+1)));
        return s;
    });
    return settings.slides;
}
function carouselElementDefaults(type){
    const d={
        heading:{content:"Heading",style:{fontSize:"32px"},settings:{}},
        text:{content:"Text",style:{fontSize:"16px"},settings:{}},
        image:{content:"",style:{width:"100%"},settings:{src:"",alt:"Image",alignment:"left"}},
        video:{content:"",style:{},settings:{src:"",alignment:"left"}},
        button:{content:"Click Me",style:{backgroundColor:"#2563eb",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center"},settings:{link:"#"}}
    }[type];
    if(!d)return null;
    return {id:uid("el"),type:type,content:d.content,style:clone(d.style),settings:clone(d.settings)};
}
function carouselAllowsDropType(type){
    return ["heading","text","image","video","button","row","column"].indexOf(type)>=0;
}
function normalizeCarouselDropType(type){
    var t=String(type||"").toLowerCase();
    if(t==="col")return "column";
    if(t==="section"||t==="menu"||t==="carousel"||t==="form"||t==="spacer"||t==="countdown")return "";
    return t;
}
function renderCarouselPreviewItem(item,onDelete){
    var type=(item&&item.type)||"text";
    var wrap=document.createElement("div");
    wrap.className="builder-carousel-item";
    wrap.style.position="relative";
    wrap.addEventListener("click",e=>e.stopPropagation());
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
        del.onclick=(e)=>{e.preventDefault();e.stopPropagation();onDelete();};
        wrap.appendChild(del);
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
        var src=(item&&item.settings&&item.settings.src)||"";
        item.settings=item.settings||{};
        wrap.innerHTML='<input type="text" placeholder="Image URL" value="'+String(src).replace(/"/g,'&quot;')+'" style="width:100%;padding:6px 8px;border:1px solid rgba(255,255,255,0.35);border-radius:6px;background:rgba(255,255,255,0.1);color:#fff;"><div class="car-img-prev" style="margin-top:6px;">'+(src?'<img src="'+src+'" alt="" style="max-width:100%;height:auto;display:block;border-radius:6px;">':'<div style="padding:10px;border:1px dashed rgba(255,255,255,0.35);border-radius:6px;">Image</div>')+'</div>';
        var inp=wrap.querySelector("input"),prev=wrap.querySelector(".car-img-prev");
        if(inp){
            inp.addEventListener("click",e=>e.stopPropagation());
            inp.addEventListener("input",()=>{item.settings.src=inp.value||"";if(prev)prev.innerHTML=item.settings.src?'<img src="'+item.settings.src+'" alt="" style="max-width:100%;height:auto;display:block;border-radius:6px;">':'<div style="padding:10px;border:1px dashed rgba(255,255,255,0.35);border-radius:6px;">Image</div>';});
        }
    }else if(type==="video"){
        item.settings=item.settings||{};
        var vsrc=(item.settings&&item.settings.src)||"";
        wrap.innerHTML='<input type="text" placeholder="Video URL" value="'+String(vsrc).replace(/"/g,'&quot;')+'" style="width:100%;padding:6px 8px;border:1px solid rgba(255,255,255,0.35);border-radius:6px;background:rgba(255,255,255,0.1);color:#fff;"><div style="margin-top:6px;padding:12px;border-radius:6px;background:rgba(15,23,42,0.45);text-align:center;">Video</div>';
        var vinp=wrap.querySelector("input");
        if(vinp){
            vinp.addEventListener("click",e=>e.stopPropagation());
            vinp.addEventListener("input",()=>{item.settings.src=vinp.value||"";});
        }
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
        item.settings=item.settings||{};
        var link=document.createElement("input");
        link.type="text";
        link.placeholder="Button link";
        link.value=(item.settings&&item.settings.link)||"";
        link.style.width="100%";
        link.style.padding="6px 8px";
        link.style.marginTop="6px";
        link.style.border="1px solid rgba(255,255,255,0.35)";
        link.style.borderRadius="6px";
        link.style.background="rgba(255,255,255,0.1)";
        link.style.color="#fff";
        link.addEventListener("click",e=>e.stopPropagation());
        link.oninput=()=>{item.settings.link=link.value||"";};
        wrap.appendChild(link);
    }else{
        var t=document.createElement("div");
        t.textContent=type;
        wrap.appendChild(t);
    }
    return wrap;
}

function addComponent(type){
    saveToHistory();
    const p=state.sel||{},s=sec(p.s)||state.layout.sections[0],r=row(p.s,p.r)||(s?.rows||[])[0],c=col(p.s,p.r,p.c)||(r?.columns||[])[0];
    if(type==="section"){state.layout.sections.push({id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff"},settings:{contentWidth:"full"},rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}]});return;}
    if(type==="row"){if(s)(s.rows=s.rows||[]).push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});return;}
    if(type==="column"){if(r)(r.columns=r.columns||[]).push({id:uid("col"),style:{},elements:[]});return;}
    if(!c)return;
    const d={heading:{content:"Heading",style:{fontSize:"32px"},settings:{}},text:{content:"Text",style:{fontSize:"16px"},settings:{}},menu:{content:"",style:{fontSize:"16px"},settings:{items:[{label:"Home",url:"#",newWindow:false,hasSubmenu:false},{label:"Contact",url:"/contact",newWindow:false,hasSubmenu:false}],itemGap:13,activeIndex:0,menuAlign:"left",underlineColor:""}},carousel:{content:"",style:{padding:"10px 10px 10px 10px"},settings:{slides:[defaultCarouselSlide("Slide #1")],activeSlide:0,vAlign:"center",showArrows:true,controlsColor:"#64748b",arrowColor:"#ffffff"}},image:{content:"",style:{width:"100%"},settings:{src:"",alt:"Image",alignment:"left"}},button:{content:"Click Me",style:{backgroundColor:"#2563eb",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center"},settings:{link:"#"}},form:{content:"Submit",style:{},settings:{}},video:{content:"",style:{},settings:{src:"",alignment:"left"}},spacer:{content:"",style:{height:"24px"},settings:{}}}[type]||{content:"Text",style:{},settings:{}};
    c.elements.push({id:uid("el"),type:type,content:d.content,style:clone(d.style),settings:clone(d.settings)});
}

function removeSelected(){
    const x=state.sel;if(!x)return;
    saveToHistory();
    if(x.k==="el"){const c=col(x.s,x.r,x.c);if(!c)return;c.elements=(c.elements||[]).filter(i=>i.id!==x.e);}
    else if(x.k==="col"){const r=row(x.s,x.r);if(!r)return;r.columns=(r.columns||[]).filter(i=>i.id!==x.c);}
    else if(x.k==="row"){const s=sec(x.s);if(!s)return;s.rows=(s.rows||[]).filter(i=>i.id!==x.r);}
    else if(x.k==="sec"){state.layout.sections=(state.layout.sections||[]).filter(i=>i.id!==x.s);}
    state.sel=null;render();
}

function renderElement(item,ctx){
    const w=document.createElement("div");w.className="el";
    if(item.type!=="button")styleApply(w,item.style||{});
    else if(item.style&&item.style.margin)w.style.margin=item.style.margin;
    if(state.sel&&state.sel.k==="el"&&state.sel.e===item.id)w.classList.add("sel");
    w.onclick=e=>{e.stopPropagation();state.sel={k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};render();};
    if(item.type==="heading"||item.type==="text"){const n=document.createElement(item.type==="heading"?"h2":"p");n.contentEditable="true";n.style.margin="0";n.innerHTML=item.content||"";styleApply(n,item.style||{});n.oninput=()=>{item.content=n.innerHTML||"";};onRichTextKeys(n,()=>{item.content=n.innerHTML||"";});w.appendChild(n);}
    else if(item.type==="button"){
        var wb=(item.settings&&item.settings.widthBehavior)||"fluid",al=(item.settings&&item.settings.alignment)||"center";
        w.classList.add(wb==="fill"?"el--button-fill":"el--button");
        w.style.display="flex";w.style.justifyContent=al==="right"?"flex-end":al==="center"?"center":"flex-start";
        const b=document.createElement("button");b.type="button";b.contentEditable="true";b.innerHTML=item.content||"Button";
        styleApply(b,item.style||{});b.style.border="none";b.style.display=wb==="fill"?"flex":"inline-flex";b.style.width=wb==="fill"?"100%":"auto";b.style.alignItems="center";b.style.justifyContent="center";if(!(item.style&&item.style.backgroundColor))b.style.backgroundColor="#2563eb";if(!(item.style&&item.style.color))b.style.color="#fff";if(!(item.style&&item.style.padding))b.style.padding="10px 18px";if(!(item.style&&item.style.borderRadius))b.style.borderRadius="999px";
        b.oninput=()=>{item.content=b.innerHTML||"";};onRichTextKeys(b,()=>{item.content=b.innerHTML||"";});w.appendChild(b);
    }
    else if(item.type==="image"){w.innerHTML=(item.settings&&item.settings.src)?'<img src="'+item.settings.src+'" alt="'+(item.settings.alt||"Image")+'" style="max-width:100%;height:auto;display:block;">':'<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;">Image placeholder</div>';}
    else if(item.type==="form"){w.innerHTML='<input disabled placeholder="Name"><input disabled placeholder="Email"><button class="fb-btn primary" disabled>'+(item.content||"Submit")+'</button>';}
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
        function addDropToCarousel(type,rowIndex,colIndex){
            type=normalizeCarouselDropType(type);
            if(!carouselAllowsDropType(type))return false;
            var sIdx=Number(cs.activeSlide);if(isNaN(sIdx)||sIdx<0||sIdx>=slides.length)sIdx=0;
            var sl=slides[sIdx];if(!sl)return false;
            sl.rows=Array.isArray(sl.rows)&&sl.rows.length?sl.rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}];
            if(type==="row"){
                sl.rows.push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});
                cs.carouselActiveRow=sl.rows.length-1;
                cs.carouselActiveCol=0;
                return true;
            }
            var rIdx=Number(rowIndex);if(isNaN(rIdx)||rIdx<0||rIdx>=sl.rows.length)rIdx=0;
            var rw=sl.rows[rIdx];rw.columns=Array.isArray(rw.columns)&&rw.columns.length?rw.columns:[{id:uid("col"),style:{},elements:[]}];
            if(type==="column"){
                if(rw.columns.length>=4)return false;
                rw.columns.push({id:uid("col"),style:{},elements:[]});
                cs.carouselActiveRow=rIdx;
                cs.carouselActiveCol=rw.columns.length-1;
                return true;
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
        wrap.style.position="relative";
        wrap.style.minHeight="180px";
        wrap.style.borderRadius="8px";
        wrap.style.background="linear-gradient(135deg,#0ea5e9,#0284c7)";
        wrap.style.color="#fff";
        wrap.style.overflow="hidden";
        wrap.style.display="flex";
        wrap.style.alignItems=(cs.vAlign==="top"?"flex-start":(cs.vAlign==="bottom"?"flex-end":"center"));
        wrap.style.justifyContent="center";
        wrap.style.padding="16px";
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
        body.style.background=(cs.bodyBgColor||"transparent");
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
            rowBox.style.display="flex";
            rowBox.style.flexWrap="wrap";
            rowBox.style.gap=((rw&&rw.style&&rw.style.gap)||"8px");
            rowBox.style.borderRadius="8px";
            rowBox.style.padding="6px";
            rowBox.style.position="relative";
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
            delRow.onclick=(e)=>{e.preventDefault();e.stopPropagation();if((curSlide.rows||[]).length<=1)return;saveToHistory();curSlide.rows.splice(ri,1);cs.carouselActiveRow=0;cs.carouselActiveCol=0;renderCanvas();renderSettings();};
            rowBox.appendChild(delRow);
            (rw.columns||[]).forEach((cl,ci)=>{
                var colBox=document.createElement("div");
                colBox.style.flex=(cl&&cl.style&&cl.style.flex)||"1 1 220px";
                colBox.style.minWidth="180px";
                colBox.style.display="flex";
                colBox.style.flexDirection="column";
                colBox.style.gap="8px";
                colBox.style.minHeight="60px";
                colBox.style.background="rgba(255,255,255,0.04)";
                colBox.style.borderRadius="8px";
                colBox.style.padding="8px";
                colBox.style.position="relative";
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
                delCol.onclick=(e)=>{e.preventDefault();e.stopPropagation();if((rw.columns||[]).length<=1)return;saveToHistory();rw.columns.splice(ci,1);cs.carouselActiveCol=0;renderCanvas();renderSettings();};
                colBox.appendChild(delCol);
                if(!Array.isArray(cl.elements)||!cl.elements.length){
                    var ph=document.createElement("div");
                    ph.textContent="Drop component here";
                    ph.style.fontSize="12px";
                    ph.style.opacity="0.78";
                    ph.style.color="rgba(255,255,255,0.9)";
                    ph.style.padding="10px 8px";
                    ph.style.borderRadius="6px";
                    ph.style.background="rgba(255,255,255,0.06)";
                    ph.style.border="1px solid rgba(255,255,255,0.15)";
                    ph.style.textAlign="center";
                    ph.style.pointerEvents="none";
                    colBox.appendChild(ph);
                }
                (cl.elements||[]).forEach((it,ei)=>colBox.appendChild(renderCarouselPreviewItem(it,()=>{
                    saveToHistory();
                    var list=Array.isArray(cl.elements)?cl.elements:[];
                    if(ei>=0&&ei<list.length)list.splice(ei,1);
                    cl.elements=list;
                    renderCanvas();
                    renderSettings();
                })));
                rowBox.appendChild(colBox);
            });
            body.appendChild(rowBox);
        });
        wrap.appendChild(body);
        w.innerHTML="";w.appendChild(wrap);
    }
    else if(item.type==="video"){
        const raw=(item.settings&&item.settings.src)||(item.content&&String(item.content).trim())||"";
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
    canvas.innerHTML="";
    var widthMap={full:"",wide:"1200px",medium:"992px",small:"768px",xsmall:"576px"};
    (state.layout.sections||[]).forEach(s=>{
        var contentWidth=((s.settings&&s.settings.contentWidth)||"full");
        const sn=document.createElement("section");sn.className="sec";styleApply(sn,s.style||{});
        const inner=document.createElement("div");inner.className="sec-inner";
        inner.style.width="100%";
        inner.style.boxSizing="border-box";
        if(widthMap[contentWidth]){
            inner.style.maxWidth=widthMap[contentWidth];
            inner.style.margin="0 auto";
        }
        if(state.sel&&state.sel.k==="sec"&&state.sel.s===s.id)sn.classList.add("sel");
        sn.onclick=e=>{e.stopPropagation();state.sel={k:"sec",s:s.id};render();};
        (s.rows||[]).forEach(r=>{
            const rn=document.createElement("div");rn.className="row";styleApply(rn,r.style||{});
            const rowInner=document.createElement("div");rowInner.className="row-inner";rowInner.style.width="100%";rowInner.style.boxSizing="border-box";
            var rowCw=((r.settings&&r.settings.contentWidth)||"full");
            if(widthMap[rowCw]){rowInner.style.maxWidth=widthMap[rowCw];rowInner.style.margin="0 auto";}
            if(state.sel&&state.sel.k==="row"&&state.sel.r===r.id)rn.classList.add("sel");
            rn.onclick=e=>{e.stopPropagation();state.sel={k:"row",s:s.id,r:r.id};render();};
            (r.columns||[]).forEach(c=>{
                const cn=document.createElement("div");cn.className="col";styleApply(cn,c.style||{});
                const colInner=document.createElement("div");colInner.className="col-inner";colInner.style.width="100%";colInner.style.boxSizing="border-box";
                var colCw=((c.settings&&c.settings.contentWidth)||"full");
                if(widthMap[colCw]){colInner.style.maxWidth=widthMap[colCw];colInner.style.margin="0 auto";}
                if(state.sel&&state.sel.k==="col"&&state.sel.c===c.id)cn.classList.add("sel");
                cn.onclick=e=>{e.stopPropagation();state.sel={k:"col",s:s.id,r:r.id,c:c.id};render();};
                cn.ondragover=e=>e.preventDefault();
                cn.ondrop=e=>{e.preventDefault();const t=e.dataTransfer.getData("c");if(!t)return;state.sel={k:"col",s:s.id,r:r.id,c:c.id};addComponent(t);render();};
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
    if(!(state.layout.sections||[]).length)canvas.innerHTML='<p style="font-size:13px;color:#475569;">Drag a Section to start.</p>';
    canvas.ondragover=e=>e.preventDefault();
    canvas.ondrop=e=>{e.preventDefault();if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;const t=e.dataTransfer.getData("c");if(t){addComponent(t);render();}};
}

function bind(id,val,cb,opts){
    const n=document.getElementById(id);if(!n)return;
    n.value=val||"";
    const fire=()=>{if(opts&&opts.undo)saveToHistory();let v=n.value;if(opts&&opts.px)v=pxIfNumber(v);cb(v);renderCanvas();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function fontSelectHtml(id){
    return '<label>Font family</label><select id="'+id+'">'+
        fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+
    '</select>';
}

function bindRichEditor(id,val,cb){
    const n=document.getElementById(id);if(!n)return;
    n.innerHTML=val||"";
    const sync=()=>{saveToHistory();cb(n.innerHTML||"");renderCanvas();};
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
    const fire=()=>{if(opts&&opts.undo)saveToHistory();const raw=(n.value||"").trim();cb(raw===""?"":raw+"px");renderCanvas();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function uploadImage(file,done,label){
    const fd=new FormData();fd.append("image",file);
    const msg=label||"Upload";
    fetch(uploadUrl,{method:"POST",headers:{"X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:fd})
        .then(r=>r.json().then(p=>({ok:r.ok,body:p})).catch(()=>({ok:false,body:null})))
        .then(({ok,body})=>{
            if(ok&&body&&body.url){done(body.url);return;}
            const err=body&&(body.message||(body.errors&&body.errors.image&&(Array.isArray(body.errors.image)?body.errors.image[0]:body.errors.image)));
            const reason=(err&&String(err).trim())?""+err:"Please check file type and size (max 100 MB).";
            alert(msg+" failed: "+reason);
        })
        .catch(()=>alert(msg+" failed. Check your connection and try again."));
}

function renderSettings(){
    settingsTitle.textContent="Settings Panel";
    const t=selectedTarget();
    if(!state.sel||!t){settings.innerHTML='<p class="meta">Select a component to edit.</p>';return;}
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
    if(state.sel.k==="sec"){
        t.settings=t.settings||{};
        var padDef=[20,20,20,20],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var cw=(t.settings&&t.settings.contentWidth)||"full";
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><label>Content width</label><select id="secCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div>'+remove;
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
    } else if(state.sel.k==="el"&&t.type==="image"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0];
        var mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var radiusLinked=t.settings.imageRadiusLinked!==false;
        var imageSourceType=(t.settings&&t.settings.imageSourceType)||"direct";
        var imageSourceFields=imageSourceType==="upload"
            ? '<label>Upload file</label><input id="up" type="file" accept="image/*">'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<label>Image type</label><select id="imgSourceType"><option value="direct"'+(imageSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(imageSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+imageSourceFields+'<label>Alt</label><input id="alt"><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><label>Border</label><input id="b"><label>Border radius</label><div class="img-radius-panel"><button type="button" id="imgRadiusLink" class="img-radius-link'+(radiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="imgRadTl" type="number" value="'+rad[0]+'"><input id="imgRadTr" type="number" value="'+rad[1]+'"><input id="imgRadBr" type="number" value="'+rad[2]+'"><input id="imgRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh">'+remove;
        var imgSourceType=document.getElementById("imgSourceType");
        if(imgSourceType)imgSourceType.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.imageSourceType=imgSourceType.value;renderSettings();};
        if(imageSourceType==="direct"){
            bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;},{undo:true});
        } else {
            const up=document.getElementById("up");
            if(up)up.onchange=()=>{if(up.files&&up.files[0]){saveToHistory();uploadImage(up.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;renderCanvas();},"Image upload");}};
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
    } else if(state.sel.k==="el"&&t.type==="video"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0],mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var videoRadiusLinked=t.settings.videoRadiusLinked!==false;
        var videoSourceType=(t.settings&&t.settings.videoSourceType)||"direct";
        var videoSourceFields=videoSourceType==="upload"
            ? '<label>Upload file</label><input id="upv" type="file" accept="video/*">'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<label>Video type</label><select id="vidSourceType"><option value="direct"'+(videoSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(videoSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+videoSourceFields+'<label>Auto play</label><select id="vAutoplay"><option value="off">Off</option><option value="on">On</option></select><label>Controls</label><select id="vControls"><option value="on">On</option><option value="off">Off</option></select><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><label>Border</label><input id="b"><label>Border radius</label><div class="img-radius-panel"><button type="button" id="vidRadiusLink" class="img-radius-link'+(videoRadiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="vidRadTl" type="number" value="'+rad[0]+'"><input id="vidRadTr" type="number" value="'+rad[1]+'"><input id="vidRadBr" type="number" value="'+rad[2]+'"><input id="vidRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh">'+remove;
        var vidSourceType=document.getElementById("vidSourceType");
        if(vidSourceType)vidSourceType.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.videoSourceType=vidSourceType.value;renderSettings();};
        if(videoSourceType==="direct"){
            bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;},{undo:true});
        } else {
            const upv=document.getElementById("upv");
            if(upv)upv.onchange=()=>{if(upv.files&&upv.files[0]){saveToHistory();uploadImage(upv.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;renderCanvas();},"Video upload");}};
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
    } else if(state.sel.k==="row"){
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
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><label>Content width</label><select id="rowCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div><div class="row-border-card"><div class="row-border-head"><strong>Border</strong><button type="button" id="rowBorderReset"><i class="fas fa-rotate-right"></i></button></div><select id="rowBorderStyle"><option value="none">None</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option><option value="double">Double</option></select><label>Corner radius</label>'+radiusBlock+'<div class="size-link"><button type="button" id="rowRadiusToggle" title="Toggle radius mode"><i class="fas fa-expand"></i></button><span>'+(perCorner?'Per corner':'Single value')+'</span></div></div>'+remove;
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
    } else if(state.sel.k==="col"){
        var parentRow=row(state.sel.s,state.sel.r);
        var currentCols=((parentRow&&parentRow.columns)||[]).length||1;
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        t.settings=t.settings||{};
        var colCw=(t.settings&&t.settings.contentWidth)||"full";
        var layoutHtml='<div class="col-layout-wrap"><div class="col-layout-title">Column layout</div><div class="col-layout-grid"><button type="button" class="col-layout-btn'+(currentCols===1?' active':'')+'" data-cols="1"><i class="fas fa-square"></i><span>1</span></button><button type="button" class="col-layout-btn'+(currentCols===2?' active':'')+'" data-cols="2"><i class="fas fa-columns"></i><span>2</span></button><button type="button" class="col-layout-btn'+(currentCols===3?' active':'')+'" data-cols="3"><i class="fas fa-table-columns"></i><span>3</span></button><button type="button" class="col-layout-btn'+(currentCols===4?' active':'')+'" data-cols="4"><i class="fas fa-grip"></i><span>4</span></button></div></div>';
        settings.innerHTML=layoutHtml+'<label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><label>Content width</label><select id="colCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div>'+remove;
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
            if(!parentRow.columns.find(c=>c.id===state.sel.c)&&parentRow.columns[0])state.sel={k:"col",s:state.sel.s,r:state.sel.r,c:parentRow.columns[0].id};
            render();
        }
        settings.querySelectorAll(".col-layout-btn").forEach(btn=>{
            btn.addEventListener("click",()=>{var n=Number(btn.getAttribute("data-cols"))||1;applyColumnLayout(n);});
        });
    } else if(state.sel.k==="el"&&t.type==="carousel"){
        t.settings=t.settings||{};
        ensureCarouselSlides(t.settings);
        var padDef=[10,10,10,10],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);

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
            var rowOptions=(aRows.length?aRows:[{columns:[]}]).map((_,idx)=>'<option value="'+idx+'"'+(idx===activeRow?' selected':'')+'>Row '+(idx+1)+'</option>').join("");
            var colOptions=(aCols.length?aCols:[{}]).map((_,idx)=>'<option value="'+idx+'"'+(idx===activeCol?' selected':'')+'>Column '+(idx+1)+'</option>').join("");
            settings.innerHTML='<div class="menu-panel-title">Carousel</div>'+slidesHtml+'<input id="carSlideLabel" value="'+String((aSlide&&aSlide.label)||"").replace(/"/g,'&quot;')+'" placeholder="Slide title"><button type="button" id="addCarouselSlide" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add New Slide</button><div class="menu-split"></div><div class="menu-section-title">Slide content</div><button type="button" id="carAddRow" class="fb-btn" style="width:100%;margin:6px 0;">Add row</button><label>Active row</label><select id="carRowSel">'+rowOptions+'</select><label>Columns in active row</label><select id="carColsSel"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option></select><label>Active column</label><select id="carColSel">'+colOptions+'</select><label>Add component</label><div class="carousel-comp-grid"><button type="button" class="carousel-comp-btn" data-add="heading" draggable="true">Heading</button><button type="button" class="carousel-comp-btn" data-add="text" draggable="true">Text</button><button type="button" class="carousel-comp-btn" data-add="image" draggable="true">Image</button><button type="button" class="carousel-comp-btn" data-add="video" draggable="true">Video</button><button type="button" class="carousel-comp-btn" data-add="button" draggable="true">Button</button><button type="button" class="carousel-comp-btn" data-add="row" draggable="true">Row</button><button type="button" class="carousel-comp-btn" data-add="column" draggable="true">Column</button></div><label>Vertical content alignment</label><div class="carousel-align-grid"><button type="button" class="carousel-align-btn" data-v="top"><i class="fas fa-align-left"></i></button><button type="button" class="carousel-align-btn" data-v="center"><i class="fas fa-align-center"></i></button><button type="button" class="carousel-align-btn" data-v="bottom"><i class="fas fa-align-right"></i></button></div><label class="inline-check"><input id="carShowArrows" type="checkbox"> Display arrow navigation</label><label>Controls color</label><input id="carControlsColor" type="color"><label>Arrow color</label><input id="carArrowColor" type="color"><label>Body color</label><input id="carBodyBgColor" type="color"><div class="menu-split"></div><div class="menu-section-title">Size and position</div><label>Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label>Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div>'+remove;

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
            var addSlide=document.getElementById("addCarouselSlide");
            if(addSlide)addSlide.onclick=()=>{saveToHistory();slides.push(defaultCarouselSlide("Slide #"+(slides.length+1)));t.settings.activeSlide=slides.length-1;t.settings.carouselActiveRow=0;t.settings.carouselActiveCol=0;renderCarouselEditor();renderCanvas();};

            var rowSel=document.getElementById("carRowSel"),colSel=document.getElementById("carColSel"),colsSel=document.getElementById("carColsSel"),addRow=document.getElementById("carAddRow");
            if(colsSel){
                var curCols=((aRows[activeRow]&&aRows[activeRow].columns)||[]).length||1;
                colsSel.value=String(curCols);
                colsSel.onchange=()=>{saveToHistory();var targetRow=(aRows[t.settings.carouselActiveRow]||aRows[0]);if(!targetRow)return;targetRow.columns=targetRow.columns||[];var cnt=Math.max(1,Math.min(4,Number(colsSel.value)||1));while(targetRow.columns.length<cnt)targetRow.columns.push({id:uid("col"),style:{},elements:[]});if(targetRow.columns.length>cnt){var kept=targetRow.columns.slice(0,cnt),removed=targetRow.columns.slice(cnt),dst=kept[kept.length-1]||kept[0];if(dst){dst.elements=dst.elements||[];removed.forEach(rc=>{if(rc&&Array.isArray(rc.elements)&&rc.elements.length)dst.elements=dst.elements.concat(rc.elements);});}targetRow.columns=kept;}if((t.settings.carouselActiveCol||0)>=cnt)t.settings.carouselActiveCol=cnt-1;renderCarouselEditor();renderCanvas();};
            }
            if(addRow)addRow.onclick=()=>{saveToHistory();aRows.push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});t.settings.carouselActiveRow=aRows.length-1;t.settings.carouselActiveCol=0;renderCarouselEditor();renderCanvas();};
            if(rowSel)rowSel.onchange=()=>{saveToHistory();t.settings.carouselActiveRow=Number(rowSel.value)||0;t.settings.carouselActiveCol=0;renderCarouselEditor();renderCanvas();};
            if(colSel)colSel.onchange=()=>{saveToHistory();t.settings.carouselActiveCol=Number(colSel.value)||0;renderCarouselEditor();renderCanvas();};
            settings.querySelectorAll(".carousel-comp-btn").forEach(btn=>{
                btn.addEventListener("dragstart",e=>{if(e&&e.dataTransfer)e.dataTransfer.setData("c",btn.getAttribute("data-add")||"");});
                btn.addEventListener("click",()=>{
                    var tp=btn.getAttribute("data-add")||"";
                    saveToHistory();
                    var sIdx=Number(t.settings.activeSlide)||0;
                    var sl=slides[sIdx];
                    if(!sl)return;
                    var rows=Array.isArray(sl.rows)?sl.rows:[];
                    if(!rows.length){rows.push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});sl.rows=rows;}
                    var rIdx=Number(t.settings.carouselActiveRow)||0;if(rIdx<0||rIdx>=rows.length)rIdx=0;
                    var rw=rows[rIdx];
                    rw.columns=Array.isArray(rw.columns)&&rw.columns.length?rw.columns:[{id:uid("col"),style:{},elements:[]}];
                    var cIdx=Number(t.settings.carouselActiveCol)||0;if(cIdx<0||cIdx>=rw.columns.length)cIdx=0;
                    if(tp==="row"){
                        rows.push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});
                        t.settings.carouselActiveRow=rows.length-1;
                        t.settings.carouselActiveCol=0;
                        renderCarouselEditor();renderCanvas();
                        return;
                    }
                    if(tp==="column"){
                        if(rw.columns.length<4)rw.columns.push({id:uid("col"),style:{},elements:[]});
                        t.settings.carouselActiveCol=Math.max(0,rw.columns.length-1);
                        renderCarouselEditor();renderCanvas();
                        return;
                    }
                    var it=carouselElementDefaults(tp);
                    if(!it)return;
                    rw.columns[cIdx].elements=Array.isArray(rw.columns[cIdx].elements)?rw.columns[cIdx].elements:[];
                    rw.columns[cIdx].elements.push(it);
                    renderCarouselEditor();renderCanvas();
                });
            });

            var vAlign=(t.settings&&t.settings.vAlign)||"center";
            settings.querySelectorAll(".carousel-align-btn").forEach(btn=>{if(btn.getAttribute("data-v")===vAlign)btn.classList.add("active");btn.addEventListener("click",()=>{saveToHistory();t.settings.vAlign=btn.getAttribute("data-v");renderCarouselEditor();renderCanvas();});});
            var showArrows=document.getElementById("carShowArrows");if(showArrows){showArrows.checked=t.settings.showArrows!==false;showArrows.addEventListener("change",()=>{saveToHistory();t.settings.showArrows=!!showArrows.checked;renderCanvas();});}
            bind("carControlsColor",(t.settings&&t.settings.controlsColor)||"#64748b",v=>{t.settings=t.settings||{};t.settings.controlsColor=v;renderCanvas();},{undo:true});
            bind("carArrowColor",(t.settings&&t.settings.arrowColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.arrowColor=v;renderCanvas();},{undo:true});
            bind("carBodyBgColor",(t.settings&&t.settings.bodyBgColor)||"#0ea5e9",v=>{t.settings=t.settings||{};t.settings.bodyBgColor=v;renderCanvas();},{undo:true});

            var paddingLinked=false,marginLinked=false;
            function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
            function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
            ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
            ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
            var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
            if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
            if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        }
        renderCarouselEditor();
    } else if(state.sel.k==="el"&&t.type==="menu"){
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
            settings.innerHTML='<div class="menu-panel-title">Menu</div>'+cards+'<button type="button" id="addMenuItem" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add menu item</button><div class="menu-split"></div><div class="menu-section-title">Typography</div><label>Font family</label><select id="mFont"><option value="">Same font as the page</option>'+fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+'</select><div class="menu-typo-grid"><div class="px-wrap"><input id="mFs" type="number" step="1"><span class="px-unit">px</span></div><div class="px-wrap"><input id="mLh" type="number" step="0.1"><span class="px-unit">lh</span></div></div><div class="menu-align-row"><button type="button" class="menu-align-btn" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn" data-align="right"><i class="fas fa-align-right"></i></button></div><label>Letter spacing</label><div class="menu-slider-row"><input id="mLsRange" type="range" min="0" max="20" step="0.1"><input id="mLsNum" type="number" min="0" max="20" step="0.1"></div><div class="menu-split"></div><div class="menu-section-title">Color</div><label>Menu items text color</label><input id="mTextColor" type="color"><label>Active menu item color</label><input id="mActiveColor" type="color"><label>Menu items underline color</label><input id="mUnderlineColor" type="color"><div class="menu-split"></div><div class="menu-section-title">Margin</div><label>Spacing between menu items</label><div class="menu-slider-row"><input id="mGapRange" type="range" min="0" max="64" step="1"><input id="mGapNum" type="number" min="0" max="64" step="1"></div><div class="menu-split"></div><div class="menu-section-title">Size and position</div><label>Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label>Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div>'+remove;

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
    } else if(state.sel.k==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var textTypographyControls=(t.type==="text"||t.type==="heading")
            ? '<label>Line height</label><input id="lh" placeholder="1.5"><label>Letter spacing</label><div class="px-wrap"><input id="ls" type="number" step="0.1"><span class="px-unit">px</span></div>'
            : '';
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>↔</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>↔</span></button><span>Link</span></div></div></div>';
        settings.innerHTML=(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+'<label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+textTypographyControls+fontSelectHtml('ff')+sizeBlock+'<label>Text align</label><select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select>'+remove;
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
        bind("co",(t.style&&t.style.color)||"#334155",v=>sty().color=v,{undo:true});bindPx("fs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v,{undo:true});bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v,{undo:true});bind("a",(t.style&&t.style.textAlign)||"",v=>sty().textAlign=v,{undo:true});
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

function render(){renderCanvas();renderSettings();if(state.sel)showLeftPanel("settings");}

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
document.getElementById("saveBtn").onclick=()=>{
    const s=cur();if(!s)return;
    if(document.activeElement&&typeof document.activeElement.blur==="function")document.activeElement.blur();
    var t=selectedTarget();
    if(state.sel&&state.sel.k==="el"&&t&&(t.type==="video"||t.type==="image")){
        var wIn=document.getElementById("w");
        if(wIn){var v=(wIn.value||"").trim();if(v){var w=pxIfNumber(v);t.style=t.style||{};t.style.width=w;t.settings=t.settings||{};t.settings.width=w;}}
    }
    saveMsg.textContent="Saving...";
    fetch(saveUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:JSON.stringify({step_id:s.id,layout_json:state.layout})}).then(r=>{if(!r.ok)throw 1;return r.json();}).then(p=>{s.layout_json=p.layout_json||clone(state.layout);saveMsg.textContent="Saved "+new Date().toLocaleTimeString();}).catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
document.getElementById("previewBtn").onclick=()=>{const s=cur();if(s)window.open(previewTpl.replace("__STEP__",String(s.id)),"_blank");};
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

    if((key==="delete"||key==="backspace") && state.sel && !isTextField){
        e.preventDefault();
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

