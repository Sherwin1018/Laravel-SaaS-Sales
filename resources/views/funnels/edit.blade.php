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
.step-action-preview{margin-top:12px;padding:14px;border:1px dashed #93c5fd;border-radius:10px;background:#f0f9ff;color:#0c4a6e;font-size:13px}
.step-action-preview .preview-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;color:#0369a1}
.step-action-preview label{display:block;margin-bottom:4px;font-weight:700}
.step-action-preview input{display:block;width:100%;max-width:280px;padding:8px 10px;margin-bottom:10px;border:1px solid #bae6fd;border-radius:6px;background:#fff}
.step-action-preview .btn{margin-top:8px;padding:8px 16px;border-radius:8px;cursor:default;opacity:0.85}
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
.settings .meta{font-size:12px;color:#475569;font-weight:700;margin-bottom:8px}
.settings-delete-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #e2e8f0}
.settings-delete-wrap .fb-btn{width:100%;justify-content:center;gap:6px}
.form-field-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
.form-field-row select{flex:1;min-width:0;padding:8px;border:1px solid #cbd5e1;border-radius:8px}
.form-field-row input.form-field-label{flex:1;min-width:0;padding:8px;border:1px solid #cbd5e1;border-radius:8px}
.form-field-row .form-field-rm{flex:0 0 32px;width:32px;padding:0;font-size:18px;line-height:1}
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
.size-link button{width:28px;height:28px;padding:0;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px}
.size-link button.linked{background:#e0e7ff;border-color:#6366f1}
.size-link button:hover{background:#e2e8f0}
.size-link span{font-size:11px;color:#64748b}
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
                <button draggable="true" data-c="image"><i class="fas fa-image"></i>Image</button>
                <button draggable="true" data-c="button"><i class="fas fa-square-plus"></i>Button</button>
                <button draggable="true" data-c="form" id="fbCompForm" class="fb-comp-step-only" data-step-type="opt_in"><i class="fas fa-file-lines"></i>Form</button>
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
        <div id="stepActionPreview" class="step-action-preview" style="display:none;"></div>
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
const defaults=()=>({sections:[{id:uid("sec"),style:{padding:"20px",backgroundColor:"#ffffff"},rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[{id:uid("el"),type:"heading",content:"Welcome to Our Offer",style:{fontSize:"32px",color:"#0f172a"},settings:{}}]}]}]}]});
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
                    } else if(el.type==="form"){
                        if(!el.style||typeof el.style!=="object")el.style={};
                        if(!el.settings||typeof el.settings!=="object")el.settings={};
                        var fw=(el.settings&&el.settings.width)||"";
                        if(fw&&!el.style.width)el.style.width=fw;
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

function addComponent(type){
    saveToHistory();
    const p=state.sel||{},s=sec(p.s)||state.layout.sections[0],r=row(p.s,p.r)||(s?.rows||[])[0],c=col(p.s,p.r,p.c)||(r?.columns||[])[0];
    if(type==="section"){state.layout.sections.push({id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff"},rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}]});return;}
    if(type==="row"){if(s)(s.rows=s.rows||[]).push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});return;}
    if(type==="column"){if(r)(r.columns=r.columns||[]).push({id:uid("col"),style:{},elements:[]});return;}
    if(!c)return;
    const formFieldTypes=[{v:"first_name",l:"First name"},{v:"last_name",l:"Last name"},{v:"email",l:"Email"},{v:"phone_number",l:"Phone number"},{v:"country",l:"Country"},{v:"city",l:"City"},{v:"custom",l:"Custom (text)"}];
const d={heading:{content:"Heading",style:{fontSize:"32px"},settings:{}},text:{content:"Text",style:{fontSize:"16px"},settings:{}},image:{content:"",style:{width:"100%"},settings:{src:"",alt:"Image",alignment:"left"}},button:{content:"Click Me",style:{backgroundColor:"#2563eb",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center"},settings:{link:"#"}},form:{content:"Submit",style:{},settings:{fields:[{type:"first_name",label:"First name"},{type:"last_name",label:"Last name"},{type:"email",label:"Email"},{type:"phone_number",label:"Phone (09XXXXXXXXX)"}]}},video:{content:"",style:{},settings:{src:"",alignment:"left"}},spacer:{content:"",style:{height:"24px"},settings:{}}}[type]||{content:"Text",style:{},settings:{}};
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
    else if(item.type==="form"){var flist=Array.isArray(item.settings&&item.settings.fields)&&item.settings.fields.length?item.settings.fields:[{type:"first_name",label:"First name"},{type:"email",label:"Email"}];if((!w.style.width||w.style.width==="")&&item.settings&&item.settings.width)w.style.width=item.settings.width;var fal=(item.settings&&item.settings.alignment)||"left";if(fal==="center"){w.style.marginLeft="auto";w.style.marginRight="auto";}else if(fal==="right"){w.style.marginLeft="auto";w.style.marginRight="0";}else{w.style.marginLeft="0";w.style.marginRight="auto";}w.style.display="flex";w.style.flexDirection="column";var gapVal=(item.style&&item.style.gap)||"10px";w.style.gap=gapVal;var iw=(item.settings&&item.settings.inputWidth)||"100%";var ip=(item.settings&&item.settings.inputPadding)||"8px";var ifs=(item.settings&&item.settings.inputFontSize)||"";var inputStyle="width:"+iw+";box-sizing:border-box;padding:"+ip+";border:1px solid #cbd5e1;border-radius:6px;"+(ifs?"font-size:"+ifs+";":"");var inputs=flist.map(f=>{var lbl=(f.label||f.type||"").replace(/"/g,"&quot;");return '<div><label style="display:block;margin-bottom:4px;font-size:14px;color:#334155;">'+lbl+'</label><input disabled placeholder="'+lbl+'" style="'+inputStyle+'">';}).join("");w.innerHTML=inputs+'<div><button class="fb-btn primary" disabled style="margin-top:4px;">'+(item.content||"Submit")+'</button></div>';}
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
    (state.layout.sections||[]).forEach(s=>{
        const sn=document.createElement("section");sn.className="sec";styleApply(sn,s.style||{});
        if(state.sel&&state.sel.k==="sec"&&state.sel.s===s.id)sn.classList.add("sel");
        sn.onclick=e=>{e.stopPropagation();state.sel={k:"sec",s:s.id};render();};
        (s.rows||[]).forEach(r=>{
            const rn=document.createElement("div");rn.className="row";styleApply(rn,r.style||{});
            if(state.sel&&state.sel.k==="row"&&state.sel.r===r.id)rn.classList.add("sel");
            rn.onclick=e=>{e.stopPropagation();state.sel={k:"row",s:s.id,r:r.id};render();};
            (r.columns||[]).forEach(c=>{
                const cn=document.createElement("div");cn.className="col";styleApply(cn,c.style||{});
                if(state.sel&&state.sel.k==="col"&&state.sel.c===c.id)cn.classList.add("sel");
                cn.onclick=e=>{e.stopPropagation();state.sel={k:"col",s:s.id,r:r.id,c:c.id};render();};
                cn.ondragover=e=>e.preventDefault();
                cn.ondrop=e=>{e.preventDefault();const t=e.dataTransfer.getData("c");if(!t)return;state.sel={k:"col",s:s.id,r:r.id,c:c.id};addComponent(t);render();};
                (c.elements||[]).forEach(it=>cn.appendChild(renderElement(it,{s:s.id,r:r.id,c:c.id})));
                rn.appendChild(cn);
            });
            sn.appendChild(rn);
        });
        canvas.appendChild(sn);
    });
    if(!(state.layout.sections||[]).length)canvas.innerHTML='<p style="font-size:13px;color:#475569;">Drag a Section to start.</p>';
    canvas.ondragover=e=>e.preventDefault();
    canvas.ondrop=e=>{e.preventDefault();const t=e.dataTransfer.getData("c");if(t){addComponent(t);render();}};
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

    if(state.sel.k==="sec"){
        var padDef=[20,20,20,20],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><label>Background image Upload</label><input id="bgUp" type="file" accept="image/*"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>⟷</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        const bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();uploadImage(bgUp.files[0],url=>{sty().backgroundImage='url('+url+')';sty().backgroundSize='cover';sty().backgroundPosition='center';renderCanvas();});}};
    } else if(state.sel.k==="el"&&t.type==="button"){
        var padDef=[10,18,10,18],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div><label>Link</label><input id="link"><label>Button width</label><select id="btnWidth"><option value="fluid">Fluid</option><option value="fill">Fill width</option></select><label>Alignment</label><select id="btnAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid" id="padGrid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>⟷</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid" id="marGrid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div><label>Background color</label><input id="bbg" type="color"><label>Border radius</label><div class="px-wrap"><input id="rad" type="number" step="1"><span class="px-unit">px</span></div>'+fontSelectHtml('ff')+'<label>Text alignment</label><select id="a"><option>left</option><option>center</option><option>right</option></select>'+remove;
        bindRichEditor("contentRt",t.content,v=>t.content=v);
        const rt=document.getElementById("contentRt");
        const b=document.getElementById("rtBold"),i=document.getElementById("rtItalic"),u=document.getElementById("rtUnderline");
        if(b)b.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("bold");t.content=rt.innerHTML||"";renderCanvas();};
        if(i)i.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("italic");t.content=rt.innerHTML||"";renderCanvas();};
        if(u)u.onclick=()=>{saveToHistory();rt&&rt.focus();document.execCommand("underline");t.content=rt.innerHTML||"";renderCanvas();};
        bind("link",(t.settings&&t.settings.link)||"",v=>{t.settings=t.settings||{};t.settings.link=v;},{undo:true});
        var btnWidth=document.getElementById("btnWidth"),btnAlign=document.getElementById("btnAlign");
        if(btnWidth){btnWidth.value=(t.settings&&t.settings.widthBehavior)||"fluid";btnWidth.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.widthBehavior=btnWidth.value;renderCanvas();};}
        if(btnAlign){btnAlign.value=(t.settings&&t.settings.alignment)||"center";btnAlign.onchange=()=>{saveToHistory();t.settings=t.settings||{};t.settings.alignment=btnAlign.value;renderCanvas();};}
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        if(linkPad){linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};}
        if(linkMar){linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};}
        bind("bbg",(t.style&&t.style.backgroundColor)||"#2563eb",v=>sty().backgroundColor=v,{undo:true});bindPx("rad",(t.style&&t.style.borderRadius)||"999px",v=>sty().borderRadius=v,{undo:true});bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v,{undo:true});bind("a",(t.style&&t.style.textAlign)||"center",v=>sty().textAlign=v,{undo:true});
    } else if(state.sel.k==="el"&&t.type==="image"){
        var marDef=[0,0,0,0],mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<label>Upload image</label><input id="up" type="file" accept="image/*"><label>Image URL</label><input id="src"><label>Alt</label><input id="alt"><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div><label>Border</label><input id="b"><label>Shadow</label><input id="sh">'+remove;
        bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;},{undo:true});
        bind("alt",(t.settings&&t.settings.alt)||"",v=>{t.settings=t.settings||{};t.settings.alt=v;},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        var marginLinked=false;
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkMar=document.getElementById("linkMar");
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        bind("b",(t.style&&t.style.border)||"",v=>sty().border=v,{undo:true});bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v,{undo:true});
        const up=document.getElementById("up");if(up)up.onchange=()=>{if(up.files&&up.files[0]){saveToHistory();uploadImage(up.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;renderCanvas();},"Image upload");}};
    } else if(state.sel.k==="el"&&t.type==="video"){
        var marDef=[0,0,0,0],mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<label>Upload video</label><input id="upv" type="file" accept="video/*"><label>Video URL</label><input id="src"><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div><label>Border</label><input id="b"><label>Shadow</label><input id="sh">'+remove;
        bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        var marginLinked=false;
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkMar=document.getElementById("linkMar");
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        bind("b",(t.style&&t.style.border)||"",v=>sty().border=v,{undo:true});bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v,{undo:true});
        const upv=document.getElementById("upv");if(upv)upv.onchange=()=>{if(upv.files&&upv.files[0]){saveToHistory();const srcInput=document.getElementById("src");uploadImage(upv.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;if(srcInput)srcInput.value=url;render();},"Video upload");}};
    } else if(state.sel.k==="el"&&t.type==="form"){
        t.settings=t.settings||{};t.settings.fields=Array.isArray(t.settings.fields)&&t.settings.fields.length?t.settings.fields:[{type:"first_name",label:"First name"},{type:"last_name",label:"Last name"},{type:"email",label:"Email"},{type:"phone_number",label:"Phone (09XXXXXXXXX)"}];
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Form width</label><input id="formWidth" placeholder="100%"><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>⟷</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div>';
        var inputStyleBlock='<label class="size-label">Gap between fields</label><div class="px-wrap"><input id="formGap" type="number" step="1"><span class="px-unit">px</span></div><label class="size-label">Input styling</label><label>Input width</label><input id="inputWidth" placeholder="100%"><label>Input padding</label><input id="inputPadding" placeholder="8px 12px"><label>Input font size</label><div class="px-wrap"><input id="inputFontSize" type="number" step="1"><span class="px-unit">px</span></div>';
        var fieldsHtml=t.settings.fields.map((f,i)=>'<div class="form-field-row" data-idx="'+i+'"><select class="form-field-type"><option value="first_name">First name</option><option value="last_name">Last name</option><option value="email">Email</option><option value="phone_number">Phone number</option><option value="country">Country</option><option value="city">City</option><option value="custom">Custom (text)</option></select><input type="text" class="form-field-label" placeholder="Label" value="'+(f.label||"").replace(/"/g,"&quot;")+'"><button type="button" class="fb-btn form-field-rm" title="Remove">×</button></div>').join('');
        settings.innerHTML='<label>Submit button text</label><input id="formSubmitText" placeholder="Submit"><label>Alignment</label><select id="formAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Form fields</label><div id="formFieldsList">'+fieldsHtml+'</div><button type="button" id="formFieldAdd" class="fb-btn">+ Add field</button>'+sizeBlock+inputStyleBlock+remove;
        document.getElementById("formSubmitText").value=t.content||"Submit";
        document.getElementById("formSubmitText").oninput=function(){saveToHistory();t.content=this.value||"Submit";renderCanvas();};
        bind("formAlign",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v||"left";renderCanvas();},{undo:true});
        bind("formWidth",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{var w=v||"100%";sty().width=w;t.settings=t.settings||{};t.settings.width=w;renderCanvas();},{undo:true});
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        bindPx("formGap",(t.style&&t.style.gap)||"",v=>{sty().gap=v;renderCanvas();},{undo:true});
        bind("inputWidth",(t.settings&&t.settings.inputWidth)||"",v=>{t.settings=t.settings||{};t.settings.inputWidth=v||"";renderCanvas();},{undo:true});
        bind("inputPadding",(t.settings&&t.settings.inputPadding)||"",v=>{t.settings=t.settings||{};t.settings.inputPadding=v||"";renderCanvas();},{undo:true});
        bindPx("inputFontSize",(t.settings&&t.settings.inputFontSize)||"",v=>{t.settings=t.settings||{};t.settings.inputFontSize=v||"";renderCanvas();},{undo:true});
        t.settings.fields.forEach((f,i)=>{var row=document.querySelector(".form-field-row[data-idx=\""+i+"\"]");if(row){var sel=row.querySelector(".form-field-type");var lbl=row.querySelector(".form-field-label");if(sel)sel.value=f.type||"custom";if(sel)sel.onchange=function(){var idx=parseInt(row.getAttribute("data-idx"),10);saveToHistory();t.settings.fields[idx].type=this.value;renderCanvas();};if(lbl)lbl.oninput=function(){var idx=parseInt(row.getAttribute("data-idx"),10);saveToHistory();t.settings.fields[idx].label=this.value;renderCanvas();};row.querySelector(".form-field-rm").onclick=function(){var idx=parseInt(row.getAttribute("data-idx"),10);saveToHistory();t.settings.fields.splice(idx,1);render();};}});
        document.getElementById("formFieldAdd").onclick=function(){saveToHistory();t.settings.fields.push({type:"custom",label:""});render();};
    } else if(state.sel.k==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>⟷</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div>';
        settings.innerHTML=(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+'<label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+fontSelectHtml('ff')+sizeBlock+'<label>Text align</label><select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select>'+remove;
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
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>⟷</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>⟷</span></button><span>Link</span></div></div></div><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div>'+remove;
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
    const btnDel=document.getElementById("btnDeleteSelected");if(btnDel)btnDel.onclick=()=>removeSelected();
}

function layoutHasForm(layout){
    if(!layout||!layout.sections)return false;
    for(var i=0;i<layout.sections.length;i++){
        var rows=layout.sections[i].rows||[];
        for(var r=0;r<rows.length;r++){
            var cols=rows[r].columns||[];
            for(var c=0;c<cols.length;c++){
                var els=cols[c].elements||[];
                for(var e=0;e<els.length;e++)if(els[e].type==="form")return true;
            }
        }
    }
    return false;
}
function updateStepActionPreview(){
    const s=cur();
    document.querySelectorAll(".fb-comp-step-only").forEach(btn=>{
        var stepType=btn.getAttribute("data-step-type");
        btn.style.display=stepType&&s&&s.type===stepType?"":"none";
    });
    const el=document.getElementById("stepActionPreview");if(!el)return;
    if(!s){el.style.display="none";el.innerHTML="";return;}
    if(s.type==="opt_in"){
        el.style.display="block";
        if(layoutHasForm(state.layout)){el.innerHTML='<div class="preview-label">Form in your layout will submit as opt-in (name, email, phone).</div>';return;}
        el.innerHTML='<div class="preview-label">Add a Form component from the panel to design your opt-in form. It will submit name, email &amp; phone with full opt-in behaviour.</div>';return;
    }
    if(s.type==="checkout"){el.style.display="block";el.innerHTML='<div class="preview-label">Step form (appears on page &amp; preview)</div><p class="price" style="margin:0 0 8px;font-weight:800;">Price / Checkout</p><button type="button" class="btn" disabled>Complete Checkout</button>';return;}
    if(s.type==="upsell"||s.type==="downsell"){el.style.display="block";el.innerHTML='<div class="preview-label">Step form (appears on page &amp; preview)</div><p class="price" style="margin:0 0 8px;font-weight:800;">Additional Offer</p><button type="button" class="btn" disabled style="margin-right:8px;">Yes, Add This Offer</button><button type="button" class="btn gray" disabled>No Thanks</button>';return;}
    el.style.display="none";el.innerHTML="";
}
function render(){renderCanvas();renderSettings();updateStepActionPreview();if(state.sel)showLeftPanel("settings");}

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
function syncPendingSelectionToState(){
    if(document.activeElement&&typeof document.activeElement.blur==="function")document.activeElement.blur();
    var t=selectedTarget();
    if(state.sel&&state.sel.k==="el"&&t){
        if(t.type==="video"||t.type==="image"){
            var wIn=document.getElementById("w");
            if(wIn){var v=(wIn.value||"").trim();if(v){var w=pxIfNumber(v);t.style=t.style||{};t.style.width=w;t.settings=t.settings||{};t.settings.width=w;}}
        } else if(t.type==="form"){
            t.style=t.style||{};t.settings=t.settings||{};
            var fa=document.getElementById("formAlign");if(fa){t.settings.alignment=(fa.value||"left");}
            var fw=document.getElementById("formWidth");if(fw){var v=(fw.value||"").trim();var w=v||"100%";t.style.width=w;t.settings.width=w;}
            var pt=document.getElementById("pTop"),pr=document.getElementById("pRight"),pb=document.getElementById("pBottom"),pl=document.getElementById("pLeft");
            if(pt&&pr&&pb&&pl){var pv=[Number(pt.value)||0,Number(pr.value)||0,Number(pb.value)||0,Number(pl.value)||0];t.style.padding=spacingToCss(pv);}
            var mt=document.getElementById("mTop"),mr=document.getElementById("mRight"),mb=document.getElementById("mBottom"),ml=document.getElementById("mLeft");
            if(mt&&mr&&mb&&ml){var mv=[Number(mt.value)||0,Number(mr.value)||0,Number(mb.value)||0,Number(ml.value)||0];t.style.margin=spacingToCss(mv);}
            var fg=document.getElementById("formGap");if(fg){var g=(fg.value||"").trim();t.style.gap=g!==""?pxIfNumber(g):"";}
            var iw=document.getElementById("inputWidth");if(iw){var v=(iw.value||"").trim();t.settings.inputWidth=v;}
            var ip=document.getElementById("inputPadding");if(ip){var v=(ip.value||"").trim();t.settings.inputPadding=v;}
            var ifs=document.getElementById("inputFontSize");if(ifs){var v=(ifs.value||"").trim();t.settings.inputFontSize=v!==""?pxIfNumber(v):"";}
        }
    }
}

function persistCurrentStep(){
    const s=cur();
    if(!s)return Promise.reject(new Error("No step selected"));
    syncPendingSelectionToState();
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
        .catch(()=>{saveMsg.textContent="Save failed";alert("Save failed. Preview was not opened to avoid showing stale data.");});
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
