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
.fb-grid{margin-top:12px;display:grid;grid-template-columns:260px 1fr 330px;gap:12px}
.fb-card{background:#fff;border:1px solid #dbeafe;border-radius:12px;padding:10px}
.fb-h{font-size:16px;font-weight:900;color:#1e40af;margin:2px 0 10px}
.fb-lib button{display:block;width:100%;text-align:left;margin-bottom:7px;padding:9px;border:1px solid #dbeafe;border-radius:8px;background:#f8fafc;font-weight:700;cursor:grab}
.fb-lib i{width:18px;margin-right:6px;color:#1d4ed8}
#canvas{min-height:60vh;border:2px dashed #93c5fd;border-radius:12px;padding:10px;background:linear-gradient(180deg,#f8fafc,#e0f2fe);overflow:auto}
.sec{border:1px dashed #64748b;border-radius:10px;padding:8px;margin-bottom:9px;background:#fff}
.row{display:flex;flex-wrap:wrap;gap:8px;border:1px dashed #cbd5e1;border-radius:8px;padding:6px}
.col{flex:1 1 240px;min-height:58px;border:1px dashed #bfdbfe;border-radius:7px;padding:6px;background:#f8fafc}
.el{border:1px solid #e2e8f0;border-radius:7px;padding:7px;background:#fff;margin-bottom:6px}
.sel{outline:2px solid #2563eb;outline-offset:2px}
.settings label{font-size:12px;font-weight:800;color:#1e3a8a;display:block;margin:0 0 4px}
.settings input,.settings select,.settings textarea{width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:8px;margin-bottom:8px}
.settings .meta{font-size:12px;color:#475569;font-weight:700;margin-bottom:8px}
.px-wrap{display:flex;align-items:center;gap:6px;margin-bottom:8px}
.px-wrap input[type="number"]{margin-bottom:0}
.px-unit{font-size:12px;font-weight:800;color:#334155;min-width:18px}
.rt-box{border:1px solid #cbd5e1;border-radius:8px;background:#fff;margin-bottom:8px}
.rt-tools{display:flex;gap:6px;padding:6px;border-bottom:1px solid #e2e8f0}
.rt-tools button{padding:5px 8px;border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;font-weight:800;cursor:pointer}
.rt-editor{min-height:90px;padding:8px;outline:none}
@media(max-width:1080px){.fb-grid{grid-template-columns:1fr}}
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

<div class="fb-grid">
    <div class="fb-card fb-lib">
        <h3 class="fb-h">Components</h3>
        <button draggable="true" data-c="section"><i class="fas fa-square"></i>Section</button>
        <button draggable="true" data-c="row"><i class="fas fa-grip-lines"></i>Row</button>
        <button draggable="true" data-c="column"><i class="fas fa-columns"></i>Column</button>
        <button draggable="true" data-c="heading"><i class="fas fa-heading"></i>Heading</button>
        <button draggable="true" data-c="text"><i class="fas fa-font"></i>Text</button>
        <button draggable="true" data-c="image"><i class="fas fa-image"></i>Image</button>
        <button draggable="true" data-c="button"><i class="fas fa-square-plus"></i>Button</button>
        <button draggable="true" data-c="form"><i class="fas fa-file-lines"></i>Form</button>
        <button draggable="true" data-c="video"><i class="fas fa-video"></i>Video</button>
        <button draggable="true" data-c="countdown"><i class="fas fa-hourglass-half"></i>Countdown</button>
        <button draggable="true" data-c="spacer"><i class="fas fa-arrows-up-down"></i>Spacer</button>
    </div>

    <div class="fb-card">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
            <label for="stepSel" style="font-weight:800;">Step</label>
            <select id="stepSel"></select>
            <span id="saveMsg" style="font-size:12px;color:#475569;font-weight:700;">Not saved yet</span>
        </div>
        <div id="canvas"></div>
    </div>

    <div class="fb-card settings">
        <h3 id="settingsTitle" class="fb-h" style="margin-bottom:4px;">Settings Panel</h3>
        <div id="settings"><p class="meta">Select a component to edit.</p></div>
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

function pxIfNumber(v){const t=(v||"").trim();return /^\d+(\.\d+)?$/.test(t)?t+"px":t;}
function pxToNumber(v){const t=(v||"").toString().trim();const m=t.match(/^(-?\d+(\.\d+)?)px$/i);if(m)return m[1];if(/^-?\d+(\.\d+)?$/.test(t))return t;return "";}
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

function loadStep(id){
    state.sid=+id;
    const s=cur();
    state.layout=(s&&s.layout_json&&Array.isArray(s.layout_json.sections)&&s.layout_json.sections.length)?clone(s.layout_json):defaults();
    state.sel=null;
    saveMsg.textContent="Loaded "+s.title;
    render();
}

function selectedTarget(){const x=state.sel;if(!x)return null;if(x.k==="sec")return sec(x.s);if(x.k==="row")return row(x.s,x.r);if(x.k==="col")return col(x.s,x.r,x.c);if(x.k==="el")return el(x.s,x.r,x.c,x.e);return null;}
function selectedType(){const x=state.sel,t=selectedTarget();if(!x||!t)return "None";if(x.k==="el")return (t.type||"element");if(x.k==="sec")return "section";if(x.k==="row")return "row";if(x.k==="col")return "column";return "None";}
function titleCase(v){return (v||"").replace(/[_-]/g," ").replace(/\b\w/g,m=>m.toUpperCase());}

function addComponent(type){
    const p=state.sel||{},s=sec(p.s)||state.layout.sections[0],r=row(p.s,p.r)||(s?.rows||[])[0],c=col(p.s,p.r,p.c)||(r?.columns||[])[0];
    if(type==="section"){state.layout.sections.push({id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff"},rows:[{id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]}]});return;}
    if(type==="row"){if(s)(s.rows=s.rows||[]).push({id:uid("row"),style:{gap:"8px"},columns:[{id:uid("col"),style:{},elements:[]}]});return;}
    if(type==="column"){if(r)(r.columns=r.columns||[]).push({id:uid("col"),style:{},elements:[]});return;}
    if(!c)return;
    const d={heading:{content:"Heading",style:{fontSize:"32px"},settings:{}},text:{content:"Text",style:{fontSize:"16px"},settings:{}},image:{content:"",style:{width:"100%"},settings:{src:"",alt:"Image"}},button:{content:"Click Me",style:{backgroundColor:"#2563eb",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center"},settings:{link:"#"}},form:{content:"Submit",style:{},settings:{}},video:{content:"",style:{},settings:{src:""}},countdown:{content:"Countdown",style:{},settings:{targetDate:""}},spacer:{content:"",style:{height:"24px"},settings:{}}}[type]||{content:"Text",style:{},settings:{}};
    c.elements.push({id:uid("el"),type:type,content:d.content,style:clone(d.style),settings:clone(d.settings)});
}

function removeSelected(){
    const x=state.sel;if(!x)return;
    if(x.k==="el"){const c=col(x.s,x.r,x.c);if(!c)return;c.elements=(c.elements||[]).filter(i=>i.id!==x.e);}
    else if(x.k==="col"){const r=row(x.s,x.r);if(!r)return;r.columns=(r.columns||[]).filter(i=>i.id!==x.c);}
    else if(x.k==="row"){const s=sec(x.s);if(!s)return;s.rows=(s.rows||[]).filter(i=>i.id!==x.r);}
    else if(x.k==="sec"){state.layout.sections=(state.layout.sections||[]).filter(i=>i.id!==x.s);}
    state.sel=null;render();
}

function renderElement(item,ctx){
    const w=document.createElement("div");w.className="el";styleApply(w,item.style||{});
    if(state.sel&&state.sel.k==="el"&&state.sel.e===item.id)w.classList.add("sel");
    w.onclick=e=>{e.stopPropagation();state.sel={k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};render();};
    if(item.type==="heading"||item.type==="text"){const n=document.createElement(item.type==="heading"?"h2":"p");n.contentEditable="true";n.style.margin="0";n.innerHTML=item.content||"";styleApply(n,item.style||{});n.oninput=()=>{item.content=n.innerHTML||"";};onRichTextKeys(n,()=>{item.content=n.innerHTML||"";});w.appendChild(n);}
    else if(item.type==="button"){const b=document.createElement("button");b.type="button";b.contentEditable="true";b.innerHTML=item.content||"Button";b.style.border="none";b.style.padding="10px 18px";b.style.borderRadius="999px";b.style.background=(item.style&&item.style.backgroundColor)||"#2563eb";b.style.color=(item.style&&item.style.color)||"#fff";b.oninput=()=>{item.content=b.innerHTML||"";};onRichTextKeys(b,()=>{item.content=b.innerHTML||"";});w.appendChild(b);}
    else if(item.type==="image"){w.innerHTML=(item.settings&&item.settings.src)?'<img src="'+item.settings.src+'" alt="'+(item.settings.alt||"Image")+'" style="max-width:100%;height:auto;display:block;">':'<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;">Image placeholder</div>';}
    else if(item.type==="form"){w.innerHTML='<input disabled placeholder="Name"><input disabled placeholder="Email"><button class="fb-btn primary" disabled>'+(item.content||"Submit")+'</button>';}
    else if(item.type==="video"){w.innerHTML=(item.settings&&item.settings.src)?'<div style="padding:10px;background:#0f172a;color:#fff;border-radius:8px;">Video: '+item.settings.src+'</div>':'<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;">Video URL placeholder</div>';}
    else if(item.type==="countdown"){w.innerHTML='<div style="display:inline-flex;padding:8px 12px;border:1px solid #cbd5e1;border-radius:999px;font-weight:800;">Countdown Timer</div>';}
    else if(item.type==="spacer"){const h=((item.style&&item.style.height)||"24px");const isSel=!!(state.sel&&state.sel.k==="el"&&state.sel.e===item.id);const bg=isSel?'repeating-linear-gradient(90deg,#f1f5f9,#f1f5f9 8px,#e2e8f0 8px,#e2e8f0 16px)':'transparent';w.innerHTML='<div style="height:'+h+';background:'+bg+'"></div>';}
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
    const fire=()=>{let v=n.value;if(opts&&opts.px)v=pxIfNumber(v);cb(v);renderCanvas();};
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
    const sync=()=>{cb(n.innerHTML||"");renderCanvas();};
    n.addEventListener("input",sync);
    n.addEventListener("keydown",e=>{
        if(!(e.ctrlKey||e.metaKey))return;
        const k=(e.key||"").toLowerCase();
        if(k==="b"){e.preventDefault();document.execCommand("bold");sync();}
        if(k==="i"){e.preventDefault();document.execCommand("italic");sync();}
        if(k==="u"){e.preventDefault();document.execCommand("underline");sync();}
    });
}

function bindPx(id,val,cb){
    const n=document.getElementById(id);if(!n)return;
    n.value=pxToNumber(val);
    const fire=()=>{const raw=(n.value||"").trim();cb(raw===""?"":raw+"px");renderCanvas();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function uploadImage(file,done){
    const fd=new FormData();fd.append("image",file);
    fetch(uploadUrl,{method:"POST",headers:{"X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:fd})
        .then(r=>r.json()).then(p=>{if(p&&p.url)done(p.url);}).catch(()=>alert("Image upload failed."));
}

function renderSettings(){
    settingsTitle.textContent="Settings Panel";
    const t=selectedTarget();
    if(!state.sel||!t){settings.innerHTML='<p class="meta">Select a component to edit.</p>';return;}
    settingsTitle.textContent=titleCase(selectedType())+" Settings";
    const sty=()=>{t.style=t.style||{};return t.style;};
    const remove='<button id="removeSel" class="fb-btn danger" type="button"><i class="fas fa-trash"></i> Remove Selected</button>';

    if(state.sel.k==="sec"){
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><label>Background image Upload</label><input id="bgUp" type="file" accept="image/*"><label>Margin</label><div class="px-wrap"><input id="m" type="number" step="1"><span class="px-unit">px</span></div><label>Padding</label><div class="px-wrap"><input id="p" type="number" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v);
        bindPx("m",(t.style&&t.style.margin)||"",v=>sty().margin=v);
        bindPx("p",(t.style&&t.style.padding)||"20px",v=>sty().padding=v);
        const bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0])uploadImage(bgUp.files[0],url=>{sty().backgroundImage='url('+url+')';sty().backgroundSize='cover';sty().backgroundPosition='center';renderCanvas();});};
    } else if(state.sel.k==="el"&&t.type==="button"){
        settings.innerHTML='<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div><label>Link</label><input id="link"><label>Background color</label><input id="bbg" type="color"><label>Border radius</label><div class="px-wrap"><input id="rad" type="number" step="1"><span class="px-unit">px</span></div><label>Padding</label><input id="p" placeholder="10px 18px">'+fontSelectHtml('ff')+'<label>Text alignment</label><select id="a"><option>left</option><option>center</option><option>right</option></select>'+remove;
        bindRichEditor("contentRt",t.content,v=>t.content=v);
        const rt=document.getElementById("contentRt");
        const b=document.getElementById("rtBold"),i=document.getElementById("rtItalic"),u=document.getElementById("rtUnderline");
        if(b)b.onclick=()=>{rt&&rt.focus();document.execCommand("bold");t.content=rt.innerHTML||"";renderCanvas();};
        if(i)i.onclick=()=>{rt&&rt.focus();document.execCommand("italic");t.content=rt.innerHTML||"";renderCanvas();};
        if(u)u.onclick=()=>{rt&&rt.focus();document.execCommand("underline");t.content=rt.innerHTML||"";renderCanvas();};
        bind("link",(t.settings&&t.settings.link)||"",v=>{t.settings=t.settings||{};t.settings.link=v;});
        bind("bbg",(t.style&&t.style.backgroundColor)||"#2563eb",v=>sty().backgroundColor=v);bindPx("rad",(t.style&&t.style.borderRadius)||"999px",v=>sty().borderRadius=v);bind("p",(t.style&&t.style.padding)||"10px 18px",v=>sty().padding=v,{px:true});bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v);bind("a",(t.style&&t.style.textAlign)||"center",v=>sty().textAlign=v);
    } else if(state.sel.k==="el"&&t.type==="image"){
        settings.innerHTML='<label>Upload image</label><input id="up" type="file" accept="image/*"><label>Image URL</label><input id="src"><label>Alt</label><input id="alt"><label>Width</label><input id="w" placeholder="100%"><label>Border</label><input id="b"><label>Shadow</label><input id="sh">'+remove;
        bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;});
        bind("alt",(t.settings&&t.settings.alt)||"",v=>{t.settings=t.settings||{};t.settings.alt=v;});
        bind("w",(t.style&&t.style.width)||"100%",v=>sty().width=v,{px:true});bind("b",(t.style&&t.style.border)||"",v=>sty().border=v);bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v);
        const up=document.getElementById("up");if(up)up.onchange=()=>{if(up.files&&up.files[0])uploadImage(up.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;renderCanvas();});};
    } else if(state.sel.k==="el"&&t.type==="video"){
        settings.innerHTML='<label>Upload video</label><input id="upv" type="file" accept="video/*"><label>Video URL</label><input id="src"><label>Width</label><input id="w" placeholder="100%"><label>Border</label><input id="b"><label>Shadow</label><input id="sh">'+remove;
        bind("src",(t.settings&&t.settings.src)||"",v=>{t.settings=t.settings||{};t.settings.src=v;});
        bind("w",(t.style&&t.style.width)||"100%",v=>sty().width=v,{px:true});bind("b",(t.style&&t.style.border)||"",v=>sty().border=v);bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v);
        const upv=document.getElementById("upv");if(upv)upv.onchange=()=>{if(upv.files&&upv.files[0])uploadImage(upv.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;renderCanvas();});};
    } else if(state.sel.k==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        settings.innerHTML=(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button"><b>B</b></button><button id="rtItalic" type="button"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+'<label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+fontSelectHtml('ff')+'<label>Padding</label><div class="px-wrap"><input id="p" type="number" step="1"><span class="px-unit">px</span></div><label>Text align</label><select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select>'+remove;
        if(rich){
            bindRichEditor("contentRt",t.content,v=>t.content=v);
            const rt=document.getElementById("contentRt");
            const b=document.getElementById("rtBold"),i=document.getElementById("rtItalic"),u=document.getElementById("rtUnderline");
            if(b)b.onclick=()=>{rt&&rt.focus();document.execCommand("bold");t.content=rt.innerHTML||"";renderCanvas();};
            if(i)i.onclick=()=>{rt&&rt.focus();document.execCommand("italic");t.content=rt.innerHTML||"";renderCanvas();};
            if(u)u.onclick=()=>{rt&&rt.focus();document.execCommand("underline");t.content=rt.innerHTML||"";renderCanvas();};
        } else {
            bind("content",t.content,v=>t.content=v);
        }
        bind("co",(t.style&&t.style.color)||"#334155",v=>sty().color=v);bindPx("fs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v);bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v);bindPx("p",(t.style&&t.style.padding)||"",v=>sty().padding=v);bind("a",(t.style&&t.style.textAlign)||"",v=>sty().textAlign=v);
    } else {
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><label>Padding</label><div class="px-wrap"><input id="p" type="number" step="1"><span class="px-unit">px</span></div><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v);bindPx("p",(t.style&&t.style.padding)||"",v=>sty().padding=v);bindPx("g",(t.style&&t.style.gap)||"",v=>sty().gap=v);
    }
    const btn=document.getElementById("removeSel");if(btn)btn.onclick=removeSelected;
}

function render(){renderCanvas();renderSettings();}

document.querySelectorAll(".fb-lib button").forEach(b=>{
    b.ondragstart=e=>e.dataTransfer.setData("c",b.dataset.c||"");
    b.onclick=()=>{addComponent(b.dataset.c||"");render();};
});

stepSel.onchange=()=>loadStep(stepSel.value);
document.getElementById("saveBtn").onclick=()=>{const s=cur();if(!s)return;saveMsg.textContent="Saving...";fetch(saveUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:JSON.stringify({step_id:s.id,layout_json:state.layout})}).then(r=>{if(!r.ok)throw 1;return r.json();}).then(p=>{s.layout_json=p.layout_json||clone(state.layout);saveMsg.textContent="Saved "+new Date().toLocaleTimeString();}).catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});};
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
});

loadStep(state.sid);
})();
</script>
@endsection
