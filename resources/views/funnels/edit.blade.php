@extends('layouts.admin')
@section('title', 'Funnel Builder')
@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Manrope:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600;700;800&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;600;700;800&family=Raleway:wght@400;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
@endsection

@section('content')
<style>
.fb-top{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;background:#240E35;color:#fff;padding:12px;border-radius:12px}
.fb-actions{display:flex;gap:8px;flex-wrap:wrap}
.fb-actions form{margin:0}
.fb-actions .fb-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:42px;padding:0 14px;line-height:1;white-space:nowrap}
.fb-btn{padding:8px 12px;border-radius:8px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;text-decoration:none;cursor:pointer}
.fb-btn.primary{background:#240E35;color:#fff;border-color:#2E1244}.fb-btn.success{background:#16a34a;color:#fff;border-color:#15803d}.fb-btn.danger{background:#dc2626;color:#fff;border-color:#b91c1c}
#saveBtn.fb-btn{background:#16a34a;border-color:#15803d}
#saveBtn.fb-btn:hover{background:#15803d;border-color:#15803d}
.fb-grid{margin-top:12px;display:grid;grid-template-columns:260px 1fr;gap:12px;transition:grid-template-columns .2s ease}
.fb-grid.components-hidden{grid-template-columns:0 1fr}
.fb-grid > .fb-canvas-col{min-width:0;overflow-x:auto}
.fb-grid.components-hidden .fb-components-col{overflow:visible;pointer-events:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-tab{pointer-events:auto}
.fb-components-col{position:sticky;top:12px;align-self:start;min-width:0;overflow-x:visible}
.fb-components-col .fb-panel-toggle{position:fixed;top:50%;transform:translateY(-50%);z-index:100;width:28px;height:28px;border-radius:50%;border:1px solid #E7D8F0;background:#fff;color:#240E35;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.fb-components-col .fb-panel-toggle:hover{background:#E7D8F0;color:#240E35}
.fb-components-col .fb-panel-toggle--hide{left:288px;margin-left:-14px}
.fb-components-col .fb-panel-tab{display:none;left:8px;width:28px;height:28px;border-radius:50%;padding:0}
.fb-grid.components-hidden .fb-components-col .fb-left-tabs{display:none}
.fb-grid.components-hidden .fb-components-col .fb-card{display:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-toggle--hide{display:none}
.fb-grid.components-hidden .fb-components-col .fb-left-panel{display:none}
.fb-grid.components-hidden .fb-components-col .fb-panel-tab{display:flex;align-items:center;justify-content:center;left:8px;top:50%;transform:translateY(-50%);position:fixed;z-index:100}
.fb-left-tabs{display:flex;gap:2px;margin-bottom:8px}
.fb-left-tabs .fb-tab{padding:8px 12px;border:1px solid #E7D8F0;background:#F3EEF7;color:#240E35;font-weight:700;font-size:12px;cursor:pointer;border-radius:8px;flex:1}
.fb-left-tabs .fb-tab:hover{background:#E7D8F0}
.fb-left-tabs .fb-tab.active{background:#240E35;color:#fff;border-color:#2E1244}
.fb-left-panel{display:block}
.fb-left-panel.hidden{display:none}
.fb-card{background:#fff;border:1px solid #E7D8F0;border-radius:12px;padding:10px;min-width:0;max-width:100%;box-sizing:border-box}
#fbLeftPanelComponents .fb-card{max-height:calc(100vh - 120px);overflow-y:auto;overflow-x:hidden}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar{width:8px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
#fbLeftPanelComponents .fb-card::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-grid > *{min-width:0}
.fb-grid .fb-card{overflow-x:hidden}
.fb-canvas-col > .fb-card{overflow-x:visible}
#canvas{overflow-x:hidden;overflow-y:auto;box-sizing:border-box}
#canvas .sec,#canvas .row,#canvas .col,#canvas .el,#canvas .sec-inner,#canvas .row-inner,#canvas .col-inner{max-width:100%;box-sizing:border-box}
#canvas img,#canvas video,#canvas iframe{max-width:100%;height:auto}
#fbSettingsCard{display:flex;flex-direction:column;max-height:calc(100vh - 120px);min-height:0}
#fbSettingsCard .fb-h{flex-shrink:0}
#fbSettingsCard #settings{overflow-y:auto;overflow-x:hidden;flex:1;min-height:0;padding-right:4px}
#fbSettingsCard #settings::-webkit-scrollbar{width:8px}
#fbSettingsCard #settings::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-h{font-size:16px;font-weight:900;color:#240E35;margin:2px 0 10px}
.fb-lib button{display:block;width:100%;text-align:left;margin-bottom:7px;padding:9px;border:1px solid #E7D8F0;border-radius:8px;background:#F3EEF7;font-weight:700;cursor:grab}
.fb-lib i{width:18px;margin-right:6px;color:#2E1244}
.fb-lib-group{margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #E6E1EF}
.fb-lib-group:last-child{margin-bottom:0;padding-bottom:0;border-bottom:0}
.fb-lib-group-title{font-size:12px;font-weight:900;letter-spacing:.02em;text-transform:uppercase;color:#2E1244;margin:0 0 8px}
#canvas{
    width:100%;
    max-width:1400px;
    margin:0 auto;
    height:calc(100vh - 220px);
    min-height:520px;
    max-height:calc(100vh - 220px);
    border:1px solid #E6E1EF;
    border-radius:12px;
    padding:10px;
    background:#ffffff;
    box-shadow:0 14px 36px rgba(15,23,42,.08);
    overflow-x:hidden;
    overflow-y:auto
}
.sec{border:1px dashed #64748b !important;border-radius:0;padding:8px;margin-bottom:9px;background:#fff}
.sec.sec--bare-carousel{border:0;background:transparent;padding:0}
.sec.sec--bare-wrap{border:0;background:transparent;padding:0}
.sec.sec--freeform-canvas{border:0 !important;background:transparent !important;padding:0;margin:0;position:relative}
.row{display:flex;flex-wrap:wrap;gap:8px;border:1px dashed #E6E1EF !important;border-radius:0 !important;padding:6px}
.row.row--bare-wrap{border:0;background:transparent;padding:0}
.row-inner{display:flex;flex-wrap:wrap;gap:8px;position:relative}
.col{flex:1 1 240px;min-height:120px;min-width:0;border:1px dashed #E6E1EF !important;border-radius:0;padding:6px;background:#ffffff;position:relative;overflow:visible}
.row-resize-handle-y{position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);z-index:4;width:42px;height:10px;border-radius:999px;border:1px solid #E7D8F0;background:#E7D8F0;cursor:ns-resize;opacity:.9}
.section-resize-handle-y{position:absolute;left:50%;bottom:-6px;transform:translateX(-50%);z-index:4;width:46px;height:10px;border-radius:999px;border:1px solid #E7D8F0;background:#E7D8F0;cursor:ns-resize;opacity:.95}
.media-resize-dot{position:absolute;z-index:5;width:18px;height:18px;border-radius:999px;border:2px solid #ffffff;background:#6B4A7A;box-shadow:0 1px 2px rgba(15,23,42,.24)}
.media-resize-dot-left{left:-9px;top:50%;transform:translateY(-50%);cursor:ew-resize}
.media-resize-dot-right{right:-9px;top:50%;transform:translateY(-50%);cursor:ew-resize}
.media-resize-dot-bl{left:-9px;bottom:-9px;cursor:nesw-resize}
.media-resize-dot-b{left:50%;bottom:-9px;transform:translateX(-50%);cursor:ns-resize}
.media-resize-dot-br{right:-9px;bottom:-9px;cursor:nwse-resize}
.carousel-resize-dot{position:absolute;z-index:50;width:22px;height:22px;border-radius:999px;border:2px solid #ffffff;background:#240E35;box-shadow:0 2px 6px rgba(15,23,42,.28);pointer-events:auto}
.carousel-resize-dot-left{left:6px;top:50%;transform:translateY(-50%);cursor:ew-resize}
.carousel-resize-dot-right{right:6px;top:50%;transform:translateY(-50%);cursor:ew-resize}
.carousel-resize-dot-bl{left:6px;bottom:6px;cursor:nesw-resize}
.carousel-resize-dot-b{left:50%;bottom:6px;transform:translateX(-50%);cursor:ns-resize}
.carousel-resize-dot-br{right:6px;bottom:6px;cursor:nwse-resize}
.el{border:0 !important;border-style:none !important;border-width:0 !important;border-radius:0 !important;padding:0;background:transparent;margin-bottom:0;min-width:0;overflow-wrap:break-word;word-break:break-word;cursor:move;position:absolute;box-sizing:border-box;pointer-events:none}
.el>*{pointer-events:auto}
.el--editing [contenteditable='true']{cursor:text!important}
.el input,.el textarea,.el select{cursor:auto}
.el:hover:not(.sel):not(.el--dragging){outline:1px solid #E7D8F0;outline-offset:0}
.el.sel{outline:2px solid #240E35;outline-offset:0;pointer-events:auto;background:rgba(255,255,255,0.02)}
.el.el--abs{margin-bottom:0 !important}
.el.el--dragging{opacity:.85;z-index:9999 !important;box-shadow:0 8px 32px rgba(37,99,235,.18);pointer-events:none}
.el-rh{position:absolute;pointer-events:auto;z-index:100;box-sizing:border-box;display:none}
.el.sel .el-rh{display:block}
.el-rh-corner{width:10px;height:10px;border-radius:50%;background:#fff;border:2px solid #240E35;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.el-rh-side{background:#fff;border:2px solid #240E35;border-radius:3px;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.el-rh-nw{left:-5px;top:-5px;cursor:nwse-resize}
.el-rh-ne{right:-5px;top:-5px;cursor:nesw-resize}
.el-rh-sw{left:-5px;bottom:-5px;cursor:nesw-resize}
.el-rh-se{right:-5px;bottom:-5px;cursor:nwse-resize}
.el-rh-n{left:50%;top:-4px;transform:translateX(-50%);width:20px;height:8px;cursor:ns-resize}
.el-rh-s{left:50%;bottom:-4px;transform:translateX(-50%);width:20px;height:8px;cursor:ns-resize}
.el-rh-w{left:-4px;top:50%;transform:translateY(-50%);width:8px;height:20px;cursor:ew-resize}
.el-rh-e{right:-4px;top:50%;transform:translateY(-50%);width:8px;height:20px;cursor:ew-resize}
.fb-snap-guide{position:absolute;pointer-events:none;z-index:90;display:none}
.fb-snap-guide-v{top:0;bottom:0;width:1px;background:#6B4A7A;box-shadow:0 0 0 1px rgba(168,85,247,.18)}
.fb-snap-guide-h{left:0;right:0;height:1px;background:#6B4A7A;box-shadow:0 0 0 1px rgba(168,85,247,.18)}
.fb-snap-label{position:absolute;background:#6B4A7A;color:#fff;font-size:10px;font-weight:700;padding:1px 5px;border-radius:3px;white-space:nowrap;pointer-events:none;z-index:91}
.el.el--carousel{border:0 !important;background:transparent !important;padding:0 !important}
.el.el--form{border:0 !important;background:transparent !important;padding:0 !important}
#canvas .el.el--menu ul{flex-wrap:nowrap !important;white-space:nowrap}
#canvas.canvas-outline-mode .sec,#canvas.canvas-outline-mode .row,#canvas.canvas-outline-mode .col,#canvas.canvas-outline-mode .el{position:relative;background:transparent;border:1px dashed #E7D8F0 !important;border-radius:0;box-shadow:none !important}
#canvas.canvas-outline-mode .sec.sec--bare-wrap,#canvas.canvas-outline-mode .sec.sec--bare-carousel{border:0 !important;background:transparent !important;padding:0 !important;margin-bottom:0 !important}
#canvas.canvas-outline-mode .sec.sec--freeform-canvas{border:0 !important;background:transparent !important;padding:0 !important;margin:0 !important}
#canvas.canvas-outline-mode .sec{padding:5px !important;margin-bottom:6px}
#canvas.canvas-outline-mode .row{padding:4px !important}
#canvas.canvas-outline-mode .col{padding:4px !important;min-height:72px;overflow:visible !important}
#canvas.canvas-outline-mode .col-inner{overflow:visible !important}
#canvas.canvas-outline-mode .el{padding:2px !important;margin-bottom:3px;min-height:26px;overflow:visible !important}
#canvas.canvas-outline-mode .sec::before,#canvas.canvas-outline-mode .row::before,#canvas.canvas-outline-mode .col::before,#canvas.canvas-outline-mode .el::before{content:attr(data-outline-label);position:absolute;left:7px;top:0;transform:translateY(-100%);display:none;background:#E7D8F0;color:#2E1244;border:1px solid #E7D8F0;border-bottom:0;font-size:10px;font-weight:800;letter-spacing:.02em;text-transform:uppercase;line-height:1;padding:3px 6px;white-space:nowrap;pointer-events:none;z-index:6}
#canvas.canvas-outline-mode .sec.sec--bare-wrap::before,#canvas.canvas-outline-mode .sec.sec--bare-carousel::before{display:none !important;content:""}
#canvas.canvas-outline-mode .sec.fb-outline-target,#canvas.canvas-outline-mode .row.fb-outline-target,#canvas.canvas-outline-mode .col.fb-outline-target,#canvas.canvas-outline-mode .el.fb-outline-target{border-color:#9E7BB5 !important;border-width:2px !important}
#canvas.canvas-outline-mode .sec.fb-outline-target::before,#canvas.canvas-outline-mode .row.fb-outline-target::before,#canvas.canvas-outline-mode .col.fb-outline-target::before,#canvas.canvas-outline-mode .el.fb-outline-target::before{display:inline-flex;align-items:center}
#canvas.canvas-outline-mode .sec.sel,#canvas.canvas-outline-mode .row.sel,#canvas.canvas-outline-mode .col.sel,#canvas.canvas-outline-mode .el.sel{border-color:#9E7BB5 !important;border-width:2px !important;outline:none !important}
#canvas.canvas-outline-mode:not(.fb-outline-has-target) .sec.sel::before,#canvas.canvas-outline-mode:not(.fb-outline-has-target) .row.sel::before,#canvas.canvas-outline-mode:not(.fb-outline-has-target) .col.sel::before,#canvas.canvas-outline-mode:not(.fb-outline-has-target) .el.sel::before{display:inline-flex;align-items:center}
.el h2,.el p,.el button{overflow-wrap:break-word;word-break:break-word;min-width:0;max-width:100%}
@keyframes el-spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
.el-loading-overlay{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(248,250,252,0.85);z-index:10;border-radius:inherit;pointer-events:none}
.el-loading-spinner{width:28px;height:28px;border:3px solid #E6E1EF;border-top-color:#240E35;border-radius:50%;animation:el-spin .7s linear infinite}
#canvas .el.el--button:not(.el--abs){width:100%;box-sizing:border-box}
#canvas .el.el--button>button{display:inline-flex;width:auto;align-items:center;justify-content:center}
#canvas .el.el--button-fill:not(.el--abs){display:flex;width:100%;box-sizing:border-box}
#canvas .el.el--button-fill>button{display:flex;width:100%;align-items:center;justify-content:center;box-sizing:border-box}
.sec.sel,.row.sel,.col.sel{outline:2px solid #240E35;outline-offset:2px}
.settings label{font-size:12px;font-weight:800;color:#2E1244;display:block;margin:0 0 4px}
.settings input,.settings select,.settings textarea{width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px}
.settings input[type="checkbox"]{width:auto;padding:0;margin:0;border:1px solid #E6E1EF;border-radius:4px}
.settings label.inline-check{display:flex;align-items:center;gap:8px;font-weight:700;color:#240E35;margin:0 0 10px}
.settings .meta{font-size:12px;color:#475569;font-weight:700;margin-bottom:8px}
.settings-delete-wrap{margin-top:14px;padding-top:12px;border-top:1px solid #E6E1EF}
.settings-delete-wrap .fb-btn{width:100%;justify-content:center;gap:6px}
.px-wrap{display:flex;align-items:center;gap:6px;margin-bottom:8px}
.px-wrap input[type="number"]{margin-bottom:0}
.px-unit{font-size:12px;font-weight:800;color:#334155;min-width:18px}
.size-position{margin-bottom:12px}
.size-position .size-label{font-size:12px;font-weight:800;color:#2E1244;display:block;margin:0 0 6px}
.size-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;margin-bottom:8px;align-items:center}
.size-grid .fld{display:flex;align-items:center;gap:4px}
.size-grid .fld label{font-size:11px;color:#64748b;min-width:18px}
.size-grid .fld input{width:100%;padding:6px 8px;border:1px solid #E6E1EF;border-radius:6px}
.size-link{grid-column:1/-1;display:flex;align-items:center;gap:6px;margin-top:2px}
.size-link button{width:34px;height:34px;padding:0;border:1px solid #E6E1EF;border-radius:8px;background:#F3EEF7;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;color:#64748b}
.size-link button.linked{background:#e0e7ff;border-color:#6366f1}
.size-link button:hover{background:#E6E1EF}
.size-link span{font-size:12px;color:#64748b}
.row-border-card{border:1px solid #E6E1EF;border-radius:10px;padding:10px;margin:10px 0;background:#fff}
.row-border-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.row-border-head strong{font-size:13px;color:#240E35}
.row-border-head button{border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:4px 8px;font-size:12px;color:#475569;cursor:pointer}
.row-radius-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.row-radius-field{display:flex;align-items:center;gap:6px}
.row-radius-field span{font-size:11px;color:#64748b;min-width:16px}
.row-radius-field input{margin-bottom:0}
.img-radius-panel{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.img-radius-link{width:34px;height:34px;border:1px solid #E6E1EF;border-radius:8px;background:#F3EEF7;color:#64748b;display:flex;align-items:center;justify-content:center;cursor:pointer}
.img-radius-link.linked{border-color:#6B4A7A;background:#E7D8F0;color:#2E1244}
.img-radius-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px;flex:1}
.img-radius-row input{margin-bottom:0;text-align:center}
.col-layout-wrap{border:1px solid #E6E1EF;border-radius:10px;padding:10px;margin:10px 0;background:#fff}
.col-layout-title{font-size:13px;font-weight:800;color:#240E35;margin:0 0 8px}
.col-layout-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
.col-layout-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:10px 6px;border:1px solid #E6E1EF;border-radius:10px;background:#F3EEF7;color:#334155;font-weight:700;cursor:pointer}
.col-layout-btn i{font-size:13px;color:#64748b}
.col-layout-btn.active{border-color:#6B4A7A;background:#E7D8F0;color:#2E1244}
.col-layout-btn:hover{background:#F3EEF7}
.menu-panel-title{font-size:16px;font-weight:900;color:#240E35;margin:2px 0 10px}
.menu-section-title{font-size:13px;font-weight:900;color:#240E35;margin:8px 0}
.menu-item-card{border:1px solid #E6E1EF;border-radius:10px;padding:10px;background:#fff;margin-bottom:10px}
.menu-item-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.menu-item-head strong{font-size:13px;color:#240E35}
.menu-item-actions{display:flex;align-items:center;gap:6px}
.menu-item-actions button{border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:4px 8px;color:#64748b;cursor:pointer}
.menu-item-actions .menu-del{color:#ef4444}
.menu-item-card label{display:flex;align-items:center;gap:8px;margin:0 0 8px;font-weight:700}
.menu-item-card label input[type="checkbox"]{width:auto;margin:0}
.menu-split{height:1px;background:#E6E1EF;margin:12px 0}
.menu-typo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px}
.menu-align-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;margin-bottom:8px}
.menu-align-btn{border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:7px 8px;color:#64748b;cursor:pointer}
.menu-align-btn.active{border-color:#6B4A7A;background:#E7D8F0;color:#2E1244}
.menu-style-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:6px;margin-bottom:8px}
.menu-slider-row{display:grid;grid-template-columns:1fr 96px;gap:8px;align-items:center;margin-bottom:8px}
.menu-slider-row input{margin-bottom:0}
#canvas .fb-form-input::placeholder{color:var(--fb-ph-color,#94a3b8);opacity:1}
.carousel-slide-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.carousel-slide-btn{flex:1;border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:8px 10px;text-align:left;font-weight:700;color:#334155;cursor:pointer}
.carousel-slide-btn.active{background:#6B4A7A;border-color:#0284c7;color:#fff}
.carousel-icon-btn{width:34px;height:34px;border:1px solid #E6E1EF;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center}
.carousel-icon-btn.active{border-color:#6B4A7A;background:#E7D8F0;color:#2E1244}
.carousel-icon-btn.danger{color:#ef4444}
.carousel-comp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-bottom:10px}
.carousel-comp-btn{border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:8px 10px;color:#334155;font-weight:700;cursor:pointer}
.carousel-comp-btn:hover{background:#F3EEF7}
.carousel-comp-btn[draggable="true"]{cursor:grab}
.carousel-group-title{font-size:11px;font-weight:900;color:#475569;letter-spacing:.02em;text-transform:uppercase;margin:6px 0}
.rt-box{border:1px solid #E6E1EF;border-radius:8px;background:#fff;margin-bottom:8px}
.rt-tools{display:flex;gap:6px;padding:6px;border-bottom:1px solid #E6E1EF}
.rt-tools button{padding:5px 8px;border:1px solid #E6E1EF;border-radius:6px;background:#F3EEF7;font-weight:800;cursor:pointer}
.rt-editor{min-height:90px;padding:8px;outline:none}
.icon-preview-box{display:flex;align-items:center;justify-content:center;min-height:68px;border:1px dashed #E6E1EF;border-radius:10px;background:#F3EEF7;margin-bottom:8px}
.icon-picker-btn{width:100%;margin-bottom:8px}
.icon-picker-modal{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:1400;padding:14px}
.icon-picker-modal.open{display:flex}
.icon-picker-card{width:min(760px,96vw);max-height:86vh;overflow:hidden;display:flex;flex-direction:column;background:#fff;border:1px solid #E6E1EF;border-radius:12px;box-shadow:0 16px 42px rgba(30,64,175,.22)}
.icon-picker-head{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:12px;border-bottom:1px solid #E6E1EF}
.icon-picker-head h4{margin:0;font-size:14px;color:#240E35}
.icon-picker-close{border:1px solid #E6E1EF;background:#F3EEF7;border-radius:8px;padding:6px 10px;cursor:pointer}
.icon-picker-tools{display:grid;grid-template-columns:1fr 170px;gap:8px;padding:10px 12px;border-bottom:1px solid #E6E1EF}
.icon-picker-tools input,.icon-picker-tools select{margin:0}
.icon-picker-grid{padding:10px 12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(94px,1fr));gap:8px;overflow:auto}
.icon-picker-item{border:1px solid #E7D8F0;background:#fff;border-radius:10px;min-height:72px;padding:6px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;cursor:pointer}
.icon-picker-item i{font-size:19px;color:#240E35}
.icon-picker-item span{font-size:11px;color:#334155;text-align:center;line-height:1.2}
.icon-picker-item:hover{background:#F3EEF7;border-color:#E7D8F0}
.icon-picker-empty{padding:16px;color:#64748b;font-size:12px}
.page-mgr-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1450;padding:18px}
.page-mgr-modal.open{display:flex}
.page-mgr-card{width:min(980px,96vw);max-height:90vh;overflow:hidden;display:flex;flex-direction:column;background:#fff;border:1px solid #E7D8F0;border-radius:18px;box-shadow:0 24px 56px rgba(15,23,42,.34)}
.page-mgr-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:18px 20px;border-bottom:1px solid #E6E1EF;background:linear-gradient(180deg,#ffffff,#F3EEF7)}
.page-mgr-head h4{margin:0;font-size:21px;line-height:1.2;font-weight:900;color:#240E35;letter-spacing:-.01em}
.page-mgr-close{width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #E6E1EF;background:#F3EEF7;border-radius:12px;padding:0;font-weight:800;color:#334155;cursor:pointer}
.page-mgr-close:hover{background:#F3EEF7;border-color:#E7D8F0;color:#240E35}
.page-mgr-body{padding:16px;display:grid;grid-template-columns:1fr 1fr;gap:14px;overflow:auto;background:#F3EEF7}
.page-mgr-col{border:1px solid #E7D8F0;border-radius:14px;background:#ffffff;padding:14px}
.page-mgr-col h5{margin:0 0 10px;font-size:16px;font-weight:900;color:#2E1244;line-height:1.2}
.page-mgr-list{width:100%;height:350px;border:1px solid #E6E1EF;border-radius:12px;background:#fff;padding:8px;box-shadow:inset 0 1px 0 rgba(255,255,255,.8);overflow:auto}
.page-mgr-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border:1px solid transparent;border-radius:10px;font-size:14px;color:#240E35;cursor:pointer;user-select:none}
.page-mgr-item + .page-mgr-item{margin-top:4px}
.page-mgr-item:hover{background:#F3EEF7}
.page-mgr-item.is-selected{background:#240E35;color:#fff}
.page-mgr-item.is-dragging{opacity:.55}
.page-mgr-item.drag-before{box-shadow:inset 0 2px 0 #2E1244}
.page-mgr-item.drag-after{box-shadow:inset 0 -2px 0 #2E1244}
.page-mgr-item-handle{font-size:12px;color:#64748b}
.page-mgr-item.is-selected .page-mgr-item-handle{color:rgba(255,255,255,.85)}
.page-mgr-section{margin-bottom:14px;padding:12px;border:1px solid #E6E1EF;border-radius:12px;background:#F3EEF7}
.page-mgr-section:last-child{margin-bottom:0}
.page-mgr-section label{display:block;margin:0 0 6px;font-size:13px;font-weight:800;color:#334155}
.page-mgr-section input,.page-mgr-section select{width:100%;padding:11px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff;font-size:14px;color:#240E35;margin-bottom:10px}
.page-mgr-section input:focus,.page-mgr-section select:focus{outline:none;border-color:#6B4A7A;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.page-mgr-create-btn{width:100%;margin-top:2px}
.page-mgr-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.page-mgr-actions .fb-btn{min-height:40px}
.page-mgr-note{font-size:12px;color:#475569;font-weight:700;margin-top:8px}
@media (max-width: 860px){
    .page-mgr-body{grid-template-columns:1fr}
    .page-mgr-head h4{font-size:20px}
}
.setting-label-help{display:flex;align-items:center;gap:8px;margin:0 0 6px}
.setting-help-icon{width:22px;height:22px;border-radius:999px;border:1px solid #E7D8F0;background:#eaf2ff;color:#240E35;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;line-height:1;cursor:pointer;padding:0}
.setting-help-icon:hover{background:#E7D8F0}
.fb-help-modal{position:fixed;inset:0;background:rgba(37,99,235,.16);display:none;align-items:center;justify-content:center;z-index:1200;padding:16px}
.fb-help-modal.open{display:flex}
.fb-help-card{width:min(560px,92vw);background:#ffffff;color:#334155;border:1px solid #E7D8F0;border-radius:14px;box-shadow:0 18px 44px rgba(30,64,175,.18);padding:16px;position:relative}
.fb-help-close{position:absolute;top:8px;right:8px;border:1px solid #E6E1EF;background:#F3EEF7;color:#2E1244;width:28px;height:28px;border-radius:8px;cursor:pointer}
.fb-help-close:hover{background:#E7D8F0}
.fb-help-title{margin:0 0 8px;font-size:16px;font-weight:900;color:#240E35}
.fb-help-text{font-size:13px;line-height:1.55;color:#334155;margin-bottom:10px}
.fb-radius-demo{height:92px;border:1px solid #E6E1EF;border-radius:8px;background:linear-gradient(180deg,#F3EEF7,#eaf2ff);display:flex;align-items:center;justify-content:center}
.fb-radius-demo-box{width:120px;height:56px;background:#240E35;animation:radiusMorph 2.2s ease-in-out infinite}
.fb-dim-tip{position:fixed;left:0;top:0;transform:translate(12px,12px);z-index:1300;pointer-events:none;display:none;padding:5px 8px;border:1px solid #E7D8F0;border-radius:8px;background:#F3EEF7;color:#2E1244;font-size:11px;font-weight:800;line-height:1.2;white-space:nowrap;box-shadow:0 6px 18px rgba(30,64,175,.18)}
.fb-ctx-menu{position:fixed;z-index:1500;min-width:150px;background:#ffffff;border:1px solid #E6E1EF;border-radius:10px;box-shadow:0 12px 30px rgba(30,64,175,.22);padding:6px;display:none}
.fb-ctx-item{width:100%;text-align:left;border:1px solid transparent;background:#fff;border-radius:8px;padding:8px 10px;color:#240E35;font-weight:700;cursor:pointer}
.fb-ctx-item:hover{background:#F3EEF7;border-color:#E6E1EF}
.fb-ctx-item[disabled]{opacity:.45;cursor:not-allowed}
.fb-space-demo{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.fb-space-box{height:90px;border:1px solid #E6E1EF;border-radius:10px;background:#F3EEF7;position:relative;overflow:hidden}
.fb-space-pad{position:absolute;inset:12px;border:2px dashed #240E35;animation:paddingPulse 1.8s ease-in-out infinite}
.fb-space-mar{position:absolute;inset:18px;background:#E7D8F0;border-radius:8px;animation:marginPulse 1.8s ease-in-out infinite}
.fb-drop-guide-v,.fb-drop-guide-h{position:absolute;pointer-events:none;z-index:60;display:none;background:#6B4A7A;opacity:.85}
.fb-drop-guide-v{top:0;bottom:0;width:1px;box-shadow:0 0 0 1px rgba(168,85,247,.15)}
.fb-drop-guide-h{left:0;right:0;height:1px;box-shadow:0 0 0 1px rgba(168,85,247,.15)}
@keyframes paddingPulse{
    0%{inset:12px}
    50%{inset:20px}
    100%{inset:12px}
}
@keyframes marginPulse{
    0%{transform:scale(1)}
    50%{transform:scale(.82)}
    100%{transform:scale(1)}
}
@keyframes radiusMorph{
    0%{border-radius:0}
    50%{border-radius:18px}
    100%{border-radius:999px}
}
@media(max-width:1080px){.fb-grid,.fb-grid.components-hidden{grid-template-columns:1fr}.fb-components-col .fb-panel-toggle{display:none!important}.fb-grid.components-hidden .fb-components-col .fb-left-panel{display:block!important}.fb-left-panel.hidden{display:none!important}}
</style>

<div class="fb-top">
    <div><strong>{{ $funnel->name }}</strong> <span style="font-size:12px;opacity:.9;">({{ ucfirst($funnel->status) }})</span></div>
    <div class="fb-actions">
        <form method="POST" action="{{ route('funnels.update', $funnel) }}" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $funnel->name }}">
            <input type="hidden" name="description" value="{{ $funnel->description }}">
            <input type="hidden" name="status" value="{{ $funnel->status }}">
            <input
                type="text"
                name="default_tags"
                value="{{ old('default_tags', implode(', ', $funnel->default_tags ?? [])) }}"
                placeholder="Funnel tags: e.g. webinar, q1-campaign"
                style="min-width:280px;padding:6px 8px;border:1px solid #E6E1EF;border-radius:8px;font-size:12px;"
            >
            <button class="fb-btn" type="submit"><i class="fas fa-tags"></i> Save Tags</button>
        </form>
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
                    <button draggable="true" data-c="icon"><i class="fas fa-icons"></i>Icon</button>
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
    </div>

    <div class="fb-canvas-col">
    <div class="fb-card">
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
            <label for="stepSel" style="font-weight:800;">Step</label>
            <select id="stepSel"></select>
            <button id="stepAddBtn" type="button" class="fb-btn" style="padding:6px 10px;min-height:32px;">+ Add Page</button>
            <label for="canvasBgColor" style="font-weight:800;">Canvas BG</label>
            <input id="canvasBgColor" type="color" value="#F3EEF7" title="Canvas background color">
            <button id="canvasBgReset" type="button" class="fb-btn" style="padding:6px 10px;min-height:32px;">Reset BG</button>
            <span id="saveMsg" style="font-size:12px;color:#475569;font-weight:700;">Not saved yet</span>
        </div>
        <div id="canvas"></div>
    </div>
    </div>
</div>
<div class="page-mgr-modal" id="pageMgrModal" aria-hidden="true">
    <div class="page-mgr-card" role="dialog" aria-modal="true" aria-labelledby="pageMgrTitle">
        <div class="page-mgr-head">
            <h4 id="pageMgrTitle">Page Manager</h4>
            <button type="button" class="page-mgr-close" id="pageMgrClose" aria-label="Close">X</button>
        </div>
        <div class="page-mgr-body">
            <div class="page-mgr-col">
                <h5>Pages</h5>
                <div id="pageMgrList" class="page-mgr-list" role="listbox" aria-label="Pages"></div>
                <div class="page-mgr-note">Select a page to manage it.</div>
            </div>
            <div class="page-mgr-col">
                <div class="page-mgr-section">
                    <h5>Add Page</h5>
                    <label for="pageMgrAddType">Type</label>
                    <select id="pageMgrAddType">
                        <option value="landing">Landing</option>
                        <option value="opt_in">Opt-in</option>
                        <option value="sales">Sales</option>
                        <option value="checkout">Checkout</option>
                        <option value="thank_you">Thank You</option>
                        <option value="custom">Custom</option>
                    </select>
                    <label for="pageMgrAddTitle">Page title</label>
                    <input id="pageMgrAddTitle" type="text" placeholder="e.g. About Offer">
                    <label for="pageMgrAddSlug">Page slug (optional)</label>
                    <input id="pageMgrAddSlug" type="text" placeholder="e.g. about-offer">
                    <button id="pageMgrCreateBtn" type="button" class="fb-btn primary page-mgr-create-btn">Create Page</button>
                </div>
                <div class="page-mgr-section">
                    <h5>Manage Selected Page</h5>
                    <label for="pageMgrRenameTitle">Title</label>
                    <input id="pageMgrRenameTitle" type="text" placeholder="Selected page title">
                    <label for="pageMgrRenameSlug">Slug</label>
                    <input id="pageMgrRenameSlug" type="text" placeholder="selected-page-slug">
                    <label for="pageMgrRenameTags">Step tags (comma separated)</label>
                    <input id="pageMgrRenameTags" type="text" placeholder="e.g. opt-in, warm-lead">
                    <div class="page-mgr-actions">
                        <button id="pageMgrRenameBtn" type="button" class="fb-btn">Rename</button>
                        <button id="pageMgrDeleteBtn" type="button" class="fb-btn danger">Delete</button>
                        <button id="pageMgrUpBtn" type="button" class="fb-btn">Up</button>
                        <button id="pageMgrDownBtn" type="button" class="fb-btn">Down</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $builderSteps = $funnel->steps->sortBy('position')->values()->map(function ($step) {
        return [
            'id' => $step->id,
            'title' => $step->title,
            'slug' => $step->slug,
            'type' => $step->type,
            'layout_json' => $step->layout_json,
            'background_color' => $step->background_color,
            'position' => $step->position,
            'is_active' => (bool) $step->is_active,
            'layout_style' => $step->layout_style,
            'template' => $step->template,
            'subtitle' => $step->subtitle,
            'content' => $step->content,
            'cta_label' => $step->cta_label,
            'price' => $step->price,
            'step_tags' => $step->step_tags ?? [],
        ];
    })->all();
@endphp
<script>
(function(){
const saveUrl="{{ route('funnels.builder.layout.save',$funnel) }}";
const uploadUrl="{{ route('funnels.builder.image.upload',$funnel) }}";
const previewTpl="{{ route('funnels.preview',['funnel'=>$funnel,'step'=>'__STEP__']) }}";
const stepStoreUrl="{{ route('funnels.steps.store',$funnel) }}";
const stepUpdateTpl="{{ route('funnels.steps.update',['funnel'=>$funnel,'step'=>'__STEP__']) }}";
const stepDeleteTpl="{{ route('funnels.steps.destroy',['funnel'=>$funnel,'step'=>'__STEP__']) }}";
const stepReorderUrl="{{ route('funnels.steps.reorder',$funnel) }}";
const csrf="{{ csrf_token() }}";
const funnelSlug=@json($funnel->slug);
const steps=@json($builderSteps);
const state={sid:{{ (int)($defaultStepId??0) }}||((steps[0]&&steps[0].id)||null),layout:null,sel:null,carouselSel:null,clipboard:null,editingEl:null,mediaLoading:new Set()};
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
const iconCatalog=[
    {name:"house",label:"House",keywords:"home main",styles:["solid"]},
    {name:"building",label:"Building",keywords:"office",styles:["solid","regular"]},
    {name:"user",label:"User",keywords:"profile account",styles:["solid","regular"]},
    {name:"users",label:"Users",keywords:"team group",styles:["solid"]},
    {name:"user-plus",label:"User Plus",keywords:"signup add",styles:["solid"]},
    {name:"envelope",label:"Envelope",keywords:"mail email",styles:["solid","regular"]},
    {name:"phone",label:"Phone",keywords:"call contact",styles:["solid"]},
    {name:"location-dot",label:"Location",keywords:"map pin",styles:["solid"]},
    {name:"calendar-days",label:"Calendar",keywords:"date schedule",styles:["solid","regular"]},
    {name:"clock",label:"Clock",keywords:"time",styles:["solid","regular"]},
    {name:"star",label:"Star",keywords:"favorite rate",styles:["solid","regular"]},
    {name:"heart",label:"Heart",keywords:"like love",styles:["solid","regular"]},
    {name:"check",label:"Check",keywords:"success done",styles:["solid"]},
    {name:"check-double",label:"Check Double",keywords:"verified",styles:["solid"]},
    {name:"xmark",label:"X Mark",keywords:"close cancel",styles:["solid"]},
    {name:"circle-check",label:"Circle Check",keywords:"success ok",styles:["solid","regular"]},
    {name:"circle-xmark",label:"Circle X",keywords:"error close",styles:["solid","regular"]},
    {name:"circle-info",label:"Circle Info",keywords:"help info",styles:["solid"]},
    {name:"triangle-exclamation",label:"Warning",keywords:"alert caution",styles:["solid"]},
    {name:"bolt",label:"Bolt",keywords:"fast lightning",styles:["solid"]},
    {name:"fire",label:"Fire",keywords:"hot",styles:["solid"]},
    {name:"gift",label:"Gift",keywords:"promo offer",styles:["solid"]},
    {name:"tag",label:"Tag",keywords:"price label",styles:["solid"]},
    {name:"cart-shopping",label:"Cart",keywords:"shop checkout",styles:["solid"]},
    {name:"credit-card",label:"Card",keywords:"payment",styles:["solid","regular"]},
    {name:"lock",label:"Lock",keywords:"secure security",styles:["solid"]},
    {name:"unlock",label:"Unlock",keywords:"open",styles:["solid"]},
    {name:"shield-halved",label:"Shield",keywords:"protect security",styles:["solid"]},
    {name:"thumbs-up",label:"Thumbs Up",keywords:"like",styles:["solid","regular"]},
    {name:"thumbs-down",label:"Thumbs Down",keywords:"dislike",styles:["solid","regular"]},
    {name:"comment",label:"Comment",keywords:"message chat",styles:["solid","regular"]},
    {name:"comments",label:"Comments",keywords:"chat talk",styles:["solid","regular"]},
    {name:"paper-plane",label:"Paper Plane",keywords:"send submit",styles:["solid","regular"]},
    {name:"image",label:"Image",keywords:"photo media",styles:["solid","regular"]},
    {name:"video",label:"Video",keywords:"media play",styles:["solid"]},
    {name:"play",label:"Play",keywords:"start media",styles:["solid"]},
    {name:"pause",label:"Pause",keywords:"media stop",styles:["solid"]},
    {name:"arrow-right",label:"Arrow Right",keywords:"next",styles:["solid"]},
    {name:"arrow-left",label:"Arrow Left",keywords:"back",styles:["solid"]},
    {name:"arrow-up-right-from-square",label:"External",keywords:"link open",styles:["solid"]},
    {name:"magnifying-glass",label:"Search",keywords:"find",styles:["solid"]},
    {name:"gear",label:"Gear",keywords:"settings config",styles:["solid"]},
    {name:"sliders",label:"Sliders",keywords:"controls",styles:["solid"]},
    {name:"bars",label:"Bars",keywords:"menu",styles:["solid"]},
    {name:"globe",label:"Globe",keywords:"web world",styles:["solid"]},
    {name:"download",label:"Download",keywords:"save",styles:["solid"]},
    {name:"upload",label:"Upload",keywords:"send",styles:["solid"]},
    {name:"circle-plus",label:"Circle Plus",keywords:"add create",styles:["solid","regular"]},
    {name:"circle-minus",label:"Circle Minus",keywords:"remove",styles:["solid","regular"]},
    {name:"facebook-f",label:"Facebook",keywords:"social",styles:["brands"]},
    {name:"instagram",label:"Instagram",keywords:"social",styles:["brands"]},
    {name:"tiktok",label:"TikTok",keywords:"social",styles:["brands"]},
    {name:"youtube",label:"YouTube",keywords:"social video",styles:["brands"]},
    {name:"x-twitter",label:"X",keywords:"social twitter",styles:["brands"]},
    {name:"linkedin-in",label:"LinkedIn",keywords:"social",styles:["brands"]},
];

const stepSel=document.getElementById("stepSel"),stepAddBtn=document.getElementById("stepAddBtn"),pageMgrModal=document.getElementById("pageMgrModal"),pageMgrClose=document.getElementById("pageMgrClose"),pageMgrList=document.getElementById("pageMgrList"),pageMgrAddType=document.getElementById("pageMgrAddType"),pageMgrAddTitle=document.getElementById("pageMgrAddTitle"),pageMgrAddSlug=document.getElementById("pageMgrAddSlug"),pageMgrCreateBtn=document.getElementById("pageMgrCreateBtn"),pageMgrRenameTitle=document.getElementById("pageMgrRenameTitle"),pageMgrRenameSlug=document.getElementById("pageMgrRenameSlug"),pageMgrRenameTags=document.getElementById("pageMgrRenameTags"),pageMgrRenameBtn=document.getElementById("pageMgrRenameBtn"),pageMgrDeleteBtn=document.getElementById("pageMgrDeleteBtn"),pageMgrUpBtn=document.getElementById("pageMgrUpBtn"),pageMgrDownBtn=document.getElementById("pageMgrDownBtn"),canvas=document.getElementById("canvas"),settings=document.getElementById("settings"),saveMsg=document.getElementById("saveMsg"),settingsTitle=document.getElementById("settingsTitle"),canvasBgColor=document.getElementById("canvasBgColor"),canvasBgReset=document.getElementById("canvasBgReset");
let _autoSaveTimer=null;
let _autoSaveInFlight=false;
let _autoSavePending=false;
let _autoSaveMode=false;
let _autoSavePromise=null;
let _autoSavePaused=false;
const AUTO_SAVE_DELAY=1000;
function queueAutoSave(){
    if(_autoSavePaused){_autoSavePending=true;return;}
    if(_autoSaveTimer)clearTimeout(_autoSaveTimer);
    _autoSaveTimer=setTimeout(runAutoSave,AUTO_SAVE_DELAY);
}
function runAutoSave(){
    _autoSaveTimer=null;
    if(_autoSaveInFlight){_autoSavePending=true;return;}
    _autoSaveInFlight=true;
    _autoSaveMode=true;
    var p=persistCurrentStep();
    if(!p||typeof p.then!=="function"){
        _autoSaveMode=false;
        _autoSaveInFlight=false;
        return;
    }
    _autoSavePromise=p;
    p.catch(()=>{saveMsg.textContent="Auto-save failed";})
        .finally(()=>{
            _autoSaveMode=false;
            _autoSaveInFlight=false;
            _autoSavePromise=null;
            if(_autoSavePending){_autoSavePending=false;queueAutoSave();}
        });
}
function flushAutoSave(){
    if(_autoSaveTimer){clearTimeout(_autoSaveTimer);_autoSaveTimer=null;runAutoSave();}
    if(_autoSavePromise&&typeof _autoSavePromise.then==="function")return _autoSavePromise;
    if(_autoSaveInFlight)return new Promise(resolve=>setTimeout(resolve,50)).then(flushAutoSave);
    return Promise.resolve();
}
let pageMgrDragId=null;
if(canvas)canvas.classList.add("canvas-outline-mode");
if(!steps.length){canvas.textContent="No steps found.";return;}
function sortStepsByPosition(){
    steps.sort(function(a,b){
        var ap=Number(a&&a.position)||0;
        var bp=Number(b&&b.position)||0;
        if(ap!==bp)return ap-bp;
        return (Number(a&&a.id)||0)-(Number(b&&b.id)||0);
    });
}
function renderStepOptions(){
    if(!stepSel)return;
    sortStepsByPosition();
    stepSel.innerHTML="";
    steps.forEach(function(s){
        var o=document.createElement("option");
        o.value=String(s.id);
        o.textContent=String(s.title||("Step "+String(s.id)));
        stepSel.appendChild(o);
    });
    var hasCurrent=steps.some(function(s){return +s.id===+state.sid;});
    if(!hasCurrent && steps.length)state.sid=steps[0].id;
    stepSel.value=String(state.sid||"");
}
renderStepOptions();
if(canvasBgColor){
    canvasBgColor.addEventListener("input",()=>{
        if(!state.layout)return;
        saveToHistory();
        propagateCanvasBgToAllSteps(canvasBgColor.value);
        applyCanvasBgPreference();
        syncCanvasBgControls();
    });
}
if(canvasBgReset){
    canvasBgReset.addEventListener("click",()=>{
        if(!state.layout)return;
        saveToHistory();
        propagateCanvasBgToAllSteps(null);
        applyCanvasBgPreference();
        syncCanvasBgControls();
    });
}

const uid=p=>p+"_"+Math.random().toString(36).slice(2,10),clone=o=>JSON.parse(JSON.stringify(o));
const defaults=(stepType)=>{
    const t=String(stepType||"").toLowerCase();
    if(t==="landing"||t==="opt_in"||t==="sales"||t==="checkout"||t==="thank_you"){
        return {root:[],sections:[]};
    }
    return {
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
                        style:{fontSize:"32px",color:"#240E35"},
                        settings:{}
                    }]
                }]
            }]
        }],
        sections:[]
    };
};
const cur=()=>steps.find(s=>+s.id===+state.sid);
function escapeRegExp(v){return String(v||"").replace(/[.*+?^${}()|[\]\\]/g,"\\$&");}
function slugifyPage(v){
    var s=String(v||"").toLowerCase().trim().replace(/[^a-z0-9]+/g,"-").replace(/^-+|-+$/g,"");
    return s||"page";
}
function normalizeTagArray(input){
    var list=Array.isArray(input)?input:String(input||"").split(",");
    var seen={};
    var out=[];
    list.forEach(function(item){
        var t=String(item||"").toLowerCase().trim().replace(/[^a-z0-9\-_ ]/g,"");
        if(!t)return;
        t=t.slice(0,40);
        if(seen[t])return;
        seen[t]=true;
        out.push(t);
    });
    return out.slice(0,30);
}
function pageTypeLabel(v){
    var t=String(v||"").toLowerCase();
    if(t==="opt_in")return "Opt-in";
    if(t==="thank_you")return "Thank You";
    if(t==="checkout")return "Checkout";
    if(t==="sales")return "Sales";
    if(t==="landing")return "Landing";
    if(t==="custom")return "Custom";
    return titleCase(t);
}
function stepUrlFromTpl(tpl,id){return String(tpl||"").replace("__STEP__",String(id));}
function asFormUrlEncoded(obj){
    var body=new URLSearchParams();
    function appendValue(key,val){
        if(Array.isArray(val)){
            val.forEach(function(item){
                appendValue(key+"[]",item);
            });
            return;
        }
        if(val&&typeof val==="object"){
            Object.keys(val).forEach(function(subKey){
                appendValue(key+"["+subKey+"]",val[subKey]);
            });
            return;
        }
        if(val===undefined||val===null)val="";
        body.append(key,String(val));
    }
    Object.keys(obj||{}).forEach(function(k){appendValue(k,obj[k]);});
    return body;
}
function requestJson(url,method,data){
    var payload=Object.assign({},data||{});
    var reqMethod=String(method||"POST").toUpperCase();
    if(reqMethod==="PUT"||reqMethod==="PATCH"||reqMethod==="DELETE"){
        payload._method=reqMethod;
        reqMethod="POST";
    }
    return fetch(url,{
        method:reqMethod,
        headers:{
            "Content-Type":"application/x-www-form-urlencoded;charset=UTF-8",
            "X-CSRF-TOKEN":csrf,
            "Accept":"application/json"
        },
        body:asFormUrlEncoded(payload)
    }).then(function(r){
        return r.text().then(function(t){
            var data={};
            try{data=t?JSON.parse(t):{};}catch(_e){data={message:t||"Request failed"};}
            if(!r.ok){
                var msg=(data&&data.message)?data.message:("HTTP "+r.status);
                throw new Error(msg);
            }
            return data||{};
        });
    });
}
function extractLinksFromLayout(layout){
    var out=[];
    function visit(node){
        if(Array.isArray(node)){node.forEach(visit);return;}
        if(!node||typeof node!=="object")return;
        var s=node.settings;
        if(s&&typeof s==="object"){
            if(typeof s.link==="string"&&s.link.trim()!=="")out.push(s.link.trim());
            if(Array.isArray(s.items)){
                s.items.forEach(function(it){
                    if(it&&typeof it.url==="string"&&it.url.trim()!=="")out.push(it.url.trim());
                });
            }
        }
        Object.keys(node).forEach(function(k){visit(node[k]);});
    }
    visit(layout||{});
    return out;
}
function referencingStepsForSlug(targetSlug,excludeStepId){
    var slug=String(targetSlug||"").trim().toLowerCase();
    if(slug==="")return [];
    var p1=new RegExp("/f/"+escapeRegExp(String(funnelSlug||""))+"/"+escapeRegExp(slug)+"(?:$|[?#])","i");
    var p2=new RegExp("/funnel/"+escapeRegExp(String(funnelSlug||""))+"/"+escapeRegExp(slug)+"(?:$|[?#])","i");
    var refs=[];
    steps.forEach(function(s){
        if(+s.id===+excludeStepId)return;
        var links=extractLinksFromLayout(s.layout_json||{});
        var hit=links.some(function(u){
            var x=String(u||"").toLowerCase();
            return p1.test(x)||p2.test(x);
        });
        if(hit)refs.push(String(s.title||("Step "+String(s.id))));
    });
    return refs;
}
function buildStepPayload(stepLike,overrides){
    var s=Object.assign({},stepLike||{},overrides||{});
    return {
        title:String(s.title||"Untitled").trim()||"Untitled",
        subtitle:String(s.subtitle||"").trim(),
        slug:slugifyPage(String(s.slug||s.title||"page")),
        type:String(s.type||"custom"),
        template:String(s.template||"simple"),
        layout_style:String(s.layout_style||"centered"),
        content:String(s.content||""),
        cta_label:String(s.cta_label||""),
        price:(s.price==null||String(s.price)==="")?"":String(s.price),
        background_color:String(s.background_color||""),
        button_color:String(s.button_color||""),
        is_active:((s.is_active===false||String(s.is_active)==="0")?0:1),
        step_tags:normalizeTagArray(s.step_tags).join(", ")
    };
}
function defaultStepTitleForType(type){
    var t=String(type||"custom").toLowerCase();
    if(t==="opt_in")return "Opt-in";
    if(t==="thank_you")return "Thank You";
    if(t==="checkout")return "Checkout";
    if(t==="sales")return "Sales";
    if(t==="landing")return "Landing";
    return "Custom Page";
}
function uniqueStepSlug(baseSlug,excludeStepId){
    var base=slugifyPage(baseSlug||"page");
    var used={};
    steps.forEach(function(s){
        if(+s.id===+excludeStepId)return;
        used[String(s.slug||"").toLowerCase()]=true;
    });
    if(!used[base])return base;
    var n=2;
    while(used[base+"-"+n])n++;
    return base+"-"+n;
}
function mergeStepData(raw){
    var r=(raw&&typeof raw==="object")?raw:{};
    return {
        id:Number(r.id||0),
        title:String(r.title||"Untitled"),
        slug:String(r.slug||""),
        type:String(r.type||"custom"),
        layout_json:(r.layout_json&&typeof r.layout_json==="object")?r.layout_json:null,
        background_color:(typeof r.background_color==="string"&&r.background_color.trim()!=="")?r.background_color.trim():null,
        position:Number(r.position||0),
        is_active:!(r.is_active===false||String(r.is_active)==="0"),
        layout_style:String(r.layout_style||"centered"),
        template:String(r.template||"simple"),
        subtitle:String(r.subtitle||""),
        content:String(r.content||""),
        cta_label:String(r.cta_label||""),
        price:(r.price==null||String(r.price)==="")?"":String(r.price),
        button_color:(typeof r.button_color==="string"&&r.button_color.trim()!=="")?r.button_color.trim():null,
        step_tags:normalizeTagArray(r.step_tags),
    };
}
function applyStepUpdate(rawStep){
    var merged=mergeStepData(rawStep);
    var idx=steps.findIndex(function(s){return +s.id===+merged.id;});
    if(idx>=0){
        steps[idx]=Object.assign({},steps[idx],merged);
    }else{
        steps.push(merged);
    }
    return merged;
}
function isRequiredStepType(type){
    var t=String(type||"").toLowerCase();
    return t==="landing"||t==="opt_in"||t==="sales"||t==="checkout"||t==="thank_you";
}
function canDeleteStep(stepObj){
    if(!stepObj)return {ok:false,message:"No step selected."};
    if(steps.length<=1)return {ok:false,message:"Cannot delete the last page."};
    var t=String(stepObj.type||"").toLowerCase();
    if(isRequiredStepType(t)){
        var count=steps.filter(function(s){return String(s.type||"").toLowerCase()===t;}).length;
        if(count<=1)return {ok:false,message:"Cannot delete the last "+pageTypeLabel(t)+" page."};
    }
    return {ok:true,message:""};
}
function orderedStepIdsWithPositions(){
    sortStepsByPosition();
    return steps.map(function(s,idx){
        s.position=idx+1;
        return Number(s.id);
    });
}
function persistStepOrder(orderIds){
    return requestJson(stepReorderUrl,"POST",{order:orderIds}).then(function(){
        orderIds.forEach(function(id,idx){
            var s=steps.find(function(x){return +x.id===+id;});
            if(s)s.position=idx+1;
        });
        renderStepOptions();
    });
}
function moveSelectedStepBy(delta){
    var current=cur();
    if(!current)return Promise.resolve(false);
    sortStepsByPosition();
    var idx=steps.findIndex(function(s){return +s.id===+current.id;});
    if(idx<0)return Promise.resolve(false);
    var ni=idx+Number(delta||0);
    if(ni<0||ni>=steps.length)return Promise.resolve(false);
    var temp=steps[idx];
    steps[idx]=steps[ni];
    steps[ni]=temp;
    return persistStepOrder(orderedStepIdsWithPositions()).then(function(){
        state.sid=current.id;
        renderStepOptions();
        showBuilderToast("Page order updated.","success");
        return true;
    }).catch(function(err){
        showBuilderToast((err&&err.message)||"Failed to reorder pages.","error");
        throw err;
    });
}
function syncAddPageDraftFromType(force){
    var type=String((pageMgrAddType&&pageMgrAddType.value)||"landing").toLowerCase();
    var defaultTitle=defaultStepTitleForType(type);
    var currentTitle=String((pageMgrAddTitle&&pageMgrAddTitle.value)||"").trim();
    if(pageMgrAddTitle && (force||currentTitle==="")){
        pageMgrAddTitle.value=defaultTitle;
    }
    if(pageMgrAddSlug){
        var currentSlug=String(pageMgrAddSlug.value||"").trim();
        if(force||currentSlug===""){
            pageMgrAddSlug.value=slugifyPage(String((pageMgrAddTitle&&pageMgrAddTitle.value)||defaultTitle));
        }
    }
}
function syncRenameDraftFromSelected(){
    var s=cur();
    if(!s){
        if(pageMgrRenameTitle)pageMgrRenameTitle.value="";
        if(pageMgrRenameSlug)pageMgrRenameSlug.value="";
        if(pageMgrRenameTags)pageMgrRenameTags.value="";
        return;
    }
    if(pageMgrRenameTitle)pageMgrRenameTitle.value=String(s.title||"");
    if(pageMgrRenameSlug)pageMgrRenameSlug.value=String(s.slug||"");
    if(pageMgrRenameTags)pageMgrRenameTags.value=normalizeTagArray(s.step_tags).join(", ");
}
function syncPageManagerList(){
    if(!pageMgrList)return;
    sortStepsByPosition();
    pageMgrList.innerHTML="";
    steps.forEach(function(s){
        var item=document.createElement("div");
        item.className="page-mgr-item"+(String(state.sid)===String(s.id)?" is-selected":"");
        item.setAttribute("role","option");
        item.setAttribute("aria-selected",String(state.sid)===String(s.id)?"true":"false");
        item.setAttribute("data-id",String(s.id));
        item.setAttribute("draggable","true");
        item.innerHTML='<span>'+String(s.title||("Step "+String(s.id))).replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span><span class="page-mgr-item-handle"><i class="fas fa-grip-vertical"></i></span>';
        item.addEventListener("click",function(){
            var id=Number(item.getAttribute("data-id")||0);
            if(!id)return;
            state.sid=id;
            renderStepOptions();
            loadStep(id);
            syncRenameDraftFromSelected();
            syncPageManagerList();
        });
        item.addEventListener("dragstart",function(e){
            pageMgrDragId=String(s.id);
            item.classList.add("is-dragging");
            if(e.dataTransfer){
                e.dataTransfer.effectAllowed="move";
                e.dataTransfer.setData("text/plain",pageMgrDragId);
            }
        });
        item.addEventListener("dragend",function(){
            pageMgrDragId=null;
            pageMgrList.querySelectorAll(".page-mgr-item").forEach(function(el){
                el.classList.remove("is-dragging","drag-before","drag-after");
            });
        });
        item.addEventListener("dragover",function(e){
            e.preventDefault();
            if(!pageMgrDragId||pageMgrDragId===String(s.id))return;
            var rect=item.getBoundingClientRect();
            var after=(e.clientY-rect.top)>(rect.height/2);
            item.classList.toggle("drag-after",after);
            item.classList.toggle("drag-before",!after);
        });
        item.addEventListener("dragleave",function(){
            item.classList.remove("drag-before","drag-after");
        });
        item.addEventListener("drop",function(e){
            e.preventDefault();
            var dragId=pageMgrDragId||String((e.dataTransfer&&e.dataTransfer.getData("text/plain"))||"");
            var targetId=String(s.id);
            item.classList.remove("drag-before","drag-after");
            if(!dragId||dragId===targetId)return;
            var ids=orderedStepIdsWithPositions();
            var fromIdx=ids.findIndex(function(id){return String(id)===dragId;});
            var toIdx=ids.findIndex(function(id){return String(id)===targetId;});
            if(fromIdx<0||toIdx<0)return;
            var rect=item.getBoundingClientRect();
            var after=(e.clientY-rect.top)>(rect.height/2);
            var moved=ids.splice(fromIdx,1)[0];
            var insertIdx=toIdx+(after?1:0);
            if(fromIdx<toIdx&&after)insertIdx=toIdx;
            if(fromIdx<toIdx&&!after)insertIdx=toIdx-1;
            if(insertIdx<0)insertIdx=0;
            if(insertIdx>ids.length)insertIdx=ids.length;
            ids.splice(insertIdx,0,moved);
            persistStepOrder(ids).then(function(){
                renderStepOptions();
                syncPageManagerList();
                showBuilderToast("Page order updated.","success");
            }).catch(function(err){
                showBuilderToast((err&&err.message)||"Failed to reorder pages.","error");
            });
        });
        pageMgrList.appendChild(item);
    });
    if(state.sid==null&&steps.length){
        state.sid=steps[0].id;
    }
}
function closePageManagerModal(){
    if(!pageMgrModal)return;
    pageMgrModal.classList.remove("open");
    pageMgrModal.setAttribute("aria-hidden","true");
}
function openPageManagerModal(){
    if(!pageMgrModal)return;
    syncPageManagerList();
    syncAddPageDraftFromType(true);
    syncRenameDraftFromSelected();
    pageMgrModal.classList.add("open");
    pageMgrModal.setAttribute("aria-hidden","false");
    if(pageMgrAddTitle)pageMgrAddTitle.focus();
}
function createPageFromManager(){
    var type=String((pageMgrAddType&&pageMgrAddType.value)||"landing").toLowerCase();
    var selected=cur();
    var titleRaw=String((pageMgrAddTitle&&pageMgrAddTitle.value)||"").trim();
    if(type==="custom" && titleRaw===""){
        showBuilderToast("Custom page title is required.","error");
        if(pageMgrAddTitle)pageMgrAddTitle.focus();
        return;
    }
    var title=titleRaw!==""?titleRaw:defaultStepTitleForType(type);
    if(String(title).trim()===""){
        showBuilderToast("Page title is required.","error");
        if(pageMgrAddTitle)pageMgrAddTitle.focus();
        return;
    }
    var slugRaw=String((pageMgrAddSlug&&pageMgrAddSlug.value)||"").trim();
    var slugBase=slugifyPage(slugRaw!==""?slugRaw:title);
    if(slugBase===""){
        showBuilderToast("Page slug is invalid. Use letters, numbers, and hyphen.","error");
        if(pageMgrAddSlug)pageMgrAddSlug.focus();
        return;
    }
    var slug=uniqueStepSlug(slugBase,null);
    var payload=buildStepPayload({
        title:title,
        slug:slug,
        type:type,
        template:"simple",
        layout_style:"centered",
        is_active:true
    });
    requestJson(stepStoreUrl,"POST",payload).then(function(resp){
        var created=applyStepUpdate((resp&&resp.step)||payload);
        if(!created.layout_json)created.layout_json={root:[],sections:[]};
        var order=orderedStepIdsWithPositions();
        var newIdx=order.findIndex(function(x){return +x===+created.id;});
        if(selected){
            var currentIdx=order.findIndex(function(x){return +x===+selected.id;});
            if(currentIdx>=0&&newIdx>=0){
                order.splice(newIdx,1);
                order.splice(currentIdx+1,0,created.id);
            }
        }
        persistStepOrder(order).then(function(){
            state.sid=created.id;
            renderStepOptions();
            loadStep(created.id);
            syncPageManagerList();
            syncRenameDraftFromSelected();
            syncAddPageDraftFromType(true);
            showBuilderToast("Page added.","success");
        }).catch(function(err){
            showBuilderToast((err&&err.message)||"Page added, but reorder failed.","error");
        });
    }).catch(function(err){
        showBuilderToast((err&&err.message)||"Failed to add page.","error");
    });
}
function renamePageFromManager(){
    var s=cur();
    if(!s){showBuilderToast("No page selected.","error");return;}
    var nextTitle=String((pageMgrRenameTitle&&pageMgrRenameTitle.value)||"").trim();
    if(nextTitle===""){
        showBuilderToast("Page title is required.","error");
        if(pageMgrRenameTitle)pageMgrRenameTitle.focus();
        return;
    }
    var typedSlug=String((pageMgrRenameSlug&&pageMgrRenameSlug.value)||"").trim();
    var nextSlug=slugifyPage(typedSlug!==""?typedSlug:nextTitle);
    if(nextSlug===""){
        showBuilderToast("Page slug is invalid. Use letters, numbers, and hyphen.","error");
        if(pageMgrRenameSlug)pageMgrRenameSlug.focus();
        return;
    }
    nextSlug=uniqueStepSlug(nextSlug,s.id);
    if(pageMgrRenameSlug)pageMgrRenameSlug.value=nextSlug;
    var refs=(nextSlug!==String(s.slug||"").toLowerCase())?referencingStepsForSlug(s.slug,s.id):[];
    if(refs.length){
        var msg="This page is linked from: "+refs.join(", ")+". Continue rename?";
        if(!window.confirm(msg))return;
    }
    var nextTags=normalizeTagArray((pageMgrRenameTags&&pageMgrRenameTags.value)||"");
    var payload=buildStepPayload(s,{title:nextTitle,slug:nextSlug,step_tags:nextTags});
    requestJson(stepUrlFromTpl(stepUpdateTpl,s.id),"PUT",payload).then(function(resp){
        var updated=applyStepUpdate((resp&&resp.step)||payload);
        state.sid=updated.id;
        renderStepOptions();
        syncPageManagerList();
        syncRenameDraftFromSelected();
        showBuilderToast("Page renamed.","success");
    }).catch(function(err){
        showBuilderToast((err&&err.message)||"Failed to rename page.","error");
    });
}
function deletePageFromManager(){
    var s=cur();
    if(!s){showBuilderToast("No page selected.","error");return;}
    var guard=canDeleteStep(s);
    if(!guard.ok){showBuilderToast(guard.message,"error");return;}
    var refs=referencingStepsForSlug(s.slug,s.id);
    if(refs.length){
        var warn="This page is linked from: "+refs.join(", ")+". Delete anyway?";
        if(!window.confirm(warn))return;
    }
    if(!window.confirm('Delete page "'+String(s.title||"Untitled")+'"?'))return;
    requestJson(stepUrlFromTpl(stepDeleteTpl,s.id),"DELETE",{}).then(function(){
        var wasId=s.id;
        var idx=steps.findIndex(function(x){return +x.id===+wasId;});
        if(idx>=0)steps.splice(idx,1);
        if(!steps.length){
            state.sid=null;
            canvas.innerHTML="<p class='meta'>No steps found.</p>";
            settings.innerHTML="<p class='meta'>Select a component to edit.</p>";
            renderStepOptions();
            syncPageManagerList();
            syncRenameDraftFromSelected();
            showBuilderToast("Page deleted.","success");
            return;
        }
        sortStepsByPosition();
        var fallback=steps[Math.max(0,Math.min(idx,steps.length-1))];
        state.sid=fallback.id;
        persistStepOrder(orderedStepIdsWithPositions()).then(function(){
            renderStepOptions();
            loadStep(state.sid);
            syncPageManagerList();
            syncRenameDraftFromSelected();
            showBuilderToast("Page deleted.","success");
        }).catch(function(err){
            showBuilderToast((err&&err.message)||"Page deleted, but reorder failed.","error");
        });
    }).catch(function(err){
        showBuilderToast((err&&err.message)||"Failed to delete page.","error");
    });
}
function wireStepManagement(){
    if(stepSel){
        stepSel.onchange=function(){loadStep(stepSel.value);};
    }
    if(stepAddBtn){
        stepAddBtn.onclick=function(){openPageManagerModal();};
    }
    if(pageMgrModal){
        pageMgrModal.addEventListener("click",function(e){
            if(e.target===pageMgrModal)closePageManagerModal();
        });
    }
    if(pageMgrClose){
        pageMgrClose.onclick=function(){closePageManagerModal();};
    }
    if(pageMgrList){
        pageMgrList.addEventListener("dragover",function(e){
            e.preventDefault();
        });
        pageMgrList.addEventListener("drop",function(e){
            if(e.target!==pageMgrList)return;
            e.preventDefault();
            var dragId=pageMgrDragId||String((e.dataTransfer&&e.dataTransfer.getData("text/plain"))||"");
            if(!dragId)return;
            var ids=orderedStepIdsWithPositions();
            var fromIdx=ids.findIndex(function(id){return String(id)===dragId;});
            if(fromIdx<0)return;
            var moved=ids.splice(fromIdx,1)[0];
            ids.push(moved);
            persistStepOrder(ids).then(function(){
                renderStepOptions();
                syncPageManagerList();
                showBuilderToast("Page order updated.","success");
            }).catch(function(err){
                showBuilderToast((err&&err.message)||"Failed to reorder pages.","error");
            });
        });
    }
    if(pageMgrAddType){
        pageMgrAddType.onchange=function(){syncAddPageDraftFromType(true);};
    }
    if(pageMgrAddTitle){
        pageMgrAddTitle.addEventListener("input",function(){
            if(pageMgrAddSlug && String(pageMgrAddSlug.value||"").trim()===""){
                pageMgrAddSlug.value=slugifyPage(pageMgrAddTitle.value||"");
            }
        });
    }
    if(pageMgrCreateBtn){
        pageMgrCreateBtn.onclick=function(){createPageFromManager();};
    }
    if(pageMgrRenameBtn){
        pageMgrRenameBtn.onclick=function(){renamePageFromManager();};
    }
    if(pageMgrDeleteBtn){
        pageMgrDeleteBtn.onclick=function(){deletePageFromManager();};
    }
    if(pageMgrUpBtn){
        pageMgrUpBtn.onclick=function(){
            moveSelectedStepBy(-1).then(function(){syncPageManagerList();}).catch(function(){});
        };
    }
    if(pageMgrDownBtn){
        pageMgrDownBtn.onclick=function(){
            moveSelectedStepBy(1).then(function(){syncPageManagerList();}).catch(function(){});
        };
    }
}
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
    if(s.__freeformCanvas){
        var ri=rootItems(),lastElIdx=-1;
        for(var i=0;i<ri.length;i++){if(String((ri[i]&&ri[i].kind)||"").toLowerCase()==="el")lastElIdx=i;}
        return {section:s,root:null,index:lastElIdx,isWrap:true,isFreeform:true};
    }
    if(s.__rootWrap&&s.__rootRef){
        const idx=rootIndexByRef(s.__rootRef);
        return {section:s,root:s.__rootRef,index:idx,isWrap:true};
    }
    const idx=rootItems().findIndex(it=>String((it&&it.kind)||"").toLowerCase()==="section"&&String(it.id||"")===String(s.id||""));
    return {section:s,root:idx>=0?rootItems()[idx]:null,index:idx,isWrap:false};
}

const undoHistory=[];const maxUndo=40;
function saveToHistory(){if(!state.layout)return;undoHistory.push(clone(state.layout));if(undoHistory.length>maxUndo)undoHistory.shift();queueAutoSave();}
function undo(){if(!undoHistory.length)return;state.layout=undoHistory.pop();render();}

function syncSectionsFromRoot(){
    state.layout=state.layout||{};
    state.layout.root=Array.isArray(state.layout.root)?state.layout.root:[];
    const out=[];
    var freeformEls=[];
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
            freeformEls.push(it);
            return;
        }
        it.elements=Array.isArray(it.elements)?it.elements:[];
        it.rows=Array.isArray(it.rows)?it.rows:[];
        out.push(it);
    });
    if(freeformEls.length>0){
        var existingFreeform=(state.layout.sections||[]).find(function(s){return !!s.__freeformCanvas;});
        out.push({
            id:existingFreeform?existingFreeform.id:"sec_freeform_canvas",
            style:existingFreeform?existingFreeform.style:{},
            settings:existingFreeform?existingFreeform.settings:{contentWidth:"full"},
            elements:freeformEls,
            rows:[],
            __rootWrap:true,
            __rootKind:"el",
            __bareRootWrap:true,
            __freeformCanvas:true
        });
    }
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
var _layoutKeys={position:1,left:1,top:1,right:1,bottom:1,width:1,height:1,minWidth:1,maxWidth:1,minHeight:1,maxHeight:1,zIndex:1,margin:1,marginTop:1,marginRight:1,marginBottom:1,marginLeft:1,flex:1};
function contentStyleApply(node,s){if(!s)return;Object.keys(s).forEach(k=>{if(!_layoutKeys[k]&&s[k]!==""&&s[k]!=null)node.style[k]=s[k];});}
function normalizeFormFields(raw,preferEmailDefault){
    var list=Array.isArray(raw)?raw:[];
    var out=list.map(function(field,idx){
        var f=(field&&typeof field==="object")?field:{};
        var type=String(f.type||"text").trim().toLowerCase();
        if(type==="")type="text";
        var label=String(f.label||"").trim();
        if(label===""){
            if(type==="email")label="Email";
            else if(type==="phone_number")label="Phone";
            else label=("Field "+(idx+1));
        }
        var placeholder=String(f.placeholder||"").trim();
        if(placeholder===""){
            if(type==="phone_number")placeholder="09XXXXXXXXX";
            else if(type==="email")placeholder="Email address";
            else placeholder=label;
        }
        return {type:type,label:label,placeholder:placeholder,required:!!f.required};
    }).filter(function(f){return String(f.label||"").trim()!=="";});
    if(!out.length){
        out.push(
            preferEmailDefault
                ? {type:"email",label:"Email",placeholder:"Email address",required:true}
                : {type:"text",label:"First name",placeholder:"First name",required:false}
        );
    }
    return out;
}
function editorPrefs(){
    state.layout=state.layout||{};
    state.layout.__editor=(state.layout.__editor&&typeof state.layout.__editor==="object")?state.layout.__editor:{};
    return state.layout.__editor;
}
function normalizeCanvasBgValue(v){
    var bg=String(v||"").trim();
    return /^#[0-9A-Fa-f]{6}$/.test(bg)?bg:null;
}
function withCanvasBgInLayout(layout,bg){
    var out=(layout&&typeof layout==="object")?clone(layout):{root:[],sections:[]};
    out.__editor=(out.__editor&&typeof out.__editor==="object")?out.__editor:{};
    if(bg)out.__editor.canvasBg=bg;
    else if(Object.prototype.hasOwnProperty.call(out.__editor,"canvasBg"))delete out.__editor.canvasBg;
    if(_canvasLockedWidth>0)out.__editor.canvasWidth=_canvasLockedWidth;
    if(_canvasInnerWidth>0)out.__editor.canvasInnerWidth=_canvasInnerWidth;
    return out;
}
function propagateCanvasBgToAllSteps(bg){
    var norm=normalizeCanvasBgValue(bg);
    steps.forEach(function(s){
        if(!s)return;
        s.background_color=norm;
        var baseLayout=(s.layout_json&&typeof s.layout_json==="object")?s.layout_json:defaults(s.type);
        s.layout_json=withCanvasBgInLayout(baseLayout,norm);
    });
    if(state.layout&&typeof state.layout==="object"){
        var prefs=editorPrefs();
        if(norm)prefs.canvasBg=norm;
        else if(Object.prototype.hasOwnProperty.call(prefs,"canvasBg"))delete prefs.canvasBg;
    }
}
function applyCanvasBgPreference(){
    if(!canvas)return;
    var prefs=(state.layout&&state.layout.__editor&&typeof state.layout.__editor==="object")?state.layout.__editor:{};
    var bg=String(prefs.canvasBg||"").trim();
    canvas.style.background=bg!==""?bg:"linear-gradient(180deg,#F3EEF7,#E7D8F0)";
}
function syncCanvasBgControls(){
    if(!canvasBgColor||!canvasBgReset)return;
    var prefs=(state.layout&&state.layout.__editor&&typeof state.layout.__editor==="object")?state.layout.__editor:{};
    var bg=String(prefs.canvasBg||"").trim();
    canvasBgColor.value=bg!==""?bg:"#F3EEF7";
    canvasBgReset.style.display=bg!==""?"inline-flex":"none";
}
function iconStyleClass(styleName){
    var s=String(styleName||"solid").toLowerCase();
    if(s==="regular")return "fa-regular";
    if(s==="brands")return "fa-brands";
    return "fa-solid";
}
function sanitizeIconName(name){
    var n=String(name||"").trim().toLowerCase();
    if(!/^[a-z0-9-]{1,40}$/.test(n))n="";
    if(n==="")n="star";
    return n;
}
function sanitizeIconStyle(styleName){
    var s=String(styleName||"solid").trim().toLowerCase();
    return (s==="regular"||s==="brands"||s==="solid")?s:"solid";
}
function iconClassName(name,styleName){
    return iconStyleClass(styleName)+" fa-"+sanitizeIconName(name);
}
function openIconPickerModal(options){
    var opts=options&&typeof options==="object"?options:{};
    var onPick=(typeof opts.onPick==="function")?opts.onPick:function(){};
    var initialSearch=String(opts.search||"").trim().toLowerCase();
    var initialStyle=sanitizeIconStyle(opts.style||"solid");
    var modal=document.createElement("div");
    modal.className="icon-picker-modal open";
    modal.innerHTML='<div class="icon-picker-card"><div class="icon-picker-head"><h4>Choose Icon</h4><button type="button" class="icon-picker-close" id="iconPickerClose">Close</button></div><div class="icon-picker-tools"><input id="iconPickerSearch" placeholder="Search icon (home, user, check)"><select id="iconPickerStyle"><option value="solid">Solid</option><option value="regular">Regular</option><option value="brands">Brands</option></select></div><div class="icon-picker-grid" id="iconPickerGrid"></div></div>';
    document.body.appendChild(modal);
    var closeModal=function(){
        if(modal&&modal.parentNode)modal.parentNode.removeChild(modal);
    };
    modal.addEventListener("click",function(e){if(e.target===modal)closeModal();});
    var closeBtn=modal.querySelector("#iconPickerClose");
    if(closeBtn)closeBtn.addEventListener("click",function(){closeModal();});
    var searchIn=modal.querySelector("#iconPickerSearch");
    var styleSel=modal.querySelector("#iconPickerStyle");
    var grid=modal.querySelector("#iconPickerGrid");
    if(searchIn)searchIn.value=initialSearch;
    if(styleSel)styleSel.value=initialStyle;
    function renderGrid(){
        if(!grid)return;
        var q=String(searchIn&&searchIn.value||"").trim().toLowerCase();
        var s=sanitizeIconStyle(styleSel&&styleSel.value||"solid");
        var list=iconCatalog.filter(function(ic){
            if(!Array.isArray(ic.styles)||ic.styles.indexOf(s)<0)return false;
            if(q==="")return true;
            var hay=(String(ic.name||"")+" "+String(ic.label||"")+" "+String(ic.keywords||"")).toLowerCase();
            return hay.indexOf(q)>=0;
        }).slice(0,200);
        grid.innerHTML="";
        if(!list.length){
            grid.innerHTML='<div class="icon-picker-empty">No icons found.</div>';
            return;
        }
        list.forEach(function(ic){
            var btn=document.createElement("button");
            btn.type="button";
            btn.className="icon-picker-item";
            btn.innerHTML='<i class="'+iconClassName(ic.name,s)+'"></i><span>'+String(ic.label||ic.name||"Icon")+'</span>';
            btn.addEventListener("click",function(){
                onPick({name:sanitizeIconName(ic.name),style:s});
                closeModal();
            });
            grid.appendChild(btn);
        });
    }
    if(searchIn)searchIn.addEventListener("input",renderGrid);
    if(styleSel)styleSel.addEventListener("change",renderGrid);
    renderGrid();
    if(searchIn)searchIn.focus();
}
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
    function normalizeLegacyColBg(col){
        if(!col||typeof col!=="object")return;
        col.style=(col.style&&typeof col.style==="object")?col.style:{};
        var bg=String(col.style.backgroundColor||"").trim().toLowerCase();
        if(bg==="#F3EEF7"||bg==="rgb(248, 250, 252)"||bg==="rgba(248, 250, 252, 1)"){
            col.style.backgroundColor="#ffffff";
        }
    }
    function restorePosition(el){
        if(!el||!el.settings)return;
        if(el.settings.positionMode==="absolute"){
            el.style=el.style||{};
            if(!el.style.position)el.style.position="absolute";
            if(el.settings.freeX!==undefined&&!el.style.left)el.style.left=el.settings.freeX+"px";
            if(el.settings.freeY!==undefined&&!el.style.top)el.style.top=el.settings.freeY+"px";
        }
    }
    (layout.sections||[]).forEach(sec=>{
        (sec.elements||[]).forEach(el=>{
            if(el.type==="video"||el.type==="image"){
                if(!el.style||typeof el.style!=="object")el.style={};
                var sw=(el.settings&&el.settings.width)||"";
                if(sw&&!el.style.width)el.style.width=sw;
            }
            restorePosition(el);
        });
        (sec.rows||[]).forEach(row=>{
            (row.columns||[]).forEach(col=>{
                normalizeLegacyColBg(col);
                (col.elements||[]).forEach(el=>{
                    if(el.type==="video"||el.type==="image"){
                        if(!el.style||typeof el.style!=="object")el.style={};
                        var sw=(el.settings&&el.settings.width)||"";
                        if(sw&&!el.style.width)el.style.width=sw;
                    }
                    restorePosition(el);
                });
            });
        });
    });
}
function loadStep(id){
    state.sid=+id;
    const s=cur();
    const hasSavedLayout=!!(s&&s.layout_json&&typeof s.layout_json==="object"&&!Array.isArray(s.layout_json));
    state.layout=hasSavedLayout?clone(s.layout_json):defaults(s&&s.type);
    var prefs=editorPrefs();
    var stepBg=(s&&typeof s.background_color==="string")?String(s.background_color).trim():"";
    if(/^#[0-9A-Fa-f]{6}$/.test(stepBg)){
        prefs.canvasBg=stepBg;
    }else if(prefs&&Object.prototype.hasOwnProperty.call(prefs,"canvasBg")){
        delete prefs.canvasBg;
    }
    ensureRootModel();
    normalizeElementStyle(state.layout);
    state.sel=null;
    state.carouselSel=null;
    undoHistory.length=0;
    saveMsg.textContent="Loaded "+s.title;
    applyCanvasBgPreference();
    syncCanvasBgControls();
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
function inferNodeKind(node){
    if(!node||typeof node!=="object")return "";
    if(typeof node.type==="string"&&node.type!=="")return "el";
    if(Array.isArray(node.columns))return "row";
    if(Array.isArray(node.rows))return "sec";
    if(Array.isArray(node.elements))return "col";
    return "";
}
function inferIdPrefix(raw){
    var v=String(raw||"").toLowerCase();
    if(/^sec[_-]/.test(v))return "sec";
    if(/^row[_-]/.test(v))return "row";
    if(/^col[_-]/.test(v))return "col";
    if(/^el[_-]/.test(v))return "el";
    if(/^sld[_-]/.test(v))return "sld";
    return "id";
}
function newIdFromPrefix(prefix){
    var p=String(prefix||"id").toLowerCase();
    if(p==="sec"||p==="row"||p==="col"||p==="el"||p==="sld")return uid(p);
    return uid("id");
}
function cloneWithNewIds(value){
    if(Array.isArray(value))return value.map(cloneWithNewIds);
    if(!value||typeof value!=="object")return value;
    var out={};
    Object.keys(value).forEach(function(k){
        if(k.indexOf("__")===0)return;
        var v=value[k];
        if(k==="id"){
            out.id=newIdFromPrefix(inferIdPrefix(v));
            return;
        }
        out[k]=cloneWithNewIds(v);
    });
    return out;
}
function copySelectedToClipboard(){
    var target=selectedTarget();
    if(!target)return false;
    var sourceKind=state.carouselSel?"carousel":"main";
    var selMeta=sourceKind==="carousel"?clone(state.carouselSel):clone(state.sel||{});
    state.clipboard={
        node:clone(target),
        nodeKind:inferNodeKind(target),
        source:sourceKind,
        selection:selMeta,
    };
    return true;
}
function selectedCarouselSlideAndParent(){
    var cs=state.carouselSel;
    var parent=selectedCarouselParent();
    if(!cs||!parent||parent.type!=="carousel")return null;
    parent.settings=parent.settings||{};
    var slides=ensureCarouselSlides(parent.settings);
    var slide=slides.find(function(s){return String((s&&s.id)||"")===String(cs.slideId||"");});
    if(!slide){
        var active=Number(parent.settings.activeSlide)||0;
        slide=slides[active]||slides[0]||null;
    }
    if(!slide)return null;
    return {parent:parent,slides:slides,slide:slide};
}
function pasteNodeInCarousel(node,nodeKind){
    var cs=state.carouselSel;
    var ctx=selectedCarouselSlideAndParent();
    if(!cs||!ctx)return false;
    var slide=ctx.slide;
    slide.rows=Array.isArray(slide.rows)?slide.rows:[];
    if(cs.k==="row"){
        var rowIndex=slide.rows.findIndex(function(r){return String((r&&r.id)||"")===String(cs.rowId||"");});
        if(rowIndex<0)rowIndex=0;
        var rowNode=slide.rows[rowIndex];
        if(!rowNode){
            rowNode=createDefaultRow();
            slide.rows.push(rowNode);
            rowIndex=slide.rows.length-1;
        }
        rowNode.columns=Array.isArray(rowNode.columns)?rowNode.columns:[];
        if(nodeKind==="row"){
            slide.rows.splice(rowIndex+1,0,node);
            state.carouselSel={k:"row",slideId:slide.id,rowId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="col"){
            if(rowNode.columns.length>=4){notifyColumnFull();return false;}
            rowNode.columns.push(node);
            state.carouselSel={k:"col",slideId:slide.id,rowId:rowNode.id,colId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="el"){
            if(!rowNode.columns.length)rowNode.columns.push(createDefaultColumn());
            var firstCol=rowNode.columns[0];
            firstCol.elements=Array.isArray(firstCol.elements)?firstCol.elements:[];
            firstCol.elements.push(node);
            state.carouselSel={k:"el",slideId:slide.id,rowId:rowNode.id,colId:firstCol.id,elId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        return false;
    }
    if(cs.k==="col"){
        var targetRow=(slide.rows||[]).find(function(r){return String((r&&r.id)||"")===String(cs.rowId||"");});
        if(!targetRow)return false;
        targetRow.columns=Array.isArray(targetRow.columns)?targetRow.columns:[];
        var colIndex=targetRow.columns.findIndex(function(c){return String((c&&c.id)||"")===String(cs.colId||"");});
        if(colIndex<0)return false;
        var colNode=targetRow.columns[colIndex];
        colNode.elements=Array.isArray(colNode.elements)?colNode.elements:[];
        if(nodeKind==="el"){
            colNode.elements.push(node);
            state.carouselSel={k:"el",slideId:slide.id,rowId:targetRow.id,colId:colNode.id,elId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="col"){
            if(targetRow.columns.length>=4){notifyColumnFull();return false;}
            targetRow.columns.splice(colIndex+1,0,node);
            state.carouselSel={k:"col",slideId:slide.id,rowId:targetRow.id,colId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="row"){
            var rowIndex=(slide.rows||[]).findIndex(function(r){return String((r&&r.id)||"")===String(targetRow.id||"");});
            slide.rows.splice(rowIndex+1,0,node);
            state.carouselSel={k:"row",slideId:slide.id,rowId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        return false;
    }
    if(cs.k==="el"){
        var rw=(slide.rows||[]).find(function(r){return String((r&&r.id)||"")===String(cs.rowId||"");});
        var cl=rw?(rw.columns||[]).find(function(c){return String((c&&c.id)||"")===String(cs.colId||"");}):null;
        if(!rw||!cl)return false;
        cl.elements=Array.isArray(cl.elements)?cl.elements:[];
        var elIndex=cl.elements.findIndex(function(e){return String((e&&e.id)||"")===String(cs.elId||"");});
        if(elIndex<0)elIndex=cl.elements.length-1;
        if(nodeKind==="el"){
            cl.elements.splice(elIndex+1,0,node);
            state.carouselSel={k:"el",slideId:slide.id,rowId:rw.id,colId:cl.id,elId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="col"){
            rw.columns=Array.isArray(rw.columns)?rw.columns:[];
            if(rw.columns.length>=4){notifyColumnFull();return false;}
            var colIndex=rw.columns.findIndex(function(c){return String((c&&c.id)||"")===String(cl.id||"");});
            rw.columns.splice(colIndex+1,0,node);
            state.carouselSel={k:"col",slideId:slide.id,rowId:rw.id,colId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        if(nodeKind==="row"){
            var rowIndex=(slide.rows||[]).findIndex(function(r){return String((r&&r.id)||"")===String(rw.id||"");});
            slide.rows.splice(rowIndex+1,0,node);
            state.carouselSel={k:"row",slideId:slide.id,rowId:node.id,parent:clone(cs.parent||{})};
            return true;
        }
        return false;
    }
    return false;
}
function pasteNodeAtRoot(node,nodeKind){
    ensureRootModel();
    var rs=rootItems();
    if(nodeKind==="sec")rs.push(Object.assign({kind:"section"},node));
    else if(nodeKind==="row")rs.push(Object.assign({kind:"row"},node));
    else if(nodeKind==="col")rs.push(Object.assign({kind:"column"},node));
    else if(nodeKind==="el")rs.push(Object.assign({kind:"el"},node));
    else return false;
    syncSectionsFromRoot();
    if(nodeKind==="sec")state.sel={k:"sec",s:node.id};
    else if(nodeKind==="row")state.sel={k:"row",s:"sec_wrap_row_"+String(node.id),r:node.id};
    else if(nodeKind==="col")state.sel={k:"col",s:"sec_wrap_col_"+String(node.id),r:"row_wrap_col_"+String(node.id),c:node.id};
    else state.sel={k:"el",scope:"section",s:"sec_wrap_el_"+String(node.id),e:node.id};
    state.carouselSel=null;
    return true;
}
function pasteNodeInMain(node,nodeKind){
    var sel=state.sel;
    if(!sel||!sel.k)return pasteNodeAtRoot(node,nodeKind);
    ensureRootModel();
    if(sel.k==="el"){
        if(sel.scope==="section"){
            var ss=sec(sel.s);if(!ss)return pasteNodeAtRoot(node,nodeKind);
            ss.elements=Array.isArray(ss.elements)?ss.elements:[];
            var sIdx=ss.elements.findIndex(function(x){return String((x&&x.id)||"")===String(sel.e||"");});
            if(nodeKind==="el"){
                ss.elements.splice((sIdx>=0?sIdx+1:ss.elements.length),0,node);
                state.sel={k:"el",scope:"section",s:ss.id,e:node.id};state.carouselSel=null;return true;
            }
        if(nodeKind==="row"){
            ss.rows=Array.isArray(ss.rows)?ss.rows:[];
            ss.rows.push(node);
            state.sel={k:"row",s:ss.id,r:node.id};state.carouselSel=null;return true;
        }
            if(nodeKind==="sec")return pasteNodeAtRoot(node,nodeKind);
            if(nodeKind==="col"){
                ss.rows=Array.isArray(ss.rows)?ss.rows:[];
                var rw=createDefaultRow();rw.columns=[node];ss.rows.push(rw);
                state.sel={k:"col",s:ss.id,r:rw.id,c:node.id};state.carouselSel=null;return true;
            }
            return false;
        }
        var rr=row(sel.s,sel.r),cc=col(sel.s,sel.r,sel.c),ss2=sec(sel.s);
        if(!rr||!cc||!ss2)return pasteNodeAtRoot(node,nodeKind);
        cc.elements=Array.isArray(cc.elements)?cc.elements:[];
        var eIdx=cc.elements.findIndex(function(x){return String((x&&x.id)||"")===String(sel.e||"");});
        if(nodeKind==="el"){
            cc.elements.splice((eIdx>=0?eIdx+1:cc.elements.length),0,node);
            state.sel={k:"el",s:ss2.id,r:rr.id,c:cc.id,e:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="col"){
            rr.columns=Array.isArray(rr.columns)?rr.columns:[];
            if(rr.columns.length>=4){notifyColumnFull();return false;}
            var cIdx=rr.columns.findIndex(function(x){return String((x&&x.id)||"")===String(cc.id||"");});
            rr.columns.splice((cIdx>=0?cIdx+1:rr.columns.length),0,node);
            state.sel={k:"col",s:ss2.id,r:rr.id,c:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="row"){
            ss2.rows=Array.isArray(ss2.rows)?ss2.rows:[];
            var rIdx=ss2.rows.findIndex(function(x){return String((x&&x.id)||"")===String(rr.id||"");});
            ss2.rows.splice((rIdx>=0?rIdx+1:ss2.rows.length),0,node);
            state.sel={k:"row",s:ss2.id,r:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="sec")return pasteNodeAtRoot(node,nodeKind);
        return false;
    }
    if(sel.k==="col"){
        var rr2=row(sel.s,sel.r),cc2=col(sel.s,sel.r,sel.c),ss3=sec(sel.s);
        if(!rr2||!cc2||!ss3)return pasteNodeAtRoot(node,nodeKind);
        if(nodeKind==="el"){
            cc2.elements=Array.isArray(cc2.elements)?cc2.elements:[];
            cc2.elements.push(node);
            state.sel={k:"el",s:ss3.id,r:rr2.id,c:cc2.id,e:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="col"){
            rr2.columns=Array.isArray(rr2.columns)?rr2.columns:[];
            if(rr2.columns.length>=4){notifyColumnFull();return false;}
            var c2Idx=rr2.columns.findIndex(function(x){return String((x&&x.id)||"")===String(cc2.id||"");});
            rr2.columns.splice((c2Idx>=0?c2Idx+1:rr2.columns.length),0,node);
            state.sel={k:"col",s:ss3.id,r:rr2.id,c:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="row"){
            ss3.rows=Array.isArray(ss3.rows)?ss3.rows:[];
            var r2Idx=ss3.rows.findIndex(function(x){return String((x&&x.id)||"")===String(rr2.id||"");});
            ss3.rows.splice((r2Idx>=0?r2Idx+1:ss3.rows.length),0,node);
            state.sel={k:"row",s:ss3.id,r:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="sec")return pasteNodeAtRoot(node,nodeKind);
        return false;
    }
    if(sel.k==="row"){
        var rr3=row(sel.s,sel.r),ss4=sec(sel.s);
        if(!rr3||!ss4)return pasteNodeAtRoot(node,nodeKind);
        if(nodeKind==="col"){
            rr3.columns=Array.isArray(rr3.columns)?rr3.columns:[];
            if(rr3.columns.length>=4){notifyColumnFull();return false;}
            rr3.columns.push(node);
            state.sel={k:"col",s:ss4.id,r:rr3.id,c:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="el"){
            rr3.columns=Array.isArray(rr3.columns)?rr3.columns:[];
            if(!rr3.columns.length)rr3.columns.push(createDefaultColumn());
            var cFirst=rr3.columns[0];
            cFirst.elements=Array.isArray(cFirst.elements)?cFirst.elements:[];
            cFirst.elements.push(node);
            state.sel={k:"el",s:ss4.id,r:rr3.id,c:cFirst.id,e:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="row"){
            ss4.rows=Array.isArray(ss4.rows)?ss4.rows:[];
            var r3Idx=ss4.rows.findIndex(function(x){return String((x&&x.id)||"")===String(rr3.id||"");});
            ss4.rows.splice((r3Idx>=0?r3Idx+1:ss4.rows.length),0,node);
            state.sel={k:"row",s:ss4.id,r:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="sec")return pasteNodeAtRoot(node,nodeKind);
        return false;
    }
    if(sel.k==="sec"){
        var ss5=sec(sel.s);if(!ss5)return pasteNodeAtRoot(node,nodeKind);
        if(nodeKind==="row"){
            ss5.rows=Array.isArray(ss5.rows)?ss5.rows:[];
            ss5.rows.push(node);
            state.sel={k:"row",s:ss5.id,r:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="el"){
            ss5.elements=Array.isArray(ss5.elements)?ss5.elements:[];
            ss5.elements.push(node);
            state.sel={k:"el",scope:"section",s:ss5.id,e:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="col"){
            ss5.rows=Array.isArray(ss5.rows)?ss5.rows:[];
            var nr=createDefaultRow();nr.columns=[node];ss5.rows.push(nr);
            state.sel={k:"col",s:ss5.id,r:nr.id,c:node.id};state.carouselSel=null;return true;
        }
        if(nodeKind==="sec")return pasteNodeAtRoot(node,nodeKind);
        return false;
    }
    return pasteNodeAtRoot(node,nodeKind);
}
function pasteFromClipboard(){
    var clip=state.clipboard;
    if(!clip||!clip.node)return false;
    var node=cloneWithNewIds(clip.node);
    var nodeKind=inferNodeKind(node);
    if(nodeKind==="")return false;
    saveToHistory();
    if(state.carouselSel){
        var okCar=pasteNodeInCarousel(node,nodeKind);
        if(okCar)return true;
    }
    return pasteNodeInMain(node,nodeKind);
}
const ctxMenu={node:null,copyBtn:null,pasteBtn:null,open:false};
function ensureContextMenu(){
    if(ctxMenu.node&&ctxMenu.node.parentNode)return ctxMenu.node;
    var menu=document.createElement("div");
    menu.className="fb-ctx-menu";
    menu.id="fbCtxMenu";
    menu.innerHTML='<button type="button" id="fbCtxCopy" class="fb-ctx-item">Copy</button><button type="button" id="fbCtxPaste" class="fb-ctx-item">Paste</button>';
    document.body.appendChild(menu);
    ctxMenu.node=menu;
    ctxMenu.copyBtn=menu.querySelector("#fbCtxCopy");
    ctxMenu.pasteBtn=menu.querySelector("#fbCtxPaste");
    if(ctxMenu.copyBtn){
        ctxMenu.copyBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.copyBtn.disabled)return;
            if(copySelectedToClipboard()){
                hideContextMenu();
                showBuilderToast("Copied component.","success");
            }
        });
    }
    if(ctxMenu.pasteBtn){
        ctxMenu.pasteBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.pasteBtn.disabled)return;
            if(pasteFromClipboard()){
                hideContextMenu();
                render();
                showBuilderToast("Pasted component.","success");
            }else{
                showBuilderToast("Paste failed for this target.","error");
            }
        });
    }
    menu.addEventListener("contextmenu",function(e){e.preventDefault();});
    return menu;
}
function syncContextMenuState(){
    ensureContextMenu();
    var hasSelection=!!selectedTarget();
    var hasClipboard=!!(state.clipboard&&state.clipboard.node);
    if(ctxMenu.copyBtn)ctxMenu.copyBtn.disabled=!hasSelection;
    if(ctxMenu.pasteBtn)ctxMenu.pasteBtn.disabled=!hasClipboard;
}
function hideContextMenu(){
    if(!ctxMenu.node)return;
    ctxMenu.node.style.display="none";
    ctxMenu.open=false;
}
function showContextMenuAt(x,y){
    var menu=ensureContextMenu();
    syncContextMenuState();
    menu.style.display="block";
    menu.style.left="0px";
    menu.style.top="0px";
    var w=menu.offsetWidth||160;
    var h=menu.offsetHeight||88;
    var vw=Math.max(document.documentElement.clientWidth||0,window.innerWidth||0);
    var vh=Math.max(document.documentElement.clientHeight||0,window.innerHeight||0);
    var nx=Math.max(8,Math.min(Number(x)||0,vw-w-8));
    var ny=Math.max(8,Math.min(Number(y)||0,vh-h-8));
    menu.style.left=nx+"px";
    menu.style.top=ny+"px";
    ctxMenu.open=true;
}
function selectFromCanvasTarget(target){
    if(!target||!canvas||!canvas.contains(target))return false;
    if(target.closest&&target.closest(".carousel-live-editor"))return false;
    var elNode=target.closest&&target.closest(".el[data-el-id]");
    if(elNode){
        var eid=String(elNode.getAttribute("data-el-id")||"");
        var sid=String(elNode.getAttribute("data-s")||"");
        var rid=String(elNode.getAttribute("data-r")||"");
        var cid=String(elNode.getAttribute("data-c")||"");
        var scope=String(elNode.getAttribute("data-scope")||"column");
        if(eid!==""&&sid!==""){
            state.carouselSel=null;
            state.sel=(scope==="section")?{k:"el",scope:"section",s:sid,e:eid}:{k:"el",s:sid,r:rid,c:cid,e:eid};
            return true;
        }
    }
    var colNode=target.closest&&target.closest(".col[data-col-id]");
    if(colNode){
        var ccid=String(colNode.getAttribute("data-col-id")||"");
        var csid=String(colNode.getAttribute("data-s")||"");
        var crid=String(colNode.getAttribute("data-r")||"");
        if(ccid!==""&&csid!==""&&crid!==""){
            state.carouselSel=null;
            state.sel={k:"col",s:csid,r:crid,c:ccid};
            return true;
        }
    }
    var rowNode=target.closest&&target.closest(".row[data-row-id]");
    if(rowNode){
        var rrid=String(rowNode.getAttribute("data-row-id")||"");
        var rsid=String(rowNode.getAttribute("data-s")||"");
        if(rrid!==""&&rsid!==""){
            state.carouselSel=null;
            state.sel={k:"row",s:rsid,r:rrid};
            return true;
        }
    }
    var secNode=target.closest&&target.closest(".sec[data-sec-id]");
    if(secNode){
        var ssid=String(secNode.getAttribute("data-sec-id")||"");
        if(ssid!==""){
            state.carouselSel=null;
            state.sel={k:"sec",s:ssid};
            return true;
        }
    }
    return false;
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
function selectedElementMoveContext(){
    if(state.carouselSel){
        const cs=state.carouselSel;
        if(!cs||cs.k!=="el")return null;
        const parent=selectedCarouselParent();
        if(!parent||parent.type!=="carousel")return null;
        parent.settings=parent.settings||{};
        const slides=ensureCarouselSlides(parent.settings);
        let slide=slides.find(s=>s.id===cs.slideId);
        if(!slide){
            const active=Number(parent.settings.activeSlide)||0;
            slide=slides[active]||slides[0]||null;
        }
        if(!slide)return null;
        const rw=(slide.rows||[]).find(r=>r.id===cs.rowId);
        const cl=rw?((rw.columns||[]).find(c=>c.id===cs.colId)):null;
        if(!cl)return null;
        cl.elements=Array.isArray(cl.elements)?cl.elements:[];
        const index=cl.elements.findIndex(i=>i.id===cs.elId);
        if(index<0)return null;
        return {list:cl.elements,index:index};
    }
    const x=state.sel;
    if(!x||x.k!=="el")return null;
    
    // For section-level elements, work with rootItems for consistency
    if(x.scope==="section"){
        const rs=rootItems();
        // Find the element in root items
        const index=rs.findIndex(i=>i&&String(i.kind||"").toLowerCase()==="el"&&String(i.id||"")===String(x.e));
        if(index<0)return null;
        // Return the root items array
        return {list:rs,index:index};
    }
    
    // For column elements, work with the column's elements
    const c=col(x.s,x.r,x.c);
    if(!c)return null;
    c.elements=Array.isArray(c.elements)?c.elements:[];
    const index=c.elements.findIndex(i=>i.id===x.e);
    if(index<0)return null;
    return {list:c.elements,index:index};
}
function moveSelectedElement(offset){
    const ctx=selectedElementMoveContext();
    if(!ctx)return false;
    const to=ctx.index+offset;
    if(to<0||to>=ctx.list.length)return false;
    saveToHistory();
    const item=ctx.list[ctx.index];
    ctx.list.splice(ctx.index,1);
    ctx.list.splice(to,0,item);
    if(state.carouselSel&&state.carouselSel.k==="el")state.carouselSel.elId=item.id;
    if(state.sel&&state.sel.k==="el")state.sel.e=item.id;
    render();
    return true;
}
function selectedStructureMoveContext(){
    if(state.carouselSel)return null;
    const x=state.sel;
    if(!x)return null;
    if(x.k==="sec"){
        const sctx=sectionRootContext(x.s);
        if(sctx&&sctx.index>=0){
            return {list:rootItems(),index:sctx.index,kind:"sec"};
        }
        const secs=Array.isArray(state.layout.sections)?state.layout.sections:[];
        const idx=secs.findIndex(s=>String((s&&s.id)||"")===String(x.s||""));
        if(idx>=0)return {list:secs,index:idx,kind:"sec"};
        return null;
    }
    if(x.k==="row"){
        const sctx=sectionRootContext(x.s);
        if(sctx&&sctx.isWrap&&sctx.root&&String(sctx.root.kind||"").toLowerCase()==="row"&&sctx.index>=0){
            return {list:rootItems(),index:sctx.index,kind:"row"};
        }
        const s=sec(x.s);if(!s)return null;
        s.rows=Array.isArray(s.rows)?s.rows:[];
        const idx=s.rows.findIndex(r=>String((r&&r.id)||"")===String(x.r||""));
        if(idx>=0)return {list:s.rows,index:idx,kind:"row"};
        return null;
    }
    if(x.k==="col"){
        const sctx=sectionRootContext(x.s);
        if(sctx&&sctx.isWrap&&sctx.root&&String(sctx.root.kind||"").toLowerCase()==="column"&&sctx.index>=0){
            return {list:rootItems(),index:sctx.index,kind:"col"};
        }
        const r=row(x.s,x.r);if(!r)return null;
        r.columns=Array.isArray(r.columns)?r.columns:[];
        const idx=r.columns.findIndex(c=>String((c&&c.id)||"")===String(x.c||""));
        if(idx>=0)return {list:r.columns,index:idx,kind:"col"};
        return null;
    }
    return null;
}
function moveSelectedStructure(offset){
    const ctx=selectedStructureMoveContext();
    if(!ctx)return false;
    const to=ctx.index+offset;
    if(to<0||to>=ctx.list.length)return false;
    saveToHistory();
    const item=ctx.list[ctx.index];
    ctx.list.splice(ctx.index,1);
    ctx.list.splice(to,0,item);
    if(state.sel){
        if(ctx.kind==="sec")state.sel={k:"sec",s:item.id};
        else if(ctx.kind==="row")state.sel={k:"row",s:state.sel.s,r:item.id};
        else if(ctx.kind==="col")state.sel={k:"col",s:state.sel.s,r:state.sel.r,c:item.id};
    }
    render();
    return true;
}
function moveSelectedBySelection(offset){
    if(moveSelectedElement(offset))return true;
    if(moveSelectedStructure(offset))return true;
    return false;
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
    return ["section","heading","text","image","video","button","icon","spacer","menu","form","row","column"].indexOf(type)>=0;
}
function normalizeCarouselDropType(type){
    var t=String(type||"").toLowerCase();
    if(t==="col")return "column";
    if(t==="carousel")return "";
    return t;
}
function renderCarouselPreviewItem(item,onDelete,onSelect,isSelected){
    var type=(item&&item.type)||"text";
    var wrap=document.createElement("div");
    wrap.className="builder-carousel-item";
    wrap.style.position="relative";
    if(isSelected){
        wrap.style.outline="2px solid #240E35";
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
        const wrapStyle="position:relative;width:100%;min-height:200px;padding-top:56.25%;background:#240E35;border-radius:8px;overflow:hidden;box-sizing:border-box;display:flex;align-items:center;justify-content:center;pointer-events:none;";
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
        b.style.background=((item&&item.style&&item.style.backgroundColor)||"#240E35");
        b.style.color=((item&&item.style&&item.style.color)||"#fff");
        b.oninput=()=>{item.content=b.innerHTML||"";};
        wrap.appendChild(b);
    }else if(type==="icon"){
        var iset=(item&&item.settings)||{};
        var iStyle=sanitizeIconStyle(iset.iconStyle||"solid");
        var iName=sanitizeIconName(iset.iconName||"star");
        var iconNode=document.createElement("i");
        iconNode.className=iconClassName(iName,iStyle);
        iconNode.style.fontSize=((item&&item.style&&item.style.fontSize)||"36px");
        iconNode.style.color=((item&&item.style&&item.style.color)||"#2E1244");
        wrap.style.display="flex";
        wrap.style.justifyContent=((iset.alignment||"center")==="right")?"flex-end":((iset.alignment||"center")==="left"?"flex-start":"center");
        wrap.appendChild(iconNode);
    }else if(type==="spacer"){
        var sp=document.createElement("div");
        sp.style.height=((item&&item.style&&item.style.height)||"24px");
        sp.style.background="repeating-linear-gradient(90deg,#F3EEF7,#F3EEF7 8px,#E6E1EF 8px,#E6E1EF 16px)";
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
        var formFields=normalizeFormFields(item&&item.settings&&item.settings.fields,false);
        var fset=(item&&item.settings)||{};
        var labelColor=String(fset.labelColor||"#240E35");
        var placeholderColor=String(fset.placeholderColor||"#94a3b8");
        var buttonBg=String(fset.buttonBgColor||"#240E35");
        var buttonTextColor=String(fset.buttonTextColor||"#ffffff");
        var buttonAlign=String(fset.buttonAlign||"left");
        var buttonWeight=(fset.buttonBold===true)?"700":"400";
        var buttonStyle=(fset.buttonItalic===true)?"italic":"normal";
        var buttonJustify=(buttonAlign==="right")?"flex-end":(buttonAlign==="center"?"center":"flex-start");
        var html="";
        formFields.forEach(function(ff){
            html+='<label style="display:block;margin-bottom:4px;color:'+labelColor+';">'+String(ff.label||"Field").replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</label>';
            html+='<input class="fb-form-input" type="text" placeholder="'+String(ff.placeholder||ff.label||"Field").replace(/"/g,'&quot;')+'" style="--fb-ph-color:'+placeholderColor+';width:100%;padding:8px;border:1px solid #E6E1EF;border-radius:8px;margin-bottom:8px;">';
        });
        html+='<div style="display:flex;justify-content:'+buttonJustify+';"><button type="button" style="padding:8px 12px;border:0;border-radius:8px;background:'+buttonBg+';color:'+buttonTextColor+';font-weight:'+buttonWeight+';font-style:'+buttonStyle+';">'+(((item&&item.content)||"Submit"))+'</button></div>';
        fm.innerHTML=html;
        wrap.appendChild(fm);
    }else if(type==="carousel"){
        var nested=document.createElement("div");
        nested.style.padding="12px";
        nested.style.border="1px dashed #E7D8F0";
        nested.style.borderRadius="8px";
        nested.style.color="#240E35";
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
function createDefaultColumn(){return {id:uid("col"),style:{flex:"1 1 240px",height:"120px",minHeight:"120px"},elements:[]};}
function createDefaultSection(){return {id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff",minHeight:"30vh"},settings:{contentWidth:"full"},elements:[],rows:[]};}
function createRootItem(type){
    if(type==="section")return Object.assign({kind:"section"},createDefaultSection());
    if(type==="row")return Object.assign({kind:"row"},createDefaultRow());
    if(type==="column")return Object.assign({kind:"column"},createDefaultColumn());
    const it=createDefaultElement(type);
    return it?Object.assign({kind:"el"},it):null;
}
function createDefaultElement(type){
    const d={heading:{content:"Heading",style:{fontSize:"32px",color:"#000000",position:"absolute"},settings:{positionMode:"absolute"}},text:{content:"Text",style:{fontSize:"16px",color:"#000000",position:"absolute"},settings:{positionMode:"absolute"}},menu:{content:"",style:{fontSize:"16px",width:"400px",position:"absolute"},settings:{positionMode:"absolute",items:[{label:"Home",url:"#",newWindow:false,hasSubmenu:false},{label:"Contact",url:"/contact",newWindow:false,hasSubmenu:false}],itemGap:13,activeIndex:0,menuAlign:"left",underlineColor:""}},carousel:{content:"",style:{width:"200px",height:"200px",padding:"0px",position:"absolute"},settings:{positionMode:"absolute",slides:[defaultCarouselSlide("Slide #1")],activeSlide:0,vAlign:"center",alignment:"left",showArrows:true,slideshowMode:"manual",controlsColor:"#64748b",arrowColor:"#ffffff",fixedWidth:200,fixedHeight:200}},image:{content:"",style:{width:"300px",position:"absolute"},settings:{positionMode:"absolute",src:"",alt:"Image",alignment:"left"}},button:{content:"Click Me",style:{backgroundColor:"#240E35",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center",position:"absolute"},settings:{positionMode:"absolute",actionType:"next_step",actionStepSlug:"",link:"#"}},icon:{content:"",style:{fontSize:"36px",color:"#2E1244",padding:"0px",borderRadius:"0px",position:"absolute"},settings:{positionMode:"absolute",iconName:"star",iconStyle:"solid",alignment:"center",link:""}},form:{content:"Submit",style:{width:"350px",position:"absolute"},settings:{positionMode:"absolute",alignment:"left",width:"350px",buttonAlign:"left",buttonBold:false,buttonItalic:false,labelColor:"#240E35",placeholderColor:"#94a3b8",buttonBgColor:"#240E35",buttonTextColor:"#ffffff",fields:[{type:"text",label:"First name",placeholder:"First name",required:false}]}},video:{content:"",style:{width:"400px",position:"absolute"},settings:{positionMode:"absolute",src:"",alignment:"left"}},spacer:{content:"",style:{height:"24px",width:"200px",position:"absolute"},settings:{positionMode:"absolute"}}}[type]||null;
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
        if(!isStructureComponent(type)){
            var freeEls=rs.filter(function(r){return String((r&&r.kind)||"").toLowerCase()==="el";});
            autoPlaceElement(rootIt,{elements:freeEls});
        }
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    const pRootCtx=sectionRootContext(p.s);
    if(pRootCtx.isWrap||pRootCtx.isFreeform){
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        if(!isStructureComponent(type)){
            var freeEls2=rs.filter(function(r){return String((r&&r.kind)||"").toLowerCase()==="el";});
            autoPlaceElement(rootIt,{elements:freeEls2});
        }
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    if(type==="section"){rs.push(createRootItem("section"));syncSectionsFromRoot();return;}
    let s=sec(p.s)||state.layout.sections[0];
    if(!s){
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        if(!isStructureComponent(type)){
            var freeEls3=rs.filter(function(r){return String((r&&r.kind)||"").toLowerCase()==="el";});
            autoPlaceElement(rootIt,{elements:freeEls3});
        }
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    s.elements=Array.isArray(s.elements)?s.elements:[];
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
            autoPlaceElement(itNoGrid,{elements:s.elements});
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
        autoPlaceElement(itNoGrid,{elements:s.elements});
        s.elements.push(itNoGrid);
        state.sel={k:"el",scope:"section",s:s.id,e:itNoGrid.id};
        return;
    }
    if(!canAddElementToColumn(s.id,r.id,c.id,type))return;
    const it=createDefaultElement(type);
    if(!it)return;
    autoPlaceElement(it,c);
    c.elements.push(it);
    state.sel={k:"el",s:s.id,r:r.id,c:c.id,e:it.id};
}

function dropPlacement(ev,node){
    var rect=node.getBoundingClientRect();
    var y=Number(ev.clientY)||0;
    return y<(rect.top+rect.height/2)?"before":"after";
}

function isStructureComponent(type){
    var t=String(type||"").toLowerCase();
    return t==="section"||t==="row"||t==="column";
}

const freeDropGuides={host:null,v:null,h:null};
const freeDropSnapThreshold=10;

function clearFreeDropGuides(){
    if(freeDropGuides.v)freeDropGuides.v.style.display="none";
    if(freeDropGuides.h)freeDropGuides.h.style.display="none";
    freeDropGuides.host=null;
}

function ensureFreeDropGuides(host){
    if(!host)return null;
    if(getComputedStyle(host).position==="static")host.style.position="relative";
    var v=host.querySelector(":scope > .fb-drop-guide-v");
    var h=host.querySelector(":scope > .fb-drop-guide-h");
    if(!v){
        v=document.createElement("div");
        v.className="fb-drop-guide-v";
        host.appendChild(v);
    }
    if(!h){
        h=document.createElement("div");
        h.className="fb-drop-guide-h";
        host.appendChild(h);
    }
    freeDropGuides.host=host;
    freeDropGuides.v=v;
    freeDropGuides.h=h;
    return {v:v,h:h};
}

function computeFreeDropPosition(ev,host){
    if(!host)return {x:0,y:0,snapX:false,snapY:false};
    var rect=host.getBoundingClientRect();
    var x=(Number(ev.clientX)||0)-rect.left;
    var y=(Number(ev.clientY)||0)-rect.top;
    var centerX=rect.width/2;
    var centerY=rect.height/2;
    var snapX=Math.abs(x-centerX)<=freeDropSnapThreshold;
    var snapY=Math.abs(y-centerY)<=freeDropSnapThreshold;
    var px=snapX?centerX:x;
    var py=snapY?centerY:y;
    px=Math.max(0,px);
    py=Math.max(0,py);
    return {x:Math.round(px),y:Math.round(py),snapX:snapX,snapY:snapY};
}

function showFreeDropGuides(ev,host){
    if(!host)return;
    var guides=ensureFreeDropGuides(host);
    if(!guides)return;
    var pos=computeFreeDropPosition(ev,host);
    if(pos.snapX){
        guides.v.style.left=pos.x+"px";
        guides.v.style.display="block";
    }else{
        guides.v.style.display="none";
    }
    if(pos.snapY){
        guides.h.style.top=pos.y+"px";
        guides.h.style.display="block";
    }else{
        guides.h.style.display="none";
    }
}

function estimateNewElementHeight(type,colNode){
    var t=String(type||"").toLowerCase();
    if(t==="heading")return 78;
    if(t==="text")return 64;
    if(t==="button")return 62;
    if(t==="icon")return 72;
    if(t==="image")return 56;
    if(t==="video")return 180;
    if(t==="form")return 240;
    if(t==="menu")return 70;
    if(t==="spacer")return 36;
    if(t==="carousel")return 220;
    var fallback=72;
    if(!colNode)return fallback;
    try{
        var probeItem=createDefaultElement(t);
        if(!probeItem)return fallback;
        var probe=document.createElement("div");
        probe.style.position="absolute";
        probe.style.left="-10000px";
        probe.style.top="-10000px";
        probe.style.visibility="hidden";
        probe.style.pointerEvents="none";
        probe.style.width=Math.max(120,(Number(colNode.clientWidth)||260)-12)+"px";
        var rendered=renderElement(probeItem,{s:"__m__",r:"__m__",c:"__m__",scope:"section"});
        probe.appendChild(rendered);
        document.body.appendChild(probe);
        var mb=0;
        try{mb=parseFloat((window.getComputedStyle(rendered).marginBottom)||"0")||0;}catch(_e){mb=0;}
        var measured=(Number(rendered.offsetHeight)||0)+mb;
        probe.remove();
        if(measured>0)return measured;
    }catch(_e){}
    return fallback;
}

function showBuilderToast(message,type){
    var variant=(type==="success")?"success":"error";
    var iconClass=variant==="success"?"fa-check":"fa-times";
    var title=variant==="success"?"Success!":"Error!";
    var id="builderStatusToastContainer";
    var existing=document.getElementById(id);
    if(existing)existing.remove();
    var wrap=document.createElement("div");
    wrap.id=id;
    wrap.className="status-toast-container";
    wrap.innerHTML=
        '<div class="status-toast '+variant+'">'
        +'<i class="status-icon fas '+iconClass+'"></i>'
        +'<div><h4>'+title+'</h4><p>'+String(message||"").replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</p></div>'
        +'<button type="button" class="status-toast-close" aria-label="Close notification"><i class="fas fa-times-circle"></i></button>'
        +'</div>';
    document.body.appendChild(wrap);
    var closeBtn=wrap.querySelector(".status-toast-close");
    if(closeBtn)closeBtn.onclick=function(){wrap.remove();};
    setTimeout(function(){if(wrap&&wrap.parentNode)wrap.remove();},3000);
}

function notifyColumnFull(){
    showBuilderToast("Column is full. Increase column height first.","error");
}

function canAddElementToColumn(sid,rid,cid,type){
    var t=String(type||"").toLowerCase();
    if(t==="section"||t==="row"||t==="column")return true;
    return true;
    var colNode=canvas?canvas.querySelector('.col[data-col-id="'+String(cid||"")+'"]'):null;
    if(!colNode)return true;
    var colInner=colNode.querySelector(".col-inner");
    if(!colInner)return true;
    var capacity=Number(colNode.clientHeight)||0;
    if(capacity<=0)return true;
    var used=0;
    Array.from(colInner.children||[]).forEach(function(ch){
        var mb=0;
        try{
            mb=parseFloat((window.getComputedStyle(ch).marginBottom)||"0")||0;
        }catch(_e){mb=0;}
        var bottom=(Number(ch.offsetTop)||0)+(Number(ch.offsetHeight)||0)+mb;
        if(bottom>used)used=bottom;
    });
    if(used<=0)used=Number(colInner.scrollHeight)||0;
    var needed=estimateNewElementHeight(t,colNode);
    if((used+needed)>(capacity+2)){
        notifyColumnFull();
        return false;
    }
    return true;
}

function applyFreePlacementToElement(it,freePos){
    if(!it||!freePos)return;
    var rawX=Number(freePos.x)||0;
    var rawY=Number(freePos.y)||0;
    it.style=it.style||{};
    it.settings=it.settings||{};
    var elW=parseInt(it.style.width)||250;
    var elH=parseInt(it.style.height)||50;
    var x=Math.max(0,Math.round(rawX-elW/2));
    var y=Math.max(0,Math.round(rawY-elH/2));
    it.style.position="absolute";
    it.style.left=x+"px";
    it.style.top=y+"px";
    it.style.margin="0";
    it.settings.positionMode="absolute";
    it.settings.freeX=x;
    it.settings.freeY=y;
}

function autoPlaceElement(it,container){
    if(!it)return;
    it.style=it.style||{};
    it.settings=it.settings||{};
    it.style.position="absolute";
    it.style.margin="0";
    it.settings.positionMode="absolute";
    var existing=(container&&container.elements)||[];
    var fixedWidthTypes={image:300,video:400,form:350,menu:400,spacer:200,carousel:200};
    var fixedHeightTypes={carousel:200};
    var defaultWidths={heading:200,text:150,button:120,icon:60,image:300,video:400,form:350,menu:400,spacer:200,carousel:200};
    var dw=defaultWidths[it.type]||250;
    if(!it.style.width&&fixedWidthTypes[it.type])it.style.width=fixedWidthTypes[it.type]+"px";
    if(!it.style.height&&fixedHeightTypes[it.type])it.style.height=fixedHeightTypes[it.type]+"px";
    var elW=parseInt(it.style.width)||dw;
    var absEls=existing.filter(function(el){return el.id!==it.id&&el.settings&&el.settings.positionMode==="absolute";});
    var offset=absEls.length*30;
    var cx=20+offset;
    var cy=20+offset;
    it.style.left=cx+"px";
    it.style.top=cy+"px";
    it.settings.freeX=cx;
    it.settings.freeY=cy;
}

/* ─── Z-order / layering helpers ─── */
function getElZIndex(item){return Number(item&&item.style&&item.style.zIndex)||0;}
function getSiblingElements(ctx){
    if(!ctx)return [];
    if(ctx.scope==="section"){var s=sec(ctx.s);return s?(s.elements||[]):[];}
    var c=col(ctx.s,ctx.r,ctx.c);
    return c?(c.elements||[]):[];
}
function layerForward(item,ctx){item.style=item.style||{};item.style.zIndex=String(getElZIndex(item)+1);}
function layerBackward(item,ctx){item.style=item.style||{};item.style.zIndex=String(Math.max(0,getElZIndex(item)-1));}
function layerToFront(item,ctx){
    var siblings=getSiblingElements(ctx);
    var max=0;siblings.forEach(function(el){if(el.id!==item.id){var z=getElZIndex(el);if(z>max)max=z;}});
    item.style=item.style||{};item.style.zIndex=String(max+1);
}
function layerToBack(item,ctx){
    var siblings=getSiblingElements(ctx);
    var min=Infinity;siblings.forEach(function(el){if(el.id!==item.id){var z=getElZIndex(el);if(z<min)min=z;}});
    item.style=item.style||{};item.style.zIndex=String(Math.max(0,(min===Infinity?0:min)-1));
}

/* ─── Universal resize handles for all elements ─── */
function attachElResizeHandles(w,item){
    if(!w||!item)return;
    function parsePx(v){var n=Number(String(v||"0").replace("px","").trim());return isNaN(n)?0:n;}
    function clamp(n,mn,mx){return Math.max(mn,Math.min(mx,n));}
    var isCorner=function(h){return h==="nw"||h==="ne"||h==="sw"||h==="se";};
    var isScalable=function(){return item.type==="heading"||item.type==="text"||item.type==="button"||item.type==="icon";};
    function startResize(handle,e){
        e.preventDefault();
        e.stopPropagation();
        _autoSavePaused=true;
        var host=w.parentElement;
        var hostRect=host?host.getBoundingClientRect():null;
        var hostW=hostRect?hostRect.width:0;
        var hostH=hostRect?hostRect.height:0;
        var startW=w.offsetWidth||parsePx(item.style.width)||200;
        var startH=w.offsetHeight||parsePx(item.style.height)||100;
        var startX=parsePx(item.style.left)||Number(item.settings&&item.settings.freeX)||0;
        var startY=parsePx(item.style.top)||Number(item.settings&&item.settings.freeY)||0;
        var ratio=(startH>0)?(startW/startH):1;
        var startFontSize=parsePx(item.style&&item.style.fontSize)||32;
        var startPadT=0,startPadR=0,startPadB=0,startPadL=0;
        var startBR=parsePx(item.style&&item.style.borderRadius)||0;
        if(item.style&&item.style.padding){
            var pp=item.style.padding.split(/\s+/).map(function(v){return parsePx(v);});
            if(pp.length===1){startPadT=startPadR=startPadB=startPadL=pp[0];}
            else if(pp.length===2){startPadT=startPadB=pp[0];startPadR=startPadL=pp[1];}
            else if(pp.length>=4){startPadT=pp[0];startPadR=pp[1];startPadB=pp[2];startPadL=pp[3];}
        }
        var contentEl=w.querySelector("[data-editable],i.fa,i.fas,i.far,i.fab,svg");
        var sx=e.clientX,sy=e.clientY;
        var didSave=false;
        var doScale=isCorner(handle)&&isScalable();
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dx=ev.clientX-sx,dy=ev.clientY-sy;
            var nw=startW,nh=startH,nx=startX,ny=startY;
            if(handle==="se"){nw=clamp(startW+dx,30,2400);nh=clamp(nw/ratio,20,1800);}
            else if(handle==="ne"){nw=clamp(startW+dx,30,2400);nh=clamp(nw/ratio,20,1800);ny=startY+(startH-nh);}
            else if(handle==="sw"){nw=clamp(startW-dx,30,2400);nh=clamp(nw/ratio,20,1800);nx=startX+(startW-nw);}
            else if(handle==="nw"){nw=clamp(startW-dx,30,2400);nh=clamp(nw/ratio,20,1800);nx=startX+(startW-nw);ny=startY+(startH-nh);}
            else if(handle==="e"){nw=clamp(startW+dx,30,2400);}
            else if(handle==="w"){nw=clamp(startW-dx,30,2400);nx=startX+(startW-nw);}
            else if(handle==="s"){nh=clamp(startH+dy,20,1800);}
            else if(handle==="n"){nh=clamp(startH-dy,20,1800);ny=startY+(startH-nh);}
            if(hostW>0){
                if(nx<0){nw+=nx;nx=0;}
                if(nx+nw>hostW)nw=hostW-nx;
                if(nw<30)nw=Math.min(30,hostW);
            }
            if(hostH>0){
                if(ny<0){nh+=ny;ny=0;}
                if(ny+nh>hostH)nh=hostH-ny;
                if(nh<20)nh=Math.min(20,hostH);
            }
            item.style=item.style||{};item.settings=item.settings||{};
            item.style.width=Math.round(nw)+"px";
            item.style.height=Math.round(nh)+"px";
            item.style.left=Math.round(nx)+"px";
            item.style.top=Math.round(ny)+"px";
            item.settings.freeX=Math.round(nx);
            item.settings.freeY=Math.round(ny);
            w.style.width=item.style.width;
            w.style.height=item.style.height;
            w.style.left=item.style.left;
            w.style.top=item.style.top;
            if(doScale&&startW>0){
                var scale=nw/startW;
                var newFs=Math.max(8,Math.round(startFontSize*scale*10)/10);
                item.style.fontSize=newFs+"px";
                if(contentEl)contentEl.style.fontSize=newFs+"px";
                var newPad=[Math.round(startPadT*scale),Math.round(startPadR*scale),Math.round(startPadB*scale),Math.round(startPadL*scale)];
                item.style.padding=newPad[0]+"px "+newPad[1]+"px "+newPad[2]+"px "+newPad[3]+"px";
                if(contentEl)contentEl.style.padding=item.style.padding;
                if(startBR>0){
                    var newBR=Math.round(startBR*scale);
                    item.style.borderRadius=newBR+"px";
                    if(contentEl)contentEl.style.borderRadius=newBR+"px";
                }
            }
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
            renderCanvas();renderSettings();
            _autoSavePaused=false;
            queueAutoSave();
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    }
    var handles=[
        {cls:"el-rh el-rh-corner el-rh-nw",h:"nw"},
        {cls:"el-rh el-rh-corner el-rh-ne",h:"ne"},
        {cls:"el-rh el-rh-corner el-rh-sw",h:"sw"},
        {cls:"el-rh el-rh-corner el-rh-se",h:"se"},
        {cls:"el-rh el-rh-side el-rh-n",h:"n"},
        {cls:"el-rh el-rh-side el-rh-s",h:"s"},
        {cls:"el-rh el-rh-side el-rh-w",h:"w"},
        {cls:"el-rh el-rh-side el-rh-e",h:"e"}
    ];
    handles.forEach(function(def){
        var d=document.createElement("div");
        d.className=def.cls;
        d.addEventListener("click",function(ev){ev.preventDefault();ev.stopPropagation();});
        d.addEventListener("mousedown",function(ev){startResize(def.h,ev);});
        w.appendChild(d);
    });
}

/* ─── Canva-style drag-to-move system ─── */
const elDrag={active:false,el:null,item:null,ctx:null,startX:0,startY:0,origX:0,origY:0,host:null,moved:false,snapGuides:[],justFinished:false};
const DRAG_THRESHOLD=4;
const SNAP_THRESHOLD=6;

function getColumnSiblingRects(host,excludeId){
    var rects=[];
    if(!host)return rects;
    var children=host.querySelectorAll(".el.el--abs");
    var hostRect=host.getBoundingClientRect();
    for(var i=0;i<children.length;i++){
        var ch=children[i];
        var eid=ch.getAttribute("data-el-id");
        if(eid===excludeId)continue;
        var cr=ch.getBoundingClientRect();
        rects.push({
            left:cr.left-hostRect.left,
            top:cr.top-hostRect.top,
            right:cr.left-hostRect.left+cr.width,
            bottom:cr.top-hostRect.top+cr.height,
            cx:cr.left-hostRect.left+cr.width/2,
            cy:cr.top-hostRect.top+cr.height/2,
            w:cr.width,h:cr.height
        });
    }
    return rects;
}

function clearSnapGuides(){
    elDrag.snapGuides.forEach(function(g){if(g.parentNode)g.parentNode.removeChild(g);});
    elDrag.snapGuides=[];
}

function addSnapGuide(host,orient,pos){
    var g=document.createElement("div");
    g.className="fb-snap-guide fb-snap-guide-"+orient;
    g.style.display="block";
    if(orient==="v")g.style.left=Math.round(pos)+"px";
    else g.style.top=Math.round(pos)+"px";
    host.appendChild(g);
    elDrag.snapGuides.push(g);
}

function computeSnap(newX,newY,elW,elH,hostW,hostH,siblings){
    var snapX=null,snapY=null;
    var guidesV=[],guidesH=[];
    var elCx=newX+elW/2, elCy=newY+elH/2;
    var elRight=newX+elW, elBottom=newY+elH;
    var hostCx=hostW/2, hostCy=hostH/2;
    var best=SNAP_THRESHOLD+1;

    function tryV(val,target){var d=Math.abs(val-target);if(d<SNAP_THRESHOLD&&d<best){best=d;return true;}return false;}

    best=SNAP_THRESHOLD+1;
    if(tryV(elCx,hostCx)){snapX=hostCx-elW/2;guidesV=[hostCx];}
    best=SNAP_THRESHOLD+1;
    if(tryV(elCy,hostCy)){snapY=hostCy-elH/2;guidesH=[hostCy];}

    siblings.forEach(function(sib){
        if(snapX===null){
            if(Math.abs(elCx-sib.cx)<SNAP_THRESHOLD){snapX=sib.cx-elW/2;guidesV.push(sib.cx);}
            else if(Math.abs(newX-sib.left)<SNAP_THRESHOLD){snapX=sib.left;guidesV.push(sib.left);}
            else if(Math.abs(elRight-sib.right)<SNAP_THRESHOLD){snapX=sib.right-elW;guidesV.push(sib.right);}
            else if(Math.abs(newX-sib.right)<SNAP_THRESHOLD){snapX=sib.right;guidesV.push(sib.right);}
            else if(Math.abs(elRight-sib.left)<SNAP_THRESHOLD){snapX=sib.left-elW;guidesV.push(sib.left);}
        }
        if(snapY===null){
            if(Math.abs(elCy-sib.cy)<SNAP_THRESHOLD){snapY=sib.cy-elH/2;guidesH.push(sib.cy);}
            else if(Math.abs(newY-sib.top)<SNAP_THRESHOLD){snapY=sib.top;guidesH.push(sib.top);}
            else if(Math.abs(elBottom-sib.bottom)<SNAP_THRESHOLD){snapY=sib.bottom-elH;guidesH.push(sib.bottom);}
            else if(Math.abs(newY-sib.bottom)<SNAP_THRESHOLD){snapY=sib.bottom;guidesH.push(sib.bottom);}
            else if(Math.abs(elBottom-sib.top)<SNAP_THRESHOLD){snapY=sib.top-elH;guidesH.push(sib.top);}
        }
    });

    return {x:snapX,y:snapY,guidesV:guidesV,guidesH:guidesH};
}

function startElDrag(e,w,item,ctx){
    if(e.button!==0)return;
    if(e.target.closest&&e.target.closest(".carousel-live-editor")&&e.target.closest("button"))return;
    if(e.target.closest&&e.target.closest(".el-rh"))return;
    if(state.editingEl===item.id){
        if(e.target.closest&&(e.target.closest("[contenteditable='true']")||e.target.closest("input")||e.target.closest("textarea")||e.target.closest("select")))return;
    }
    e.preventDefault();
    state.editingEl=null;
    state.carouselSel=null;
    state.sel=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};

    var host=w.parentElement;
    if(!host)return;
    var hostRect=host.getBoundingClientRect();
    var elRect=w.getBoundingClientRect();

    elDrag.el=w;
    elDrag.item=item;
    elDrag.ctx=ctx;
    elDrag.startX=e.clientX;
    elDrag.startY=e.clientY;
    elDrag.moved=false;
    elDrag.host=host;

    if(item.settings&&item.settings.positionMode==="absolute"){
        elDrag.origX=parseFloat(item.style.left)||0;
        elDrag.origY=parseFloat(item.style.top)||0;
    }else{
        elDrag.origX=elRect.left-hostRect.left;
        elDrag.origY=elRect.top-hostRect.top;
    }
    elDrag.active=true;
    _autoSavePaused=true;
}

document.addEventListener("mousemove",function(e){
    if(!elDrag.active)return;
    var dx=e.clientX-elDrag.startX;
    var dy=e.clientY-elDrag.startY;
    if(!elDrag.moved&&Math.abs(dx)<DRAG_THRESHOLD&&Math.abs(dy)<DRAG_THRESHOLD)return;
    if(!elDrag.moved){
        elDrag.moved=true;
        saveToHistory();
        elDrag.el.classList.add("el--dragging");
    }

    var item=elDrag.item;
    if(!(item.settings&&item.settings.positionMode==="absolute")){
        var currentW=elDrag.el.offsetWidth;
        item.style=item.style||{};
        item.settings=item.settings||{};
        item.style.position="absolute";
        item.style.margin="0";
        if(!item.style.width&&currentW>0)item.style.width=currentW+"px";
        item.settings.positionMode="absolute";
        elDrag.el.style.position="absolute";
        elDrag.el.classList.add("el--abs");
        elDrag.el.style.marginBottom="0";
        elDrag.el.style.margin="0";
        if(currentW>0)elDrag.el.style.width=currentW+"px";
    }

    var rawX=elDrag.origX+dx;
    var rawY=elDrag.origY+dy;
    rawX=Math.max(0,rawX);
    rawY=Math.max(0,rawY);

    var hostRect=elDrag.host.getBoundingClientRect();
    var elW=elDrag.el.offsetWidth;
    var elH=elDrag.el.offsetHeight;
    var siblings=getColumnSiblingRects(elDrag.host,item.id);
    var snap=computeSnap(rawX,rawY,elW,elH,hostRect.width,hostRect.height,siblings);

    var finalX=(snap.x!==null)?snap.x:rawX;
    var finalY=(snap.y!==null)?snap.y:rawY;
    var maxX=Math.max(0,hostRect.width-elW);
    var maxY=Math.max(0,hostRect.height-elH);

    // Auto-grow freeform section height while dragging near the bottom edge.
    var secNode=elDrag.host.closest&&elDrag.host.closest(".sec");
    if(secNode){
        var secId=secNode.getAttribute("data-sec-id");
        var sObj=secId?sec(secId):null;
        var isFreeform=!!(sObj&&sObj.__freeformCanvas);
        if(isFreeform){
            var bottomNeeded=(finalY+elH+20);
            if(bottomNeeded>hostRect.height){
                var nextH=Math.max(bottomNeeded,hostRect.height+40);
                elDrag.host.style.minHeight=Math.round(nextH)+"px";
                secNode.style.minHeight=elDrag.host.style.minHeight;
                sObj.style=sObj.style||{};
                sObj.style.minHeight=elDrag.host.style.minHeight;
                hostRect=elDrag.host.getBoundingClientRect();
                maxY=Math.max(0,hostRect.height-elH);
            }
        }
    }
    if(finalX<0)finalX=0;
    if(finalY<0)finalY=0;
    if(finalX>maxX)finalX=maxX;
    if(finalY>maxY)finalY=maxY;

    clearSnapGuides();
    snap.guidesV.forEach(function(gx){addSnapGuide(elDrag.host,"v",gx);});
    snap.guidesH.forEach(function(gy){addSnapGuide(elDrag.host,"h",gy);});

    elDrag.el.style.left=Math.round(finalX)+"px";
    elDrag.el.style.top=Math.round(finalY)+"px";

    item.style.left=Math.round(finalX)+"px";
    item.style.top=Math.round(finalY)+"px";
    item.settings.freeX=Math.round(finalX);
    item.settings.freeY=Math.round(finalY);
});

document.addEventListener("mouseup",function(e){
    if(!elDrag.active)return;
    clearSnapGuides();
    if(elDrag.el)elDrag.el.classList.remove("el--dragging");
    state.editingEl=null;
    elDrag.justFinished=true;
    elDrag.active=false;
    elDrag.el=null;
    elDrag.item=null;
    elDrag.ctx=null;
    elDrag.host=null;
    elDrag.moved=false;
    render();
    _autoSavePaused=false;
    queueAutoSave();
    setTimeout(function(){elDrag.justFinished=false;},0);
});
/* ─── end drag-to-move system ─── */

function addComponentAt(type,target,place){
    var freePlacement=(place&&typeof place==="object"&&place.mode==="free")?place:null;
    var placeInside=(place==="inside")||!!freePlacement;
    place=place==="before"?"before":"after";
    saveToHistory();
    var t=target||{};
    ensureRootModel();
    const rs=rootItems();
    state.layout.sections=Array.isArray(state.layout.sections)?state.layout.sections:[];
    if(!t||!t.k){
        var rootNew=createRootItem(type);
        if(!rootNew)return false;
        if(!isStructureComponent(type)){
            if(freePlacement)applyFreePlacementToElement(rootNew,freePlacement);
            else{var freeElsAt=rs.filter(function(r){return String((r&&r.kind)||"").toLowerCase()==="el";});autoPlaceElement(rootNew,{elements:freeElsAt});}
        }
        rs.push(rootNew);
        syncSectionsFromRoot();
        return true;
    }
    var tRootCtx=sectionRootContext(t.s);
    var isNestedGridTarget=(t.k==="row"||t.k==="col"||(t.k==="el"&&!!t.c));
    if((tRootCtx.isWrap||tRootCtx.isFreeform)&&tRootCtx.index>=0&&!isNestedGridTarget){
        var wrapInsert=createRootItem(type);
        if(!wrapInsert)return false;
        if(!isStructureComponent(type)){
            if(freePlacement)applyFreePlacementToElement(wrapInsert,freePlacement);
            else{var freeElsAt2=rs.filter(function(r){return String((r&&r.kind)||"").toLowerCase()==="el";});autoPlaceElement(wrapInsert,{elements:freeElsAt2});}
        }
        var wrapIdx=(place==="before"?tRootCtx.index:tRootCtx.index+1);
        rs.splice(Math.max(0,Math.min(wrapIdx,rs.length)),0,wrapInsert);
        syncSectionsFromRoot();
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
            if(freePlacement)applyFreePlacementToElement(secItem,freePlacement);
            s.elements.splice(Math.max(0,Math.min(secIdx,s.elements.length)),0,secItem);
            state.sel={k:"el",scope:"section",s:s.id,e:secItem.id};
            return true;
        }
        if(t.k==="el" && t.scope==="section"){
            var sFound=s.elements.findIndex(x=>x.id===t.e);
            var sIns=sFound>=0?(place==="before"?sFound:sFound+1):s.elements.length;
            var sItem=createDefaultElement(type);
            if(!sItem)return false;
            if(freePlacement)applyFreePlacementToElement(sItem,freePlacement);
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
    if(!canAddElementToColumn(s.id,r.id,c.id,type))return false;
    var eIdx=placeInside?c.elements.length:(place==="before"?0:c.elements.length);
    if(t.k==="el"){
        var ei=c.elements.findIndex(x=>x.id===t.e);
        if(ei>=0)eIdx=placeInside?c.elements.length:(place==="before"?ei:ei+1);
    }else if(t.k==="col"||t.k==="row"||t.k==="sec"){
        eIdx=placeInside?c.elements.length:(place==="before"?0:c.elements.length);
    }
    var it=createDefaultElement(type);
    if(!it)return false;
    if(freePlacement)applyFreePlacementToElement(it,freePlacement);
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
            var delSec=sec(x.s);
            if(delSec&&delSec.__freeformCanvas){
                var ri=rootItems();
                var rootIdx=ri.findIndex(function(r){return String((r&&r.kind)||"").toLowerCase()==="el"&&String(r.id||"")===String(x.e||"");});
                if(rootIdx>=0){ri.splice(rootIdx,1);didRootDelete=true;}
                else{delSec.elements=(delSec.elements||[]).filter(function(i){return i.id!==x.e;});}
            } else if(xRootCtx.isWrap&&xRootCtx.root&&String(xRootCtx.root.kind||"").toLowerCase()==="el"&&xRootCtx.index>=0){
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
    const isSelected=!!(state.sel&&state.sel.k==="el"&&state.sel.e===item.id);
    const isCarouselActive=!!(
        isSelected
        || (state.carouselSel && state.carouselSel.parent && state.carouselSel.parent.e===item.id)
    );
    const mediaClip="";
    w.setAttribute("data-el-type",String(item.type||"element"));
    w.setAttribute("data-el-id",String(item.id||""));
    w.setAttribute("data-s",String(ctx&&ctx.s||""));
    w.setAttribute("data-r",String(ctx&&ctx.r||""));
    w.setAttribute("data-c",String(ctx&&ctx.c||""));
    w.setAttribute("data-scope",String(ctx&&ctx.scope||"column"));
    w.setAttribute("data-outline-label",titleCase(String(item.type||"element")));
    if(item.type==="carousel")w.classList.add("el--carousel");
    if(item.type==="form")w.classList.add("el--form");
    if(item.type==="menu")w.classList.add("el--menu");
    if(item.type==="image")w.classList.add("el--image");
    if(item.type==="video")w.classList.add("el--video");
    if(item.type!=="button")styleApply(w,item.style||{});
    else if(item.style&&item.style.margin)w.style.margin=item.style.margin;
    if((item.settings&&item.settings.positionMode)==="absolute"||String((item.style&&item.style.position)||"").toLowerCase()==="absolute"){
        w.classList.add("el--abs");
        w.style.position="absolute";
        if(item.style&&item.style.left!==undefined)w.style.left=String(item.style.left);
        if(item.style&&item.style.top!==undefined)w.style.top=String(item.style.top);
        if(item.style&&item.style.width)w.style.width=String(item.style.width);
        if(item.style&&item.style.height)w.style.height=String(item.style.height);
        w.style.marginBottom="0";
        var hasZi=!!(item.style&&Object.prototype.hasOwnProperty.call(item.style,"zIndex"));
        var zi=Number(item.style&&item.style.zIndex)||0;
        if(hasZi)w.style.zIndex=String(zi);
    }
    if((item.type==="image"||item.type==="video")&&(item.style&&item.style.borderRadius)){
        w.style.setProperty("border-radius",String(item.style.borderRadius),"important");
    }
    var isEditing=!!(state.editingEl&&state.editingEl===item.id);
    if(isSelected){w.classList.add("sel");}
    if(isEditing)w.classList.add("el--editing");
    w.onmousedown=function(e){e.stopPropagation();startElDrag(e,w,item,ctx);};
    w.onclick=e=>{e.stopPropagation();if(!elDrag.active&&!elDrag.justFinished){if(state.editingEl&&state.editingEl!==item.id){state.editingEl=null;var oe=document.querySelector(".el--editing");if(oe){oe.classList.remove("el--editing");var ce=oe.querySelector("[data-editable]");if(ce){ce.contentEditable="false";ce.style.cursor="move";}}}state.carouselSel=null;state.sel=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};renderSettings();if(state.sel)showLeftPanel("settings");}};
    w.ondblclick=function(e){
        e.stopPropagation();
        if(item.type==="heading"||item.type==="text"||item.type==="button"){
            state.editingEl=item.id;
            var editable=w.querySelector("[data-editable]");
            if(editable){editable.contentEditable="true";editable.focus();try{var sel=window.getSelection();sel.selectAllChildren(editable);sel.collapseToEnd();}catch(ex){}}
            w.classList.add("el--editing");
        }
    };
    w.ondragover=e=>{
        e.preventDefault();
        e.stopPropagation();
        const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
        if(t&&!isStructureComponent(t)){
            var freeHost=w.parentElement||null;
            showFreeDropGuides(e,freeHost);
        }else{
            clearFreeDropGuides();
        }
    };
    w.ondragleave=e=>{
        e.stopPropagation();
        if(!w.contains(e.relatedTarget))clearFreeDropGuides();
    };
    w.ondrop=e=>{
        e.preventDefault();
        e.stopPropagation();
        if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
        const t=e.dataTransfer.getData("c");
        if(!t)return;
        state.carouselSel=null;
        var dropTarget=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};
        var place;
        if(!isStructureComponent(t)){
            var host=w.parentElement||w;
            var freePos=computeFreeDropPosition(e,host);
            place={mode:"free",x:freePos.x,y:freePos.y};
            dropTarget=ctx.scope==="section"?{k:"sec",s:ctx.s}:{k:"col",s:ctx.s,r:ctx.r,c:ctx.c};
        }else{
            place=dropPlacement(e,w);
        }
        clearFreeDropGuides();
        if(addComponentAt(t,dropTarget,place))render();
    };
    if(item.type==="heading"||item.type==="text"){const n=document.createElement(item.type==="heading"?"h2":"div");n.setAttribute("data-editable","1");n.contentEditable=isEditing?"true":"false";n.style.margin="0";n.style.overflowWrap="break-word";n.style.wordBreak="break-word";n.style.maxWidth="100%";n.style.cursor=isEditing?"text":"move";n.innerHTML=item.content||"";contentStyleApply(n,item.style||{});if(!(item.style&&item.style.color))n.style.color="#000000";n.oninput=()=>{item.content=n.innerHTML||"";queueAutoSave();};onRichTextKeys(n,()=>{item.content=n.innerHTML||"";queueAutoSave();});w.appendChild(n);}
    else if(item.type==="button"){
        var wb=(item.settings&&item.settings.widthBehavior)||"fluid",al=(item.settings&&item.settings.alignment)||((item.style&&item.style.textAlign)||"center");
        var wrapBg=(item.settings&&item.settings.containerBgColor)||"";
        w.classList.add(wb==="fill"?"el--button-fill":"el--button");
        w.style.display="flex";w.style.justifyContent=al==="right"?"flex-end":al==="center"?"center":"flex-start";
        w.style.backgroundColor=wrapBg||"";
        const b=document.createElement("button");b.type="button";b.setAttribute("data-editable","1");b.contentEditable=isEditing?"true":"false";b.style.cursor=isEditing?"text":"move";b.innerHTML=item.content||"Button";
        contentStyleApply(b,item.style||{});b.style.border="none";b.style.display=wb==="fill"?"flex":"inline-flex";b.style.width=wb==="fill"?"100%":"auto";b.style.alignItems="center";b.style.justifyContent="center";if(!(item.style&&item.style.backgroundColor))b.style.backgroundColor="#240E35";if(!(item.style&&item.style.color))b.style.color="#fff";if(!(item.style&&item.style.padding))b.style.padding="10px 18px";if(!(item.style&&item.style.borderRadius))b.style.borderRadius="999px";
        b.oninput=()=>{item.content=b.innerHTML||"";queueAutoSave();};onRichTextKeys(b,()=>{item.content=b.innerHTML||"";queueAutoSave();});w.appendChild(b);
    }
    else if(item.type==="icon"){
        item.settings=item.settings||{};
        var iconWrapAlign=item.settings.alignment||"center";
        w.style.display="flex";
        w.style.justifyContent=iconWrapAlign==="right"?"flex-end":iconWrapAlign==="left"?"flex-start":"center";
        w.style.alignItems="center";
        var iconName=sanitizeIconName(item.settings.iconName||"star");
        var iconStyle=sanitizeIconStyle(item.settings.iconStyle||"solid");
        var iconLink=String(item.settings.link||"").trim();
        var node=document.createElement(iconLink!==""?"a":"span");
        if(iconLink!==""){
            node.href=iconLink;
            node.addEventListener("click",function(e){e.preventDefault();});
        }
        node.style.display="inline-flex";
        node.style.alignItems="center";
        node.style.justifyContent="center";
        var i=document.createElement("i");
        i.className=iconClassName(iconName,iconStyle);
        i.style.fontSize=((item.style&&item.style.fontSize)||"36px");
        i.style.color=((item.style&&item.style.color)||"#2E1244");
        node.appendChild(i);
        w.innerHTML="";
        w.appendChild(node);
    }
    else if(item.type==="image"){
        var hasFixedH=!!(item.style&&String(item.style.height||"").trim()!=="");
        var _imgLoading=state.mediaLoading.has(item.id);
        if(item.settings&&item.settings.src){
            var imgWrap=document.createElement("div");
            imgWrap.style.position="relative";
            imgWrap.style.width="100%";
            if(hasFixedH){imgWrap.style.height="100%";}
            else{imgWrap.style.minHeight="80px";}
            var img=document.createElement("img");
            img.alt=String(item.settings.alt||"Image");
            img.style.display="block";
            img.style.maxWidth="100%";
            if(item.style&&item.style.borderRadius)img.style.borderRadius=String(item.style.borderRadius);
            if(hasFixedH){
                img.style.width="100%";
                img.style.height="100%";
                img.style.objectFit="cover";
            }else{
                img.style.height="auto";
                img.style.objectFit="contain";
                img.style.objectPosition="top center";
            }
            if(mediaClip)img.style.cssText+=(img.style.cssText&&img.style.cssText.slice(-1)!==";"?";":"")+mediaClip;
            var _imgOverlay=document.createElement("div");
            _imgOverlay.className="el-loading-overlay";
            _imgOverlay.innerHTML='<div class="el-loading-spinner"></div>';
            imgWrap.appendChild(img);
            imgWrap.appendChild(_imgOverlay);
            img.onload=function(){_imgOverlay.remove();if(!hasFixedH)imgWrap.style.minHeight="";};
            img.onerror=function(){_imgOverlay.remove();if(!hasFixedH)imgWrap.style.minHeight="";};
            img.src=String(item.settings.src||"");
            w.innerHTML="";
            w.appendChild(imgWrap);
        }else if(_imgLoading){
            w.innerHTML='<div style="position:relative;padding:30px 12px;border:1px dashed #94a3b8;border-radius:8px;width:100%;box-sizing:border-box;min-height:80px;"><div class="el-loading-overlay"><div class="el-loading-spinner"></div></div></div>';
        }else{
            w.innerHTML='<div style="padding:12px;border:1px dashed #94a3b8;border-radius:8px;width:100%;box-sizing:border-box;">Image placeholder</div>';
        }
    }
    else if(item.type==="form"){
        item.settings=item.settings||{};
        var fal=(item.settings.alignment)||"left";
        var fw=(item.style&&item.style.width)||(item.settings&&item.settings.width)||"100%";
        var flabelColor=String(item.settings.labelColor||"#240E35");
        var fplaceholderColor=String(item.settings.placeholderColor||"#94a3b8");
        var fbtnBg=String(item.settings.buttonBgColor||"#240E35");
        var fbtnColor=String(item.settings.buttonTextColor||"#ffffff");
        var fbtnAlign=String(item.settings.buttonAlign||"left");
        var fbtnJustify=fbtnAlign==="right"?"flex-end":(fbtnAlign==="center"?"center":"flex-start");
        var fbtnWeight=item.settings.buttonBold===true?"700":"400";
        var fbtnStyle=item.settings.buttonItalic===true?"italic":"normal";
        var flist=normalizeFormFields(item.settings.fields,false);
        item.settings.fields=flist;
        w.style.display="block";
        w.style.boxSizing="border-box";
        w.style.textAlign="left";
        w.style.width=fw;
        w.style.maxWidth="100%";
        if(fal==="center"){w.style.marginLeft="auto";w.style.marginRight="auto";}
        else if(fal==="right"){w.style.marginLeft="auto";w.style.marginRight="0";}
        else{w.style.marginLeft="0";w.style.marginRight="auto";}
        var formBox=document.createElement("div");
        formBox.style.width="100%";
        formBox.style.maxWidth="100%";
        formBox.style.boxSizing="border-box";
        formBox.style.textAlign="left";
        flist.forEach(function(f){
            var lbl=(f&&f.label)?String(f.label):"Field";
            var lab=document.createElement("label");
            lab.style.display="block";
            lab.style.marginBottom="4px";
            lab.style.color=flabelColor;
            lab.textContent=lbl;
            var inp=document.createElement("input");
            inp.disabled=true;
            inp.placeholder=String((f&&f.placeholder)||lbl);
            inp.className="fb-form-input";
            inp.style.setProperty("--fb-ph-color",fplaceholderColor);
            inp.style.width="100%";
            inp.style.padding="8px";
            inp.style.border="1px solid #E6E1EF";
            inp.style.borderRadius="8px";
            inp.style.marginBottom="8px";
            formBox.appendChild(lab);
            formBox.appendChild(inp);
        });
        var btnWrap=document.createElement("div");
        btnWrap.style.display="flex";
        btnWrap.style.justifyContent=fbtnJustify;
        var btn=document.createElement("button");
        btn.type="button";
        btn.className="fb-btn primary";
        btn.disabled=true;
        btn.textContent=(item.content||"Submit");
        btn.style.backgroundColor=fbtnBg;
        btn.style.color=fbtnColor;
        btn.style.fontWeight=fbtnWeight;
        btn.style.fontStyle=fbtnStyle;
        btnWrap.appendChild(btn);
        formBox.appendChild(btnWrap);
        w.innerHTML="";
        w.appendChild(formBox);
    }
    else if(item.type==="menu"){
        var ms=item.settings||{};
        var items=Array.isArray(ms.items)&&ms.items.length?ms.items:[{label:"Menu item",url:"#",newWindow:false,hasSubmenu:false}];
        var gap=Number(ms.itemGap);if(isNaN(gap))gap=13;
        var activeIdx=Number(ms.activeIndex);if(isNaN(activeIdx))activeIdx=0;
        var align=(ms.menuAlign||"left");
        var st=item.style||{};
        const ul=document.createElement("ul");
        ul.style.listStyle="none";ul.style.margin="0";ul.style.padding="0";
        ul.style.display="flex";ul.style.flexWrap="nowrap";ul.style.whiteSpace="nowrap";ul.style.gap=gap+"px";
        ul.style.justifyContent=align==="right"?"flex-end":align==="center"?"center":"flex-start";
        if(st.fontFamily)ul.style.fontFamily=st.fontFamily;
        if(st.fontSize)ul.style.fontSize=st.fontSize;
        if(st.lineHeight)ul.style.lineHeight=st.lineHeight;
        if(st.letterSpacing)ul.style.letterSpacing=st.letterSpacing;
        items.forEach((mi,idx)=>{
            const li=document.createElement("li");
            const a=document.createElement("a");
            a.href=(mi&&mi.url)||"#";
            a.textContent=(mi&&mi.label)||("Menu item "+(idx+1));
            if(mi&&mi.newWindow)a.target="_blank";
            a.style.color=(ms.textColor)||"#374151";
            a.style.textDecoration=ms.underlineColor?"underline":"none";
            if(ms.underlineColor)a.style.textDecorationColor=ms.underlineColor;
            a.style.textUnderlineOffset="3px";
            a.style.fontFamily=st.fontFamily||"inherit";
            a.style.fontSize=st.fontSize||"inherit";
            a.style.lineHeight=st.lineHeight||"inherit";
            a.style.letterSpacing=st.letterSpacing||"inherit";
            a.style.fontWeight=st.fontWeight||"inherit";
            a.style.fontStyle=st.fontStyle||"inherit";
            a.addEventListener("click",e=>e.preventDefault());
            li.appendChild(a);ul.appendChild(li);
        });
        w.innerHTML="";w.appendChild(ul);
    }
    else if(item.type==="carousel"){
        var cs=item.settings||{};
        var slides=ensureCarouselSlides(cs);
        var active=Number(cs.activeSlide);if(isNaN(active)||active<0||active>=slides.length)active=0;
        var slideshowMode=String(cs.slideshowMode||"manual").toLowerCase();
        if(slideshowMode!=="auto"&&slideshowMode!=="manual")slideshowMode="manual";
        var isAutoSlideshow=(slideshowMode==="auto");
        if(typeof cs.showArrows!=="boolean")cs.showArrows=true;
        if(isAutoSlideshow)cs.showArrows=false;
        var curSlide=slides[active]||slides[0]||defaultCarouselSlide("Slide #1");
        var parentSel={scope:ctx.scope||"column",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};
        function slideHasContent(slide){
            if(!slide||typeof slide!=="object")return false;
            var img=slide.image&&typeof slide.image==="object"?slide.image:{};
            return String(img.src||"").trim()!=="";
        }
        function getTargetSlideForImageInsert(insertOrder){
            var activeIdx=Number(cs.activeSlide);
            if(isNaN(activeIdx)||activeIdx<0||activeIdx>=slides.length)activeIdx=0;
            if((Number(insertOrder)||0)===0){
                var activeSlide=slides[activeIdx];
                if(activeSlide && !slideHasContent(activeSlide))return activeSlide;
            }
            if(slides.length===1 && !slideHasContent(slides[0])){
                return slides[0];
            }
            var s={id:uid("sld"),label:"Slide #"+(slides.length+1),image:{src:"",alt:"Image"}};
            slides.push(s);
            return s;
        }
        function addImageSlide(imageUrl,skipRender,insertOrder){
            var slide=getTargetSlideForImageInsert(insertOrder);
            if(!slide.id)slide.id=uid("sld");
            if(!slide.label||String(slide.label).trim()==="")slide.label="Slide #"+(slides.indexOf(slide)+1);
            slide.image={src:String(imageUrl||"").trim(),alt:"Image"};
            var slideIdx=slides.findIndex(function(sl){return String((sl&&sl.id)||"")===String(slide.id||"");});
            cs.activeSlide=(slideIdx>=0)?slideIdx:Math.max(0,slides.length-1);
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
                        addImageSlide(url,true,added);
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
        if(isNaN(fixedW)||fixedW<50)fixedW=200;
        if(isNaN(fixedH)||fixedH<50)fixedH=200;
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
        w.style.overflow="visible";
        w.style.marginLeft=carAlign==="right"?"auto":(carAlign==="center"?"auto":"0");
        w.style.marginRight=carAlign==="left"?"auto":(carAlign==="center"?"auto":"0");
        simpleWrap.style.minHeight=(fixedH+"px");
        simpleWrap.style.borderRadius="8px";
        simpleWrap.style.background="#ffffff";
        simpleWrap.style.color="#240E35";
        simpleWrap.style.border="1px solid #E6E1EF";
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
            emptyPickBtn.style.border="1px solid #E7D8F0";
            emptyPickBtn.style.background="#ffffff";
            emptyPickBtn.style.color="#2E1244";
            emptyPickBtn.style.cursor="pointer";
            emptyPickBtn.style.zIndex="4";
            emptyPickBtn.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();promptImageFilesForSlides();};
            simpleWrap.appendChild(emptyPickBtn);
        }
        if(slides.length>1 && !isAutoSlideshow && cs.showArrows!==false){
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
            prevBtn.style.border="1px solid #E6E1EF";
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
            nextBtn.style.border="1px solid #E6E1EF";
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
        if(slides.length>1){
            var dotsWrap=document.createElement("div");
            dotsWrap.style.position="absolute";
            dotsWrap.style.left="50%";
            dotsWrap.style.bottom="10px";
            dotsWrap.style.transform="translateX(-50%)";
            dotsWrap.style.display="flex";
            dotsWrap.style.alignItems="center";
            dotsWrap.style.gap="8px";
            dotsWrap.style.padding="4px 8px";
            dotsWrap.style.borderRadius="999px";
            dotsWrap.style.background="rgba(15, 23, 42, 0.35)";
            dotsWrap.style.backdropFilter="blur(2px)";
            dotsWrap.style.zIndex="3";
            slides.forEach(function(_unused,idx){
                var dot=document.createElement("button");
                dot.type="button";
                dot.style.width="8px";
                dot.style.height="8px";
                dot.style.borderRadius="999px";
                dot.style.border="0";
                dot.style.padding="0";
                dot.style.cursor="pointer";
                dot.style.background=(idx===active)?"#ffffff":"rgba(255,255,255,0.55)";
                dot.onclick=function(e){
                    e.preventDefault();e.stopPropagation();
                    saveToHistory();
                    cs.activeSlide=idx;
                    renderCanvas();
                    renderSettings();
                };
                dotsWrap.appendChild(dot);
            });
            simpleWrap.appendChild(dotsWrap);
        }
        if(isAutoSlideshow && slides.length>1){
            state._carAutoTimers=Array.isArray(state._carAutoTimers)?state._carAutoTimers:[];
            var tmr=setTimeout(function(){
                cs.activeSlide=(active+1)%slides.length;
                renderCanvas();
                renderSettings();
            },3000);
            state._carAutoTimers.push(tmr);
        }
        w.innerHTML="";
        w.appendChild(simpleWrap);
        if(isSelected)attachCarouselResizeHandles(w,item);
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
        wrap.style.color="#240E35";
        wrap.style.border="1px solid #E6E1EF";
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
        addImageSlideBtn.style.border="1px solid #E7D8F0";
        addImageSlideBtn.style.background="#ffffff";
        addImageSlideBtn.style.color="#2E1244";
        addImageSlideBtn.style.fontSize="20px";
        addImageSlideBtn.style.fontWeight="800";
        addImageSlideBtn.style.cursor="pointer";
        addImageSlideBtn.style.zIndex="4";
        addImageSlideBtn.style.display="none";
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
            rowBox.style.border=rowHasAnyElements?"0":"1px dashed #E7D8F0";
            rowBox.style.background=rowHasAnyElements?"transparent":"#ffffff";
            if(state.carouselSel && state.carouselSel.k==="row" && state.carouselSel.slideId===curSlide.id && state.carouselSel.rowId===rw.id){
                rowBox.style.outline="2px solid #240E35";
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
                colBox.style.border="1px dashed #E7D8F0";
                colBox.style.borderRadius="8px";
                colBox.style.padding="8px";
                colBox.style.position="relative";
                if(state.carouselSel && state.carouselSel.k==="col" && state.carouselSel.slideId===curSlide.id && state.carouselSel.rowId===rw.id && state.carouselSel.colId===cl.id){
                    colBox.style.outline="2px solid #240E35";
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
            emptyAdd.style.border="1px solid #E7D8F0";
            emptyAdd.style.background="#ffffff";
            emptyAdd.style.color="#2E1244";
            emptyAdd.style.cursor="pointer";
            emptyAdd.style.zIndex="4";
            emptyAdd.onclick=e=>{e.preventDefault();e.stopPropagation();saveToHistory();promptImageFilesForSlides();};
            wrap.appendChild(emptyAdd);
        }
        (function mountInlineCarouselResizeHandles(){
            var mk=function(mode){
                var h=document.createElement("div");
                h.title="Drag to resize";
                h.style.position="absolute";
                h.style.width="24px";
                h.style.height="24px";
                h.style.borderRadius="999px";
                h.style.border="2px solid #ffffff";
                h.style.background="#240E35";
                h.style.boxShadow="0 2px 6px rgba(15,23,42,.28)";
                h.style.pointerEvents="auto";
                h.style.zIndex="999";
                if(mode==="left"){h.style.left="6px";h.style.top="50%";h.style.transform="translateY(-50%)";h.style.cursor="ew-resize";}
                else if(mode==="right"){h.style.right="6px";h.style.top="50%";h.style.transform="translateY(-50%)";h.style.cursor="ew-resize";}
                else if(mode==="bottom"){h.style.left="50%";h.style.bottom="6px";h.style.transform="translateX(-50%)";h.style.cursor="ns-resize";}
                else if(mode==="bottom-left"){h.style.left="6px";h.style.bottom="6px";h.style.cursor="nesw-resize";}
                else if(mode==="bottom-right"){h.style.right="6px";h.style.bottom="6px";h.style.cursor="nwse-resize";}
                h.addEventListener("click",function(ev){ev.preventDefault();ev.stopPropagation();});
                h.addEventListener("mousedown",function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    var rect=w.getBoundingClientRect();
                    var startX=Number(e.clientX)||0;
                    var startY=Number(e.clientY)||0;
                    var startW=Math.max(50,Math.round(rect.width||Number(cs.fixedWidth)||200));
                    var startH=Math.max(50,Math.round(rect.height||Number(cs.fixedHeight)||200));
                    var didSave=false;
                    function onMove(ev){
                        if(!didSave){saveToHistory();didSave=true;}
                        var dx=(Number(ev.clientX)||0)-startX;
                        var dy=(Number(ev.clientY)||0)-startY;
                        var nw=startW,nh=startH;
                        if(mode==="left")nw=Math.max(50,Math.min(2400,Math.round(startW-dx)));
                        else if(mode==="right")nw=Math.max(50,Math.min(2400,Math.round(startW+dx)));
                        else if(mode==="bottom")nh=Math.max(50,Math.min(1600,Math.round(startH+dy)));
                        else if(mode==="bottom-left"){nw=Math.max(50,Math.min(2400,Math.round(startW-dx)));nh=Math.max(50,Math.min(1600,Math.round(startH+dy)));}
                        else if(mode==="bottom-right"){nw=Math.max(50,Math.min(2400,Math.round(startW+dx)));nh=Math.max(50,Math.min(1600,Math.round(startH+dy)));}
                        cs.fixedWidth=nw;cs.fixedHeight=nh;
                        w.style.setProperty("width",nw+"px","important");
                        w.style.setProperty("min-width",nw+"px","important");
                        w.style.setProperty("max-width",nw+"px","important");
                        w.style.setProperty("height",nh+"px","important");
                        w.style.setProperty("min-height",nh+"px","important");
                        wrap.style.minHeight=nh+"px";
                    }
                    function onUp(){
                        document.removeEventListener("mousemove",onMove);
                        document.removeEventListener("mouseup",onUp);
                        renderCanvas();
                        renderSettings();
                    }
                    document.addEventListener("mousemove",onMove);
                    document.addEventListener("mouseup",onUp);
                });
                return h;
            };
            wrap.style.position="relative";
            wrap.appendChild(mk("left"));
            wrap.appendChild(mk("right"));
            wrap.appendChild(mk("bottom-left"));
            wrap.appendChild(mk("bottom"));
            wrap.appendChild(mk("bottom-right"));
        })();
        w.innerHTML="";w.appendChild(wrap);
    }
    else if(item.type==="video"){
        const raw=String((item.settings&&item.settings.src)||"").trim();
        const settings=(item&&item.settings&&typeof item.settings==="object")?item.settings:{};
        const lower=raw.toLowerCase();
        const isYoutubeVimeo=lower.indexOf("youtube.com")>=0||lower.indexOf("youtu.be")>=0||lower.indexOf("vimeo.com")>=0;
        let embedUrl=raw;
        if(/youtube\.com\/watch/i.test(raw)){
            const q=raw.split("?")[1]||"";
            const params=new URLSearchParams(q);
            const v=params.get("v");
            if(v)embedUrl="https://www.youtube.com/embed/"+v;
        }else{
            const ytShort=raw.match(/youtu\.be\/([a-zA-Z0-9_-]+)/i);
            if(ytShort&&ytShort[1])embedUrl="https://www.youtube.com/embed/"+ytShort[1];
            const vimeo=raw.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
            if(vimeo&&vimeo[1])embedUrl="https://player.vimeo.com/video/"+vimeo[1];
        }
        const isHttp=/^https?:\/\//i.test(raw);
        const videoSrc=isYoutubeVimeo
            ? embedUrl
            : (isHttp?raw:(raw!==""?((raw.charAt(0)==="/")?(window.location.origin+raw):(window.location.origin+"/"+raw.replace(/^\/+/,""))):""));
        var hasFixedVideoH=!!(item.style&&String(item.style.height||"").trim()!=="");
        const wrap=document.createElement("div");
        wrap.style.position="relative";
        wrap.style.width="100%";
        wrap.style.background="#240E35";
        wrap.style.borderRadius=(item.style&&item.style.borderRadius)?String(item.style.borderRadius):"8px";
        wrap.style.overflow="hidden";
        wrap.style.boxSizing="border-box";
        if(hasFixedVideoH){
            wrap.style.height="100%";
        }else{
            wrap.style.minHeight="200px";
            wrap.style.paddingTop="56.25%";
        }
        if(mediaClip)wrap.style.cssText+=(wrap.style.cssText&&wrap.style.cssText.slice(-1)!==";"?";":"")+mediaClip;
        if(videoSrc!==""){
            var _vidLoadOverlay=document.createElement("div");
            _vidLoadOverlay.className="el-loading-overlay";
            _vidLoadOverlay.style.background="rgba(15,23,42,0.75)";
            _vidLoadOverlay.innerHTML='<div class="el-loading-spinner" style="border-color:rgba(255,255,255,0.2);border-top-color:#fff"></div>';
            wrap.appendChild(_vidLoadOverlay);
            if(isYoutubeVimeo){
                const qs=[];
                if(settings&&settings.autoplay===true)qs.push("autoplay=1");
                if(settings&&settings.controls===false)qs.push("controls=0");
                let src=embedUrl;
                if(src!==""){
                    src+=(src.indexOf("?")>=0?"&":"?")+qs.join("&");
                    src=src.replace(/[?&]$/,"");
                }
                const frame=document.createElement("iframe");
                frame.allowFullscreen=true;
                frame.allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
                frame.loading="lazy";
                frame.style.position="absolute";
                frame.style.top="0";
                frame.style.left="0";
                frame.style.width="100%";
                frame.style.height="100%";
                frame.style.border="0";
                frame.style.pointerEvents="none";
                frame.onload=function(){_vidLoadOverlay.remove();};
                frame.src=src;
                wrap.appendChild(frame);
            }else{
                const video=document.createElement("video");
                if((settings&&settings.controls)!==false)video.controls=true;
                if(settings&&settings.autoplay===true){video.autoplay=true;video.muted=true;}
                video.playsInline=true;
                video.preload="metadata";
                video.style.position="absolute";
                video.style.top="0";
                video.style.left="0";
                video.style.width="100%";
                video.style.height="100%";
                video.style.border="0";
                video.style.objectFit="contain";
                video.style.pointerEvents="none";
                video.onloadeddata=function(){_vidLoadOverlay.remove();};
                video.onerror=function(){_vidLoadOverlay.remove();};
                video.src=videoSrc;
                wrap.appendChild(video);
            }
        }else{
            wrap.innerHTML='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.8);padding:12px;"><span style="font-size:28px;margin-bottom:6px;">&#9654;</span><span style="font-size:12px;">Video URL placeholder</span><span style="font-size:11px;margin-top:4px;">Paste link or upload</span></div>';
        }
        if(state.mediaLoading.has(item.id)){
            var _vidUploadOverlay=document.createElement("div");
            _vidUploadOverlay.className="el-loading-overlay";
            _vidUploadOverlay.style.background="rgba(15,23,42,0.75)";
            _vidUploadOverlay.innerHTML='<div class="el-loading-spinner" style="border-color:rgba(255,255,255,0.2);border-top-color:#fff"></div>';
            wrap.appendChild(_vidUploadOverlay);
        }
        w.innerHTML="";
        w.appendChild(wrap);
    }
    else if(item.type==="spacer"){const h=((item.style&&item.style.height)||"24px");const isSel=!!(state.sel&&state.sel.k==="el"&&state.sel.e===item.id);const bg=isSel?'repeating-linear-gradient(90deg,#F3EEF7,#F3EEF7 8px,#E6E1EF 8px,#E6E1EF 16px)':'transparent';w.innerHTML='<div style="height:'+h+';background:'+bg+'"></div>';}
    if(item.type==="image"||item.type==="video"||item.type==="icon"){
        var a=(item.settings&&item.settings.alignment)||(item.type==="icon"?"center":"left");
        w.style.setProperty("display","flex");
        w.style.setProperty("justify-content",a==="right"?"flex-end":a==="center"?"center":"flex-start");
        w.style.setProperty("margin-left",a==="left"?"0":"auto");
        w.style.setProperty("margin-right",a==="right"?"0":"auto");
    }
    if(item.type==="image"||item.type==="video"){
        var a=(item.settings&&item.settings.alignment)||"left";
        w.style.setProperty("display","flex");
        w.style.setProperty("justify-content",a==="right"?"flex-end":a==="center"?"center":"flex-start");
        w.style.setProperty("margin-left",a==="left"?"0":"auto");
        w.style.setProperty("margin-right",a==="right"?"0":"auto");
        var editorOffsetX=Number(item&&item.settings&&item.settings.offsetX)||0;
        if(!isNaN(editorOffsetX)&&editorOffsetX!==0){
            w.style.transform="translateX("+editorOffsetX+"px)";
        }else{
            w.style.transform="";
        }
    }
    if(item.type==="carousel"){
        if(isSelected)attachCarouselResizeHandles(w,item);
    }
    if(isSelected&&item.type!=="carousel")attachElResizeHandles(w,item);
    return w;
}

function attachCarouselResizeHandles(node,item){
    if(!node||!item)return;
    if(getComputedStyle(node).position==="static")node.style.position="relative";
    node.style.overflow="visible";
    node.style.zIndex="9";
    item.settings=item.settings||{};
    var parseNum=function(v,fallback){var n=Number(v);return isNaN(n)?fallback:n;};
    var clamp=function(n,min,max){return Math.max(min,Math.min(max,n));};
    var applySize=function(w,h){
        var nextW=clamp(Math.round(parseNum(w,200)),50,2400);
        var nextH=clamp(Math.round(parseNum(h,200)),50,1600);
        item.settings.fixedWidth=nextW;
        item.settings.fixedHeight=nextH;
        item.style=item.style||{};
        item.style.width=nextW+"px";
        item.style.height=nextH+"px";
        node.style.setProperty("width",nextW+"px","important");
        node.style.setProperty("min-width",nextW+"px","important");
        node.style.setProperty("max-width",nextW+"px","important");
        node.style.setProperty("height",nextH+"px","important");
        node.style.setProperty("min-height",nextH+"px","important");
    };
    function startDrag(mode,e){
        e.preventDefault();
        e.stopPropagation();
        var rect=node.getBoundingClientRect();
        var startX=Number(e.clientX)||0;
        var startY=Number(e.clientY)||0;
        var startW=Math.max(50,Math.round(rect.width||parseNum(item.settings.fixedWidth,200)));
        var startH=Math.max(50,Math.round(rect.height||parseNum(item.settings.fixedHeight,200)));
        var didSave=false;
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dx=(Number(ev.clientX)||0)-startX;
            var dy=(Number(ev.clientY)||0)-startY;
            var nextW=startW;
            var nextH=startH;
            if(mode==="left")nextW=clamp(startW-dx,50,2400);
            else if(mode==="right")nextW=clamp(startW+dx,50,2400);
            else if(mode==="bottom")nextH=clamp(startH+dy,50,1600);
            else if(mode==="bottom-left"){nextW=clamp(startW-dx,50,2400);nextH=clamp(startH+dy,50,1600);}
            else if(mode==="bottom-right"){nextW=clamp(startW+dx,50,2400);nextH=clamp(startH+dy,50,1600);}
            applySize(nextW,nextH);
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
            renderCanvas();
            renderSettings();
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    }
    function makeHandle(mode){
        var h=document.createElement("div");
        h.className="media-resize-dot";
        h.title="Drag to resize";
        if(mode==="left")h.classList.add("media-resize-dot-left");
        else if(mode==="right")h.classList.add("media-resize-dot-right");
        else if(mode==="bottom")h.classList.add("media-resize-dot-b");
        else if(mode==="bottom-left")h.classList.add("media-resize-dot-bl");
        else if(mode==="bottom-right")h.classList.add("media-resize-dot-br");
        h.style.zIndex="999";
        h.addEventListener("click",function(ev){ev.preventDefault();ev.stopPropagation();});
        h.addEventListener("mousedown",function(ev){startDrag(mode,ev);});
        node.appendChild(h);
    }
    makeHandle("left");
    makeHandle("right");
    makeHandle("bottom-left");
    makeHandle("bottom");
    makeHandle("bottom-right");
}

function attachMediaResizeHandles(node,item){
    if(!node||!item)return;
    if(getComputedStyle(node).position==="static")node.style.position="relative";
    item.style=item.style||{};
    item.settings=item.settings||{};
    var img=node.querySelector("img");
    var mediaRatio=(item.type==="video")
        ? (16/9)
        : ((img&&img.naturalWidth>0&&img.naturalHeight>0) ? (img.naturalWidth/img.naturalHeight) : null);

    function parsePx(v){
        if(v===null||v===undefined)return 0;
        var n=Number(String(v).replace("px","").trim());
        return isNaN(n)?0:n;
    }
    function clamp(n,min,max){return Math.max(min,Math.min(max,n));}
    function startDrag(mode,e){
        e.preventDefault();
        e.stopPropagation();
        var rect=node.getBoundingClientRect();
        var startX=Number(e.clientX)||0;
        var startY=Number(e.clientY)||0;
        var startW=Math.max(60,Math.round(rect.width||parsePx(item.style.width)||300));
        var startH=Math.max(40,Math.round(rect.height||parsePx(item.style.height)||180));
        var startOffsetX=Number(item&&item.settings&&item.settings.offsetX)||0;
        var ratio=(mediaRatio&&mediaRatio>0)?mediaRatio:((startH>0)?(startW/startH):null);
        var didSave=false;
        function applySize(nextW,nextH){
            if(nextW>0){
                item.style.width=Math.round(nextW)+"px";
                item.settings.width=item.style.width;
                node.style.width=item.style.width;
            }
            if(nextH>0){
                item.style.height=Math.round(nextH)+"px";
                node.style.height=item.style.height;
            }
        }
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dx=(Number(ev.clientX)||0)-startX;
            var dy=(Number(ev.clientY)||0)-startY;
            var nextW=startW;
            var nextH=startH;
            if(mode==="left")nextW=clamp(startW-dx,60,2400);
            else if(mode==="right")nextW=clamp(startW+dx,60,2400);
            else if(mode==="bottom")nextH=clamp(startH+dy,40,1800);
            else if(mode==="bottom-left"){
                nextW=clamp(startW-dx,60,2400);
                nextH=clamp(startH+dy,40,1800);
            } else if(mode==="bottom-right"){
                nextW=clamp(startW+dx,60,2400);
                nextH=clamp(startH+dy,40,1800);
            }
            if(ratio&&(mode==="bottom-left"||mode==="bottom-right")){
                nextH=clamp(nextW/ratio,40,1800);
            }
            applySize(nextW,nextH);
            if(mode==="left"||mode==="bottom-left"){
                var effectiveDx=startW-nextW;
                item.settings=item.settings||{};
                item.settings.offsetX=startOffsetX+effectiveDx;
            }
            var ox=Number(item&&item.settings&&item.settings.offsetX)||0;
            node.style.transform=(ox!==0)?("translateX("+ox+"px)"):"";
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
            renderCanvas();
            renderSettings();
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    }
    function makeHandle(cls,mode){
        var h=document.createElement("div");
        h.className=cls;
        h.title="Drag to resize";
        h.addEventListener("click",function(ev){ev.preventDefault();ev.stopPropagation();});
        h.addEventListener("mousedown",function(ev){startDrag(mode,ev);});
        node.appendChild(h);
    }
    makeHandle("media-resize-dot media-resize-dot-left","left");
    makeHandle("media-resize-dot media-resize-dot-right","right");
    makeHandle("media-resize-dot media-resize-dot-bl","bottom-left");
    makeHandle("media-resize-dot media-resize-dot-b","bottom");
    makeHandle("media-resize-dot media-resize-dot-br","bottom-right");
}

function attachRowHeightResizeHandle(rowInner,rowObj){
    if(!rowInner||!rowObj)return;
    var cols=Array.isArray(rowObj.columns)?rowObj.columns:[];
    if(!cols.length)return;
    var hHandle=document.createElement("div");
    hHandle.className="row-resize-handle-y";
    hHandle.title="Drag to resize column height";
    hHandle.addEventListener("click",e=>{e.preventDefault();e.stopPropagation();});
    hHandle.addEventListener("mousedown",function(e){
        e.preventDefault();
        e.stopPropagation();
        var colNodes=Array.from(rowInner.querySelectorAll(".col"));
        if(!colNodes.length)return;
        var startY=Number(e.clientY)||0;
        var startH=0;
        cols.forEach(function(c){
            c.style=c.style||{};
            var h=Number(pxToNumber(c.style.minHeight));
            if(!isNaN(h)&&h>startH)startH=h;
        });
        if(startH<=0){
            startH=Math.max(120,colNodes.reduce(function(m,n){return Math.max(m,n.offsetHeight||0);},0));
        }
        var didSave=false;
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dy=(Number(ev.clientY)||0)-startY;
            var next=Math.max(58,Math.min(1600,Math.round(startH+dy)));
            cols.forEach(function(c){c.style=c.style||{};c.style.height=next+"px";c.style.minHeight=next+"px";});
            colNodes.forEach(function(n){n.style.height=next+"px";n.style.minHeight=next+"px";});
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    });
    rowInner.appendChild(hHandle);
}

function attachSectionHeightResizeHandle(sectionNode,sectionObj){
    if(!sectionNode||!sectionObj)return;
    if(getComputedStyle(sectionNode).position==="static")sectionNode.style.position="relative";
    var hHandle=document.createElement("div");
    hHandle.className="section-resize-handle-y";
    hHandle.title="Drag to resize section height";
    hHandle.addEventListener("click",e=>{e.preventDefault();e.stopPropagation();});
    hHandle.addEventListener("mousedown",function(e){
        e.preventDefault();
        e.stopPropagation();
        var startY=Number(e.clientY)||0;
        sectionObj.style=sectionObj.style||{};
        var cssMin=String(sectionObj.style.minHeight||"").trim();
        var startH=0;
        if(cssMin.endsWith("px")){
            var n=Number(cssMin.replace("px","").trim());
            if(!isNaN(n))startH=n;
        }else if(cssMin.endsWith("vh")){
            var vh=Number(cssMin.replace("vh","").trim());
            if(!isNaN(vh))startH=Math.round((window.innerHeight||900)*(vh/100));
        }
        if(startH<=0){
            startH=Math.max(120,Math.round(sectionNode.getBoundingClientRect().height||0));
        }
        var didSave=false;
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dy=(Number(ev.clientY)||0)-startY;
            var next=Math.max(80,Math.min(2200,Math.round(startH+dy)));
            sectionObj.style.minHeight=next+"px";
            sectionNode.style.minHeight=sectionObj.style.minHeight;
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    });
    sectionNode.appendChild(hHandle);
}

function applyColumnImageFit(colNode,colInner,colObj){
    if(!colNode||!colInner||!colObj)return;
    var els=Array.isArray(colObj.elements)?colObj.elements:[];
    if(els.length!==1||!els[0]||els[0].type!=="image")return;
    var hasImageSrc=!!String((els[0].settings&&els[0].settings.src)||"").trim();
    colNode.style.overflow="hidden";
    colInner.style.height="";
    var imgWrap=colInner.querySelector(".el");
    if(!imgWrap)return;
    imgWrap.style.height="";
    imgWrap.style.marginBottom="0";
    imgWrap.style.overflow="hidden";
    imgWrap.style.display="block";
    imgWrap.style.alignItems="";
    imgWrap.style.justifyContent="";
    if(!hasImageSrc)return;
    var img=imgWrap.querySelector("img");
    if(!img)return;
    img.style.width="100%";
    img.style.height="auto";
    img.style.maxWidth="100%";
    img.style.maxHeight="";
    img.style.objectFit="contain";
    img.style.objectPosition="top center";
}

function renderCanvas(){
    hideDimTip();
    document.querySelectorAll(".carousel-resize-overlay-dot").forEach(function(n){if(n&&n.parentNode)n.parentNode.removeChild(n);});
    applyCanvasBgPreference();
    syncCanvasBgControls();
    if(Array.isArray(state._carAutoTimers)){
        state._carAutoTimers.forEach(function(t){try{clearTimeout(t);}catch(_e){}});
    }
    state._carAutoTimers=[];
    ensureRootModel();
    canvas.innerHTML="";
    var widthMap={full:"",wide:"1200px",medium:"992px",small:"768px",xsmall:"576px"};
    (state.layout.sections||[]).forEach(s=>{
        var contentWidth=((s.settings&&s.settings.contentWidth)||"full");
        var secElements=Array.isArray(s.elements)?s.elements:[];
        var secRows=Array.isArray(s.rows)?s.rows:[];
        var isBareCarouselSection=!!(s.__bareCarouselWrap||(!s.__rootWrap&&secRows.length===0&&secElements.length===1&&secElements[0]&&secElements[0].type==="carousel"));
        var isBareRootWrap=!!(s.__bareRootWrap&&!s.__freeformCanvas);
        var isBareSection=!!(isBareCarouselSection||isBareRootWrap);
        const sn=document.createElement("section");sn.className="sec";
        var nodeKind=s.__freeformCanvas?"canvas":"section";
        var outlineLabel=s.__freeformCanvas?"": "Section";
        sn.setAttribute("data-node-kind",nodeKind);
        if(outlineLabel!=="")sn.setAttribute("data-outline-label",outlineLabel);
        sn.setAttribute("data-sec-id",String(s.id||""));
        styleApply(sn,s.style||{});
        if(!isBareSection&&!s.__freeformCanvas){
            var secMinH=(s&&s.style&&String(s.style.minHeight||"").trim())||"";
            if(secMinH===""){
                sn.style.minHeight="30vh";
            }
        }
        if(isBareCarouselSection)sn.classList.add("sec--bare-carousel");
        if(isBareRootWrap&&!s.__freeformCanvas)sn.classList.add("sec--bare-wrap");
        if(s.__freeformCanvas)sn.classList.add("sec--freeform-canvas");
        const inner=document.createElement("div");inner.className="sec-inner";
        inner.style.width="100%";
        inner.style.boxSizing="border-box";
        inner.style.position="relative";
        if(s.__freeformCanvas){
            inner.style.minHeight="300px";
        }else{
            inner.style.minHeight=(sn.style.minHeight&&String(sn.style.minHeight).trim()!=="")?String(sn.style.minHeight):"30vh";
        }
        if(widthMap[contentWidth]){
            inner.style.maxWidth=widthMap[contentWidth];
            inner.style.margin="0 auto";
        }
        if(state.sel&&state.sel.k==="sec"&&state.sel.s===s.id)sn.classList.add("sel");
        sn.onclick=e=>{
            e.stopPropagation();
            if(isBareSection||s.__freeformCanvas)return;
            state.carouselSel=null;
            state.editingEl=null;
            state.sel={k:"sec",s:s.id};
            render();
        };
        sn.ondragover=e=>{
            e.preventDefault();
            const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
            if(t&&!isStructureComponent(t))showFreeDropGuides(e,inner);
            else clearFreeDropGuides();
        };
        sn.ondragleave=e=>{
            if(!sn.contains(e.relatedTarget))clearFreeDropGuides();
        };
        sn.ondrop=e=>{
            e.preventDefault();
            e.stopPropagation();
            if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
            const t=e.dataTransfer.getData("c");
            if(!t)return;
            state.carouselSel=null;
            var ok=false;
            if(!isStructureComponent(t)){
                var freePos=computeFreeDropPosition(e,inner);
                ok=addComponentAt(t,{k:"sec",s:s.id},{mode:"free",x:freePos.x,y:freePos.y});
            }else{
                ok=addComponentAt(t,{k:"sec",s:s.id},dropPlacement(e,sn));
            }
            clearFreeDropGuides();
            if(ok)render();
        };
        s.elements=Array.isArray(s.elements)?s.elements:[];
        (s.elements||[]).forEach(it=>inner.appendChild(renderElement(it,{s:s.id,scope:"section"})));
        if(s.__freeformCanvas){
            var secMaxBot=0;
            (s.elements||[]).forEach(function(it){
                if(it.settings&&it.settings.positionMode==="absolute"){
                    var ey=Number(it.settings.freeY)||0;
                    var eh=parseInt(it.style&&it.style.height)||(it.settings&&it.settings.fixedHeight?Number(it.settings.fixedHeight):0)||80;
                    var bot=ey+Math.max(40,eh)+20;
                    if(bot>secMaxBot)secMaxBot=bot;
                }
            });
            var freeformH=Math.max(300,secMaxBot);
            sn.style.minHeight=freeformH+"px";
            inner.style.minHeight=freeformH+"px";
        }
        (s.rows||[]).forEach(r=>{
            var isAutoWrapColumnRow=!!(s.__rootWrap&&s.__rootKind==="column"&&String(r.id||"").indexOf("row_wrap_col_")===0);
            const rn=document.createElement("div");rn.className="row";rn.setAttribute("data-node-kind","row");rn.setAttribute("data-outline-label","Row");rn.setAttribute("data-row-id",String(r.id||""));rn.setAttribute("data-s",String(s.id||""));styleApply(rn,r.style||{});
            if(isAutoWrapColumnRow)rn.classList.add("row--bare-wrap");
            const rowInner=document.createElement("div");rowInner.className="row-inner";rowInner.style.width="100%";rowInner.style.boxSizing="border-box";rowInner.style.display="flex";rowInner.style.flexWrap="wrap";rowInner.style.gap=((r&&r.style&&r.style.gap)||"8px");
            var rowCw=((r.settings&&r.settings.contentWidth)||"full");
            if(widthMap[rowCw]){rowInner.style.maxWidth=widthMap[rowCw];rowInner.style.margin="0 auto";}
            if(!isAutoWrapColumnRow && state.sel&&state.sel.k==="row"&&state.sel.r===r.id)rn.classList.add("sel");
            rn.onclick=e=>{if(isAutoWrapColumnRow)return;e.stopPropagation();state.carouselSel=null;state.editingEl=null;state.sel={k:"row",s:s.id,r:r.id};render();};
            rn.ondragover=e=>e.preventDefault();
            rn.ondrop=e=>{
                e.preventDefault();
                e.stopPropagation();
                if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
                const t=e.dataTransfer.getData("c");
                if(!t)return;
                state.carouselSel=null;
                if(t!=="column"&&t!=="row"&&t!=="section"){
                    var cols=Array.from(rowInner.querySelectorAll(".col"));
                    if(cols.length){
                        var px=Number(e.clientX)||0;
                        var nearest=cols[0],best=Infinity;
                        cols.forEach(function(colEl){
                            var rect=colEl.getBoundingClientRect();
                            var cx=rect.left+(rect.width/2);
                            var d=Math.abs(px-cx);
                            if(d<best){best=d;nearest=colEl;}
                        });
                        var nearId=nearest.getAttribute("data-col-id");
                        if(nearId){
                            var nearInner=nearest.querySelector(".col-inner")||nearest;
                            var freePos=computeFreeDropPosition(e,nearInner);
                            if(addComponentAt(t,{k:"col",s:s.id,r:r.id,c:nearId},{mode:"free",x:freePos.x,y:freePos.y}))render();
                            return;
                        }
                    }
                }
                if(addComponentAt(t,{k:"row",s:s.id,r:r.id},dropPlacement(e,rn)))render();
            };
            (r.columns||[]).forEach((c,colIndex)=>{
                const cn=document.createElement("div");cn.className="col";cn.setAttribute("data-node-kind","column");cn.setAttribute("data-outline-label","Column");cn.setAttribute("data-s",String(s.id||""));cn.setAttribute("data-r",String(r.id||""));styleApply(cn,c.style||{});
                cn.setAttribute("data-col-id",c.id);
                const colInner=document.createElement("div");colInner.className="col-inner";colInner.style.width="100%";colInner.style.boxSizing="border-box";
                colInner.style.position="relative";
                colInner.style.minHeight="100%";
                var colCw=((c.settings&&c.settings.contentWidth)||"full");
                if(widthMap[colCw]){colInner.style.maxWidth=widthMap[colCw];colInner.style.margin="0 auto";}
                if(state.sel&&state.sel.k==="col"&&state.sel.c===c.id)cn.classList.add("sel");
                cn.onclick=e=>{e.stopPropagation();state.carouselSel=null;state.editingEl=null;state.sel={k:"col",s:s.id,r:r.id,c:c.id};render();};
                cn.ondragover=e=>{
                    e.preventDefault();
                    const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
                    if(t&&!isStructureComponent(t))showFreeDropGuides(e,colInner);
                    else clearFreeDropGuides();
                };
                cn.ondragleave=e=>{
                    if(!cn.contains(e.relatedTarget))clearFreeDropGuides();
                };
                cn.ondrop=e=>{
                    e.preventDefault();
                    e.stopPropagation();
                    if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
                    const t=e.dataTransfer.getData("c");
                    if(!t)return;
                    state.carouselSel=null;
                    var ok=false;
                    if(!isStructureComponent(t)){
                        var freePos=computeFreeDropPosition(e,colInner);
                        ok=addComponentAt(t,{k:"col",s:s.id,r:r.id,c:c.id},{mode:"free",x:freePos.x,y:freePos.y});
                    }else{
                        ok=addComponentAt(t,{k:"col",s:s.id,r:r.id,c:c.id},dropPlacement(e,cn));
                    }
                    clearFreeDropGuides();
                    if(ok)render();
                };
                var colMaxBot=0;
                (c.elements||[]).forEach(function(it){
                    colInner.appendChild(renderElement(it,{s:s.id,r:r.id,c:c.id}));
                    if(it.settings&&it.settings.positionMode==="absolute"){
                        var ey=Number(it.settings.freeY)||0;
                        var eh=parseInt(it.style&&it.style.height)||80;
                        var bot=ey+Math.max(40,eh)+10;
                        if(bot>colMaxBot)colMaxBot=bot;
                    }
                });
                if(colMaxBot>0)cn.style.minHeight=Math.max(parseInt(cn.style.minHeight)||120,colMaxBot)+"px";
                cn.appendChild(colInner);
                applyColumnImageFit(cn,colInner,c);
                rowInner.appendChild(cn);
            });
            attachRowHeightResizeHandle(rowInner,r);
            rn.appendChild(rowInner);
            inner.appendChild(rn);
        });
        if(!isBareSection&&!s.__freeformCanvas){
            attachSectionHeightResizeHandle(sn,s);
        }
        sn.appendChild(inner);
        canvas.appendChild(sn);
    });
    if(!(state.layout.sections||[]).length)canvas.innerHTML='<p style="font-size:13px;color:#475569;">Drag any component to start.</p>';
    canvas.ondragover=e=>{e.preventDefault();clearFreeDropGuides();};
    canvas.ondrop=e=>{e.preventDefault();clearFreeDropGuides();if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;const t=e.dataTransfer.getData("c");if(t){var freePos=computeFreeDropPosition(e,canvas);var ok=(!isStructureComponent(t))?addComponentAt(t,null,{mode:"free",x:freePos.x,y:freePos.y}):addComponentAt(t,null,"after");if(ok)render();}};
}

function refreshAfterSetting(){
    if(state.carouselSel)render();
    else renderCanvas();
}
const dimCtx={tip:null};
const outlineCtx={hoverNode:null};
function ensureDimTip(){
    if(dimCtx.tip&&dimCtx.tip.parentNode)return dimCtx.tip;
    var n=document.createElement("div");
    n.className="fb-dim-tip";
    n.id="fbDimTip";
    document.body.appendChild(n);
    dimCtx.tip=n;
    return n;
}
function dimTypeLabel(node){
    if(!node)return "Component";
    if(node.classList&&node.classList.contains("el")){
        var t=String(node.getAttribute("data-el-type")||"component");
        return titleCase(t);
    }
    var k=String(node.getAttribute("data-node-kind")||"component");
    return titleCase(k);
}
function updateDimTipFromEvent(e){
    if(!canvas)return;
    var tip=ensureDimTip();
    var trg=e&&e.target&&e.target.closest?e.target.closest(".el,.col,.row,.sec"):null;
    if(!trg||!canvas.contains(trg)){
        setOutlineHoverTarget(null);
        tip.style.display="none";
        return;
    }
    setOutlineHoverTarget(trg);
    var r=trg.getBoundingClientRect();
    var w=Math.max(0,Math.round(r.width));
    var h=Math.max(0,Math.round(r.height));
    tip.textContent=dimTypeLabel(trg)+": "+w+" x "+h;
    tip.style.left=(Number(e.clientX)||0)+"px";
    tip.style.top=(Number(e.clientY)||0)+"px";
    tip.style.display="block";
}
function hideDimTip(){
    var tip=dimCtx.tip||document.getElementById("fbDimTip");
    if(tip)tip.style.display="none";
    setOutlineHoverTarget(null);
}
function setOutlineHoverTarget(node){
    if(!canvas)return;
    var next=(node&&canvas.contains(node))?node:null;
    var prev=outlineCtx.hoverNode;
    if(prev===next)return;
    if(prev&&prev.classList)prev.classList.remove("fb-outline-target");
    outlineCtx.hoverNode=next;
    if(next&&next.classList){
        next.classList.add("fb-outline-target");
        canvas.classList.add("fb-outline-has-target");
    }else{
        canvas.classList.remove("fb-outline-has-target");
    }
}
function initDimTipHover(){
    if(!canvas||canvas.__dimTipBound)return;
    canvas.__dimTipBound=true;
    canvas.addEventListener("mousemove",updateDimTipFromEvent);
    canvas.addEventListener("mouseleave",hideDimTip);
    canvas.addEventListener("dragstart",hideDimTip);
    canvas.addEventListener("drop",hideDimTip);
}
function initContextMenu(){
    ensureContextMenu();
    if(canvas&&!canvas.__ctxMenuBound){
        canvas.__ctxMenuBound=true;
        canvas.addEventListener("contextmenu",function(e){
            if(!(e&&e.target&&canvas.contains(e.target)))return;
            e.preventDefault();
            selectFromCanvasTarget(e.target);
            showContextMenuAt(e.clientX,e.clientY);
        });
    }
    if(!document.__ctxMenuGlobalBound){
        document.__ctxMenuGlobalBound=true;
        document.addEventListener("click",function(){hideContextMenu();});
        document.addEventListener("scroll",function(){hideContextMenu();},true);
        window.addEventListener("resize",function(){hideContextMenu();});
    }
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

function uploadImage(file,done,label,onFail,elId){
    const fd=new FormData();fd.append("image",file);
    const msg=label||"Upload";
    if(elId){state.mediaLoading.add(elId);render();}
    fetch(uploadUrl,{method:"POST",headers:{"X-CSRF-TOKEN":csrf,"Accept":"application/json"},body:fd})
        .then(r=>r.json().then(p=>({ok:r.ok,body:p})).catch(()=>({ok:false,body:null})))
        .then(({ok,body})=>{
            if(elId)state.mediaLoading.delete(elId);
            if(ok&&body&&body.url){done(body.url);return;}
            const err=body&&(body.message||(body.errors&&body.errors.image&&(Array.isArray(body.errors.image)?body.errors.image[0]:body.errors.image)));
            const reason=(err&&String(err).trim())?""+err:"Please check file type and size (max 100 MB).";
            if(typeof onFail==="function")onFail(reason);
            alert(msg+" failed: "+reason);
        })
        .catch(()=>{
            if(elId)state.mediaLoading.delete(elId);
            if(typeof onFail==="function")onFail("Check your connection and try again.");
            alert(msg+" failed. Check your connection and try again.");
        });
}

function ensureRadiusHelpModal(){
    var m=document.getElementById("fbRadiusHelpModal");
    if(m)return m;
    m=document.createElement("div");
    m.id="fbRadiusHelpModal";
    m.className="fb-help-modal";
    m.innerHTML='<div class="fb-help-card" role="dialog" aria-modal="true" aria-label="Border radius help">'
        +'<button type="button" class="fb-help-close" id="fbRadiusHelpClose" aria-label="Close">X</button>'
        +'<h4 class="fb-help-title">Border Radius</h4>'
        +'<div class="fb-help-text">The roundness meter. <strong>0</strong> makes sharp corners. Higher values make corners softer and more curved.</div>'
        +'<div class="fb-radius-demo"><div class="fb-radius-demo-box"></div></div>'
        +'</div>';
    m.addEventListener("click",function(e){if(e.target===m)closeRadiusHelpModal();});
    document.body.appendChild(m);
    var c=document.getElementById("fbRadiusHelpClose");
    if(c)c.addEventListener("click",function(e){e.preventDefault();closeRadiusHelpModal();});
    return m;
}
function openRadiusHelpModal(){
    var m=ensureRadiusHelpModal();
    m.classList.add("open");
}
function closeRadiusHelpModal(){
    var m=document.getElementById("fbRadiusHelpModal");
    if(m)m.classList.remove("open");
}
function ensureSpacingHelpModal(){
    var m=document.getElementById("fbSpacingHelpModal");
    if(m)return m;
    m=document.createElement("div");
    m.id="fbSpacingHelpModal";
    m.className="fb-help-modal";
    m.innerHTML='<div class="fb-help-card" role="dialog" aria-modal="true" aria-label="Spacing help">'
        +'<button type="button" class="fb-help-close" id="fbSpacingHelpClose" aria-label="Close">X</button>'
        +'<h4 class="fb-help-title">Spacing Helper</h4>'
        +'<div class="fb-help-text"><strong>Padding</strong>: Inside breathing room. It adds space inside the box.<br><strong>Margin</strong>: Personal space. It pushes other elements outside the box.</div>'
        +'<div class="fb-space-demo"><div class="fb-space-box"><div class="fb-space-pad"></div></div><div class="fb-space-box"><div class="fb-space-mar"></div></div></div>'
        +'</div>';
    m.addEventListener("click",function(e){if(e.target===m)closeSpacingHelpModal();});
    document.body.appendChild(m);
    var c=document.getElementById("fbSpacingHelpClose");
    if(c)c.addEventListener("click",function(e){e.preventDefault();closeSpacingHelpModal();});
    return m;
}
function openSpacingHelpModal(){
    var m=ensureSpacingHelpModal();
    m.classList.add("open");
}
function closeSpacingHelpModal(){
    var m=document.getElementById("fbSpacingHelpModal");
    if(m)m.classList.remove("open");
}

function renderSettings(){
    settingsTitle.textContent="Settings Panel";
    const inCarousel=!!state.carouselSel;
    const selKind=inCarousel?(state.carouselSel&&state.carouselSel.k):(state.sel&&state.sel.k);
    const t=selectedTarget();
    if((!state.sel&&!inCarousel)||!t){settings.innerHTML='<p class="meta">Select a component to edit.</p>';return;}
    settingsTitle.textContent=titleCase(selectedType())+" Settings";
    const sty=()=>{t.style=t.style||{};return t.style;};
    const moveCtx=(selKind==="el")?selectedElementMoveContext():((selKind==="sec"||selKind==="row"||selKind==="col")?selectedStructureMoveContext():null);
    const canMoveUp=!!(moveCtx&&moveCtx.index>0);
    const canMoveDown=!!(moveCtx&&moveCtx.index<(moveCtx.list.length-1));
    const moveControls=((selKind==="el"||selKind==="sec"||selKind==="row"||selKind==="col")&&moveCtx)
        ? '<div class="menu-split"></div><div class="menu-section-title">Order</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;"><button type="button" id="btnMoveUp" class="fb-btn"'+(canMoveUp?'':' disabled')+'>Move Up</button><button type="button" id="btnMoveDown" class="fb-btn"'+(canMoveDown?'':' disabled')+'>Move Down</button></div>'
        : '';
    const isAbsEl=!!(selKind==="el"&&t.settings&&t.settings.positionMode==="absolute");
    var curW=parseInt(t.style&&t.style.width)||0;
    var curH=parseInt(t.style&&t.style.height)||0;
    var curZ=Number(t.style&&t.style.zIndex)||0;
    const posControls=isAbsEl?'<div class="menu-split"></div><div class="menu-section-title">Position &amp; Size</div><div class="size-position"><div class="size-grid"><div class="fld"><label>X</label><input id="elPosX" type="number" min="0" step="1" value="'+(Number(t.settings.freeX)||0)+'"></div><div class="fld"><label>Y</label><input id="elPosY" type="number" min="0" step="1" value="'+(Number(t.settings.freeY)||0)+'"></div></div><div class="size-grid" style="margin-top:6px;"><div class="fld"><label>W</label><input id="elSizeW" type="number" min="30" step="1" value="'+curW+'"></div><div class="fld"><label>H</label><input id="elSizeH" type="number" min="20" step="1" value="'+curH+'"></div></div></div><div class="menu-split"></div><div class="menu-section-title">Layer</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px;"><button type="button" id="btnLayerFwd" class="fb-btn" style="font-size:11px;">Forward</button><button type="button" id="btnLayerBwd" class="fb-btn" style="font-size:11px;">Backward</button><button type="button" id="btnLayerFront" class="fb-btn" style="font-size:11px;">To Front</button><button type="button" id="btnLayerBack" class="fb-btn" style="font-size:11px;">To Back</button></div><div class="fld" style="margin-bottom:8px;"><label>Z-Index</label><input id="elZIndex" type="number" min="0" step="1" value="'+curZ+'"></div>':'';
    const remove='<div class="settings-delete-wrap"><button type="button" id="btnDeleteSelected" class="fb-btn danger"><i class="fas fa-trash-alt"></i> Delete</button></div>';
    function mountPositionControls(){
        var px=document.getElementById("elPosX"),py=document.getElementById("elPosY");
        if(px)px.addEventListener("input",function(){saveToHistory();var v=Math.max(0,Number(px.value)||0);t.style=t.style||{};t.settings=t.settings||{};t.style.left=v+"px";t.settings.freeX=v;renderCanvas();});
        if(py)py.addEventListener("input",function(){saveToHistory();var v=Math.max(0,Number(py.value)||0);t.style=t.style||{};t.settings=t.settings||{};t.style.top=v+"px";t.settings.freeY=v;renderCanvas();});
        var sw=document.getElementById("elSizeW"),sh=document.getElementById("elSizeH");
        if(sw)sw.addEventListener("input",function(){saveToHistory();t.style=t.style||{};t.style.width=Math.max(30,Number(sw.value)||30)+"px";renderCanvas();});
        if(sh)sh.addEventListener("input",function(){saveToHistory();t.style=t.style||{};t.style.height=Math.max(20,Number(sh.value)||20)+"px";renderCanvas();});
        var zi=document.getElementById("elZIndex");
        if(zi)zi.addEventListener("input",function(){saveToHistory();t.style=t.style||{};t.style.zIndex=String(Math.max(0,Number(zi.value)||0));renderCanvas();});
        var selCtx=state.sel||{};
        var fwd=document.getElementById("btnLayerFwd");if(fwd)fwd.onclick=function(){saveToHistory();layerForward(t,selCtx);render();};
        var bwd=document.getElementById("btnLayerBwd");if(bwd)bwd.onclick=function(){saveToHistory();layerBackward(t,selCtx);render();};
        var front=document.getElementById("btnLayerFront");if(front)front.onclick=function(){saveToHistory();layerToFront(t,selCtx);render();};
        var back=document.getElementById("btnLayerBack");if(back)back.onclick=function(){saveToHistory();layerToBack(t,selCtx);render();};
    }
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
    function radiusHelpLabelHtml(btnId,labelText){
        return '<div class="setting-label-help"><label style="margin:0;">'+labelText+'</label><button type="button" id="'+btnId+'" class="setting-help-icon" aria-label="'+labelText+' help">?</button></div>';
    }
    function bindRadiusHelpButton(btnId){
        var b=document.getElementById(btnId);
        if(!b)return;
        b.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            openRadiusHelpModal();
        });
    }
    function ensureSpacingHelperButton(){
        var hasSpacingFields=!!(document.getElementById("pTop")||document.getElementById("mTop"));
        if(!hasSpacingFields)return;
        var anchor=null;
        var titles=settings.querySelectorAll(".menu-section-title");
        titles.forEach(function(n){
            if(anchor)return;
            if(String(n.textContent||"").trim().toLowerCase()==="spacing")anchor=n;
        });
        if(!anchor){
            anchor=settings.querySelector(".size-position .size-label");
        }
        if(!anchor)return;
        if(anchor.querySelector(".fb-spacing-help-btn"))return;
        if(anchor.classList.contains("menu-section-title")){
            anchor.style.display="flex";
            anchor.style.alignItems="center";
            anchor.style.gap="8px";
        }
        var b=document.createElement("button");
        b.type="button";
        b.className="setting-help-icon fb-spacing-help-btn";
        b.setAttribute("aria-label","Spacing help");
        b.textContent="?";
        b.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            openSpacingHelpModal();
        });
        anchor.appendChild(b);
    }
    if(selKind==="sec"){
        t.settings=t.settings||{};
        var padDef=[20,20,20,20],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var cw=(t.settings&&t.settings.contentWidth)||"full";
        settings.innerHTML='<div class="menu-section-title">Layout</div><label>Content width</label><select id="secCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><label>Section ID (anchor)</label><input id="secAnchor" placeholder="contact"><div class="meta">Example: contact, etc</div><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+radiusHelpLabelHtml("secRadiusHelp","Border radius")+'<div class="px-wrap"><input id="secRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("secCw",cw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        bind("secAnchor",(t.settings&&t.settings.anchorId)||"",v=>{
            t.settings=t.settings||{};
            var next=String(v||"").trim().replace(/^#+/,"").replace(/[^A-Za-z0-9\-_]/g,"").slice(0,80);
            if(next==="")delete t.settings.anchorId;else t.settings.anchorId=next;
            var a=document.getElementById("secAnchor");
            if(a&&a.value!==next)a.value=next;
            renderCanvas();
        },{undo:true});
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
        bindPx("secRadius",(t.style&&t.style.borderRadius)||"",v=>sty().borderRadius=v,{undo:true});
        bindRadiusHelpButton("secRadiusHelp");
    } else if(selKind==="el"&&t.type==="image"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0];
        var mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var radiusLinked=t.settings.imageRadiusLinked!==false;
        var imageSourceType=(t.settings&&t.settings.imageSourceType)||"direct";
        var imageSourceFields=imageSourceType==="upload"
            ? '<label>Upload file</label><input id="up" type="file" accept="image/*"><div class="meta" id="imgCurrentFile"></div>'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Image type</label><select id="imgSourceType"><option value="direct"'+(imageSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(imageSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+imageSourceFields+'<label>Alt</label><input id="alt"><div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><label>Height</label><input id="h" placeholder="auto"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b">'+radiusHelpLabelHtml("imgRadiusHelp","Border radius")+'<div class="img-radius-panel"><button type="button" id="imgRadiusLink" class="img-radius-link'+(radiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="imgRadTl" type="number" value="'+rad[0]+'"><input id="imgRadTr" type="number" value="'+rad[1]+'"><input id="imgRadBr" type="number" value="'+rad[2]+'"><input id="imgRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh">'+posControls+moveControls+remove;
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
            if(up)up.onchange=()=>{if(up.files&&up.files[0]){saveToHistory();uploadImage(up.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;render();},"Image upload",null,t.id);}};
        }
        bind("alt",(t.settings&&t.settings.alt)||"",v=>{t.settings=t.settings||{};t.settings.alt=v;},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        bind("h",(t.style&&t.style.height)||"auto",v=>{sty().height=v;},{px:true,undo:true});
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
        bindRadiusHelpButton("imgRadiusHelp");
        bind("b",(t.style&&t.style.border)||"",v=>sty().border=v,{undo:true});bind("sh",(t.style&&t.style.boxShadow)||"",v=>sty().boxShadow=v,{undo:true});
    } else if(selKind==="el"&&t.type==="video"){
        t.settings=t.settings||{};
        var marDef=[0,0,0,0],radDef=[0,0,0,0],mar=parseSpacing(t.style&&t.style.margin,marDef),rad=parseSpacing(t.style&&t.style.borderRadius,radDef);
        var videoRadiusLinked=t.settings.videoRadiusLinked!==false;
        var videoSourceType=(t.settings&&t.settings.videoSourceType)||"direct";
        var videoSourceFields=videoSourceType==="upload"
            ? '<label>Upload file</label><input id="upv" type="file" accept="video/*"><div class="meta" id="vidCurrentFile"></div>'
            : '<label>URL</label><input id="src">';
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Video type</label><select id="vidSourceType"><option value="direct"'+(videoSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(videoSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+videoSourceFields+'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><label>Height</label><input id="h" placeholder="auto"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b">'+radiusHelpLabelHtml("vidRadiusHelp","Border radius")+'<div class="img-radius-panel"><button type="button" id="vidRadiusLink" class="img-radius-link'+(videoRadiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="vidRadTl" type="number" value="'+rad[0]+'"><input id="vidRadTr" type="number" value="'+rad[1]+'"><input id="vidRadBr" type="number" value="'+rad[2]+'"><input id="vidRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh"><div class="menu-split"></div><div class="menu-section-title">Behavior</div><label>Auto play</label><select id="vAutoplay"><option value="off">Off</option><option value="on">On</option></select><label>Controls</label><select id="vControls"><option value="on">On</option><option value="off">Off</option></select>'+posControls+moveControls+remove;
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
            if(upv)upv.onchange=()=>{if(upv.files&&upv.files[0]){saveToHistory();uploadImage(upv.files[0],url=>{t.settings=t.settings||{};t.settings.src=url;render();},"Video upload",null,t.id);}};
        }
        bind("vAutoplay",(t.settings&&t.settings.autoplay)?"on":"off",v=>{t.settings=t.settings||{};t.settings.autoplay=(v==="on");},{undo:true});
        bind("vControls",(t.settings&&typeof t.settings.controls==="boolean")?(t.settings.controls?"on":"off"):"on",v=>{t.settings=t.settings||{};t.settings.controls=(v!=="off");},{undo:true});
        bind("align",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v;},{undo:true});
        bind("w",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{sty().width=v;t.settings=t.settings||{};t.settings.width=v;},{px:true,undo:true});
        bind("h",(t.style&&t.style.height)||"auto",v=>{sty().height=v;},{px:true,undo:true});
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
        bindRadiusHelpButton("vidRadiusHelp");
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
        settings.innerHTML='<div class="menu-section-title">Layout</div><label>Content width</label><select id="rowCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><div class="row-border-card"><div class="row-border-head"><strong>Border</strong></div><select id="rowBorderStyle"><option value="none">None</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option><option value="double">Double</option></select>'+radiusHelpLabelHtml("rowRadiusHelp","Corner radius")+radiusBlock+'<div class="size-link"><button type="button" id="rowRadiusToggle" title="Toggle radius mode"><i class="fas fa-expand"></i></button><span>'+(perCorner?'Per corner':'Single value')+'</span></div></div><div class="menu-split"></div><div class="menu-section-title">Behavior</div><button type="button" id="rowBorderReset" class="fb-btn" style="width:100%;"><i class="fas fa-rotate-right"></i> Reset row border</button>'+remove;
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
        function applyBorderStyle(v){t.settings=t.settings||{};t.settings.rowBorderStyle=v;if(v==="none")sty().border="none";else sty().border="1px "+v+" #E6E1EF";renderCanvas();}
        if(bs){bs.value=borderStyle;bs.onchange=()=>{saveToHistory();applyBorderStyle(bs.value);};}
        function applyRadius(vals){sty().borderRadius=spacingToCss(vals);renderCanvas();}
        var rowRadAll=document.getElementById("rowRadAll"),rowRadTl=document.getElementById("rowRadTl"),rowRadTr=document.getElementById("rowRadTr"),rowRadBr=document.getElementById("rowRadBr"),rowRadBl=document.getElementById("rowRadBl");
        if(rowRadAll)rowRadAll.addEventListener("input",()=>{saveToHistory();var v=Number(rowRadAll.value)||0;applyRadius([v,v,v,v]);});
        function syncCornerRadius(){saveToHistory();var tl=Number((rowRadTl&&rowRadTl.value)||0)||0,tr=Number((rowRadTr&&rowRadTr.value)||0)||0,br=Number((rowRadBr&&rowRadBr.value)||0)||0,bl=Number((rowRadBl&&rowRadBl.value)||0)||0;applyRadius([tl,tr,br,bl]);}
        [rowRadTl,rowRadTr,rowRadBr,rowRadBl].forEach(n=>{if(n)n.addEventListener("input",syncCornerRadius);});
        var rowRadiusToggle=document.getElementById("rowRadiusToggle");
        if(rowRadiusToggle)rowRadiusToggle.onclick=()=>{saveToHistory();t.settings=t.settings||{};t.settings.rowRadiusPerCorner=!t.settings.rowRadiusPerCorner;renderSettings();};
        bindRadiusHelpButton("rowRadiusHelp");
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
        settings.innerHTML='<div class="menu-section-title">Layout</div>'+layoutHtml+'<label>Content width</label><select id="colCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+radiusHelpLabelHtml("colRadiusHelp","Border radius")+'<div class="px-wrap"><input id="colRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
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
        bindPx("colRadius",(t.style&&t.style.borderRadius)||"",v=>sty().borderRadius=v,{undo:true});
        bindRadiusHelpButton("colRadiusHelp");

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
            while(cols.length<count){cols.push(createDefaultColumn());}
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
        if(!t.settings.fixedWidth || Number(t.settings.fixedWidth)<50)t.settings.fixedWidth=200;
        if(!t.settings.fixedHeight || Number(t.settings.fixedHeight)<50)t.settings.fixedHeight=200;
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
                +'<div class="meta" style="margin:6px 0 10px;">Add a blank slide here, or upload image slides. You can also use the <strong>+</strong> button inside carousel.</div>'
                +'<button type="button" id="addSlideFromSettings" class="fb-btn primary" style="width:100%;margin:0 0 8px;">Add slide</button>'
                +'<button type="button" id="addImageSlideFromSettings" class="fb-btn" style="width:100%;margin:0 0 10px;">Add image slide</button>'
                +'<input id="carImageSlideFiles" type="file" accept="image/*" multiple style="display:none;">'
                +carouselComponentsHtml
                +'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Carousel alignment</label><div class="menu-align-row"><button type="button" class="menu-align-btn car-align-btn" data-ca="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn car-align-btn" data-ca="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn car-align-btn" data-ca="right"><i class="fas fa-align-right"></i></button></div>'
                +'<label>Fixed width</label><div class="px-wrap"><input id="carFixedW" type="number" min="50" step="1"><span class="px-unit">px</span></div><label>Fixed height</label><div class="px-wrap"><input id="carFixedH" type="number" min="50" step="1"><span class="px-unit">px</span></div>'
                +'<div class="menu-split"></div><div class="menu-section-title">Behavior</div><label>Slideshow mode</label><select id="carSlideMode"><option value="manual">Manual (use arrows)</option><option value="auto">Automatic (no arrows)</option></select><div class="meta">Slide selection, view, and ordering controls are in the Content section above.</div>'
                +'<div class="menu-split"></div><div class="menu-section-title">Style</div>'+radiusHelpLabelHtml("carRadiusHelp","Border radius")+'<div class="px-wrap"><input id="carRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'
                +posControls+moveControls+remove;

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
            var addSlideFromSettings=document.getElementById("addSlideFromSettings");
            var addImageSlideFromSettings=document.getElementById("addImageSlideFromSettings");
            var carImageSlideFiles=document.getElementById("carImageSlideFiles");
            if(addSlideFromSettings)addSlideFromSettings.onclick=()=>{
                ensureCarouselSlides(t.settings);
                var liveSlides=t.settings.slides||[];
                saveToHistory();
                var next={id:uid("sld"),label:"Slide #"+(liveSlides.length+1),image:{src:"",alt:"Image"}};
                liveSlides.push(next);
                t.settings.activeSlide=liveSlides.length-1;
                t.settings.carouselActiveRow=0;
                t.settings.carouselActiveCol=0;
                state.carouselSel=null;
                renderCarouselEditor();
                renderCanvas();
            };
            if(addImageSlideFromSettings&&carImageSlideFiles)addImageSlideFromSettings.onclick=()=>{carImageSlideFiles.click();};
            if(carImageSlideFiles)carImageSlideFiles.onchange=()=>{
                var files=Array.from(carImageSlideFiles.files||[]);
                if(!files.length)return;
                saveToHistory();
                var idx=0;
                var activeIdx=Number(t.settings.activeSlide);
                if(isNaN(activeIdx)||activeIdx<0||activeIdx>=slides.length)activeIdx=0;
                var addNext=()=>{
                    if(idx>=files.length){
                        renderCarouselEditor();
                        renderCanvas();
                        return;
                    }
                    var file=files[idx++];
                    uploadImage(file,url=>{
                        var sld=null;
                        if((idx-1)===0){
                            sld=slides[activeIdx];
                            if(!sld){
                                sld={id:uid("sld"),label:"Slide #"+(slides.length+1),image:{src:"",alt:"Image"}};
                                slides.push(sld);
                                activeIdx=slides.length-1;
                            }
                        }else{
                            sld={id:uid("sld"),label:"Slide #"+(slides.length+1),image:{src:"",alt:"Image"}};
                            slides.push(sld);
                        }
                        sld.image={src:String(url||"").trim(),alt:"Image"};
                        var nextActive=slides.findIndex(sl=>String((sl&&sl.id)||"")===String(sld.id||""));
                        t.settings.activeSlide=(nextActive>=0)?nextActive:activeIdx;
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
            bind("carSlideMode",(t.settings&&t.settings.slideshowMode)||"manual",v=>{t.settings=t.settings||{};var m=(v==="auto")?"auto":"manual";t.settings.slideshowMode=m;t.settings.showArrows=(m==="auto")?false:true;renderCanvas();},{undo:true});
            bindPx("carRadius",(t.style&&t.style.borderRadius)||"",v=>sty().borderRadius=v,{undo:true});
            bindRadiusHelpButton("carRadiusHelp");
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
            function collectSectionAnchors(){
                var seen={};
                var out=[];
                rootItems().forEach(function(it){
                    if(String((it&&it.kind)||"").toLowerCase()!=="section")return;
                    var settings=(it&&it.settings&&typeof it.settings==="object")?it.settings:{};
                    var raw=String(settings.anchorId||"").trim().replace(/^#+/,"").replace(/[^A-Za-z0-9\-_]/g,"").slice(0,80);
                    if(!raw||seen[raw])return;
                    seen[raw]=true;
                    out.push(raw);
                });
                return out;
            }
            var sectionAnchors=collectSectionAnchors();
            var cards=items.map((it,idx)=>{
                var collapsed=!!t.settings.menuCollapsed[idx];
                var currentUrl=String((it&&it.url)||"").trim();
                var currentAnchor=currentUrl.indexOf("#")===0?currentUrl.slice(1):"";
                currentAnchor=String(currentAnchor||"").trim().replace(/^#+/,"").replace(/[^A-Za-z0-9\-_]/g,"").slice(0,80);
                var hasCurrentInList=currentAnchor!==""&&sectionAnchors.indexOf(currentAnchor)>=0;
                var useSectionMode=currentAnchor!=="";
                var anchorOptions=sectionAnchors.map(function(anchor){
                    var esc=String(anchor).replace(/"/g,'&quot;');
                    return '<option value="'+esc+'"'+(anchor===currentAnchor?' selected':'')+'>'+anchor+'</option>';
                }).join("");
                if(currentAnchor!==""&&!hasCurrentInList){
                    var missingEsc=String(currentAnchor).replace(/"/g,'&quot;');
                    anchorOptions='<option value="'+missingEsc+'" selected>Missing: '+currentAnchor+'</option>'+anchorOptions;
                }
                if(anchorOptions===""){
                    anchorOptions='<option value="">No section IDs found</option>';
                }
                var body=collapsed?'':'<input id="miLabel_'+idx+'" value="'+String((it&&it.label)||"").replace(/"/g,'&quot;')+'" placeholder="Label"><label>Target</label><select id="miMode_'+idx+'"><option value="section"'+(useSectionMode?' selected':'')+'>Section anchor</option><option value="custom"'+(!useSectionMode?' selected':'')+'>Custom link</option></select><div id="miSectionWrap_'+idx+'"'+(useSectionMode?'':' style="display:none;"')+'><label>Section</label><select id="miAnchor_'+idx+'"'+(sectionAnchors.length===0?' disabled':'')+'>'+anchorOptions+'</select><div class="meta">Uses #anchor automatically.</div></div><div id="miCustomWrap_'+idx+'"'+(!useSectionMode?'':' style="display:none;"')+'><label>Link</label><input id="miUrl_'+idx+'" value="'+String((it&&it.url)||"").replace(/"/g,'&quot;')+'" placeholder="e.g. /about or https://example.com"></div><label><input id="miNew_'+idx+'" type="checkbox"'+((it&&it.newWindow)?' checked':'')+'> Open in a new window</label><label><input id="miSub_'+idx+'" type="checkbox"'+((it&&it.hasSubmenu)?' checked':'')+'> Has submenu</label>';
                return '<div class="menu-item-card"><div class="menu-item-head"><strong>Menu item '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button><button type="button" class="menu-toggle" data-idx="'+idx+'" title="Toggle"><i class="fas '+(collapsed?'fa-chevron-down':'fa-chevron-up')+'"></i></button></div></div>'+body+'</div>';
            }).join("");
            settings.innerHTML='<div class="menu-panel-title">Menu</div><div class="menu-section-title">Content</div>'+cards+'<button type="button" id="addMenuItem" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add menu item</button><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Font family</label><select id="mFont"><option value="">Same font as the page</option>'+fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+'</select><div class="menu-typo-grid"><div class="px-wrap"><input id="mFs" type="number" step="1"><span class="px-unit">px</span></div><div class="px-wrap"><input id="mLh" type="number" step="0.1"><span class="px-unit">lh</span></div></div><label>Text style</label><div class="menu-style-row"><button type="button" id="mBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="mItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div><div class="menu-split"></div><div class="menu-section-title">Layout</div><div class="menu-align-row"><button type="button" class="menu-align-btn" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn" data-align="right"><i class="fas fa-align-right"></i></button></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Letter spacing</label><div class="menu-slider-row"><input id="mLsRange" type="range" min="0" max="20" step="0.1"><input id="mLsNum" type="number" min="0" max="20" step="0.1"></div><label>Text color</label><input id="mTextColor" type="color"><label>Menu items underline color</label><input id="mUnderlineColor" type="color"><label>Background color</label><input id="mBgColor" type="color"><label>Background image URL</label><input id="mBgImg" placeholder="https://..."><label>Upload background image</label><input id="mBgUp" type="file" accept="image/*"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><label>Spacing between menu items</label><div class="menu-slider-row"><input id="mGapRange" type="range" min="0" max="64" step="1"><input id="mGapNum" type="number" min="0" max="64" step="1"></div><label>Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label>Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div>'+posControls+moveControls+remove;

            items.forEach((it,idx)=>{
                var lab=document.getElementById("miLabel_"+idx),url=document.getElementById("miUrl_"+idx),mode=document.getElementById("miMode_"+idx),anchor=document.getElementById("miAnchor_"+idx),sectionWrap=document.getElementById("miSectionWrap_"+idx),customWrap=document.getElementById("miCustomWrap_"+idx),nw=document.getElementById("miNew_"+idx),sm=document.getElementById("miSub_"+idx);
                if(lab)lab.addEventListener("input",()=>{it.label=lab.value||"";renderCanvas();});
                if(url)url.addEventListener("input",()=>{it.url=url.value||"";renderCanvas();});
                if(mode)mode.addEventListener("change",()=>{
                    var isSection=mode.value==="section";
                    if(sectionWrap)sectionWrap.style.display=isSection?"":"none";
                    if(customWrap)customWrap.style.display=isSection?"none":"";
                    if(isSection&&anchor&&anchor.value){
                        it.url="#"+anchor.value;
                    }
                    renderCanvas();
                });
                if(anchor)anchor.addEventListener("change",()=>{
                    var next=String(anchor.value||"").trim();
                    if(next!=="")it.url="#"+next;
                    renderCanvas();
                });
                if(nw)nw.addEventListener("change",()=>{it.newWindow=!!nw.checked;renderCanvas();});
                if(sm)sm.addEventListener("change",()=>{it.hasSubmenu=!!sm.checked;renderCanvas();});
            });
            settings.querySelectorAll(".menu-del").forEach(btn=>btn.addEventListener("click",()=>{var i=Number(btn.getAttribute("data-idx"));if(items.length<=1)return;saveToHistory();items.splice(i,1);delete t.settings.menuCollapsed[i];renderMenuEditor();renderCanvas();}));
            settings.querySelectorAll(".menu-toggle").forEach(btn=>btn.addEventListener("click",()=>{var i=Number(btn.getAttribute("data-idx"));t.settings.menuCollapsed[i]=!t.settings.menuCollapsed[i];renderMenuEditor();}));
            var addBtn=document.getElementById("addMenuItem");if(addBtn)addBtn.onclick=()=>{saveToHistory();items.push({label:"Menu item "+(items.length+1),url:"#",newWindow:false,hasSubmenu:false});renderMenuEditor();renderCanvas();};

            bind("mFont",(t.style&&t.style.fontFamily)||"",v=>sty().fontFamily=v,{undo:true});
            bindPx("mFs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v,{undo:true});
            bind("mLh",(t.style&&t.style.lineHeight)||"",v=>sty().lineHeight=v,{undo:true});
            var mBold=document.getElementById("mBold"),mItalic=document.getElementById("mItalic");
            function menuStyleState(){
                var fw=String((t.style&&t.style.fontWeight)||"").toLowerCase();
                var fs=String((t.style&&t.style.fontStyle)||"").toLowerCase();
                return {bold:(fw==="bold"||Number(fw)>=600),italic:(fs==="italic")};
            }
            function syncMenuStyleButtons(){
                var st=menuStyleState();
                if(mBold)mBold.classList.toggle("active",!!st.bold);
                if(mItalic)mItalic.classList.toggle("active",!!st.italic);
            }
            if(mBold)mBold.onclick=()=>{saveToHistory();var st=menuStyleState();sty().fontWeight=st.bold?"400":"700";syncMenuStyleButtons();renderCanvas();};
            if(mItalic)mItalic.onclick=()=>{saveToHistory();var st=menuStyleState();sty().fontStyle=st.italic?"normal":"italic";syncMenuStyleButtons();renderCanvas();};
            syncMenuStyleButtons();
            var curAlign=(t.settings&&t.settings.menuAlign)||"left";
            settings.querySelectorAll(".menu-align-btn[data-align]").forEach(btn=>{if(btn.getAttribute("data-align")===curAlign)btn.classList.add("active");btn.addEventListener("click",()=>{saveToHistory();t.settings=t.settings||{};t.settings.menuAlign=btn.getAttribute("data-align");renderMenuEditor();renderCanvas();});});

            var lsVal=Number(pxToNumber((t.style&&t.style.letterSpacing)||""));if(isNaN(lsVal))lsVal=0;
            var lsRange=document.getElementById("mLsRange"),lsNum=document.getElementById("mLsNum");
            function syncLs(v,skipR,skipN){var n=Number(v);if(isNaN(n))n=0;if(n<0)n=0;if(n>20)n=20;if(lsRange&&!skipR)lsRange.value=String(n);if(lsNum&&!skipN)lsNum.value=String(n);sty().letterSpacing=n+"px";renderCanvas();}
            if(lsRange)lsRange.oninput=()=>{saveToHistory();syncLs(lsRange.value,true,false);};
            if(lsNum){lsNum.oninput=()=>{saveToHistory();syncLs(lsNum.value,false,true);};lsNum.onchange=()=>{saveToHistory();syncLs(lsNum.value,false,true);};}
            syncLs(lsVal,false,false);

            bind("mTextColor",(t.settings&&t.settings.textColor)||"#374151",v=>{t.settings=t.settings||{};t.settings.textColor=v;renderCanvas();},{undo:true});
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
        t.settings.fields=normalizeFormFields(t.settings.fields,false);
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Submit button text</label><input id="formSubmitText" placeholder="Submit"><div class="menu-split"></div><div class="menu-section-title">Form inputs</div><div id="formFieldsEditor"></div><button type="button" id="addFormInput" class="fb-btn" style="width:100%;margin:6px 0 10px;">Add form-input</button><div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="formAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Form width</label><input id="formWidth" placeholder="100%"><div class="meta" style="margin-top:8px;">Set width in % (example: 50%) and place using alignment only.</div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Label text color</label><input id="formLabelColor" type="color"><label>Placeholder text color</label><input id="formPlaceholderColor" type="color"><label>Submit button color</label><input id="formBtnBgColor" type="color"><label>Submit button text color</label><input id="formBtnTextColor" type="color"><label>Submit button text style</label><div class="menu-style-row"><button type="button" id="formBtnBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="formBtnItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div><label>Submit button alignment</label><div class="menu-align-row"><button type="button" class="menu-align-btn form-btn-align" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn form-btn-align" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn form-btn-align" data-align="right"><i class="fas fa-align-right"></i></button></div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+posControls+moveControls+remove;
        function renderFormFieldsEditor(){
            t.settings=t.settings||{};
            t.settings.fields=normalizeFormFields(t.settings.fields,false);
            var box=document.getElementById("formFieldsEditor");
            if(!box)return;
            var cards=t.settings.fields.map(function(f,idx){
                var lbl=String((f&&f.label)||"Field").replace(/"/g,'&quot;');
                var ph=String((f&&f.placeholder)||lbl).replace(/"/g,'&quot;');
                return '<div class="menu-item-card" data-idx="'+idx+'">'
                    +'<div class="menu-item-head"><strong>Input '+(idx+1)+'</strong><div class="menu-item-actions">'
                    +'<button type="button" class="formFieldMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button>'
                    +'<button type="button" class="formFieldMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button>'
                    +'<button type="button" class="formFieldDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button>'
                    +'</div></div>'
                    +'<label>Label</label><input class="formFieldLabel" data-idx="'+idx+'" value="'+lbl+'" placeholder="Field label">'
                    +'<label>Placeholder</label><input class="formFieldPlaceholder" data-idx="'+idx+'" value="'+ph+'" placeholder="Field placeholder">'
                    +'</div>';
            }).join("");
            box.innerHTML=cards;
            box.querySelectorAll(".formFieldLabel").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.fields[idx])return;
                    saveToHistory();
                    t.settings.fields[idx].label=String(inp.value||"").trim()||("Field "+(idx+1));
                    if(!String((t.settings.fields[idx]&&t.settings.fields[idx].placeholder)||"").trim()){
                        t.settings.fields[idx].placeholder=t.settings.fields[idx].label;
                    }
                    renderCanvas();
                });
            });
            box.querySelectorAll(".formFieldPlaceholder").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.fields[idx])return;
                    saveToHistory();
                    t.settings.fields[idx].placeholder=String(inp.value||"").trim()||t.settings.fields[idx].label||("Field "+(idx+1));
                    renderCanvas();
                });
            });
            box.querySelectorAll(".formFieldDelete").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.fields))return;
                    saveToHistory();
                    t.settings.fields.splice(idx,1);
                    t.settings.fields=normalizeFormFields(t.settings.fields,false);
                    renderFormFieldsEditor();
                    renderCanvas();
                });
            });
            box.querySelectorAll(".formFieldMoveUp").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||idx<=0||!Array.isArray(t.settings.fields))return;
                    saveToHistory();
                    var tmp=t.settings.fields[idx-1];
                    t.settings.fields[idx-1]=t.settings.fields[idx];
                    t.settings.fields[idx]=tmp;
                    renderFormFieldsEditor();
                    renderCanvas();
                });
            });
            box.querySelectorAll(".formFieldMoveDown").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.fields)||idx>=t.settings.fields.length-1)return;
                    saveToHistory();
                    var tmp=t.settings.fields[idx+1];
                    t.settings.fields[idx+1]=t.settings.fields[idx];
                    t.settings.fields[idx]=tmp;
                    renderFormFieldsEditor();
                    renderCanvas();
                });
            });
        }
        var addFormInputBtn=document.getElementById("addFormInput");
        if(addFormInputBtn){
            addFormInputBtn.onclick=function(){
                saveToHistory();
                t.settings=t.settings||{};
                t.settings.fields=normalizeFormFields(t.settings.fields,false);
                var nextIndex=t.settings.fields.length+1;
                t.settings.fields.push({type:"text",label:"Field "+nextIndex,placeholder:"Field "+nextIndex,required:false});
                renderFormFieldsEditor();
                renderCanvas();
            };
        }
        renderFormFieldsEditor();
        bind("formSubmitText",t.content||"Submit",v=>{t.content=v||"Submit";},{undo:true});
        bind("formAlign",(t.settings&&t.settings.alignment)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v||"left";var s=sty();if(s&&Object.prototype.hasOwnProperty.call(s,"textAlign"))delete s.textAlign;},{undo:true});
        bind("formWidth",(t.style&&t.style.width)||(t.settings&&t.settings.width)||"100%",v=>{var w=v||"100%";sty().width=w;sty().height="";sty().maxWidth="";sty().minHeight="";t.settings=t.settings||{};t.settings.width=w;t.settings.formWidth=w;t.settings.height="";t.settings.maxWidth="";t.settings.minHeight="";},{undo:true});
        bind("formLabelColor",(t.settings&&t.settings.labelColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.labelColor=v||"#240E35";},{undo:true});
        bind("formPlaceholderColor",(t.settings&&t.settings.placeholderColor)||"#94a3b8",v=>{t.settings=t.settings||{};t.settings.placeholderColor=v||"#94a3b8";},{undo:true});
        bind("formBtnBgColor",(t.settings&&t.settings.buttonBgColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.buttonBgColor=v||"#240E35";},{undo:true});
        bind("formBtnTextColor",(t.settings&&t.settings.buttonTextColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.buttonTextColor=v||"#ffffff";},{undo:true});
        var formBtnBold=document.getElementById("formBtnBold");
        var formBtnItalic=document.getElementById("formBtnItalic");
        function formBtnStyleState(){
            t.settings=t.settings||{};
            return {bold:t.settings.buttonBold===true,italic:t.settings.buttonItalic===true};
        }
        function syncFormBtnStyleControls(){
            var st=formBtnStyleState();
            if(formBtnBold)formBtnBold.classList.toggle("active",!!st.bold);
            if(formBtnItalic)formBtnItalic.classList.toggle("active",!!st.italic);
            var activeAlign=String((t.settings&&t.settings.buttonAlign)||"left");
            settings.querySelectorAll(".form-btn-align").forEach(function(btn){
                btn.classList.toggle("active",btn.getAttribute("data-align")===activeAlign);
            });
        }
        if(formBtnBold)formBtnBold.onclick=function(){saveToHistory();t.settings=t.settings||{};t.settings.buttonBold=!(t.settings.buttonBold===true);syncFormBtnStyleControls();renderCanvas();};
        if(formBtnItalic)formBtnItalic.onclick=function(){saveToHistory();t.settings=t.settings||{};t.settings.buttonItalic=!(t.settings.buttonItalic===true);syncFormBtnStyleControls();renderCanvas();};
        settings.querySelectorAll(".form-btn-align").forEach(function(btn){
            btn.addEventListener("click",function(){
                saveToHistory();
                t.settings=t.settings||{};
                t.settings.buttonAlign=String(btn.getAttribute("data-align")||"left");
                syncFormBtnStyleControls();
                renderCanvas();
            });
        });
        var formSubmitTextField=document.getElementById("formSubmitText");
        if(formSubmitTextField){
            formSubmitTextField.addEventListener("keydown",function(e){
                if(!(e.ctrlKey||e.metaKey))return;
                var k=String(e.key||"").toLowerCase();
                if(k==="b"){e.preventDefault();if(formBtnBold)formBtnBold.click();}
                if(k==="i"){e.preventDefault();if(formBtnItalic)formBtnItalic.click();}
            });
        }
        syncFormBtnStyleControls();
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        var bgUpForm=document.getElementById("bgUp");
        if(bgUpForm)bgUpForm.onchange=()=>{if(bgUpForm.files&&bgUpForm.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUpForm.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
    } else if(selKind==="el"&&t.type==="icon"){
        t.settings=t.settings||{};
        t.settings.iconName=sanitizeIconName(t.settings.iconName||"star");
        t.settings.iconStyle=sanitizeIconStyle(t.settings.iconStyle||"solid");
        t.settings.alignment=(["left","center","right"].indexOf(String(t.settings.alignment||"center"))>=0)?String(t.settings.alignment||"center"):"center";
        settings.innerHTML='<div class="menu-section-title">Icon</div><div class="icon-preview-box" id="iconPreviewBox"></div><button type="button" id="openIconPicker" class="fb-btn icon-picker-btn">Choose icon</button><label>Search</label><input id="iconSearch" placeholder="home, user, check"><label>Icon style</label><select id="iconStyle"><option value="solid">Solid</option><option value="regular">Regular</option><option value="brands">Brands</option></select><div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="iconAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Link URL (optional)</label><input id="iconLink" placeholder="/contact or https://example.com"><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Color</label><input id="iconColor" type="color"><label>Size</label><div class="px-wrap"><input id="iconSize" type="number" step="1"><span class="px-unit">px</span></div><label>Background color</label><input id="iconBg" type="color"><label>Border radius</label><div class="px-wrap"><input id="iconRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'+posControls+moveControls+remove;
        function renderIconPreview(){
            var box=document.getElementById("iconPreviewBox");
            if(!box)return;
            var iconName=sanitizeIconName(t.settings.iconName||"star");
            var iconStyle=sanitizeIconStyle(t.settings.iconStyle||"solid");
            var iconColor=(t.style&&t.style.color)||"#2E1244";
            var iconSize=(t.style&&t.style.fontSize)||"36px";
            box.innerHTML='<i class="'+iconClassName(iconName,iconStyle)+'" style="font-size:'+String(iconSize).replace(/"/g,'&quot;')+';color:'+String(iconColor).replace(/"/g,'&quot;')+';"></i><span style="margin-left:8px;font-size:12px;color:#334155;font-weight:700;">'+iconName+'</span>';
        }
        function pickFromSearch(){
            var q=String(document.getElementById("iconSearch")&&document.getElementById("iconSearch").value||"").trim().toLowerCase();
            var style=sanitizeIconStyle(document.getElementById("iconStyle")&&document.getElementById("iconStyle").value||t.settings.iconStyle||"solid");
            if(q==="")return;
            var match=iconCatalog.find(function(ic){
                if(!Array.isArray(ic.styles)||ic.styles.indexOf(style)<0)return false;
                var hay=(String(ic.name||"")+" "+String(ic.label||"")+" "+String(ic.keywords||"")).toLowerCase();
                return hay.indexOf(q)>=0;
            });
            if(match){
                saveToHistory();
                t.settings.iconName=sanitizeIconName(match.name);
                t.settings.iconStyle=style;
                renderIconPreview();
                renderCanvas();
            }
        }
        bind("iconStyle",t.settings.iconStyle,v=>{t.settings.iconStyle=sanitizeIconStyle(v||"solid");renderIconPreview();},{undo:true});
        bind("iconAlign",t.settings.alignment||"center",v=>{t.settings.alignment=(v==="left"||v==="right"||v==="center")?v:"center";},{undo:true});
        bind("iconLink",t.settings.link||"",v=>{t.settings.link=String(v||"").trim();},{undo:true});
        bind("iconColor",(t.style&&t.style.color)||"#2E1244",v=>sty().color=v,{undo:true});
        bindPx("iconSize",(t.style&&t.style.fontSize)||"36px",v=>sty().fontSize=v,{undo:true});
        bind("iconBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bindPx("iconRadius",(t.style&&t.style.borderRadius)||"0px",v=>sty().borderRadius=v,{undo:true});
        var iconSearch=document.getElementById("iconSearch");
        var iconStyle=document.getElementById("iconStyle");
        if(iconSearch){
            iconSearch.value=t.settings.iconName||"";
            iconSearch.addEventListener("change",pickFromSearch);
            iconSearch.addEventListener("keydown",function(e){if(e.key==="Enter"){e.preventDefault();pickFromSearch();}});
        }
        if(iconStyle)iconStyle.addEventListener("change",pickFromSearch);
        var openPicker=document.getElementById("openIconPicker");
        if(openPicker)openPicker.onclick=function(){
            openIconPickerModal({
                search:(iconSearch&&iconSearch.value)||"",
                style:(iconStyle&&iconStyle.value)||t.settings.iconStyle||"solid",
                onPick:function(chosen){
                    saveToHistory();
                    t.settings.iconName=sanitizeIconName(chosen&&chosen.name||"star");
                    t.settings.iconStyle=sanitizeIconStyle(chosen&&chosen.style||"solid");
                    if(iconSearch)iconSearch.value=t.settings.iconName;
                    if(iconStyle)iconStyle.value=t.settings.iconStyle;
                    renderIconPreview();
                    renderCanvas();
                }
            });
        };
        renderIconPreview();
    } else if(selKind==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var textTypographyControls=(t.type==="text"||t.type==="heading")
            ? '<label>Line height</label><input id="lh" placeholder="1.5"><label>Letter spacing</label><div class="px-wrap"><input id="ls" type="number" step="0.1"><span class="px-unit">px</span></div>'
            : '';
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>↔</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>↔</span></button><span>Link</span></div></div></div>';
        var buttonBgControl=(t.type==="button")?'<label>Button color</label><input id="btnBg" type="color">':'';
        var buttonWrapBgControl=(t.type==="button")?'<label>Background color</label><input id="btnWrapBg" type="color">':'';
        var buttonTextStyleControl=(t.type==="button")?'<label>Text style</label><div class="menu-style-row"><button type="button" id="btnBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="btnItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div>':'';
        var buttonRadiusControl=(t.type==="button")?(radiusHelpLabelHtml("btnRadiusHelp","Border radius")+'<div class="px-wrap"><input id="btnRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'):'';
        var buttonStepOptions=(t.type==="button")?steps.filter(function(s){return String(s.id)!==String(state.sid);}).map(function(s){
            var sl=String(s.slug||"").replace(/"/g,'&quot;');
            var tt=String(s.title||s.slug||"Untitled").replace(/"/g,'&quot;');
            return '<option value="'+sl+'">'+tt+' ('+sl+')</option>';
        }).join(''):'';
        var buttonStepDisabled=false;
        if(t.type==="button" && buttonStepOptions===""){
            buttonStepOptions='<option value="">No other pages found</option>';
            buttonStepDisabled=true;
        }
        var buttonActionControl=(t.type==="button")
            ? '<label>Button action</label><select id="btnAction"><option value="next_step">Next step</option><option value="step">Specific step</option><option value="link">Custom URL</option><option value="checkout">Checkout submit</option><option value="offer_accept">Accept offer</option><option value="offer_decline">Decline offer</option></select><div id="btnStepWrap" style="display:none;"><label>Target page</label><select id="btnStep"'+(buttonStepDisabled?' disabled':'')+'>'+buttonStepOptions+'</select></div><div id="btnLinkWrap" style="display:none;"><label>Link URL</label><input id="btnLink" placeholder="/contact or https://example.com"></div>'
            : '';
        var sharedBgControls=(t.type==="button")?'':'<label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">';
        settings.innerHTML='<div class="menu-section-title">Content</div>'+(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button" title="Bold (Ctrl+B)"><b>B</b></button><button id="rtItalic" type="button" title="Italic (Ctrl+I)"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+buttonActionControl+'<div class="menu-split"></div><div class="menu-section-title">Layout</div><label>Alignment</label><select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div>'+sizeBlock+'<div class="menu-split"></div><div class="menu-section-title">Style</div>'+buttonWrapBgControl+buttonBgControl+buttonRadiusControl+buttonTextStyleControl+sharedBgControls+'<label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+textTypographyControls+fontSelectHtml('ff')+posControls+moveControls+remove;
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
        var defaultTextColor=(t.type==="text"||t.type==="heading")?"#000000":"#334155";
        if(t.type==="button"){
            var coBtn=document.getElementById("co");
            if(coBtn){
                coBtn.value=(t.style&&t.style.color)||"#ffffff";
                var coRaf=0,coDraft="",coStarted=false;
                coBtn.addEventListener("input",()=>{
                    coDraft=coBtn.value||"#ffffff";
                    if(!coStarted){saveToHistory();coStarted=true;}
                    if(coRaf)return;
                    coRaf=requestAnimationFrame(()=>{
                        coRaf=0;
                        sty().color=coDraft;
                        refreshAfterSetting();
                    });
                });
                coBtn.addEventListener("change",()=>{
                    if(!coStarted)saveToHistory();
                    coStarted=false;
                    sty().color=coBtn.value||"#ffffff";
                    refreshAfterSetting();
                });
            }
        } else {
            bind("co",(t.style&&t.style.color)||defaultTextColor,v=>sty().color=v,{undo:true});
        }
        if(t.type!=="button"){
            bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
            var bgUpGeneric=document.getElementById("bgUp");
            if(bgUpGeneric)bgUpGeneric.onchange=()=>{if(bgUpGeneric.files&&bgUpGeneric.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUpGeneric.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        }
        bindPx("fs",(t.style&&t.style.fontSize)||"",v=>sty().fontSize=v,{undo:true});bind("ff",(t.style&&t.style.fontFamily)||"Inter, sans-serif",v=>sty().fontFamily=v,{undo:true});
        if(t.type==="button"){
            var btnBold=document.getElementById("btnBold"),btnItalic=document.getElementById("btnItalic");
            function btnTextStyleState(){
                var fw=String((t.style&&t.style.fontWeight)||"").toLowerCase();
                var fs=String((t.style&&t.style.fontStyle)||"").toLowerCase();
                return {bold:(fw==="bold"||Number(fw)>=600),italic:(fs==="italic")};
            }
            function syncBtnTextStyleControls(){
                var st=btnTextStyleState();
                if(btnBold)btnBold.classList.toggle("active",!!st.bold);
                if(btnItalic)btnItalic.classList.toggle("active",!!st.italic);
            }
            if(btnBold)btnBold.onclick=()=>{saveToHistory();var st=btnTextStyleState();sty().fontWeight=st.bold?"400":"700";syncBtnTextStyleControls();renderCanvas();};
            if(btnItalic)btnItalic.onclick=()=>{saveToHistory();var st=btnTextStyleState();sty().fontStyle=st.italic?"normal":"italic";syncBtnTextStyleControls();renderCanvas();};
            var contentField=document.getElementById("content");
            if(contentField){
                contentField.addEventListener("keydown",e=>{
                    if(!(e.ctrlKey||e.metaKey))return;
                    var k=String(e.key||"").toLowerCase();
                    if(k==="b"){e.preventDefault();if(btnBold)btnBold.click();}
                    if(k==="i"){e.preventDefault();if(btnItalic)btnItalic.click();}
                });
            }
            syncBtnTextStyleControls();
            bind("btnWrapBg",(t.settings&&t.settings.containerBgColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.containerBgColor=v;},{undo:true});
            t.settings=t.settings||{};
            var allowedActionTypes=["next_step","step","link","checkout","offer_accept","offer_decline"];
            var curAction=String(t.settings.actionType||"").trim().toLowerCase();
            if(allowedActionTypes.indexOf(curAction)<0){
                var legacyLink=String(t.settings.link||"").trim();
                curAction=(legacyLink!==""&&legacyLink!=="#")?"link":"next_step";
            }
            t.settings.actionType=curAction;
            if(typeof t.settings.actionStepSlug!=="string")t.settings.actionStepSlug="";
            var btnAction=document.getElementById("btnAction"),btnStep=document.getElementById("btnStep");
            var btnStepWrap=document.getElementById("btnStepWrap"),btnLinkWrap=document.getElementById("btnLinkWrap");
            function syncButtonActionUi(){
                var mode=String((t.settings&&t.settings.actionType)||"next_step");
                if(btnAction)btnAction.value=mode;
                if(btnStepWrap)btnStepWrap.style.display=(mode==="step")?"block":"none";
                if(btnLinkWrap)btnLinkWrap.style.display=(mode==="link")?"block":"none";
                if(btnStep){
                    var wanted=String((t.settings&&t.settings.actionStepSlug)||"");
                    var hasOption=Array.from(btnStep.options||[]).some(function(o){return String(o.value)===wanted;});
                    if(hasOption)btnStep.value=wanted;
                    else if(btnStep.options&&btnStep.options.length)btnStep.value=btnStep.options[0].value;
                }
            }
            if(btnAction){
                btnAction.value=curAction;
                btnAction.addEventListener("change",function(){
                    saveToHistory();
                    t.settings=t.settings||{};
                    t.settings.actionType=String(btnAction.value||"next_step");
                    syncButtonActionUi();
                    renderCanvas();
                });
            }
            if(btnStep){
                var initialSlug=String(t.settings.actionStepSlug||"");
                var hasInitial=Array.from(btnStep.options||[]).some(function(o){return String(o.value)===initialSlug;});
                if(hasInitial)btnStep.value=initialSlug;
                else if(btnStep.options&&btnStep.options.length){
                    btnStep.value=btnStep.options[0].value;
                    t.settings.actionStepSlug=String(btnStep.value||"");
                }
                btnStep.addEventListener("change",function(){
                    saveToHistory();
                    t.settings=t.settings||{};
                    t.settings.actionStepSlug=String(btnStep.value||"").trim();
                    renderCanvas();
                });
            }
            syncButtonActionUi();
            bind("btnLink",(t.settings&&t.settings.link)||"#",v=>{t.settings=t.settings||{};var u=String(v||"").trim();t.settings.link=(u==="")?"#":u;},{undo:true});
            bindPx("btnRadius",(t.style&&t.style.borderRadius)||"",v=>sty().borderRadius=v,{undo:true});
            bindRadiusHelpButton("btnRadiusHelp");
            var btnBg=document.getElementById("btnBg");
            if(btnBg){
                btnBg.value=(t.style&&t.style.backgroundColor)||"#240E35";
                var btnBgRaf=0,btnBgDraft="",btnBgStarted=false;
                btnBg.addEventListener("input",()=>{
                    btnBgDraft=btnBg.value||"#240E35";
                    if(!btnBgStarted){saveToHistory();btnBgStarted=true;}
                    if(btnBgRaf)return;
                    btnBgRaf=requestAnimationFrame(()=>{
                        btnBgRaf=0;
                        sty().backgroundColor=btnBgDraft;
                        refreshAfterSetting();
                    });
                });
                btnBg.addEventListener("change",()=>{
                    if(!btnBgStarted)saveToHistory();
                    btnBgStarted=false;
                    sty().backgroundColor=btnBg.value||"#240E35";
                    refreshAfterSetting();
                });
            }
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
    ensureSpacingHelperButton();
    mountBackgroundImageDisplayControl();
    mountPositionControls();
    const btnMoveUp=document.getElementById("btnMoveUp");if(btnMoveUp)btnMoveUp.onclick=()=>moveSelectedBySelection(-1);
    const btnMoveDown=document.getElementById("btnMoveDown");if(btnMoveDown)btnMoveDown.onclick=()=>moveSelectedBySelection(1);
    const btnDel=document.getElementById("btnDeleteSelected");if(btnDel)btnDel.onclick=()=>removeSelected();
}

function render(){renderCanvas();renderSettings();if(state.sel||state.carouselSel)showLeftPanel("settings");}

var _sidebarDragActive=false;
document.querySelectorAll(".fb-lib button").forEach(b=>{
    b.ondragstart=e=>{_sidebarDragActive=true;e.dataTransfer.setData("c",b.dataset.c||"");};
    b.ondragend=e=>{setTimeout(()=>{_sidebarDragActive=false;},100);};
    b.onclick=()=>{if(_sidebarDragActive){_sidebarDragActive=false;return;}addComponent(b.dataset.c||"");render();};
});

wireStepManagement();
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
var _canvasLockedWidth=0;
var _canvasInnerWidth=0;
function lockCanvasWidth(){
    if(!canvas)return;
    if(fbGrid&&fbGrid.classList.contains("components-hidden"))return;
    var w=canvas.offsetWidth;
    if(w>200){
        _canvasLockedWidth=w;
        var innerW=canvas.clientWidth||0; // includes padding, excludes scrollbar
        if(innerW>0)_canvasInnerWidth=innerW;
        canvas.style.width=w+"px";canvas.style.maxWidth=w+"px";
    }
}
function unlockAndRelockCanvas(){
    if(!canvas)return;
    canvas.style.width="100%";canvas.style.maxWidth="none";
    setTimeout(()=>{lockCanvasWidth();},250);
}
requestAnimationFrame(()=>{lockCanvasWidth();});
var _resizeTimer=null;
window.addEventListener("resize",()=>{
    clearTimeout(_resizeTimer);
    _resizeTimer=setTimeout(()=>{
        if(fbGrid&&!fbGrid.classList.contains("components-hidden")){unlockAndRelockCanvas();}
        else{
            if(canvas){canvas.style.width="100%";canvas.style.maxWidth="none";}
        }
    },150);
});
if(fbComponentsHide)fbComponentsHide.onclick=()=>{if(fbGrid){fbGrid.classList.add("components-hidden");if(canvas){canvas.style.width="100%";canvas.style.maxWidth="none";}}};
if(fbComponentsShow)fbComponentsShow.onclick=()=>{if(fbGrid){fbGrid.classList.remove("components-hidden");if(_canvasLockedWidth>0&&canvas){canvas.style.width=_canvasLockedWidth+"px";canvas.style.maxWidth=_canvasLockedWidth+"px";}unlockAndRelockCanvas();}};
function persistCurrentStep(){
    const s=cur();if(!s)return;
    if(!_autoSaveMode && document.activeElement&&typeof document.activeElement.blur==="function")document.activeElement.blur();
    var t=selectedTarget();
    if(t && state.sel && (state.sel.k==="sec"||state.sel.k==="row"||state.sel.k==="col")){
        var bgIn=document.getElementById("bg");
        var bgImgIn=document.getElementById("bgImg");
        if(bgIn && typeof bgIn.value==="string" && bgIn.value.trim()!==""){
            t.style=t.style||{};
            t.style.backgroundColor=bgIn.value.trim();
        }
        if(bgImgIn && typeof bgImgIn.value==="string"){
            var bgu=bgImgIn.value.trim();
            t.style=t.style||{};
            t.style.backgroundImage=bgu!==""?('url('+bgu+')'):"";
        }
    }
    if(t&&(t.type==="video"||t.type==="image")){
        var wIn=document.getElementById("w");
        if(wIn){var v=(wIn.value||"").trim();if(v){var w=pxIfNumber(v);t.style=t.style||{};t.style.width=w;t.settings=t.settings||{};t.settings.width=w;}}
    }
    if(t&&t.settings&&t.settings.positionMode==="absolute"){
        var px=document.getElementById("elPosX"),py=document.getElementById("elPosY");
        if(px){var xv=Math.max(0,Number(px.value)||0);t.style=t.style||{};t.settings=t.settings||{};t.style.left=xv+"px";t.settings.freeX=xv;}
        if(py){var yv=Math.max(0,Number(py.value)||0);t.style=t.style||{};t.settings=t.settings||{};t.style.top=yv+"px";t.settings.freeY=yv;}
        var sw=document.getElementById("elSizeW"),sh=document.getElementById("elSizeH"),zi=document.getElementById("elZIndex");
        if(sw&&Number(sw.value)>0){t.style.width=Math.max(30,Number(sw.value))+"px";}
        if(sh&&Number(sh.value)>0){t.style.height=Math.max(20,Number(sh.value))+"px";}
        if(zi){t.style.zIndex=String(Math.max(0,Number(zi.value)||0));}
    }
    if(t&&t.type==="form"){
        var fw=document.getElementById("formWidth");
        if(fw){var fv=(fw.value||"").trim();var fwv=pxIfNumber(fv||"100%");t.style=t.style||{};t.style.width=fwv;t.settings=t.settings||{};t.settings.width=fwv;t.settings.formWidth=fwv;}
        t.style=t.style||{};t.settings=t.settings||{};
        t.style.height="";t.style.maxWidth="";t.style.minHeight="";
        t.settings.height="";t.settings.maxWidth="";t.settings.minHeight="";
        var fa=document.getElementById("formAlign");
        if(fa){var aval=(fa.value||"left");t.settings=t.settings||{};t.settings.alignment=aval;t.style=t.style||{};t.style.textAlign=aval;}
    }
    var prefs=editorPrefs();
    if(_canvasLockedWidth>0)prefs.canvasWidth=_canvasLockedWidth;
    if(_canvasInnerWidth>0)prefs.canvasInnerWidth=_canvasInnerWidth;
    var canvasBg=normalizeCanvasBgValue(prefs.canvasBg||"");
    var requestHeaders={"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"};
    function saveStepLayout(stepId,layout,bg){
        return fetch(saveUrl,{
            method:"POST",
            headers:requestHeaders,
            body:JSON.stringify({step_id:stepId,layout_json:layout,background_color:bg})
        }).then(function(r){
            if(!r.ok)throw new Error("Save failed");
            return r.json();
        });
    }
    saveMsg.textContent=_autoSaveMode?"Autosaving...":"Saving...";
    return saveStepLayout(s.id,state.layout,canvasBg)
        .then(function(p){
            s.layout_json=p.layout_json||clone(state.layout);
            s.background_color=(p&&typeof p.background_color==="string"&&p.background_color.trim()!=="")?p.background_color.trim():null;
            var others=steps.filter(function(step){return +step.id!==+s.id;});
            if(!others.length)return null;
            var jobs=others.map(function(step){
                var stepLayout=(step.layout_json&&typeof step.layout_json==="object")?step.layout_json:defaults(step.type);
                stepLayout=withCanvasBgInLayout(stepLayout,canvasBg);
                return saveStepLayout(step.id,stepLayout,canvasBg).then(function(resp){
                    step.layout_json=resp.layout_json||clone(stepLayout);
                    step.background_color=(resp&&typeof resp.background_color==="string"&&resp.background_color.trim()!=="")?resp.background_color.trim():null;
                    return true;
                });
            });
            return Promise.all(jobs);
        })
        .then(function(){
            saveMsg.textContent=(_autoSaveMode?"Autosaved ":"Saved all pages ")+new Date().toLocaleTimeString();
        });
}
document.getElementById("saveBtn").onclick=()=>{
    persistCurrentStep().catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
document.getElementById("previewBtn").onclick=()=>{
    const s=cur();if(!s)return;
    flushAutoSave()
        .then(()=>persistCurrentStep())
        .then(()=>{window.open(previewTpl.replace("__STEP__",String(s.id)),"_blank");})
        .catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
document.addEventListener("keydown",e=>{
    const key=String(e.key||"").toLowerCase();
    const ae=document.activeElement;
    const isTextField=!!(ae && (ae.tagName==="INPUT" || ae.tagName==="TEXTAREA" || ae.isContentEditable));
    if(key==="escape"){
        if(state.editingEl){state.editingEl=null;renderCanvas();return;}
        closeRadiusHelpModal();
        closeSpacingHelpModal();
        closePageManagerModal();
        hideContextMenu();
    }

    if((e.ctrlKey||e.metaKey)&&key==="s"){e.preventDefault();document.getElementById("saveBtn").click();return;}
    if((e.ctrlKey||e.metaKey)&&key==="c"&&!isTextField){
        e.preventDefault();
        if(copySelectedToClipboard()){
            showBuilderToast("Copied component.","success");
        }
        return;
    }
    if((e.ctrlKey||e.metaKey)&&key==="v"&&!isTextField){
        e.preventDefault();
        if(pasteFromClipboard()){
            render();
            showBuilderToast("Pasted component.","success");
        }else{
            showBuilderToast("Paste failed for this target.","error");
        }
        return;
    }

    if((e.ctrlKey||e.metaKey)&&(key==="b"||key==="i"||key==="u")){
        var selT=selectedTarget();
        if(selT&&selT.type==="menu"&&!isTextField&&(key==="b"||key==="i")){
            e.preventDefault();
            saveToHistory();
            selT.style=selT.style||{};
            if(key==="b"){
                var fw=String(selT.style.fontWeight||"").toLowerCase();
                var isBold=(fw==="bold"||Number(fw)>=600);
                selT.style.fontWeight=isBold?"400":"700";
            }else if(key==="i"){
                var fs=String(selT.style.fontStyle||"").toLowerCase();
                selT.style.fontStyle=(fs==="italic")?"normal":"italic";
            }
            renderCanvas();
            renderSettings();
            return;
        }
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

    // Arrow keys for moving selected item up/down (element or structure), not in text fields
    if((key==="arrowup"||key==="arrowdown") && (state.sel||state.carouselSel) && !isTextField){
        e.preventDefault();
        if(key==="arrowup"){
            moveSelectedBySelection(-1);
        }else{
            moveSelectedBySelection(1);
        }
    }

    if(key==="z"&&(e.ctrlKey||e.metaKey)&&!e.shiftKey&&!isTextField){
        e.preventDefault();
        undo();
    }
});
document.addEventListener("dragend",clearFreeDropGuides);
document.addEventListener("drop",clearFreeDropGuides);

initDimTipHover();
initContextMenu();
loadStep(state.sid);
})();
</script>
@endsection
