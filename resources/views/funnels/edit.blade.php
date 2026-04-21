@extends('layouts.admin')
@section('title', 'Funnel Builder')
@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Manrope:wght@400;600;700;800&family=Montserrat:wght@400;600;700;800&family=Nunito:wght@400;600;700;800&family=Open+Sans:wght@400;600;700;800&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;600;700;800&family=Raleway:wght@400;600;700;800&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/extracted/funnels-edit-style1.css') }}">
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
#fbLeftPanelTemplates .fb-card{max-height:calc(100vh - 120px);overflow-y:auto;overflow-x:hidden}
#fbLeftPanelTemplates .fb-card::-webkit-scrollbar{width:8px}
#fbLeftPanelTemplates .fb-card::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
#fbLeftPanelTemplates .fb-card::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
#fbLeftPanelTemplates .fb-card::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-grid > *{min-width:0}
.fb-grid .fb-card{overflow-x:hidden}
.fb-canvas-col > .fb-card{overflow-x:visible}
#canvas{overflow-x:hidden;overflow-y:auto;box-sizing:border-box}
#canvas .sec,#canvas .row,#canvas .col,#canvas .el,#canvas .sec-inner,#canvas .row-inner,#canvas .col-inner{max-width:100%;box-sizing:border-box}
#canvas img,#canvas video,#canvas iframe{max-width:100%;height:auto}
.fb-image-placeholder{width:100%;min-height:140px;border:2px solid #6ea0ff;border-radius:12px;background:linear-gradient(180deg,#ffffff,#fbfcff);display:block;position:relative;box-sizing:border-box;overflow:hidden}
.fb-image-placeholder--compact{min-height:84px;border-width:1px;border-style:dashed;border-color:#b9c9ea;border-radius:10px;background:#fcfdff}
.fb-image-placeholder__center{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:18px}
.fb-image-placeholder__plus{width:58px;height:58px;border-radius:999px;background:#d9d9d9;color:#5f6368;display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:300;line-height:1;box-shadow:0 6px 18px rgba(15,23,42,.16);border:none}
.fb-image-placeholder__center .fb-image-placeholder__plus{cursor:pointer}
.fb-image-placeholder--compact .fb-image-placeholder__center{justify-content:flex-start;gap:12px;position:static;padding:18px;height:100%}
.fb-image-placeholder--compact .fb-image-placeholder__plus{width:34px;height:34px;font-size:26px;box-shadow:none;background:#ffffff;border:1px solid #240E35;color:#240E35;cursor:default}
.fb-image-placeholder__label{position:absolute;left:16px;right:16px;bottom:18px;text-align:center;font-size:14px;font-weight:600;color:#5f6368;letter-spacing:.01em;pointer-events:none}
.fb-image-placeholder--compact .fb-image-placeholder__label{position:static;text-align:left;font-size:13px;color:#1f2937;font-weight:500}
.fb-image-placeholder--actionable{cursor:pointer}
.fb-image-placeholder__menu{position:fixed;left:0;top:0;transform:none;margin-top:0;display:flex;flex-direction:column;gap:10px;min-width:230px;padding:14px;border:2px solid #240E35;border-radius:16px;background:rgba(255,255,255,.98);box-shadow:0 22px 48px rgba(36,14,53,.26);z-index:2200;backdrop-filter:blur(8px)}
.fb-image-placeholder__menu[hidden]{display:none}
.fb-image-placeholder__menu::before{content:"Choose image";font-size:11px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;color:#6B4A7A}
.fb-image-placeholder__menu button{width:100%;min-height:46px;border:2px solid #D8C8EA;border-radius:12px;background:#ffffff;color:#240E35;padding:12px 14px;text-align:left;font-size:15px;font-weight:800;cursor:pointer;box-shadow:0 6px 14px rgba(36,14,53,.08);transition:transform .12s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease}
.fb-image-placeholder__menu button:hover{background:#F8F5FB;border-color:#240E35;box-shadow:0 10px 22px rgba(36,14,53,.14);transform:translateY(-1px)}
#fbSettingsCard{display:flex;flex-direction:column;max-height:calc(100vh - 120px);min-height:0}
#fbSettingsCard .fb-h{flex-shrink:0}
#fbSettingsCard #settings{overflow-y:auto;overflow-x:hidden;flex:1;min-height:0;padding-right:4px}
#fbSettingsCard #settings::-webkit-scrollbar{width:8px}
#fbSettingsCard #settings::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
#fbSettingsCard #settings::-webkit-scrollbar-thumb:hover{background:#94a3b8}
#fbLeftPanelHistory .fb-card{display:flex;flex-direction:column;max-height:calc(100vh - 120px);min-height:0}
#fbHistoryContainer{overflow-y:auto;overflow-x:hidden;flex:1;min-height:0;padding-right:4px}
#fbHistoryContainer::-webkit-scrollbar{width:8px}
#fbHistoryContainer::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
#fbHistoryContainer::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
#fbHistoryContainer::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.fb-history-shell{display:flex;flex-direction:column;gap:10px;padding-bottom:6px}
.fb-history-status{border-radius:12px;padding:12px}
.fb-history-label{padding:2px 2px 0;font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.06em}
.fb-history-note{padding:0 2px 2px;color:#64748b;font-size:12px;line-height:1.45}
.fb-history-accordion{display:flex;flex-direction:column;border-top:1px solid #EEE7F5}
.fb-history-group{border-bottom:1px solid #EEE7F5}
.fb-history-group-toggle{width:100%;display:grid;grid-template-columns:minmax(0,1fr) 20px;align-items:center;gap:10px;padding:12px 2px;border:none;background:transparent;cursor:pointer;text-align:left}
.fb-history-group-main{min-width:0;display:flex;flex-direction:column;gap:2px}
.fb-history-group-title{font-size:14px;font-weight:800;color:#240E35;line-height:1.2}
.fb-history-group-meta{font-size:12px;color:#6B7280;line-height:1.25}
.fb-history-group-arrow{width:20px;height:20px;display:flex;align-items:center;justify-content:center;color:#6B4A7A;font-size:13px}
.fb-history-group-panel{padding:0 0 8px}
.fb-history-entry{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:10px;padding:10px 0 10px 12px;margin-left:6px;border-left:2px solid #EEE7F5}
.fb-history-entry + .fb-history-entry{border-top:1px solid #F6F1FA}
.fb-history-entry.is-open{border-left-color:#10B981;background:linear-gradient(90deg,rgba(16,185,129,.08),rgba(255,255,255,0));border-radius:0 12px 12px 0}
.fb-history-entry-text{min-width:0;display:flex;flex-direction:column;gap:4px}
.fb-history-entry-title-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;min-width:0}
.fb-history-entry-title{font-size:14px;font-weight:800;color:#240E35;line-height:1.2}
.fb-history-entry-meta{font-size:12px;color:#64748b;line-height:1.25}
.fb-history-tag{display:inline-flex;align-items:center;justify-content:center;padding:2px 7px;border-radius:999px;background:#ECFDF5;color:#047857;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;white-space:nowrap}
.fb-history-action{min-height:32px;padding:7px 12px;border:none;border-radius:999px;background:#240E35;color:#fff;white-space:nowrap;box-shadow:none;font-size:12px}
.fb-history-pill{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;background:#F3EEF7;color:#240E35;font-size:11px;font-weight:800;white-space:nowrap;border:1px solid #E6E1EF}
.fb-h{font-size:16px;font-weight:900;color:#240E35;margin:2px 0 10px}
.fb-lib button{display:block;width:100%;text-align:left;margin-bottom:7px;padding:9px;border:1px solid #E7D8F0;border-radius:8px;background:#F3EEF7;font-weight:700;cursor:grab}
.fb-lib button.fb-template-btn{cursor:pointer;background:#ffffff;border-style:dashed}
.fb-lib button.fb-template-btn:hover{background:#F8F5FB}
.fb-lib i{width:18px;margin-right:6px;color:#2E1244}
.fb-comp-drag-ghost{width:240px;padding:12px;border:1px solid #E7D8F0;border-radius:14px;background:linear-gradient(180deg,#ffffff,#FBF8FE);box-shadow:0 18px 38px rgba(36,14,53,.18);display:flex;flex-direction:column;gap:10px;position:fixed;top:-1000px;left:-1000px;pointer-events:none;z-index:2000}
.fb-comp-drag-ghost__head{display:flex;align-items:flex-start;gap:10px}
.fb-comp-drag-ghost__icon{width:34px;height:34px;border-radius:10px;background:#F3EEF7;border:1px solid #E7D8F0;display:flex;align-items:center;justify-content:center;flex:0 0 auto}
.fb-comp-drag-ghost__icon i{width:auto;margin-right:0;font-size:14px;color:#240E35}
.fb-comp-drag-ghost__copy{min-width:0;display:grid;gap:3px}
.fb-comp-drag-ghost__label{display:block;font-size:14px;line-height:1.15;font-weight:800;color:#240E35}
.fb-comp-drag-ghost__desc{display:block;font-size:11px;line-height:1.35;color:#6B7280;font-weight:600}
.fb-comp-drag-ghost__preview{display:block;padding:10px;border-radius:12px;border:1px solid #ECE4F3;background:linear-gradient(180deg,#ffffff,#FAF7FD);overflow:hidden}
.fb-comp-preview-stack{display:grid;gap:5px}
.fb-comp-preview-line{display:block;height:6px;border-radius:999px;background:#DACDEA}
.fb-comp-preview-line.is-dark{background:#240E35}
.fb-comp-preview-line.sm{width:42%}
.fb-comp-preview-line.md{width:62%}
.fb-comp-preview-line.lg{width:80%}
.fb-comp-preview-line.full{width:100%}
.fb-comp-preview-pill{display:inline-flex;align-items:center;justify-content:center;height:14px;padding:0 8px;border-radius:999px;background:#E9DCF4;color:#5B2A79;font-size:9px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
.fb-comp-preview-btn{display:inline-flex;align-items:center;justify-content:center;height:16px;padding:0 12px;border-radius:999px;background:#240E35;color:#fff;font-size:9px;font-weight:800;white-space:nowrap;align-self:flex-start}
.fb-comp-preview-frame{height:42px;border:1px dashed #D8C8E9;border-radius:10px;background:linear-gradient(180deg,#fff,#F8F5FB);padding:6px;display:flex;align-items:center;justify-content:center}
.fb-comp-preview-shell{width:100%;height:100%;border-radius:8px;border:1px dashed #D8C8E9;background:rgba(243,238,247,.85)}
.fb-comp-preview-grid{display:grid;gap:6px;height:42px}
.fb-comp-preview-grid--two{grid-template-columns:repeat(2,minmax(0,1fr))}
.fb-comp-preview-grid--three{grid-template-columns:repeat(3,minmax(0,1fr))}
.fb-comp-preview-cell{border-radius:8px;border:1px solid #E7D8F0;background:#F3EEF7}
.fb-comp-preview-spacer{height:42px;border-radius:10px;border:1px dashed #D8C8E9;background:linear-gradient(180deg,#fff,#F8F5FB);position:relative}
.fb-comp-preview-spacer::before,.fb-comp-preview-spacer::after{content:"";position:absolute;top:8px;bottom:8px;width:1px;background:#B99FD2}
.fb-comp-preview-spacer::before{left:28%}
.fb-comp-preview-spacer::after{right:28%}
.fb-comp-preview-spacer-label{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);padding:3px 8px;border-radius:999px;background:#EFE7F7;color:#6B4A7A;font-size:9px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.fb-comp-preview-button-wrap{height:42px;border-radius:10px;border:1px solid #ECE4F3;background:linear-gradient(180deg,#fff,#FAF7FD);display:flex;align-items:center;justify-content:center}
.fb-comp-preview-icon-wrap{height:42px;border-radius:10px;border:1px solid #ECE4F3;background:linear-gradient(180deg,#fff,#FAF7FD);display:flex;align-items:center;gap:8px;padding:0 10px}
.fb-comp-preview-icon-chip{width:22px;height:22px;border-radius:999px;background:#F3EEF7;border:1px solid #E7D8F0;display:flex;align-items:center;justify-content:center;color:#240E35;font-size:11px}
.fb-comp-preview-nav{display:flex;gap:6px;align-items:center;flex-wrap:nowrap}
.fb-comp-preview-nav-item{height:14px;border-radius:999px;background:#F3EEF7;border:1px solid #E7D8F0}
.fb-comp-preview-nav-item.is-wide{width:48px}
.fb-comp-preview-nav-item.is-mid{width:34px}
.fb-comp-preview-nav-item.is-cta{width:44px;background:#240E35;border-color:#240E35}
.fb-comp-preview-media{height:42px;border-radius:10px;border:1px solid #E7D8F0;background:linear-gradient(135deg,#F9F6FC,#E8DDF3);position:relative;overflow:hidden}
.fb-comp-preview-media-sun{position:absolute;top:7px;right:8px;width:8px;height:8px;border-radius:999px;background:#fff;opacity:.95}
.fb-comp-preview-media-ridge{position:absolute;left:7px;right:7px;bottom:7px;height:15px;background:linear-gradient(180deg,#D6C4E7,#C4ACDD);clip-path:polygon(0 100%,22% 35%,40% 68%,64% 16%,100% 100%)}
.fb-comp-preview-media-play{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:22px;height:22px;border-radius:999px;background:rgba(36,14,53,.86);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;box-shadow:0 4px 10px rgba(36,14,53,.18)}
.fb-comp-preview-carousel{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;align-items:end}
.fb-comp-preview-slide{height:28px;border-radius:8px;border:1px solid #E7D8F0;background:#F3EEF7}
.fb-comp-preview-slide.is-tall{height:38px;background:linear-gradient(135deg,#F8F4FC,#E8DDF3)}
.fb-comp-preview-form{display:grid;gap:5px}
.fb-comp-preview-input{height:8px;border-radius:999px;background:#EFE7F7;border:1px solid #E7D8F0}
.fb-comp-preview-card{display:grid;gap:6px;padding:8px;border-radius:12px;background:#fff;border:1px solid #E6E1EF;box-shadow:0 6px 14px rgba(36,14,53,.06)}
.fb-comp-preview-card--pricing{gap:5px}
.fb-comp-preview-price-row{display:flex;align-items:flex-end;gap:4px}
.fb-comp-preview-price{font-size:18px;line-height:1;font-weight:900;color:#16A34A}
.fb-comp-preview-period{font-size:9px;line-height:1.2;color:#64748B;font-weight:700}
.fb-comp-preview-avatar-row{display:flex;align-items:center;gap:8px}
.fb-comp-preview-avatar{width:20px;height:20px;border-radius:999px;background:#E7D8F0;border:1px solid #D8C8E9}
.fb-comp-preview-faq-item{display:flex;align-items:center;gap:7px}
.fb-comp-preview-faq-badge{width:16px;height:16px;border-radius:999px;background:#F3EEF7;border:1px solid #E7D8F0;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:#240E35}
.fb-comp-preview-timer{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:6px}
.fb-comp-preview-timer-box{height:34px;border-radius:8px;background:#F3EEF7;border:1px solid #E7D8F0;display:grid;place-items:center}
.fb-comp-preview-timer-num{font-size:11px;font-weight:900;color:#240E35;line-height:1}
.fb-comp-preview-timer-unit{font-size:8px;font-weight:800;color:#64748B;line-height:1;text-transform:uppercase}
.fb-lib-group{margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid #E6E1EF}
.fb-lib-group:last-child{margin-bottom:0;padding-bottom:0;border-bottom:0}
.fb-lib-group-title{font-size:12px;font-weight:900;letter-spacing:.02em;text-transform:uppercase;color:#2E1244;margin:0 0 8px}
.fb-template-grid{display:grid;grid-template-columns:1fr;gap:12px}
.fb-template-card{border:1px solid #E6E1EF;border-radius:16px;background:#fff;padding:14px;display:flex;flex-direction:column;gap:12px;box-shadow:0 10px 20px rgba(36,14,53,.06);transition:transform .16s ease,box-shadow .2s ease,border-color .2s ease}
.fb-template-card:hover{transform:translateY(-1px);border-color:#D8C8EA;box-shadow:0 16px 28px rgba(36,14,53,.10)}
.fb-template-status{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 8px}
.fb-template-pill{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;background:#F3EEF7;color:#240E35;font-size:11px;font-weight:800;white-space:nowrap;border:1px solid #E6E1EF}
.fb-template-mode{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:0 0 12px}
.fb-template-mode-btn{min-height:38px;border:1px solid #E6E1EF;border-radius:12px;background:#ffffff;color:#6B4A7A;font-size:12px;font-weight:800;letter-spacing:.01em;cursor:pointer;transition:background .18s ease,color .18s ease,border-color .18s ease,box-shadow .18s ease}
.fb-template-mode-btn.active{background:#240E35;color:#ffffff;border-color:#240E35;box-shadow:0 10px 20px rgba(36,14,53,.18)}
.fb-template-mode-btn:not(.active):hover{background:#F8F5FB;border-color:#D8C8EA}
.fb-template-current{margin:0 0 12px;font-size:12px;color:#64748b;line-height:1.45}
.fb-template-pane.hidden{display:none}
.fb-template-preview{height:92px;border-radius:12px;border:1px dashed #E6E1EF;background:linear-gradient(135deg,#F8F5FB,#F3EEF7);position:relative;overflow:hidden}
.fb-template-title{font-size:15px;font-weight:800;color:#240E35;margin:0;line-height:1.25}
.fb-template-desc{font-size:12px;color:#64748b;line-height:1.55;margin:0}
.fb-template-actions{display:flex;flex-direction:column;align-items:stretch;gap:10px}
.fb-template-tags{display:flex;flex-wrap:wrap;gap:6px}
.fb-template-tag{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:800;color:#6B4A7A;background:#F3EEF7;border:1px solid #E6E1EF;border-radius:999px;padding:2px 6px;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
.fb-template-card-meta{display:grid;gap:6px}
.fb-template-card-controls{display:flex;gap:8px;align-items:stretch;justify-content:space-between;flex-wrap:wrap}
.fb-template-card-controls .fb-btn{min-height:42px}
.fb-template-card-controls .fb-btn.primary{flex:1 1 160px;justify-content:center;text-align:center;line-height:1.2;padding:10px 14px}
.fb-template-card-controls .fb-btn:not(.primary){flex:0 0 auto}
@media (max-width:640px){
    .fb-template-card{padding:12px}
    .fb-template-card-controls{flex-direction:column;align-items:stretch}
    .fb-template-card-controls .fb-btn,
    .fb-template-card-controls .fb-btn.primary{width:100%;flex:1 1 auto}
}
.fb-template-preview .tp-line{height:6px;background:#E6E1EF;border-radius:999px;margin:6px 8px}
.fb-template-preview .tp-line-lg{height:10px;width:70%;background:#D6C6E2}
.fb-template-preview .tp-line-md{width:88%}
.fb-template-preview .tp-line-sm{width:56%}
.fb-template-preview .tp-btn{height:10px;width:38%;background:#240E35;border-radius:999px;margin:8px 8px}
.fb-template-preview .tp-img{position:absolute;right:8px;top:10px;width:40%;height:72%;border-radius:10px;background:linear-gradient(135deg,#E7D8F0,#F8F5FB);border:1px solid #E6E1EF}
.fb-template-preview .tp-form{margin:6px 8px;border-radius:8px;background:#ffffff;border:1px solid #E6E1EF;height:48px}
.fb-template-preview .tp-card{margin:8px;border-radius:10px;background:#ffffff;border:1px solid #E6E1EF;height:62px;box-shadow:0 6px 16px rgba(36,14,53,.08)}
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
    position:relative;
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
.col{flex:1 1 0;min-height:60px;min-width:0;border:1px dashed #E6E1EF !important;border-radius:0;padding:6px;background:#ffffff;position:relative;overflow:visible;text-align:center}
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
#canvas.canvas-outline-mode .sec,#canvas.canvas-outline-mode .row,#canvas.canvas-outline-mode .col,#canvas.canvas-outline-mode .el{position:relative;border:1px dashed #E7D8F0 !important;border-radius:0;box-shadow:none !important}
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
.fb-link-row{display:flex;gap:8px;align-items:center;margin-bottom:8px}
.fb-link-row select{flex:1;min-width:0;margin-bottom:0}
.fb-link-list{display:flex;flex-direction:column;gap:6px;margin-bottom:8px}
.fb-link-actions{display:flex;gap:8px;margin-bottom:8px}
.fb-link-banner{position:sticky;top:8px;z-index:120;display:flex;align-items:center;justify-content:space-between;gap:10px;background:#240E35;color:#fff;border-radius:999px;padding:8px 12px;margin:6px auto 10px;max-width:360px;font-size:12px;font-weight:700;box-shadow:0 10px 24px rgba(36,14,53,.25)}
.fb-link-banner button{background:#fff;color:#240E35;border:0;border-radius:999px;padding:4px 10px;font-size:11px;font-weight:800;cursor:pointer}
.fb-link-layer{position:absolute;left:0;top:0;pointer-events:none;z-index:70;overflow:visible}
#canvas.fb-link-pick .el[data-el-type="pricing"]{outline:2px dashed #9E7BB5;outline-offset:2px}
.el.el--link-target:not(.sel){outline:2px solid #240E35;outline-offset:1px;box-shadow:0 0 0 2px rgba(36,14,53,.15)}
.el.el--link-source:not(.sel){outline:2px dashed #240E35;outline-offset:1px}
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
.fb-testimonial{display:grid;gap:10px}
.fb-testimonial-quote{font-style:italic;color:#334155;line-height:1.5}
.fb-testimonial-author{display:flex;align-items:center;gap:10px}
.fb-testimonial-avatar{width:40px;height:40px;border-radius:999px;object-fit:cover;background:#E6E1EF;flex-shrink:0}
.fb-testimonial-name{font-weight:800;color:#240E35}
.fb-testimonial-role{font-size:12px;color:#64748b}
.fb-review-form{display:grid;gap:10px}
.fb-review-form-title{font-size:18px;font-weight:900;color:#240E35}
.fb-review-form-subtitle{font-size:12px;color:#64748b;line-height:1.5}
.fb-review-stars{display:flex;gap:6px;color:#f59e0b;font-size:18px}
.fb-review-input,.fb-review-textarea{width:100%;padding:10px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff}
.fb-review-textarea{min-height:84px;resize:vertical}
.fb-review-check{display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#475569}
.fb-review-form{width:100%;min-width:0}
.fb-review-list{display:grid;gap:12px;width:100%;min-width:0}
.fb-review-list.grid{grid-template-columns:repeat(auto-fit,minmax(min(280px,100%),1fr))}
.fb-review-card{border:1px solid #E6E1EF;border-radius:14px;padding:14px;width:100%;min-width:0;box-sizing:border-box;background:#fff;display:grid;gap:8px}
.fb-review-card-head{display:flex;justify-content:space-between;gap:8px;align-items:flex-start}
.fb-review-card-name{font-weight:800;color:#240E35}
.fb-review-card-date{font-size:11px;color:#64748b}
.fb-review-card-text{font-size:13px;color:#334155;line-height:1.55}
.fb-review-card-stars{color:#f59e0b;font-size:14px;letter-spacing:.05em}
.fb-faq{display:grid;gap:10px}
.fb-faq-item{border-bottom:1px solid #E6E1EF;padding-bottom:8px}
.fb-faq-item:last-child{border-bottom:0;padding-bottom:0}
.fb-faq-q{font-weight:800;color:#240E35}
.fb-faq-a{color:#475569;font-size:13px;margin-top:4px;white-space:pre-wrap}
.fb-pricing{display:grid;gap:10px}
.fb-product-offer{display:grid;gap:4px}
.fb-product-offer .fb-pricing-badge{padding:2px 6px;font-size:9px}
.fb-product-offer .fb-pricing-title{font-size:13px;line-height:1.25}
.fb-product-offer .fb-pricing-price{font-size:20px;line-height:1}
.fb-product-offer .fb-pricing-period{font-size:10px}
.fb-product-offer .fb-pricing-subtitle{font-size:10px;line-height:1.3}
.fb-product-offer .fb-pricing-features{gap:4px}
.fb-product-offer .fb-pricing-features li{font-size:10px;gap:4px}
.fb-product-offer .fb-product-actions{display:grid;gap:6px}
.fb-product-offer .fb-product-utility{display:grid;grid-template-columns:minmax(0,1fr) 32px;gap:6px}
.fb-product-offer .fb-pricing-cta{width:100%;padding:7px 8px;font-size:11px}
.fb-product-offer .fb-product-secondary{width:100%;padding:6px 8px;font-size:10px;border-radius:999px;border:1px solid #d7cdea;background:#fff;color:#240E35;font-weight:700;text-align:center}
.fb-product-offer .fb-product-cart{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:999px;border:1px solid #d7cdea;background:#fff;color:#240E35;font-size:12px;font-weight:700}
.fb-product-media{position:relative;border:1px solid #E6E1EF;border-radius:12px;overflow:hidden;background:linear-gradient(180deg,#ffffff,#F8FAFC);min-height:88px}
.fb-product-media-stage{position:relative;display:flex;align-items:center;justify-content:center;aspect-ratio:1/1;min-height:88px;background:#fff}
.fb-product-media-stage img,.fb-product-media-stage video{width:100%;height:100%;display:block;object-fit:cover;background:#fff}
.fb-product-media-placeholder{width:100%;height:100%;min-height:88px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;color:#64748b;text-align:center;padding:8px;font-size:10px;cursor:pointer}
.fb-product-media-placeholder i{font-size:16px}
.fb-product-media-nav{position:absolute;top:50%;transform:translateY(-50%);width:36px;height:36px;border:1px solid rgba(255,255,255,.72);border-radius:999px;background:rgba(15,23,42,.6);color:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;z-index:2}
.fb-product-media-nav.is-left{left:10px}
.fb-product-media-nav.is-right{right:10px}
.fb-product-media-nav[disabled]{opacity:.45;cursor:default}
.fb-product-media:hover .fb-product-media-nav,.fb-product-media:focus-within .fb-product-media-nav{opacity:1;pointer-events:auto;transform:translateY(-50%) scale(1)}
@media (hover:hover) and (pointer:fine){
  .fb-product-media .fb-product-media-nav{opacity:0;pointer-events:none;transform:translateY(-50%) scale(.92);transition:opacity .18s ease,transform .18s ease}
}
.fb-product-media-dots{display:flex;align-items:center;justify-content:center;gap:6px}
.fb-product-media-dot{width:8px;height:8px;border-radius:999px;background:#CBD5E1}
.fb-product-media-dot.is-active{width:22px;background:#240E35}
.fb-pricing-badge{align-self:flex-start;background:#E7D8F0;color:#2E1244;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
.fb-pricing-title{font-size:18px;font-weight:900;color:#240E35}
.fb-pricing-price{font-size:30px;font-weight:900;color:#16a34a}
.fb-pricing-period{font-size:12px;color:#64748b;margin-left:4px}
.fb-pricing-subtitle{font-size:12px;color:#64748b}
.fb-pricing-features{list-style:none;padding:0;margin:0;display:grid;gap:6px}
.fb-pricing-features li{display:flex;align-items:flex-start;gap:6px;font-size:12px;color:#334155}
.fb-pricing-cta{display:inline-flex;align-items:center;justify-content:center;padding:8px 12px;border-radius:8px;font-weight:700;text-decoration:none;border:0;cursor:pointer}
.fb-physical-checkout{display:grid;gap:12px;padding:16px}
.fb-physical-checkout .fb-pricing-badge{background:#eaf2ff;color:#1d4ed8}
.fb-physical-checkout-head{display:grid;gap:4px}
.fb-physical-checkout-label{font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.fb-physical-checkout-product{display:grid;grid-template-columns:64px minmax(0,1fr);gap:12px;align-items:center;padding:12px;border:1px solid #e6eaf2;border-radius:16px;background:linear-gradient(180deg,#ffffff,#faf8fd)}
.fb-physical-checkout-thumb{width:64px;height:64px;border-radius:16px;border:1px solid #dbe3f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden}
.fb-physical-checkout-thumb i{font-size:22px;color:#94a3b8}
.fb-physical-checkout-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.fb-physical-checkout-meta{display:grid;gap:4px;min-width:0}
.fb-physical-checkout-meta .fb-pricing-title{font-size:16px;line-height:1.2}
.fb-physical-checkout-meta .fb-pricing-subtitle{font-size:11px;line-height:1.35}
.fb-physical-checkout-price{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.fb-physical-checkout-price .fb-pricing-price{font-size:24px}
.fb-physical-checkout-price .fb-pricing-period{margin-left:0}
.fb-physical-checkout-rows{display:grid;gap:8px;padding:10px 0;border-top:1px solid #eef2f7;border-bottom:1px solid #eef2f7}
.fb-physical-checkout-row{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12px;color:#475569}
.fb-physical-checkout-row strong{color:#0f172a;font-size:13px}
.fb-physical-checkout-row--total strong:last-child{font-size:18px;color:#16a34a}
.fb-physical-checkout-lines{display:grid;gap:8px}
.fb-physical-checkout-line{display:grid;grid-template-columns:40px 1fr auto;gap:10px;align-items:center;padding:8px 0;border-bottom:1px solid #eef2f7}
.fb-physical-checkout-line:last-child{border-bottom:0;padding-bottom:0}
.fb-physical-checkout-line-thumb{width:40px;height:40px;border-radius:12px;border:1px solid #dbe3f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;overflow:hidden}
.fb-physical-checkout-line-thumb i{font-size:14px;color:#94a3b8}
.fb-physical-checkout-line-thumb img{width:100%;height:100%;object-fit:cover;display:block}
.fb-physical-checkout-line-meta{min-width:0;display:grid;gap:2px}
.fb-physical-checkout-line-title{font-size:12px;font-weight:800;color:#0f172a;line-height:1.25}
.fb-physical-checkout-line-sub{font-size:11px;color:#64748b;line-height:1.25}
.fb-physical-checkout-line-total{font-size:12px;font-weight:800;color:#0f172a}
.fb-physical-checkout .fb-pricing-features{gap:5px}
.fb-physical-checkout .fb-pricing-features li{font-size:11px}
.fb-physical-checkout .fb-pricing-cta{width:100%;padding:10px 14px;border-radius:12px}
.fb-countdown{display:grid;gap:8px}
.fb-countdown-label{font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;letter-spacing:.08em}
.fb-countdown-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
.fb-countdown-box{background:#F3EEF7;border-radius:10px;padding:8px;text-align:center}
.fb-countdown-num{font-size:20px;font-weight:900;color:#240E35}
.fb-countdown-unit{font-size:10px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.08em}
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
.fb-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1500;padding:18px}
.fb-modal.open{display:flex}
.fb-modal-card{width:min(520px,92vw);background:#fff;border-radius:16px;border:1px solid #E6E1EF;box-shadow:0 24px 60px rgba(15,23,42,.2);padding:18px}
.fb-modal-title{font-size:16px;font-weight:900;color:#240E35;margin:0 0 8px}
.fb-modal-desc{font-size:13px;color:#475569;line-height:1.5;margin:0 0 16px}
.fb-modal-actions{display:flex;justify-content:flex-end;gap:8px}
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
.page-mgr-item-copy{display:grid;gap:2px;min-width:0}
.page-mgr-item-title{font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.page-mgr-item-meta{font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:#64748b}
.page-mgr-item-handle{font-size:12px;color:#64748b}
.page-mgr-item.is-selected .page-mgr-item-handle{color:rgba(255,255,255,.85)}
.page-mgr-item.is-selected .page-mgr-item-meta{color:rgba(255,255,255,.78)}
.page-mgr-section{margin-bottom:14px;padding:12px;border:1px solid #E6E1EF;border-radius:12px;background:#F3EEF7}
.version-modal-card{width:min(460px,96vw)}
.version-modal-body{padding:18px 20px;background:#F3EEF7;display:flex;flex-direction:column;gap:12px}
.version-modal-body label{font-size:12px;font-weight:900;color:#2E1244;margin:0}
.version-modal-body input{width:100%;padding:10px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff;font-size:14px}
.version-modal-note{font-size:12px;color:#64748b;line-height:1.5}
.version-modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:4px}
.version-modal-actions .fb-btn{min-height:40px}
.page-mgr-section:last-child{margin-bottom:0}
.page-mgr-section label{display:block;margin:0 0 6px;font-size:13px;font-weight:800;color:#334155}
.page-mgr-section input,.page-mgr-section select{width:100%;padding:11px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff;font-size:14px;color:#240E35;margin-bottom:10px}
.page-mgr-section input:focus,.page-mgr-section select:focus{outline:none;border-color:#6B4A7A;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.page-mgr-create-btn{width:100%;margin-top:2px}
.page-mgr-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.page-mgr-actions .fb-btn{min-height:40px}
.page-mgr-note{font-size:12px;color:#475569;font-weight:700;margin-top:8px}
.asset-library-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1460;padding:18px}
.asset-library-modal.open{display:flex}
.asset-library-card{width:min(900px,96vw);max-height:90vh;overflow:hidden;display:flex;flex-direction:column;background:#fff;border:1px solid #E7D8F0;border-radius:18px;box-shadow:0 24px 56px rgba(15,23,42,.34)}
.asset-library-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px 8px}
.asset-library-head h4{margin:0;font-size:20px;font-weight:900;color:#240E35}
.asset-library-close{border:1px solid #E6E1EF;background:#F3EEF7;color:#240E35;width:32px;height:32px;border-radius:10px;cursor:pointer}
.asset-library-sub{padding:0 18px 12px;color:#64748b;font-size:13px;line-height:1.45;border-bottom:1px solid #EEE7F5}
.asset-library-body{padding:16px 18px 18px;display:grid;grid-template-rows:auto 1fr;gap:14px;min-height:0}
.asset-library-toolbar{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.asset-library-upload{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.asset-library-upload input{display:none}
.asset-library-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.asset-library-status{font-size:12px;color:#64748b}
.asset-library-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px;overflow:auto;padding:2px 4px 4px 0;min-height:180px;align-content:start}
.asset-library-grid::-webkit-scrollbar{width:8px}
.asset-library-grid::-webkit-scrollbar-track{background:#F3EEF7;border-radius:4px}
.asset-library-grid::-webkit-scrollbar-thumb{background:#E6E1EF;border-radius:4px}
.asset-library-grid::-webkit-scrollbar-thumb:hover{background:#94a3b8}
.asset-library-item{border:1px solid #E6E1EF;border-radius:16px;background:#fff;overflow:hidden;display:flex;flex-direction:column;min-width:0;min-height:238px;padding:0;cursor:pointer;text-align:left;transition:border-color .15s ease,transform .15s ease,box-shadow .15s ease;box-shadow:0 8px 20px rgba(36,14,53,.06)}
.asset-library-item:hover{border-color:#6B4A7A;transform:translateY(-1px);box-shadow:0 14px 28px rgba(36,14,53,.12)}
.asset-library-item:focus-visible{outline:3px solid rgba(107,74,122,.18);outline-offset:2px}
.asset-library-item.is-selection-mode{cursor:default}
.asset-library-item.is-selected{border-color:#6B4A7A;box-shadow:0 0 0 3px rgba(107,74,122,.16),0 14px 28px rgba(36,14,53,.12)}
.asset-library-preview{position:relative;height:148px;min-height:148px;background:#F8F5FB;display:flex;align-items:center;justify-content:center;overflow:hidden;border-bottom:1px solid #F1EBF6}
.asset-library-preview img,.asset-library-preview video{width:100%;height:100%;object-fit:cover;display:block}
.asset-library-preview.is-empty{background:linear-gradient(180deg,#F8F5FB,#F3EEF7)}
.asset-library-preview-icon{font-size:28px;color:#6B4A7A}
.asset-library-check{position:absolute;top:10px;right:10px;width:28px;height:28px;border-radius:999px;border:1px solid #D8CCE5;background:rgba(255,255,255,.96);color:#6B4A7A;display:inline-flex;align-items:center;justify-content:center;font-size:12px;box-shadow:0 6px 14px rgba(36,14,53,.14);z-index:2}
.asset-library-check.is-selected{background:#240E35;border-color:#240E35;color:#fff}
.asset-library-info{padding:12px;display:flex;flex-direction:column;gap:5px;min-width:0;flex:1}
.asset-library-name{font-size:13px;font-weight:800;color:#240E35;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.asset-library-meta{font-size:12px;color:#64748b}
.asset-library-action{display:inline-flex;align-items:center;gap:6px;margin-top:auto;padding-top:8px;font-size:12px;font-weight:800;color:#2E1244}
.asset-library-empty{display:flex;align-items:center;justify-content:center;border:1px dashed #D8CCE5;border-radius:14px;padding:18px;color:#64748b;font-size:13px;text-align:center;background:#FCFBFD;min-height:180px}
.asset-library-launch{width:100%;margin-top:8px}
@media (max-width: 860px){
    .page-mgr-body{grid-template-columns:1fr}
    .page-mgr-head h4{font-size:20px}
    .asset-library-grid{grid-template-columns:1fr 1fr}
    .asset-library-item{min-height:214px}
    .asset-library-preview{height:128px;min-height:128px}
}
.setting-label-help{display:flex;align-items:center;gap:8px;margin:0 0 6px}
.setting-help-icon{width:22px;height:22px;border-radius:999px;border:1px solid #E7D8F0;background:#eaf2ff;color:#240E35;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;line-height:1;cursor:pointer;padding:0}
.setting-help-icon:hover{background:#E7D8F0}
.menu-section-title.fb-help-row{display:flex;align-items:center;gap:8px}
.setting-label-help .menu-section-title{margin:0}
.fb-help-modal{position:fixed;inset:0;background:rgba(37,99,235,.16);display:none;align-items:center;justify-content:center;z-index:1200;padding:16px}
.fb-help-modal.open{display:flex}
.fb-help-card{width:min(520px,92vw);background:#ffffff;color:#334155;border:1px solid #D9D2F3;border-radius:14px;box-shadow:0 18px 44px rgba(64,64,120,.18);padding:16px;position:relative}
.fb-help-close{position:absolute;top:10px;right:10px;border:1px solid #D9D2F3;background:#F3EEF7;color:#2E1244;width:28px;height:28px;border-radius:999px;cursor:pointer}
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
.fb-drop-preview{position:absolute;pointer-events:none;z-index:65;border:2px dashed #6B4A7A;background:rgba(107,74,122,.08);box-shadow:0 10px 24px rgba(36,14,53,.12);border-radius:10px;display:none}
.fb-drop-insert{position:absolute;left:6px;right:6px;height:3px;background:#6B4A7A;box-shadow:0 0 0 1px rgba(168,85,247,.25);z-index:65;pointer-events:none;border-radius:3px;display:none}
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
        <form method="POST" action="{{ $builderUpdateUrl ?? route('funnels.update', $funnel) }}" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $funnel->name }}">
            <select
                id="fbPurposeTopBar"
                style="min-width:190px;padding:6px 8px;border:1px solid #E6E1EF;border-radius:8px;font-size:12px;background:#fff;font-weight:700;"
                title="Funnel Purpose"
            >
                @php $resolvedPurpose = $builderPurpose ?? $funnel->purpose ?? 'service'; @endphp
                <option value="service" {{ $resolvedPurpose === 'service' ? 'selected' : '' }}>Service</option>
                <option value="physical_product" {{ $resolvedPurpose === 'physical_product' ? 'selected' : '' }}>Physical Product</option>
            </select>
            @if(($builderMode ?? 'funnel') === 'template')
                <input
                    type="text"
                    id="builderTemplateDescription"
                    name="description"
                    value="{{ old('description', $funnel->description) }}"
                    placeholder="Template description"
                    style="min-width:320px;padding:6px 8px;border:1px solid #E6E1EF;border-radius:8px;font-size:12px;"
                >
            @else
                <input type="hidden" name="description" value="{{ $funnel->description }}">
            @endif
            <input type="hidden" name="status" value="{{ $funnel->status }}">
            <input
                type="text"
                name="default_tags"
                value="{{ old('default_tags', $builderTagValue ?? implode(', ', $funnel->default_tags ?? [])) }}"
                placeholder="{{ $builderTagPlaceholder ?? 'Funnel tags: e.g. webinar, q1-campaign' }}"
                @if($builderTagInputDisabled ?? false) disabled @endif
                style="min-width:280px;padding:6px 8px;border:1px solid #E6E1EF;border-radius:8px;font-size:12px;"
            >
            @if(!(($builderMode ?? 'funnel') === 'template' && $funnel->status !== 'published'))
                <button class="fb-btn" type="submit"><i class="fas fa-tags"></i> {{ ($builderMode ?? 'funnel') === 'template' ? 'Save Template' : 'Save Tags' }}</button>
            @endif
        </form>
        <button id="saveBtn" class="fb-btn primary" type="button"><i class="fas fa-save"></i> Save</button>
        <button id="previewBtn" class="fb-btn" type="button"><i class="fas fa-eye"></i> Preview</button>
        @if(($builderMode ?? 'funnel') !== 'template')
            <a href="{{ route('funnels.reviews.index', $funnel) }}" class="fb-btn"><i class="fas fa-star-half-alt"></i> Reviews</a>
        @endif
        @if(($builderMode ?? 'funnel') === 'template')
            <button id="testFlowBtn" class="fb-btn" type="button"><i class="fas fa-vial"></i> Test Flow</button>
        @endif
        @if($funnel->status === 'published')
            <form method="POST" action="{{ $builderUnpublishUrl ?? route('funnels.unpublish', $funnel) }}" id="builderUnpublishForm">@csrf<button class="fb-btn danger" type="submit"><i class="fas fa-ban"></i> Unpublish</button></form>
        @else
            <form method="POST" action="{{ $builderPublishUrl ?? route('funnels.publish', $funnel) }}" id="builderPublishForm">@csrf
                @if(($builderMode ?? 'funnel') === 'template')
                    <input type="hidden" name="description" id="builderPublishDescription" value="{{ old('description', $funnel->description) }}">
                @endif
                <button class="fb-btn success" type="submit" id="builderPublishBtn"><i class="fas fa-upload"></i> {{ ($builderMode ?? 'funnel') === 'template' ? 'Save as Template' : 'Publish' }}</button>
            </form>
        @endif
        <a href="{{ $builderExitUrl ?? route('funnels.index') }}" class="fb-btn"><i class="fas fa-door-open"></i> Exit Builder</a>
    </div>
</div>

<div class="fb-grid" id="fbGrid">
    <div class="fb-components-col" id="fbComponentsCol">
        <div class="fb-left-tabs">
            <button type="button" class="fb-tab active" id="fbTabComponents" title="Components"><i class="fas fa-th-large"></i></button>
            <button type="button" class="fb-tab" id="fbTabSettings" title="Settings"><i class="fas fa-cog"></i></button>
            <button type="button" class="fb-tab" id="fbTabTemplates" title="Templates"><i class="fas fa-layer-group"></i></button>
            <button type="button" class="fb-tab" id="fbTabHistory" title="History"><i class="fas fa-history"></i></button>
        </div>
        <div class="fb-left-panel" id="fbLeftPanelComponents">
            <div class="fb-card fb-lib">
                <h3 class="fb-h">Components</h3>
                <p class="meta" id="fbPurposeMeta" style="margin:0 0 10px;font-size:11px;"></p>
                <div class="fb-lib-group" data-component-group>
                    <div class="fb-lib-group-title">Layout & Structure</div>
                    <button draggable="true" data-c="section" data-purpose="all"><i class="fas fa-square"></i>Section</button>
                    <button draggable="true" data-c="column" data-purpose="all"><i class="fas fa-columns"></i>Column</button>
                    <button draggable="true" data-c="spacer" data-purpose="all"><i class="fas fa-arrows-up-down"></i>Spacer</button>
                </div>
                <div class="fb-lib-group" data-component-group>
                    <div class="fb-lib-group-title">Basic Content</div>
                    <button draggable="true" data-c="heading" data-purpose="all"><i class="fas fa-heading"></i>Heading</button>
                    <button draggable="true" data-c="text" data-purpose="all"><i class="fas fa-font"></i>Text</button>
                    <button draggable="true" data-c="button" data-purpose="all"><i class="fas fa-square-plus"></i>Button</button>
                    <button draggable="true" data-c="icon" data-purpose="all"><i class="fas fa-icons"></i>Icon</button>
                </div>
                <div class="fb-lib-group" data-component-group>
                    <div class="fb-lib-group-title">Media & Visuals</div>
                    <button draggable="true" data-c="image" data-purpose="all"><i class="fas fa-image"></i>Image</button>
                    <button draggable="true" data-c="video" data-purpose="all"><i class="fas fa-video"></i>Video</button>
                    <button draggable="true" data-c="carousel" data-purpose="all"><i class="fas fa-images"></i>Carousel</button>
                </div>
                <div class="fb-lib-group" data-component-group>
                    <div class="fb-lib-group-title">Interaction & Navigation</div>
                    <button draggable="true" data-c="menu" data-purpose="all"><i class="fas fa-bars"></i>Menu</button>
                    <button draggable="true" data-c="form" data-purpose="all"><i class="fas fa-file-lines"></i>Form</button>
                </div>
                <div class="fb-lib-group" data-component-group>
                    <div class="fb-lib-group-title">Advanced Blocks</div>
                    <button draggable="true" data-c="testimonial" data-purpose="all"><i class="fas fa-quote-right"></i>Testimonial</button>
                    <button draggable="true" data-c="review_form" data-purpose="all"><i class="fas fa-star-half-stroke"></i>Review Form</button>
                    <button draggable="true" data-c="reviews" data-purpose="all"><i class="fas fa-stars"></i>Reviews</button>
                    <button draggable="true" data-c="faq" data-purpose="all"><i class="fas fa-circle-question"></i>FAQ</button>
                    <button draggable="true" data-c="pricing" data-purpose="service,digital_product,hybrid"><i class="fas fa-tags"></i>Pricing</button>
                    <button draggable="true" data-c="product_offer" data-purpose="physical_product,hybrid"><i class="fas fa-box-open"></i>Product Offer</button>
                    <button draggable="true" data-c="checkout_summary" data-purpose="service,digital_product,hybrid"><i class="fas fa-receipt"></i>Checkout Summary</button>
                    <button draggable="true" data-c="physical_checkout_summary" data-purpose="physical_product,hybrid"><i class="fas fa-basket-shopping"></i>Physical Checkout Summary</button>
                    <button draggable="true" data-c="countdown" data-purpose="all"><i class="fas fa-stopwatch"></i>Countdown</button>
                </div>
            </div>
        </div>
        <div class="fb-left-panel hidden" id="fbLeftPanelTemplates">
            <div class="fb-card settings">
                <h3 class="fb-h" id="fbTemplateHeading">Templates</h3>
                <p class="meta" id="fbTemplateMeta" style="margin:0 0 10px;">Apply a saved super-admin template across this funnel.</p>
                <div class="fb-template-status">
                    <span class="fb-template-pill" id="fbTemplateTypePill">Super Admin</span>
                    <span class="fb-template-pill" id="fbTemplateCountPill">0 templates</span>
                </div>
                <p class="fb-template-current" id="fbTemplateCurrentPage">Choose a saved super-admin template to apply it to all pages in this funnel.</p>
                <div id="fbTemplateFunnelPane" class="fb-template-pane">
                    <div id="fbFunnelTemplateGrid" class="fb-template-grid"></div>
                </div>
            </div>
        </div>
        <div class="fb-left-panel hidden" id="fbLeftPanelHistory">
            <div class="fb-card settings">
                <h3 class="fb-h">Version History</h3>
                <p class="meta" style="margin:0 0 10px;">This page saves automatically. Newest version is at the top, and Restore takes you back.</p>
                <div id="fbHistoryContainer"></div>
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
            @if(!($builderSingleScrollMode ?? false))
                <label for="stepSel" style="font-weight:800;">Step</label>
                <select id="stepSel"></select>
                <button id="stepAddBtn" type="button" class="fb-btn" style="padding:6px 10px;min-height:32px;">+ Add Page</button>
            @else
                <span style="font-weight:800;color:#1f2937;">Single Screen Mode</span>
            @endif
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
                    <div class="page-mgr-note">Repeated page types are suggested as numbered groups like Sales 1, Sales 2, and Sales 3 so they are easier to find later.</div>
                    <label for="pageMgrAddType">Type</label>
                    <select id="pageMgrAddType">
                        <option value="landing">Landing</option>
                        <option value="opt_in">Opt-in</option>
                        <option value="sales">Sales</option>
                        <option value="checkout">Checkout</option>
                        <option value="upsell">Upsell</option>
                        <option value="downsell">Downsell</option>
                        <option value="thank_you">Thank You</option>
                        <option value="custom">Custom</option>
                    </select>
                    <label for="pageMgrAddTitle">Page title</label>
                    <input id="pageMgrAddTitle" type="text" placeholder="e.g. Sales 2 - Bundle Offer">
                    <label for="pageMgrAddSlug">Page slug (optional)</label>
                    <input id="pageMgrAddSlug" type="text" placeholder="e.g. sales-2-bundle-offer">
                    <button id="pageMgrCreateBtn" type="button" class="fb-btn primary page-mgr-create-btn">Create Page</button>
                </div>
                <div class="page-mgr-section">
                    <h5>Manage Selected Page</h5>
                    <label for="pageMgrRenameTitle">Title</label>
                    <input id="pageMgrRenameTitle" type="text" placeholder="Selected page title">
                    <label for="pageMgrRenameType">Type</label>
                    <select id="pageMgrRenameType">
                        <option value="landing">Landing</option>
                        <option value="opt_in">Opt-in</option>
                        <option value="sales">Sales</option>
                        <option value="checkout">Checkout</option>
                        <option value="upsell">Upsell</option>
                        <option value="downsell">Downsell</option>
                        <option value="thank_you">Thank You</option>
                        <option value="custom">Custom</option>
                    </select>
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
<div class="page-mgr-modal" id="versionModal" aria-hidden="true">
    <div class="page-mgr-card version-modal-card" role="dialog" aria-modal="true" aria-labelledby="versionModalTitle">
        <div class="page-mgr-head">
            <h4 id="versionModalTitle">Save Version</h4>
            <button type="button" class="page-mgr-close" id="versionModalClose" aria-label="Close">X</button>
        </div>
        <div class="version-modal-body">
            <div class="version-modal-note" id="versionModalPageName">Create a restore point for this page.</div>
            <div>
                <label for="versionModalLabel">Version name</label>
                <input id="versionModalLabel" type="text" maxlength="120" placeholder="Optional, e.g. Before checkout redesign">
            </div>
            <div class="version-modal-note">This saves a restore point for the current page only.</div>
            <div class="version-modal-actions">
                <button type="button" class="fb-btn" id="versionModalCancel">Cancel</button>
                <button type="button" class="fb-btn primary" id="versionModalSave">Save Version</button>
            </div>
        </div>
    </div>
</div>
<div class="fb-modal" id="fbTemplateConfirm" aria-hidden="true">
    <div class="fb-modal-card" role="dialog" aria-modal="true" aria-labelledby="fbTemplateConfirmTitle">
        <div class="fb-modal-title" id="fbTemplateConfirmTitle">Apply template</div>
        <p class="fb-modal-desc" id="fbTemplateConfirmDesc">This will replace the current layout.</p>
        <div class="fb-modal-actions">
            <button type="button" class="fb-btn" id="fbTemplateConfirmCancel">Cancel</button>
            <button type="button" class="fb-btn primary" id="fbTemplateConfirmOk">Apply</button>
        </div>
    </div>
</div>
<div class="fb-modal" id="fbSharedTemplateEditModal" aria-hidden="true">
    <div class="fb-modal-card" role="dialog" aria-modal="true" aria-labelledby="fbSharedTemplateEditTitle">
        <div class="fb-modal-title" id="fbSharedTemplateEditTitle">Edit saved template</div>
        <p class="fb-modal-desc">Update the builder card description and the chips shown under it.</p>
        <div style="display:grid;gap:14px;">
            <div>
                <label for="fbSharedTemplateEditDescription" style="display:block;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#5d4476;margin-bottom:6px;">Description</label>
                <textarea id="fbSharedTemplateEditDescription" rows="4" style="width:100%;border:1px solid #d7cce8;border-radius:14px;padding:12px 14px;font:inherit;resize:vertical;background:#fff;color:#240e35;" placeholder="What this template is for"></textarea>
            </div>
            <div>
                <label for="fbSharedTemplateEditTags" style="display:block;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#5d4476;margin-bottom:6px;">Card chips</label>
                <input id="fbSharedTemplateEditTags" type="text" style="width:100%;border:1px solid #d7cce8;border-radius:14px;padding:12px 14px;font:inherit;background:#fff;color:#240e35;" placeholder="5 Pages, Landing, Opt In, Published">
                <div style="margin-top:6px;font-size:12px;color:#7a6890;">Use commas to separate each chip.</div>
            </div>
        </div>
        <div class="fb-modal-actions" style="margin-top:18px;">
            <button type="button" class="fb-btn" id="fbSharedTemplateEditCancel">Cancel</button>
            <button type="button" class="fb-btn primary" id="fbSharedTemplateEditSave">Save Changes</button>
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
            'revision_history' => $step->revisions->sortBy(function ($revision) {
                return [
                    $revision->created_at?->getTimestamp() ?? 0,
                    $revision->id,
                ];
            })->values()->map(function ($revision) {
                return [
                    'id' => $revision->id,
                    'label' => $revision->label ?: null,
                    'version_type' => $revision->version_type ?? 'autosave',
                    'layout_json' => $revision->layout_json,
                    'background_color' => $revision->background_color,
                    'created_at' => $revision->created_at?->toIso8601String(),
                ];
            })->values()->all(),
        ];
    })->all();
    $defaultStep = $funnel->steps->sortBy('position')->first();
    $defaultStepId = $defaultStep?->id;
@endphp
<script>
(function(){
const saveUrl=@json($builderSaveUrl ?? route('funnels.builder.layout.save',$funnel));
const assetLibraryUrl=@json($builderAssetLibraryUrl ?? route('funnels.builder.assets.index',$funnel));
const assetLibraryDeleteUrl=@json($builderAssetDeleteUrl ?? route('funnels.builder.assets.destroy',$funnel));
const uploadUrl=@json($builderUploadUrl ?? route('funnels.builder.image.upload',$funnel));
const previewTpl=@json($builderPreviewUrlTemplate ?? route('funnels.preview',['funnel'=>$funnel,'step'=>'__STEP__']));
const testFlowTpl=@json($builderTestUrlTemplate ?? null);
const stepVersionTpl=@json($builderStepVersionUrlTemplate ?? route('funnels.steps.versions.store',['funnel'=>$funnel,'step'=>'__STEP__']));
const stepStoreUrl=@json($builderStepStoreUrl ?? route('funnels.steps.store',$funnel));
const stepUpdateTpl=@json($builderStepUpdateUrlTemplate ?? route('funnels.steps.update',['funnel'=>$funnel,'step'=>'__STEP__']));
const stepDeleteTpl=@json($builderStepDeleteUrlTemplate ?? route('funnels.steps.destroy',['funnel'=>$funnel,'step'=>'__STEP__']));
const stepReorderUrl=@json($builderStepReorderUrl ?? route('funnels.steps.reorder',$funnel));
const funnelUpdateUrl=@json($builderUpdateUrl ?? route('funnels.update', $funnel));
const csrf="{{ csrf_token() }}";
const funnelSlug=@json($funnel->slug);
const builderPurposeRaw=@json(($builderPurpose ?? $funnel->purpose ?? $funnel->template_type ?? 'service'));
let builderPurpose=String(builderPurposeRaw||"service").toLowerCase();
const funnelStepUrlTpl=@json($builderPublicStepUrlTemplate ?? route('funnels.portal.step',['funnelSlug'=>$funnel->slug,'stepSlug'=>'__STEP__']));
const steps=@json($builderSteps);
const builderSingleScrollMode=@json((bool) ($builderSingleScrollMode ?? false));
const sharedTemplatesUrl=@json($builderSharedTemplatesUrl ?? null);
let sharedFunnelTemplates=@json($builderSharedTemplates ?? []);
const state={sid:{{ (int)($defaultStepId??0) }}||((steps[0]&&steps[0].id)||null),layout:null,sel:null,carouselSel:null,clipboard:null,pasteAnchor:null,editingEl:null,mediaLoading:new Set()};
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

const stepSel=document.getElementById("stepSel"),stepAddBtn=document.getElementById("stepAddBtn"),pageMgrModal=document.getElementById("pageMgrModal"),pageMgrClose=document.getElementById("pageMgrClose"),pageMgrList=document.getElementById("pageMgrList"),pageMgrAddType=document.getElementById("pageMgrAddType"),pageMgrAddTitle=document.getElementById("pageMgrAddTitle"),pageMgrAddSlug=document.getElementById("pageMgrAddSlug"),pageMgrCreateBtn=document.getElementById("pageMgrCreateBtn"),pageMgrRenameTitle=document.getElementById("pageMgrRenameTitle"),pageMgrRenameType=document.getElementById("pageMgrRenameType"),pageMgrRenameSlug=document.getElementById("pageMgrRenameSlug"),pageMgrRenameTags=document.getElementById("pageMgrRenameTags"),pageMgrRenameBtn=document.getElementById("pageMgrRenameBtn"),pageMgrDeleteBtn=document.getElementById("pageMgrDeleteBtn"),pageMgrUpBtn=document.getElementById("pageMgrUpBtn"),pageMgrDownBtn=document.getElementById("pageMgrDownBtn"),versionModal=document.getElementById("versionModal"),versionModalClose=document.getElementById("versionModalClose"),versionModalCancel=document.getElementById("versionModalCancel"),versionModalSave=document.getElementById("versionModalSave"),versionModalLabel=document.getElementById("versionModalLabel"),versionModalPageName=document.getElementById("versionModalPageName"),canvas=document.getElementById("canvas"),settings=document.getElementById("settings"),saveMsg=document.getElementById("saveMsg"),settingsTitle=document.getElementById("settingsTitle"),canvasBgColor=document.getElementById("canvasBgColor"),canvasBgReset=document.getElementById("canvasBgReset");
const fbPurposeMeta=document.getElementById("fbPurposeMeta");
const fbPurposeSelect=document.getElementById("fbPurposeTopBar");
if(fbPurposeSelect){
    fbPurposeSelect.value=builderPurpose;
    fbPurposeSelect.addEventListener("change",function(){
        var next=normalizeBuilderPurpose(fbPurposeSelect.value);
        setBuilderPurpose(next);
        if(funnelUpdateUrl){
            requestJson(funnelUpdateUrl,"PUT",{purpose:next}).then(function(){
                showBuilderToast("Purpose updated to "+fbPurposeSelect.options[fbPurposeSelect.selectedIndex].text+".","success");
            }).catch(function(){
                showBuilderToast("Failed to save purpose.","error");
            });
        }
    });
}
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
        o.textContent=pageDisplayLabel(s);
        stepSel.appendChild(o);
    });
    var hasCurrent=steps.some(function(s){return +s.id===+state.sid;});
    if(!hasCurrent && steps.length)state.sid=steps[0].id;
    stepSel.value=String(state.sid||"");
}
function applyPurposeComponentVisibility(){
    var labels={
        service:"Service Funnel",
        single_page:"Single Page Funnel",
        digital_product:"Digital Product Funnel",
        physical_product:"Physical Product Funnel",
        hybrid:"Hybrid Funnel"
    };
    var descriptions={
        service:"Service components: pricing, checkout, forms, proof, and lead capture.",
        single_page:"Service components: pricing, checkout, forms, proof, and lead capture.",
        digital_product:"Service components: pricing, checkout, forms, proof, and lead capture.",
        physical_product:"Physical product components: product offers, cart, shipping, and checkout.",
        hybrid:"Service components: pricing, checkout, forms, proof, and lead capture."
    };
    if(fbPurposeMeta){
        fbPurposeMeta.textContent=descriptions[builderPurpose]||descriptions.service;
    }
    if(fbPurposeSelect&&fbPurposeSelect.value!==builderPurpose){
        fbPurposeSelect.value=builderPurpose;
    }
    document.querySelectorAll("[data-c][data-purpose]").forEach(function(btn){
        var allowed=String(btn.getAttribute("data-purpose")||"all").split(",").map(function(v){return String(v||"").trim().toLowerCase();}).filter(Boolean);
        var visible=allowed.indexOf("all")>=0||allowed.indexOf(builderPurpose)>=0||(builderPurpose==="single_page"&&allowed.indexOf("service")>=0);
        btn.style.display=visible?"":"none";
    });
    document.querySelectorAll("[data-component-group]").forEach(function(group){
        var visibleButtons=Array.from(group.querySelectorAll("[data-c]")).filter(function(btn){
            return btn.style.display!=="none";
        });
        group.style.display=visibleButtons.length?"":"none";
    });
}
function normalizeBuilderPurpose(value){
    var normalized=String(value||"service").trim().toLowerCase();
    var valid=["service","single_page","digital_product","physical_product","hybrid"];
    return valid.indexOf(normalized)>=0?normalized:"service";
}
function setBuilderPurpose(nextPurpose){
    builderPurpose=normalizeBuilderPurpose(nextPurpose);
    applyPurposeComponentVisibility();
}
function syncBuilderPurposeFromTemplate(template){
    var nextPurpose=normalizeBuilderPurpose((template&&template.funnel_purpose)||(template&&template.template_type)||"service");
    if(!funnelUpdateUrl){
        setBuilderPurpose(nextPurpose);
        return Promise.resolve(nextPurpose);
    }
    if(nextPurpose===builderPurpose){
        setBuilderPurpose(nextPurpose);
        return Promise.resolve(nextPurpose);
    }
    return requestJson(funnelUpdateUrl,"PUT",{purpose:nextPurpose}).then(function(resp){
        var savedPurpose=normalizeBuilderPurpose((resp&&resp.funnel&&resp.funnel.purpose)||nextPurpose);
        setBuilderPurpose(savedPurpose);
        return savedPurpose;
    });
}
renderStepOptions();
applyPurposeComponentVisibility();
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
    if(t==="landing"||t==="opt_in"||t==="sales"||t==="checkout"||t==="upsell"||t==="downsell"||t==="thank_you"){
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
const makeSection=(opts)=>{
    var o=opts&&typeof opts==="object"?opts:{};
    return {
        kind:"section",
        id:uid("sec"),
        style:o.style||{padding:"64px 24px",backgroundColor:"#ffffff"},
        settings:Object.assign({contentWidth:"wide"},o.settings||{}),
        elements:Array.isArray(o.elements)?o.elements:[],
        rows:Array.isArray(o.rows)?o.rows:[]
    };
};
const makeRow=(columns,style,settings)=>({
    id:uid("row"),
    style:style||{gap:"16px"},
    settings:settings||{},
    columns:Array.isArray(columns)?columns:[]
});
const makeColumn=(elements,style,settings)=>({
    id:uid("col"),
    style:style||{},
    settings:settings||{},
    elements:Array.isArray(elements)?elements:[]
});
const makeEl=(type,content,style,settings)=>({
    id:uid("el"),
    type:type,
    content:content||"",
    style:style||{},
    settings:settings||{}
});
function futureCountdownValue(days){
    var d=new Date(Date.now()+((Number(days)||7)*24*60*60*1000));
    var pad=n=>String(n).padStart(2,"0");
    return d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate())+"T"+pad(d.getHours())+":"+pad(d.getMinutes());
}
const makeStretchColumn=(elements,style,settings)=>makeColumn(
    Array.isArray(elements)?elements:[],
    Object.assign({flex:"1"},style||{}),
    Object.assign({stretch:true,stretchJustify:"flex-start",stretchAlign:"stretch"},settings||{})
);
function makeCheckoutSummaryEl(opts,style){
    opts=opts||{};
    return makeEl("checkout_summary","",Object.assign({
        width:"100%",
        padding:"22px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"20px",
        boxShadow:"0 12px 24px rgba(15,23,42,.08)"
    },style||{}),{
        heading:opts.heading||"Complete Your Order",
        plan:opts.plan||"Chosen Plan",
        price:opts.price||"Selected price",
        period:opts.period||"/month",
        subtitle:opts.subtitle||"This summary updates from the pricing selected earlier in the funnel.",
        badge:opts.badge||"Selected Plan",
        features:Array.isArray(opts.features)&&opts.features.length?opts.features:["Unlimited steps","Custom domains","Email support"],
        ctaLabel:opts.ctaLabel||"Pay Now",
        ctaBgColor:opts.ctaBgColor||"#240E35",
        ctaTextColor:opts.ctaTextColor||"#ffffff"
    });
}
function makePhysicalCheckoutSummaryEl(opts,style){
    opts=opts||{};
    return makeEl("physical_checkout_summary","",Object.assign({
        width:"100%",
        padding:"22px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"20px",
        boxShadow:"0 12px 24px rgba(15,23,42,.08)"
    },style||{}),{
        heading:opts.heading||"Cart Summary",
        plan:opts.plan||"3 items",
        price:opts.price||"4,000",
        period:opts.period||"",
        subtitle:opts.subtitle||"Review the products in your cart before paying.",
        badge:opts.badge||"Cart",
        features:Array.isArray(opts.features)&&opts.features.length?opts.features:["Product subtotal updates automatically","Cart items show here before payment","Shipping details are completed before payment"],
        ctaLabel:opts.ctaLabel||"Place Order",
        ctaBgColor:opts.ctaBgColor||"#240E35",
        ctaTextColor:opts.ctaTextColor||"#ffffff"
    });
}
const makeFeatureCardColumn=(iconName,title,body)=>makeStretchColumn([
    makeEl("icon","",{fontSize:"28px",color:"#6B4A7A",margin:"0 0 10px"},{iconName:iconName||"star",iconStyle:"solid",alignment:"left",link:""}),
    makeEl("heading",title||"Feature title",{fontSize:"18px",color:"#240E35",fontWeight:"800",margin:"0 0 6px"},{}),
    makeEl("text",body||"Short feature description goes here.",{fontSize:"14px",color:"#64748b",lineHeight:"1.6",margin:"0",flex:"1"},{})
],{padding:"18px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"16px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",minHeight:"210px"});
const makeStatColumn=(stat,label)=>makeColumn([
    makeEl("heading",stat||"10x",{fontSize:"28px",color:"#240E35",fontWeight:"800",margin:"0 0 4px",textAlign:"center"},{}),
    makeEl("text",label||"Metric",{fontSize:"12px",color:"#64748b",textTransform:"uppercase",letterSpacing:"0.12em",textAlign:"center",margin:"0"},{})
],{textAlign:"center",padding:"10px 8px"});
const makeLogoColumn=(label)=>makeColumn([
    makeEl("text",label||"Logo",{fontSize:"12px",color:"#64748b",fontWeight:"800",textTransform:"uppercase",letterSpacing:"0.18em",textAlign:"center",margin:"0"},{})
],{textAlign:"center",padding:"8px 12px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"999px"});
function templateHeroLayout(){
    var leftCol=makeColumn([
        makeEl("heading","Grow your audience fast",{fontSize:"42px",color:"#240E35",fontWeight:"800",margin:"0 0 12px",lineHeight:"1.15"},{}),
        makeEl("text","Build high-converting pages in minutes with our funnel builder.",{fontSize:"18px",color:"#475569",lineHeight:"1.6",margin:"0 0 18px"},{}),
        makeEl("button","Get Started",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"12px 24px",fontWeight:"700"},{actionType:"next_step",actionStepSlug:"",link:"#",alignment:"left"})
    ],{flex:"1"});
    var rightCol=makeColumn([
        makeEl("image","",{width:"100%"},{src:"",alt:"Product preview",alignment:"center"})
    ],{flex:"1"});
    var heroSection=makeSection({
        style:{padding:"72px 24px",backgroundColor:"#ffffff"},
        rows:[makeRow([leftCol,rightCol],{gap:"20px",alignItems:"center"})]
    });
    return {root:[heroSection],sections:[],__editor:{canvasBg:"#F3EEF7"}};
}
function templateLeadCaptureLayout(){
    var formEl=makeEl("form","",{width:"100%",maxWidth:"520px"},{alignment:"center",width:"520px",buttonAlign:"center",buttonBold:true,fields:[
        {type:"text",label:"First name",placeholder:"First name",required:false},
        {type:"email",label:"Email",placeholder:"Email address",required:true}
    ]});
    var section=makeSection({
        style:{padding:"64px 24px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"medium"},
        rows:[makeRow([makeColumn([
            makeEl("heading","Get the free playbook",{fontSize:"36px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
            makeEl("text","Join 12,000+ founders getting weekly growth tips.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",textAlign:"center",margin:"0 0 20px"},{}),
            formEl
        ],{alignItems:"center",textAlign:"center"})],{gap:"12px"})]
    });
    return {root:[section],sections:[],__editor:{canvasBg:"#F8F5FB"}};
}
function templatePricingFaqLayout(){
    var intro=makeSection({
        style:{padding:"56px 24px 28px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"medium"},
        rows:[makeRow([makeColumn([
            makeEl("heading","Choose a plan that grows with you",{fontSize:"32px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
            makeEl("text","Start today and upgrade anytime.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",textAlign:"center"},{})
        ],{textAlign:"center"})],{gap:"10px"})]
    });
    var pricing=makePricingCardEl({plan:"Pro",price:"49",period:"/month",subtitle:"Best for growing teams",features:["Unlimited pages","Custom domains","Priority support"],badge:"Popular"},makeBareCardStyle());
    var faq=makeFaqCardEl([{q:"Can I cancel anytime?",a:"Yes, cancel anytime from your account settings."},{q:"Do you offer a free trial?",a:"Yes, you get a 14-day free trial."}],makeBareCardStyle());
    var gridSection=makeSection({
        style:{padding:"28px 24px 64px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow([
            makePanelColumn([pricing]),
            makePanelColumn([faq])
        ],{gap:"20px",alignItems:"stretch"})]
    });
    return {root:[intro,gridSection],sections:[],__editor:{canvasBg:"#F3EEF7"}};
}
function templateHeroVideoLayout(){
    var leftCol=makeColumn([
        makeEl("heading","Launch your next webinar",{fontSize:"40px",color:"#240E35",fontWeight:"800",margin:"0 0 12px",lineHeight:"1.15"},{}),
        makeEl("text","Turn visitors into attendees with a clear offer and instant signup.",{fontSize:"18px",color:"#475569",lineHeight:"1.6",margin:"0 0 18px"},{}),
        makeEl("button","Reserve My Seat",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"12px 24px",fontWeight:"700"},{actionType:"next_step",actionStepSlug:"",link:"#",alignment:"left"})
    ],{flex:"1"});
    var rightCol=makeColumn([
        makeEl("video","",{width:"100%"},{src:"",alignment:"center"})
    ],{flex:"1"});
    var heroSection=makeSection({
        style:{padding:"72px 24px",backgroundColor:"#ffffff"},
        rows:[makeRow([leftCol,rightCol],{gap:"24px",alignItems:"center"})]
    });
    var logos=makeSection({
        style:{padding:"22px 24px 40px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow([makeLogoColumn("Nova"),makeLogoColumn("Bright"),makeLogoColumn("Flux"),makeLogoColumn("Pulse")],{gap:"10px",alignItems:"center"})]
    });
    return {root:[heroSection,logos],sections:[],__editor:{canvasBg:"#F8F5FB"}};
}
function templateFeatureGridLayout(){
    var intro=makeSection({
        style:{padding:"64px 24px 28px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"medium"},
        rows:[makeRow([makeColumn([
            makeEl("heading","All-in-one funnel builder",{fontSize:"34px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
            makeEl("text","Launch fast with templates, drag-and-drop blocks, and instant previews.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",textAlign:"center"},{})
        ],{textAlign:"center"})],{gap:"10px"})]
    });
    var features=makeSection({
        style:{padding:"0 24px 64px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow([
            makeFeatureCardColumn("bolt","Fast setup","Get a working page in minutes with prebuilt blocks."),
            makeFeatureCardColumn("star","Beautiful layouts","Modern spacing, typography, and responsive grids."),
            makeFeatureCardColumn("chart-line","Track results","Measure clicks and conversions in real time.")
        ],{gap:"18px"})]
    });
    return {root:[intro,features],sections:[],__editor:{canvasBg:"#F3EEF7"}};
}
function templateWebinarLayout(){
    var countdown=makeEl("countdown","",{width:"100%"},{endAt:futureCountdownValue(5),label:"Webinar starts in",expiredText:"Webinar ended",numberColor:"#240E35",labelColor:"#64748b",itemGap:8});
    var formEl=makeEl("form","",{width:"100%",maxWidth:"520px"},{alignment:"center",width:"520px",buttonAlign:"center",buttonBold:true,fields:[
        {type:"text",label:"Full name",placeholder:"Full name",required:false},
        {type:"email",label:"Email",placeholder:"Email address",required:true}
    ]});
    var section=makeSection({
        style:{padding:"72px 24px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"medium"},
        rows:[makeRow([makeColumn([
            makeEl("heading","Live training: Build a funnel in 30 minutes",{fontSize:"34px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
            makeEl("text","Save your seat and get the replay. We will walk you through each step.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",textAlign:"center",margin:"0 0 16px"},{}),
            countdown,
            formEl
        ],{alignItems:"center",textAlign:"center"})],{gap:"12px"})]
    });
    return {root:[section],sections:[],__editor:{canvasBg:"#F8F5FB"}};
}
function templateCheckoutLayout(){
    var section=makeSplitInfoSection(
        makePanelColumn([makeCheckoutSummaryEl({
            heading:"Complete Your Order",
            plan:"Chosen Plan",
            price:"Selected price",
            period:"/billing cycle",
            subtitle:"This summary updates from the pricing selected earlier in the funnel.",
            features:["Unlimited steps","Custom domains","Email support"],
            badge:"Selected Plan",
            ctaLabel:"Pay Now"
        },makeBareCardStyle())]),
        makePanelColumn([
            makeEl("heading","Complete your order",{fontSize:"24px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text",buildChecklistHtml("Use this area for final reassurance",[
                "Summarize what the buyer gets",
                "Mention delivery or onboarding timing",
                "Call out guarantee or support details"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 18px"},{}),
            makePrimaryButtonEl("Complete Purchase",{actionType:"checkout",alignment:"left"}),
            makeEl("text","Secure checkout ready. Replace this with your own purchase reassurance copy.",{fontSize:"13px",color:"#64748b",margin:"12px 0 0"},{}),
            makeTestimonialCardEl("The streamlined checkout felt much more trustworthy and easier to finish.","Jamie Lee","Founder",makeBareCardStyle({margin:"18px 0 0"}))
        ]),
        {alignItems:"stretch"}
    );
    return buildTemplateLayout("#F3EEF7",[section]);
}
function templateThankYouLayout(){
    var section=makeSection({
        style:{padding:"80px 24px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"medium"},
        rows:[makeRow([makeColumn([
            makeEl("heading","Thank you for signing up!",{fontSize:"36px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
            makeEl("text","Check your inbox for the next steps and your bonus materials.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",textAlign:"center",margin:"0 0 18px"},{}),
            makeEl("button","Back to Home",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"12px 24px",fontWeight:"700"},{actionType:"link",link:"#",alignment:"center"}),
            makeEl("testimonial","",{width:"100%"},{quote:"This builder helped us launch in a weekend.",name:"Jamie Lee",role:"Founder, Northwind",avatar:""})
        ],{alignItems:"center",textAlign:"center"})],{gap:"14px"})]
    });
    return {root:[section],sections:[],__editor:{canvasBg:"#F8F5FB"}};
}
function templateStoryLayout(){
    var story=makeSection({
        style:{padding:"72px 24px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow([makeColumn([
            makeEl("image","",{width:"100%"},{src:"",alt:"Team photo",alignment:"center"})
        ],{flex:"1"}),makeColumn([
            makeEl("heading","Built by teams who care about conversion",{fontSize:"30px",color:"#240E35",fontWeight:"800",margin:"0 0 12px"},{}),
            makeEl("text","We have helped hundreds of creators ship faster and convert better with streamlined funnels.",{fontSize:"16px",color:"#64748b",lineHeight:"1.7",margin:"0 0 16px"},{}),
            makeEl("button","Read the story",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"10px 20px",fontWeight:"700"},{actionType:"link",link:"#",alignment:"left"})
        ],{flex:"1"})],{gap:"24px",alignItems:"center"})]
    });
    var stats=makeSection({
        style:{padding:"20px 24px 64px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow([
            makeStatColumn("240%","Avg lift"),
            makeStatColumn("12k+","Creators"),
            makeStatColumn("4.9/5","Rating")
        ],{gap:"12px"})]
    });
    return {root:[story,stats],sections:[],__editor:{canvasBg:"#F3EEF7"}};
}
function buildTemplateLayout(canvasBg,sections){
    return {root:Array.isArray(sections)?sections:[],sections:[],__editor:{canvasBg:canvasBg||"#F3EEF7"}};
}
function buildChecklistHtml(title,items){
    var rows=[];
    if(title)rows.push("<strong>"+String(title||"")+"</strong>");
    (Array.isArray(items)?items:[]).forEach(function(item){
        if(String(item||"").trim()!=="")rows.push("&bull; "+String(item));
    });
    return rows.join("<br>");
}
function makePrimaryButtonEl(label,settings,style){
    return makeEl("button",label||"Continue",Object.assign({
        backgroundColor:"#240E35",
        color:"#ffffff",
        borderRadius:"999px",
        padding:"12px 24px",
        fontWeight:"700",
        textAlign:"center"
    },style||{}),Object.assign({
        actionType:"next_step",
        actionStepSlug:"",
        link:"#",
        alignment:"left"
    },settings||{}));
}
function makeOptInFormEl(label,fields,opts){
    opts=opts||{};
    return makeEl("form",label||"Get Access",Object.assign({
        width:"100%",
        maxWidth:opts.maxWidth||"520px",
        padding:"22px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"20px",
        boxShadow:"0 12px 24px rgba(36,14,53,.08)"
    },opts.style||{}),{
        alignment:opts.alignment||"center",
        width:opts.formWidth||"100%",
        buttonAlign:opts.buttonAlign||"center",
        buttonBold:true,
        labelColor:"#240E35",
        placeholderColor:"#94a3b8",
        buttonBgColor:"#240E35",
        buttonTextColor:"#ffffff",
        fields:Array.isArray(fields)&&fields.length?fields:[
            {type:"first_name",label:"First name",placeholder:"First name",required:false},
            {type:"email",label:"Email",placeholder:"Email address",required:true}
        ]
    });
}
function makePricingCardEl(opts,style){
    opts=opts||{};
    return makeEl("pricing","",Object.assign({
        width:"100%",
        flex:"1",
        height:"100%",
        display:"flex",
        flexDirection:"column",
        justifyContent:"space-between",
        boxSizing:"border-box",
        padding:"18px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"18px",
        boxShadow:"0 12px 24px rgba(36,14,53,.08)"
    },style||{}),{
        plan:opts.plan||"Growth",
        price:opts.price||"49",
        period:opts.period||"/month",
        subtitle:opts.subtitle||"Best for growing teams",
        features:Array.isArray(opts.features)&&opts.features.length?opts.features:["Unlimited funnels","Priority support","Conversion analytics"],
        ctaLabel:opts.ctaLabel||"",
        ctaActionType:opts.ctaActionType||"next_step",
        ctaActionStepSlug:opts.ctaActionStepSlug||"",
        ctaLink:opts.ctaLink||"#",
        ctaBgColor:opts.ctaBgColor||"#240E35",
        ctaTextColor:opts.ctaTextColor||"#ffffff",
        badge:opts.badge||"",
        textColor:opts.textColor||"#240E35",
        regularPrice:opts.regularPrice||""
    });
}
function makeFaqCardEl(items,style){
    return makeEl("faq","",Object.assign({
        width:"100%",
        flex:"1",
        height:"100%",
        display:"flex",
        flexDirection:"column",
        boxSizing:"border-box",
        padding:"18px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"18px",
        boxShadow:"0 12px 24px rgba(36,14,53,.08)"
    },style||{}),{
        items:Array.isArray(items)?items:[],
        itemGap:10,
        questionColor:"#240E35",
        answerColor:"#475569"
    });
}
function makeTestimonialCardEl(quote,name,role,style){
    return makeEl("testimonial","",Object.assign({
        width:"100%",
        flex:"1",
        height:"100%",
        display:"flex",
        flexDirection:"column",
        justifyContent:"space-between",
        boxSizing:"border-box",
        padding:"18px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"18px",
        boxShadow:"0 12px 24px rgba(36,14,53,.08)"
    },style||{}),{
        quote:quote||"This layout helped us launch faster.",
        name:name||"Alex Morgan",
        role:role||"Founder",
        avatar:""
    });
}
function makeBareCardStyle(style){
    return Object.assign({
        backgroundColor:"transparent",
        border:"0",
        boxShadow:"none",
        padding:"0",
        borderRadius:"0"
    },style||{});
}
function makePanelColumn(elements,style){
    return makeStretchColumn(Array.isArray(elements)?elements:[],Object.assign({
        padding:"22px",
        backgroundColor:"#ffffff",
        border:"1px solid #E6E1EF",
        borderRadius:"20px",
        boxShadow:"0 12px 24px rgba(36,14,53,.08)"
    },style||{}));
}
function makeIntroElements(kicker,heading,body,opts){
    opts=opts||{};
    var align=opts.align||"left";
    var headingSize=opts.headingSize||"38px";
    var bodySize=opts.bodySize||"16px";
    var out=[];
    if(kicker){
        out.push(makeEl("text",String(kicker||"").toUpperCase(),{
            fontSize:"12px",
            color:"#6B4A7A",
            fontWeight:"800",
            letterSpacing:"0.16em",
            textAlign:align,
            margin:"0 0 10px"
        },{}));
    }
    if(heading){
        out.push(makeEl("heading",heading,{
            fontSize:headingSize,
            color:"#240E35",
            fontWeight:"800",
            lineHeight:"1.15",
            textAlign:align,
            margin:"0 0 12px"
        },{}));
    }
    if(body){
        out.push(makeEl("text",body,{
            fontSize:bodySize,
            color:"#64748b",
            lineHeight:"1.7",
            textAlign:align,
            margin:"0 0 18px"
        },{}));
    }
    if(opts.button){
        out.push(makePrimaryButtonEl(
            opts.button.label,
            Object.assign({alignment:opts.button.alignment||align},opts.button.settings||{}),
            opts.button.style||{}
        ));
    }
    (Array.isArray(opts.extraEls)?opts.extraEls:[]).forEach(function(extra){
        if(extra)out.push(extra);
    });
    return out;
}
function makeTrustSection(labels){
    return makeSection({
        style:{padding:"18px 24px 42px",backgroundColor:"#ffffff"},
        settings:{contentWidth:"wide"},
        rows:[makeRow((labels||[]).map(function(label){return makeLogoColumn(label);}),{gap:"10px",alignItems:"center"})]
    });
}
function makeSplitInfoSection(leftCol,rightCol,opts){
    opts=opts||{};
    return makeSection({
        style:{padding:opts.padding||"72px 24px",backgroundColor:opts.backgroundColor||"#ffffff"},
        settings:{contentWidth:opts.contentWidth||"wide"},
        rows:[makeRow([leftCol,rightCol],{gap:opts.gap||"24px",alignItems:opts.alignItems||"center"})]
    });
}
function makeCardGridSection(opts){
    opts=opts||{};
    var sectionElements=[];
    var rows=[];
    if(opts.title||opts.body||opts.kicker){
        sectionElements=sectionElements.concat(makeIntroElements(opts.kicker,opts.title,opts.body,{
            align:opts.align||"center",
            headingSize:opts.headingSize||"32px",
            bodySize:opts.bodySize||"16px"
        }));
    }
    if(Array.isArray(opts.columns)&&opts.columns.length){
        rows.push(makeRow(opts.columns,{gap:opts.gap||"18px",alignItems:opts.alignItems||"stretch"}));
    }
    return makeSection({
        style:{padding:opts.padding||"0 24px 64px",backgroundColor:opts.backgroundColor||"#ffffff"},
        settings:{contentWidth:opts.contentWidth||"wide"},
        elements:sectionElements,
        rows:rows
    });
}
function makeCenteredCtaSection(opts){
    opts=opts||{};
    var style={padding:opts.padding||"32px 24px",backgroundColor:opts.backgroundColor||"#F8F5FB"};
    if(opts.bordered!==false){
        style.border="1px solid #E6E1EF";
        style.borderRadius=opts.radius||"20px";
    }
    return makeSection({
        style:style,
        settings:{contentWidth:opts.contentWidth||"wide"},
        elements:makeIntroElements(opts.kicker,opts.heading,opts.body,{
            align:"center",
            headingSize:opts.headingSize||"28px",
            bodySize:opts.bodySize||"15px",
            button:opts.button?{
                label:opts.button.label,
                alignment:"center",
                settings:opts.button.settings||{},
                style:opts.button.style||{}
            }:null,
            extraEls:opts.extraEls||[]
        }),
        rows:[]
    });
}
function makeNextStepCtaSection(opts){
    opts=opts||{};
    return makeCenteredCtaSection({
        kicker:opts.kicker||"Next Step",
        heading:opts.heading||"Keep the funnel moving",
        body:opts.body||"This starter includes a dedicated button block so users can wire the next page later.",
        button:{
            label:opts.label||"Continue To Next Step",
            settings:Object.assign({
                actionType:"next_step",
                actionStepSlug:"",
                link:"#"
            },opts.buttonSettings||{}),
            style:opts.buttonStyle||{}
        },
        backgroundColor:opts.backgroundColor||"#F8F5FB",
        padding:opts.padding||"30px 24px 38px"
    });
}
function templateAuthorityLandingLayout(){
    var hero=makeSplitInfoSection(
        makeColumn(makeIntroElements(
            "Consulting Funnel",
            "Book more qualified calls with a polished first impression",
            "Start with a premium hero, layer in trust, and guide visitors toward the next step without clutter.",
            {button:{label:"See The Offer"}}
        )),
        makePanelColumn([
            makeEl("heading","Why this layout converts",{fontSize:"24px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text",buildChecklistHtml("Keep the message tight",[
                "Lead with one outcome visitors care about",
                "Add proof before asking for the click",
                "Use each section to remove one objection"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 16px"},{}),
            makeTestimonialCardEl("We used this exact structure to increase booked calls by 34% in two weeks.","Maya Chen","Growth Consultant",makeBareCardStyle())
        ])
    );
    var trust=makeTrustSection(["NORTHWIND","SUMMIT","AURORA","ATLAS","EVERGREEN"]);
    var services=makeCardGridSection({
        title:"What to place below the fold",
        body:"Use these cards to explain the offer, the process, and the proof.",
        columns:[
            makeFeatureCardColumn("comments","Clear positioning","Show exactly who this page is for and what changes after they say yes."),
            makeFeatureCardColumn("shield-halved","Trust builders","Add authority, testimonials, and proof elements before the call to action."),
            makeFeatureCardColumn("paper-plane","Simple next step","End sections with one direct action instead of too many choices.")
        ]
    });
    var cta=makeCenteredCtaSection({
        heading:"Ready to make it yours?",
        body:"Swap in your copy, your proof, and your offer details to ship a professional page quickly.",
        button:{label:"Use This Landing Page"}
    });
    return buildTemplateLayout("#F3EEF7",[hero,trust,services,cta]);
}
function templateAppShowcaseLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Product Showcase",
        heading:"Put the product promise front and center",
        body:"This template gives you a modern product page flow: headline, proof, benefits, and a strong call to action.",
        button:{label:"View Pricing"},
        headingSize:"40px",
        bodySize:"16px",
        padding:"84px 24px 34px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var stats=makeCardGridSection({
        padding:"0 24px 28px",
        backgroundColor:"#ffffff",
        columns:[
            makeStatColumn("24H","Launch time"),
            makeStatColumn("3.4X","Clickthrough"),
            makeStatColumn("4.9/5","User rating")
        ]
    });
    var features=makeCardGridSection({
        title:"Professional structure for a product page",
        body:"Lead with a clear value prop, then support it with product proof and friction-reducing sections.",
        columns:[
            makeFeatureCardColumn("bolt","Fast setup","Use this as a clean first draft for SaaS, apps, or digital tools."),
            makeFeatureCardColumn("image","Visual storytelling","Pair screenshots or mockups with short, persuasive copy blocks."),
            makeFeatureCardColumn("cart-shopping","Stronger Call to Actions","Move visitors naturally from curiosity to offer review.")
        ]
    });
    var social=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[
            makePanelColumn([makeTestimonialCardEl("The layout instantly made our product look more established and easier to trust.","Darren Cole","Product Lead",makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("We finally had a template that felt premium without over-designing everything.","Sofia Tran","Marketing Manager",makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,stats,features,social]);
}
function templateWaitlistLaunchLayout(){
    var formEl=makeOptInFormEl("Join the Waitlist",[
        {type:"first_name",label:"First name",placeholder:"First name",required:false},
        {type:"email",label:"Email",placeholder:"Email address",required:true}
    ],{alignment:"center",buttonAlign:"center",maxWidth:"520px"});
    var hero=makeCenteredCtaSection({
        kicker:"Launch Waitlist",
        heading:"Build early demand before your offer goes live",
        body:"Use this waitlist page to collect warm leads, tease the launch, and show why joining early matters.",
        headingSize:"38px",
        bodySize:"16px",
        padding:"80px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false,
        extraEls:[formEl]
    });
    var trust=makeTrustSection(["EARLY ACCESS","BONUS DROP","VIP LIST","FIRST NOTICE"]);
    var reasons=makeCardGridSection({
        title:"Why people sign up early",
        body:"Call out the exclusivity, bonuses, or launch updates they get by joining now.",
        columns:[
            makeFeatureCardColumn("gift","Launch bonus","Reward early subscribers with an exclusive bonus or discount."),
            makeFeatureCardColumn("clock","Priority access","Let them know they will hear about openings first."),
            makeFeatureCardColumn("users","Community feel","Create momentum by framing this as an insider list.")
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,trust,reasons]);
}
function templateApplicationCallLayout(){
    var formEl=makeOptInFormEl("Apply Now",[
        {type:"first_name",label:"First name",placeholder:"First name",required:false},
        {type:"email",label:"Email",placeholder:"Email address",required:true},
        {type:"phone_number",label:"Phone",placeholder:"09XXXXXXXXX",required:false}
    ],{alignment:"left",buttonAlign:"left",maxWidth:"100%",style:makeBareCardStyle()});
    var hero=makeSplitInfoSection(
        makePanelColumn(makeIntroElements(
            "Application Funnel",
            "Qualify leads before you ever get on a call",
            "Use this page when you want the next step to feel selective, credible, and higher value.",
            {extraEls:[
                makeEl("text",buildChecklistHtml("This layout works best when you need to:",[
                    "Frame the application as a premium next step",
                    "Set expectations before the form",
                    "Reduce low-intent submissions"
                ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0"}, {})
            ]}
        )),
        makePanelColumn([
            makeEl("heading","Tell them what happens next",{fontSize:"24px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text","Explain review time, who this is for, and what they can expect after applying.",{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 16px"},{}),
            formEl
        ]),
        {padding:"78px 24px 38px"}
    );
    var proof=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[
            makePanelColumn([makeTestimonialCardEl("The application format instantly improved lead quality for our sales calls.","Alyssa Park","Agency Owner",makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("We stopped attracting casual clicks and started talking to better-fit prospects.","Nico Alvarez","Consultant",makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F3EEF7",[hero,proof]);
}
function templateNewsletterDigestLayout(){
    var formEl=makeOptInFormEl("Subscribe Free",[
        {type:"first_name",label:"First name",placeholder:"First name",required:false},
        {type:"email",label:"Email",placeholder:"Email address",required:true}
    ],{alignment:"center",buttonAlign:"center",maxWidth:"460px"});
    var hero=makeCenteredCtaSection({
        kicker:"Weekly Newsletter",
        heading:"Turn casual visitors into repeat readers",
        body:"Use a simple editorial-style page when the offer is your insights, updates, or curated resources.",
        headingSize:"38px",
        bodySize:"16px",
        padding:"80px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false,
        extraEls:[formEl]
    });
    var issues=makeCardGridSection({
        title:"What to include below the signup",
        body:"Show what subscribers receive, how often you send, and why your perspective is worth following.",
        columns:[
            makeFeatureCardColumn("envelope","Clear promise","Describe the type of insight or updates subscribers receive."),
            makeFeatureCardColumn("calendar-days","Consistent cadence","Set expectations with a reliable weekly or monthly rhythm."),
            makeFeatureCardColumn("star","Trusted perspective","Use proof, niche expertise, or recent wins to build confidence.")
        ]
    });
    var social=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[
            makePanelColumn([makeTestimonialCardEl("Our newsletter opt-in rate improved as soon as the page felt more intentional.","Jamie Lee","Creator",makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("This gives a simple but premium structure for audience building.","Chris Park","Founder",makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,issues,social]);
}
function templateSalesOfferStackLayout(){
    var hero=makeSplitInfoSection(
        makeColumn(makeIntroElements(
            "Offer Page",
            "Present the transformation before the price",
            "Lead with the result, reinforce it with proof, and only then move into the offer details."
        )),
        makePanelColumn([
            makePricingCardEl({
                plan:"Signature Offer",
                price:"1,500",
                period:"",
                subtitle:"Ideal for clients who want faster implementation",
                features:["Strategy kickoff","Custom build","Launch support"],
                badge:"Most Popular"
            },makeBareCardStyle())
        ]),
        {alignItems:"stretch"}
    );
    var sections=makeCardGridSection({
        title:"Build the sales story in this order",
        body:"This layout is designed for consultants, service providers, or high-ticket digital offers.",
        columns:[
            makeFeatureCardColumn("circle-check","Outcome first","Open with the transformation clients want, not your whole process."),
            makeFeatureCardColumn("comments","Proof second","Use testimonials, wins, and credibility markers before the pitch."),
            makeFeatureCardColumn("tag","Offer third","Present pricing only after the value feels concrete.")
        ]
    });
    var close=makeCardGridSection({
        padding:"0 24px 28px",
        columns:[
            makePanelColumn([makeTestimonialCardEl("This structure made our offer page feel premium without getting too long.","Morgan Yu","Business Coach",makeBareCardStyle())]),
            makePanelColumn([makeFaqCardEl([
                {q:"Is this layout only for services?",a:"No, it also works well for premium digital products and programs."},
                {q:"Should I include pricing right away?",a:"Lead with the promise and proof first, then reveal the offer."}
            ],makeBareCardStyle())])
        ]
    });
    var cta=makeCenteredCtaSection({
        heading:"Keep the offer choice clear",
        body:"Use the pricing card on this page as the real call to action. The chosen offer should carry forward into checkout."
    });
    return buildTemplateLayout("#F3EEF7",[hero,sections,close,cta]);
}
function templateVideoSalesLetterLayout(){
    var hero=makeSplitInfoSection(
        makeColumn(makeIntroElements(
            "Video Sales Letter",
            "Pair a strong opening promise with a focused sales video",
            "Use this format when the offer sells best through story, demonstration, or teaching.",
            {button:{label:"Watch The Overview"}}
        )),
        makePanelColumn([
            makeEl("video","",{width:"100%"},{src:"",alignment:"center"})
        ])
    );
    var proof=makeCardGridSection({
        title:"Support the video with crisp proof points",
        body:"Keep the supporting sections short so the video stays the hero of the page.",
        columns:[
            makeFeatureCardColumn("play","Lead with movement","Use video when tone, explanation, or demo matters most."),
            makeFeatureCardColumn("shield-halved","Remove friction","Add proof, guarantees, and short objection-handling sections."),
            makeFeatureCardColumn("cart-shopping","Close clearly","Use a direct call to action after the key proof blocks.")
        ]
    });
    var offer=makeCardGridSection({
        padding:"0 24px 28px",
        columns:[
            makePanelColumn([makePricingCardEl({
                plan:"Launch Intensive",
                price:"497",
                period:"",
                subtitle:"Fast-start program with templates and support",
                features:["Video training","Templates","Q and A session"],
                badge:"Limited Spots"
            },makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("The video-first layout made our sales page feel far more persuasive.","Darren Cole","Course Creator",makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,proof,offer]);
}
function templateComparisonSalesLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Offer Comparison",
        heading:"Show the best-fit option and help visitors choose faster",
        body:"This format works when you need to compare plans, bundles, or implementation levels before checkout.",
        headingSize:"38px",
        bodySize:"16px",
        padding:"80px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var pricing=makeCardGridSection({
        padding:"0 24px 32px",
        backgroundColor:"#ffffff",
        columns:[
            makePanelColumn([makePricingCardEl({plan:"Starter",price:"49",period:"/month",subtitle:"For getting started",features:["Core pages","Email capture","Basic support"]},makeBareCardStyle())]),
            makePanelColumn([makePricingCardEl({plan:"Growth",price:"99",period:"/month",subtitle:"For serious launches",features:["Unlimited funnels","Priority support","Analytics"],badge:"Best Value"},makeBareCardStyle())]),
            makePanelColumn([makePricingCardEl({plan:"Scale",price:"199",period:"/month",subtitle:"For advanced teams",features:["Advanced insights","Team access","Hands-on onboarding"]},makeBareCardStyle())])
        ]
    });
    var faq=makeCardGridSection({
        padding:"0 24px 28px",
        columns:[
            makePanelColumn([makeFaqCardEl([
                {q:"Can I upgrade later?",a:"Yes, upgrade when your funnel needs expand."},
                {q:"Which plan should I highlight?",a:"Use the middle or best-fit option as your anchor offer."}
            ],makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("This layout made plan selection feel much clearer and less overwhelming.","Sofia Tran","Marketing Lead",makeBareCardStyle())])
        ]
    });
    var cta=makeCenteredCtaSection({
        heading:"Let the selected plan drive checkout",
        body:"Each pricing button should carry its own plan and amount into the checkout step."
    });
    return buildTemplateLayout("#F3EEF7",[hero,pricing,faq,cta]);
}
function templatePremiumCheckoutLayout(){
    var summary=makeSplitInfoSection(
        makePanelColumn([makePricingCardEl({
            plan:"Premium Access",
            price:"299",
            period:"",
            subtitle:"Everything needed to launch with confidence",
            features:["Templates included","Priority email support","Bonus training vault"],
            badge:"Secure Checkout"
        },makeBareCardStyle())]),
        makePanelColumn([
            makeEl("heading","Before you complete the order",{fontSize:"24px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text",buildChecklistHtml("Use this panel for key reassurance",[
                "Summarize what is included",
                "Call out refund or guarantee details",
                "Remind them what happens immediately after purchase"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 18px"},{}),
            makeEl("text","Use the Pay Now button on the pricing card to complete checkout with the correct amount.",{fontSize:"14px",color:"#1d4ed8",fontWeight:"700",margin:"0 0 10px"},{}),
            makeEl("text","Secure checkout ready. Replace this with your final checkout copy.",{fontSize:"13px",color:"#64748b",margin:"12px 0 0"},{}),
            makeTestimonialCardEl("The checkout finally felt premium and trustworthy instead of rushed.","Ari Flores","Operations Lead",makeBareCardStyle({margin:"18px 0 0"}))
        ]),
        {alignItems:"stretch"}
    );
    var trust=makeCardGridSection({
        title:"What to reassure buyers about",
        body:"This is the place for guarantees, delivery timing, and support details.",
        columns:[
            makeFeatureCardColumn("lock","Secure purchase","Reassure them the order process is protected and straightforward."),
            makeFeatureCardColumn("circle-check","Instant access","Explain how they receive the product, login, or next instructions."),
            makeFeatureCardColumn("comments","Support available","Set expectations for onboarding, delivery, or support response times.")
        ]
    });
    return buildTemplateLayout("#F8F5FB",[summary,trust]);
}
function templateWorkshopTicketCheckoutLayout(){
    var countdown=makeEl("countdown","",{width:"100%"},{endAt:futureCountdownValue(7),label:"Ticket price changes in",expiredText:"Price update coming soon",numberColor:"#240E35",labelColor:"#64748b",itemGap:8});
    var ticket=makeSplitInfoSection(
        makePanelColumn([
            makeEl("heading","Workshop registration",{fontSize:"28px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text","Use this style for live events, trainings, and intensives where urgency matters.",{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 16px"},{}),
            countdown,
            makeEl("text","Use the pricing card to the right to secure the ticket at the displayed amount.",{fontSize:"14px",color:"#1d4ed8",fontWeight:"700",margin:"16px 0 0"}, {})
        ]),
        makePanelColumn([makePricingCardEl({
            plan:"Workshop Ticket",
            price:"97",
            period:"",
            subtitle:"Live training plus replay and workbook",
            features:["90-minute workshop","Replay access","Action workbook"],
            badge:"Limited Seats"
        },makeBareCardStyle())]),
        {alignItems:"stretch"}
    );
    var agenda=makeCardGridSection({
        title:"Show buyers what the ticket includes",
        body:"Agenda blocks help the workshop feel more tangible and valuable before the purchase.",
        columns:[
            makeFeatureCardColumn("calendar-days","Live session","Explain how long the event is and what they will learn."),
            makeFeatureCardColumn("gift","Bonus assets","List the workbook, replay, templates, or support extras."),
            makeFeatureCardColumn("clock","Fast access","Tell them when they will receive event details and reminders.")
        ]
    });
    return buildTemplateLayout("#F3EEF7",[ticket,agenda]);
}
function templateWorkshopTicketCheckoutDiscountLayout(){
    // Same as the workshop checkout template, but includes a regular price so the countdown can switch displayed pricing.
    var countdown=makeEl("countdown","",{width:"100%"},{
        endAt:futureCountdownValue(7),
        label:"Ticket price changes in",
        expiredText:"Price update coming soon",
        numberColor:"#240E35",
        labelColor:"#64748b",
        itemGap:8
    });
    var ticket=makeSplitInfoSection(
        makePanelColumn([
            makeEl("heading","Workshop registration",{fontSize:"28px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text","Use this style for live events, trainings, and intensives where urgency matters.",{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 16px"},{}),
            countdown,
            makeEl("text","Use the pricing card to the right to secure the ticket at the displayed amount.",{fontSize:"14px",color:"#1d4ed8",fontWeight:"700",margin:"16px 0 0"}, {})
        ]),
        makePanelColumn([makePricingCardEl({
            plan:"Workshop Ticket",
            price:"97",
            regularPrice:"117",
            period:"",
            subtitle:"Live training plus replay and workbook",
            features:["90-minute workshop","Replay access","Action workbook"],
            badge:"Limited Seats"
        },makeBareCardStyle())]),
        {alignItems:"stretch"}
    );
    var agenda=makeCardGridSection({
        title:"Show buyers what the ticket includes",
        body:"Agenda blocks help the workshop feel more tangible and valuable before the purchase.",
        columns:[
            makeFeatureCardColumn("calendar-days","Live session","Explain how long the event is and what they will learn."),
            makeFeatureCardColumn("gift","Bonus assets","List the workbook, replay, templates, or support extras."),
            makeFeatureCardColumn("clock","Fast access","Tell them when they will receive event details and reminders.")
        ]
    });
    return buildTemplateLayout("#F3EEF7",[ticket,agenda]);
}
function templateBundleCheckoutLayout(){
    var hero=makeSplitInfoSection(
        makeColumn(makeIntroElements(
            "Bundle Checkout",
            "Increase perceived value with a stacked-offer checkout",
            "This layout is ideal when you want the main purchase and included bonuses to feel especially clear. Use the pricing card to complete checkout at the displayed amount."
        )),
        makePanelColumn([makePricingCardEl({
            plan:"Launch Bundle",
            price:"149",
            period:"",
            subtitle:"Core offer plus valuable bonuses",
            features:["Main product","Bonus template pack","Private Q and A","Quick-start checklist"],
            badge:"Bundle Price"
        },makeBareCardStyle())]),
        {alignItems:"stretch"}
    );
    var bonuses=makeCardGridSection({
        title:"Use the bonus section to justify the stack",
        body:"Spell out the add-ons so the bundle feels like a stronger, easier yes.",
        columns:[
            makeFeatureCardColumn("gift","Bonus templates","Add ready-made assets that shorten the buyer's path to success."),
            makeFeatureCardColumn("bolt","Quick-start checklist","Help them get results faster with a simple implementation guide."),
            makeFeatureCardColumn("comments","Support touchpoint","Add a short support or coaching bonus to lift the offer.")
        ]
    });
    var proof=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[
            makePanelColumn([makeTestimonialCardEl("The bundle framing made the order feel much more complete and worthwhile.","Jamie Lee","Founder",makeBareCardStyle())]),
            makePanelColumn([makeFaqCardEl([
                {q:"Should I show every bonus value?",a:"Yes, showing value stacks can make the bundle easier to justify."},
                {q:"Can the call to action stay simple?",a:"Yes, one strong checkout call to action is usually enough here."}
            ],makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,bonuses,proof]);
}
function templateMembershipCheckoutLayout(){
    var section=makeSplitInfoSection(
        makePanelColumn([makePricingCardEl({
            plan:"Membership Access",
            price:"39",
            period:"/month",
            subtitle:"Ongoing training, resources, and support",
            features:["New sessions monthly","Resource vault","Member-only updates"],
            badge:"Join Today"
        },makeBareCardStyle())]),
        makePanelColumn([
            makeEl("heading","Why this checkout works for subscriptions",{fontSize:"24px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
            makeEl("text",buildChecklistHtml("Use the right-side panel to clarify:",[
                "What members get every month",
                "Whether they can cancel any time",
                "What the first week inside looks like"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 18px"},{}),
            makeEl("text","Use the pricing card to start the membership with the displayed monthly amount.",{fontSize:"14px",color:"#1d4ed8",fontWeight:"700",margin:"0"}, {})
        ]),
        {alignItems:"stretch"}
    );
    var faq=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[
            makePanelColumn([makeFaqCardEl([
                {q:"Can members cancel later?",a:"Use this block to answer billing and cancellation questions clearly."},
                {q:"What should be highlighted most?",a:"Focus on the rhythm of value they receive after joining."}
            ],makeBareCardStyle())]),
            makePanelColumn([makeTestimonialCardEl("The recurring-offer checkout felt much more confident and clear with this structure.","Nico Alvarez","Community Builder",makeBareCardStyle())])
        ]
    });
    return buildTemplateLayout("#F3EEF7",[section,faq]);
}
function templateUpsellVipUpgradeLayout(){
    var hero=makeSplitInfoSection(
        makePanelColumn([
            makeEl("heading","Wait. Add the VIP Upgrade before you go.",{fontSize:"30px",color:"#240E35",fontWeight:"800",margin:"0 0 12px"},{}),
            makeEl("text","This upsell page is built for a fast yes after checkout. Highlight the added transformation, not a whole new pitch.",{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 18px"},{}),
            makeEl("text",buildChecklistHtml("Use this space to explain:",[
                "What buyers get on top of the original purchase",
                "Why adding it now is the best time",
                "What result becomes easier or faster with this upgrade"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0"},{}),
            makeEl("button","Yes, Add VIP Access",{backgroundColor:"#0F766E",color:"#ffffff",borderRadius:"999px",padding:"12px 18px",margin:"20px 10px 0 0"},{actionType:"offer_accept",link:"#"}),
            makeEl("button","No, Keep My Original Order",{backgroundColor:"#E5E7EB",color:"#240E35",borderRadius:"999px",padding:"12px 18px",margin:"20px 0 0"},{actionType:"offer_decline",link:"#"})
        ]),
        makePanelColumn([
            makePricingCardEl({
                plan:"VIP Upgrade",
                price:"19",
                period:"",
                subtitle:"Extra support, premium resources, and a faster path to results",
                features:["Priority Q and A","Bonus implementation guide","VIP-only template pack"],
                badge:"One-Time Upgrade"
            },makeBareCardStyle()),
            makeTestimonialCardEl("The post-purchase upgrade worked because it felt like the natural next step, not a random extra.","Lia Ramos","Program Creator",makeBareCardStyle({margin:"18px 0 0"}))
        ]),
        {alignItems:"stretch"}
    );
    var support=makeCardGridSection({
        title:"What makes a strong upsell page",
        body:"Keep it specific, immediate, and clearly connected to the product they just bought.",
        columns:[
            makeFeatureCardColumn("bolt","Immediate win","Show how the upgrade helps them get results faster."),
            makeFeatureCardColumn("star","Higher-touch value","Position the upsell as premium support or premium access."),
            makeFeatureCardColumn("circle-check","Simple choice","Keep the decision focused with one accept and one decline path.")
        ]
    });
    return buildTemplateLayout("#F3FFF8",[hero,support]);
}
function templateDownsellLiteLayout(){
    var hero=makeSplitInfoSection(
        makePanelColumn([
            makeEl("heading","Not ready for the full upgrade?",{fontSize:"30px",color:"#240E35",fontWeight:"800",margin:"0 0 12px"},{}),
            makeEl("text","This downsell page gives a simpler fallback offer after an upsell decline. Make it feel easier, lighter, and lower commitment.",{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0 0 18px"},{}),
            makeEl("text",buildChecklistHtml("Use the downsell to offer:",[
                "A lower price point",
                "A lighter version of the bonus",
                "A reduced-commitment alternative"
            ]),{fontSize:"15px",color:"#64748b",lineHeight:"1.7",margin:"0"},{}),
            makeEl("button","Yes, Give Me the Lite Version",{backgroundColor:"#2563EB",color:"#ffffff",borderRadius:"999px",padding:"12px 18px",margin:"20px 10px 0 0"},{actionType:"offer_accept",link:"#"}),
            makeEl("button","No Thanks, Finish My Order",{backgroundColor:"#E5E7EB",color:"#240E35",borderRadius:"999px",padding:"12px 18px",margin:"20px 0 0"},{actionType:"offer_decline",link:"#"})
        ]),
        makePanelColumn([
            makePricingCardEl({
                plan:"Lite Bonus Pack",
                price:"9",
                period:"",
                subtitle:"A smaller add-on for buyers who want one practical extra",
                features:["Quick-start checklist","Bonus worksheet","Mini resource pack"],
                badge:"Lower Commitment"
            },makeBareCardStyle()),
            makeFaqCardEl([
                {q:"Why include a downsell?",a:"It gives buyers a softer second option instead of ending the offer sequence immediately."},
                {q:"What should change from the upsell?",a:"Usually the price, scope, or commitment level should feel easier to accept."}
            ],makeBareCardStyle({margin:"18px 0 0"}))
        ]),
        {alignItems:"stretch"}
    );
    var guidance=makeCardGridSection({
        title:"How to position the downsell",
        body:"Keep it obviously related to the upsell, but easier to say yes to in the moment.",
        columns:[
            makeFeatureCardColumn("tag","Reduce the price","A lower price is the clearest signal that this is the easier option."),
            makeFeatureCardColumn("layer-group","Trim the scope","Offer a slimmer version instead of the full premium upgrade."),
            makeFeatureCardColumn("heart","Protect goodwill","Make the fallback feel helpful rather than pushy.")
        ]
    });
    return buildTemplateLayout("#F5F9FF",[hero,guidance]);
}
function templateThankYouDownloadLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Download Ready",
        heading:"Your resource is on the way",
        body:"Use this thank-you page to confirm the opt-in, set expectations, and guide visitors to the next useful action.",
        button:{label:"Open Resource",settings:{actionType:"link",link:"#"},style:{backgroundColor:"#6B4A7A"}},
        headingSize:"38px",
        bodySize:"16px",
        padding:"84px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var next=makeCardGridSection({
        title:"What to place below the confirmation",
        body:"Show the resource, recommend the next step, and reinforce the relationship after the signup.",
        columns:[
            makeFeatureCardColumn("image","Resource access","Tell them where the file, bonus, or replay can be found."),
            makeFeatureCardColumn("arrow-right","Next action","Send them to a sales page, community page, or onboarding step."),
            makeFeatureCardColumn("envelope","Inbox reminder","Let them know a copy or confirmation was sent by email.")
        ]
    });
    return buildTemplateLayout("#F8F5FB",[hero,next]);
}
function templateThankYouCommunityLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Community Invite",
        heading:"Welcome in. Here is what to do next.",
        body:"This format works well after a purchase or registration when you want the thank-you page to keep momentum going.",
        button:{label:"Join The Community",settings:{actionType:"link",link:"#"}},
        headingSize:"38px",
        bodySize:"16px",
        padding:"84px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var steps=makeCardGridSection({
        title:"Three smart next steps",
        body:"Use cards like these to reduce confusion after the conversion is complete.",
        columns:[
            makeFeatureCardColumn("circle-check","Check email","Tell them where important login or confirmation details were sent."),
            makeFeatureCardColumn("users","Join the group","Point them to your community, portal, or onboarding hub."),
            makeFeatureCardColumn("calendar-days","Watch for reminders","Set expectations for what they will receive next.")
        ]
    });
    return buildTemplateLayout("#F3EEF7",[hero,steps]);
}
function templateThankYouCallLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Call Confirmed",
        heading:"Your call is booked and the next steps are clear",
        body:"Use a stronger thank-you page after booking so people know how to prepare and what to expect.",
        button:{label:"Add To Calendar",settings:{actionType:"link",link:"#"}},
        headingSize:"38px",
        bodySize:"16px",
        padding:"84px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var prep=makeCardGridSection({
        title:"What to include before the meeting",
        body:"Reduce no-shows and improve call quality by making the prep simple and visible.",
        columns:[
            makeFeatureCardColumn("clock","Show up ready","Tell them exactly when to arrive and what link to use."),
            makeFeatureCardColumn("comment","Bring context","Ask them to prepare any questions, screenshots, or current metrics."),
            makeFeatureCardColumn("shield-halved","Set expectations","Explain what the meeting covers and what happens after.")
        ]
    });
    var proof=makeCardGridSection({
        padding:"0 24px 64px",
        columns:[makePanelColumn([makeTestimonialCardEl("Our thank-you page became a much stronger handoff after calls were booked.","Morgan Yu","Sales Strategist",makeBareCardStyle())])]
    });
    return buildTemplateLayout("#F8F5FB",[hero,prep,proof]);
}
function templateThankYouEventLayout(){
    var countdown=makeEl("countdown","",{width:"100%"},{endAt:futureCountdownValue(3),label:"Event starts in",expiredText:"The event has started",numberColor:"#240E35",labelColor:"#64748b",itemGap:8});
    var hero=makeCenteredCtaSection({
        kicker:"Event Confirmed",
        heading:"Your seat is saved",
        body:"Use this page after webinar or event registration to confirm the seat and keep anticipation high.",
        headingSize:"38px",
        bodySize:"16px",
        padding:"84px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false,
        extraEls:[countdown,makePrimaryButtonEl("Save My Spot",{
            actionType:"link",
            link:"#",
            alignment:"center"
        },{backgroundColor:"#6B4A7A"})]
    });
    var details=makeCardGridSection({
        title:"What to reinforce here",
        body:"Keep the thank-you focused on the reminder, logistics, and the payoff for attending.",
        columns:[
            makeFeatureCardColumn("calendar-days","Event details","Tell them the time, format, and how they will join."),
            makeFeatureCardColumn("gift","Bonus promise","Mention the replay, workbook, or attendee bonus."),
            makeFeatureCardColumn("paper-plane","Reminder flow","Explain when the follow-up emails and reminders will arrive.")
        ]
    });
    return buildTemplateLayout("#F3EEF7",[hero,details]);
}
function templateContactAuthorityLayout(){
    var hero=makeCenteredCtaSection({
        kicker:"Contact Page",
        heading:"Give visitors a clean path to reach out",
        body:"Use this as a polished custom page for contact, consulting inquiries, or partnership requests.",
        button:{label:"Start The Conversation",settings:{actionType:"link",link:"#"}},
        headingSize:"38px",
        bodySize:"16px",
        padding:"84px 24px 30px",
        backgroundColor:"#ffffff",
        bordered:false
    });
    var options=makeCardGridSection({
        title:"Common contact-page blocks",
        body:"These sections make the page feel complete without making it busy.",
        columns:[
            makeFeatureCardColumn("envelope","General inquiry","Share the best path for a first conversation or request."),
            makeFeatureCardColumn("calendar-days","Booking option","Invite qualified leads to the next scheduling step."),
            makeFeatureCardColumn("comments","Partnerships","Use a clear card for collaborations, speaking, or press requests.")
        ]
    });
    var close=makeCenteredCtaSection({
        heading:"Keep the next step clear",
        body:"This final section is a great place for a direct call to action or a concise response-time promise.",
        button:{label:"Book A Call"},
        backgroundColor:"#F8F5FB"
    });
    return buildTemplateLayout("#F3EEF7",[hero,options,close]);
}
const landingTemplates=[
    {id:"landing_hero_launch",name:"Hero Launch",description:"Classic launch hero with a strong call to action and product visual.",tags:["Landing","SaaS"],preview:"hero",build:templateHeroLayout},
    {id:"landing_feature_grid",name:"Feature Grid",description:"Headline-led landing page with clean proof cards.",tags:["Landing","Product"],preview:"cards",build:templateFeatureGridLayout},
    {id:"landing_story_stats",name:"Story + Stats",description:"Founder-style landing layout with image story and metrics.",tags:["Landing","Brand"],preview:"hero",build:templateStoryLayout},
    {id:"landing_authority",name:"Authority Page",description:"Professional service-focused landing page with trust and call to action flow.",tags:["Landing","Service"],preview:"hero",build:templateAuthorityLandingLayout},
    {id:"landing_app_showcase",name:"App Showcase",description:"Modern landing layout for apps, tools, and product demos.",tags:["Landing","Showcase"],preview:"cards",build:templateAppShowcaseLayout}
];
const optInTemplates=[
    {id:"optin_lead_capture",name:"Lead Capture",description:"Simple centered opt-in for checklists, guides, and freebies.",tags:["Opt-in","Lead"],preview:"lead",build:templateLeadCaptureLayout},
    {id:"optin_webinar_signup",name:"Webinar Signup",description:"Countdown-first registration layout for live events.",tags:["Opt-in","Webinar"],preview:"lead",build:templateWebinarLayout},
    {id:"optin_waitlist_launch",name:"Waitlist Launch",description:"Early-access waitlist page with launch momentum.",tags:["Opt-in","Waitlist"],preview:"lead",build:templateWaitlistLaunchLayout},
    {id:"optin_application_call",name:"Application Page",description:"Premium application-style opt-in for higher-intent leads.",tags:["Opt-in","Application"],preview:"hero",build:templateApplicationCallLayout},
    {id:"optin_newsletter_digest",name:"Newsletter Digest",description:"Editorial signup page for newsletters and weekly insights.",tags:["Opt-in","Newsletter"],preview:"lead",build:templateNewsletterDigestLayout}
];
const salesTemplates=[
    {id:"sales_pricing_faq",name:"Pricing + FAQ",description:"Straight-to-offer sales page with pricing and objection handling.",tags:["Sales","Pricing"],preview:"pricing",build:templatePricingFaqLayout},
    {id:"sales_offer_stack",name:"Offer Stack",description:"Transformation-led sales page for services or premium offers.",tags:["Sales","Offer"],preview:"pricing",build:templateSalesOfferStackLayout},
    {id:"sales_video_letter",name:"Video Sales Letter",description:"Sales page built around a focused video pitch.",tags:["Sales","Video"],preview:"video",build:templateVideoSalesLetterLayout},
    {id:"sales_story_close",name:"Story Close",description:"Story-driven sales page that mixes brand narrative and proof.",tags:["Sales","Story"],preview:"hero",build:templateStoryLayout},
    {id:"sales_comparison",name:"Comparison Close",description:"Multi-plan sales page that helps buyers choose fast.",tags:["Sales","Plans"],preview:"pricing",build:templateComparisonSalesLayout}
];
const checkoutTemplates=[
    {id:"checkout_split",name:"Checkout Split",description:"Simple two-column checkout starter with summary and order area.",tags:["Checkout","Starter"],preview:"pricing",build:templateCheckoutLayout},
    {id:"checkout_premium",name:"Premium Checkout",description:"Polished checkout page with reassurance copy and proof.",tags:["Checkout","Premium"],preview:"pricing",build:templatePremiumCheckoutLayout},
    {id:"checkout_workshop_ticket",name:"Workshop Ticket",description:"Event-ticket checkout with urgency and agenda framing.",tags:["Checkout","Event"],preview:"pricing",build:templateWorkshopTicketCheckoutLayout},
    {id:"checkout_workshop_ticket_discount",name:"Workshop Ticket (Countdown Discount)",description:"Workshop checkout with a regular price so the countdown can update pricing.",tags:["Checkout","Event","Countdown"],preview:"pricing",build:templateWorkshopTicketCheckoutDiscountLayout},
    {id:"checkout_bundle",name:"Bundle Checkout",description:"Stacked-offer checkout for bundles and bonus-heavy offers.",tags:["Checkout","Bundle"],preview:"pricing",build:templateBundleCheckoutLayout},
    {id:"checkout_membership",name:"Membership Checkout",description:"Recurring-offer checkout layout for subscriptions and communities.",tags:["Checkout","Membership"],preview:"pricing",build:templateMembershipCheckoutLayout}
];
const upsellTemplates=[
    {id:"upsell_vip_upgrade",name:"VIP Upgrade",description:"Post-purchase upgrade page with a premium add-on offer and clear accept/decline actions.",tags:["Upsell","Offer"],preview:"pricing",build:templateUpsellVipUpgradeLayout}
];
const downsellTemplates=[
    {id:"downsell_lite_offer",name:"Lite Offer",description:"Fallback post-purchase page for a lighter, lower-commitment offer after an upsell decline.",tags:["Downsell","Offer"],preview:"pricing",build:templateDownsellLiteLayout}
];
const thankYouTemplates=[
    {id:"thankyou_centered",name:"Classic Thank You",description:"Centered confirmation page with a clean next step.",tags:["Thank You","Starter"],preview:"banner",build:templateThankYouLayout},
    {id:"thankyou_download",name:"Download Delivery",description:"Thank-you page for guides, replays, and resource delivery.",tags:["Thank You","Download"],preview:"banner",build:templateThankYouDownloadLayout},
    {id:"thankyou_community",name:"Community Invite",description:"Use the thank-you page to move buyers into the next ecosystem step.",tags:["Thank You","Community"],preview:"banner",build:templateThankYouCommunityLayout},
    {id:"thankyou_call",name:"Call Confirmed",description:"Booking confirmation layout with prep instructions and reassurance.",tags:["Thank You","Call"],preview:"banner",build:templateThankYouCallLayout},
    {id:"thankyou_event",name:"Event Confirmed",description:"Confirmation page for webinars and workshops with reminder flow.",tags:["Thank You","Event"],preview:"banner",build:templateThankYouEventLayout}
];
const customTemplates=[
    {id:"custom_story_stats",name:"Story + Stats",description:"Flexible custom page for story-led brand content.",tags:["Custom","Story"],preview:"hero",build:templateStoryLayout},
    {id:"custom_hero_video",name:"Hero + Video",description:"General-purpose custom page with media-led storytelling.",tags:["Custom","Video"],preview:"video",build:templateHeroVideoLayout},
    {id:"custom_feature_grid",name:"Feature Grid",description:"Modular custom page for product, service, or feature sections.",tags:["Custom","Blocks"],preview:"cards",build:templateFeatureGridLayout},
    {id:"custom_pricing_faq",name:"Pricing + FAQ",description:"Reusable custom layout for plans, comparisons, or help content.",tags:["Custom","Pricing"],preview:"pricing",build:templatePricingFaqLayout},
    {id:"custom_contact",name:"Contact Authority",description:"Professional custom page for contact, booking, or inquiries.",tags:["Custom","Contact"],preview:"banner",build:templateContactAuthorityLayout}
];
const builtInTemplatesByType={
    landing:landingTemplates,
    opt_in:optInTemplates,
    sales:salesTemplates,
    checkout:checkoutTemplates,
    upsell:upsellTemplates,
    downsell:downsellTemplates,
    thank_you:thankYouTemplates,
    custom:customTemplates
};
function normalizeTemplateType(type){
    var t=String(type||"custom").toLowerCase();
    return builtInTemplatesByType[t]?t:"custom";
}
function findTemplateById(type,id){
    var list=builtInTemplatesByType[normalizeTemplateType(type)]||customTemplates;
    var target=String(id||"").trim();
    return list.find(function(t){return String(t.id||"")===target;})||list[0]||customTemplates[0];
}
function buildFunnelStepHref(stepLike){
    var funnel=String(funnelSlug||"").trim();
    var slug=String(stepLike&&stepLike.slug||"").trim();
    if(funnel===""||slug==="")return "#";
    if(typeof funnelStepUrlTpl==="string"&&funnelStepUrlTpl.indexOf("__STEP__")>=0){
        return funnelStepUrlTpl.replace("__STEP__",encodeURIComponent(slug));
    }
    return "/f/"+encodeURIComponent(funnel)+"/"+encodeURIComponent(slug);
}
function templateStepType(stepLike){
    return normalizeTemplateType((stepLike&&stepLike.type)||stepLike||"custom");
}
function stepIndexInFlow(stepList,currentStep){
    var currentId=String((currentStep&&currentStep.id)||"");
    return (Array.isArray(stepList)?stepList:[]).findIndex(function(step){
        return String((step&&step.id)||"")===currentId;
    });
}
function firstOtherFlowStep(stepList,currentStep){
    return (Array.isArray(stepList)?stepList:[]).find(function(step){
        return step&&String(step.id||"")!==String((currentStep&&currentStep.id)||"");
    })||null;
}
function nextFlowStep(stepList,currentStep){
    var list=Array.isArray(stepList)?stepList:[];
    var idx=stepIndexInFlow(list,currentStep);
    if(idx<0||idx>=list.length-1)return null;
    return list[idx+1]||null;
}
function findFlowStepByTypes(stepList,currentStep,types){
    var list=Array.isArray(stepList)?stepList:[];
    var wanted=(Array.isArray(types)?types:[]).map(normalizeTemplateType);
    if(!wanted.length)return null;
    var currentId=String((currentStep&&currentStep.id)||"");
    var idx=stepIndexInFlow(list,currentStep);
    if(idx>=0){
        for(var i=idx+1;i<list.length;i++){
            var later=list[i];
            if(!later||String(later.id||"")===currentId)continue;
            if(wanted.indexOf(templateStepType(later))>=0)return later;
        }
    }
    for(var j=0;j<list.length;j++){
        var step=list[j];
        if(!step||String(step.id||"")===currentId)continue;
        if(wanted.indexOf(templateStepType(step))>=0)return step;
    }
    return null;
}
function chooseTemplateTargetStep(stepList,currentStep,labelText){
    var text=String(labelText||"").trim().toLowerCase();
    var nextStep=nextFlowStep(stepList,currentStep);
    var homeStep=findFlowStepByTypes(stepList,currentStep,["landing"])||firstOtherFlowStep(stepList,currentStep)||null;
    if(/(^|\s)(home|back)(\s|$)/.test(text)){
        return homeStep;
    }
    if(nextStep){
        return nextStep;
    }
    return homeStep||firstOtherFlowStep(stepList,currentStep);
}
function choosePricingTargetStep(stepList,currentStep,labelText){
    var text=String(labelText||"").trim().toLowerCase();
    var currentType=templateStepType(currentStep);
    var nextStep=nextFlowStep(stepList,currentStep);
    var homeStep=findFlowStepByTypes(stepList,currentStep,["landing"])||firstOtherFlowStep(stepList,currentStep)||null;
    var optInStep=findFlowStepByTypes(stepList,currentStep,["opt_in"]);
    var salesStep=findFlowStepByTypes(stepList,currentStep,["sales"]);
    var checkoutStep=findFlowStepByTypes(stepList,currentStep,["checkout"]);
    var thankYouStep=findFlowStepByTypes(stepList,currentStep,["thank_you"]);
    var customStep=findFlowStepByTypes(stepList,currentStep,["custom"]);
    if(/(^|\s)(home|back)(\s|$)/.test(text)){
        return homeStep;
    }
    if(/(checkout|purchase|buy|order|seat|spot|bundle|membership)/.test(text) && checkoutStep){
        return checkoutStep;
    }
    if(/(community|resource|download|calendar)/.test(text) && customStep){
        return customStep;
    }
    if(currentType==="sales"||currentType==="custom"){
        return checkoutStep||thankYouStep||customStep||nextStep||homeStep||salesStep;
    }
    if(currentType==="landing"){
        return salesStep||checkoutStep||optInStep||nextStep||thankYouStep||customStep||homeStep;
    }
    if(currentType==="opt_in"){
        return salesStep||checkoutStep||thankYouStep||customStep||nextStep||homeStep;
    }
    if(currentType==="checkout"){
        return thankYouStep||customStep||nextStep||homeStep;
    }
    if(currentType==="thank_you"){
        return customStep||homeStep||nextStep||firstOtherFlowStep(stepList,currentStep);
    }
    return checkoutStep||salesStep||thankYouStep||customStep||optInStep||homeStep||nextStep||firstOtherFlowStep(stepList,currentStep);
}
function wireButtonElementForStep(el,currentStep,stepList){
    if(!el||typeof el!=="object")return;
    el.settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
    var currentType=templateStepType(currentStep);
    var currentAction=String(el.settings.actionType||"").trim().toLowerCase();
    var labelText=String(el.content||"").replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim().toLowerCase();
    if(currentType==="checkout"){
        el.settings.actionType="checkout";
        el.settings.actionStepSlug="";
        el.settings.link="#";
        return;
    }
    if(currentType==="upsell"||currentType==="downsell"){
        if(currentAction==="offer_accept"||currentAction==="offer_decline"){
            el.settings.actionStepSlug="";
            el.settings.link="#";
            return;
        }
        if(/(no thanks|skip|finish my order|keep my original order|decline)/.test(labelText)){
            el.settings.actionType="offer_decline";
            el.settings.actionStepSlug="";
            el.settings.link="#";
            return;
        }
        if(/(yes|add|upgrade|vip|lite version|give me|accept)/.test(labelText)){
            el.settings.actionType="offer_accept";
            el.settings.actionStepSlug="";
            el.settings.link="#";
            return;
        }
    }
    var target=chooseTemplateTargetStep(stepList,currentStep,el.content);
    if(target&&String(target.id||"")!==String((currentStep&&currentStep.id)||"")){
        el.settings.actionType="step";
        el.settings.actionStepSlug=String(target.slug||"");
        el.settings.link=buildFunnelStepHref(target);
        return;
    }
    el.settings.actionType="next_step";
    el.settings.actionStepSlug="";
    el.settings.link="#";
}
function wirePricingElementForStep(el,currentStep,stepList){
    if(!el||typeof el!=="object")return;
    el.settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
    var currentType=templateStepType(currentStep);
    if(currentType==="checkout"){
        if(String(el.settings.ctaLabel||"").trim()==="")el.settings.ctaLabel="Pay Now";
        el.settings.ctaActionType="checkout";
        el.settings.ctaActionStepSlug="";
        el.settings.ctaLink="#";
        return;
    }
    if(currentType==="upsell"||currentType==="downsell"){
        if(String(el.settings.ctaLabel||"").trim()===""){
            el.settings.ctaLabel=currentType==="downsell"?"Yes, Add This Offer":"Yes, Add This Upgrade";
        }
        el.settings.ctaActionType="offer_accept";
        el.settings.ctaActionStepSlug="";
        el.settings.ctaLink="#";
        return;
    }
    var ctaLabel=String(el.settings.ctaLabel||"").trim();
    var target=choosePricingTargetStep(stepList,currentStep,ctaLabel||el.settings.plan);
    if(ctaLabel===""){
        var planName=String(el.settings.plan||"").replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim();
        el.settings.ctaLabel=(currentType==="sales"&&planName!=="")?("Choose "+planName):"Get Started";
        ctaLabel=el.settings.ctaLabel;
    }
    el.settings.ctaActionType=(target&&String(target.id||"")!==String((currentStep&&currentStep.id)||""))?"step":"next_step";
    el.settings.ctaActionStepSlug=(target&&String(target.id||"")!==String((currentStep&&currentStep.id)||""))?String(target.slug||""):"";
    el.settings.ctaLink=(target&&String(target.id||"")!==String((currentStep&&currentStep.id)||""))?buildFunnelStepHref(target):"#";
}
function wireTemplateLayoutForStep(layout,currentStep,stepList){
    if(!layout||typeof layout!=="object")return layout;
    function visitElements(list){
        (Array.isArray(list)?list:[]).forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(el.type==="button")wireButtonElementForStep(el,currentStep,stepList);
            if(el.type==="pricing")wirePricingElementForStep(el,currentStep,stepList);
            var slideList=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            slideList.forEach(function(slide){
                if(!slide||typeof slide!=="object")return;
                visitElements(slide.elements);
            });
        });
    }
    function visitColumns(cols){
        (Array.isArray(cols)?cols:[]).forEach(function(col){
            if(!col||typeof col!=="object")return;
            visitElements(col.elements);
        });
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(section){
            if(!section||typeof section!=="object")return;
            visitElements(section.elements);
            (Array.isArray(section.rows)?section.rows:[]).forEach(function(row){
                if(!row||typeof row!=="object")return;
                visitColumns(row.columns);
            });
        });
    }
    if(Array.isArray(layout.root))visitSections(layout.root);
    if(Array.isArray(layout.sections))visitSections(layout.sections);
    return layout;
}
function isKnownTemplateStepHref(link,stepList){
    var raw=String(link||"").trim();
    if(raw==="")return false;
    var normalized=raw.replace(/\/+$/,"");
    return (Array.isArray(stepList)?stepList:[]).some(function(step){
        var href=String(buildFunnelStepHref(step)||"").trim().replace(/\/+$/,"");
        if(href===""||href==="#")return false;
        return normalized===href||normalized.indexOf(href+"?")===0;
    });
}
function parseTemplateMoneyValue(raw){
    var s=String(raw||"").trim();
    if(s==="")return null;
    s=s.replace(/[^0-9,.\-]/g,"");
    if(s==="")return null;
    var n=parseFloat(s.replace(/,/g,""));
    return (!isNaN(n)&&isFinite(n)&&n>0)?n:null;
}
function derivePrimaryPricingAmountFromLayout(layout){
    if(!layout||typeof layout!=="object")return null;
    function findInElements(list){
        var elements=Array.isArray(list)?list:[];
        for(var i=0;i<elements.length;i++){
            var el=elements[i];
            if(!el||typeof el!=="object")continue;
            if(String(el.type||"").toLowerCase()==="pricing"){
                var settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
                var price=parseTemplateMoneyValue(settings.price);
                if(price!==null)return price;
                var regular=parseTemplateMoneyValue(settings.regularPrice);
                if(regular!==null)return regular;
            }
            var slides=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            for(var j=0;j<slides.length;j++){
                var slide=slides[j];
                var nested=findInElements(slide&&slide.elements);
                if(nested!==null)return nested;
            }
        }
        return null;
    }
    function findInSections(list){
        var sections=Array.isArray(list)?list:[];
        for(var i=0;i<sections.length;i++){
            var sec=sections[i];
            if(!sec||typeof sec!=="object")continue;
            var sectionAmount=findInElements(sec.elements);
            if(sectionAmount!==null)return sectionAmount;
            var rows=Array.isArray(sec.rows)?sec.rows:[];
            for(var r=0;r<rows.length;r++){
                var row=rows[r];
                var cols=Array.isArray(row&&row.columns)?row.columns:[];
                for(var c=0;c<cols.length;c++){
                    var amount=findInElements(cols[c]&&cols[c].elements);
                    if(amount!==null)return amount;
                }
            }
        }
        return null;
    }
    return findInSections(layout.root)||findInSections(layout.sections);
}
function normalizeTemplateCurrencyValue(raw){
    var value=String(raw||"");
    if(/^\s*\$/.test(value)){
        return value.replace(/^\s*\$/,"\u20b1");
    }
    return value;
}
function normalizeTemplateCurrencyLayout(layout){
    if(!layout||typeof layout!=="object")return layout;
    function visitElements(list){
        (Array.isArray(list)?list:[]).forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(String(el.type||"").toLowerCase()==="pricing"){
                el.settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
                if(typeof el.settings.price==="string")el.settings.price=normalizeTemplateCurrencyValue(el.settings.price);
                if(typeof el.settings.regularPrice==="string")el.settings.regularPrice=normalizeTemplateCurrencyValue(el.settings.regularPrice);
            }
            var slides=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            slides.forEach(function(slide){
                if(slide&&Array.isArray(slide.elements))visitElements(slide.elements);
            });
        });
    }
    function visitSections(list){
        (Array.isArray(list)?list:[]).forEach(function(sec){
            if(!sec||typeof sec!=="object")return;
            visitElements(sec.elements);
            (Array.isArray(sec.rows)?sec.rows:[]).forEach(function(row){
                (Array.isArray(row&&row.columns)?row.columns:[]).forEach(function(col){
                    visitElements(col&&col.elements);
                });
            });
        });
    }
    visitSections(layout.root);
    visitSections(layout.sections);
    return layout;
}
function repairDefaultPricingFlowLayout(layout,currentStep,stepList){
    if(!layout||typeof layout!=="object")return layout;
    var currentType=templateStepType(currentStep);
    function shouldRepairPricing(el){
        var settings=(el&&el.settings&&typeof el.settings==="object")?el.settings:{};
        if(currentType==="checkout")return true;
        var action=String(settings.ctaActionType||"").trim().toLowerCase();
        var link=String(settings.ctaLink||"").trim();
        var slug=String(settings.ctaActionStepSlug||"").trim();
        if(action==="")return true;
        if(action==="next_step")return true;
        if(action==="step")return slug==="";
        if(action==="link")return link===""||link==="#"||isKnownTemplateStepHref(link,stepList);
        return false;
    }
    function visitElements(list){
        (Array.isArray(list)?list:[]).forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(el.type==="pricing"&&shouldRepairPricing(el))wirePricingElementForStep(el,currentStep,stepList);
            var slideList=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            slideList.forEach(function(slide){
                if(slide&&Array.isArray(slide.elements))visitElements(slide.elements);
            });
        });
    }
    function visitColumns(cols){
        (Array.isArray(cols)?cols:[]).forEach(function(col){
            if(!col||typeof col!=="object")return;
            visitElements(col.elements);
        });
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(section){
            if(!section||typeof section!=="object")return;
            visitElements(section.elements);
            (Array.isArray(section.rows)?section.rows:[]).forEach(function(row){
                if(!row||typeof row!=="object")return;
                visitColumns(row.columns);
            });
        });
    }
    if(Array.isArray(layout.root))visitSections(layout.root);
    if(Array.isArray(layout.sections))visitSections(layout.sections);
    return layout;
}
function repairTemplateFlowLayout(layout,currentStep,stepList){
    if(!layout||typeof layout!=="object")return layout;
    var currentType=templateStepType(currentStep);
    var hasPricing=derivePrimaryPricingAmountFromLayout(layout)!==null;
    function isRedundantSalesTemplateButton(el){
        if(currentType!=="sales"||!hasPricing||String(el&&el.type||"").toLowerCase()!=="button")return false;
        var settings=(el&&el.settings&&typeof el.settings==="object")?el.settings:{};
        var action=String(settings.actionType||"").trim().toLowerCase();
        var link=String(settings.link||"").trim();
        var isInternalNavigate=action===""||action==="next_step"||action==="step"||(action==="link"&&(link===""||link==="#"||isKnownTemplateStepHref(link,stepList)));
        if(!isInternalNavigate)return false;
        var label=String(el.content||"").replace(/<[^>]*>/g," ").replace(/\s+/g," ").trim().toLowerCase();
        return /^(see (the )?(package|plan|plans)|choose (a )?plan|go to checkout|continue to checkout|get started)$/.test(label);
    }
    function shouldRepairButton(el){
        var settings=(el&&el.settings&&typeof el.settings==="object")?el.settings:{};
        if(currentType==="checkout")return true;
        var action=String(settings.actionType||"").trim().toLowerCase();
        var link=String(settings.link||"").trim();
        if(action==="next_step"||action==="step")return true;
        if(action==="link")return link===""||link==="#"||isKnownTemplateStepHref(link,stepList);
        return action==="";
    }
    function shouldRepairPricing(el){
        var settings=(el&&el.settings&&typeof el.settings==="object")?el.settings:{};
        if(currentType==="checkout")return true;
        var action=String(settings.ctaActionType||"").trim().toLowerCase();
        var link=String(settings.ctaLink||"").trim();
        if(action==="next_step"||action==="step"||action==="checkout")return true;
        if(action==="link")return link===""||link==="#"||isKnownTemplateStepHref(link,stepList);
        return action==="";
    }
    function visitElements(list){
        for(var i=(Array.isArray(list)?list:[]).length-1;i>=0;i--){
            var el=list[i];
            if(!el||typeof el!=="object")continue;
            var settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
            if(currentType==="checkout"&&hasPricing&&String(el.type||"").toLowerCase()==="button"&&String(settings.actionType||"").trim().toLowerCase()==="checkout"){
                list.splice(i,1);
                continue;
            }
            if(isRedundantSalesTemplateButton(el)){
                list.splice(i,1);
                continue;
            }
            if(el.type==="button"&&shouldRepairButton(el))wireButtonElementForStep(el,currentStep,stepList);
            if(el.type==="pricing"&&shouldRepairPricing(el))wirePricingElementForStep(el,currentStep,stepList);
            var slideList=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            slideList.forEach(function(slide){
                if(slide&&Array.isArray(slide.elements))visitElements(slide.elements);
            });
        }
    }
    function visitColumns(cols){
        (Array.isArray(cols)?cols:[]).forEach(function(col){
            if(!col||typeof col!=="object")return;
            visitElements(col.elements);
        });
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(section){
            if(!section||typeof section!=="object")return;
            visitElements(section.elements);
            (Array.isArray(section.rows)?section.rows:[]).forEach(function(row){
                if(!row||typeof row!=="object")return;
                visitColumns(row.columns);
            });
        });
    }
    if(Array.isArray(layout.root))visitSections(layout.root);
    if(Array.isArray(layout.sections))visitSections(layout.sections);
    return layout;
}
function solidBorder(color){
    return "1px solid "+String(color||"#E6E1EF");
}
function isBareCardSkin(style){
    var s=(style&&typeof style==="object")?style:{};
    var border=String(s.border||"").trim().toLowerCase();
    var bg=String(s.backgroundColor||"").trim().toLowerCase();
    var shadow=String(s.boxShadow||"").trim().toLowerCase();
    var pad=String(s.padding||"").trim().toLowerCase();
    return border==="0"||border==="0px"||border==="none"||bg==="transparent"||shadow==="none"||pad==="0"||pad==="0px";
}
function autoLinkCountdownPricing(layout){
    if(!layout||typeof layout!=="object")return layout;
    var pricingEls=[];
    var countdownEls=[];

    function visitElements(list){
        (Array.isArray(list)?list:[]).forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(el.type==="pricing")pricingEls.push(el);
            if(el.type==="countdown")countdownEls.push(el);

            var slideList=(el.settings&&Array.isArray(el.settings.slides))?el.settings.slides:[];
            slideList.forEach(function(slide){
                if(slide&&Array.isArray(slide.elements)){
                    visitElements(slide.elements);
                }
            });
        });
    }
    function visitColumns(cols){
        (Array.isArray(cols)?cols:[]).forEach(function(col){
            if(!col||typeof col!=="object")return;
            if(col&&Array.isArray(col.elements)){
                visitElements(col.elements);
            }
        });
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(section){
            if(!section||typeof section!=="object")return;
            visitElements(section.elements);
            (Array.isArray(section.rows)?section.rows:[]).forEach(function(row){
                if(row&&Array.isArray(row.columns)){
                    visitColumns(row.columns);
                }
            });
        });
    }

    if(Array.isArray(layout.root))visitSections(layout.root);
    if(Array.isArray(layout.sections))visitSections(layout.sections);

    if(!pricingEls.length||!countdownEls.length)return layout;

    var pricingIds=pricingEls
        .map(function(p){return String(p&&p.id||"").trim();})
        .filter(Boolean);
    if(!pricingIds.length)return layout;

    var firstPricingEl=pricingEls[0];

    countdownEls.forEach(function(cd){
        cd.settings=(cd.settings&&typeof cd.settings==="object")?cd.settings:{};

        // Collect existing linked pricing ids (both new + legacy fields).
        var linkedIds=[];
        var raw=cd.settings.linkedPricingIds;
        if(Array.isArray(raw)){
            linkedIds=raw.map(function(v){return String(v||"").trim();}).filter(Boolean);
        }else if(typeof raw==="string"){
            var s=String(raw||"").trim();
            if(s){
                linkedIds=s.split(",").map(function(v){return String(v||"").trim();}).filter(Boolean);
            }
        }
        var legacy=String(cd.settings.linkedPricingId||"").trim();
        if(!linkedIds.length&&legacy!=="")linkedIds=[legacy];

        // If not linked yet, link to all pricing cards found on this step layout.
        if(!linkedIds.length){
            cd.settings.linkedPricingIds=pricingIds;
            if(Object.prototype.hasOwnProperty.call(cd.settings,"linkedPricingId")){
                delete cd.settings.linkedPricingId;
            }
            linkedIds=pricingIds;
        }

        // Countdown promoKey rule:
        // - If countdown promoKey is empty, copy it from the first linked pricing.
        // - If countdown promoKey is manual, do not override it.
        var curPromo=String(cd.settings.promoKey||"").trim();
        if(curPromo===""){
            var firstLinkedId=String(linkedIds[0]||"").trim();
            var pricingTarget=firstPricingEl;
            if(firstLinkedId!==""){
                var found=pricingEls.find(function(p){
                    return String(p&&p.id||"").trim()===firstLinkedId;
                });
                if(found)pricingTarget=found;
            }
            pricingTarget.settings=(pricingTarget.settings&&typeof pricingTarget.settings==="object")?pricingTarget.settings:{};
            var pPromo=String(pricingTarget.settings.promoKey||"").trim();
            if(pPromo===""){
                // Generate a safe promo key when pricing doesn't have one yet.
                pPromo=("promo_"+String(pricingTarget.id||"")).replace(/[^a-z0-9_\-]/gi,"");
                if(pPromo==="promo_")pPromo="promo_"+String(Date.now());
                pricingTarget.settings.promoKey=pPromo;
            }
            cd.settings.promoKey=pPromo;
        }
    });

    return layout;
}
function normalizeTemplateLayout(layout){
    var out=(layout&&typeof layout==="object")?clone(layout):{root:[],sections:[]};
    function isEmptyObject(obj){
        if(!obj||typeof obj!=="object")return true;
        return Object.keys(obj).length===0;
    }
    function hasOnlyDefaultRowStyle(style){
        if(!style||typeof style!=="object")return true;
        var keys=Object.keys(style);
        if(!keys.length)return true;
        if(keys.length===1&&keys[0]==="gap"){
            var gap=String(style.gap||"").trim();
            return gap===""||gap==="8px"||gap==="12px"||gap==="16px";
        }
        return false;
    }
    function isHoistableColumn(col){
        if(!col||typeof col!=="object")return false;
        var style=(col.style&&typeof col.style==="object")?col.style:{};
        var keys=Object.keys(style);
        if(!keys.length)return true;
        var allowed=["textAlign"];
        return keys.every(function(k){return allowed.indexOf(k)>=0;});
    }
    function hoistLeadingSimpleRows(section){
        if(!section||typeof section!=="object")return section;
        section.elements=Array.isArray(section.elements)?section.elements:[];
        section.rows=Array.isArray(section.rows)?section.rows:[];
        var keepRows=[];
        var canHoist=true;
        section.rows.forEach(function(row){
            var hoisted=false;
            if(canHoist&&row&&Array.isArray(row.columns)&&row.columns.length===1&&hasOnlyDefaultRowStyle(row.style)&&isEmptyObject(row.settings)){
                var col=row.columns[0];
                if(col&&Array.isArray(col.elements)&&col.elements.length&&isHoistableColumn(col)&&isEmptyObject(col.settings)){
                    section.style=(section.style&&typeof section.style==="object")?section.style:{};
                    if(col.style&&typeof col.style==="object"&&String(col.style.textAlign||"").trim()!==""&&String(section.style.textAlign||"").trim()===""){
                        section.style.textAlign=String(col.style.textAlign||"");
                    }
                    section.elements=section.elements.concat(col.elements);
                    hoisted=true;
                }
            }
            if(!hoisted){
                canHoist=false;
                keepRows.push(row);
            }
        });
        section.rows=keepRows;
        return section;
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(section,idx){
            if(!section||typeof section!=="object")return;
            visitSections(section.sections);
            sections[idx]=hoistLeadingSimpleRows(section);
        });
    }
    if(Array.isArray(out.root))visitSections(out.root);
    if(Array.isArray(out.sections))visitSections(out.sections);
    return out;
}
function applyFunnelThemeToLayout(layout,theme){
    var out=(layout&&typeof layout==="object")?clone(layout):{root:[],sections:[]};
    var brand=theme&&typeof theme==="object"?theme:{};
    var primary=/^#[0-9A-Fa-f]{6}$/.test(String(brand.primary||""))?String(brand.primary):"#240E35";
    var accent=/^#[0-9A-Fa-f]{6}$/.test(String(brand.accent||""))?String(brand.accent):primary;
    var heading=/^#[0-9A-Fa-f]{6}$/.test(String(brand.heading||""))?String(brand.heading):"#240E35";
    var body=/^#[0-9A-Fa-f]{6}$/.test(String(brand.body||""))?String(brand.body):"#64748b";
    var surface=/^#[0-9A-Fa-f]{6}$/.test(String(brand.surface||""))?String(brand.surface):"#ffffff";
    var soft=/^#[0-9A-Fa-f]{6}$/.test(String(brand.soft||""))?String(brand.soft):"#F8F5FB";
    var border=/^#[0-9A-Fa-f]{6}$/.test(String(brand.border||""))?String(brand.border):"#E6E1EF";
    function visitElements(list){
        (Array.isArray(list)?list:[]).forEach(function(el){
            if(!el||typeof el!=="object")return;
            el.style=(el.style&&typeof el.style==="object")?el.style:{};
            el.settings=(el.settings&&typeof el.settings==="object")?el.settings:{};
            var bareSkin=isBareCardSkin(el.style);
            if(el.type==="heading"){
                el.style.color=heading;
            }else if(el.type==="text"){
                el.style.color=body;
            }else if(el.type==="button"){
                el.style.backgroundColor=primary;
                el.style.color="#ffffff";
            }else if(el.type==="icon"){
                el.style.color=accent;
            }else if(el.type==="form"){
                el.settings.labelColor=heading;
                el.settings.placeholderColor="#94a3b8";
                el.settings.buttonBgColor=primary;
                el.settings.buttonTextColor="#ffffff";
                if(!bareSkin){
                    el.style.backgroundColor=surface;
                    el.style.border=solidBorder(border);
                }
            }else if(el.type==="pricing"){
                el.settings.ctaBgColor=primary;
                el.settings.ctaTextColor="#ffffff";
                if(!bareSkin){
                    el.style.backgroundColor=surface;
                    el.style.border=solidBorder(border);
                }
            }else if(el.type==="faq"){
                el.settings.questionColor=heading;
                el.settings.answerColor=body;
                if(!bareSkin){
                    el.style.backgroundColor=surface;
                    el.style.border=solidBorder(border);
                }
            }else if(el.type==="testimonial"){
                el.style.color=heading;
                if(!bareSkin){
                    el.style.backgroundColor=surface;
                    el.style.border=solidBorder(border);
                }
            }else if(el.type==="countdown"){
                el.settings.numberColor=primary;
                el.settings.labelColor=body;
                if(!bareSkin){
                    el.style.backgroundColor=surface;
                    el.style.border=solidBorder(border);
                }
            }
        });
    }
    function visitColumns(cols){
        (Array.isArray(cols)?cols:[]).forEach(function(col){
            if(!col||typeof col!=="object")return;
            col.style=(col.style&&typeof col.style==="object")?col.style:{};
            if(col.style.border||col.style.boxShadow){
                col.style.backgroundColor=surface;
                col.style.border=solidBorder(border);
            }else if(String(col.style.backgroundColor||"").toLowerCase()==="#f8f5fb"){
                col.style.backgroundColor=soft;
            }
            visitElements(col.elements);
        });
    }
    function visitSections(sections){
        (Array.isArray(sections)?sections:[]).forEach(function(sec){
            if(!sec||typeof sec!=="object")return;
            sec.style=(sec.style&&typeof sec.style==="object")?sec.style:{};
            var secBg=String(sec.style.backgroundColor||"").toLowerCase();
            if(secBg!==""&&secBg!=="#ffffff"&&secBg!=="transparent")sec.style.backgroundColor=soft;
            visitElements(sec.elements);
            (Array.isArray(sec.rows)?sec.rows:[]).forEach(function(row){
                if(!row||typeof row!=="object")return;
                row.style=(row.style&&typeof row.style==="object")?row.style:{};
                visitColumns(row.columns);
            });
        });
    }
    if(Array.isArray(out.root))visitSections(out.root);
    if(Array.isArray(out.sections))visitSections(out.sections);
    return out;
}
function buildPackLayout(pack,stepLike,stepList){
    var type=normalizeTemplateType((stepLike&&stepLike.type)||stepLike);
    var packTemplates=(pack&&pack.templates&&typeof pack.templates==="object")?pack.templates:{};
    var tpl=findTemplateById(type,packTemplates[type]||packTemplates.custom||"");
    var built=(tpl&&typeof tpl.build==="function")?tpl.build():defaults(type);
    built=applyFunnelThemeToLayout(built,(pack&&pack.theme)||{});
    built=wireTemplateLayoutForStep(built,(stepLike&&typeof stepLike==="object")?stepLike:{type:type},stepList||steps);
    if(pack&&pack.autoLinkCountdownPricing){
        built=autoLinkCountdownPricing(built);
    }
    built=normalizeTemplateLayout(built);
    var packBg=normalizeCanvasBgValue(pack&&pack.theme&&pack.theme.canvasBg);
    if(packBg)built=withCanvasBgInLayout(built,packBg);
    normalizeElementStyle(built);
    return {template:tpl,layout:built};
}
const funnelTemplatePacks=[
    {
        id:"pack_consulting_authority",
        name:"Consulting Authority",
        description:"Authority landing, application opt-in, high-ticket sales page, and premium checkout across the whole funnel.",
        tags:["All Pages","Consulting","Premium"],
        preview:"pricing",
        theme:{primary:"#1D4ED8",accent:"#C2410C",heading:"#172554",body:"#475569",surface:"#ffffff",soft:"#EEF4FF",border:"#D7E3FF",canvasBg:"#F4F8FF"},
        templates:{landing:"landing_authority",opt_in:"optin_application_call",sales:"sales_offer_stack",checkout:"checkout_premium",thank_you:"thankyou_call",custom:"custom_contact"}
    },
    {
        id:"pack_saas_launch",
        name:"SaaS Launch",
        description:"Modern app-style funnel for software launches with waitlist flow, comparison sales page, and polished handoff.",
        tags:["All Pages","SaaS","Launch"],
        preview:"cards",
        theme:{primary:"#0F766E",accent:"#14B8A6",heading:"#134E4A",body:"#52606D",surface:"#ffffff",soft:"#ECFEF8",border:"#BDEEE3",canvasBg:"#F2FFFB"},
        templates:{landing:"landing_app_showcase",opt_in:"optin_waitlist_launch",sales:"sales_comparison",checkout:"checkout_premium",thank_you:"thankyou_community",custom:"custom_feature_grid"}
    },
    {
        id:"pack_course_creator",
        name:"Course Creator",
        description:"Lead magnet or webinar into video sales letter, bundle checkout, and download-style thank-you flow.",
        tags:["All Pages","Course","Creator"],
        preview:"video",
        theme:{primary:"#2563EB",accent:"#F97316",heading:"#1E3A8A",body:"#64748B",surface:"#ffffff",soft:"#FFF7ED",border:"#FED7AA",canvasBg:"#FFF8F0"},
        templates:{landing:"landing_hero_launch",opt_in:"optin_webinar_signup",sales:"sales_video_letter",checkout:"checkout_bundle",thank_you:"thankyou_download",custom:"custom_hero_video"}
    },
    {
        id:"pack_workshop_event",
        name:"Workshop Event",
        description:"Registration-first funnel for live workshops and events with urgency, ticket checkout, and event confirmation page.",
        tags:["All Pages","Event","Workshop"],
        preview:"lead",
        theme:{primary:"#DC2626",accent:"#F59E0B",heading:"#7F1D1D",body:"#57534E",surface:"#ffffff",soft:"#FFF7ED",border:"#FECACA",canvasBg:"#FFF8F5"},
        templates:{landing:"landing_hero_launch",opt_in:"optin_webinar_signup",sales:"sales_offer_stack",checkout:"checkout_workshop_ticket",thank_you:"thankyou_event",custom:"custom_hero_video"}
    },
    {
        id:"pack_workshop_event_countdown_discount",
        name:"Workshop Event (Countdown Discount)",
        description:"Apply-to-all-pages funnel where the checkout countdown auto-updates pricing using sale vs regular price.",
        tags:["All Pages","Event","Workshop","Countdown"],
        preview:"pricing",
        autoLinkCountdownPricing:true,
        theme:{primary:"#DC2626",accent:"#F59E0B",heading:"#7F1D1D",body:"#57534E",surface:"#ffffff",soft:"#FFF7ED",border:"#FECACA",canvasBg:"#FFF8F5"},
        templates:{landing:"landing_hero_launch",opt_in:"optin_webinar_signup",sales:"sales_offer_stack",checkout:"checkout_workshop_ticket_discount",thank_you:"thankyou_event",custom:"custom_hero_video"}
    },
    {
        id:"pack_membership_growth",
        name:"Membership Growth",
        description:"Subscription-focused system with newsletter capture, plan comparison, membership checkout, and community thank-you page.",
        tags:["All Pages","Membership","Community"],
        preview:"pricing",
        theme:{primary:"#047857",accent:"#22C55E",heading:"#14532D",body:"#4B5563",surface:"#ffffff",soft:"#F0FDF4",border:"#BBF7D0",canvasBg:"#F5FFF8"},
        templates:{landing:"landing_feature_grid",opt_in:"optin_newsletter_digest",sales:"sales_comparison",checkout:"checkout_membership",thank_you:"thankyou_community",custom:"custom_pricing_faq"}
    },
    {
        id:"pack_agency_premium",
        name:"Agency Premium",
        description:"Service funnel with authority positioning, direct lead capture, story-led sales page, and premium checkout.",
        tags:["All Pages","Agency","Service"],
        preview:"hero",
        theme:{primary:"#111827",accent:"#D97706",heading:"#111827",body:"#4B5563",surface:"#ffffff",soft:"#FAF7F0",border:"#E5D3B3",canvasBg:"#FBF8F2"},
        templates:{landing:"landing_authority",opt_in:"optin_lead_capture",sales:"sales_story_close",checkout:"checkout_premium",thank_you:"thankyou_call",custom:"custom_contact"}
    },
    {
        id:"pack_digital_product",
        name:"Digital Product",
        description:"Straightforward product funnel with lead capture, classic pricing sales page, bundle checkout, and delivery thank-you.",
        tags:["All Pages","Digital","Product"],
        preview:"pricing",
        theme:{primary:"#0F766E",accent:"#EAB308",heading:"#134E4A",body:"#64748B",surface:"#ffffff",soft:"#F7FEE7",border:"#D9F99D",canvasBg:"#FBFFF2"},
        templates:{landing:"landing_feature_grid",opt_in:"optin_lead_capture",sales:"sales_pricing_faq",checkout:"checkout_bundle",thank_you:"thankyou_download",custom:"custom_feature_grid"}
    },
    {
        id:"pack_editorial_media",
        name:"Editorial Media",
        description:"Story-led funnel for newsletters, media brands, and editorial offers with softer conversion steps.",
        tags:["All Pages","Editorial","Newsletter"],
        preview:"banner",
        theme:{primary:"#1E40AF",accent:"#0EA5E9",heading:"#1E3A8A",body:"#475569",surface:"#ffffff",soft:"#EFF6FF",border:"#BFDBFE",canvasBg:"#F6FAFF"},
        templates:{landing:"landing_story_stats",opt_in:"optin_newsletter_digest",sales:"sales_story_close",checkout:"checkout_membership",thank_you:"thankyou_community",custom:"custom_contact"}
    },
    {
        id:"pack_high_ticket_closer",
        name:"High-Ticket Closer",
        description:"Application plus video-led close for premium offers, then a more formal checkout and call-confirmed thank-you.",
        tags:["All Pages","High Ticket","Closer"],
        preview:"video",
        theme:{primary:"#7C3AED",accent:"#EC4899",heading:"#4C1D95",body:"#5B5B77",surface:"#ffffff",soft:"#FAF5FF",border:"#E9D5FF",canvasBg:"#FCF7FF"},
        templates:{landing:"landing_authority",opt_in:"optin_application_call",sales:"sales_video_letter",checkout:"checkout_premium",thank_you:"thankyou_call",custom:"custom_contact"}
    },
    {
        id:"pack_community_starter",
        name:"Community Starter",
        description:"Warm, growth-focused funnel for memberships, communities, and audience-building offers.",
        tags:["All Pages","Community","Growth"],
        preview:"cards",
        theme:{primary:"#0369A1",accent:"#10B981",heading:"#0F172A",body:"#475569",surface:"#ffffff",soft:"#ECFEFF",border:"#A5F3FC",canvasBg:"#F2FEFF"},
        templates:{landing:"landing_app_showcase",opt_in:"optin_waitlist_launch",sales:"sales_video_letter",checkout:"checkout_membership",thank_you:"thankyou_community",custom:"custom_hero_video"}
    },
    {
        id:"pack_offer_ascension",
        name:"Offer Ascension",
        description:"Complete funnel system with checkout, premium upsell, lighter downsell, and a thank-you close for post-purchase monetization.",
        tags:["All Pages","Upsell","Downsell"],
        preview:"pricing",
        theme:{primary:"#0F766E",accent:"#2563EB",heading:"#083344",body:"#52606D",surface:"#ffffff",soft:"#F0FDFA",border:"#BAE6FD",canvasBg:"#F6FFFE"},
        templates:{landing:"landing_hero_launch",opt_in:"optin_lead_capture",sales:"sales_offer_stack",checkout:"checkout_bundle",upsell:"upsell_vip_upgrade",downsell:"downsell_lite_offer",thank_you:"thankyou_community",custom:"custom_pricing_faq"}
    }
];
function currentTemplateType(){
    var step=cur();
    return normalizeTemplateType((step&&step.type)||"landing");
}
function currentPageTemplates(){
    return builtInTemplatesByType[currentTemplateType()]||customTemplates;
}
function templatePreviewHtml(kind){
    if(kind==="lead"){
        return '<div class="tp-line tp-line-lg"></div><div class="tp-line tp-line-md"></div><div class="tp-form"></div><div class="tp-btn"></div>';
    }
    if(kind==="pricing"){
        return '<div class="tp-card"></div><div class="tp-line tp-line-md"></div><div class="tp-line tp-line-sm"></div>';
    }
    if(kind==="cards"){
        return '<div class="tp-card"></div><div class="tp-card" style="height:36px"></div><div class="tp-line tp-line-sm"></div>';
    }
    if(kind==="video"){
        return '<div class="tp-line tp-line-md"></div><div class="tp-img"></div><div class="tp-btn"></div>';
    }
    if(kind==="banner"){
        return '<div class="tp-line tp-line-lg"></div><div class="tp-line tp-line-sm"></div><div class="tp-btn"></div>';
    }
    return '<div class="tp-line tp-line-lg"></div><div class="tp-line tp-line-md"></div><div class="tp-btn"></div><div class="tp-img"></div>';
}
function confirmTemplateApply(message){
    var modal=document.getElementById("fbTemplateConfirm");
    var desc=document.getElementById("fbTemplateConfirmDesc");
    var btnOk=document.getElementById("fbTemplateConfirmOk");
    var btnCancel=document.getElementById("fbTemplateConfirmCancel");
    if(!modal||!desc||!btnOk||!btnCancel){
        return Promise.resolve(window.confirm(message));
    }
    desc.textContent=message;
    modal.classList.add("open");
    modal.setAttribute("aria-hidden","false");
    return new Promise(function(resolve){
        function cleanup(result){
            modal.classList.remove("open");
            modal.setAttribute("aria-hidden","true");
            btnOk.removeEventListener("click",onOk);
            btnCancel.removeEventListener("click",onCancel);
            modal.removeEventListener("click",onBackdrop);
            document.removeEventListener("keydown",onKey);
            resolve(result);
        }
        function onOk(){cleanup(true);}
        function onCancel(){cleanup(false);}
        function onBackdrop(e){if(e.target===modal)cleanup(false);}
        function onKey(e){
            var k=String(e.key||"").toLowerCase();
            if(k==="escape")cleanup(false);
        }
        btnOk.addEventListener("click",onOk);
        btnCancel.addEventListener("click",onCancel);
        modal.addEventListener("click",onBackdrop);
        document.addEventListener("keydown",onKey);
    });
}
function bindSharedTemplateEditModal(){
    var modal=document.getElementById("fbSharedTemplateEditModal");
    var btnCancel=document.getElementById("fbSharedTemplateEditCancel");
    var btnSave=document.getElementById("fbSharedTemplateEditSave");
    if(!modal||!btnCancel||!btnSave)return;
    btnCancel.addEventListener("click",closeSharedTemplateEditModal);
    btnSave.addEventListener("click",saveSharedTemplateEdit);
    modal.addEventListener("click",function(e){
        if(e.target===modal)closeSharedTemplateEditModal();
    });
    document.addEventListener("keydown",function(e){
        var k=String(e.key||"").toLowerCase();
        if(k==="escape"&&modal.classList.contains("open"))closeSharedTemplateEditModal();
    });
}
function applyPageTemplate(tpl){
    var s=cur();
    if(!s||!tpl||typeof tpl.build!=="function")return;
    saveToHistory();
    var layout=tpl.build();
    if(state.layout&&state.layout.__editor&&state.layout.__editor.canvasBg&&!(layout&&layout.__editor&&layout.__editor.canvasBg)){
        layout=withCanvasBgInLayout(layout,state.layout.__editor.canvasBg);
    }
    layout=wireTemplateLayoutForStep(layout,s,steps);
    layout=normalizeTemplateLayout(layout);
    var derivedCheckoutAmount=normalizeTemplateType((s&&s.type)||"")==="checkout"?derivePrimaryPricingAmountFromLayout(layout):null;
    if(derivedCheckoutAmount!==null&&derivedCheckoutAmount>0){
        s.price=derivedCheckoutAmount;
    }
    state.sel=null;
    state.carouselSel=null;
    state.editingEl=null;
    state.linkPick=null;
    state.layout=layout;
    normalizeElementStyle(state.layout);
    s.layout_json=clone(state.layout);
    s.background_color=(state.layout&&state.layout.__editor&&state.layout.__editor.canvasBg)?String(state.layout.__editor.canvasBg):"";
    s.template=String(tpl.id||"simple");
    applyCanvasBgPreference();
    syncCanvasBgControls();
    render();
    queueAutoSave();
    if(saveMsg)saveMsg.textContent="Template applied and call to actions auto-connected. Not saved yet.";
}
var templateLibraryMode="shared";
const funnelPackStepOrder=["landing","opt_in","sales","checkout","upsell","downsell","thank_you"];
function setTemplateLibraryMode(mode){
    templateLibraryMode="shared";
    renderTemplateLibrary();
}
function funnelPackManagedTypes(pack){
    var templates=(pack&&pack.templates&&typeof pack.templates==="object")?pack.templates:{};
    return funnelPackStepOrder.filter(function(type){
        return !!templates[type];
    });
}
function createStepForPack(type){
    var normalizedType=String(type||"custom").toLowerCase();
    var title=defaultStepTitleForType(normalizedType);
    var slug=uniqueStepSlug(slugifyPage(title));
    return requestJson(stepStoreUrl,"POST",{
        title:title,
        slug:slug,
        type:normalizedType
    }).then(function(resp){
        var created=applyStepUpdate((resp&&resp.step)||resp||{});
        return created;
    });
}
function desiredOrderIdsForPack(pack){
    sortStepsByPosition();
    var managedTypes=funnelPackManagedTypes(pack);
    var managedSet={};
    managedTypes.forEach(function(type){managedSet[type]=true;});
    var ordered=[];
    managedTypes.forEach(function(type){
        steps
            .filter(function(step){return String((step&&step.type)||"").toLowerCase()===type;})
            .sort(function(a,b){return Number(a.position||0)-Number(b.position||0);})
            .forEach(function(step){ordered.push(Number(step.id));});
    });
    steps
        .filter(function(step){return !managedSet[String((step&&step.type)||"").toLowerCase()];})
        .sort(function(a,b){return Number(a.position||0)-Number(b.position||0);})
        .forEach(function(step){ordered.push(Number(step.id));});
    return ordered;
}
function ensurePackStepsExist(pack){
    var managedTypes=funnelPackManagedTypes(pack);
    var sequence=Promise.resolve([]);
    managedTypes.forEach(function(type){
        sequence=sequence.then(function(createdTypes){
            var exists=steps.some(function(step){
                return String((step&&step.type)||"").toLowerCase()===type;
            });
            if(exists)return createdTypes;
            return createStepForPack(type).then(function(){
                createdTypes.push(type);
                return createdTypes;
            });
        });
    });
    return sequence.then(function(createdTypes){
        var desiredIds=desiredOrderIdsForPack(pack);
        var currentIds=orderedStepIdsWithPositions();
        var needsReorder=desiredIds.length===currentIds.length && desiredIds.some(function(id,idx){
            return Number(id)!==Number(currentIds[idx]);
        });
        var reorderPromise=needsReorder ? persistStepOrder(desiredIds) : Promise.resolve();
        return reorderPromise.then(function(){
            renderStepOptions();
            syncPageManagerList();
            return createdTypes;
        });
    });
}
function applyFunnelTemplatePack(pack){
    if(!pack||!steps.length)return;
    var current=cur();
    var managedTypes=funnelPackManagedTypes(pack);
    var missingTypes=managedTypes.filter(function(type){
        return !steps.some(function(step){
            return String((step&&step.type)||"").toLowerCase()===type;
        });
    });
    var pageCount=steps.length;
    var msg='Apply the "'+String(pack.name||"funnel pack")+'" funnel pack to all '+pageCount+' pages? This will replace every page layout in the funnel.';
    if(missingTypes.length){
        msg+=' Missing pages will also be created: '+missingTypes.map(pageTypeLabel).join(", ")+'.';
    }
    confirmTemplateApply(msg).then(function(ok){
        if(!ok)return;
        saveToHistory();
        return ensurePackStepsExist(pack).then(function(createdTypes){
            var currentStepId=current?+current.id:null;
            var nextStateLayout=null;
            steps.forEach(function(step){
                if(!step)return;
                var built=buildPackLayout(pack,step,steps);
                step.layout_json=clone(built.layout);
                step.background_color=(built.layout&&built.layout.__editor&&built.layout.__editor.canvasBg)?String(built.layout.__editor.canvasBg):"";
                step.template=String(pack.id||"funnel_pack")+"__"+String((built.template&&built.template.id)||normalizeTemplateType(step.type));
                if(normalizeTemplateType(step.type)==="checkout"){
                    var packCheckoutAmount=derivePrimaryPricingAmountFromLayout(built.layout);
                    if(packCheckoutAmount!==null&&packCheckoutAmount>0){
                        step.price=packCheckoutAmount;
                    }
                }
                if(currentStepId!==null&&+step.id===currentStepId){
                    nextStateLayout=clone(built.layout);
                }
            });
            state.sel=null;
            state.carouselSel=null;
            state.editingEl=null;
            state.linkPick=null;
            if(nextStateLayout)state.layout=nextStateLayout;
            applyCanvasBgPreference();
            syncCanvasBgControls();
            renderTemplateLibrary();
            render();
            return persistStepDefinitions(steps).then(function(){
                return persistLayoutsForSteps(steps,true);
            }).then(function(){
                if(saveMsg){
                    saveMsg.textContent=createdTypes.length
                        ? "Funnel pack saved on all "+steps.length+" page(s); created: "+createdTypes.map(pageTypeLabel).join(", ")+"."
                        : "Funnel pack saved on all "+steps.length+" page(s).";
                }
            });
        }).catch(function(err){
            showBuilderToast((err&&err.message)||"Failed to apply funnel pack.","error");
        });
    });
}
function sharedTemplateSteps(template){
    return ((template&&template.steps)||[]).slice().sort(function(a,b){
        return Number(a&&a.position||0)-Number(b&&b.position||0);
    });
}
function replaceSharedTemplateSnapshot(template){
    if(!template||!template.template_id)return;
    var replaced=false;
    sharedFunnelTemplates=(sharedFunnelTemplates||[]).map(function(item){
        if(String(item&&item.template_id||"")===String(template.template_id||"")){
            replaced=true;
            return template;
        }
        return item;
    });
    if(!replaced){
        sharedFunnelTemplates.push(template);
    }
}
function fetchLatestSharedTemplate(templateId){
    var id=String(templateId||"");
    var fallback=(sharedFunnelTemplates||[]).find(function(item){return String(item&&item.template_id||"")===id;})||null;
    if(!sharedTemplatesUrl)return Promise.resolve(fallback);
    return requestGetJson(sharedTemplatesUrl).then(function(resp){
        var templates=(resp&&Array.isArray(resp.templates))?resp.templates:[];
        if(templates.length){
            sharedFunnelTemplates=templates;
        }
        return templates.find(function(item){return String(item&&item.template_id||"")===id;})||fallback;
    }).catch(function(){
        return fallback;
    });
}
function ensureSharedTemplateStepCount(template){
    var tplSteps=sharedTemplateSteps(template);
    var need=Math.max(0,tplSteps.length-steps.length);
    var existingStepCount=steps.length;
    var seq=Promise.resolve([]);
    for(var i=0;i<need;i++){
        (function(idx){
            seq=seq.then(function(created){
                var source=tplSteps[existingStepCount+idx]||tplSteps[idx]||{};
                return createStepForPack(String(source.type||"custom")).then(function(){
                    created.push(String(source.type||"custom"));
                    return created;
                });
            });
        })(i);
    }
    return seq.then(function(createdTypes){
        renderStepOptions();
        syncPageManagerList();
        return createdTypes;
    });
}
function applySharedFunnelTemplate(template){
    if(!template)return Promise.resolve(false);
    replaceSharedTemplateSnapshot(template);
    var loadPromise=Promise.resolve(template);
    if(template.template_id&&sharedTemplatesUrl){
        loadPromise=fetchLatestSharedTemplate(template.template_id).then(function(latest){
            return latest||template;
        });
    }
    return loadPromise.then(function(resolved){
        template=resolved||template;
        replaceSharedTemplateSnapshot(template);
        var tplSteps=sharedTemplateSteps(template);
        if(!tplSteps.length){
            showBuilderToast("This saved template has no pages to apply.","error");
            return Promise.resolve(false);
        }
        var msg='Apply the saved template "'+String(template.name||"template")+'" to this funnel? This will replace the layouts of the first '+tplSteps.length+' page'+(tplSteps.length===1?"":"s")+' and save them to the server.';
        if(steps.length>tplSteps.length){
            msg+=' Extra pages in the current funnel will stay after the applied template pages.';
        }
        return confirmTemplateApply(msg).then(function(ok){
            if(!ok)return Promise.resolve(false);
            saveToHistory();
            return ensureSharedTemplateStepCount(template).then(function(createdTypes){
                sortStepsByPosition();
                var ordered=steps.slice().sort(function(a,b){return Number(a.position||0)-Number(b.position||0);});
                var currentStepId=cur()?+cur().id:null;
                var nextStateLayout=null;
                var appliedIds={};
                tplSteps.forEach(function(sourceStep,idx){
                    var targetStep=ordered[idx]||null;
                    if(!targetStep)return;
                    appliedIds[String(targetStep.id||"")]=true;
                    targetStep.position=idx+1;
                    targetStep.title=String(sourceStep.title||defaultStepTitleForType(sourceStep.type)||"Untitled");
                    targetStep.subtitle=String(sourceStep.subtitle||"");
                    targetStep.slug=uniqueStepSlug(sourceStep.slug||targetStep.title,targetStep.id);
                    targetStep.type=String(sourceStep.type||"custom");
                    targetStep.template=String(sourceStep.template||"simple");
                    targetStep.template_data=clone(sourceStep.template_data||null);
                    targetStep.layout_style=String(sourceStep.layout_style||"centered");
                    targetStep.content=String(sourceStep.content||"");
                    targetStep.cta_label=String(sourceStep.cta_label||"");
                    targetStep.price=(sourceStep.price==null||String(sourceStep.price)==="")?"":String(sourceStep.price);
                    targetStep.background_color=String(sourceStep.background_color||"");
                    targetStep.button_color=String(sourceStep.button_color||"");
                    targetStep.is_active=!(sourceStep.is_active===false||String(sourceStep.is_active)==="0");
                    targetStep.step_tags=Array.isArray(sourceStep.step_tags)?clone(sourceStep.step_tags):[];
                    targetStep.layout_json=clone(sourceStep.layout_json||{root:[],sections:[]});
                });
                var extraPosition=tplSteps.length+1;
                ordered.forEach(function(step,idx){
                    if(appliedIds[String(step&&step.id||"")])return;
                    step.position=extraPosition;
                    extraPosition++;
                });
                sortStepsByPosition();
                steps.forEach(function(step){
                    step.layout_json=wireTemplateLayoutForStep(
                        clone(step.layout_json||{root:[],sections:[]}),
                        step,
                        steps
                    );
                    if(currentStepId!==null&&+step.id===currentStepId){
                        nextStateLayout=clone(step.layout_json||{root:[],sections:[]});
                    }
                });
                var appliedSteps=ordered.filter(function(step){
                    return !!appliedIds[String(step&&step.id||"")];
                });
                return persistStepDefinitions(ordered).then(function(){
                    return persistStepOrder(orderedStepIdsWithPositions());
                }).then(function(){
                    return persistLayoutsForSteps(ordered,true);
                }).then(function(){
                    renderStepOptions();
                    syncPageManagerList();
                    if(nextStateLayout)state.layout=nextStateLayout;
                    state.sel=null;
                    state.carouselSel=null;
                    state.editingEl=null;
                    state.linkPick=null;
                    renderTemplateLibrary();
                    render();
                    return syncBuilderPurposeFromTemplate(template).catch(function(err){
                        showBuilderToast((err&&err.message)||"Template applied, but funnel purpose could not be updated.","error");
                        return builderPurpose;
                    }).then(function(){
                        if(saveMsg){
                            saveMsg.textContent=createdTypes.length
                                ? 'Saved template applied to '+appliedSteps.length+' page(s); created: '+createdTypes.map(pageTypeLabel).join(", ")+'.'
                                : 'Saved template applied to all '+appliedSteps.length+' page(s).';
                        }
                        return true;
                    });
                });
            }).catch(function(err){
                showBuilderToast((err&&err.message)||"Failed to apply saved template.","error");
                throw err;
            });
        });
    });
}
function editSharedTemplateDescription(template){
    if(!template||!template.update_url){
        showBuilderToast("Template description can't be edited here.","error");
        return;
    }
    openSharedTemplateEditModal(template);
}
var sharedTemplateEditState={template:null};
function sharedTemplateTagsInputValue(template){
    return (template&&Array.isArray(template.tags)?template.tags:[]).map(function(tag){
        return String(tag||"").trim();
    }).filter(Boolean).join(", ");
}
function openSharedTemplateEditModal(template){
    var modal=document.getElementById("fbSharedTemplateEditModal");
    var desc=document.getElementById("fbSharedTemplateEditDescription");
    var tags=document.getElementById("fbSharedTemplateEditTags");
    var title=document.getElementById("fbSharedTemplateEditTitle");
    var saveBtn=document.getElementById("fbSharedTemplateEditSave");
    if(!modal||!desc||!tags||!saveBtn)return;
    sharedTemplateEditState.template=template||null;
    if(title)title.textContent='Edit "'+String((template&&template.name)||"Saved Template")+'"';
    desc.value=String((template&&template.description)||"");
    tags.value=sharedTemplateTagsInputValue(template);
    saveBtn.disabled=false;
    modal.classList.add("open");
    modal.setAttribute("aria-hidden","false");
    setTimeout(function(){ desc.focus(); desc.select(); },10);
}
function closeSharedTemplateEditModal(){
    var modal=document.getElementById("fbSharedTemplateEditModal");
    if(!modal)return;
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden","true");
    sharedTemplateEditState.template=null;
}
function parseSharedTemplateTags(raw){
    return String(raw||"").split(",").map(function(tag){
        return String(tag||"").trim();
    }).filter(Boolean).slice(0,6);
}
function saveSharedTemplateEdit(){
    var template=sharedTemplateEditState.template;
    var desc=document.getElementById("fbSharedTemplateEditDescription");
    var tags=document.getElementById("fbSharedTemplateEditTags");
    var saveBtn=document.getElementById("fbSharedTemplateEditSave");
    if(!template||!template.update_url||!desc||!tags||!saveBtn)return;
    var nextDescription=String(desc.value||"").trim();
    var nextTags=parseSharedTemplateTags(tags.value);
    saveBtn.disabled=true;
    requestJson(String(template.update_url),"PUT",{
        name:String(template.name||"Untitled Template").trim()||"Untitled Template",
        description:nextDescription,
        status:String(template.status||"draft").trim()||"draft",
        template_tags:nextTags
    }).then(function(){
        template.description=nextDescription;
        template.tags=nextTags;
        closeSharedTemplateEditModal();
        renderTemplateLibrary();
        showBuilderToast("Template card updated.","success");
    }).catch(function(err){
        saveBtn.disabled=false;
        showBuilderToast((err&&err.message)||"Failed to update template card.","error");
    });
}
function renderTemplateLibrary(){
    var grid=document.getElementById("fbTemplateGrid");
    var funnelGrid=document.getElementById("fbFunnelTemplateGrid");
    var pagePane=document.getElementById("fbTemplatePagePane");
    var funnelPane=document.getElementById("fbTemplateFunnelPane");
    var pageModeBtn=document.getElementById("fbTemplateModePage");
    var funnelModeBtn=document.getElementById("fbTemplateModeFunnel");
    var titleEl=document.getElementById("fbTemplateHeading");
    var metaEl=document.getElementById("fbTemplateMeta");
    var typePill=document.getElementById("fbTemplateTypePill");
    var countPill=document.getElementById("fbTemplateCountPill");
    var currentPageEl=document.getElementById("fbTemplateCurrentPage");
    var step=cur();
    var pageType=currentTemplateType();
    var templates=currentPageTemplates();
    var pageLabel=pageTypeLabel(pageType);
    var mode="shared";
    var allFunnelTemplates=(sharedFunnelTemplates||[]);
    var matchingFunnelTemplates=allFunnelTemplates.filter(function(template){
        return normalizeBuilderPurpose((template&&template.funnel_purpose)||(template&&template.template_type)||"service")===builderPurpose;
    });
    var purposeLabels={
        service:"Service Funnel",
        single_page:"Single Page Funnel",
        physical_product:"Physical Product Funnel"
    };
    var activePurposeLabel=purposeLabels[builderPurpose]||"Service Funnel";
    if(pageModeBtn){
        pageModeBtn.classList.toggle("active",mode==="page");
        pageModeBtn.setAttribute("aria-pressed",mode==="page"?"true":"false");
        pageModeBtn.onclick=function(){setTemplateLibraryMode("page");};
    }
    if(funnelModeBtn){
        funnelModeBtn.classList.toggle("active",mode==="funnel");
        funnelModeBtn.setAttribute("aria-pressed",mode==="funnel"?"true":"false");
        funnelModeBtn.onclick=function(){setTemplateLibraryMode("funnel");};
    }
    if(pagePane)pagePane.classList.toggle("hidden",mode!=="page");
    if(funnelPane)funnelPane.classList.toggle("hidden",mode==="page");
    if(titleEl)titleEl.textContent=activePurposeLabel+" Templates";
    if(metaEl)metaEl.textContent="Showing "+matchingFunnelTemplates.length+" saved super-admin template"+(matchingFunnelTemplates.length===1?"":"s")+" for "+activePurposeLabel+". Applying one replaces the layouts across all pages in this funnel.";
    if(typePill)typePill.textContent=activePurposeLabel;
    if(countPill)countPill.textContent=String(matchingFunnelTemplates.length)+" template"+(matchingFunnelTemplates.length===1?"":"s");
    if(currentPageEl)currentPageEl.textContent="Choose one "+activePurposeLabel.toLowerCase()+" template to update all "+steps.length+" page"+(steps.length===1?"":"s")+" in this funnel.";
    if(grid){
        grid.innerHTML="";
    }
    if(funnelGrid){
        var sharedCards=matchingFunnelTemplates.map(function(template){
            var tags=(template.tags||[]).map(function(tag){return '<span class="fb-template-tag">'+String(tag)+'</span>';}).join("");
            var editBtn=template.update_url
                ? '<button type="button" class="fb-btn" data-edit-shared-template-id="'+String(template.template_id||"")+'" style="font-size:12px;padding:8px 10px;">Edit Card</button>'
                : '';
            return '<div class="fb-template-card">'
                +'<div class="fb-template-preview">'+templatePreviewHtml(template.preview)+'</div>'
                +'<div class="fb-template-card-meta">'
                +'<p class="fb-template-title">'+String(template.name||"Saved Template")+'</p>'
                +'<p class="fb-template-desc">'+String(template.description||"")+'</p>'
                +'<p class="fb-template-desc" style="margin-top:6px;font-size:12px;color:#64748b;">Applies the saved super-admin template across '+String(sharedTemplateSteps(template).length||0)+' page(s) in this funnel.</p>'
                +'</div>'
                +'<div class="fb-template-actions">'
                +'<div class="fb-template-tags">'+tags+'</div>'
                +'<div class="fb-template-card-controls">'
                +editBtn
                +'<button type="button" class="fb-btn primary" data-shared-template-id="'+String(template.template_id||"")+'">Apply To All Pages</button>'
                +'</div>'
                +'</div>'
                +'</div>';
        }).join("");
        funnelGrid.innerHTML=sharedCards || '<div class="fb-template-card"><div><p class="fb-template-title">No matching templates</p><p class="fb-template-desc">There are no saved super-admin templates yet for '+activePurposeLabel+'. Create or publish one in that category so AO sees only relevant templates here.</p></div></div>';
        funnelGrid.querySelectorAll("[data-shared-template-id]").forEach(function(btn){
            btn.addEventListener("click",function(){
                if(btn.disabled)return;
                var id=String(btn.getAttribute("data-shared-template-id")||"");
                var originalLabel=btn.innerHTML;
                btn.disabled=true;
                btn.textContent="Applying...";
                fetchLatestSharedTemplate(id).then(function(template){
                    if(!template){
                        throw new Error("Saved template could not be loaded.");
                    }
                    return applySharedFunnelTemplate(template);
                }).catch(function(err){
                    showBuilderToast((err&&err.message)||"Failed to load the latest template.","error");
                }).finally(function(){
                    btn.disabled=false;
                    btn.innerHTML=originalLabel;
                });
            });
        });
        funnelGrid.querySelectorAll("[data-edit-shared-template-id]").forEach(function(btn){
            btn.addEventListener("click",function(){
                var id=String(btn.getAttribute("data-edit-shared-template-id")||"");
                var template=(sharedFunnelTemplates||[]).find(function(item){return String(item.template_id||"")===id;});
                if(!template)return;
                editSharedTemplateDescription(template);
            });
        });
    }
}
const componentTemplates=[
    {id:"hero_split_section",name:"Hero Split",kind:"section",build:function(){
        var leftCol=makeColumn([
            makeEl("heading","Launch your new offer",{fontSize:"38px",color:"#240E35",fontWeight:"800",margin:"0 0 12px",lineHeight:"1.2"},{}),
            makeEl("text","Explain the value in one short paragraph and add a bold call to action.",{fontSize:"16px",color:"#64748b",lineHeight:"1.6",margin:"0 0 18px"},{}),
            makeEl("button","Get Started",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"12px 22px",fontWeight:"700"},{actionType:"next_step",actionStepSlug:"",link:"#",alignment:"left"})
        ],{flex:"1"});
        var rightCol=makeColumn([
            makeEl("image","",{width:"100%"},{src:"",alt:"Hero image",alignment:"center"})
        ],{flex:"1"});
        return makeSection({style:{padding:"72px 24px",backgroundColor:"#ffffff"},rows:[makeRow([leftCol,rightCol],{gap:"24px",alignItems:"center"})]});
    }},
    {id:"feature_cards",name:"Feature Cards",kind:"section",build:function(){
        return makeSection({
            style:{padding:"56px 24px",backgroundColor:"#ffffff"},
            settings:{contentWidth:"wide"},
            rows:[makeRow([
                makeFeatureCardColumn("bolt","Fast setup","Launch quickly with prebuilt blocks and layouts."),
                makeFeatureCardColumn("star","Custom styles","Make every section match your brand."),
                makeFeatureCardColumn("chart-line","Track results","See what converts and optimize instantly.")
            ],{gap:"18px"})]
        });
    }},
    {id:"logo_strip",name:"Logo Strip",kind:"section",build:function(){
        return makeSection({
            style:{padding:"32px 24px",backgroundColor:"#ffffff"},
            settings:{contentWidth:"wide"},
            rows:[makeRow([
                makeLogoColumn("Northwind"),
                makeLogoColumn("Boreal"),
                makeLogoColumn("Summit"),
                makeLogoColumn("Everest"),
                makeLogoColumn("Aurora")
            ],{gap:"10px",alignItems:"center"})]
        });
    }},
    {id:"cta_banner",name:"Call to Actions Banner",kind:"section",build:function(){
        var leftCol=makeColumn([
            makeEl("heading","Ready to launch today?",{fontSize:"26px",color:"#240E35",fontWeight:"800",margin:"0 0 6px"},{}),
            makeEl("text","Start with a template and personalize it in minutes.",{fontSize:"14px",color:"#475569",margin:"0"},{})
        ],{flex:"1"});
        var rightCol=makeColumn([
            makeEl("button","Start Now",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"10px 20px",fontWeight:"700"},{actionType:"next_step",actionStepSlug:"",link:"#",alignment:"right"})
        ],{flex:"0 0 auto",textAlign:"right"});
        return makeSection({
            style:{padding:"28px 24px",backgroundColor:"#F8F5FB",border:"1px solid #E6E1EF",borderRadius:"18px"},
            settings:{contentWidth:"wide"},
            rows:[makeRow([leftCol,rightCol],{gap:"16px",alignItems:"center"})]
        });
    }},
    {id:"pricing_trio",name:"Pricing Trio",kind:"section",build:function(){
        return makeSection({
            style:{padding:"56px 24px",backgroundColor:"#ffffff"},
            settings:{contentWidth:"wide"},
            rows:[makeRow([
                makeColumn([makeEl("pricing","",{width:"100%"},{plan:"Starter",price:"19",period:"/month",subtitle:"For new teams",features:["1 funnel","Basic support","Email capture"],ctaLabel:"Choose Starter",ctaLink:"#",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:""})],{flex:"1"}),
                makeColumn([makeEl("pricing","",{width:"100%"},{plan:"Growth",price:"49",period:"/month",subtitle:"For growing teams",features:["Unlimited funnels","Custom domains","Priority support"],ctaLabel:"Choose Growth",ctaLink:"#",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:"Popular"})],{flex:"1"}),
                makeColumn([makeEl("pricing","",{width:"100%"},{plan:"Scale",price:"99",period:"/month",subtitle:"For scale-ups",features:["Advanced analytics","SLA support","Custom onboarding"],ctaLabel:"Choose Scale",ctaLink:"#",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:""})],{flex:"1"})
            ],{gap:"18px",alignItems:"stretch"})]
        });
    }},
    {id:"testimonials_row",name:"Testimonials Row",kind:"section",build:function(){
        return makeSection({
            style:{padding:"56px 24px",backgroundColor:"#ffffff"},
            settings:{contentWidth:"wide"},
            rows:[makeRow([
                makeColumn([makeEl("testimonial","",{width:"100%"},{quote:"Our conversions doubled in one week.",name:"Chris Park",role:"Founder, Rally",avatar:""})],{flex:"1"}),
                makeColumn([makeEl("testimonial","",{width:"100%"},{quote:"Best builder we have used so far.",name:"Sofia Tran",role:"Marketing Lead",avatar:""})],{flex:"1"})
            ],{gap:"18px"})]
        });
    }},
    {id:"faq_stack",name:"FAQ Stack",kind:"section",build:function(){
        return makeSection({
            style:{padding:"56px 24px",backgroundColor:"#ffffff"},
            settings:{contentWidth:"medium"},
            rows:[makeRow([makeColumn([
                makeEl("heading","Frequently asked questions",{fontSize:"28px",color:"#240E35",fontWeight:"800",textAlign:"center",margin:"0 0 10px"},{}),
                makeEl("faq","",{width:"100%"},{items:[{q:"Can I cancel anytime?",a:"Yes, cancel anytime from your account settings."},{q:"Do you offer a free trial?",a:"Yes, you can try it free for 14 days."},{q:"Do templates work on mobile?",a:"Yes, everything is responsive out of the box."}],itemGap:10,questionColor:"#240E35",answerColor:"#475569"})
            ],{textAlign:"center"})],{gap:"12px"})]
        });
    }},
    {id:"stats_row",name:"Stats Row",kind:"row",build:function(){
        return makeRow([
            makeStatColumn("98%","Satisfaction"),
            makeStatColumn("3x","Speed"),
            makeStatColumn("45k","Leads")
        ],{gap:"12px",alignItems:"center"});
    }},
    {id:"text_media_row",name:"Text + Image Row",kind:"row",build:function(){
        return makeRow([
            makeColumn([
                makeEl("heading","Explain the value",{fontSize:"26px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
                makeEl("text","Use this row to tell a short story and point to a visual.",{fontSize:"15px",color:"#64748b",lineHeight:"1.6",margin:"0 0 16px"},{}),
                makeEl("button","Learn More",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"10px 20px",fontWeight:"700"},{actionType:"link",link:"#",alignment:"left"})
            ],{flex:"1"}),
            makeColumn([
                makeEl("image","",{width:"100%"},{src:"",alt:"Feature image",alignment:"center"})
            ],{flex:"1"})
        ],{gap:"24px",alignItems:"center"});
    }},
    {id:"media_text_row",name:"Image + Text Row",kind:"row",build:function(){
        return makeRow([
            makeColumn([
                makeEl("image","",{width:"100%"},{src:"",alt:"Feature image",alignment:"center"})
            ],{flex:"1"}),
            makeColumn([
                makeEl("heading","Show the product",{fontSize:"26px",color:"#240E35",fontWeight:"800",margin:"0 0 10px"},{}),
                makeEl("text","Pair visuals with a short explanation to build trust fast.",{fontSize:"15px",color:"#64748b",lineHeight:"1.6",margin:"0 0 16px"},{}),
                makeEl("button","See Details",{backgroundColor:"#240E35",color:"#ffffff",borderRadius:"999px",padding:"10px 20px",fontWeight:"700"},{actionType:"link",link:"#",alignment:"left"})
            ],{flex:"1"})
        ],{gap:"24px",alignItems:"center"});
    }}
];
function applyComponentTemplate(id){
    var tpl=componentTemplates.find(function(t){return t.id===id;});
    if(!tpl||typeof tpl.build!=="function")return;
    saveToHistory();
    ensureRootModel();
    var did=false;
    if(tpl.kind==="section"){
        var section=tpl.build();
        if(!section)return;
        var rs=rootItems();
        var insertIdx=rs.length;
        if(state.sel&&state.sel.s){
            var ctx=sectionRootContext(state.sel.s);
            if(ctx&&ctx.index>=0)insertIdx=ctx.index+1;
        }
        rs.splice(Math.max(0,Math.min(insertIdx,rs.length)),0,Object.assign({kind:"section"},section));
        syncSectionsFromRoot();
        state.sel={k:"sec",s:section.id};
        did=true;
    }else if(tpl.kind==="row"){
        var s=(state.sel&&state.sel.s)?sec(state.sel.s):((state.layout.sections||[])[0]||null);
        if(!s||s.__freeformCanvas){
            var wrapSection=makeSection({style:{padding:"56px 24px",backgroundColor:"#ffffff"},settings:{contentWidth:"wide"},rows:[tpl.build()]});
            var rs2=rootItems();
            var insertIdx2=rs2.length;
            if(state.sel&&state.sel.s){
                var ctx2=sectionRootContext(state.sel.s);
                if(ctx2&&ctx2.index>=0)insertIdx2=ctx2.index+1;
            }
            rs2.splice(Math.max(0,Math.min(insertIdx2,rs2.length)),0,Object.assign({kind:"section"},wrapSection));
            syncSectionsFromRoot();
            state.sel={k:"sec",s:wrapSection.id};
            did=true;
        }else{
            s.rows=Array.isArray(s.rows)?s.rows:[];
            var rowObj=tpl.build();
            var rowIdx=s.rows.length;
            if(state.sel&&state.sel.r){
                var ri=s.rows.findIndex(function(r){return String(r.id||"")===String(state.sel.r||"");});
                if(ri>=0)rowIdx=ri+1;
            }
            s.rows.splice(Math.max(0,Math.min(rowIdx,s.rows.length)),0,rowObj);
            state.sel={k:"row",s:s.id,r:rowObj.id};
            did=true;
        }
    }
    if(did){
        render();
        queueAutoSave();
        if(saveMsg)saveMsg.textContent="Template added. Not saved yet.";
    }
}
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
    if(t==="upsell")return "Upsell";
    if(t==="downsell")return "Downsell";
    if(t==="sales")return "Sales";
    if(t==="landing")return "Landing";
    if(t==="custom")return "Custom";
    return titleCase(t);
}
function stepsOfType(type,excludeStepId){
    var wanted=String(type||"").toLowerCase();
    return steps.filter(function(step){
        if(excludeStepId!=null && +step.id===+excludeStepId)return false;
        return String(step&&step.type||"").toLowerCase()===wanted;
    }).sort(function(a,b){
        var ap=Number(a&&a.position)||0;
        var bp=Number(b&&b.position)||0;
        if(ap!==bp)return ap-bp;
        return (Number(a&&a.id)||0)-(Number(b&&b.id)||0);
    });
}
function repeatedTypeIndex(step){
    if(!step)return 1;
    var sameType=stepsOfType(step.type);
    var idx=sameType.findIndex(function(item){return +item.id===+step.id;});
    return idx>=0?idx+1:Math.max(1,sameType.length);
}
function numberedPageTypeLabel(type,index,total){
    var base=pageTypeLabel(type);
    var count=Math.max(0,Number(total||0));
    var order=Math.max(1,Number(index||1));
    if(count<=1)return base;
    return base+" "+order;
}
function suggestedStepTitleForType(type,excludeStepId){
    var sameType=stepsOfType(type,excludeStepId);
    return numberedPageTypeLabel(type,sameType.length+1,sameType.length+1);
}
function pageDisplayLabel(step){
    if(!step)return "Untitled";
    var sameType=stepsOfType(step.type);
    var fallbackLabel=numberedPageTypeLabel(step.type,repeatedTypeIndex(step),sameType.length);
    var stepTitle=String(step.title||"").trim();
    if(stepTitle==="")return fallbackLabel;
    return stepTitle+" ("+fallbackLabel+")";
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
function requestGetJson(url){
    return fetch(url,{
        method:"GET",
        headers:{
            "X-CSRF-TOKEN":csrf,
            "Accept":"application/json"
        }
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
            if(typeof s.ctaLink==="string"&&s.ctaLink.trim()!=="")out.push(s.ctaLink.trim());
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
function persistStepDefinition(stepLike){
    var s=stepLike||{};
    if(!s.id)return Promise.resolve(null);
    return requestJson(stepUrlFromTpl(stepUpdateTpl,s.id),"PUT",buildStepPayload(s)).then(function(resp){
        return applyStepUpdate((resp&&resp.step)||s);
    });
}
function persistStepDefinitions(stepList){
    var queue=Promise.resolve([]);
    (Array.isArray(stepList)?stepList:[]).forEach(function(step){
        queue=queue.then(function(saved){
            return persistStepDefinition(step).then(function(updated){
                if(updated)saved.push(updated);
                return saved;
            });
        });
    });
    return queue;
}
/** Persist layout_json + background via builder save endpoint (PUT updateStep ignores layout). */
function postStepLayoutSave(stepId,layoutJson,bgColor,skipRevision){
    var requestHeaders={"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"};
    var bg=bgColor;
    if(typeof bg==="string"&&bg.trim()==="")bg=null;
    return fetch(saveUrl,{
        method:"POST",
        headers:requestHeaders,
        body:JSON.stringify({
            step_id:stepId,
            layout_json:layoutJson,
            background_color:bg,
            skip_revision:!!skipRevision
        })
    }).then(function(r){
        if(!r.ok){
            return r.text().then(function(t){
                var msg=t||("HTTP "+r.status);
                try{var j=JSON.parse(t);if(j&&j.message)msg=j.message;}catch(_e){}
                throw new Error(msg);
            });
        }
        return r.json();
    });
}
function persistLayoutsForSteps(stepList,skipRevision){
    var list=Array.isArray(stepList)?stepList:[];
    var sr=skipRevision!==false;
    var queue=Promise.resolve(true);
    list.forEach(function(step){
        if(!step||!step.id)return;
        queue=queue.then(function(){
            var layout=(step.layout_json&&typeof step.layout_json==="object"&&!Array.isArray(step.layout_json))
                ?clone(step.layout_json)
                :{root:[],sections:[]};
            var bg=(typeof step.background_color==="string"&&step.background_color.trim()!=="")?step.background_color.trim():null;
            if(!bg&&layout&&layout.__editor&&typeof layout.__editor.canvasBg==="string"&&layout.__editor.canvasBg.trim()!==""){
                bg=layout.__editor.canvasBg.trim();
            }
            return postStepLayoutSave(step.id,layout,bg,sr).then(function(resp){
                if(resp&&resp.layout_json)step.layout_json=resp.layout_json;
                if(resp&&typeof resp.background_color==="string"){
                    step.background_color=resp.background_color.trim()||null;
                }
                return true;
            });
        });
    });
    return queue;
}
function defaultStepTitleForType(type){
    var t=String(type||"custom").toLowerCase();
    if(t==="opt_in")return "Opt-in";
    if(t==="thank_you")return "Thank You";
    if(t==="checkout")return "Checkout";
    if(t==="upsell")return "Upsell";
    if(t==="downsell")return "Downsell";
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
        revision_history:normalizeRevisionHistory(r.revision_history||r.manual_versions),
    };
}
function layoutHasContent(layout){
    if(!layout||typeof layout!=="object"||Array.isArray(layout))return false;
    var rootCount=Array.isArray(layout.root)?layout.root.length:0;
    var sectionCount=Array.isArray(layout.sections)?layout.sections.length:0;
    return rootCount>0||sectionCount>0;
}
function normalizeRevisionHistory(raw){
    var list=Array.isArray(raw)?raw:[];
    return list.map(function(item){
        var entry=(item&&typeof item==="object")?item:{};
        var sourceLayout=(entry.layout&&typeof entry.layout==="object"&&!Array.isArray(entry.layout))
            ? entry.layout
            : ((entry.layout_json&&typeof entry.layout_json==="object"&&!Array.isArray(entry.layout_json)) ? entry.layout_json : null);
        var layout=sourceLayout
            ? clone(sourceLayout)
            : {root:[],sections:[]};
        var bg=(typeof entry.background_color==="string"&&entry.background_color.trim()!=="")
            ? entry.background_color.trim()
            : ((layout&&layout.__editor&&typeof layout.__editor.canvasBg==="string"&&layout.__editor.canvasBg.trim()!=="") ? layout.__editor.canvasBg.trim() : null);
        if(bg){
            layout=withCanvasBgInLayout(layout,bg);
        }
        return {
            id:Number(entry.id||0),
            label:String(entry.label||"").trim(),
            time:String(entry.created_at||entry.time||""),
            type:String(entry.version_type||entry.type||"autosave").trim().toLowerCase()||"autosave",
            layout:layout,
        };
    }).sort(function(a,b){
        var ta=Date.parse(a.time||"")||0;
        var tb=Date.parse(b.time||"")||0;
        return ta-tb;
    });
}
function applyStepUpdate(rawStep){
    var merged=mergeStepData(rawStep);
    var idx=steps.findIndex(function(s){return +s.id===+merged.id;});
    if(idx>=0){
        if((!merged.layout_json||typeof merged.layout_json!=="object")&&steps[idx]&&steps[idx].layout_json&&typeof steps[idx].layout_json==="object"){
            merged.layout_json=steps[idx].layout_json;
        }else if(
            steps[idx]&&steps[idx].layout_json&&typeof steps[idx].layout_json==="object"&&
            layoutHasContent(steps[idx].layout_json)&&
            !layoutHasContent(merged.layout_json)
        ){
            merged.layout_json=steps[idx].layout_json;
        }
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
    var defaultTitle=suggestedStepTitleForType(type);
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
        if(pageMgrRenameType)pageMgrRenameType.value="landing";
        if(pageMgrRenameSlug)pageMgrRenameSlug.value="";
        if(pageMgrRenameTags)pageMgrRenameTags.value="";
        return;
    }
    if(pageMgrRenameTitle)pageMgrRenameTitle.value=String(s.title||"");
    if(pageMgrRenameType)pageMgrRenameType.value=normalizeTemplateType(s.type);
    if(pageMgrRenameSlug)pageMgrRenameSlug.value=String(s.slug||"");
    if(pageMgrRenameTags)pageMgrRenameTags.value=normalizeTagArray(s.step_tags).join(", ");
}
function syncPageManagerList(){
    if(!pageMgrList)return;
    sortStepsByPosition();
    pageMgrList.innerHTML="";
    steps.forEach(function(s){
        var sameTypeCount=stepsOfType(s.type).length;
        var typeLabel=numberedPageTypeLabel(s.type,repeatedTypeIndex(s),sameTypeCount);
        var titleLabel=String((s.title&&String(s.title).trim())||typeLabel);
        var item=document.createElement("div");
        item.className="page-mgr-item"+(String(state.sid)===String(s.id)?" is-selected":"");
        item.setAttribute("role","option");
        item.setAttribute("aria-selected",String(state.sid)===String(s.id)?"true":"false");
        item.setAttribute("data-id",String(s.id));
        item.setAttribute("draggable","true");
        item.innerHTML='<div class="page-mgr-item-copy"><span class="page-mgr-item-title">'+titleLabel.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span><span class="page-mgr-item-meta">'+typeLabel.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span></div><span class="page-mgr-item-handle"><i class="fas fa-grip-vertical"></i></span>';
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
function closeVersionModal(){
    if(!versionModal)return;
    versionModal.classList.remove("open");
    versionModal.setAttribute("aria-hidden","true");
    if(versionModalSave){
        versionModalSave.disabled=false;
        versionModalSave.textContent="Save Version";
    }
}
function openVersionModal(){
    var s=cur();
    if(!s){
        showBuilderToast("No page selected.","error");
        return;
    }
    if(versionModalPageName){
        versionModalPageName.textContent='Create a restore point for "'+String(s.title||"Untitled")+'".';
    }
    if(versionModalLabel)versionModalLabel.value="";
    if(versionModal){
        versionModal.classList.add("open");
        versionModal.setAttribute("aria-hidden","false");
    }
    if(versionModalSave){
        versionModalSave.disabled=false;
        versionModalSave.textContent="Save Version";
    }
    if(versionModalLabel)versionModalLabel.focus();
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
    var title=titleRaw!==""?titleRaw:suggestedStepTitleForType(type);
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
    var nextType=normalizeTemplateType((pageMgrRenameType&&pageMgrRenameType.value)||s.type||"custom");
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
    var payload=buildStepPayload(s,{title:nextTitle,slug:nextSlug,type:nextType,step_tags:nextTags});
    requestJson(stepUrlFromTpl(stepUpdateTpl,s.id),"PUT",payload).then(function(resp){
        var updated=applyStepUpdate((resp&&resp.step)||payload);
        state.sid=updated.id;
        renderStepOptions();
        syncPageManagerList();
        syncRenameDraftFromSelected();
        showBuilderToast("Page updated.","success");
    }).catch(function(err){
        showBuilderToast((err&&err.message)||"Failed to update page.","error");
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
        stepAddBtn.onclick=function(){
            if(builderSingleScrollMode){
                showBuilderToast("Single screen mode keeps this funnel as one scrollable page.","error");
                return;
            }
            openPageManagerModal();
        };
    }
    if(pageMgrModal){
        pageMgrModal.addEventListener("click",function(e){
            if(e.target===pageMgrModal)closePageManagerModal();
        });
    }
    if(versionModal){
        versionModal.addEventListener("click",function(e){
            if(e.target===versionModal)closeVersionModal();
        });
    }
    if(pageMgrClose){
        pageMgrClose.onclick=function(){closePageManagerModal();};
    }
    if(versionModalClose){
        versionModalClose.onclick=function(){closeVersionModal();};
    }
    if(versionModalCancel){
        versionModalCancel.onclick=function(){closeVersionModal();};
    }
    if(versionModalSave){
        versionModalSave.onclick=function(){submitManualVersion();};
    }
    if(versionModalLabel){
        versionModalLabel.addEventListener("keydown",function(e){
            if(e.key==="Enter"){
                e.preventDefault();
                submitManualVersion();
            }else if(e.key==="Escape"){
                e.preventDefault();
                closeVersionModal();
            }
        });
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

const undoHistory=[];const redoHistory=[];const maxUndo=40;
var realLayoutData=null;
var previewState=null;
function formatHistoryTime(ts){
    var d=new Date(ts);
    if(isNaN(d.getTime()))return "Saved earlier";
    var t=new Date();
    var isToday=d.getDate()===t.getDate()&&d.getMonth()===t.getMonth()&&d.getFullYear()===t.getFullYear();
    var hm=d.toLocaleTimeString([],{hour:'numeric',minute:'2-digit',second:'2-digit'});
    if(isToday)return "Today, "+hm;
    return d.toLocaleDateString([],{month:'short',day:'numeric'})+", "+hm;
}
function formatHistoryMinuteLabel(ts){
    var d=new Date(ts);
    if(isNaN(d.getTime()))return "Earlier";
    var t=new Date();
    var isToday=d.getDate()===t.getDate()&&d.getMonth()===t.getMonth()&&d.getFullYear()===t.getFullYear();
    var hm=d.toLocaleTimeString([],{hour:'numeric',minute:'2-digit'});
    if(isToday)return "Today, "+hm;
    return d.toLocaleDateString([],{month:'short',day:'numeric'})+", "+hm;
}
function formatHistorySecondLabel(ts){
    var d=new Date(ts);
    if(isNaN(d.getTime()))return "Saved earlier";
    var t=new Date();
    var isToday=d.getDate()===t.getDate()&&d.getMonth()===t.getMonth()&&d.getFullYear()===t.getFullYear();
    var hms=d.toLocaleTimeString([],{hour:'numeric',minute:'2-digit',second:'2-digit'});
    if(isToday)return hms;
    return d.toLocaleDateString([],{month:'short',day:'numeric'})+", "+hms;
}
function historyMinuteKey(ts){
    var d=new Date(ts);
    if(isNaN(d.getTime()))return "unknown";
    var pad=function(n){return String(n).padStart(2,"0");};
    return d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate())+"-"+pad(d.getHours())+"-"+pad(d.getMinutes());
}
function buildHistoryGroups(entries,currentStepId){
    var groups=[];
    var byKey={};
    var stepKey=Number(currentStepId||0);
    for(var idx=entries.length-1;idx>=0;idx--){
        var entry=entries[idx];
        var minuteKey=historyMinuteKey(entry&&entry.time);
        var stateKey="step-"+stepKey+"-"+minuteKey;
        if(!byKey[stateKey]){
            byKey[stateKey]={
                key:stateKey,
                minuteKey:minuteKey,
                label:formatHistoryMinuteLabel(entry&&entry.time),
                items:[]
            };
            groups.push(byKey[stateKey]);
        }
        byKey[stateKey].items.push({entry:entry,index:idx});
    }
    return groups;
}
function escapeHtml(value){
    return String(value==null?"":value)
        .replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;")
        .replace(/"/g,"&quot;")
        .replace(/'/g,"&#39;");
}
function historyLayoutsMatch(a,b){
    try{
        return JSON.stringify(a||{})===JSON.stringify(b||{});
    }catch(_err){
        return false;
    }
}
function currentHistoryLayout(){
    var s=cur();
    var prefs=editorPrefs();
    var bg=normalizeCanvasBgValue((prefs&&prefs.canvasBg)||((s&&s.background_color)||""));
    return withCanvasBgInLayout(clone(state.layout||{root:[],sections:[]}),bg);
}
function savedHistoryEntries(){
    var s=cur();
    return normalizeRevisionHistory(s&&s.revision_history);
}
function saveToHistory(){
    if(!state.layout||state.isPreviewingHistory)return;
    undoHistory.push({time:Date.now(),layout:clone(state.layout)});
    if(undoHistory.length>maxUndo)undoHistory.shift();
    redoHistory.length=0;
    queueAutoSave();
    if(typeof renderHistoryDrawer==='function')renderHistoryDrawer();
}
function undo(){
    if(state.isPreviewingHistory||!undoHistory.length)return;
    redoHistory.push({time:Date.now(),layout:clone(state.layout)});
    var popped=undoHistory.pop();
    state.layout=popped.layout;
    render();
    queueAutoSave();
    if(typeof renderHistoryDrawer==='function')renderHistoryDrawer();
}
function redo(){
    if(state.isPreviewingHistory||!redoHistory.length)return;
    undoHistory.push({time:Date.now(),layout:clone(state.layout)});
    var popped=redoHistory.pop();
    state.layout=popped.layout;
    render();
    queueAutoSave();
    if(typeof renderHistoryDrawer==='function')renderHistoryDrawer();
}
function previewHistory(index,isRedo){
    if(!state.isPreviewingHistory){
        realLayoutData=clone(state.layout);
        state.isPreviewingHistory=true;
    }
    previewState={index:index,isRedo:isRedo};
    var target=isRedo?redoHistory[index]:undoHistory[index];
    if(target){
        state.layout=clone(target.layout);
        render();
    }
    renderHistoryDrawer();
    renderHistoryBanner();
}
function exitPreviewHistory(){
    if(!state.isPreviewingHistory)return;
    state.layout=realLayoutData;
    realLayoutData=null;
    state.isPreviewingHistory=false;
    previewState=null;
    render();
    renderHistoryDrawer();
    renderHistoryBanner();
}
function restorePreviewHistory(){
    if(!state.isPreviewingHistory||!previewState)return;
    var idx=previewState.index;var rdo=previewState.isRedo;
    state.layout=realLayoutData;
    state.isPreviewingHistory=false;
    realLayoutData=null;
    previewState=null;
    jumpToHistory(idx,rdo);
    renderHistoryBanner();
}
function restoreHistory(index,isRedo){
    if(state.isPreviewingHistory){
        state.layout=realLayoutData;
        realLayoutData=null;
        state.isPreviewingHistory=false;
        previewState=null;
    }
    jumpToHistory(index,isRedo);
    renderHistoryDrawer();
    renderHistoryBanner();
}
function restoreSavedRevision(index){
    var entries=savedHistoryEntries();
    var entry=entries[index];
    var s=cur();
    if(!entry||!s)return;
    if(historyLayoutsMatch(currentHistoryLayout(),entry.layout)){
        renderHistoryDrawer();
        return;
    }
    undoHistory.push({time:Date.now(),layout:clone(state.layout)});
    if(undoHistory.length>maxUndo)undoHistory.shift();
    redoHistory.length=0;
    state.isPreviewingHistory=false;
    realLayoutData=null;
    previewState=null;
    state.layout=clone(entry.layout);
    s.layout_json=clone(entry.layout);
    s.background_color=(entry.layout&&entry.layout.__editor&&typeof entry.layout.__editor.canvasBg==="string"&&entry.layout.__editor.canvasBg.trim()!=="")
        ? entry.layout.__editor.canvasBg.trim()
        : null;
    render();
    queueAutoSave();
    saveMsg.textContent="Restoring version...";
    renderHistoryDrawer();
    renderHistoryBanner();
}
function versionUrlForStep(id){
    return stepUrlFromTpl(stepVersionTpl,id);
}
function submitManualVersion(){
    var s=cur();
    if(!s){
        showBuilderToast("No page selected.","error");
        return;
    }
    var rawLabel=String((versionModalLabel&&versionModalLabel.value)||"").trim();
    if(versionModalSave){
        versionModalSave.disabled=true;
        versionModalSave.textContent="Saving...";
    }
    saveMsg.textContent="Saving version...";
    flushAutoSave()
        .then(function(){
            return requestJson(versionUrlForStep(s.id),"POST",{label:rawLabel});
        })
        .then(function(resp){
            s.revision_history=normalizeRevisionHistory((s&&s.revision_history||[]).concat((resp&&resp.manual_versions)||[]));
            closeVersionModal();
            renderHistoryDrawer();
            saveMsg.textContent="Version saved "+new Date().toLocaleTimeString();
            showBuilderToast("Version saved for this page.","success");
        })
        .catch(function(err){
            if(versionModalSave){
                versionModalSave.disabled=false;
                versionModalSave.textContent="Save Version";
            }
            saveMsg.textContent="Version save failed";
            showBuilderToast((err&&err.message)||"Failed to save version.","error");
        });
}
window.previewHistory=previewHistory;
window.exitPreviewHistory=exitPreviewHistory;
window.restorePreviewHistory=restorePreviewHistory;
window.restoreHistory=restoreHistory;
window.restoreSavedRevision=restoreSavedRevision;
window.createManualVersion=openVersionModal;
window.submitManualVersion=submitManualVersion;
function renderHistoryBanner(){
    var banner=document.getElementById("fbHistoryBanner");
    if(!banner){
        banner=document.createElement("div");
        banner.id="fbHistoryBanner";
        banner.style.position="fixed";
        banner.style.top="12px";
        banner.style.left="50%";
        banner.style.transform="translateX(-50%)";
        banner.style.zIndex="2000";
        document.body.appendChild(banner);
    }
    if(state.isPreviewingHistory){
        banner.style.display="flex";
        banner.style.alignItems="center";
        banner.style.gap="12px";
        banner.style.background="#0f172a";
        banner.style.padding="8px 16px";
        banner.style.borderRadius="999px";
        banner.style.color="#fff";
        banner.style.boxShadow="0 10px 25px rgba(15,23,42,0.4)";
        banner.innerHTML="<div style='font-size:13px;font-weight:700;'><i class='fas fa-eye' style='margin-right:6px;'></i> Previewing Version</div>" +
            "<button class='fb-btn' style='background:#10b981;border:none;color:#fff;border-radius:999px;padding:6px 14px;' onclick='window.restorePreviewHistory()'>Restore Version</button>" +
            "<button class='fb-btn' style='background:rgba(255,255,255,0.15);border:none;color:#fff;border-radius:999px;padding:6px 14px;' onclick='window.exitPreviewHistory()'>Back</button>";
    }else{
        banner.style.display="none";
    }
}
window.historyExpanded = window.historyExpanded || {};
window.toggleHistoryGroup = function(lbl){
    var key=String(lbl||"");
    var current=!!window.historyExpanded[key];
    var m=key.match(/^(step-\d+-)/);
    var prefix=m?m[1]:"";
    Object.keys(window.historyExpanded).forEach(function(existingKey){
        if(!prefix || existingKey.indexOf(prefix)===0){
            window.historyExpanded[existingKey]=false;
        }
    });
    window.historyExpanded[key]=!current;
    renderHistoryDrawer();
};
function renderHistoryDrawer(){
    var container=document.getElementById("fbHistoryContainer");
    if(!container)return;
    var currentStep=cur();
    var entries=savedHistoryEntries();
    var currentLayout=currentHistoryLayout();
    var latestSaved=entries.length?entries[entries.length-1]:null;
    var openSavedIndex=-1;
    for(var i=entries.length-1;i>=0;i--){
        if(historyLayoutsMatch(currentLayout,entries[i].layout)){
            openSavedIndex=i;
            break;
        }
    }
    var statusBg="#eff6ff";
    var statusBorder="#bfdbfe";
    var statusTitle="Current page";
    var statusText="Changes on this page save automatically.";
    if(!entries.length){
        statusBg="#fff7ed";
        statusBorder="#fed7aa";
        statusText="No versions yet. Make a change and wait a moment for autosave.";
    }else if(openSavedIndex===entries.length-1){
        statusBg="#ecfdf5";
        statusBorder="#86efac";
        statusText="All changes are saved.";
    }else if(openSavedIndex>=0){
        statusBg="#fff7ed";
        statusBorder="#fdba74";
        statusText="You are viewing an older version. Autosave will make it the newest version.";
    }else if(latestSaved){
        statusBg="#fff7ed";
        statusBorder="#fdba74";
        statusText="Recent changes are still waiting for autosave.";
    }

    var html="<div class='fb-history-shell'>";
    html+="<div class='fb-history-status' style='border:1px solid "+statusBorder+";background:"+statusBg+";'>";
    html+="<div style='font-size:14px;font-weight:800;color:#240E35;display:flex;align-items:center;gap:8px;'><i class='fas fa-star' style='color:#10b981;'></i> "+escapeHtml(statusTitle)+"</div>";
    html+="<div style='font-size:12px;color:#475569;margin-top:4px;line-height:1.45;'>"+escapeHtml(statusText)+"</div>";
    html+="</div>";

    if(!entries.length){
        html+="<div style='padding:10px 4px;color:#64748b;font-size:12px;line-height:1.45;'>Your autosaved timeline will appear here for this page.</div>";
        html+="</div>";
        container.innerHTML=html;
        return;
    }

    html+="<div class='fb-history-label'>Version timeline</div>";
    html+="<div class='fb-history-note'>Grouped by minute so the list stays tidy.</div>";
    html+="<div class='fb-history-accordion'>";
    var groups=buildHistoryGroups(entries,currentStep&&currentStep.id);
    for(var groupIdx=0;groupIdx<groups.length;groupIdx++){
        var group=groups[groupIdx];
        var containsOpen=group.items.some(function(item){return item.index===openSavedIndex;});
        var hasState=Object.prototype.hasOwnProperty.call(window.historyExpanded,group.key);
        var expanded=hasState ? !!window.historyExpanded[group.key] : (groupIdx===0 || containsOpen);
        var countLabel=group.items.length+" save"+(group.items.length===1?"":"s");
        var chevron=expanded?"fa-chevron-down":"fa-chevron-right";
        html+="<div class='fb-history-group'>";
        html+="<button type='button' class='fb-history-group-toggle' onclick='window.toggleHistoryGroup(\""+group.key+"\")'>";
        html+="<span class='fb-history-group-main'>";
        html+="<span class='fb-history-group-title'>"+escapeHtml(group.label)+"</span>";
        html+="<span class='fb-history-group-meta'>"+escapeHtml(countLabel)+(containsOpen?" | Current":"")+"</span>";
        html+="</span>";
        html+="<span class='fb-history-group-arrow'><i class='fas "+chevron+"'></i></span>";
        html+="</button>";
        if(expanded){
            html+="<div class='fb-history-group-panel'>";
            for(var itemIdx=0;itemIdx<group.items.length;itemIdx++){
                var item=group.items[itemIdx];
                var entry=item.entry;
                var actualIndex=item.index;
                var isOpen=openSavedIndex===actualIndex;
                var isLatest=actualIndex===entries.length-1;
                var hasCustomLabel=String(entry.label||"").trim()!=="";
                var label=hasCustomLabel ? String(entry.label).trim() : formatHistorySecondLabel(entry.time||"");
                var sub="Saved automatically";
                if(hasCustomLabel){
                    sub=formatHistorySecondLabel(entry.time||"");
                }else if(String(entry.type||"autosave")==="manual"){
                    sub="Saved manually";
                }
                var badgeText=isOpen?"Open now":"Restore";
                html+="<div class='fb-history-entry"+(isOpen?" is-open":"")+"'>";
                html+="<div class='fb-history-entry-text'>";
                html+="<div class='fb-history-entry-title-row'>";
                html+="<div class='fb-history-entry-title'>"+escapeHtml(label)+"</div>";
                if(isLatest){
                    html+="<span class='fb-history-tag'>Newest</span>";
                }
                html+="</div>";
                html+="<div class='fb-history-entry-meta'>"+escapeHtml(sub)+"</div>";
                html+="</div>";
                if(isOpen){
                    html+="<span class='fb-history-pill'>"+escapeHtml(badgeText)+"</span>";
                }else{
                    html+="<button class='fb-btn fb-history-action' onclick='window.restoreSavedRevision("+actualIndex+")'>"+escapeHtml(badgeText)+"</button>";
                }
                html+="</div>";
            }
            html+="</div>";
        }
        html+="</div>";
    }
    html+="</div>";
    html+="</div>";
    container.innerHTML=html;
}
function jumpToHistory(index,isRedo){
    if(isRedo){
        var c1=redoHistory.length-index;
        for(var i=0;i<c1;i++)redo();
    }else{
        var c2=undoHistory.length-index;
        for(var i=0;i<c2;i++)undo();
    }
}

function syncSectionsFromRoot(){
    state.layout=state.layout||{};
    state.layout.root=Array.isArray(state.layout.root)?state.layout.root:[];
    const out=[];
    var freeformEls=[];
    var freeformIndex=0;
    function flushFreeformGroup(){
        if(!freeformEls.length)return;
        var wrapId="sec_freeform_canvas_"+String(freeformIndex++);
        var existingFreeform=(state.layout.sections||[]).find(function(s){
            return !!s.__freeformCanvas && String(s.id||"")===wrapId;
        });
        out.push({
            id:wrapId,
            style:existingFreeform?existingFreeform.style:{},
            settings:existingFreeform?existingFreeform.settings:{contentWidth:"full"},
            elements:freeformEls.slice(),
            rows:[],
            __rootWrap:true,
            __rootKind:"el",
            __bareRootWrap:true,
            __freeformCanvas:true
        });
        freeformEls=[];
    }
    state.layout.root.forEach((it,idx)=>{
        const kind=String((it&&it.kind)||"section").toLowerCase();
        if(kind==="section"){
            flushFreeformGroup();
            it.elements=Array.isArray(it.elements)?it.elements:[];
            it.rows=Array.isArray(it.rows)?it.rows:[];
            out.push(it);
            return;
        }
        if(kind==="row"){
            flushFreeformGroup();
            const wrap={id:"sec_wrap_row_"+String(it.id||idx),style:{},settings:{contentWidth:"full"},elements:[],rows:[it],__rootWrap:true,__rootKind:"row",__rootRef:it,__bareRootWrap:true};
            out.push(wrap);
            return;
        }
        if(kind==="column"){
            flushFreeformGroup();
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
    flushFreeformGroup();
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
function normalizeFaqItems(raw){
    var list=Array.isArray(raw)?raw:[];
    var out=list.map(function(item,idx){
        var it=(item&&typeof item==="object")?item:{};
        var q=String(it.q||it.question||"").trim();
        var a=String(it.a||it.answer||"").trim();
        if(q==="")q="Question "+(idx+1);
        if(a==="")a="Answer "+(idx+1);
        return {q:q,a:a};
    }).filter(function(it){return String(it.q||"").trim()!=="";});
    if(!out.length){
        out.push({q:"What is included?",a:"Everything you need to get started."});
    }
    return out;
}
function normalizeFeatureList(raw){
    var list=[];
    if(Array.isArray(raw))list=raw;
    else if(typeof raw==="string")list=raw.split("\n");
    var out=list.map(function(it){return String(it||"").trim();}).filter(function(it){return it!=="";});
    if(!out.length){
        out=["Feature one","Feature two","Feature three"];
    }
    return out;
}
function normalizeProductOfferMediaList(raw){
    var list=Array.isArray(raw)?raw:[];
    var out=list.map(function(item,idx){
        if(typeof item==="string"){
            return {type:"image",src:String(item||"").trim(),alt:"Media "+(idx+1),poster:""};
        }
        item=(item&&typeof item==="object")?item:{};
        var type=String(item.type||"image").trim().toLowerCase();
        if(type!=="video")type="image";
        return {
            type:type,
            src:String(item.src||"").trim(),
            alt:String(item.alt||item.label||("Media "+(idx+1))).trim(),
            poster:String(item.poster||"").trim()
        };
    });
    if(!out.length){
        out=[
            {type:"image",src:"",alt:"Main product image",poster:""},
            {type:"image",src:"",alt:"Detail image",poster:""}
        ];
    }
    return out;
}
function formatDateTimeLocal(v){
    var raw=String(v||"").trim();
    if(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(raw))return raw.slice(0,16);
    var d=new Date(raw);
    if(isNaN(d.getTime()))return "";
    var pad=n=>String(n).padStart(2,"0");
    return d.getFullYear()+"-"+pad(d.getMonth()+1)+"-"+pad(d.getDate())+"T"+pad(d.getHours())+":"+pad(d.getMinutes());
}
function countdownParts(endAt){
    var t=Date.parse(String(endAt||""));
    if(isNaN(t))return null;
    var diff=Math.max(0,t-Date.now());
    var total=Math.floor(diff/1000);
    var days=Math.floor(total/86400);
    total%=86400;
    var hours=Math.floor(total/3600);
    total%=3600;
    var minutes=Math.floor(total/60);
    var seconds=total%60;
    return {days:days,hours:hours,minutes:minutes,seconds:seconds};
}
function pad2(n){return String(n).padStart(2,"0");}
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
    if(_canvasContentWidth>0)out.__editor.canvasContentWidth=_canvasContentWidth;
    return out;
}
function measureSectionStageWidths(){
    var out={};
    if(!canvas||!canvas.querySelectorAll)return out;
    canvas.querySelectorAll(".sec[data-sec-id]").forEach(function(sectionNode){
        if(!sectionNode||sectionNode.classList.contains("sec--freeform-canvas"))return;
        var secId=String(sectionNode.getAttribute("data-sec-id")||"").trim();
        if(secId==="")return;
        var inner=sectionNode.querySelector(".sec-inner")||sectionNode;
        var width=Math.max(
            Math.round(inner.clientWidth||0),
            inner.getBoundingClientRect?Math.round(inner.getBoundingClientRect().width||0):0
        );
        if(width>0)out[secId]=width;
    });
    return out;
}
function withMeasuredSectionStageWidths(layout){
    var out=(layout&&typeof layout==="object")?clone(layout):{root:[],sections:[]};
    var measured=measureSectionStageWidths();
    var applyStageWidth=function(section){
        if(!section||typeof section!=="object"||section.__freeformCanvas)return;
        var secId=String(section.id||"").trim();
        var stageWidth=Math.round(Number(measured[secId])||0);
        if(stageWidth<=0)return;
        section.settings=(section.settings&&typeof section.settings==="object")?section.settings:{};
        section.settings.stageWidth=stageWidth;
    };
    (Array.isArray(out.sections)?out.sections:[]).forEach(applyStageWidth);
    (Array.isArray(out.root)?out.root:[]).forEach(function(node){
        if(!node||typeof node!=="object")return;
        if(String(node.kind||"").toLowerCase()!=="section")return;
        applyStageWidth(node);
    });
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
    state.layout=normalizeTemplateCurrencyLayout(state.layout);
    state.layout=normalizeTemplateLayout(state.layout);
    state.layout=repairDefaultPricingFlowLayout(state.layout,s,steps);
    if(s&&String(s.template||"").trim()!==""&&String(s.template||"").trim()!=="simple"){
        state.layout=repairTemplateFlowLayout(state.layout,s,steps);
    }
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
    redoHistory.length=0;
    if(typeof renderHistoryDrawer==='function')renderHistoryDrawer();
    saveMsg.textContent="Loaded "+s.title;
    applyCanvasBgPreference();
    syncCanvasBgControls();
    if(typeof renderTemplateLibrary==="function")renderTemplateLibrary();
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
function findElementById(id){
    var targetId=String(id||"");
    if(targetId==="")return null;
    var found=null;
    function scanElements(list){
        if(!Array.isArray(list))return false;
        for(var i=0;i<list.length;i++){
            var el=list[i];
            if(!el||typeof el!=="object")continue;
            if(String(el.id||"")===targetId){found=el;return true;}
            if(el.type==="carousel"){
                var slides=ensureCarouselSlides(el.settings||{});
                for(var si=0;si<slides.length;si++){
                    var rwList=slides[si]&&slides[si].rows;
                    if(!Array.isArray(rwList))continue;
                    for(var ri=0;ri<rwList.length;ri++){
                        var cols=rwList[ri]&&rwList[ri].columns;
                        if(!Array.isArray(cols))continue;
                        for(var ci=0;ci<cols.length;ci++){
                            if(scanElements(cols[ci]&&cols[ci].elements))return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    (state.layout.sections||[]).some(function(sec){
        if(scanElements(sec.elements))return true;
        var rows=sec.rows||[];
        for(var r=0;r<rows.length;r++){
            var cols=rows[r]&&rows[r].columns;
            if(!Array.isArray(cols))continue;
            for(var c=0;c<cols.length;c++){
                if(scanElements(cols[c]&&cols[c].elements))return true;
            }
        }
        return false;
    });
    return found;
}
function collectElementsByType(type){
    var out=[];
    var want=String(type||"");
    function scanElements(list){
        if(!Array.isArray(list))return;
        list.forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(String(el.type||"")===want)out.push(el);
            if(el.type==="carousel"){
                var slides=ensureCarouselSlides(el.settings||{});
                slides.forEach(function(sl){
                    (sl.rows||[]).forEach(function(rw){
                        (rw.columns||[]).forEach(function(col){
                            scanElements(col.elements);
                        });
                    });
                });
            }
        });
    }
    (state.layout.sections||[]).forEach(function(sec){
        scanElements(sec.elements);
        (sec.rows||[]).forEach(function(rw){
            (rw.columns||[]).forEach(function(col){
                scanElements(col.elements);
            });
        });
    });
    return out;
}
function normalizeLinkedPricingIds(raw){
    if(Array.isArray(raw))return raw.map(v=>String(v||"").trim()).filter(Boolean);
    if(typeof raw==="string"&&raw.trim()!=="")return [raw.trim()];
    return [];
}
function getLinkedPricingIds(item){
    if(!item||item.type!=="countdown")return [];
    var settings=item.settings||{};
    var ids=normalizeLinkedPricingIds(settings.linkedPricingIds);
    if(!ids.length){
        ids=normalizeLinkedPricingIds(settings.linkedPricingId);
    }
    var out=[];
    ids.forEach(function(id){
        if(id!==""&&out.indexOf(id)===-1)out.push(id);
    });
    return out;
}
function setLinkedPricingIds(item,ids){
    if(!item||item.type!=="countdown")return [];
    item.settings=item.settings||{};
    var clean=normalizeLinkedPricingIds(ids);
    var out=[];
    clean.forEach(function(id){
        if(id!==""&&out.indexOf(id)===-1)out.push(id);
    });
    if(out.length){
        item.settings.linkedPricingIds=out;
        if(Object.prototype.hasOwnProperty.call(item.settings,"linkedPricingId"))delete item.settings.linkedPricingId;
    }else{
        if(Object.prototype.hasOwnProperty.call(item.settings,"linkedPricingIds"))delete item.settings.linkedPricingIds;
        if(Object.prototype.hasOwnProperty.call(item.settings,"linkedPricingId"))delete item.settings.linkedPricingId;
    }
    return out;
}
function getLinkedPricingIdsForSelection(){
    var t=selectedTarget();
    if(t&&t.type==="countdown"){
        return getLinkedPricingIds(t);
    }
    if(state.linkPick&&state.linkPick.sourceId){
        var src=findElementById(state.linkPick.sourceId);
        if(src&&src.type==="countdown"){
            return getLinkedPricingIds(src);
        }
    }
    return [];
}
function startPricingLink(sourceId){
    var src=findElementById(sourceId);
    if(!src||src.type!=="countdown"){
        showBuilderToast("Select a countdown first.","error");
        return false;
    }
    if(collectElementsByType("pricing").length===0){
        showBuilderToast("Add a pricing component first.","error");
        return false;
    }
    state.linkPick={type:"pricing",sourceId:String(src.id||"")};
    renderCanvas();
    renderSettings();
    showBuilderToast("Click pricing components to link. Press Esc to finish.","success");
    return true;
}
function collectCountdownLinks(){
    var links=[];
    var allPricings=[];
    function scanForPricings(list){
        if(!Array.isArray(list))return;
        list.forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(el.type==="pricing")allPricings.push(el);
            if(el.type==="carousel"){
                var slides=ensureCarouselSlides(el.settings||{});
                slides.forEach(function(sl){(sl.rows||[]).forEach(function(rw){(rw.columns||[]).forEach(function(col){scanForPricings(col.elements);});});});
            }
        });
    }
    (state.layout.sections||[]).forEach(function(sec){
        scanForPricings(sec.elements);
        (sec.rows||[]).forEach(function(rw){(rw.columns||[]).forEach(function(col){scanForPricings(col.elements);});});
    });

    function scanElements(list){
        if(!Array.isArray(list))return;
        list.forEach(function(el){
            if(!el||typeof el!=="object")return;
            if(el.type==="countdown"){
                var fromId=String(el.id||"");
                var ids=getLinkedPricingIds(el);
                ids.forEach(function(toId){
                    if(fromId!==""&&toId!=="")links.push({from:fromId,to:toId});
                });
                var cPromo=String((el.settings&&el.settings.promoKey)||"").trim();
                if(cPromo!==""){
                    allPricings.forEach(function(p){
                        var pPromo=String((p.settings&&p.settings.promoKey)||"").trim();
                        if(pPromo===cPromo){
                            var pId=String(p.id||"");
                            var exists=links.find(function(l){return l.from===fromId&&l.to===pId;});
                            if(!exists&&fromId!==""&&pId!=="")links.push({from:fromId,to:pId});
                        }
                    });
                }
            }
            if(el.type==="carousel"){
                var slides=ensureCarouselSlides(el.settings||{});
                slides.forEach(function(sl){
                    (sl.rows||[]).forEach(function(rw){
                        (rw.columns||[]).forEach(function(col){
                            scanElements(col.elements);
                        });
                    });
                });
            }
        });
    }
    (state.layout.sections||[]).forEach(function(sec){
        scanElements(sec.elements);
        (sec.rows||[]).forEach(function(rw){
            (rw.columns||[]).forEach(function(col){
                scanElements(col.elements);
            });
        });
    });
    return links;
}
function ensureLinkLayer(){
    if(!canvas)return null;
    var svg=canvas.__linkLayer;
    if(!svg||!svg.parentNode){
        svg=document.createElementNS("http://www.w3.org/2000/svg","svg");
        svg.setAttribute("id","fbLinkLayer");
        svg.classList.add("fb-link-layer");
        canvas.appendChild(svg);
        canvas.__linkLayer=svg;
    }
    var w=Math.max(canvas.scrollWidth,canvas.clientWidth);
    var h=Math.max(canvas.scrollHeight,canvas.clientHeight);
    svg.setAttribute("width",w);
    svg.setAttribute("height",h);
    svg.setAttribute("viewBox","0 0 "+w+" "+h);
    return svg;
}
function updateWireVisibility(hoveredId){
    var svg=canvas&&canvas.__linkLayer;
    if(!svg)return;
    var showAll=!!state.linkPick;
    var wires=svg.querySelectorAll('.wire');
    wires.forEach(function(w){
        if(showAll){
            w.setAttribute("opacity","0.9");
        }else{
            if(hoveredId&&w.classList.contains("component-wire-"+hoveredId)){
                w.setAttribute("opacity","0.9");
            }else{
                w.setAttribute("opacity","0");
            }
        }
    });
}
function drawLinkWires(){
    if(!canvas)return;
    if(!canvas.__wireHoverBound){
        canvas.__wireHoverBound=true;
        canvas.addEventListener("mousemove",function(e){
            // Block hovers locally during preview
            if(state.isPreviewingHistory) return;
            var el=e.target.closest&&e.target.closest('.el[data-el-id]');
            var id=el?el.getAttribute('data-el-id'):null;
            if(canvas.__wireHoverId!==id){
                canvas.__wireHoverId=id;
                updateWireVisibility(id);
            }
        });
        canvas.addEventListener("mouseleave",function(){
            canvas.__wireHoverId=null;
            updateWireVisibility(null);
        });
    }
    var links=collectCountdownLinks();
    var svg=canvas.__linkLayer;
    if(!links.length){
        if(svg)svg.innerHTML="";
        return;
    }
    svg=ensureLinkLayer();
    if(!svg)return;
    svg.innerHTML="";
    var crect=canvas.getBoundingClientRect();
    function toCanvasPoint(x,y){
        return {
            x:(x-crect.left)+canvas.scrollLeft,
            y:(y-crect.top)+canvas.scrollTop
        };
    }
    links.forEach(function(link){
        var fromNode=canvas.querySelector('.el[data-el-id="'+link.from+'"]');
        var toNode=canvas.querySelector('.el[data-el-id="'+link.to+'"]');
        if(!fromNode||!toNode)return;
        var fr=fromNode.getBoundingClientRect();
        var tr=toNode.getBoundingClientRect();
        var fcx=fr.left+fr.width/2, fcy=fr.top+fr.height/2;
        var tcx=tr.left+tr.width/2, tcy=tr.top+tr.height/2;
        var horizontal=Math.abs(tcx-fcx)>=Math.abs(tcy-fcy);
        var sx,sy,ex,ey;
        if(horizontal){
            if(tcx>=fcx){sx=fr.right;sy=fcy;ex=tr.left;ey=tcy;}
            else{sx=fr.left;sy=fcy;ex=tr.right;ey=tcy;}
        }else{
            if(tcy>=fcy){sx=fcx;sy=fr.bottom;ex=tcx;ey=tr.top;}
            else{sx=fcx;sy=fr.top;ex=tcx;ey=tr.bottom;}
        }
        var sp=toCanvasPoint(sx,sy);
        var ep=toCanvasPoint(ex,ey);
        var dx=ep.x-sp.x;
        var dy=ep.y-sp.y;
        var curve;
        var c1x,c1y,c2x,c2y;
        if(horizontal){
            curve=Math.min(140,Math.max(40,Math.abs(dx)*0.35));
            c1x=sp.x+(dx>0?curve:-curve);c1y=sp.y;
            c2x=ep.x-(dx>0?curve:-curve);c2y=ep.y;
        }else{
            curve=Math.min(140,Math.max(40,Math.abs(dy)*0.35));
            c1x=sp.x;c1y=sp.y+(dy>0?curve:-curve);
            c2x=ep.x;c2y=ep.y-(dy>0?curve:-curve);
        }
        var pClass="wire component-wire-"+link.from+" component-wire-"+link.to;
        var initOp=(state.linkPick?"0.9":"0");
        var path=document.createElementNS("http://www.w3.org/2000/svg","path");
        path.setAttribute("d","M "+sp.x+" "+sp.y+" C "+c1x+" "+c1y+" "+c2x+" "+c2y+" "+ep.x+" "+ep.y);
        path.setAttribute("stroke","#6B4A7A");
        path.setAttribute("stroke-width","2");
        path.setAttribute("fill","none");
        path.setAttribute("class",pClass);
        path.setAttribute("opacity",initOp);
        path.style.transition="opacity 0.2s";
        svg.appendChild(path);
        var c1=document.createElementNS("http://www.w3.org/2000/svg","circle");
        c1.setAttribute("cx",sp.x);
        c1.setAttribute("cy",sp.y);
        c1.setAttribute("r","4");
        c1.setAttribute("fill","#6B4A7A");
        c1.setAttribute("class",pClass);
        c1.setAttribute("opacity",initOp);
        c1.style.transition="opacity 0.2s";
        var c2=document.createElementNS("http://www.w3.org/2000/svg","circle");
        c2.setAttribute("cx",ep.x);
        c2.setAttribute("cy",ep.y);
        c2.setAttribute("r","4");
        c2.setAttribute("fill","#6B4A7A");
        c2.setAttribute("class",pClass);
        c2.setAttribute("opacity",initOp);
        c2.style.transition="opacity 0.2s";
        svg.appendChild(c1);
        svg.appendChild(c2);
    });
    updateWireVisibility(canvas.__wireHoverId);
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
function pasteNodeInMain(node,nodeKind,selectionOverride){
    var sel=selectionOverride||state.sel;
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
function pasteFromClipboard(anchorOverride){
    var clip=state.clipboard;
    if(!clip||!clip.node)return false;
    var node=cloneWithNewIds(clip.node);
    var nodeKind=inferNodeKind(node);
    if(nodeKind==="")return false;
    var anchor=(anchorOverride&&typeof anchorOverride==="object")?anchorOverride:null;
    var selectionOverride=(anchor&&anchor.target&&typeof anchor.target==="object")?clone(anchor.target):null;
    if(anchor&&anchor.freePlacement&&nodeKind==="el"){
        applyFreePlacementToElement(node,anchor.freePlacement);
    }
    saveToHistory();
    if(state.carouselSel){
        var okCar=pasteNodeInCarousel(node,nodeKind);
        if(okCar)return true;
    }
    return pasteNodeInMain(node,nodeKind,selectionOverride);
}
function nudgeDuplicatePosition(node){
    if(!node||typeof node!=="object")return;
    if(!node.style)node.style={};
    if(!node.settings)node.settings={};
    var isAbs=(String(node.settings.positionMode||"").toLowerCase()==="absolute")||(String(node.style.position||"").toLowerCase()==="absolute");
    if(!isAbs)return;
    var dx=20,dy=20;
    var leftRaw=parseFloat(String(node.style.left||"").replace("px",""));
    var topRaw=parseFloat(String(node.style.top||"").replace("px",""));
    if(!isNaN(leftRaw))node.style.left=(leftRaw+dx)+"px";
    if(!isNaN(topRaw))node.style.top=(topRaw+dy)+"px";
    if(!isNaN(leftRaw)||!isNaN(topRaw))return;
    var fx=Number(node.settings.freeX),fy=Number(node.settings.freeY);
    if(!isNaN(fx))node.settings.freeX=fx+dx;
    if(!isNaN(fy))node.settings.freeY=fy+dy;
    if(!isNaN(fx))node.style.left=Math.round(node.settings.freeX)+"px";
    if(!isNaN(fy))node.style.top=Math.round(node.settings.freeY)+"px";
}
function duplicateSelected(){
    var target=selectedTarget();
    if(!target)return false;
    var node=cloneWithNewIds(target);
    var nodeKind=inferNodeKind(node);
    if(nodeKind==="")return false;
    if(nodeKind==="el")nudgeDuplicatePosition(node);
    saveToHistory();
    if(state.carouselSel){
        var okCar=pasteNodeInCarousel(node,nodeKind);
        if(okCar)return true;
        return false;
    }
    if(nodeKind==="el" && state.sel && state.sel.k==="el" && state.sel.scope==="section"){
        var s=sec(state.sel.s);
        if(s && s.__freeformCanvas){
            ensureRootModel();
            var rs=rootItems();
            var idx=rs.findIndex(function(it){
                return String((it&&it.kind)||"").toLowerCase()==="el" && String(it.id||"")===String(target.id||"");
            });
            if(idx>=0)rs.splice(idx+1,0,Object.assign({kind:"el"},node));
            else rs.push(Object.assign({kind:"el"},node));
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"section",s:s.id,e:node.id};
            state.carouselSel=null;
            return true;
        }
    }
    if(state.sel && state.sel.k==="sec" && nodeKind==="sec"){
        ensureRootModel();
        var sctx=sectionRootContext(state.sel.s);
        var rs=rootItems();
        if(sctx.index>=0)rs.splice(sctx.index+1,0,Object.assign({kind:"section"},node));
        else rs.push(Object.assign({kind:"section"},node));
        syncSectionsFromRoot();
        state.sel={k:"sec",s:node.id};
        state.carouselSel=null;
        return true;
    }
    return pasteNodeInMain(node,nodeKind);
}
const ctxMenu={node:null,copyBtn:null,pasteBtn:null,dupBtn:null,connectBtn:null,deleteBtn:null,open:false,mode:"full"};
function ensureContextMenu(){
    if(ctxMenu.node&&ctxMenu.node.parentNode)return ctxMenu.node;
    var menu=document.createElement("div");
    menu.className="fb-ctx-menu";
    menu.id="fbCtxMenu";
    menu.innerHTML='<button type="button" id="fbCtxCopy" class="fb-ctx-item">Copy</button><button type="button" id="fbCtxPaste" class="fb-ctx-item">Paste</button><button type="button" id="fbCtxDuplicate" class="fb-ctx-item">Duplicate</button><button type="button" id="fbCtxConnect" class="fb-ctx-item">Connect to pricing</button><button type="button" id="fbCtxDelete" class="fb-ctx-item" style="color:#ef4444;">Delete</button>';
    document.body.appendChild(menu);
    ctxMenu.node=menu;
    ctxMenu.copyBtn=menu.querySelector("#fbCtxCopy");
    ctxMenu.pasteBtn=menu.querySelector("#fbCtxPaste");
    ctxMenu.dupBtn=menu.querySelector("#fbCtxDuplicate");
    ctxMenu.connectBtn=menu.querySelector("#fbCtxConnect");
    ctxMenu.deleteBtn=menu.querySelector("#fbCtxDelete");
    if(ctxMenu.copyBtn){
        ctxMenu.copyBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.copyBtn.disabled)return;
            hideContextMenu();
            if(copySelectedToClipboard()){
                showBuilderToast("Copied component.","success");
            }else{
                showBuilderToast("Nothing to copy.","error");
            }
        });
    }
    if(ctxMenu.pasteBtn){
        ctxMenu.pasteBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.pasteBtn.disabled)return;
            var anchor=ctxMenu.mode==="paste-only"?clone(state.pasteAnchor):null;
            hideContextMenu();
            if(pasteFromClipboard(anchor)){
                render();
                showBuilderToast("Pasted component.","success");
            }else{
                showBuilderToast("Nothing to paste here.","error");
            }
        });
    }
    if(ctxMenu.dupBtn){
        ctxMenu.dupBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.dupBtn.disabled)return;
            if(duplicateSelected()){
                hideContextMenu();
                render();
                showBuilderToast("Duplicated component.","success");
            }else{
                showBuilderToast("Duplicate failed for this target.","error");
            }
        });
    }
    if(ctxMenu.connectBtn){
        ctxMenu.connectBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.connectBtn.disabled)return;
            hideContextMenu();
            var t=selectedTarget();
            if(!t||t.type!=="countdown"){
                showBuilderToast("Select a countdown to connect.","error");
                return;
            }
            startPricingLink(t.id);
        });
    }
    if(ctxMenu.deleteBtn){
        ctxMenu.deleteBtn.addEventListener("click",function(e){
            e.preventDefault();
            e.stopPropagation();
            if(ctxMenu.deleteBtn.disabled)return;
            hideContextMenu();
            removeSelected();
            render();
            showBuilderToast("Deleted component.","success");
        });
    }
    menu.addEventListener("contextmenu",function(e){e.preventDefault();});
    return menu;
}
function syncContextMenuState(){
    ensureContextMenu();
    var hasSelection=!!selectedTarget();
    var hasClipboard=!!(state.clipboard&&state.clipboard.node);
    var t=selectedTarget();
    var canConnect=!!(t&&t.type==="countdown");
    var pasteOnly=ctxMenu.mode==="paste-only";
    if(ctxMenu.copyBtn){
        ctxMenu.copyBtn.disabled=!hasSelection;
        ctxMenu.copyBtn.style.display=pasteOnly?"none":"block";
    }
    if(ctxMenu.dupBtn){
        ctxMenu.dupBtn.disabled=!hasSelection;
        ctxMenu.dupBtn.style.display=pasteOnly?"none":"block";
    }
    if(ctxMenu.pasteBtn){
        ctxMenu.pasteBtn.disabled=!hasClipboard;
        ctxMenu.pasteBtn.style.display=hasClipboard?"block":"none";
    }
    if(ctxMenu.deleteBtn){
        ctxMenu.deleteBtn.disabled=!hasSelection;
        ctxMenu.deleteBtn.style.display=pasteOnly?"none":"block";
    }
    if(ctxMenu.connectBtn){
        ctxMenu.connectBtn.disabled=!canConnect;
        ctxMenu.connectBtn.style.display=(!pasteOnly&&canConnect)?"block":"none";
    }
}
function hideContextMenu(){
    if(!ctxMenu.node)return;
    ctxMenu.node.style.display="none";
    ctxMenu.open=false;
    ctxMenu.mode="full";
    state.pasteAnchor=null;
}
function showContextMenuAt(x,y,mode){
    var menu=ensureContextMenu();
    ctxMenu.mode=mode==="paste-only"?"paste-only":"full";
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
function resolvePasteAnchorFromEvent(e){
    if(!(e&&e.target&&canvas&&canvas.contains(e.target)))return null;
    var colNode=e.target.closest&&e.target.closest(".col[data-col-id]");
    if(colNode){
        var colInner=colNode.querySelector(".col-inner")||colNode;
        return {
            target:{
                k:"col",
                s:String(colNode.getAttribute("data-s")||""),
                r:String(colNode.getAttribute("data-r")||""),
                c:String(colNode.getAttribute("data-col-id")||"")
            },
            freePlacement:computeFreeDropPosition(e,colInner)
        };
    }
    var rowNode=e.target.closest&&e.target.closest(".row[data-row-id]");
    if(rowNode){
        var rowInner=rowNode.querySelector(".row-inner")||rowNode;
        var nearCol=nearestColumnNode(rowInner,e.clientX);
        if(nearCol){
            var nearInner=nearCol.querySelector(".col-inner")||nearCol;
            return {
                target:{
                    k:"col",
                    s:String(nearCol.getAttribute("data-s")||""),
                    r:String(nearCol.getAttribute("data-r")||""),
                    c:String(nearCol.getAttribute("data-col-id")||"")
                },
                freePlacement:computeFreeDropPosition(e,nearInner)
            };
        }
        return {
            target:{
                k:"row",
                s:String(rowNode.getAttribute("data-s")||""),
                r:String(rowNode.getAttribute("data-row-id")||"")
            },
            freePlacement:null
        };
    }
    var secNode=e.target.closest&&e.target.closest(".sec[data-sec-id]");
    if(secNode){
        var secInner=secNode.querySelector(".sec-inner")||secNode;
        return {
            target:{
                k:"sec",
                s:String(secNode.getAttribute("data-sec-id")||"")
            },
            freePlacement:computeFreeDropPosition(e,secInner)
        };
    }
    return {
        target:null,
        freePlacement:computeFreeDropPosition(e,canvas)
    };
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
function titleCase(v){
    var raw=String(v||"").toLowerCase();
    if(raw==="faq")return "FAQ";
    return (v||"").replace(/[_-]/g," ").replace(/\b\w/g,m=>m.toUpperCase());
}
function isAdvancedScaleComponent(t){
    var type=String(t||"").toLowerCase();
    return type==="testimonial"||type==="review_form"||type==="reviews"||type==="faq"||type==="pricing"||type==="product_offer"||type==="checkout_summary"||type==="physical_checkout_summary"||type==="shipping_details"||type==="countdown"||type==="form";
}
function parsePxVal(v){
    var n=Number(String(v||"0").replace("px","").trim());
    return isNaN(n)?0:n;
}
function clampVal(n,min,max){return Math.max(min,Math.min(max,n));}
function scalePaddingValue(pad,scale){
    var raw=String(pad||"").trim();
    if(!raw)return "";
    var parts=raw.split(/\s+/).filter(Boolean);
    if(!parts.length)return "";
    var nums=parts.map(parsePxVal);
    var t=0,r=0,b=0,l=0;
    if(nums.length===1){t=r=b=l=nums[0];}
    else if(nums.length===2){t=b=nums[0];r=l=nums[1];}
    else if(nums.length===3){t=nums[0];r=l=nums[1];b=nums[2];}
    else{t=nums[0];r=nums[1];b=nums[2];l=nums[3];}
    return Math.round(t*scale)+"px "+Math.round(r*scale)+"px "+Math.round(b*scale)+"px "+Math.round(l*scale)+"px";
}
function getContentScale(item){
    var s=Number(item&&item.settings&&item.settings.contentScale);
    if(isNaN(s)||s<=0)return 1;
    return clampVal(s,0.5,3);
}
function applyAdvancedScaleToNode(node,item,scale){
    if(!node||!item||!isAdvancedScaleComponent(item.type))return;
    var s=(scale!==undefined)?scale:getContentScale(item);
    s=clampVal(s,0.5,3);
    if(item.style){
        if(item.style.padding){
            var sp=scalePaddingValue(item.style.padding,s);
            if(sp)node.style.padding=sp;
        }
        if(item.style.borderRadius){
            var br=parsePxVal(item.style.borderRadius);
            if(br>0)node.style.borderRadius=Math.round(br*s)+"px";
        }
    }
    var type=String(item.type||"").toLowerCase();
    if(type==="testimonial"){
        var card=node.querySelector(".fb-testimonial");
        var quote=node.querySelector(".fb-testimonial-quote");
        var author=node.querySelector(".fb-testimonial-author");
        var avatar=node.querySelector(".fb-testimonial-avatar");
        var name=node.querySelector(".fb-testimonial-name");
        var role=node.querySelector(".fb-testimonial-role");
        if(card)card.style.gap=Math.round(10*s)+"px";
        if(author)author.style.gap=Math.round(10*s)+"px";
        if(avatar){var av=Math.round(40*s);avatar.style.width=av+"px";avatar.style.height=av+"px";}
        if(quote)quote.style.fontSize=Math.round(16*s)+"px";
        if(name)name.style.fontSize=Math.round(16*s)+"px";
        if(role)role.style.fontSize=Math.round(12*s)+"px";
    }
    if(type==="faq"){
        var faq=node.querySelector(".fb-faq");
        var q=node.querySelectorAll(".fb-faq-q");
        var a=node.querySelectorAll(".fb-faq-a");
        if(faq){
            var gap=Number(item.settings&&item.settings.itemGap);if(isNaN(gap)||gap<0)gap=10;
            faq.style.gap=Math.round(gap*s)+"px";
        }
        q.forEach(function(el){el.style.fontSize=Math.round(16*s)+"px";});
        a.forEach(function(el){el.style.fontSize=Math.round(13*s)+"px";});
    }
    if(type==="pricing"){
        var pricing=node.querySelector(".fb-pricing");
        var badge=node.querySelector(".fb-pricing-badge");
        var title=node.querySelector(".fb-pricing-title");
        var price=node.querySelector(".fb-pricing-price");
        var period=node.querySelector(".fb-pricing-period");
        var subtitle=node.querySelector(".fb-pricing-subtitle");
        var features=node.querySelector(".fb-pricing-features");
        var feats=node.querySelectorAll(".fb-pricing-features li");
        var cta=node.querySelector(".fb-pricing-cta");
        if(pricing)pricing.style.gap=Math.round(10*s)+"px";
        if(badge){
            badge.style.fontSize=Math.round(11*s)+"px";
            badge.style.padding=Math.round(4*s)+"px "+Math.round(10*s)+"px";
        }
        if(title)title.style.fontSize=Math.round(18*s)+"px";
        if(price)price.style.fontSize=Math.round(30*s)+"px";
        if(period)period.style.fontSize=Math.round(12*s)+"px";
        if(subtitle)subtitle.style.fontSize=Math.round(12*s)+"px";
        if(features)features.style.gap=Math.round(6*s)+"px";
        feats.forEach(function(el){el.style.fontSize=Math.round(12*s)+"px";});
        if(cta){
            cta.style.fontSize=Math.round(16*s)+"px";
            cta.style.padding=Math.round(8*s)+"px "+Math.round(12*s)+"px";
        }
    }
    if(type==="checkout_summary"||type==="physical_checkout_summary"){
        var isPhysicalSummaryScale=type==="physical_checkout_summary";
        var checkoutCard=node.querySelector(".fb-pricing");
        var checkoutBadge=node.querySelector(".fb-pricing-badge");
        var checkoutTitle=node.querySelector(".fb-pricing-title");
        var checkoutPrice=node.querySelector(".fb-pricing-price");
        var checkoutPeriods=node.querySelectorAll(".fb-pricing-period");
        var checkoutSubtitle=node.querySelector(".fb-pricing-subtitle");
        var checkoutFeatures=node.querySelector(".fb-pricing-features");
        var checkoutFeatureItems=node.querySelectorAll(".fb-pricing-features li");
        var checkoutCta=node.querySelector(".fb-pricing-cta");
        if(checkoutCard)checkoutCard.style.gap=Math.max(6,Math.round((isPhysicalSummaryScale?12:10)*s))+"px";
        if(checkoutBadge){
            checkoutBadge.style.fontSize=Math.max(8,Math.round(11*s))+"px";
            checkoutBadge.style.padding=Math.max(2,Math.round(4*s))+"px "+Math.max(6,Math.round(10*s))+"px";
        }
        if(checkoutTitle)checkoutTitle.style.fontSize=Math.max(12,Math.round((isPhysicalSummaryScale?16:18)*s))+"px";
        if(checkoutPrice)checkoutPrice.style.fontSize=Math.max(18,Math.round((isPhysicalSummaryScale?24:30)*s))+"px";
        checkoutPeriods.forEach(function(el){el.style.fontSize=Math.max(9,Math.round(12*s))+"px";});
        if(checkoutSubtitle)checkoutSubtitle.style.fontSize=Math.max(9,Math.round(12*s))+"px";
        if(checkoutFeatures)checkoutFeatures.style.gap=Math.max(3,Math.round((isPhysicalSummaryScale?5:6)*s))+"px";
        checkoutFeatureItems.forEach(function(el){el.style.fontSize=Math.max(9,Math.round((isPhysicalSummaryScale?11:12)*s))+"px";});
        if(checkoutCta){
            checkoutCta.style.fontSize=Math.max(11,Math.round(16*s))+"px";
            checkoutCta.style.padding=Math.max(7,Math.round((isPhysicalSummaryScale?10:8)*s))+"px "+Math.max(10,Math.round((isPhysicalSummaryScale?14:12)*s))+"px";
        }
        if(isPhysicalSummaryScale){
            var physicalLabel=node.querySelector(".fb-physical-checkout-label");
            var physicalProduct=node.querySelector(".fb-physical-checkout-product");
            var physicalThumb=node.querySelector(".fb-physical-checkout-thumb");
            var physicalThumbIcon=node.querySelector(".fb-physical-checkout-thumb i");
            var physicalRows=node.querySelector(".fb-physical-checkout-rows");
            var physicalRowItems=node.querySelectorAll(".fb-physical-checkout-row");
            var physicalRowStrong=node.querySelectorAll(".fb-physical-checkout-row strong");
            var physicalLines=node.querySelector(".fb-physical-checkout-lines");
            var physicalLineItems=node.querySelectorAll(".fb-physical-checkout-line");
            var physicalLineThumbs=node.querySelectorAll(".fb-physical-checkout-line-thumb");
            var physicalLineThumbIcons=node.querySelectorAll(".fb-physical-checkout-line-thumb i");
            var physicalLineTitles=node.querySelectorAll(".fb-physical-checkout-line-title");
            var physicalLineSubs=node.querySelectorAll(".fb-physical-checkout-line-sub");
            var physicalLineTotals=node.querySelectorAll(".fb-physical-checkout-line-total");
            if(physicalLabel)physicalLabel.style.fontSize=Math.max(8,Math.round(11*s))+"px";
            if(physicalProduct){
                physicalProduct.style.gridTemplateColumns=Math.max(44,Math.round(64*s))+"px minmax(0,1fr)";
                physicalProduct.style.gap=Math.max(8,Math.round(12*s))+"px";
                physicalProduct.style.padding=Math.max(8,Math.round(12*s))+"px";
            }
            if(physicalThumb){
                var thumbSize=Math.max(44,Math.round(64*s));
                physicalThumb.style.width=thumbSize+"px";
                physicalThumb.style.height=thumbSize+"px";
                physicalThumb.style.borderRadius=Math.max(10,Math.round(16*s))+"px";
            }
            if(physicalThumbIcon)physicalThumbIcon.style.fontSize=Math.max(14,Math.round(22*s))+"px";
            if(physicalRows){
                physicalRows.style.gap=Math.max(5,Math.round(8*s))+"px";
                physicalRows.style.padding=Math.max(6,Math.round(10*s))+"px 0";
            }
            physicalRowItems.forEach(function(el){el.style.fontSize=Math.max(9,Math.round(12*s))+"px";});
            physicalRowStrong.forEach(function(el){el.style.fontSize=Math.max(10,Math.round(13*s))+"px";});
            if(physicalLines)physicalLines.style.gap=Math.max(5,Math.round(8*s))+"px";
            physicalLineItems.forEach(function(el){
                el.style.gridTemplateColumns=Math.max(30,Math.round(40*s))+"px 1fr auto";
                el.style.gap=Math.max(6,Math.round(10*s))+"px";
                el.style.padding=Math.max(5,Math.round(8*s))+"px 0";
            });
            physicalLineThumbs.forEach(function(el){
                var lineThumbSize=Math.max(30,Math.round(40*s));
                el.style.width=lineThumbSize+"px";
                el.style.height=lineThumbSize+"px";
                el.style.borderRadius=Math.max(8,Math.round(12*s))+"px";
            });
            physicalLineThumbIcons.forEach(function(el){el.style.fontSize=Math.max(10,Math.round(14*s))+"px";});
            physicalLineTitles.forEach(function(el){el.style.fontSize=Math.max(9,Math.round(12*s))+"px";});
            physicalLineSubs.forEach(function(el){el.style.fontSize=Math.max(8,Math.round(11*s))+"px";});
            physicalLineTotals.forEach(function(el){el.style.fontSize=Math.max(9,Math.round(12*s))+"px";});
        }
    }
    if(type==="product_offer"){
        var productCard=node.querySelector(".fb-product-offer");
        var productMedia=node.querySelector(".fb-product-media");
        var productMediaStage=node.querySelector(".fb-product-media-stage");
        var productPlaceholder=node.querySelector(".fb-product-media-placeholder");
        var productPlaceholderIcon=node.querySelector(".fb-product-media-placeholder i");
        var productNavs=node.querySelectorAll(".fb-product-media-nav");
        var productDots=node.querySelector(".fb-product-media-dots");
        var productDotItems=node.querySelectorAll(".fb-product-media-dot");
        var productBadge=node.querySelector(".fb-pricing-badge");
        var productTitle=node.querySelector(".fb-pricing-title");
        var productPrice=node.querySelector(".fb-pricing-price");
        var productPeriods=node.querySelectorAll(".fb-pricing-period");
        var productSubtitle=node.querySelector(".fb-pricing-subtitle");
        var productFeatures=node.querySelector(".fb-pricing-features");
        var productFeatureItems=node.querySelectorAll(".fb-pricing-features li");
        var productCta=node.querySelector(".fb-pricing-cta");
        var mediaMin=Math.max(64,Math.round(88*s));
        if(productCard)productCard.style.gap=Math.round(4*s)+"px";
        if(productMedia)productMedia.style.minHeight=mediaMin+"px";
        if(productMediaStage)productMediaStage.style.minHeight=mediaMin+"px";
        if(productPlaceholder){
            productPlaceholder.style.minHeight=mediaMin+"px";
            productPlaceholder.style.gap=Math.max(3,Math.round(4*s))+"px";
            productPlaceholder.style.padding=Math.max(6,Math.round(8*s))+"px";
            productPlaceholder.style.fontSize=Math.max(9,Math.round(10*s))+"px";
        }
        if(productPlaceholderIcon)productPlaceholderIcon.style.fontSize=Math.max(14,Math.round(16*s))+"px";
        productNavs.forEach(function(el){
            var navSize=Math.max(16,Math.round(22*s));
            el.style.width=navSize+"px";
            el.style.height=navSize+"px";
            el.style.fontSize=Math.max(8,Math.round(9*s))+"px";
        });
        if(productDots)productDots.style.gap=Math.max(3,Math.round(4*s))+"px";
        productDotItems.forEach(function(el){
            var isActive=el.classList.contains("is-active");
            el.style.width=(isActive?Math.max(8,Math.round(12*s)):Math.max(3,Math.round(5*s)))+"px";
            el.style.height=Math.max(3,Math.round(5*s))+"px";
        });
        if(productBadge){
            productBadge.style.fontSize=Math.round(9*s)+"px";
            productBadge.style.padding=Math.round(2*s)+"px "+Math.round(6*s)+"px";
        }
        if(productTitle)productTitle.style.fontSize=Math.round(13*s)+"px";
        if(productPrice)productPrice.style.fontSize=Math.round(20*s)+"px";
        productPeriods.forEach(function(el){el.style.fontSize=Math.round(10*s)+"px";});
        if(productSubtitle)productSubtitle.style.fontSize=Math.round(10*s)+"px";
        if(productFeatures)productFeatures.style.gap=Math.round(4*s)+"px";
        productFeatureItems.forEach(function(el){el.style.fontSize=Math.round(10*s)+"px";});
        if(productCta){
            productCta.style.fontSize=Math.round(11*s)+"px";
            productCta.style.padding=Math.round(7*s)+"px "+Math.round(8*s)+"px";
        }
    }
    if(type==="countdown"){
        var cd=node.querySelector(".fb-countdown");
        var label=node.querySelector(".fb-countdown-label");
        var grid=node.querySelector(".fb-countdown-grid");
        var boxes=node.querySelectorAll(".fb-countdown-box");
        var nums=node.querySelectorAll(".fb-countdown-num");
        var units=node.querySelectorAll(".fb-countdown-unit");
        if(cd){}
        if(label)label.style.fontSize=Math.round(12*s)+"px";
        if(grid){
            var gap=Number(item.settings&&item.settings.itemGap);if(isNaN(gap)||gap<0)gap=8;
            grid.style.gap=Math.round(gap*s)+"px";
        }
        boxes.forEach(function(el){el.style.padding=Math.round(8*s)+"px";});
        nums.forEach(function(el){el.style.fontSize=Math.round(20*s)+"px";});
        units.forEach(function(el){el.style.fontSize=Math.round(10*s)+"px";});
    }
    if(type==="form"||type==="shipping_details"){
        var isShippingDetails=type==="shipping_details";
        var shippingHeading=node.querySelector(".fb-shipping-heading");
        var shippingSubtitle=node.querySelector(".fb-shipping-subtitle");
        var labels=node.querySelectorAll("label");
        var inputs=node.querySelectorAll(".fb-form-input");
        var submit=node.querySelector("button");
        var formBox=node.firstElementChild;
        if(formBox&&formBox.style){
            formBox.style.gap=Math.max(0,Math.round(2*s))+"px";
        }
        if(shippingHeading){
            shippingHeading.style.fontSize=Math.max(12,Math.round(20*s))+"px";
            shippingHeading.style.marginBottom=Math.max(3,Math.round(6*s))+"px";
        }
        if(shippingSubtitle){
            shippingSubtitle.style.fontSize=Math.max(9,Math.round(12*s))+"px";
            shippingSubtitle.style.marginBottom=Math.max(5,Math.round(10*s))+"px";
        }
        labels.forEach(function(el){
            el.style.fontSize=Math.max(9,Math.round(12*s))+"px";
            el.style.marginBottom=Math.max(2,Math.round(4*s))+"px";
        });
        inputs.forEach(function(el){
            el.style.fontSize=Math.max(10,Math.round(14*s))+"px";
            el.style.padding=Math.max(5,Math.round(8*s))+"px";
            el.style.borderRadius=Math.max(4,Math.round(8*s))+"px";
            el.style.marginBottom=Math.max(4,Math.round(8*s))+"px";
        });
        if(!isShippingDetails&&submit){
            submit.style.fontSize=Math.round(14*s)+"px";
            submit.style.padding=Math.round(8*s)+"px "+Math.round(12*s)+"px";
            submit.style.borderRadius=Math.max(6,Math.round(8*s))+"px";
        }
    }
}
function syncAdvancedElementHeight(node,item){
    if(!node||!item||!isAdvancedScaleComponent(item.type))return;
    item.style=item.style||{};
    node.style.height="auto";
    var needed=Math.ceil(node.scrollHeight||0);
    if(!needed){
        var rect=node.getBoundingClientRect();
        needed=Math.ceil(rect.height||0);
    }
    if(needed>0){
        item.style.height=Math.max(20,needed)+"px";
        node.style.height=item.style.height;
    }
}
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
        imgBody.innerHTML=(item.settings&&item.settings.src)?'<img src="'+item.settings.src+'" alt="'+(item.settings.alt||"Image")+'" style="max-width:100%;height:auto;display:block;">':'<div class="fb-image-placeholder fb-image-placeholder--compact"><div class="fb-image-placeholder__plus">+</div><div class="fb-image-placeholder__label">Image placeholder</div></div>';
        wrap.appendChild(imgBody);
    }else if(type==="video"){
        const raw=(item.settings&&item.settings.src)||"";
        const vurl=raw?(raw.startsWith("http")?raw:"https://"+raw.replace(/^\/*/,"")):"";
        const wrapStyle="position:relative;width:100%;min-height:200px;padding-top:56.25%;background:#240E35;border-radius:8px;overflow:hidden;box-sizing:border-box;display:flex;align-items:center;justify-content:center;pointer-events:none;";
        var vidBody=document.createElement("div");
        if(vurl){
            const label=vurl.length>50?vurl.slice(0,47)+"...":vurl;
            vidBody.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.9);padding:12px;text-align:center;"><span style="font-size:32px;margin-bottom:8px;opacity:0.9;">â–¶</span><span style="font-size:12px;font-weight:700;">Video</span><span style="font-size:11px;opacity:0.8;word-break:break-all;max-width:100%;">'+label.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span></div></div>';
        } else {
            vidBody.innerHTML='<div style="'+wrapStyle+'"><div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.8);padding:12px;"><span style="font-size:28px;margin-bottom:6px;">â–¶</span><span style="font-size:12px;">Video URL placeholder</span><span style="font-size:11px;margin-top:4px;">Paste link or upload</span></div></div>';
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
function createDefaultColumn(){return {id:uid("col"),style:{flex:"1 1 0"},elements:[]};}
function createDefaultSection(){return {id:uid("sec"),style:{padding:"20px",backgroundColor:"#fff",minHeight:"30vh"},settings:{contentWidth:"full"},elements:[],rows:[]};}
function createRootItem(type){
    if(type==="section")return Object.assign({kind:"section"},createDefaultSection());
    if(type==="row")return Object.assign({kind:"row"},createDefaultRow());
    if(type==="column")return Object.assign({kind:"column"},createDefaultColumn());
    const it=createDefaultElement(type);
    return it?Object.assign({kind:"el"},it):null;
}
function createDefaultElement(type){
    var _cd=new Date(Date.now()+7*24*60*60*1000);
    var _pad=n=>String(n).padStart(2,"0");
    var countdownEndVal=_cd.getFullYear()+"-"+_pad(_cd.getMonth()+1)+"-"+_pad(_cd.getDate())+"T"+_pad(_cd.getHours())+":"+_pad(_cd.getMinutes());
const d={heading:{content:"Heading",style:{fontSize:"32px",color:"#000000",position:"absolute"},settings:{positionMode:"absolute"}},text:{content:"Text",style:{fontSize:"16px",color:"#000000",position:"absolute"},settings:{positionMode:"absolute"}},menu:{content:"",style:{fontSize:"16px",width:"400px",position:"absolute"},settings:{positionMode:"absolute",items:[{label:"Home",url:"#",newWindow:false,hasSubmenu:false},{label:"Contact",url:"/contact",newWindow:false,hasSubmenu:false}],itemGap:13,activeIndex:0,menuAlign:"left",underlineColor:""}},carousel:{content:"",style:{width:"200px",height:"200px",padding:"0px",position:"absolute"},settings:{positionMode:"absolute",slides:[defaultCarouselSlide("Slide #1")],activeSlide:0,vAlign:"center",alignment:"left",showArrows:true,slideshowMode:"manual",controlsColor:"#64748b",arrowColor:"#ffffff",fixedWidth:200,fixedHeight:200}},image:{content:"",style:{width:"300px",position:"absolute"},settings:{positionMode:"absolute",src:"",alt:"Image",alignment:"left"}},button:{content:"Click Me",style:{backgroundColor:"#240E35",color:"#fff",borderRadius:"999px",padding:"10px 18px",textAlign:"center",position:"absolute"},settings:{positionMode:"absolute",actionType:"next_step",actionStepSlug:"",link:"#"}},icon:{content:"",style:{fontSize:"36px",color:"#2E1244",padding:"0px",borderRadius:"0px",position:"absolute"},settings:{positionMode:"absolute",iconName:"star",iconStyle:"solid",alignment:"center",link:""}},form:{content:"Submit",style:{width:"350px",position:"absolute"},settings:{positionMode:"absolute",alignment:"left",width:"350px",buttonAlign:"left",buttonBold:false,buttonItalic:false,labelColor:"#240E35",placeholderColor:"#94a3b8",buttonBgColor:"#240E35",buttonTextColor:"#ffffff",fields:[{type:"text",label:"First name",placeholder:"First name",required:false}]}},shipping_details:{content:"",style:{width:"420px",padding:"18px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"18px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",heading:"Shipping Details",subtitle:"Enter your delivery and contact information before placing the order.",labelColor:"#240E35",placeholderColor:"#94a3b8",fields:[{type:"first_name",label:"First name",placeholder:"First name",required:true},{type:"last_name",label:"Last name",placeholder:"Last name",required:true},{type:"email",label:"Email",placeholder:"Email address",required:true},{type:"phone_number",label:"Phone number",placeholder:"09XXXXXXXXX",required:true},{type:"province",label:"Province",placeholder:"Province",required:true},{type:"city_municipality",label:"City / Municipality",placeholder:"City / Municipality",required:true},{type:"barangay",label:"Barangay",placeholder:"Barangay",required:true},{type:"street",label:"Street address",placeholder:"House no., street, building",required:true},{type:"postal_code",label:"Postal code",placeholder:"Postal code",required:false},{type:"notes",label:"Order notes",placeholder:"Optional notes for delivery",required:false}]}},video:{content:"",style:{width:"400px",position:"absolute"},settings:{positionMode:"absolute",src:"",alignment:"left"}},spacer:{content:"",style:{height:"24px",width:"200px",position:"absolute"},settings:{positionMode:"absolute"}},testimonial:{content:"",style:{width:"320px",padding:"16px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"16px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",quote:"This product changed how we work.",name:"Alex Johnson",role:"Founder, Startify",avatar:""}},review_form:{content:"",style:{width:"360px",padding:"18px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"18px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",heading:"How was your experience?",subtitle:"Share a quick review after your order or service experience.",physicalHeading:"How was your order experience?",physicalSubtitle:"Tell us how the ordering and checkout experience felt while your item is on the way.",buttonLabel:"Submit Review",successMessage:"Thanks for the review. It is now waiting for approval.",publicLabel:"I am okay with showing this review publicly."}},reviews:{content:"",style:{width:"420px",padding:"18px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"18px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",heading:"What customers are saying",subtitle:"Approved reviews from this funnel appear here automatically.",emptyText:"Approved reviews will appear here after customers submit them.",maxItems:6,filterRating:0,layout:"list",showRating:true,showDate:false,collapsible:true,collapsedCount:3,expandLabel:"Show all reviews",collapseLabel:"Show fewer reviews"}},faq:{content:"",style:{width:"420px",padding:"16px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"16px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",items:[{q:"How does it work?",a:"Choose a template, customize, and publish."},{q:"Is there a free trial?",a:"Yes, you can start with a 14-day trial."}],itemGap:10,questionColor:"#240E35",answerColor:"#475569"}},pricing:{content:"",style:{width:"320px",padding:"18px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"18px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",plan:"Pro",price:"₱49",period:"/month",subtitle:"Best for growing teams",features:["Unlimited pages","Custom domains","Priority support"],ctaLabel:"Get Started",ctaActionType:"next_step",ctaActionStepSlug:"",ctaLink:"#",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:"Popular"}},product_offer:{content:"",style:{width:"160px",padding:"8px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"16px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",plan:"Starter Bundle",price:"₱299",regularPrice:"₱499",period:"",subtitle:"",description:"Add a fuller product description here for your quick-view modal.",features:[],ctaLabel:"Buy",ctaActionType:"next_step",ctaActionStepSlug:"",ctaLink:"#",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:"Best Seller",stockQuantity:"",quickViewEnabled:true,quickViewLabel:"Details",cartEnabled:true,activeMedia:0,media:[{type:"image",src:"",alt:"Main product image",poster:""}]}},checkout_summary:{content:"",style:{width:"360px",padding:"22px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"20px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",heading:"Complete Your Order",plan:"Chosen Plan",price:"Selected price",period:"/billing cycle",subtitle:"This summary updates from the pricing selected earlier in the funnel.",features:["Unlimited steps","Custom domains","Email support"],ctaLabel:"Pay Now",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:"Selected Plan"}},physical_checkout_summary:{content:"",style:{width:"360px",padding:"22px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"20px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",heading:"Cart Summary",plan:"3 items",price:"₱4,000",period:"",subtitle:"Review the products in your cart before paying.",features:["Product subtotal updates automatically","Cart items show here before payment","Shipping details are collected before payment"],ctaLabel:"Place Order",ctaBgColor:"#240E35",ctaTextColor:"#ffffff",badge:"Cart"}},countdown:{content:"",style:{width:"300px",padding:"16px",backgroundColor:"#ffffff",border:"1px solid #E6E1EF",borderRadius:"16px",boxShadow:"0 12px 24px rgba(15,23,42,.08)",position:"absolute"},settings:{positionMode:"absolute",endAt:countdownEndVal,label:"Offer ends in",expiredText:"Offer ended",numberColor:"#240E35",labelColor:"#64748b",itemGap:8}}}[type]||null;
    if(!d)return null;
    return {id:uid("el"),type:type,content:d.content,style:clone(d.style),settings:clone(d.settings)};
}

function findLooseRootSection(){
    return (state.layout.sections||[]).find(function(secNode){
        return secNode && !secNode.__rootWrap && !secNode.__freeformCanvas;
    })||null;
}

function hasLooseRootSection(){
    ensureRootModel();
    return !!findLooseRootSection();
}

function canAddComponentNow(type){
    var t=String(type||"").toLowerCase();
    if(t==="row"){
        showBuilderToast("Row component is removed. Use Section as the main container.","error");
        return false;
    }
    if(t==="section"||t==="menu"||t==="column"){
        return true;
    }
    if(!hasLooseRootSection()){
        showBuilderToast("Add a Section first before placing this component.","error");
        return false;
    }
    return true;
}

function ensureLooseRootSection(createFresh,insertIndex){
    ensureRootModel();
    var existing=createFresh?null:findLooseRootSection();
    if(existing)return existing;
    var rs=rootItems();
    var newSection=createRootItem("section");
    if(!newSection)return null;
    if(typeof insertIndex==="number" && isFinite(insertIndex)){
        var idx=Math.max(0,Math.min(Math.round(insertIndex),rs.length));
        rs.splice(idx,0,newSection);
    }else{
        rs.push(newSection);
    }
    syncSectionsFromRoot();
    return sec(newSection.id)||newSection;
}


function addComponent(type){
    saveToHistory();
    const p=state.sel||{};
    ensureRootModel();
    const rs=rootItems();
    if(!canAddComponentNow(type))return;
    function convertToFlowElement(node){
        if(!node)return node;
        node.style=node.style||{};
        node.settings=node.settings||{};
        ["position","left","top","right","bottom","zIndex"].forEach(function(key){
            if(Object.prototype.hasOwnProperty.call(node.style,key))delete node.style[key];
        });
        ["positionMode","freeX","freeY"].forEach(function(key){
            if(Object.prototype.hasOwnProperty.call(node.settings,key))delete node.settings[key];
        });
        return node;
    }
    var isColumnContextSelection=(
        p
        && (
            p.k==="col"
            || p.k==="row"
            || (p.k==="el" && p.scope!=="section")
            || (p.k==="el" && !!p.c)
        )
    );
    if(!isStructureComponent(type)&&!isStandaloneRootComponent(type)&&!isColumnContextSelection){
        var targetSection=(p&&p.s)?sec(p.s):null;
        if(!targetSection||targetSection.__rootWrap||targetSection.__freeformCanvas){
            targetSection=findLooseRootSection();
        }
        if(!targetSection){
            showBuilderToast("Add a Section first before placing this component.","error");
            return;
        }
        targetSection.elements=Array.isArray(targetSection.elements)?targetSection.elements:[];
        const freeEl=convertToFlowElement(createDefaultElement(type));
        if(!freeEl)return;
        autoPlaceElement(freeEl,{elements:targetSection.elements});
        targetSection.elements.push(freeEl);
        state.sel={k:"el",scope:"section",s:targetSection.id,e:freeEl.id};
        return;
    }
    if(!p||!p.k){
        if(isStandaloneRootComponent(type)){
            const rootLoose=createRootItem(type);
            if(!rootLoose)return;
            rs.push(rootLoose);
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"root",e:rootLoose.id};
            return;
        }
        if(!isStructureComponent(type)){
            let targetSection=ensureLooseRootSection(false);
            targetSection.elements=Array.isArray(targetSection.elements)?targetSection.elements:[];
            const freeEl=convertToFlowElement(createDefaultElement(type));
            if(!freeEl)return;
            targetSection.elements.push(freeEl);
            state.sel={k:"el",scope:"section",s:targetSection.id,e:freeEl.id};
            return;
        }
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    const pRootCtx=sectionRootContext(p.s);
    if(pRootCtx.isWrap||pRootCtx.isFreeform){
        if(isStandaloneRootComponent(type)){
            const rootLoose2=createRootItem(type);
            if(!rootLoose2)return;
            rs.push(rootLoose2);
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"root",e:rootLoose2.id};
            return;
        }
        if(!isStructureComponent(type)){
            let targetSection=ensureLooseRootSection(false);
            targetSection.elements=Array.isArray(targetSection.elements)?targetSection.elements:[];
            const freeEl2=convertToFlowElement(createDefaultElement(type));
            if(!freeEl2)return;
            targetSection.elements.push(freeEl2);
            state.sel={k:"el",scope:"section",s:targetSection.id,e:freeEl2.id};
            return;
        }
        const rootIt=createRootItem(type);
        if(!rootIt)return;
        rs.push(rootIt);
        syncSectionsFromRoot();
        return;
    }
    if(type==="section"){rs.push(createRootItem("section"));syncSectionsFromRoot();return;}
    let s=sec(p.s)||state.layout.sections[0];
    if(!s){
        if(isStandaloneRootComponent(type)){
            const rootLoose3=createRootItem(type);
            if(!rootLoose3)return;
            rs.push(rootLoose3);
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"root",e:rootLoose3.id};
            return;
        }
        if(!isStructureComponent(type)){
            let targetSection=ensureLooseRootSection(false);
            targetSection.elements=Array.isArray(targetSection.elements)?targetSection.elements:[];
            const freeEl3=convertToFlowElement(createDefaultElement(type));
            if(!freeEl3)return;
            targetSection.elements.push(freeEl3);
            state.sel={k:"el",scope:"section",s:targetSection.id,e:freeEl3.id};
            return;
        }
        const rootIt=createRootItem(type);
        if(!rootIt)return;
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
function isStandaloneRootComponent(type){
    var t=String(type||"").toLowerCase();
    return t==="menu";
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

const dropPreview={node:null,host:null,line:null,lineHost:null};

function clearDropPreview(){
    if(dropPreview.node)dropPreview.node.style.display="none";
    if(dropPreview.line)dropPreview.line.style.display="none";
    dropPreview.host=null;
    dropPreview.lineHost=null;
}

function ensureDropPreview(host){
    if(!host)return null;
    if(getComputedStyle(host).position==="static")host.style.position="relative";
    if(!dropPreview.node||dropPreview.node.parentNode!==host){
        if(dropPreview.node&&dropPreview.node.parentNode)dropPreview.node.parentNode.removeChild(dropPreview.node);
        var n=document.createElement("div");
        n.className="fb-drop-preview";
        host.appendChild(n);
        dropPreview.node=n;
    }
    dropPreview.host=host;
    return dropPreview.node;
}

function ensureDropInsertLine(host){
    if(!host)return null;
    if(getComputedStyle(host).position==="static")host.style.position="relative";
    if(!dropPreview.line||dropPreview.line.parentNode!==host){
        if(dropPreview.line&&dropPreview.line.parentNode)dropPreview.line.parentNode.removeChild(dropPreview.line);
        var n=document.createElement("div");
        n.className="fb-drop-insert";
        host.appendChild(n);
        dropPreview.line=n;
    }
    dropPreview.lineHost=host;
    return dropPreview.line;
}

function getPreviewSize(type,host){
    var w=0,h=0;
    try{
        var probe=createDefaultElement(type);
        if(probe&&probe.style&&probe.style.width)w=parseInt(probe.style.width)||0;
        if(probe&&probe.style&&probe.style.height)h=parseInt(probe.style.height)||0;
    }catch(_e){}
    var hostW=(host&&(host.clientWidth||host.offsetWidth))||320;
    if(!w||w<=0)w=Math.min(320,Math.max(140,hostW-24));
    if(hostW>0&&w>(hostW-12))w=Math.max(60,hostW-12);
    if(!h||h<=0)h=estimateNewElementHeight(type,host.closest?host.closest(".col"):null);
    return {w:w,h:h};
}

function getFreeDropLayout(type,host,ev){
    var pos=computeFreeDropPosition(ev,host);
    var size=getPreviewSize(type,host);
    var hostW=(host&&((host.clientWidth||host.offsetWidth)||0))||0;
    var hostH=(host&&((host.clientHeight||host.offsetHeight)||0))||0;
    if(hostH>0&&size.h>(hostH-12))size.h=Math.max(40,hostH-12);
    var left=Math.round(pos.x-(size.w/2));
    var top=Math.round(pos.y-(size.h/2));
    var maxX=Math.max(0,hostW-size.w);
    var maxY=Math.max(0,hostH-size.h);
    if(left<0)left=0;
    if(top<0)top=0;
    if(left>maxX)left=maxX;
    if(top>maxY)top=maxY;
    return {
        x:pos.x,
        y:pos.y,
        snapX:!!pos.snapX,
        snapY:!!pos.snapY,
        w:size.w,
        h:size.h,
        left:left,
        top:top
    };
}

function buildFreePlacement(type,host,ev){
    var layout=getFreeDropLayout(type,host,ev);
    return {
        mode:"free",
        x:layout.x,
        y:layout.y,
        left:layout.left,
        top:layout.top,
        w:layout.w,
        h:layout.h
    };
}

function showDropPreview(type,host,ev){
    if(!type||!host){clearDropPreview();return;}
    var node=ensureDropPreview(host);
    if(!node)return;
    var layout=getFreeDropLayout(type,host,ev);
    node.style.width=layout.w+"px";
    node.style.height=layout.h+"px";
    node.style.left=layout.left+"px";
    node.style.top=layout.top+"px";
    node.style.display="block";
    if(dropPreview.line)dropPreview.line.style.display="none";
}

function showDropInsert(host,place){
    if(!host){clearDropPreview();return;}
    var line=ensureDropInsertLine(host);
    if(!line)return;
    line.style.display="block";
    line.style.top=(place==="before")?"0":"calc(100% - 3px)";
    if(dropPreview.node)dropPreview.node.style.display="none";
}

function nearestColumnNode(rowInner,clientX){
    if(!rowInner)return null;
    var cols=Array.from(rowInner.querySelectorAll(":scope > .col"));
    if(!cols.length)return null;
    var px=Number(clientX)||0;
    var nearest=cols[0],best=Infinity;
    cols.forEach(function(colEl){
        var rect=colEl.getBoundingClientRect();
        var cx=rect.left+(rect.width/2);
        var d=Math.abs(px-cx);
        if(d<best){best=d;nearest=colEl;}
    });
    return nearest;
}

function getStructureInsertHost(type,node){
    var t=String(type||"").toLowerCase();
    if(!node)return null;
    if(t==="section")return node.closest(".sec")||node;
    if(t==="row")return node.closest(".row")||node.closest(".sec")||node;
    if(t==="column")return node.closest(".col")||node.closest(".row")||node;
    return node;
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
    if(t==="section"){
        showBuilderToast("Section cannot be added inside a Column.","error");
        return false;
    }
    if(t==="menu"){
        showBuilderToast("Menu cannot be added inside a Column. Add it as a top-level component.","error");
        return false;
    }
    return true;
}

function applyFreePlacementToElement(it,freePos){
    if(!it||!freePos)return;
    var rawX=Number(freePos.x)||0;
    var rawY=Number(freePos.y)||0;
    var exactLeft=Number(freePos.left);
    var exactTop=Number(freePos.top);
    var hasExactLeft=!isNaN(exactLeft);
    var hasExactTop=!isNaN(exactTop);
    var hintedW=Number(freePos.w);
    var hintedH=Number(freePos.h);
    it.style=it.style||{};
    it.settings=it.settings||{};
    var elW=hintedW>0?hintedW:(parseInt(it.style.width)||250);
    var elH=hintedH>0?hintedH:(parseInt(it.style.height)||50);
    var x=hasExactLeft?Math.max(0,Math.round(exactLeft)):Math.max(0,Math.round(rawX-elW/2));
    var y=hasExactTop?Math.max(0,Math.round(exactTop)):Math.max(0,Math.round(rawY-elH/2));
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

/* â”€â”€â”€ Z-order / layering helpers â”€â”€â”€ */
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

/* â”€â”€â”€ Universal resize handles for all elements â”€â”€â”€ */
function attachElResizeHandles(w,item){
    if(!w||!item)return;
    function parsePx(v){var n=Number(String(v||"0").replace("px","").trim());return isNaN(n)?0:n;}
    function clamp(n,mn,mx){return Math.max(mn,Math.min(mx,n));}
    var isCorner=function(h){return h==="nw"||h==="ne"||h==="sw"||h==="se";};
    var isScalable=function(){return item.type==="heading"||item.type==="text"||item.type==="icon";};
    var scaleAdvanced=isAdvancedScaleComponent(item.type);
    function startResize(handle,e){
        e.preventDefault();
        e.stopPropagation();
        _autoSavePaused=true;
        var host=w.parentElement;
        if(!host)return;
        var hostRect=host.getBoundingClientRect();
        var hostW=hostRect?hostRect.width:0;
        var hostH=hostRect?hostRect.height:0;
        var rect=w.getBoundingClientRect();
        var hadWidth=!!(item.style&&String(item.style.width||"").trim()!=="");
        var hadHeight=!!(item.style&&String(item.style.height||"").trim()!=="");
        var startW=Math.max(30,rect.width||parsePx(item.style.width)||200);
        var startH=Math.max(20,rect.height||parsePx(item.style.height)||100);
        var wasAbsolute=((item.settings&&item.settings.positionMode)==="absolute"||String((item.style&&item.style.position)||"").toLowerCase()==="absolute");
        var hasLeft=!!(item.style&&String(item.style.left||"").trim()!=="");
        var hasTop=!!(item.style&&String(item.style.top||"").trim()!=="");
        var startX=hasLeft?parsePx(item.style.left):(Number(item.settings&&item.settings.freeX)||0);
        var startY=hasTop?parsePx(item.style.top):(Number(item.settings&&item.settings.freeY)||0);
        if((!hasLeft || !hasTop) && hostRect){
            if(!hasLeft)startX=Math.round(rect.left-hostRect.left);
            if(!hasTop)startY=Math.round(rect.top-hostRect.top);
        }
        var ratio=(startH>0)?(startW/startH):1;
        var startScale=scaleAdvanced?getContentScale(item):1;
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
        var lockRatio=isCorner(handle)&&(item.type==="carousel");
        var doScale=isCorner(handle)&&isScalable();
        var affectsW=(handle==="e"||handle==="w"||isCorner(handle));
        var affectsH=(handle==="n"||handle==="s"||isCorner(handle));
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dx=ev.clientX-sx,dy=ev.clientY-sy;
            var nw=startW,nh=startH,nx=startX,ny=startY;
            if(handle==="se"){
                if(lockRatio){nw=clamp(startW+dx,30,2400);nh=clamp(nw/ratio,20,1800);}
                else{nw=clamp(startW+dx,30,2400);nh=clamp(startH+dy,20,1800);}
            }
            else if(handle==="ne"){
                if(lockRatio){nw=clamp(startW+dx,30,2400);nh=clamp(nw/ratio,20,1800);ny=startY+(startH-nh);}
                else{nw=clamp(startW+dx,30,2400);nh=clamp(startH-dy,20,1800);ny=startY+(startH-nh);}
            }
            else if(handle==="sw"){
                if(lockRatio){nw=clamp(startW-dx,30,2400);nh=clamp(nw/ratio,20,1800);nx=startX+(startW-nw);}
                else{nw=clamp(startW-dx,30,2400);nh=clamp(startH+dy,20,1800);nx=startX+(startW-nw);}
            }
            else if(handle==="nw"){
                if(lockRatio){nw=clamp(startW-dx,30,2400);nh=clamp(nw/ratio,20,1800);nx=startX+(startW-nw);ny=startY+(startH-nh);}
                else{nw=clamp(startW-dx,30,2400);nh=clamp(startH-dy,20,1800);nx=startX+(startW-nw);ny=startY+(startH-nh);}
            }
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
            if(scaleAdvanced&&startW>0&&startH>0){
                var wr=nw/startW;
                var hr=nh/startH;
                var scaleFactor=1;
                if(handle==="e"||handle==="w")scaleFactor=wr;
                else if(handle==="n"||handle==="s")scaleFactor=hr;
                else scaleFactor=Math.max(wr,hr);
                item.settings.contentScale=clamp(startScale*scaleFactor,0.5,3);
                applyAdvancedScaleToNode(w,item,item.settings.contentScale);
            }
            if(wasAbsolute){
                item.style.position="absolute";
                item.settings.positionMode="absolute";
                item.style.left=Math.round(nx)+"px";
                item.style.top=Math.round(ny)+"px";
                item.settings.freeX=Math.round(nx);
                item.settings.freeY=Math.round(ny);
                w.style.position="absolute";
                w.style.left=item.style.left;
                w.style.top=item.style.top;
            }else{
                if(item.style&&Object.prototype.hasOwnProperty.call(item.style,"position"))delete item.style.position;
                if(item.style&&Object.prototype.hasOwnProperty.call(item.style,"left"))delete item.style.left;
                if(item.style&&Object.prototype.hasOwnProperty.call(item.style,"top"))delete item.style.top;
                if(item.settings){
                    if(Object.prototype.hasOwnProperty.call(item.settings,"positionMode"))delete item.settings.positionMode;
                    if(Object.prototype.hasOwnProperty.call(item.settings,"freeX"))delete item.settings.freeX;
                    if(Object.prototype.hasOwnProperty.call(item.settings,"freeY"))delete item.settings.freeY;
                }
                w.style.position="";
                w.style.left="";
                w.style.top="";
            }
            if(affectsW || hadWidth){
                item.style.width=Math.round(nw)+"px";
                w.style.width=item.style.width;
            }
            if((affectsH || hadHeight) && !isScalable()){
                item.style.height=Math.round(nh)+"px";
                w.style.height=item.style.height;
            }
            if(doScale&&startW>0){
                var wr=nw/startW;
                var hr=startH>0?(nh/startH):wr;
                var scale=(handle==="e"||handle==="w")?wr:((handle==="n"||handle==="s")?hr:Math.max(wr,hr));
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
                if(item.style&&Object.prototype.hasOwnProperty.call(item.style,"height"))delete item.style.height;
                w.style.height="auto";
            }
            if(scaleAdvanced)syncAdvancedElementHeight(w,item);
            if(!wasAbsolute){
                syncFlowContainerMinHeights(w,nh);
            }
            drawLinkWires();
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

function getNodeContentMinHeight(containerNode){
    if(!containerNode)return 0;
    var minNeeded=0;
    Array.from(containerNode.children||[]).forEach(function(child){
        if(!child)return;
        if(child.classList&&(
            child.classList.contains("section-resize-handle-y")
            || child.classList.contains("row-resize-handle-y")
        ))return;
        var mb=0;
        try{
            mb=parseFloat((window.getComputedStyle(child).marginBottom)||"0")||0;
        }catch(_e){mb=0;}
        var bottom=(Number(child.offsetTop)||0)+(Number(child.offsetHeight)||0)+mb;
        if(bottom>minNeeded)minNeeded=bottom;
    });
    var padTop=0,padBottom=0;
    try{
        var css=window.getComputedStyle(containerNode);
        padTop=parseFloat(css.paddingTop||"0")||0;
        padBottom=parseFloat(css.paddingBottom||"0")||0;
    }catch(_e){}
    return Math.max(0,Math.round(minNeeded+padTop+padBottom));
}

function syncFlowContainerMinHeights(node,preferredHeight){
    if(!node)return;
    var flowCol=node.closest(".col");
    var flowRow=node.closest(".row");
    var flowSec=node.closest(".sec");
    if(flowCol){
        flowCol.style.minHeight=Math.max(58,Number(preferredHeight)||0,getNodeContentMinHeight(flowCol))+"px";
    }
    if(flowRow){
        requestAnimationFrame(function(){
            flowRow.style.minHeight=Math.max(58,getNodeContentMinHeight(flowRow))+"px";
        });
    }
    if(flowSec){
        requestAnimationFrame(function(){
            applySectionMinHeight(flowSec,Math.max(80,getSectionContentMinHeight(flowSec)));
        });
    }
}

function applySectionMinHeight(sectionNode,nextMin){
    if(!sectionNode)return;
    var value=Math.max(80,Number(nextMin)||0)+"px";
    sectionNode.style.minHeight=value;
    var inner=sectionNode.querySelector(".sec-inner");
    if(inner)inner.style.minHeight=value;
    var secId=String(sectionNode.getAttribute("data-sec-id")||"");
    var sObj=secId?sec(secId):null;
    if(sObj){
        sObj.style=sObj.style||{};
        sObj.style.minHeight=value;
    }
}

/* â”€â”€â”€ Canva-style drag-to-move system â”€â”€â”€ */
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
    var elCx=newX+elW/2, elCy=newY+elH/2;
    var elRight=newX+elW, elBottom=newY+elH;
    var bestX={dist:SNAP_THRESHOLD+1,pos:null,guides:[],label:""};
    var bestY={dist:SNAP_THRESHOLD+1,pos:null,guides:[],label:""};

    function tryX(current,target,finalPos,guides,label){
        var d=Math.abs(current-target);
        if(d<=SNAP_THRESHOLD&&d<bestX.dist){
            bestX={dist:d,pos:finalPos,guides:(guides||[]),label:String(label||"").trim()};
        }
    }

    function tryY(current,target,finalPos,guides,label){
        var d=Math.abs(current-target);
        if(d<=SNAP_THRESHOLD&&d<bestY.dist){
            bestY={dist:d,pos:finalPos,guides:(guides||[]),label:String(label||"").trim()};
        }
    }

    var hostCx=hostW/2, hostCy=hostH/2;
    tryX(elCx,hostCx,hostCx-elW/2,[hostCx],"Canvas center");
    tryY(elCy,hostCy,hostCy-elH/2,[hostCy],"Canvas center");

    siblings.forEach(function(sib){
        tryX(elCx,sib.cx,sib.cx-elW/2,[sib.cx],"Align center");
        tryX(newX,sib.left,sib.left,[sib.left],"Align left");
        tryX(elRight,sib.right,sib.right-elW,[sib.right],"Align right");
        tryX(newX,sib.right,sib.right,[sib.right],"Snap to edge");
        tryX(elRight,sib.left,sib.left-elW,[sib.left],"Snap to edge");

        tryY(elCy,sib.cy,sib.cy-elH/2,[sib.cy],"Align middle");
        tryY(newY,sib.top,sib.top,[sib.top],"Align top");
        tryY(elBottom,sib.bottom,sib.bottom-elH,[sib.bottom],"Align bottom");
        tryY(newY,sib.bottom,sib.bottom,[sib.bottom],"Snap to edge");
        tryY(elBottom,sib.top,sib.top-elH,[sib.top],"Snap to edge");
    });

    for(var i=0;i<siblings.length;i++){
        for(var j=i+1;j<siblings.length;j++){
            var pair=siblings[i],other=siblings[j];
            var midCx=(pair.cx+other.cx)/2;
            var midCy=(pair.cy+other.cy)/2;
            tryX(elCx,midCx,midCx-elW/2,[midCx],"Center between");
            tryY(elCy,midCy,midCy-elH/2,[midCy],"Center between");
        }
    }

    return {
        x:bestX.pos,
        y:bestY.pos,
        guidesV:bestX.guides,
        guidesH:bestY.guides,
        labelV:bestX.label,
        labelH:bestY.label
    };
}

function startElDrag(e,w,item,ctx){
    if(state.linkPick && state.linkPick.type==="pricing") return;
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
    drawLinkWires();
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
/* â”€â”€â”€ end drag-to-move system â”€â”€â”€ */

function addComponentAt(type,target,place){
    var freePlacement=(place&&typeof place==="object"&&place.mode==="free")?place:null;
    var placeInside=(place==="inside")||!!freePlacement;
    place=place==="before"?"before":"after";
    saveToHistory();
    var t=target||{};
    ensureRootModel();
    const rs=rootItems();
    state.layout.sections=Array.isArray(state.layout.sections)?state.layout.sections:[];
    if(!canAddComponentNow(type))return false;
    function convertToFlowElement(node){
        if(!node)return node;
        node.style=node.style||{};
        node.settings=node.settings||{};
        ["position","left","top","right","bottom","zIndex"].forEach(function(key){
            if(Object.prototype.hasOwnProperty.call(node.style,key))delete node.style[key];
        });
        ["positionMode","freeX","freeY"].forEach(function(key){
            if(Object.prototype.hasOwnProperty.call(node.settings,key))delete node.settings[key];
        });
        return node;
    }
    var isColumnContextTarget=(
        t
        && (
            t.k==="col"
            || t.k==="row"
            || (t.k==="el" && t.scope!=="section")
            || (t.k==="el" && !!t.c)
        )
    );
    if(!isStructureComponent(type)&&!isStandaloneRootComponent(type)&&!isColumnContextTarget){
        var targetSection=(t&&t.s)?sec(t.s):null;
        if(!targetSection||targetSection.__rootWrap||targetSection.__freeformCanvas){
            targetSection=findLooseRootSection();
        }
        if(!targetSection){
            showBuilderToast("Add a Section first before placing this component.","error");
            return false;
        }
        targetSection.elements=Array.isArray(targetSection.elements)?targetSection.elements:[];
        var freeEl=convertToFlowElement(createDefaultElement(type));
        if(!freeEl)return false;
        if(freePlacement)applyFreePlacementToElement(freeEl,freePlacement);
        else autoPlaceElement(freeEl,{elements:targetSection.elements});
        targetSection.elements.push(freeEl);
        state.sel={k:"el",scope:"section",s:targetSection.id,e:freeEl.id};
        return true;
    }
    if(!t||!t.k){
        if(isStandaloneRootComponent(type)){
            var rootLoose=createRootItem(type);
            if(!rootLoose)return false;
            rs.push(rootLoose);
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"root",e:rootLoose.id};
            return true;
        }
        if(!isStructureComponent(type)){
            var emptyTargetSection=freePlacement
                ? ensureLooseRootSection(true)
                : ensureLooseRootSection(false);
            emptyTargetSection.elements=Array.isArray(emptyTargetSection.elements)?emptyTargetSection.elements:[];
            var emptyFlowEl=convertToFlowElement(createDefaultElement(type));
            if(!emptyFlowEl)return false;
            emptyTargetSection.elements.push(emptyFlowEl);
            state.sel={k:"el",scope:"section",s:emptyTargetSection.id,e:emptyFlowEl.id};
            return true;
        }
        var rootNew=createRootItem(type);
        if(!rootNew)return false;
        rs.push(rootNew);
        syncSectionsFromRoot();
        return true;
    }
    var tRootCtx=sectionRootContext(t.s);
    var isNestedGridTarget=(t.k==="row"||t.k==="col"||(t.k==="el"&&!!t.c));
    if((tRootCtx.isWrap||tRootCtx.isFreeform)&&tRootCtx.index>=0&&!isNestedGridTarget){
        if(isStandaloneRootComponent(type)){
            var wrapLoose=createRootItem(type);
            if(!wrapLoose)return false;
            var wrapLooseIdx=(place==="before"?tRootCtx.index:tRootCtx.index+1);
            rs.splice(Math.max(0,Math.min(wrapLooseIdx,rs.length)),0,wrapLoose);
            syncSectionsFromRoot();
            state.sel={k:"el",scope:"root",e:wrapLoose.id};
            return true;
        }
        if(!isStructureComponent(type)){
            var wrapTargetSection=ensureLooseRootSection(true,tRootCtx.index+1);
            wrapTargetSection.elements=Array.isArray(wrapTargetSection.elements)?wrapTargetSection.elements:[];
            var wrapFlowEl=convertToFlowElement(createDefaultElement(type));
            if(!wrapFlowEl)return false;
            wrapTargetSection.elements.push(wrapFlowEl);
            state.sel={k:"el",scope:"section",s:wrapTargetSection.id,e:wrapFlowEl.id};
            return true;
        }
        var wrapInsert=createRootItem(type);
        if(!wrapInsert)return false;
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
    var linkedTargetIds=Array.isArray(state._linkTargetIds)?state._linkTargetIds.map(v=>String(v||"")):[];
    if(item.type==="pricing"){
        if(state.linkPick&&state.linkPick.type==="pricing")w.classList.add("el--link-candidate");
        if(linkedTargetIds.indexOf(String(item.id||""))>=0)w.classList.add("el--link-target");
    }
    if(item.type==="countdown"&&state.linkPick&&String(item.id||"")===String(state.linkPick.sourceId||""))w.classList.add("el--link-source");
    w.onmousedown=function(e){e.stopPropagation();startElDrag(e,w,item,ctx);};
    w.onclick=e=>{
        e.stopPropagation();
        if(state.linkPick&&state.linkPick.type==="pricing"){
            e.preventDefault();
            if(item.type!=="pricing"){
                showBuilderToast("Select a pricing component to link.","error");
                return;
            }
            var src=findElementById(state.linkPick.sourceId);
            if(!src||src.type!=="countdown"){
                state.linkPick=null;
                renderCanvas();
                showBuilderToast("Countdown link source not found.","error");
                return;
            }
            saveToHistory();
            var idStr=String(item.id||"");
            var ids=getLinkedPricingIds(src);
            var idx=ids.indexOf(idStr);
            if(idx>=0){
                ids.splice(idx,1);
                setLinkedPricingIds(src,ids);
                showBuilderToast("Unlinked countdown from pricing.","success");
            }else{
                ids.push(idStr);
                setLinkedPricingIds(src,ids);
                // Auto-fill pricing promo key from countdown promo key.
                // If countdown has no promo key, generate one automatically (do NOT overwrite manual values).
                var srcPromo=String((src.settings&&src.settings.promoKey)||"").trim();
                if(srcPromo===""){
                    var taken=new Set();
                    collectElementsByType("countdown").forEach(function(cd){
                        var k=String(cd&&cd.settings&&cd.settings.promoKey||"").trim();
                        if(k!=="")taken.add(k);
                    });
                    collectElementsByType("pricing").forEach(function(pr){
                        var k=String(pr&&pr.settings&&pr.settings.promoKey||"").trim();
                        if(k!=="")taken.add(k);
                    });
                    var base=("promo_"+String(src.id||"")).replace(/[^a-zA-Z0-9_-]+/g,"_").slice(0,60);
                    if(base==="")base="promo";
                    var candidate=base;
                    var n=2;
                    while(taken.has(candidate)){
                        candidate=(base+"_"+n).slice(0,60);
                        n++;
                        if(n>999)break;
                    }
                    srcPromo=candidate;
                    src.settings=src.settings||{};
                    src.settings.promoKey=srcPromo;
                }
                item.settings=item.settings||{};
                var curPromo=String(item.settings.promoKey||"").trim();
                if(curPromo===""){
                    item.settings.promoKey=srcPromo;
                }
                showBuilderToast("Linked countdown to pricing.","success");
            }
            renderCanvas();
            renderSettings();
            return;
        }
        if(!elDrag.active&&!elDrag.justFinished){
            if(state.editingEl&&state.editingEl!==item.id){
                state.editingEl=null;
                var oe=document.querySelector(".el--editing");
                if(oe){
                    oe.classList.remove("el--editing");
                    var ce=oe.querySelector("[data-editable]");
                    if(ce){
                        ce.contentEditable="false";
                        ce.style.cursor="move";
                    }
                }
            }
            state.carouselSel=null;
            state.sel=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};
            renderSettings();
            if(state.sel)showLeftPanel("settings");
        }
    };
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
        const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
        if(!t){
            clearFreeDropGuides();
            clearDropPreview();
            return;
        }
        if(isStructureComponent(t)){
            // Let section/row/column handlers manage structure drops to avoid flicker.
            return;
        }
        e.stopPropagation();
        var freeHost=w.parentElement||null;
        showFreeDropGuides(e,freeHost);
        showDropPreview(t,freeHost,e);
    };
    w.ondragleave=e=>{
        const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
        if(isStructureComponent(t)){
            return;
        }
        e.stopPropagation();
        if(!w.contains(e.relatedTarget)){
            clearFreeDropGuides();
            clearDropPreview();
        }
    };
    w.ondrop=e=>{
        e.preventDefault();
        if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;
        const t=e.dataTransfer.getData("c");
        if(!t)return;
        if(isStructureComponent(t)){
            // Allow parent handlers to process section/row/column drops.
            return;
        }
        e.stopPropagation();
        state.carouselSel=null;
        var dropTarget=ctx.scope==="section"?{k:"el",scope:"section",s:ctx.s,e:item.id}:{k:"el",s:ctx.s,r:ctx.r,c:ctx.c,e:item.id};
        var place;
        var host=w.parentElement||w;
        place=buildFreePlacement(t,host,e);
        dropTarget=ctx.scope==="section"?{k:"sec",s:ctx.s}:{k:"col",s:ctx.s,r:ctx.r,c:ctx.c};
        clearFreeDropGuides();
        clearDropPreview();
        if(addComponentAt(t,dropTarget,place))render();
    };
    if(item.type==="heading"||item.type==="text"){const n=document.createElement(item.type==="heading"?"h2":"div");n.setAttribute("data-editable","1");n.contentEditable=isEditing?"true":"false";n.style.margin="0";n.style.overflowWrap="break-word";n.style.wordBreak="break-word";n.style.maxWidth="100%";n.style.cursor=isEditing?"text":"move";n.innerHTML=item.content||"";contentStyleApply(n,item.style||{});if(!(item.style&&item.style.color))n.style.color="#000000";n.oninput=()=>{item.content=n.innerHTML||"";queueAutoSave();};onRichTextKeys(n,()=>{item.content=n.innerHTML||"";queueAutoSave();});w.appendChild(n);}
    else if(item.type==="button"){
        var wb=(item.settings&&item.settings.widthBehavior)||"fluid",al=(item.settings&&item.settings.alignment)||((item.style&&item.style.textAlign)||"center");
        var wrapBg=(item.settings&&item.settings.containerBgColor)||"";
        var hasManualWidth=!!(item.style&&String(item.style.width||"").trim()!=="");
        var hasManualHeight=!!(item.style&&String(item.style.height||"").trim()!=="");
        w.classList.add(wb==="fill"?"el--button-fill":"el--button");
        w.style.display="flex";w.style.justifyContent=al==="right"?"flex-end":al==="center"?"center":"flex-start";
        w.style.alignItems=hasManualHeight?"stretch":"center";
        w.style.backgroundColor=wrapBg||"";
        if(hasManualWidth)w.style.width=String(item.style.width);
        if(hasManualHeight)w.style.height=String(item.style.height);
        const b=document.createElement("button");b.type="button";b.setAttribute("data-editable","1");b.contentEditable=isEditing?"true":"false";b.style.cursor=isEditing?"text":"move";b.innerHTML=item.content||"Button";
        contentStyleApply(b,item.style||{});b.style.border="none";b.style.display="flex";b.style.width=(wb==="fill"||hasManualWidth)?"100%":"auto";b.style.height=hasManualHeight?"100%":"auto";b.style.boxSizing="border-box";b.style.alignItems="center";b.style.justifyContent="center";if(!(item.style&&item.style.backgroundColor))b.style.backgroundColor="#240E35";if(!(item.style&&item.style.color))b.style.color="#fff";if(!(item.style&&item.style.padding))b.style.padding="10px 18px";if(!(item.style&&item.style.borderRadius))b.style.borderRadius="999px";
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
        var hasFixedW=!!(item.style&&String(item.style.width||"").trim()!=="");
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
                if(hasFixedW)img.style.width="100%";
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
            w.innerHTML='<div class="fb-image-placeholder" style="min-height:120px;"><div class="fb-image-placeholder__center"><div class="fb-image-placeholder__plus">+</div></div><div class="fb-image-placeholder__label">Loading image...</div><div class="el-loading-overlay"><div class="el-loading-spinner"></div></div></div>';
        }else{
            w.innerHTML="";
            var ph=document.createElement("div");
            ph.className="fb-image-placeholder fb-image-placeholder--actionable";
            var center=document.createElement("div");
            center.className="fb-image-placeholder__center";
            var plusBtn=document.createElement("button");
            plusBtn.type="button";
            plusBtn.className="fb-image-placeholder__plus";
            plusBtn.setAttribute("aria-label","Choose image action");
            plusBtn.textContent="+";
            var label=document.createElement("div");
            label.className="fb-image-placeholder__label";
            label.textContent="Click to upload image";
            var menu=document.createElement("div");
            menu.className="fb-image-placeholder__menu";
            menu.hidden=true;
            var uploadBtn=document.createElement("button");
            uploadBtn.type="button";
            uploadBtn.textContent="Upload image";
            var libraryBtn=document.createElement("button");
            libraryBtn.type="button";
            libraryBtn.textContent="Choose from library";
            center.appendChild(plusBtn);
            ph.appendChild(center);
            ph.appendChild(label);
            menu.appendChild(uploadBtn);
            menu.appendChild(libraryBtn);
            ph.appendChild(menu);
            [plusBtn,menu,uploadBtn,libraryBtn].forEach(function(node){
                node.addEventListener("mousedown",function(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                });
            });
            var openPicker=function(ev){
                if(ev){
                    ev.preventDefault();
                    ev.stopPropagation();
                }
                openImagePlaceholderMenu(item,ph,menu);
            };
            plusBtn.addEventListener("click",openPicker);
            uploadBtn.addEventListener("click",function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                menu.hidden=true;
                promptImageUploadForElement(item, ph);
            });
            libraryBtn.addEventListener("click",function(ev){
                ev.preventDefault();
                ev.stopPropagation();
                menu.hidden=true;
                openAssetLibraryModal({
                    kind:"image",
                    title:"Image Asset Library",
                    subtitle:"Reuse stored images or upload a new one.",
                    onSelect:function(url){
                        saveToHistory();
                        applyUploadedImageToElement(item,ph,url);
                    }
                });
            });
            w.appendChild(ph);
        }
    }
    else if(item.type==="form"||item.type==="shipping_details"){
        item.settings=item.settings||{};
        var isShippingDetails=item.type==="shipping_details";
        var fal=(item.settings.alignment)||"left";
        var fw=(item.style&&item.style.width)||(item.settings&&item.settings.width)||"100%";
        var hasFormHeight=!!(item.style&&String(item.style.height||"").trim()!=="");
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
        formBox.style.display="grid";
        formBox.style.alignContent="start";
        if(hasFormHeight)formBox.style.height="100%";
        if(isShippingDetails){
            var shipHeading=document.createElement("div");
            shipHeading.className="fb-shipping-heading";
            shipHeading.textContent=String(item.settings.heading||"Shipping Details");
            shipHeading.style.fontSize="20px";
            shipHeading.style.fontWeight="800";
            shipHeading.style.marginBottom="6px";
            shipHeading.style.color="#240E35";
            formBox.appendChild(shipHeading);
            var shipSubtitle=String(item.settings.subtitle||"").trim();
            if(shipSubtitle!==""){
                var shipSubtitleEl=document.createElement("div");
                shipSubtitleEl.className="fb-shipping-subtitle";
                shipSubtitleEl.textContent=shipSubtitle;
                shipSubtitleEl.style.fontSize="12px";
                shipSubtitleEl.style.lineHeight="1.5";
                shipSubtitleEl.style.color="#64748b";
                shipSubtitleEl.style.marginBottom="10px";
                formBox.appendChild(shipSubtitleEl);
            }
        }
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
        if(!isShippingDetails){
            var btnWrap=document.createElement("div");
            btnWrap.style.display="flex";
            btnWrap.style.justifyContent=fbtnJustify;
            btnWrap.style.marginTop="4px";
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
        }
        w.innerHTML="";
        w.appendChild(formBox);
        var neededFormHeight=Math.ceil(formBox.scrollHeight||formBox.getBoundingClientRect().height||0);
        if(neededFormHeight>0){
            if(hasFormHeight){
                var currentFormHeight=parsePxVal(item.style&&item.style.height);
                if(currentFormHeight<neededFormHeight){
                    item.style.height=neededFormHeight+"px";
                }
                w.style.height=String(item.style.height||neededFormHeight+"px");
            }else{
                if(item.style&&Object.prototype.hasOwnProperty.call(item.style,"height"))delete item.style.height;
                w.style.height=neededFormHeight+"px";
            }
        }
    }
    else if(item.type==="testimonial"){
        item.settings=item.settings||{};
        var quote=String(item.settings.quote||item.content||"Testimonial quote");
        var name=String(item.settings.name||"Customer name");
        var role=String(item.settings.role||"");
        var avatar=String(item.settings.avatar||"");
        var customColor=(item.style&&item.style.color)?String(item.style.color):"";
        var card=document.createElement("div");
        card.className="fb-testimonial";
        var q=document.createElement("div");
        q.className="fb-testimonial-quote";
        q.textContent=quote;
        if(customColor)q.style.color=customColor;
        card.appendChild(q);
        var author=document.createElement("div");
        author.className="fb-testimonial-author";
        if(avatar){
            var img=document.createElement("img");
            img.className="fb-testimonial-avatar";
            img.src=avatar;
            img.alt=name||"Avatar";
            author.appendChild(img);
        }
        var meta=document.createElement("div");
        var nm=document.createElement("div");
        nm.className="fb-testimonial-name";
        nm.textContent=name||"Customer name";
        if(customColor)nm.style.color=customColor;
        meta.appendChild(nm);
        if(role){
            var rl=document.createElement("div");
            rl.className="fb-testimonial-role";
            rl.textContent=role;
            if(customColor){rl.style.color=customColor;rl.style.opacity="0.7";}
            meta.appendChild(rl);
        }
        author.appendChild(meta);
        card.appendChild(author);
        w.appendChild(card);
    }
    else if(item.type==="review_form"){
        item.settings=item.settings||{};
        var reviewHeading=String(item.settings.heading||"How was your experience?");
        var reviewSubtitle=String(item.settings.subtitle||"Share a quick review after your order or service experience.");
        var reviewButton=String(item.settings.buttonLabel||"Submit Review").trim()||"Submit Review";
        var reviewPublicLabel=String(item.settings.publicLabel||"I am okay with showing this review publicly.");
        var reviewCard=document.createElement("div");
        reviewCard.className="fb-review-form";
        var reviewTitle=document.createElement("div");
        reviewTitle.className="fb-review-form-title";
        reviewTitle.textContent=reviewHeading;
        var reviewSub=document.createElement("div");
        reviewSub.className="fb-review-form-subtitle";
        reviewSub.textContent=reviewSubtitle;
        var reviewStars=document.createElement("div");
        reviewStars.className="fb-review-stars";
        reviewStars.innerHTML='<span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>';
        var reviewName=document.createElement("input");
        reviewName.className="fb-review-input";
        reviewName.placeholder="Your name";
        reviewName.disabled=true;
        var reviewEmail=document.createElement("input");
        reviewEmail.className="fb-review-input";
        reviewEmail.placeholder="Email address";
        reviewEmail.disabled=true;
        var reviewText=document.createElement("textarea");
        reviewText.className="fb-review-textarea";
        reviewText.placeholder="Write a quick review...";
        reviewText.disabled=true;
        var reviewCheck=document.createElement("label");
        reviewCheck.className="fb-review-check";
        reviewCheck.innerHTML='<input type="checkbox" disabled> <span>'+reviewPublicLabel.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span>';
        var reviewBtn=document.createElement("button");
        reviewBtn.type="button";
        reviewBtn.className="fb-pricing-cta";
        reviewBtn.textContent=reviewButton;
        reviewBtn.disabled=true;
        reviewBtn.style.background=String(item.settings.ctaBgColor||"#240E35");
        reviewBtn.style.color=String(item.settings.ctaTextColor||"#ffffff");
        reviewCard.appendChild(reviewTitle);
        reviewCard.appendChild(reviewSub);
        reviewCard.appendChild(reviewStars);
        reviewCard.appendChild(reviewName);
        reviewCard.appendChild(reviewEmail);
        reviewCard.appendChild(reviewText);
        reviewCard.appendChild(reviewCheck);
        reviewCard.appendChild(reviewBtn);
        w.appendChild(reviewCard);
    }
    else if(item.type==="reviews"){
        item.settings=item.settings||{};
        var listHeading=String(item.settings.heading||"What customers are saying");
        var listSubtitle=String(item.settings.subtitle||"Approved reviews from this funnel appear here automatically.");
        var listLayout=String(item.settings.layout||"list").toLowerCase()==="grid"?"grid":"list";
        var listShowRating=item.settings.showRating!==false;
        var listShowDate=item.settings.showDate===true;
        var reviewsShell=document.createElement("div");
        reviewsShell.style.width="100%";
        reviewsShell.style.height="100%";
        reviewsShell.style.minWidth="0";
        reviewsShell.style.minHeight="0";
        reviewsShell.style.display="grid";
        reviewsShell.style.alignContent="start";
        reviewsShell.style.gap="12px";
        reviewsShell.style.overflow="hidden";
        var listWrap=document.createElement("div");
        listWrap.className="fb-review-list "+listLayout;
        listWrap.style.minWidth="0";
        listWrap.style.alignContent="start";
        var listHeader=document.createElement("div");
        listHeader.className="fb-review-form";
        var listHeaderTitle=document.createElement("div");
        listHeaderTitle.className="fb-review-form-title";
        listHeaderTitle.textContent=listHeading;
        var listHeaderSub=document.createElement("div");
        listHeaderSub.className="fb-review-form-subtitle";
        listHeaderSub.textContent=listSubtitle;
        listHeader.appendChild(listHeaderTitle);
        listHeader.appendChild(listHeaderSub);
        reviewsShell.appendChild(listHeader);
        [{name:"Maria Dela Cruz",text:"Fast checkout and very smooth experience.",rating:5,date:"Approved review"},{name:"John Reyes",text:"Clear process and easy to follow from start to finish.",rating:4,date:"Approved review"}].forEach(function(sample){
            var card=document.createElement("div");
            card.className="fb-review-card";
            var head=document.createElement("div");
            head.className="fb-review-card-head";
            var meta=document.createElement("div");
            var name=document.createElement("div");
            name.className="fb-review-card-name";
            name.textContent=sample.name;
            meta.appendChild(name);
            if(listShowDate){
                var date=document.createElement("div");
                date.className="fb-review-card-date";
                date.textContent=sample.date;
                meta.appendChild(date);
            }
            head.appendChild(meta);
            if(listShowRating){
                var stars=document.createElement("div");
                stars.className="fb-review-card-stars";
                stars.textContent="★★★★★".slice(0,sample.rating)+"☆☆☆☆☆".slice(0,5-sample.rating);
                head.appendChild(stars);
            }
            var text=document.createElement("div");
            text.className="fb-review-card-text";
            text.textContent=sample.text;
            card.appendChild(head);
            card.appendChild(text);
            listWrap.appendChild(card);
        });
        reviewsShell.appendChild(listWrap);
        w.appendChild(reviewsShell);
    }
    else if(item.type==="faq"){
        item.settings=item.settings||{};
        item.settings.items=normalizeFaqItems(item.settings.items);
        var list=item.settings.items||[];
        var gap=Number(item.settings.itemGap);if(isNaN(gap)||gap<0)gap=10;
        var faqWrap=document.createElement("div");
        faqWrap.className="fb-faq";
        faqWrap.style.gap=gap+"px";
        var faqAlign=(item.settings&&item.settings.alignment)||(item.style&&item.style.textAlign)||"left";
        faqWrap.style.textAlign=faqAlign;
        list.forEach(function(it){
            var row=document.createElement("div");
            row.className="fb-faq-item";
            var qn=document.createElement("div");
            qn.className="fb-faq-q";
            qn.textContent=String(it.q||"Question");
            if(item.settings.questionColor)qn.style.color=String(item.settings.questionColor);
            var an=document.createElement("div");
            an.className="fb-faq-a";
            an.textContent=String(it.a||"Answer");
            if(item.settings.answerColor)an.style.color=String(item.settings.answerColor);
            row.appendChild(qn);
            row.appendChild(an);
            faqWrap.appendChild(row);
        });
        w.appendChild(faqWrap);
    }
    else if(item.type==="pricing"){
        item.settings=item.settings||{};
        item.settings.features=normalizeFeatureList(item.settings.features);
        var activeStepForPricing=cur();
        var pricingStepType=normalizeTemplateType((activeStepForPricing&&activeStepForPricing.type)||"custom");
        var plan=String(item.settings.plan||"Plan");
        var salePrice=normalizeTemplateCurrencyValue(item.settings.price||"");
        var regularPrice=normalizeTemplateCurrencyValue(item.settings.regularPrice||"");
        var price=(salePrice!==""?salePrice:(regularPrice!==""?regularPrice:"0"));
        var period=String(item.settings.period||"");
        var subtitle=String(item.settings.subtitle||"");
        var badge=String(item.settings.badge||"");
        var ctaLink=String(item.settings.ctaLink||"#");
        var ctaLabelRaw=(typeof item.settings.ctaLabel==="string")?String(item.settings.ctaLabel).trim():"";
        var ctaLabel=pricingStepType==="checkout"
            ?"Pay Now"
            :(ctaLabelRaw!==""?ctaLabelRaw:(pricingStepType==="sales"&&plan!==""?("Choose "+plan):"Get Started"));
        var ctaBg=String(item.settings.ctaBgColor||"#240E35");
        var ctaText=String(item.settings.ctaTextColor||"#ffffff");
        var customColor2=(item.style&&item.style.color)?String(item.style.color):"";
        var pricing=document.createElement("div");
        pricing.className="fb-pricing";
        if(badge){
            var bd=document.createElement("div");
            bd.className="fb-pricing-badge";
            bd.textContent=badge;
            pricing.appendChild(bd);
        }
        var title=document.createElement("div");
        title.className="fb-pricing-title";
        title.textContent=plan;
        if(customColor2)title.style.color=customColor2;
        pricing.appendChild(title);
        var priceRow=document.createElement("div");
        var priceVal=document.createElement("span");
        priceVal.className="fb-pricing-price";
        priceVal.textContent=price;
        if(customColor2)priceVal.style.color=customColor2;
        priceRow.appendChild(priceVal);
        if(period){
            var per=document.createElement("span");
            per.className="fb-pricing-period";
            per.textContent=period;
            if(customColor2){per.style.color=customColor2;per.style.opacity="0.7";}
            priceRow.appendChild(per);
        }
        pricing.appendChild(priceRow);
        if(subtitle){
            var sub=document.createElement("div");
            sub.className="fb-pricing-subtitle";
            sub.textContent=subtitle;
            if(customColor2){sub.style.color=customColor2;sub.style.opacity="0.7";}
            pricing.appendChild(sub);
        }
        var listWrap=document.createElement("ul");
        listWrap.className="fb-pricing-features";
        item.settings.features.forEach(function(f){
            var li=document.createElement("li");
            li.textContent=String(f||"Feature");
            if(customColor2)li.style.color=customColor2;
            listWrap.appendChild(li);
        });
        pricing.appendChild(listWrap);
        if(ctaLabel!==""){
            var cta=document.createElement(ctaLink?"a":"button");
            if(ctaLink){
                cta.href=ctaLink;
                cta.addEventListener("click",function(e){e.preventDefault();});
            }else{
                cta.type="button";
            }
            cta.className="fb-pricing-cta";
            cta.textContent=ctaLabel;
            cta.style.background=ctaBg;
            cta.style.color=ctaText;
            pricing.appendChild(cta);
        }
        w.appendChild(pricing);
    }
    else if(item.type==="product_offer"){
        item.settings=item.settings||{};
        item.settings.features=normalizeFeatureList(item.settings.features);
        item.settings.media=normalizeProductOfferMediaList(item.settings.media);
        var activeStepForProduct=cur();
        var productStepType=normalizeTemplateType((activeStepForProduct&&activeStepForProduct.type)||"custom");
        var productName=String(item.settings.plan||"Product");
        var productSale=normalizeTemplateCurrencyValue(item.settings.price||"");
        var productRegular=normalizeTemplateCurrencyValue(item.settings.regularPrice||"");
        var productPrice=(productSale!==""?productSale:(productRegular!==""?productRegular:"0"));
        var productPeriod=String(item.settings.period||"");
        var productSubtitle=String(item.settings.subtitle||"");
        var productBadge=String(item.settings.badge||"");
        var productDescription=String(item.settings.description||"").trim();
        var productStockRaw=String(item.settings.stockQuantity||"").trim();
        var productStockCount=productStockRaw===""?null:Math.max(0,parseInt(productStockRaw,10)||0);
        var productButtonRaw=(typeof item.settings.ctaLabel==="string")?String(item.settings.ctaLabel).trim():"";
        var productQuickViewEnabled=item.settings.quickViewEnabled!==false;
        var productQuickViewLabel=String(item.settings.quickViewLabel||"Details").trim()||"Details";
        var productCartEnabled=item.settings.cartEnabled!==false;
        var productButton=productStepType==="checkout"
            ?"Pay Now"
            :(productButtonRaw!==""?productButtonRaw:(productStepType==="sales"&&productName!==""?("Buy "+productName):"Buy Now"));
        var productBg=String(item.settings.ctaBgColor||"#240E35");
        var productText=String(item.settings.ctaTextColor||"#ffffff");
        var productColor=(item.style&&item.style.color)?String(item.style.color):"";
        var mediaList=item.settings.media||[];
        var activeMedia=Number(item.settings.activeMedia)||0;
        if(activeMedia<0)activeMedia=0;
        if(activeMedia>=mediaList.length)activeMedia=mediaList.length-1;
        if(activeMedia<0)activeMedia=0;
        item.settings.activeMedia=activeMedia;
        var productCard=document.createElement("div");
        productCard.className="fb-pricing fb-product-offer";
        var mediaWrap=document.createElement("div");
        mediaWrap.className="fb-product-media";
        var mediaStage=document.createElement("div");
        mediaStage.className="fb-product-media-stage";
        var activeMediaItem=mediaList[activeMedia]||null;
        if(activeMediaItem&&String(activeMediaItem.src||"").trim()!==""){
            if(String(activeMediaItem.type||"image")==="video"){
                var mediaVideo=document.createElement("video");
                mediaVideo.src=String(activeMediaItem.src||"");
                mediaVideo.controls=true;
                mediaVideo.preload="metadata";
                mediaVideo.playsInline=true;
                if(String(activeMediaItem.poster||"").trim()!=="")mediaVideo.poster=String(activeMediaItem.poster||"").trim();
                mediaStage.appendChild(mediaVideo);
            }else{
                var mediaImage=document.createElement("img");
                mediaImage.src=String(activeMediaItem.src||"");
                mediaImage.alt=String(activeMediaItem.alt||productName||"Product media");
                mediaStage.appendChild(mediaImage);
            }
        }else{
            var productPlaceholder=document.createElement("div");
            productPlaceholder.className="fb-product-media-placeholder";
            productPlaceholder.innerHTML='<i class="fas fa-images"></i><div>Click to upload product image</div>';
            productPlaceholder.title="Upload product image from your PC";
            productPlaceholder.addEventListener("click",function(e){
                e.preventDefault();
                e.stopPropagation();
                promptImageUploadForProductOffer(item,activeMedia);
            });
            mediaStage.appendChild(productPlaceholder);
        }
        mediaWrap.appendChild(mediaStage);
        if(mediaList.length>1){
            var productPrev=document.createElement("button");
            productPrev.type="button";
            productPrev.className="fb-product-media-nav is-left";
            productPrev.innerHTML='<i class="fas fa-chevron-left"></i>';
            productPrev.onclick=function(e){
                e.preventDefault();e.stopPropagation();
                saveToHistory();
                item.settings.activeMedia=(Number(item.settings.activeMedia)||0)-1;
                if(item.settings.activeMedia<0)item.settings.activeMedia=mediaList.length-1;
                renderCanvas();
            };
            var productNext=document.createElement("button");
            productNext.type="button";
            productNext.className="fb-product-media-nav is-right";
            productNext.innerHTML='<i class="fas fa-chevron-right"></i>';
            productNext.onclick=function(e){
                e.preventDefault();e.stopPropagation();
                saveToHistory();
                item.settings.activeMedia=((Number(item.settings.activeMedia)||0)+1)%mediaList.length;
                renderCanvas();
            };
            mediaWrap.appendChild(productPrev);
            mediaWrap.appendChild(productNext);
        }
        productCard.appendChild(mediaWrap);
        if(mediaList.length>1){
            var mediaDots=document.createElement("div");
            mediaDots.className="fb-product-media-dots";
            mediaList.forEach(function(_media,idx){
                var dot=document.createElement("span");
                dot.className="fb-product-media-dot"+(idx===activeMedia?" is-active":"");
                mediaDots.appendChild(dot);
            });
            productCard.appendChild(mediaDots);
        }
        if(productBadge){
            var productBd=document.createElement("div");
            productBd.className="fb-pricing-badge";
            productBd.textContent=productBadge;
            productCard.appendChild(productBd);
        }
        var productTitle=document.createElement("div");
        productTitle.className="fb-pricing-title";
        productTitle.textContent=productName;
        if(productColor)productTitle.style.color=productColor;
        productCard.appendChild(productTitle);
        var productPriceRow=document.createElement("div");
        var productPriceEl=document.createElement("span");
        productPriceEl.className="fb-pricing-price";
        productPriceEl.textContent=productPrice;
        if(productColor)productPriceEl.style.color=productColor;
        productPriceRow.appendChild(productPriceEl);
        if(productRegular&&productRegular!==productPrice){
            var productRegularEl=document.createElement("span");
            productRegularEl.className="fb-pricing-period";
            productRegularEl.textContent=productRegular;
            productRegularEl.style.textDecoration="line-through";
            productRegularEl.style.marginLeft="8px";
            if(productColor){productRegularEl.style.color=productColor;productRegularEl.style.opacity="0.55";}
            productPriceRow.appendChild(productRegularEl);
        }
        if(productPeriod){
            var productPeriodEl=document.createElement("span");
            productPeriodEl.className="fb-pricing-period";
            productPeriodEl.textContent=productPeriod;
            if(productColor){productPeriodEl.style.color=productColor;productPeriodEl.style.opacity="0.7";}
            productPriceRow.appendChild(productPeriodEl);
        }
        productCard.appendChild(productPriceRow);
        if(productSubtitle){
            var productSub=document.createElement("div");
            productSub.className="fb-pricing-subtitle";
            productSub.textContent=productSubtitle;
            if(productColor){productSub.style.color=productColor;productSub.style.opacity="0.7";}
            productCard.appendChild(productSub);
        }
        if(productStockCount!==null){
            var productStock=document.createElement("div");
            productStock.className="fb-pricing-subtitle";
            productStock.textContent=productStockCount===0?"Out of stock":(productStockCount+" in stock");
            if(productColor){productStock.style.color=productColor;productStock.style.opacity="0.82";}
            productCard.appendChild(productStock);
        }
        var productList=document.createElement("ul");
        productList.className="fb-pricing-features";
        item.settings.features.forEach(function(f){
            var li=document.createElement("li");
            li.textContent=String(f||"Feature");
            if(productColor)li.style.color=productColor;
            productList.appendChild(li);
        });
        productCard.appendChild(productList);
        var productActions=document.createElement("div");
        productActions.className="fb-product-actions";
        if(productButton!==""){
            var productCta=document.createElement("button");
            productCta.type="button";
            productCta.className="fb-pricing-cta";
            productCta.textContent=productButton;
            productCta.style.background=productBg;
            productCta.style.color=productText;
            productActions.appendChild(productCta);
        }
        if(productQuickViewEnabled||productCartEnabled){
            var productUtility=document.createElement("div");
            productUtility.className="fb-product-utility";
            if(productQuickViewEnabled){
                var productMore=document.createElement("button");
                productMore.type="button";
                productMore.className="fb-product-secondary";
                productMore.textContent=productQuickViewLabel;
                productMore.title=productDescription!==""?productDescription:"Open product details modal in preview or published mode";
                productUtility.appendChild(productMore);
            }
            if(productCartEnabled){
                var productCart=document.createElement("button");
                productCart.type="button";
                productCart.className="fb-product-cart";
                productCart.innerHTML='<i class="fas fa-cart-shopping"></i>';
                productCart.title="Add to cart";
                productUtility.appendChild(productCart);
            }
            productActions.appendChild(productUtility);
        }
        if(productActions.children.length)productCard.appendChild(productActions);
        w.appendChild(productCard);
    }
    else if(item.type==="checkout_summary"||item.type==="physical_checkout_summary"){
        item.settings=item.settings||{};
        item.settings.features=normalizeFeatureList(item.settings.features);
        var isPhysicalCheckoutSummary=item.type==="physical_checkout_summary";
        var summaryHeading=String(item.settings.heading||(isPhysicalCheckoutSummary?"Cart Summary":"Order Summary"));
        var summaryPlan=String(item.settings.plan||(isPhysicalCheckoutSummary?"3 items":"Starter"));
        var summaryPrice=normalizeTemplateCurrencyValue(item.settings.price||"");
        var summaryRegular=normalizeTemplateCurrencyValue(item.settings.regularPrice||"");
        var summaryPeriod=String(item.settings.period||"");
        var summarySubtitle=String(item.settings.subtitle||"");
        var summaryBadge=String(item.settings.badge||(isPhysicalCheckoutSummary?"Cart":""));
        var summaryButton=String(item.settings.ctaLabel||(isPhysicalCheckoutSummary?"Place Order":"Pay Now")).trim()||(isPhysicalCheckoutSummary?"Place Order":"Pay Now");
        var summaryBg=String(item.settings.ctaBgColor||"#240E35");
        var summaryText=String(item.settings.ctaTextColor||"#ffffff");
        var customSummaryColor=(item.style&&item.style.color)?String(item.style.color):"";
        var priceText=summaryPrice!==""?summaryPrice:(summaryRegular!==""?summaryRegular:"0");
        var card=document.createElement("div");
        card.className=isPhysicalCheckoutSummary?"fb-pricing fb-physical-checkout":"fb-pricing";
        if(summaryBadge){
            var sBadge=document.createElement("div");
            sBadge.className="fb-pricing-badge";
            sBadge.textContent=isPhysicalCheckoutSummary?"Cart":summaryBadge;
            card.appendChild(sBadge);
        }
        if(isPhysicalCheckoutSummary){
            var head=document.createElement("div");
            head.className="fb-physical-checkout-head";
            var sHeading=document.createElement("div");
            sHeading.className="fb-physical-checkout-label";
            sHeading.textContent="Cart Summary";
            if(customSummaryColor){sHeading.style.color=customSummaryColor;sHeading.style.opacity="0.7";}
            head.appendChild(sHeading);
            card.appendChild(head);

            var product=document.createElement("div");
            product.className="fb-physical-checkout-product";
            var thumb=document.createElement("div");
            thumb.className="fb-physical-checkout-thumb";
            thumb.innerHTML='<i class="fas fa-box-open"></i>';
            product.appendChild(thumb);
            var meta=document.createElement("div");
            meta.className="fb-physical-checkout-meta";
            var sTitle=document.createElement("div");
            sTitle.className="fb-pricing-title";
            sTitle.textContent="3 items";
            if(customSummaryColor)sTitle.style.color=customSummaryColor;
            meta.appendChild(sTitle);
            var sSubtitle=document.createElement("div");
            sSubtitle.className="fb-pricing-subtitle";
            sSubtitle.textContent="Review the products in your cart before paying.";
            if(customSummaryColor){sSubtitle.style.color=customSummaryColor;sSubtitle.style.opacity="0.7";}
            meta.appendChild(sSubtitle);
            var priceWrap=document.createElement("div");
            priceWrap.className="fb-physical-checkout-price";
            var sPrice=document.createElement("span");
            sPrice.className="fb-pricing-price";
            sPrice.textContent="4,000";
            if(customSummaryColor)sPrice.style.color=customSummaryColor;
            priceWrap.appendChild(sPrice);
            if(summaryPeriod){
                var sPeriod=document.createElement("span");
                sPeriod.className="fb-pricing-period";
                sPeriod.textContent=summaryPeriod;
                if(customSummaryColor){sPeriod.style.color=customSummaryColor;sPeriod.style.opacity="0.7";}
                priceWrap.appendChild(sPeriod);
            }
            meta.appendChild(priceWrap);
            product.appendChild(meta);
            card.appendChild(product);

            var rows=document.createElement("div");
            rows.className="fb-physical-checkout-rows";
            var row1=document.createElement("div");
            row1.className="fb-physical-checkout-row";
            row1.innerHTML='<span>Items subtotal</span><strong>4,000</strong>';
            var row2=document.createElement("div");
            row2.className="fb-physical-checkout-row";
            row2.innerHTML='<span>Shipping</span><strong>Calculated at checkout</strong>';
            var row3=document.createElement("div");
            row3.className="fb-physical-checkout-row fb-physical-checkout-row--total";
            row3.innerHTML='<strong>Order total</strong><strong>4,000</strong>';
            rows.appendChild(row1);
            rows.appendChild(row2);
            rows.appendChild(row3);
            card.appendChild(rows);

            var placeholderLines=document.createElement("div");
            placeholderLines.className="fb-physical-checkout-lines";
            [
                {name:"Product one",sub:"Qty: 1 â€¢ Best Seller",total:"500"},
                {name:"Product two",sub:"Qty: 1 â€¢ Featured",total:"1,500"}
            ].forEach(function(line){
                var row=document.createElement("div");
                row.className="fb-physical-checkout-line";
                var rowThumb=document.createElement("div");
                rowThumb.className="fb-physical-checkout-line-thumb";
                rowThumb.innerHTML='<i class="fas fa-box-open"></i>';
                var rowMeta=document.createElement("div");
                rowMeta.className="fb-physical-checkout-line-meta";
                var rowTitle=document.createElement("div");
                rowTitle.className="fb-physical-checkout-line-title";
                rowTitle.textContent=line.name;
                var rowSub=document.createElement("div");
                rowSub.className="fb-physical-checkout-line-sub";
                rowSub.textContent=line.sub;
                var rowTotal=document.createElement("div");
                rowTotal.className="fb-physical-checkout-line-total";
                rowTotal.textContent=line.total;
                if(customSummaryColor){
                    rowTitle.style.color=customSummaryColor;
                    rowTotal.style.color=customSummaryColor;
                    rowSub.style.color=customSummaryColor;
                    rowSub.style.opacity="0.7";
                }
                rowMeta.appendChild(rowTitle);
                rowMeta.appendChild(rowSub);
                row.appendChild(rowThumb);
                row.appendChild(rowMeta);
                row.appendChild(rowTotal);
                placeholderLines.appendChild(row);
            });
            card.appendChild(placeholderLines);
        }else{
            var sHeading=document.createElement("div");
            sHeading.className="fb-pricing-subtitle";
            sHeading.textContent=summaryHeading;
            sHeading.style.fontSize="11px";
            sHeading.style.fontWeight="800";
            sHeading.style.letterSpacing="0.08em";
            sHeading.style.textTransform="uppercase";
            if(customSummaryColor){sHeading.style.color=customSummaryColor;sHeading.style.opacity="0.7";}
            card.appendChild(sHeading);
            var sTitle=document.createElement("div");
            sTitle.className="fb-pricing-title";
            sTitle.textContent=summaryPlan;
            if(customSummaryColor)sTitle.style.color=customSummaryColor;
            card.appendChild(sTitle);
            var sPriceRow=document.createElement("div");
            var sPrice=document.createElement("span");
            sPrice.className="fb-pricing-price";
            sPrice.textContent=priceText;
            if(customSummaryColor)sPrice.style.color=customSummaryColor;
            sPriceRow.appendChild(sPrice);
            if(summaryPeriod){
                var sPeriod=document.createElement("span");
                sPeriod.className="fb-pricing-period";
                sPeriod.textContent=summaryPeriod;
                if(customSummaryColor){sPeriod.style.color=customSummaryColor;sPeriod.style.opacity="0.7";}
                sPriceRow.appendChild(sPeriod);
            }
            card.appendChild(sPriceRow);
            if(summarySubtitle){
                var sSubtitle=document.createElement("div");
                sSubtitle.className="fb-pricing-subtitle";
                sSubtitle.textContent=summarySubtitle;
                if(customSummaryColor){sSubtitle.style.color=customSummaryColor;sSubtitle.style.opacity="0.7";}
                card.appendChild(sSubtitle);
            }
        }
        if(!isPhysicalCheckoutSummary){
            var sList=document.createElement("ul");
            sList.className="fb-pricing-features";
            item.settings.features.forEach(function(f){
                var li=document.createElement("li");
                li.textContent=String(f||"Feature");
                if(customSummaryColor)li.style.color=customSummaryColor;
                sList.appendChild(li);
            });
            card.appendChild(sList);
        }
        var sButton=document.createElement("button");
        sButton.type="button";
        sButton.className="fb-pricing-cta";
        sButton.textContent=summaryButton;
        sButton.style.background=summaryBg;
        sButton.style.color=summaryText;
        card.appendChild(sButton);
        w.appendChild(card);
    }
    else if(item.type==="countdown"){
        item.settings=item.settings||{};
        var endAt=String(item.settings.endAt||"");
        var label=String(item.settings.label||"Offer ends in");
        var numberColor=String(item.settings.numberColor||"#240E35");
        var labelColor=String(item.settings.labelColor||"#64748b");
        var gap=Number(item.settings.itemGap);if(isNaN(gap)||gap<0)gap=8;
        var parts=countdownParts(endAt)||{days:0,hours:0,minutes:0,seconds:0};
        var cd=document.createElement("div");
        cd.className="fb-countdown";
        var cdLabel=document.createElement("div");
        cdLabel.className="fb-countdown-label";
        cdLabel.textContent=label;
        cdLabel.style.color=labelColor;
        cd.appendChild(cdLabel);
        var grid=document.createElement("div");
        grid.className="fb-countdown-grid";
        grid.style.gap=gap+"px";
        function addBox(val,unit){
            var box=document.createElement("div");
            box.className="fb-countdown-box";
            var num=document.createElement("div");
            num.className="fb-countdown-num";
            num.textContent=pad2(val);
            num.style.color=numberColor;
            var un=document.createElement("div");
            un.className="fb-countdown-unit";
            un.textContent=unit;
            un.style.color=labelColor;
            box.appendChild(num);
            box.appendChild(un);
            grid.appendChild(box);
        }
        addBox(parts.days,"Days");
        addBox(parts.hours,"Hours");
        addBox(parts.minutes,"Mins");
        addBox(parts.seconds,"Secs");
        cd.appendChild(grid);
        w.appendChild(cd);
    }
    else if(item.type==="menu"){
        var ms=item.settings||{};
        var items=Array.isArray(ms.items)&&ms.items.length?ms.items:[{label:"Menu item",url:"#",newWindow:false,hasSubmenu:false}];
        var gap=Number(ms.itemGap);if(isNaN(gap))gap=13;
        var activeIdx=Number(ms.activeIndex);if(isNaN(activeIdx))activeIdx=0;
        var align=(ms.menuAlign||"left");
        var rightButtonLabel=String(ms.leftButtonLabel||"Get Started");
        var rightButtonUrl=String(ms.leftButtonUrl||"#");
        var rightButtonBg=String(ms.leftButtonBgColor||"#240E35");
        var rightButtonText=String(ms.leftButtonTextColor||"#ffffff");
        var rightButtonFontSize=Number(ms.leftButtonTextSize);if(isNaN(rightButtonFontSize)||rightButtonFontSize<10||rightButtonFontSize>48)rightButtonFontSize=14;
        var rightButtonBold=!!ms.leftButtonBold;
        var rightButtonItalic=!!ms.leftButtonItalic;
        var rightButtonRadius=Number(ms.leftButtonBorderRadius);if(isNaN(rightButtonRadius)||rightButtonRadius<0||rightButtonRadius>80)rightButtonRadius=999;
        var rightButtonPadY=Number(ms.leftButtonPaddingY);if(isNaN(rightButtonPadY)||rightButtonPadY<4||rightButtonPadY>40)rightButtonPadY=8;
        var rightButtonPadX=Number(ms.leftButtonPaddingX);if(isNaN(rightButtonPadX)||rightButtonPadX<8||rightButtonPadX>80)rightButtonPadX=14;
        var leftLogoUrl=String(ms.rightLogoUrl||"");
        var leftLogoAlt=String(ms.rightLogoAlt||"Logo");
        var st=item.style||{};
        const shell=document.createElement("div");
        shell.style.display="flex";
        shell.style.alignItems="center";
        shell.style.gap="12px";
        shell.style.width="100%";
        const leftWrap=document.createElement("div");
        leftWrap.style.flex="0 0 auto";
        if(leftLogoUrl){
            const logo=document.createElement("img");
            logo.src=leftLogoUrl;
            logo.alt=leftLogoAlt||"Logo";
            logo.style.display="block";
            logo.style.maxHeight="42px";
            logo.style.width="auto";
            logo.style.maxWidth="180px";
            logo.style.objectFit="contain";
            leftWrap.appendChild(logo);
        }else{
            const ph=document.createElement("div");
            ph.textContent="Logo";
            ph.style.padding="8px 12px";
            ph.style.border="1px dashed #cbd5e1";
            ph.style.borderRadius="10px";
            ph.style.fontSize="12px";
            ph.style.color="#64748b";
            leftWrap.appendChild(ph);
        }
        shell.appendChild(leftWrap);
        const centerWrap=document.createElement("div");
        centerWrap.style.flex="1 1 auto";
        centerWrap.style.minWidth="0";
        const ul=document.createElement("ul");
        ul.style.listStyle="none";ul.style.margin="0";ul.style.padding="0";
        ul.style.display="flex";ul.style.flexWrap="nowrap";ul.style.whiteSpace="nowrap";ul.style.gap=gap+"px";
        ul.style.justifyContent=align==="right"?"flex-end":align==="center"?"center":"flex-start";
        ul.style.alignItems="center";
        ul.style.overflowX="auto";
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
        centerWrap.appendChild(ul);
        shell.appendChild(centerWrap);
        const rightWrap=document.createElement("div");
        rightWrap.style.flex="0 0 auto";
        const rightBtn=document.createElement("a");
        rightBtn.href=rightButtonUrl||"#";
        rightBtn.textContent=rightButtonLabel||"Get Started";
        rightBtn.style.display="inline-block";
        rightBtn.style.padding=rightButtonPadY+"px "+rightButtonPadX+"px";
        rightBtn.style.borderRadius=rightButtonRadius+"px";
        rightBtn.style.textDecoration="none";
        rightBtn.style.fontWeight=rightButtonBold?"700":"600";
        rightBtn.style.fontStyle=rightButtonItalic?"italic":"normal";
        rightBtn.style.fontSize=rightButtonFontSize+"px";
        rightBtn.style.backgroundColor=rightButtonBg;
        rightBtn.style.color=rightButtonText;
        rightBtn.addEventListener("click",e=>e.preventDefault());
        rightWrap.appendChild(rightBtn);
        shell.appendChild(rightWrap);
        w.innerHTML="";w.appendChild(shell);
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
    if(isAdvancedScaleComponent(item.type))applyAdvancedScaleToNode(w,item);
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
        var liveMin=String(sectionNode.style.minHeight||"").trim();
        if(liveMin.endsWith("px")){
            var liveN=Number(liveMin.replace("px","").trim());
            if(!isNaN(liveN))startH=liveN;
        }else if(cssMin.endsWith("px")){
            var n=Number(cssMin.replace("px","").trim());
            if(!isNaN(n))startH=n;
        }else if(cssMin.endsWith("vh")){
            var vh=Number(cssMin.replace("vh","").trim());
            if(!isNaN(vh))startH=Math.round((window.innerHeight||900)*(vh/100));
        }
        if(startH<=0){
            startH=Math.max(120,Math.round(sectionNode.getBoundingClientRect().height||0));
        }
        var latestH=startH;
        var didSave=false;
        function onMove(ev){
            if(!didSave){saveToHistory();didSave=true;}
            var dy=(Number(ev.clientY)||0)-startY;
            var contentMin=getSectionContentMinHeight(sectionNode);
            var next=Math.max(contentMin,Math.min(2200,Math.round(startH+dy)));
            latestH=next;
            applySectionMinHeight(sectionNode,next);
            fitFlowImagesWithinSection(sectionNode,next);
        }
        function onUp(){
            document.removeEventListener("mousemove",onMove);
            document.removeEventListener("mouseup",onUp);
            applySectionMinHeight(sectionNode,latestH);
            fitFlowImagesWithinSection(sectionNode,latestH);
        }
        document.addEventListener("mousemove",onMove);
        document.addEventListener("mouseup",onUp);
    });
    sectionNode.appendChild(hHandle);
}

function getSectionContentMinHeight(sectionNode){
    if(!sectionNode)return 80;
    var inner=sectionNode.querySelector(".sec-inner")||sectionNode;
    return Math.max(80,getNodeContentMinHeight(inner));
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

function fitFlowImagesWithinSection(sectionNode,targetHeight){
    if(!sectionNode)return;
    var inner=sectionNode.querySelector(".sec-inner")||sectionNode;
    var innerRect=inner.getBoundingClientRect();
    if(!innerRect||!(innerRect.width>0)||!(innerRect.height>0))return;
    var requestedHeight=Number(targetHeight)||0;
    if(requestedHeight<=0){
        var cssH=String(sectionNode.style.minHeight||"").trim();
        if(cssH.endsWith("px")){
            var parsed=Number(cssH.replace("px","").trim());
            if(!isNaN(parsed))requestedHeight=parsed;
        }
    }
    if(requestedHeight<=0)requestedHeight=Math.round(innerRect.height||0);
    var innerTop=innerRect.top;
    Array.from(inner.querySelectorAll('.el[data-el-type="image"]')).forEach(function(node){
        if(!node||node.classList.contains("el--abs"))return;
        var item=findElementById(node.getAttribute("data-el-id"));
        if(!item||item.type!=="image")return;
        var nodeRect=node.getBoundingClientRect();
        var currentW=Math.max(1,Math.round(nodeRect.width||0));
        var currentH=Math.max(1,Math.round(nodeRect.height||0));
        if(currentW<=1||currentH<=1)return;
        var host=node.parentElement||inner;
        var hostRect=host.getBoundingClientRect();
        var offsetTop=Math.max(0,Math.round(nodeRect.top-innerTop));
        var availableW=Math.max(40,Math.min(
            Math.round(hostRect.width||currentW),
            Math.round(innerRect.right-nodeRect.left)
        ));
        var availableH=Math.max(40,requestedHeight-offsetTop);
        var shrink=Math.min(1,availableW/currentW,availableH/currentH);
        if(!(shrink<0.999))return;
        var nextW=Math.max(30,Math.round(currentW*shrink));
        var nextH=Math.max(20,Math.round(currentH*shrink));
        item.style=item.style||{};
        item.style.width=nextW+"px";
        item.style.height=nextH+"px";
        node.style.width=item.style.width;
        node.style.height=item.style.height;
        var imgWrap=node.firstElementChild;
        if(imgWrap){
            imgWrap.style.width="100%";
            imgWrap.style.height="100%";
        }
        var img=node.querySelector("img");
        if(img){
            img.style.width="100%";
            img.style.height="100%";
            img.style.maxWidth="100%";
            img.style.objectFit="contain";
            img.style.objectPosition="top center";
        }
    });
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
    if(canvas)canvas.classList.toggle("fb-link-pick",!!state.linkPick);
    state._linkTargetIds=getLinkedPricingIdsForSelection();
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
        var secHasRows=Array.isArray(s.rows)&&s.rows.length>0;
        if(!isBareSection&&!s.__freeformCanvas){
            var secMinH=(s&&s.style&&String(s.style.minHeight||"").trim())||"";
            if(secMinH===""){
                sn.style.minHeight=secHasRows?"auto":"30vh";
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
            inner.style.minHeight="56px";
        }else{
            var innerMin=(sn.style.minHeight&&String(sn.style.minHeight).trim()!=="")?String(sn.style.minHeight):(secHasRows?"auto":"30vh");
            inner.style.minHeight=innerMin;
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
            if(!t){
                clearFreeDropGuides();
                clearDropPreview();
                return;
            }
            if(!isStructureComponent(t)){
                showFreeDropGuides(e,inner);
                showDropPreview(t,inner,e);
            }else{
                clearFreeDropGuides();
                showDropInsert(sn,dropPlacement(e,sn));
            }
        };
        sn.ondragleave=e=>{
            if(!sn.contains(e.relatedTarget)){
                clearFreeDropGuides();
                clearDropPreview();
            }
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
                ok=addComponentAt(t,{k:"sec",s:s.id},buildFreePlacement(t,inner,e));
            }else{
                ok=addComponentAt(t,{k:"sec",s:s.id},dropPlacement(e,sn));
            }
            clearFreeDropGuides();
            clearDropPreview();
            if(ok)render();
        };
        s.elements=Array.isArray(s.elements)?s.elements:[];
        var freeformRenderedBottom=0;
        {
            (s.elements||[]).forEach(function(it){
                var elNode=renderElement(it,{s:s.id,scope:"section"});
                inner.appendChild(elNode);
                if(isAdvancedScaleComponent(it.type))syncAdvancedElementHeight(elNode,it);
                if(s.__freeformCanvas && it.settings && it.settings.positionMode==="absolute"){
                    var eyRendered=Number(it.settings.freeY)||0;
                    var renderedHeight=Math.round(elNode.getBoundingClientRect().height)||Number(elNode.offsetHeight)||0;
                    var renderedBottom=eyRendered+Math.max(24,renderedHeight)+8;
                    if(renderedBottom>freeformRenderedBottom)freeformRenderedBottom=renderedBottom;
                }
            });
        }
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
            var freeformH=Math.max(40,freeformRenderedBottom,secMaxBot>0?Math.min(secMaxBot,freeformRenderedBottom||secMaxBot):0);
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
            rn.ondragover=e=>{
                e.preventDefault();
                const t=e.dataTransfer&&e.dataTransfer.getData?e.dataTransfer.getData("c"):"";
                if(!t){
                    clearFreeDropGuides();
                    clearDropPreview();
                    return;
                }
                if(!isStructureComponent(t)){
                    var nearCol=nearestColumnNode(rowInner,e.clientX);
                    var host=nearCol?(nearCol.querySelector(".col-inner")||nearCol):rowInner;
                    showFreeDropGuides(e,host);
                    showDropPreview(t,host,e);
                }else{
                    clearFreeDropGuides();
                    showDropInsert(rn,dropPlacement(e,rn));
                }
            };
            rn.ondragleave=e=>{
                if(!rn.contains(e.relatedTarget)){
                    clearFreeDropGuides();
                    clearDropPreview();
                }
            };
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
                            if(addComponentAt(t,{k:"col",s:s.id,r:r.id,c:nearId},buildFreePlacement(t,nearInner,e)))render();
                            clearFreeDropGuides();
                            clearDropPreview();
                            return;
                        }
                    }
                }
                if(addComponentAt(t,{k:"row",s:s.id,r:r.id},dropPlacement(e,rn)))render();
                clearFreeDropGuides();
                clearDropPreview();
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
                    if(!t){
                        clearFreeDropGuides();
                        clearDropPreview();
                        return;
                    }
                    if(!isStructureComponent(t)){
                        showFreeDropGuides(e,colInner);
                        showDropPreview(t,colInner,e);
                    }else{
                        clearFreeDropGuides();
                        var insHost=getStructureInsertHost(t,cn);
                        var place=insHost?dropPlacement(e,insHost):null;
                        showDropInsert(insHost,place);
                    }
                };
                cn.ondragleave=e=>{
                    if(!cn.contains(e.relatedTarget)){
                        clearFreeDropGuides();
                        clearDropPreview();
                    }
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
                        ok=addComponentAt(t,{k:"col",s:s.id,r:r.id,c:c.id},buildFreePlacement(t,colInner,e));
                    }else{
                        ok=addComponentAt(t,{k:"col",s:s.id,r:r.id,c:c.id},dropPlacement(e,cn));
                    }
                    clearFreeDropGuides();
                    clearDropPreview();
                    if(ok)render();
                };
                var colMaxBot=0;
                (c.elements||[]).forEach(function(it){
                    var elNode=renderElement(it,{s:s.id,r:r.id,c:c.id});
                    colInner.appendChild(elNode);
                    if(isAdvancedScaleComponent(it.type))syncAdvancedElementHeight(elNode,it);
                    if(it.settings&&it.settings.positionMode==="absolute"){
                        var ey=Number(it.settings.freeY)||0;
                        var eh=parseInt(it.style&&it.style.height)||Math.round(elNode.getBoundingClientRect().height)||80;
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
    if(state.linkPick && state.linkPick.type==="pricing"){
        var banner=document.createElement("div");
        banner.className="fb-link-banner";
        banner.innerHTML='<span>Click pricing components to link or unlink. Press Esc to finish.</span><button type="button" id="fbLinkCancel">Done</button>';
        canvas.insertBefore(banner,canvas.firstChild||null);
        var cancelBtn=banner.querySelector("#fbLinkCancel");
        if(cancelBtn){
            cancelBtn.onclick=function(){
                state.linkPick=null;
                renderCanvas();
                renderSettings();
            };
        }
    }
    drawLinkWires();
    if(canvas && !canvas.__linkScrollBound){
        canvas.__linkScrollBound=true;
        canvas.addEventListener("scroll",function(){drawLinkWires();});
    }
    canvas.ondragover=e=>{e.preventDefault();clearFreeDropGuides();clearDropPreview();};
    canvas.ondrop=e=>{e.preventDefault();clearFreeDropGuides();clearDropPreview();if(e.target&&e.target.closest&&e.target.closest(".carousel-live-editor"))return;const t=e.dataTransfer.getData("c");if(t){var ok=(!isStructureComponent(t))?addComponentAt(t,null,buildFreePlacement(t,canvas,e)):addComponentAt(t,null,"after");if(ok)render();}};
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
            var hasClipboard=!!(state.clipboard&&state.clipboard.node);
            var clickedElement=!!(e.target.closest&&e.target.closest(".el[data-el-id]"));
            if(!selectFromCanvasTarget(e.target)){
                state.sel=null;
                state.carouselSel=null;
                renderSettings();
                if(!hasClipboard){
                    hideContextMenu();
                    return;
                }
                state.pasteAnchor=resolvePasteAnchorFromEvent(e);
                showContextMenuAt(e.clientX,e.clientY,"paste-only");
                return;
            }
            if(clickedElement){
                state.pasteAnchor=null;
                showContextMenuAt(e.clientX,e.clientY,"full");
                return;
            }
            state.pasteAnchor=hasClipboard?resolvePasteAnchorFromEvent(e):null;
            showContextMenuAt(e.clientX,e.clientY,hasClipboard?"paste-only":"full");
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
    const fire=()=>{if(opts&&opts.undo)saveToHistory();let v=n.value;if(opts&&opts.px)v=pxIfNumber(v);cb(v);queueAutoSave();refreshAfterSetting();};
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();fire();}});
}

function bindCurrency(id,val,cb,opts){
    const n=document.getElementById(id);if(!n)return;
    const normalize=v=>{
        var raw=String(v||"").replace(/\u20b1/g,"");
        raw=raw.replace(/^\s+/,"");
        return "\u20b1"+raw;
    };
    const keepCaretPastSymbol=()=>{
        if(!n.setSelectionRange)return;
        var start=Math.max(1,n.selectionStart||1);
        var end=Math.max(1,n.selectionEnd||1);
        n.setSelectionRange(start,end);
    };
    n.value=normalize(val||"");
    const fire=()=>{
        if(opts&&opts.undo)saveToHistory();
        const prev=n.value||"";
        const start=n.selectionStart||0;
        const end=n.selectionEnd||0;
        const normalized=normalize(prev);
        n.value=normalized;
        if(document.activeElement===n&&n.setSelectionRange){
            var nextStart=Math.max(1,start+(normalized.length-prev.length));
            var nextEnd=Math.max(nextStart,end+(normalized.length-prev.length));
            n.setSelectionRange(Math.min(nextStart,normalized.length),Math.min(nextEnd,normalized.length));
        }
        cb(normalized);
        queueAutoSave();
        refreshAfterSetting();
    };
    n.addEventListener("input",fire);
    n.addEventListener("change",fire);
    n.addEventListener("click",keepCaretPastSymbol);
    n.addEventListener("focus",keepCaretPastSymbol);
    n.addEventListener("keydown",e=>{
        const start=n.selectionStart||0;
        const end=n.selectionEnd||0;
        if((e.key==="Backspace"&&start<=1&&end<=1)||(e.key==="Delete"&&start===0&&end<=1)){
            e.preventDefault();
            keepCaretPastSymbol();
            return;
        }
        if(e.key==="Enter"){
            e.preventDefault();
            fire();
        }
    });
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
    const fire=()=>{if(opts&&opts.undo)saveToHistory();const raw=(n.value||"").trim();cb(raw===""?"":raw+"px");queueAutoSave();refreshAfterSetting();};
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

function applyUploadedImageToElement(item,hostEl,url){
    item.settings=item.settings||{};
    item.settings.imageSourceType="upload";
    item.settings.src=url;
    item.style=item.style||{};
    var hostW=hostEl&&hostEl.clientWidth?hostEl.clientWidth:0;
    var hostH=hostEl&&hostEl.clientHeight?hostEl.clientHeight:0;
    if(hostW>0 && (!String(item.style.width||"").trim() || String(item.style.width||"").trim()==="100%")){
        item.style.width=Math.max(120,hostW)+"px";
        item.settings.width=item.style.width;
    }
    if(hostH>0 && (!String(item.style.height||"").trim() || String(item.style.height||"").trim()==="auto")){
        item.style.height=Math.max(80,hostH)+"px";
    }
    render();
}

function promptImageUploadForElement(item,hostEl){
    if(!item)return;
    var input=document.createElement("input");
    input.type="file";
    input.accept="image/*";
    input.style.display="none";
    document.body.appendChild(input);
    input.addEventListener("change",function(){
        var file=input.files&&input.files[0]?input.files[0]:null;
        if(!file){
            if(input.parentNode)input.parentNode.removeChild(input);
            return;
        }
        saveToHistory();
        uploadImage(file,function(url){
            applyUploadedImageToElement(item,hostEl,url);
        },"Image upload",null,item.id);
        if(input.parentNode)input.parentNode.removeChild(input);
    },{once:true});
    input.click();
}

function ensureProductOfferMediaList(item){
    item.settings=item.settings||{};
    item.settings.media=normalizeProductOfferMediaList(item.settings.media);
    return item.settings.media;
}

function applyUploadedImageToProductOffer(item,url,mediaIndex){
    if(!item)return;
    item.settings=item.settings||{};
    var media=ensureProductOfferMediaList(item);
    var idx=Number(mediaIndex);
    if(isNaN(idx)||idx<0)idx=Number(item.settings.activeMedia)||0;
    if(idx<0)idx=0;
    if(idx>=media.length){
        media.push({type:"image",src:"",alt:"Product image",poster:""});
        idx=media.length-1;
    }
    media[idx]=Object.assign({}, media[idx]||{}, {
        type:"image",
        src:url,
        alt:String((media[idx]&&media[idx].alt)||item.settings.plan||"Product image"),
        poster:""
    });
    item.settings.media=media;
    item.settings.activeMedia=idx;
    renderCanvas();
}

function promptImageUploadForProductOffer(item,mediaIndex){
    if(!item)return;
    var input=document.createElement("input");
    input.type="file";
    input.accept="image/*";
    input.style.display="none";
    document.body.appendChild(input);
    input.addEventListener("change",function(){
        var file=input.files&&input.files[0]?input.files[0]:null;
        if(!file){
            if(input.parentNode)input.parentNode.removeChild(input);
            return;
        }
        saveToHistory();
        uploadImage(file,function(url){
            applyUploadedImageToProductOffer(item,url,mediaIndex);
        },"Product image upload",null,item.id);
        if(input.parentNode)input.parentNode.removeChild(input);
    },{once:true});
    input.click();
}

function openImagePlaceholderMenu(item,hostEl,menuEl){
    if(!item||!hostEl||!menuEl)return;
    var hostRect=hostEl.getBoundingClientRect();
    var preferredLeft=Math.round(hostRect.left+((hostRect.width||0)/2)-115);
    var preferredTop=Math.round(hostRect.top+((hostRect.height||0)/2)+18);
    menuEl.hidden=false;
    menuEl.style.left="0px";
    menuEl.style.top="0px";
    var menuW=menuEl.offsetWidth||230;
    var menuH=menuEl.offsetHeight||140;
    var vw=Math.max(document.documentElement.clientWidth||0,window.innerWidth||0);
    var vh=Math.max(document.documentElement.clientHeight||0,window.innerHeight||0);
    var nextLeft=Math.max(12,Math.min(preferredLeft,vw-menuW-12));
    var nextTop=Math.max(12,Math.min(preferredTop,vh-menuH-12));
    menuEl.style.left=nextLeft+"px";
    menuEl.style.top=nextTop+"px";
    function closeOnOutside(ev){
        if(hostEl.contains(ev.target))return;
        if(menuEl.contains(ev.target))return;
        menuEl.hidden=true;
        document.removeEventListener("mousedown",closeOnOutside,true);
    }
    setTimeout(function(){
        document.addEventListener("mousedown",closeOnOutside,true);
    },0);
}

const assetLibraryCtx={modal:null,title:null,sub:null,uploadInput:null,status:null,grid:null,onSelect:null,kind:"image",titleText:"Asset Library",actions:null,selectBtn:null,selectAllBtn:null,deleteBtn:null,cancelBtn:null,assets:[],selectionMode:false,selectedPaths:new Set(),statusBase:"Loading library...",statusOverride:"",busy:false};
function formatAssetLibraryDate(raw){
    var d=new Date(String(raw||""));
    if(isNaN(d.getTime()))return "Saved earlier";
    return d.toLocaleDateString([],{month:"short",day:"numeric"})+" "+d.toLocaleTimeString([],{hour:"numeric",minute:"2-digit"});
}
function assetLibraryAcceptForKind(kind){
    var k=String(kind||"image").toLowerCase();
    if(k==="video")return "video/*";
    if(k==="any")return "image/*,video/*";
    return "image/*";
}
function ensureAssetLibraryModal(){
    if(assetLibraryCtx.modal&&assetLibraryCtx.modal.parentNode)return assetLibraryCtx.modal;
    var modal=document.createElement("div");
    modal.id="assetLibraryModal";
    modal.className="asset-library-modal";
    modal.innerHTML='<div class="asset-library-card" role="dialog" aria-modal="true" aria-labelledby="assetLibraryTitle">'
        +'<div class="asset-library-head"><h4 id="assetLibraryTitle">Asset Library</h4><button type="button" class="asset-library-close" id="assetLibraryClose" aria-label="Close">X</button></div>'
        +'<div class="asset-library-sub" id="assetLibrarySub">Reuse files you already uploaded, or add a new one.</div>'
        +'<div class="asset-library-body">'
        +'<div class="asset-library-toolbar">'
        +'<div class="asset-library-upload"><label class="fb-btn primary" for="assetLibraryUploadInput"><i class="fas fa-upload"></i> Upload New</label><input id="assetLibraryUploadInput" type="file"><span class="asset-library-status" id="assetLibraryStatus">Loading library...</span></div>'
        +'<div class="asset-library-actions" id="assetLibraryActions"><button type="button" class="fb-btn" id="assetLibrarySelectBtn">Select</button><button type="button" class="fb-btn" id="assetLibrarySelectAllBtn" hidden>Select all</button><button type="button" class="fb-btn danger" id="assetLibraryDeleteBtn" hidden disabled>Delete selected</button><button type="button" class="fb-btn" id="assetLibraryCancelBtn" hidden>Cancel</button></div>'
        +'</div>'
        +'<div class="asset-library-grid" id="assetLibraryGrid"></div>'
        +'</div>'
        +'</div>';
    document.body.appendChild(modal);
    assetLibraryCtx.modal=modal;
    assetLibraryCtx.title=modal.querySelector("#assetLibraryTitle");
    assetLibraryCtx.sub=modal.querySelector("#assetLibrarySub");
    assetLibraryCtx.uploadInput=modal.querySelector("#assetLibraryUploadInput");
    assetLibraryCtx.status=modal.querySelector("#assetLibraryStatus");
    assetLibraryCtx.grid=modal.querySelector("#assetLibraryGrid");
    assetLibraryCtx.actions=modal.querySelector("#assetLibraryActions");
    assetLibraryCtx.selectBtn=modal.querySelector("#assetLibrarySelectBtn");
    assetLibraryCtx.selectAllBtn=modal.querySelector("#assetLibrarySelectAllBtn");
    assetLibraryCtx.deleteBtn=modal.querySelector("#assetLibraryDeleteBtn");
    assetLibraryCtx.cancelBtn=modal.querySelector("#assetLibraryCancelBtn");
    modal.addEventListener("click",function(e){if(e.target===modal)closeAssetLibraryModal();});
    var closeBtn=modal.querySelector("#assetLibraryClose");
    if(closeBtn)closeBtn.addEventListener("click",function(){closeAssetLibraryModal();});
    if(assetLibraryCtx.selectBtn)assetLibraryCtx.selectBtn.addEventListener("click",function(){setAssetLibrarySelectionMode(true);});
    if(assetLibraryCtx.selectAllBtn)assetLibraryCtx.selectAllBtn.addEventListener("click",function(){toggleAssetLibrarySelectAll();});
    if(assetLibraryCtx.deleteBtn)assetLibraryCtx.deleteBtn.addEventListener("click",function(){deleteSelectedAssetLibraryItems();});
    if(assetLibraryCtx.cancelBtn)assetLibraryCtx.cancelBtn.addEventListener("click",function(){setAssetLibrarySelectionMode(false);});
    if(assetLibraryCtx.uploadInput){
        assetLibraryCtx.uploadInput.addEventListener("change",function(){
            var file=(assetLibraryCtx.uploadInput.files&&assetLibraryCtx.uploadInput.files[0])||null;
            if(!file)return;
            assetLibraryCtx.statusOverride="Uploading...";
            syncAssetLibraryToolbar();
            uploadImage(file,function(url){
                if(typeof assetLibraryCtx.onSelect==="function")assetLibraryCtx.onSelect(url);
                closeAssetLibraryModal();
            },"Asset upload",function(reason){
                assetLibraryCtx.statusOverride=reason||"Upload failed";
                syncAssetLibraryToolbar();
            });
            assetLibraryCtx.uploadInput.value="";
        });
    }
    syncAssetLibraryToolbar();
    return modal;
}
function syncAssetLibraryToolbar(){
    var count=Array.isArray(assetLibraryCtx.assets)?assetLibraryCtx.assets.length:0;
    var selected=assetLibraryCtx.selectedPaths.size;
    if(assetLibraryCtx.selectBtn)assetLibraryCtx.selectBtn.hidden=!!assetLibraryCtx.selectionMode;
    if(assetLibraryCtx.selectAllBtn){
        assetLibraryCtx.selectAllBtn.hidden=!assetLibraryCtx.selectionMode||count===0;
        assetLibraryCtx.selectAllBtn.disabled=!!assetLibraryCtx.busy||count===0;
        assetLibraryCtx.selectAllBtn.textContent=(count>0&&selected===count)?"Clear all":"Select all";
    }
    if(assetLibraryCtx.deleteBtn){
        assetLibraryCtx.deleteBtn.hidden=!assetLibraryCtx.selectionMode;
        assetLibraryCtx.deleteBtn.disabled=!!assetLibraryCtx.busy||selected===0;
        assetLibraryCtx.deleteBtn.textContent=selected>0?("Delete selected ("+selected+")"):"Delete selected";
    }
    if(assetLibraryCtx.cancelBtn){
        assetLibraryCtx.cancelBtn.hidden=!assetLibraryCtx.selectionMode;
        assetLibraryCtx.cancelBtn.disabled=!!assetLibraryCtx.busy;
    }
    if(assetLibraryCtx.status){
        var statusText=assetLibraryCtx.statusOverride||"";
        if(!statusText){
            if(assetLibraryCtx.selectionMode){
                statusText=count>0?(selected>0?(selected+" selected"):"Select files to delete"):"No stored media yet";
            }else{
                statusText=assetLibraryCtx.statusBase||"Loading library...";
            }
        }
        assetLibraryCtx.status.textContent=statusText;
    }
}
function syncAssetLibrarySelectionUi(){
    var grid=assetLibraryCtx.grid;
    if(!grid)return;
    grid.querySelectorAll(".asset-library-item[data-asset-path]").forEach(function(card){
        var path=String(card.getAttribute("data-asset-path")||"");
        var selected=assetLibraryCtx.selectedPaths.has(path);
        card.classList.toggle("is-selection-mode",!!assetLibraryCtx.selectionMode);
        card.classList.toggle("is-selected",selected);
        card.setAttribute("aria-pressed",assetLibraryCtx.selectionMode&&selected?"true":"false");
        var badge=card.querySelector(".asset-library-check");
        if(badge){
            badge.hidden=!assetLibraryCtx.selectionMode;
            badge.classList.toggle("is-selected",selected);
            badge.innerHTML='<i class="fas '+(selected?'fa-check':'fa-plus')+'"></i>';
        }
        var action=card.querySelector(".asset-library-action");
        if(action){
            action.innerHTML='<i class="fas '+(assetLibraryCtx.selectionMode?(selected?'fa-check-circle':'fa-circle-plus'):'fa-arrow-up-right-from-square')+'"></i> '+(assetLibraryCtx.selectionMode?(selected?'Selected':'Select'):'Use this');
        }
    });
}
function setAssetLibrarySelectionMode(enabled){
    assetLibraryCtx.selectionMode=!!enabled;
    assetLibraryCtx.statusOverride="";
    if(!assetLibraryCtx.selectionMode)assetLibraryCtx.selectedPaths=new Set();
    syncAssetLibraryToolbar();
    syncAssetLibrarySelectionUi();
}
function toggleAssetLibrarySelection(path){
    var key=String(path||"");
    if(!key)return;
    if(assetLibraryCtx.selectedPaths.has(key))assetLibraryCtx.selectedPaths.delete(key);
    else assetLibraryCtx.selectedPaths.add(key);
    assetLibraryCtx.statusOverride="";
    syncAssetLibraryToolbar();
    syncAssetLibrarySelectionUi();
}
function toggleAssetLibrarySelectAll(){
    var paths=(assetLibraryCtx.assets||[]).map(function(item){return String(item&&item.path||"");}).filter(Boolean);
    if(!paths.length)return;
    var allSelected=paths.every(function(path){return assetLibraryCtx.selectedPaths.has(path);});
    assetLibraryCtx.selectedPaths=new Set(allSelected?[]:paths);
    assetLibraryCtx.statusOverride="";
    syncAssetLibraryToolbar();
    syncAssetLibrarySelectionUi();
}
function deleteSelectedAssetLibraryItems(){
    if(assetLibraryCtx.busy)return;
    var paths=Array.from(assetLibraryCtx.selectedPaths);
    if(!paths.length)return;
    var count=paths.length;
    if(!window.confirm(count===1?"Delete this file from the asset library?":"Delete "+count+" selected files from the asset library?"))return;
    assetLibraryCtx.busy=true;
    assetLibraryCtx.statusOverride=count===1?"Deleting 1 file...":"Deleting "+count+" files...";
    syncAssetLibraryToolbar();
    fetch(assetLibraryDeleteUrl,{
        method:"POST",
        headers:{
            "Accept":"application/json",
            "Content-Type":"application/json",
            "X-CSRF-TOKEN":csrf
        },
        body:JSON.stringify({paths:paths})
    }).then(function(r){
        return r.text().then(function(t){
            var data={};
            try{data=t?JSON.parse(t):{};}catch(_e){data={message:t||"Failed to delete assets"};}
            if(!r.ok){
                throw new Error((data&&data.message)?data.message:("HTTP "+r.status));
            }
            return data;
        });
    }).then(function(data){
        assetLibraryCtx.selectionMode=false;
        assetLibraryCtx.selectedPaths=new Set();
        return loadAssetLibraryAssets(assetLibraryCtx.kind).then(function(){
            assetLibraryCtx.busy=false;
            assetLibraryCtx.statusOverride=(data&&data.message)?String(data.message):"Delete complete";
            syncAssetLibraryToolbar();
        });
    }).catch(function(err){
        assetLibraryCtx.busy=false;
        assetLibraryCtx.statusOverride=(err&&err.message)||"Failed to delete files";
        syncAssetLibraryToolbar();
    });
}
function closeAssetLibraryModal(){
    var modal=assetLibraryCtx.modal||document.getElementById("assetLibraryModal");
    if(modal)modal.classList.remove("open");
    assetLibraryCtx.onSelect=null;
    assetLibraryCtx.selectionMode=false;
    assetLibraryCtx.selectedPaths=new Set();
    assetLibraryCtx.assets=[];
    assetLibraryCtx.busy=false;
    assetLibraryCtx.statusBase="Loading library...";
    assetLibraryCtx.statusOverride="";
    syncAssetLibraryToolbar();
}
function renderAssetLibraryGrid(assets){
    var grid=assetLibraryCtx.grid;
    if(!grid)return;
    var list=Array.isArray(assets)?assets:[];
    var availablePaths=new Set(list.map(function(item){return String(item&&item.path||"");}).filter(Boolean));
    assetLibraryCtx.assets=list;
    assetLibraryCtx.selectedPaths=new Set(Array.from(assetLibraryCtx.selectedPaths).filter(function(path){return availablePaths.has(path);}));
    if(!list.length){
        grid.innerHTML='<div class="asset-library-empty">No stored media yet. Upload a file to add it here.</div>';
        syncAssetLibraryToolbar();
        return;
    }
    grid.innerHTML=list.map(function(asset){
        var item=(asset&&typeof asset==="object")?asset:{};
        var kind=String(item.kind||"image").toLowerCase();
        var url=String(item.url||"");
        var path=String(item.path||"");
        var name=escapeHtml(item.name||fileNameFromUrl(url)||"Stored media");
        var meta=escapeHtml(formatAssetLibraryDate(item.modified_at||""));
        var selected=assetLibraryCtx.selectedPaths.has(path);
        var preview=kind==="video"
            ? '<div class="asset-library-preview"><span class="asset-library-check'+(selected?' is-selected':'')+'"'+(assetLibraryCtx.selectionMode?'':' hidden')+'><i class="fas '+(selected?'fa-check':'fa-plus')+'"></i></span><video src="'+escapeHtml(url)+'" muted playsinline preload="metadata"></video></div>'
            : '<div class="asset-library-preview'+(url?'':' is-empty')+'"><span class="asset-library-check'+(selected?' is-selected':'')+'"'+(assetLibraryCtx.selectionMode?'':' hidden')+'><i class="fas '+(selected?'fa-check':'fa-plus')+'"></i></span>'+(url?'<img src="'+escapeHtml(url)+'" alt="'+name+'" loading="lazy">':'<i class="fas fa-image asset-library-preview-icon"></i>')+'</div>';
        return '<button type="button" class="asset-library-item'+(assetLibraryCtx.selectionMode?' is-selection-mode':'')+(selected?' is-selected':'')+'" data-asset-url="'+escapeHtml(url)+'" data-asset-path="'+escapeHtml(path)+'" aria-pressed="'+(assetLibraryCtx.selectionMode&&selected?'true':'false')+'" title="'+(assetLibraryCtx.selectionMode?(selected?'Selected: ':'Select: '):'Use ')+name+'">'
            +preview
            +'<div class="asset-library-info"><div class="asset-library-name" title="'+name+'">'+name+'</div><div class="asset-library-meta">'+meta+'</div><div class="asset-library-action"><i class="fas '+(assetLibraryCtx.selectionMode?(selected?'fa-check-circle':'fa-circle-plus'):'fa-arrow-up-right-from-square')+'"></i> '+(assetLibraryCtx.selectionMode?(selected?'Selected':'Select'):'Use this')+'</div></div>'
            +'</button>';
    }).join("");
    grid.querySelectorAll(".asset-library-preview img,.asset-library-preview video").forEach(function(media){
        media.addEventListener("error",function(){
            var preview=media.parentNode;
            if(!preview)return;
            preview.classList.add("is-empty");
            preview.innerHTML='<i class="fas fa-image asset-library-preview-icon"></i>';
        });
    });
    grid.querySelectorAll(".asset-library-item[data-asset-url]").forEach(function(card){
        card.addEventListener("click",function(e){
            var url=String(card.getAttribute("data-asset-url")||"");
            var path=String(card.getAttribute("data-asset-path")||"");
            if(assetLibraryCtx.selectionMode){
                e.preventDefault();
                toggleAssetLibrarySelection(path);
                return;
            }
            if(url!==""&&typeof assetLibraryCtx.onSelect==="function"){
                assetLibraryCtx.onSelect(url);
                closeAssetLibraryModal();
            }
        });
    });
    syncAssetLibraryToolbar();
}
function loadAssetLibraryAssets(kind){
    var k=String(kind||"image").toLowerCase();
    assetLibraryCtx.statusBase="Loading library...";
    assetLibraryCtx.statusOverride="";
    syncAssetLibraryToolbar();
    if(assetLibraryCtx.grid)assetLibraryCtx.grid.innerHTML='<div class="asset-library-empty">Loading stored media...</div>';
    var params=[];
    if(k&&k!=="any")params.push("kind="+encodeURIComponent(k));
    params.push("_="+Date.now());
    var qs="?"+params.join("&");
    return fetch(assetLibraryUrl+qs,{
        cache:"no-store",
        headers:{
            "Accept":"application/json",
            "X-CSRF-TOKEN":csrf
        }
    }).then(function(r){
        return r.text().then(function(t){
            var data={};
            try{data=t?JSON.parse(t):{};}catch(_e){data={message:t||"Failed to load assets"};}
            if(!r.ok){
                throw new Error((data&&data.message)?data.message:("HTTP "+r.status));
            }
            return data;
        });
    }).then(function(data){
        var assets=(data&&Array.isArray(data.assets))?data.assets:[];
        renderAssetLibraryGrid(assets);
        assetLibraryCtx.statusBase=assets.length?("Showing "+assets.length+" stored file"+(assets.length===1?"":"s")):"No stored media yet";
        assetLibraryCtx.statusOverride="";
        syncAssetLibraryToolbar();
    }).catch(function(err){
        assetLibraryCtx.assets=[];
        assetLibraryCtx.statusBase=(err&&err.message)||"Failed to load library";
        assetLibraryCtx.statusOverride="";
        syncAssetLibraryToolbar();
        if(assetLibraryCtx.grid)assetLibraryCtx.grid.innerHTML='<div class="asset-library-empty">'+escapeHtml((err&&err.message)||"Failed to load stored media.")+'</div>';
    });
}
function openAssetLibraryModal(options){
    var opts=(options&&typeof options==="object")?options:{};
    ensureAssetLibraryModal();
    assetLibraryCtx.kind=String(opts.kind||"image").toLowerCase();
    assetLibraryCtx.titleText=String(opts.title||"Asset Library");
    assetLibraryCtx.onSelect=typeof opts.onSelect==="function"?opts.onSelect:null;
    assetLibraryCtx.selectionMode=false;
    assetLibraryCtx.selectedPaths=new Set();
    assetLibraryCtx.assets=[];
    assetLibraryCtx.busy=false;
    assetLibraryCtx.statusBase="Loading library...";
    assetLibraryCtx.statusOverride="";
    if(assetLibraryCtx.title)assetLibraryCtx.title.textContent=assetLibraryCtx.titleText;
    if(assetLibraryCtx.sub)assetLibraryCtx.sub.textContent=String(opts.subtitle||"Reuse files you already uploaded, or add a new one.");
    if(assetLibraryCtx.uploadInput)assetLibraryCtx.uploadInput.setAttribute("accept",assetLibraryAcceptForKind(assetLibraryCtx.kind));
    assetLibraryCtx.modal.classList.add("open");
    syncAssetLibraryToolbar();
    loadAssetLibraryAssets(assetLibraryCtx.kind);
}
function attachAssetLibraryButton(buttonId,options){
    var btn=document.getElementById(buttonId);
    if(!btn||btn.getAttribute("data-asset-library-bound")==="1")return;
    btn.setAttribute("data-asset-library-bound","1");
    btn.addEventListener("click",function(e){
        e.preventDefault();
        e.stopPropagation();
        openAssetLibraryModal(options);
    });
}
function ensureAssetLibraryLauncher(anchorId,buttonId,label){
    var anchor=document.getElementById(anchorId);
    if(!anchor)return null;
    var existing=document.getElementById(buttonId);
    if(existing)return existing;
    var btn=document.createElement("button");
    btn.type="button";
    btn.id=buttonId;
    btn.className="fb-btn asset-library-launch";
    btn.innerHTML='<i class="fas fa-photo-film"></i> '+escapeHtml(label||"Open Asset Library");
    anchor.insertAdjacentElement("afterend",btn);
    return btn;
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

function helpContentForKey(key){
    switch(String(key||"").trim()){
        case "positionSize":
            return {title:"Position & Size",text:"Use <strong>X/Y</strong> to place an element and <strong>W/H</strong> to size it. These only apply when the element is in <strong>absolute</strong> positioning mode."};
        case "layering":
            return {title:"Layer",text:"Layer controls change stacking. Higher <strong>Z-Index</strong> appears on top. Forward/Backward moves the element within the stack."};
        case "contentWidth":
            return {title:"Content Width",text:"Sets the max width of the content container. <strong>Full</strong> uses the full page width; smaller sizes center the content and add side margins."};
        case "textAlignment":
            return {title:"Alignment",text:"Aligns text/content <strong>inside</strong> the element. It does not move the element itself."};
        case "mediaAlignment":
            return {title:"Alignment",text:"Controls how the image/video aligns inside its container (left, center, right). It doesnâ€™t change the container position."};
        case "formAlignment":
            return {title:"Form Alignment",text:"Positions the whole form block within its column/section. Pair with Form width for precise placement."};
        case "buttonAlignment":
            return {title:"Button Alignment",text:"Aligns the submit button <strong>inside</strong> the form width (left, center, right)."};
        case "buttonAction":
            return {title:"Button Action",text:"Choose what happens on click: go to the next step, a specific step, or a custom URL."};
        case "buttonTarget":
            return {title:"Button Target",text:"Pick a specific step or enter a custom URL. Use full URLs for external links."};
        case "menuTarget":
            return {title:"Menu Target",text:"<strong>Section anchor</strong> jumps to a section with a matching Section ID. <strong>Custom link</strong> uses a normal URL."};
        case "sectionAnchor":
            return {title:"Section ID (Anchor)",text:"This ID becomes a jump target like <strong>#contact</strong>. Use letters, numbers, dashes, and underscores only."};
        case "bgDisplay":
            return {title:"Background Image Display",text:"Controls how the background image fits: cover (full), repeat, or stretch to 100% width."};
        case "carouselBehavior":
            return {title:"Carousel Behavior",text:"<strong>Manual</strong> uses arrows. <strong>Automatic</strong> auto-plays and hides arrows."};
        case "carouselSize":
            return {title:"Carousel Size",text:"Sets a fixed frame size. Content inside will adapt to the carouselâ€™s width/height."};
        default:
            return null;
    }
}
function ensureGenericHelpModal(){
    var m=document.getElementById("fbGenericHelpModal");
    if(m)return m;
    m=document.createElement("div");
    m.id="fbGenericHelpModal";
    m.className="fb-help-modal";
    m.innerHTML='<div class="fb-help-card" role="dialog" aria-modal="true" aria-label="Help">'
        +'<button type="button" class="fb-help-close" id="fbGenericHelpClose" aria-label="Close">X</button>'
        +'<h4 class="fb-help-title" id="fbGenericHelpTitle"></h4>'
        +'<div class="fb-help-text" id="fbGenericHelpText"></div>'
        +'</div>';
    m.addEventListener("click",function(e){if(e.target===m)closeGenericHelpModal();});
    document.body.appendChild(m);
    var c=document.getElementById("fbGenericHelpClose");
    if(c)c.addEventListener("click",function(e){e.preventDefault();closeGenericHelpModal();});
    return m;
}
function openGenericHelpModal(key){
    var data=helpContentForKey(key);
    if(!data)return;
    var m=ensureGenericHelpModal();
    var title=document.getElementById("fbGenericHelpTitle");
    var text=document.getElementById("fbGenericHelpText");
    if(title)title.textContent=data.title||"Help";
    if(text)text.innerHTML=data.text||"";
    m.classList.add("open");
}
function closeGenericHelpModal(){
    var m=document.getElementById("fbGenericHelpModal");
    if(m)m.classList.remove("open");
}

function renderSettings(){
    settingsTitle.textContent="Settings Panel";
    const inCarousel=!!state.carouselSel;
    const selKind=inCarousel?(state.carouselSel&&state.carouselSel.k):(state.sel&&state.sel.k);
    const t=selectedTarget();
    if(state.linkPick){
        var src=findElementById(state.linkPick.sourceId);
        if(!src||src.type!=="countdown"){
            state.linkPick=null;
        }
    }
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
    const posControls=isAbsEl?'<div class="menu-split"></div>'+helpTitleHtml("positionSize","Position &amp; Size")+'<div class="size-position"><div class="size-grid"><div class="fld"><label>X</label><input id="elPosX" type="number" min="0" step="1" value="'+(Number(t.settings.freeX)||0)+'"></div><div class="fld"><label>Y</label><input id="elPosY" type="number" min="0" step="1" value="'+(Number(t.settings.freeY)||0)+'"></div></div><div class="size-grid" style="margin-top:6px;"><div class="fld"><label>W</label><input id="elSizeW" type="number" min="30" step="1" value="'+curW+'"></div><div class="fld"><label>H</label><input id="elSizeH" type="number" min="20" step="1" value="'+curH+'"></div></div></div><div class="menu-split"></div>'+helpTitleHtml("layering","Layer")+'<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px;"><button type="button" id="btnLayerFwd" class="fb-btn" style="font-size:11px;">Forward</button><button type="button" id="btnLayerBwd" class="fb-btn" style="font-size:11px;">Backward</button><button type="button" id="btnLayerFront" class="fb-btn" style="font-size:11px;">To Front</button><button type="button" id="btnLayerBack" class="fb-btn" style="font-size:11px;">To Back</button></div><div class="fld" style="margin-bottom:8px;"><label>Z-Index</label><input id="elZIndex" type="number" min="0" step="1" value="'+curZ+'"></div>':'';
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
        wrap.innerHTML=helpLabelHtml("bgDisplay","Display image")+'<select id="bgImgDisplayMode"><option value="default">Default</option><option value="full-center-fixed">Full Center (Fixed)</option><option value="repeat">Repeat</option><option value="fill-100">Fill 100% Width</option></select>';
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
    function mountBackgroundAssetLibrary(anchorId,buttonId,inputId,titleText){
        if(!ensureAssetLibraryLauncher(anchorId,buttonId,"Open Asset Library"))return;
        attachAssetLibraryButton(buttonId,{
            kind:"image",
            title:titleText||"Asset Library",
            subtitle:"Reuse a stored image or upload a new one for this background.",
            onSelect:function(url){
                saveToHistory();
                var bgInput=document.getElementById(inputId);
                var s=sty();
                s.backgroundImage='url('+url+')';
                if(bgInput)bgInput.value=url;
                renderCanvas();
            }
        });
    }
    function mountMediaAssetLibrary(anchorId,buttonId,kind,titleText,onSelect){
        if(!ensureAssetLibraryLauncher(anchorId,buttonId,"Open Asset Library"))return;
        attachAssetLibraryButton(buttonId,{
            kind:kind,
            title:titleText||"Asset Library",
            subtitle:"Reuse stored media or upload a new file.",
            onSelect:onSelect
        });
    }
    function radiusHelpLabelHtml(btnId,labelText){
        return '<div class="setting-label-help"><label style="margin:0;">'+labelText+'</label><button type="button" id="'+btnId+'" class="setting-help-icon" aria-label="'+labelText+' help">?</button></div>';
    }
    function helpLabelHtml(helpKey,labelText){
        return '<div class="setting-label-help"><label style="margin:0;">'+labelText+'</label><button type="button" class="setting-help-icon" data-help-key="'+helpKey+'" aria-label="'+labelText+' help">?</button></div>';
    }
    function helpTitleHtml(helpKey,titleHtml){
        return '<div class="menu-section-title fb-help-row">'+titleHtml+'<button type="button" class="setting-help-icon" data-help-key="'+helpKey+'" aria-label="'+titleHtml+' help">?</button></div>';
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
    function bindGenericHelpButtons(){
        settings.querySelectorAll("[data-help-key]").forEach(function(b){
            if(b.getAttribute("data-help-bound")==="1")return;
            b.setAttribute("data-help-bound","1");
            b.addEventListener("click",function(e){
                e.preventDefault();
                e.stopPropagation();
                openGenericHelpModal(b.getAttribute("data-help-key"));
            });
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
    function spacingControlsHtml(pad,mar){
        return '<div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div>';
    }
    function mountSpacingControls(){
        var paddingLinked=false,marginLinked=false;
        function syncPadding(){saveToHistory();var pt=Number(document.getElementById("pTop").value)||0,pr=Number(document.getElementById("pRight").value)||0,pb=Number(document.getElementById("pBottom").value)||0,pl=Number(document.getElementById("pLeft").value)||0;if(paddingLinked){document.getElementById("pRight").value=pt;document.getElementById("pBottom").value=pt;document.getElementById("pLeft").value=pt;sty().padding=spacingToCss([pt,pt,pt,pt]);}else sty().padding=spacingToCss([pt,pr,pb,pl]);renderCanvas();}
        function syncMargin(){saveToHistory();var mt=Number(document.getElementById("mTop").value)||0,mr=Number(document.getElementById("mRight").value)||0,mb=Number(document.getElementById("mBottom").value)||0,ml=Number(document.getElementById("mLeft").value)||0;if(marginLinked){document.getElementById("mRight").value=mt;document.getElementById("mBottom").value=mt;document.getElementById("mLeft").value=mt;sty().margin=spacingToCss([mt,mt,mt,mt]);}else sty().margin=spacingToCss([mt,mr,mb,ml]);renderCanvas();}
        ["pTop","pRight","pBottom","pLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncPadding);});
        ["mTop","mRight","mBottom","mLeft"].forEach(id=>{var el=document.getElementById(id);if(el)el.addEventListener("input",syncMargin);});
        var linkPad=document.getElementById("linkPad"),linkMar=document.getElementById("linkMar");
        if(linkPad)linkPad.onclick=()=>{saveToHistory();paddingLinked=!paddingLinked;linkPad.classList.toggle("linked",paddingLinked);if(paddingLinked){var v=document.getElementById("pTop").value;document.getElementById("pRight").value=v;document.getElementById("pBottom").value=v;document.getElementById("pLeft").value=v;sty().padding=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
        if(linkMar)linkMar.onclick=()=>{saveToHistory();marginLinked=!marginLinked;linkMar.classList.toggle("linked",marginLinked);if(marginLinked){var v=document.getElementById("mTop").value;document.getElementById("mRight").value=v;document.getElementById("mBottom").value=v;document.getElementById("mLeft").value=v;sty().margin=spacingToCss([Number(v)||0,Number(v)||0,Number(v)||0,Number(v)||0]);renderCanvas();}};
    }
    if(selKind==="sec"){
        t.settings=t.settings||{};
        var padDef=[20,20,20,20],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var cw=(t.settings&&t.settings.contentWidth)||"full";
        settings.innerHTML='<div class="menu-section-title">Layout</div>'+helpLabelHtml("contentWidth","Content width")+'<select id="secCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select>'+helpLabelHtml("sectionAnchor","Section ID (anchor)")+'<input id="secAnchor" placeholder="contact"><div class="meta">Example: contact, etc</div><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+radiusHelpLabelHtml("secRadiusHelp","Border radius")+'<div class="px-wrap"><input id="secRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'+remove;
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
        mountBackgroundAssetLibrary("bgUp","bgAssetLibraryBtn","bgImg","Background Asset Library");
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
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Image type</label><select id="imgSourceType"><option value="direct"'+(imageSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(imageSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+imageSourceFields+'<label>Alt</label><input id="alt"><div class="menu-split"></div><div class="menu-section-title">Layout</div>'+helpLabelHtml("mediaAlignment","Alignment")+'<select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><label>Height</label><input id="h" placeholder="auto"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b">'+radiusHelpLabelHtml("imgRadiusHelp","Border radius")+'<div class="img-radius-panel"><button type="button" id="imgRadiusLink" class="img-radius-link'+(radiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="imgRadTl" type="number" value="'+rad[0]+'"><input id="imgRadTr" type="number" value="'+rad[1]+'"><input id="imgRadBr" type="number" value="'+rad[2]+'"><input id="imgRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh">'+posControls+moveControls+remove;
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
        mountMediaAssetLibrary(imageSourceType==="upload"?"imgCurrentFile":"src","imgAssetLibraryBtn","image","Image Asset Library",function(url){
            saveToHistory();
            t.settings=t.settings||{};
            t.settings.imageSourceType="upload";
            t.settings.src=url;
            render();
        });
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
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Video type</label><select id="vidSourceType"><option value="direct"'+(videoSourceType==="direct"?' selected':'')+'>Direct link</option><option value="upload"'+(videoSourceType==="upload"?' selected':'')+'>Upload file</option></select>'+videoSourceFields+'<div class="menu-split"></div><div class="menu-section-title">Layout</div>'+helpLabelHtml("mediaAlignment","Alignment")+'<select id="align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Width</label><input id="w" placeholder="100%"><label>Height</label><input id="h" placeholder="auto"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Border</label><input id="b">'+radiusHelpLabelHtml("vidRadiusHelp","Border radius")+'<div class="img-radius-panel"><button type="button" id="vidRadiusLink" class="img-radius-link'+(videoRadiusLinked?' linked':'')+'" title="Link corners"><i class="fas fa-link"></i></button><div class="img-radius-row"><input id="vidRadTl" type="number" value="'+rad[0]+'"><input id="vidRadTr" type="number" value="'+rad[1]+'"><input id="vidRadBr" type="number" value="'+rad[2]+'"><input id="vidRadBl" type="number" value="'+rad[3]+'"></div></div><label>Shadow</label><input id="sh"><div class="menu-split"></div><div class="menu-section-title">Behavior</div><label>Auto play</label><select id="vAutoplay"><option value="off">Off</option><option value="on">On</option></select><label>Controls</label><select id="vControls"><option value="on">On</option><option value="off">Off</option></select>'+posControls+moveControls+remove;
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
        mountMediaAssetLibrary(videoSourceType==="upload"?"vidCurrentFile":"src","vidAssetLibraryBtn","video","Video Asset Library",function(url){
            saveToHistory();
            t.settings=t.settings||{};
            t.settings.videoSourceType="upload";
            t.settings.src=url;
            render();
        });
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
        settings.innerHTML='<div class="menu-section-title">Layout</div>'+helpLabelHtml("contentWidth","Content width")+'<select id="rowCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*"><div class="row-border-card"><div class="row-border-head"><strong>Border</strong></div><select id="rowBorderStyle"><option value="none">None</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option><option value="double">Double</option></select>'+radiusHelpLabelHtml("rowRadiusHelp","Corner radius")+radiusBlock+'<div class="size-link"><button type="button" id="rowRadiusToggle" title="Toggle radius mode"><i class="fas fa-expand"></i></button><span>'+(perCorner?'Per corner':'Single value')+'</span></div></div><div class="menu-split"></div><div class="menu-section-title">Behavior</div><button type="button" id="rowBorderReset" class="fb-btn" style="width:100%;"><i class="fas fa-rotate-right"></i> Reset row border</button>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("rowCw",rowCw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        var bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        mountBackgroundAssetLibrary("bgUp","bgAssetLibraryBtn","bgImg","Background Asset Library");
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
        settings.innerHTML='<div class="menu-section-title">Layout</div>'+layoutHtml+helpLabelHtml("contentWidth","Content width")+'<select id="colCw"><option value="full">Full page</option><option value="wide">Wide</option><option value="medium">Medium</option><option value="small">Small</option><option value="xsmall">Extra small</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+radiusHelpLabelHtml("colRadiusHelp","Border radius")+'<div class="px-wrap"><input id="colRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>'+remove;
        bind("bg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("bgImg",readBgImageUrl(),v=>{var s=sty();s.backgroundImage=(v&&String(v).trim()!=="")?('url('+String(v).trim()+')'):"";renderCanvas();},{undo:true});
        bind("colCw",colCw,v=>{t.settings=t.settings||{};t.settings.contentWidth=v;renderCanvas();},{undo:true});
        var bgUp=document.getElementById("bgUp");if(bgUp)bgUp.onchange=()=>{if(bgUp.files&&bgUp.files[0]){saveToHistory();var bgImg=document.getElementById("bgImg");uploadImage(bgUp.files[0],url=>{var s=sty();s.backgroundImage='url('+url+')';if(bgImg)bgImg.value=url;renderCanvas();},"Background image upload");}};
        mountBackgroundAssetLibrary("bgUp","bgAssetLibraryBtn","bgImg","Background Asset Library");
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
                +helpLabelHtml("carouselSize","Fixed width")+'<div class="px-wrap"><input id="carFixedW" type="number" min="50" step="1"><span class="px-unit">px</span></div>'+helpLabelHtml("carouselSize","Fixed height")+'<div class="px-wrap"><input id="carFixedH" type="number" min="50" step="1"><span class="px-unit">px</span></div>'
                +'<div class="menu-split"></div><div class="menu-section-title">Behavior</div>'+helpLabelHtml("carouselBehavior","Slideshow mode")+'<select id="carSlideMode"><option value="manual">Manual (use arrows)</option><option value="auto">Automatic (no arrows)</option></select><div class="meta">Slide selection, view, and ordering controls are in the Content section above.</div>'
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
                var body=collapsed?'':'<input id="miLabel_'+idx+'" value="'+String((it&&it.label)||"").replace(/"/g,'&quot;')+'" placeholder="Label">'+helpLabelHtml("menuTarget","Target")+'<select id="miMode_'+idx+'"><option value="section"'+(useSectionMode?' selected':'')+'>Section anchor</option><option value="custom"'+(!useSectionMode?' selected':'')+'>Custom link</option></select><div id="miSectionWrap_'+idx+'"'+(useSectionMode?'':' style="display:none;"')+'><label>Section</label><select id="miAnchor_'+idx+'"'+(sectionAnchors.length===0?' disabled':'')+'>'+anchorOptions+'</select><div class="meta">Uses #anchor automatically.</div></div><div id="miCustomWrap_'+idx+'"'+(!useSectionMode?'':' style="display:none;"')+'><label>Link</label><input id="miUrl_'+idx+'" value="'+String((it&&it.url)||"").replace(/"/g,'&quot;')+'" placeholder="e.g. /about or https://example.com"></div><label><input id="miNew_'+idx+'" type="checkbox"'+((it&&it.newWindow)?' checked':'')+'> Open in a new window</label><label><input id="miSub_'+idx+'" type="checkbox"'+((it&&it.hasSubmenu)?' checked':'')+'> Has submenu</label>';
                return '<div class="menu-item-card"><div class="menu-item-head"><strong>Menu item '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button><button type="button" class="menu-toggle" data-idx="'+idx+'" title="Toggle"><i class="fas '+(collapsed?'fa-chevron-down':'fa-chevron-up')+'"></i></button></div></div>'+body+'</div>';
            }).join("");
            settings.innerHTML='<div class="menu-panel-title">Menu</div><div class="menu-section-title">Content</div>'+cards+'<button type="button" id="addMenuItem" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add menu item</button><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Font family</label><select id="mFont"><option value="">Same font as the page</option>'+fonts.map(f=>'<option value="'+f.value.replace(/"/g,'&quot;')+'">'+f.label+'</option>').join('')+'</select><div class="menu-typo-grid"><div class="px-wrap"><input id="mFs" type="number" step="1"><span class="px-unit">px</span></div><div class="px-wrap"><input id="mLh" type="number" step="0.1"><span class="px-unit">lh</span></div></div><label>Text style</label><div class="menu-style-row"><button type="button" id="mBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="mItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div><div class="menu-split"></div><div class="menu-section-title">Layout</div><div class="menu-align-row"><button type="button" class="menu-align-btn" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn" data-align="right"><i class="fas fa-align-right"></i></button></div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Letter spacing</label><div class="menu-slider-row"><input id="mLsRange" type="range" min="0" max="20" step="0.1"><input id="mLsNum" type="number" min="0" max="20" step="0.1"></div><label>Text color</label><input id="mTextColor" type="color"><label>Menu items underline color</label><input id="mUnderlineColor" type="color"><label>Background color</label><input id="mBgColor" type="color"><label>Background image URL</label><input id="mBgImg" placeholder="https://..."><label>Upload background image</label><input id="mBgUp" type="file" accept="image/*"><div class="menu-split"></div><div class="menu-section-title">Spacing</div><label>Spacing between menu items</label><div class="menu-slider-row"><input id="mGapRange" type="range" min="0" max="64" step="1"><input id="mGapNum" type="number" min="0" max="64" step="1"></div><label>Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>&harr;</span></button><span>Link</span></div></div><label>Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>&harr;</span></button><span>Link</span></div></div>'+posControls+moveControls+remove;
            settings.insertAdjacentHTML("beforeend",'<div class="menu-split"></div><div class="menu-section-title">Right CTA Button</div><label>Button label</label><input id="mLeftBtnLabel" placeholder="Get Started"><label>Button URL</label><input id="mLeftBtnUrl" placeholder="#"><label>Button background color</label><input id="mLeftBtnBg" type="color"><label>Button text color</label><input id="mLeftBtnText" type="color"><label>Button text size</label><div class="px-wrap"><input id="mLeftBtnTextSize" type="number" min="10" max="48" step="1"><span class="px-unit">px</span></div><label>Button text style</label><div class="menu-style-row"><button type="button" id="mLeftBtnBold" class="menu-align-btn" title="Bold"><i class="fas fa-bold"></i></button><button type="button" id="mLeftBtnItalic" class="menu-align-btn" title="Italic"><i class="fas fa-italic"></i></button></div><label>Button border radius</label><div class="px-wrap"><input id="mLeftBtnRadius" type="number" min="0" max="80" step="1"><span class="px-unit">px</span></div><label>Button size (vertical padding)</label><div class="px-wrap"><input id="mLeftBtnPadY" type="number" min="4" max="40" step="1"><span class="px-unit">px</span></div><label>Button size (horizontal padding)</label><div class="px-wrap"><input id="mLeftBtnPadX" type="number" min="8" max="80" step="1"><span class="px-unit">px</span></div><div class="menu-split"></div><div class="menu-section-title">Left Logo</div><label>Logo image URL</label><input id="mRightLogoUrl" placeholder="https://..."><label>Logo alt text</label><input id="mRightLogoAlt" placeholder="Logo"><label>Upload logo image</label><input id="mRightLogoUp" type="file" accept="image/*">');

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
            mountBackgroundAssetLibrary("mBgUp","mBgAssetLibraryBtn","mBgImg","Menu Background Asset Library");
            bind("mLeftBtnLabel",(t.settings&&t.settings.leftButtonLabel)||"Get Started",v=>{t.settings=t.settings||{};t.settings.leftButtonLabel=v;renderCanvas();},{undo:true});
            bind("mLeftBtnUrl",(t.settings&&t.settings.leftButtonUrl)||"#",v=>{t.settings=t.settings||{};t.settings.leftButtonUrl=v;renderCanvas();},{undo:true});
            bind("mLeftBtnBg",(t.settings&&t.settings.leftButtonBgColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.leftButtonBgColor=v;renderCanvas();},{undo:true});
            bind("mLeftBtnText",(t.settings&&t.settings.leftButtonTextColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.leftButtonTextColor=v;renderCanvas();},{undo:true});
            bind("mLeftBtnTextSize",(t.settings&&t.settings.leftButtonTextSize)||14,v=>{t.settings=t.settings||{};var n=Number(v);t.settings.leftButtonTextSize=(!isNaN(n)&&n>=10&&n<=48)?Math.round(n):14;renderCanvas();},{undo:true});
            bind("mLeftBtnRadius",(t.settings&&t.settings.leftButtonBorderRadius)||999,v=>{t.settings=t.settings||{};var n=Number(v);t.settings.leftButtonBorderRadius=(!isNaN(n)&&n>=0&&n<=80)?Math.round(n):999;renderCanvas();},{undo:true});
            bind("mLeftBtnPadY",(t.settings&&t.settings.leftButtonPaddingY)||8,v=>{t.settings=t.settings||{};var n=Number(v);t.settings.leftButtonPaddingY=(!isNaN(n)&&n>=4&&n<=40)?Math.round(n):8;renderCanvas();},{undo:true});
            bind("mLeftBtnPadX",(t.settings&&t.settings.leftButtonPaddingX)||14,v=>{t.settings=t.settings||{};var n=Number(v);t.settings.leftButtonPaddingX=(!isNaN(n)&&n>=8&&n<=80)?Math.round(n):14;renderCanvas();},{undo:true});
            var mLeftBtnBold=document.getElementById("mLeftBtnBold"),mLeftBtnItalic=document.getElementById("mLeftBtnItalic");
            function syncMenuBtnStyleButtons(){
                var s=t.settings||{};
                if(mLeftBtnBold)mLeftBtnBold.classList.toggle("active",!!s.leftButtonBold);
                if(mLeftBtnItalic)mLeftBtnItalic.classList.toggle("active",!!s.leftButtonItalic);
            }
            if(mLeftBtnBold)mLeftBtnBold.onclick=()=>{saveToHistory();t.settings=t.settings||{};t.settings.leftButtonBold=!t.settings.leftButtonBold;syncMenuBtnStyleButtons();renderCanvas();};
            if(mLeftBtnItalic)mLeftBtnItalic.onclick=()=>{saveToHistory();t.settings=t.settings||{};t.settings.leftButtonItalic=!t.settings.leftButtonItalic;syncMenuBtnStyleButtons();renderCanvas();};
            syncMenuBtnStyleButtons();
            bind("mRightLogoUrl",(t.settings&&t.settings.rightLogoUrl)||"",v=>{t.settings=t.settings||{};t.settings.rightLogoUrl=v;renderCanvas();},{undo:true});
            bind("mRightLogoAlt",(t.settings&&t.settings.rightLogoAlt)||"Logo",v=>{t.settings=t.settings||{};t.settings.rightLogoAlt=v;renderCanvas();},{undo:true});
            var mRightLogoUp=document.getElementById("mRightLogoUp");
            if(mRightLogoUp)mRightLogoUp.onchange=()=>{if(mRightLogoUp.files&&mRightLogoUp.files[0]){saveToHistory();var mRightLogoUrl=document.getElementById("mRightLogoUrl");uploadImage(mRightLogoUp.files[0],url=>{t.settings=t.settings||{};t.settings.rightLogoUrl=url;if(mRightLogoUrl)mRightLogoUrl.value=url;renderCanvas();},"Menu left logo upload");}};
            mountMediaAssetLibrary("mRightLogoUp","mRightLogoAssetLibraryBtn","image","Menu Left Logo Asset Library",function(url){
                saveToHistory();
                t.settings=t.settings||{};
                t.settings.rightLogoUrl=url;
                var mRightLogoUrl=document.getElementById("mRightLogoUrl");
                if(mRightLogoUrl)mRightLogoUrl.value=url;
                renderCanvas();
            });

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
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Submit button text</label><input id="formSubmitText" placeholder="Submit"><div class="menu-split"></div><div class="menu-section-title">Form inputs</div><div id="formFieldsEditor"></div><button type="button" id="addFormInput" class="fb-btn" style="width:100%;margin:6px 0 10px;">Add form-input</button><div class="menu-split"></div><div class="menu-section-title">Layout</div>'+helpLabelHtml("formAlignment","Alignment")+'<select id="formAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Form width</label><input id="formWidth" placeholder="100%"><div class="meta" style="margin-top:8px;">Set width in % (example: 50%) and place using alignment only.</div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Label text color</label><input id="formLabelColor" type="color"><label>Placeholder text color</label><input id="formPlaceholderColor" type="color"><label>Submit button color</label><input id="formBtnBgColor" type="color"><label>Submit button text color</label><input id="formBtnTextColor" type="color"><label>Submit button text style</label><div class="menu-style-row"><button type="button" id="formBtnBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="formBtnItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div>'+helpLabelHtml("buttonAlignment","Submit button alignment")+'<div class="menu-align-row"><button type="button" class="menu-align-btn form-btn-align" data-align="left"><i class="fas fa-align-left"></i></button><button type="button" class="menu-align-btn form-btn-align" data-align="center"><i class="fas fa-align-center"></i></button><button type="button" class="menu-align-btn form-btn-align" data-align="right"><i class="fas fa-align-right"></i></button></div><label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">'+posControls+moveControls+remove;
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
        mountBackgroundAssetLibrary("bgUp","bgAssetLibraryBtn","bgImg","Form Background Asset Library");
    } else if(selKind==="el"&&t.type==="shipping_details"){
        t.settings=t.settings||{};
        t.settings.fields=normalizeFormFields(t.settings.fields,false);
        settings.innerHTML='<div class="menu-section-title">Shipping Details</div><div class="meta" style="margin:0 0 10px;">Dedicated delivery and customer information block for physical-product checkout pages.</div><label>Heading</label><input id="shipHeading"><label>Subtitle</label><input id="shipSubtitle"><label>Width</label><input id="shipWidth" placeholder="420px"><label>Label color</label><input id="shipLabelColor" type="color"><label>Placeholder color</label><input id="shipPlaceholderColor" type="color"><label>Background color</label><input id="shipBg" type="color"><label>Border</label><input id="shipBorder"><label>Shadow</label><input id="shipShadow">'+posControls+moveControls+remove;
        bind("shipHeading",(t.settings&&t.settings.heading)||"Shipping Details",v=>{t.settings=t.settings||{};t.settings.heading=v||"Shipping Details";renderCanvas();},{undo:true});
        bind("shipSubtitle",(t.settings&&t.settings.subtitle)||"Enter your delivery and contact information before placing the order.",v=>{t.settings=t.settings||{};t.settings.subtitle=v||"";renderCanvas();},{undo:true});
        bind("shipWidth",(t.style&&t.style.width)||"420px",v=>{sty().width=v||"420px";sty().height="";},{undo:true});
        bind("shipLabelColor",(t.settings&&t.settings.labelColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.labelColor=v||"#240E35";renderCanvas();},{undo:true});
        bind("shipPlaceholderColor",(t.settings&&t.settings.placeholderColor)||"#94a3b8",v=>{t.settings=t.settings||{};t.settings.placeholderColor=v||"#94a3b8";renderCanvas();},{undo:true});
        bind("shipBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("shipBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
        bind("shipShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
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
    } else if(selKind==="el"&&t.type==="review_form"){
        t.settings=t.settings||{};
        var padDef=[18,18,18,18],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Heading</label><input id="rfHeading"><label>Subtitle</label><textarea id="rfSubtitle" rows="4"></textarea><div class="menu-split"></div><div class="menu-section-title">Physical Product Copy</div><label>Physical heading</label><input id="rfPhysicalHeading"><label>Physical subtitle</label><textarea id="rfPhysicalSubtitle" rows="3"></textarea><div class="meta">Use this wording when the funnel purpose is physical product, so the thank-you page asks about the order experience instead of the item itself.</div><div class="menu-split"></div><label>Button label</label><input id="rfButton"><label>Success message</label><textarea id="rfSuccess" rows="3"></textarea><label>Public consent label</label><input id="rfPublicLabel">'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Text color</label><input id="rfColor" type="color"><label>Background color</label><input id="rfBg" type="color"><label>Border</label><input id="rfBorder">'+radiusHelpLabelHtml("rfRadiusHelp","Border radius")+'<div class="px-wrap"><input id="rfRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="rfShadow"><label>Button color</label><input id="rfBtnBg" type="color"><label>Button text color</label><input id="rfBtnText" type="color">'+posControls+moveControls+remove;
        bind("rfHeading",(t.settings&&t.settings.heading)||"How was your experience?",v=>{t.settings.heading=v;renderCanvas();},{undo:true});
        bind("rfSubtitle",(t.settings&&t.settings.subtitle)||"Share a quick review after your order or service experience.",v=>{t.settings.subtitle=v;renderCanvas();},{undo:true});
        bind("rfPhysicalHeading",(t.settings&&t.settings.physicalHeading)||"How was your order experience?",v=>{t.settings.physicalHeading=v;renderCanvas();},{undo:true});
        bind("rfPhysicalSubtitle",(t.settings&&t.settings.physicalSubtitle)||"Tell us how the ordering and checkout experience felt while your item is on the way.",v=>{t.settings.physicalSubtitle=v;renderCanvas();},{undo:true});
        bind("rfButton",(t.settings&&t.settings.buttonLabel)||"Submit Review",v=>{t.settings.buttonLabel=v;renderCanvas();},{undo:true});
        bind("rfSuccess",(t.settings&&t.settings.successMessage)||"Thanks for the review. It is now waiting for approval.",v=>{t.settings.successMessage=v;renderCanvas();},{undo:true});
        bind("rfPublicLabel",(t.settings&&t.settings.publicLabel)||"I am okay with showing this review publicly.",v=>{t.settings.publicLabel=v;renderCanvas();},{undo:true});
        bind("rfColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
        bind("rfBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("rfBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
        bindPx("rfRadius",(t.style&&t.style.borderRadius)||"18px",v=>sty().borderRadius=v,{undo:true});
        bind("rfShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
        bind("rfBtnBg",(t.settings&&t.settings.ctaBgColor)||"#240E35",v=>{t.settings.ctaBgColor=v;renderCanvas();},{undo:true});
        bind("rfBtnText",(t.settings&&t.settings.ctaTextColor)||"#ffffff",v=>{t.settings.ctaTextColor=v;renderCanvas();},{undo:true});
        mountSpacingControls();
        bindRadiusHelpButton("rfRadiusHelp");
    } else if(selKind==="el"&&t.type==="reviews"){
        t.settings=t.settings||{};
        var padDef=[18,18,18,18],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Heading</label><input id="rvHeading"><label>Subtitle</label><textarea id="rvSubtitle" rows="4"></textarea><label>Empty state</label><textarea id="rvEmpty" rows="3"></textarea><label>Maximum reviews</label><div class="px-wrap"><input id="rvMax" type="number" min="1" step="1"><span class="px-unit">items</span></div><label>Star filter</label><select id="rvFilter"><option value="0">Show all ratings</option><option value="5">5 stars</option><option value="4">4 stars</option><option value="3">3 stars</option><option value="2">2 stars</option><option value="1">1 star</option></select><label>Layout</label><select id="rvLayout"><option value="list">List</option><option value="grid">Grid</option></select><div class="menu-split"></div><div class="menu-section-title">Space Control</div><label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input id="rvCollapsible" type="checkbox" style="width:auto;margin:0;"> Collapse long review lists</label><label>Visible reviews before collapse</label><div class="px-wrap"><input id="rvCollapsedCount" type="number" min="1" step="1"><span class="px-unit">items</span></div><label>Expand label</label><input id="rvExpandLabel"><label>Collapse label</label><input id="rvCollapseLabel"><label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input id="rvShowRating" type="checkbox" style="width:auto;margin:0;"> Show rating stars</label><label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input id="rvShowDate" type="checkbox" style="width:auto;margin:0;"> Show approval date</label>'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Text color</label><input id="rvColor" type="color"><label>Background color</label><input id="rvBg" type="color"><label>Border</label><input id="rvBorder">'+radiusHelpLabelHtml("rvRadiusHelp","Border radius")+'<div class="px-wrap"><input id="rvRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="rvShadow">'+posControls+moveControls+remove;
        bind("rvHeading",(t.settings&&t.settings.heading)||"What customers are saying",v=>{t.settings.heading=v;renderCanvas();},{undo:true});
        bind("rvSubtitle",(t.settings&&t.settings.subtitle)||"Approved reviews from this funnel appear here automatically.",v=>{t.settings.subtitle=v;renderCanvas();},{undo:true});
        bind("rvEmpty",(t.settings&&t.settings.emptyText)||"Approved reviews will appear here after customers submit them.",v=>{t.settings.emptyText=v;renderCanvas();},{undo:true});
        bind("rvMax",String((t.settings&&t.settings.maxItems)||6),v=>{var n=Number(v);t.settings.maxItems=isNaN(n)?6:Math.max(1,Math.min(24,Math.round(n)));renderCanvas();},{undo:true});
        bind("rvFilter",String((t.settings&&t.settings.filterRating)||0),v=>{var n=Number(v);t.settings.filterRating=isNaN(n)?0:Math.max(0,Math.min(5,Math.round(n)));renderCanvas();},{undo:true});
        bind("rvLayout",(t.settings&&t.settings.layout)||"list",v=>{t.settings.layout=(String(v||"list")==="grid"?"grid":"list");renderCanvas();},{undo:true});
        var rvCollapsible=document.getElementById("rvCollapsible"); if(rvCollapsible){rvCollapsible.checked=t.settings.collapsible!==false;rvCollapsible.addEventListener("change",function(){saveToHistory();t.settings.collapsible=!!rvCollapsible.checked;renderCanvas();});}
        bind("rvCollapsedCount",String((t.settings&&t.settings.collapsedCount)||3),v=>{var n=Number(v);t.settings.collapsedCount=isNaN(n)?3:Math.max(1,Math.min(24,Math.round(n)));renderCanvas();},{undo:true});
        bind("rvExpandLabel",(t.settings&&t.settings.expandLabel)||"Show all reviews",v=>{t.settings.expandLabel=v;renderCanvas();},{undo:true});
        bind("rvCollapseLabel",(t.settings&&t.settings.collapseLabel)||"Show fewer reviews",v=>{t.settings.collapseLabel=v;renderCanvas();},{undo:true});
        var rvShowRating=document.getElementById("rvShowRating"); if(rvShowRating){rvShowRating.checked=t.settings.showRating!==false;rvShowRating.addEventListener("change",function(){saveToHistory();t.settings.showRating=!!rvShowRating.checked;renderCanvas();});}
        var rvShowDate=document.getElementById("rvShowDate"); if(rvShowDate){rvShowDate.checked=t.settings.showDate===true;rvShowDate.addEventListener("change",function(){saveToHistory();t.settings.showDate=!!rvShowDate.checked;renderCanvas();});}
        bind("rvColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
        bind("rvBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("rvBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
        bindPx("rvRadius",(t.style&&t.style.borderRadius)||"18px",v=>sty().borderRadius=v,{undo:true});
        bind("rvShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
        mountSpacingControls();
        bindRadiusHelpButton("rvRadiusHelp");
    } else if(selKind==="el"&&t.type==="testimonial"){
        t.settings=t.settings||{};
        var padDef=[16,16,16,16],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        settings.innerHTML='<div class="menu-section-title">Content</div><label>Quote</label><textarea id="tsQuote" rows="4"></textarea><label>Name</label><input id="tsName"><label>Role</label><input id="tsRole"><label>Avatar URL</label><input id="tsAvatar" placeholder="https://...">'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Alignment</label><select id="tsAlign"><option value="">Default</option><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Text color</label><input id="tsColor" type="color"><label>Background color</label><input id="tsBg" type="color"><label>Border</label><input id="tsBorder">'+radiusHelpLabelHtml("tsRadiusHelp","Border radius")+'<div class="px-wrap"><input id="tsRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="tsShadow">'+posControls+moveControls+remove;
        bind("tsQuote",(t.settings&&t.settings.quote)||"",v=>{t.settings=t.settings||{};t.settings.quote=v;},{undo:true});
        bind("tsName",(t.settings&&t.settings.name)||"",v=>{t.settings=t.settings||{};t.settings.name=v;},{undo:true});
        bind("tsRole",(t.settings&&t.settings.role)||"",v=>{t.settings=t.settings||{};t.settings.role=v;},{undo:true});
        bind("tsAvatar",(t.settings&&t.settings.avatar)||"",v=>{t.settings=t.settings||{};t.settings.avatar=v;},{undo:true});
        bind("tsAlign",(t.style&&t.style.textAlign)||"",v=>sty().textAlign=v,{undo:true});
        bind("tsColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
        bind("tsBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
        bind("tsBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
        bindPx("tsRadius",(t.style&&t.style.borderRadius)||"16px",v=>sty().borderRadius=v,{undo:true});
        bind("tsShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
        mountSpacingControls();
        bindRadiusHelpButton("tsRadiusHelp");
    } else if(selKind==="el"&&t.type==="faq"){
        t.settings=t.settings||{};
        t.settings.items=normalizeFaqItems(t.settings.items);
        var padDef=[16,16,16,16],marDef=[0,0,0,0];
        function renderFaqEditor(){
            var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
            var items=t.settings.items||[];
            var cards=items.map(function(it,idx){
                var q=String((it&&it.q)||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
                var a=String((it&&it.a)||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
                return '<div class="menu-item-card" data-idx="'+idx+'"><div class="menu-item-head"><strong>FAQ '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="faqMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button><button type="button" class="faqMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button><button type="button" class="faqDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button></div></div><label>Question</label><input class="faqQ" data-idx="'+idx+'" value="'+q+'"><label>Answer</label><textarea class="faqA" data-idx="'+idx+'" rows="3">'+a+'</textarea></div>';
            }).join("");
            settings.innerHTML='<div class="menu-section-title">Content</div><div id="faqItems">'+cards+'</div><button type="button" id="addFaqItem" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add FAQ item</button>'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Alignment</label><select id="faqAlign"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select><label>Question color</label><input id="faqQColor" type="color"><label>Answer color</label><input id="faqAColor" type="color"><label>Item spacing</label><div class="px-wrap"><input id="faqGap" type="number" step="1"><span class="px-unit">px</span></div><label>Background color</label><input id="faqBg" type="color"><label>Border</label><input id="faqBorder">'+radiusHelpLabelHtml("faqRadiusHelp","Border radius")+'<div class="px-wrap"><input id="faqRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="faqShadow">'+posControls+moveControls+remove;
            settings.querySelectorAll(".faqQ").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.items[idx])return;
                    saveToHistory();
                    t.settings.items[idx].q=String(inp.value||"").trim()||("Question "+(idx+1));
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".faqA").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.items[idx])return;
                    saveToHistory();
                    t.settings.items[idx].a=String(inp.value||"").trim()||("Answer "+(idx+1));
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".faqDelete").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.items))return;
                    if(t.settings.items.length<=1)return;
                    saveToHistory();
                    t.settings.items.splice(idx,1);
                    t.settings.items=normalizeFaqItems(t.settings.items);
                    renderFaqEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".faqMoveUp").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||idx<=0||!Array.isArray(t.settings.items))return;
                    saveToHistory();
                    var tmp=t.settings.items[idx-1];
                    t.settings.items[idx-1]=t.settings.items[idx];
                    t.settings.items[idx]=tmp;
                    renderFaqEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".faqMoveDown").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.items)||idx>=t.settings.items.length-1)return;
                    saveToHistory();
                    var tmp=t.settings.items[idx+1];
                    t.settings.items[idx+1]=t.settings.items[idx];
                    t.settings.items[idx]=tmp;
                    renderFaqEditor();
                    renderCanvas();
                });
            });
            var addFaqItem=document.getElementById("addFaqItem");
            if(addFaqItem)addFaqItem.onclick=function(){
                saveToHistory();
                t.settings.items=t.settings.items||[];
                var nextIndex=t.settings.items.length+1;
                t.settings.items.push({q:"Question "+nextIndex,a:"Answer "+nextIndex});
                renderFaqEditor();
                renderCanvas();
            };
            bind("faqQColor",(t.settings&&t.settings.questionColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.questionColor=v;renderCanvas();},{undo:true});
            bind("faqAColor",(t.settings&&t.settings.answerColor)||"#475569",v=>{t.settings=t.settings||{};t.settings.answerColor=v;renderCanvas();},{undo:true});
            bind("faqGap",(t.settings&&t.settings.itemGap)!=null?String(t.settings.itemGap):"10",v=>{t.settings=t.settings||{};var n=Number(v);t.settings.itemGap=isNaN(n)?10:Math.max(0,n);renderCanvas();},{undo:true});
            bind("faqAlign",(t.settings&&t.settings.alignment)||(t.style&&t.style.textAlign)||"left",v=>{t.settings=t.settings||{};t.settings.alignment=v||"left";t.style=t.style||{};t.style.textAlign=v||"left";renderCanvas();},{undo:true});
            bind("faqBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("faqBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
            bindPx("faqRadius",(t.style&&t.style.borderRadius)||"16px",v=>sty().borderRadius=v,{undo:true});
            bind("faqShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
            mountSpacingControls();
            bindRadiusHelpButton("faqRadiusHelp");
        }
        renderFaqEditor();
    } else if(selKind==="el"&&t.type==="product_offer"){
        t.settings=t.settings||{};
        t.settings.features=normalizeFeatureList(t.settings.features);
        t.settings.media=normalizeProductOfferMediaList(t.settings.media);
        var popadDef=[18,18,18,18],pomarDef=[0,0,0,0];
        function renderProductOfferEditor(){
            var pad=parseSpacing(t.style&&t.style.padding,popadDef),mar=parseSpacing(t.style&&t.style.margin,pomarDef);
            var currentProductStepType=templateStepType(cur());
            var isCheckoutProductEditor=currentProductStepType==="checkout";
            var isOfferProductEditor=currentProductStepType==="upsell"||currentProductStepType==="downsell";
            var mediaList=t.settings.media||[];
            var features=t.settings.features||[];
            var mediaCards=mediaList.map(function(m,idx){
                m=(m&&typeof m==="object")?m:{};
                var mediaType=String(m.type||"image")==="video"?"video":"image";
                var mediaSrc=escapeSidebarHtml(String(m.src||""));
                var mediaAlt=escapeSidebarHtml(String(m.alt||""));
                var mediaPoster=escapeSidebarHtml(String(m.poster||""));
                return '<div class="menu-item-card" data-idx="'+idx+'"><div class="menu-item-head"><strong>Media '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="poMediaMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button><button type="button" class="poMediaMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button><button type="button" class="poMediaDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button></div></div><label>Type</label><select class="poMediaType" data-idx="'+idx+'"><option value="image"'+(mediaType==="image"?' selected':'')+'>Image</option><option value="video"'+(mediaType==="video"?' selected':'')+'>Video</option></select><div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;"><button type="button" class="fb-btn poMediaUploadBtn" data-idx="'+idx+'" style="font-size:12px;padding:8px 10px;">Upload from PC</button><button type="button" class="fb-btn poMediaLibraryBtn" data-idx="'+idx+'" style="font-size:12px;padding:8px 10px;">Choose from Library</button></div><label>Media URL</label><input class="poMediaSrc" data-idx="'+idx+'" value="'+mediaSrc+'" placeholder="https://..."><label>Label / Alt text</label><input class="poMediaAlt" data-idx="'+idx+'" value="'+mediaAlt+'" placeholder="Main product image"><label>Poster image URL (video optional)</label><input class="poMediaPoster" data-idx="'+idx+'" value="'+mediaPoster+'" placeholder="https://..."></div>';
            }).join("");
            var featureCards=features.map(function(f,idx){
                var val=escapeSidebarHtml(String(f||""));
                return '<div class="menu-item-card" data-idx="'+idx+'"><div class="menu-item-head"><strong>Feature '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="poMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button><button type="button" class="poMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button><button type="button" class="poDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button></div></div><label>Text</label><input class="poFeature" data-idx="'+idx+'" value="'+val+'"></div>';
            }).join("");
            var productContentMeta=isCheckoutProductEditor?'<div class="meta" style="margin:0 0 10px;">These values act as fallback product details on checkout. If the buyer selected a product earlier, checkout can replace them with the selected offer details.</div>':'<div class="meta" style="margin:0 0 10px;">Best for physical products, bundles, and marketplace-style offers with multiple photos or a demo video.</div>';
            var productActionSection=isCheckoutProductEditor
                ? '<div class="menu-split"></div><div class="menu-section-title">Payment Action</div><div class="meta" style="margin:0 0 10px;">Checkout-step product buttons automatically submit payment.</div><label>Payment button label</label><input id="poCtaLabel"><label>Button color</label><input id="poCtaBg" type="color"><label>Button text color</label><input id="poCtaText" type="color">'
                : '<div class="menu-split"></div><div class="menu-section-title">Call to Actions</div><label>Button label</label><input id="poCtaLabel"><label>Button link</label><input id="poCtaLink" placeholder="https://..."><label>Button color</label><input id="poCtaBg" type="color"><label>Button text color</label><input id="poCtaText" type="color">';
            settings.innerHTML='<div class="menu-section-title">Product Offer</div>'+productContentMeta+'<label>Product name</label><input id="poPlan"><label>'+(isCheckoutProductEditor?'Fallback sale price':'Sale price')+'</label><input id="poPrice" placeholder="299"><label>'+(isCheckoutProductEditor?'Fallback regular price':'Regular price')+'</label><input id="poRegular" placeholder="499"><label>Period / suffix</label><input id="poPeriod" placeholder="/bundle"><label>Subtitle</label><input id="poSubtitle"><label>Badge</label><input id="poBadge" placeholder="Best Seller"><label>Quick details description</label><textarea id="poDescription" rows="5" placeholder="Write the fuller product story, specs, sizing, inclusions, delivery notes, or care instructions."></textarea><div class="menu-split"></div><div class="menu-section-title">Media Gallery</div>'+mediaCards+'<button type="button" id="addPoMedia" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add media</button><div class="menu-split"></div><div class="menu-section-title">Features</div>'+featureCards+'<button type="button" id="addPoFeature" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add feature</button>'+spacingControlsHtml(pad,mar)+productActionSection+'<div class="menu-split"></div><div class="menu-section-title">Quick View Modal</div><label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input id="poQuickViewEnabled" type="checkbox" style="width:auto;margin:0;"> Enable quick details modal</label><label>Quick view button label</label><input id="poQuickViewLabel" placeholder="Details"><div class="menu-split"></div><div class="menu-section-title">Cart</div><label style="display:flex;align-items:center;gap:8px;font-weight:600;"><input id="poCartEnabled" type="checkbox" style="width:auto;margin:0;"> Show add-to-cart icon</label><div class="meta" style="margin:6px 0 0;">Buy can stay as the main action, while the cart icon lets shoppers save the item and open the cart drawer in live mode.</div><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Text color</label><input id="poTextColor" type="color"><label>Background color</label><input id="poBg" type="color"><label>Border</label><input id="poBorder"><div class="px-wrap"><input id="poRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="poShadow">'+posControls+moveControls+remove;
            var poDescriptionField=document.getElementById("poDescription");
            if(poDescriptionField){
                poDescriptionField.insertAdjacentHTML("afterend",'<div class="menu-split"></div><div class="menu-section-title">Inventory</div><label>Available stock</label><input id="poStock" type="number" min="0" step="1" placeholder="Leave blank for unlimited"><div class="meta" style="margin:6px 0 0;">Set a stock count to cap cart quantity and stop checkout once paid orders consume the remaining inventory.</div>');
            }
            bind("poPlan",(t.settings&&t.settings.plan)||"",v=>{t.settings.plan=v;renderCanvas();},{undo:true});
            bindCurrency("poPrice",(t.settings&&t.settings.price)||"",v=>{t.settings.price=v;renderCanvas();},{undo:true});
            bindCurrency("poRegular",(t.settings&&t.settings.regularPrice)||"",v=>{t.settings.regularPrice=v;renderCanvas();},{undo:true});
            bind("poPeriod",(t.settings&&t.settings.period)||"",v=>{t.settings.period=v;renderCanvas();},{undo:true});
            bind("poSubtitle",(t.settings&&t.settings.subtitle)||"",v=>{t.settings.subtitle=v;renderCanvas();},{undo:true});
            bind("poBadge",(t.settings&&t.settings.badge)||"",v=>{t.settings.badge=v;renderCanvas();},{undo:true});
            bind("poDescription",(t.settings&&t.settings.description)||"",v=>{t.settings.description=v;renderCanvas();},{undo:true});
            bind("poStock",(t.settings&&t.settings.stockQuantity)!==undefined?(t.settings.stockQuantity):"",v=>{var raw=String(v||"").trim();if(raw===""){t.settings.stockQuantity="";}else{t.settings.stockQuantity=String(Math.max(0,parseInt(raw,10)||0));}renderCanvas();},{undo:true});
            bind("poCtaLabel",(t.settings&&t.settings.ctaLabel)||"Buy Now",v=>{t.settings.ctaLabel=v;renderCanvas();},{undo:true});
            bind("poCtaBg",(t.settings&&t.settings.ctaBgColor)||"#240E35",v=>{t.settings.ctaBgColor=v;renderCanvas();},{undo:true});
            bind("poCtaText",(t.settings&&t.settings.ctaTextColor)||"#ffffff",v=>{t.settings.ctaTextColor=v;renderCanvas();},{undo:true});
            var poQuickViewEnabled=document.getElementById("poQuickViewEnabled");
            var poQuickViewLabel=document.getElementById("poQuickViewLabel");
            var poCartEnabled=document.getElementById("poCartEnabled");
            if(poQuickViewEnabled){
                poQuickViewEnabled.checked=t.settings.quickViewEnabled!==false;
                poQuickViewEnabled.addEventListener("change",function(){
                    saveToHistory();
                    t.settings.quickViewEnabled=!!poQuickViewEnabled.checked;
                    renderCanvas();
                });
            }
            if(poCartEnabled){
                poCartEnabled.checked=t.settings.cartEnabled!==false;
                poCartEnabled.addEventListener("change",function(){
                    saveToHistory();
                    t.settings.cartEnabled=!!poCartEnabled.checked;
                    renderCanvas();
                });
            }
            bind("poQuickViewLabel",(t.settings&&t.settings.quickViewLabel)||"Details",v=>{t.settings.quickViewLabel=v;renderCanvas();},{undo:true});
            bind("poTextColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
            bind("poBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("poBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
            bindPx("poRadius",(t.style&&t.style.borderRadius)||"18px",v=>sty().borderRadius=v,{undo:true});
            bind("poShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
            settings.querySelectorAll(".poMediaType").forEach(function(inp){inp.addEventListener("change",function(){var idx=Number(inp.getAttribute("data-idx"));if(isNaN(idx)||!t.settings.media[idx])return;saveToHistory();t.settings.media[idx].type=String(inp.value||"image")==="video"?"video":"image";renderCanvas();});});
            settings.querySelectorAll(".poMediaUploadBtn").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.media[idx])return;
                    promptImageUploadForProductOffer(t,idx);
                });
            });
            settings.querySelectorAll(".poMediaLibraryBtn").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.media[idx])return;
                    openAssetLibraryModal({
                        kind:"image",
                        title:"Product Image Asset Library",
                        subtitle:"Reuse stored images or upload a new one.",
                        onSelect:function(url){
                            saveToHistory();
                            applyUploadedImageToProductOffer(t,url,idx);
                        }
                    });
                });
            });
            settings.querySelectorAll(".poMediaSrc").forEach(function(inp){inp.addEventListener("input",function(){var idx=Number(inp.getAttribute("data-idx"));if(isNaN(idx)||!t.settings.media[idx])return;saveToHistory();t.settings.media[idx].src=String(inp.value||"").trim();renderCanvas();});});
            settings.querySelectorAll(".poMediaAlt").forEach(function(inp){inp.addEventListener("input",function(){var idx=Number(inp.getAttribute("data-idx"));if(isNaN(idx)||!t.settings.media[idx])return;saveToHistory();t.settings.media[idx].alt=String(inp.value||"").trim();renderCanvas();});});
            settings.querySelectorAll(".poMediaPoster").forEach(function(inp){inp.addEventListener("input",function(){var idx=Number(inp.getAttribute("data-idx"));if(isNaN(idx)||!t.settings.media[idx])return;saveToHistory();t.settings.media[idx].poster=String(inp.value||"").trim();renderCanvas();});});
            settings.querySelectorAll(".poMediaDelete").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||t.settings.media.length<=1)return;saveToHistory();t.settings.media.splice(idx,1);t.settings.media=normalizeProductOfferMediaList(t.settings.media);renderProductOfferEditor();renderCanvas();});});
            settings.querySelectorAll(".poMediaMoveUp").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||idx<=0)return;saveToHistory();var tmp=t.settings.media[idx-1];t.settings.media[idx-1]=t.settings.media[idx];t.settings.media[idx]=tmp;renderProductOfferEditor();renderCanvas();});});
            settings.querySelectorAll(".poMediaMoveDown").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||idx>=t.settings.media.length-1)return;saveToHistory();var tmp=t.settings.media[idx+1];t.settings.media[idx+1]=t.settings.media[idx];t.settings.media[idx]=tmp;renderProductOfferEditor();renderCanvas();});});
            var addPoMedia=document.getElementById("addPoMedia"); if(addPoMedia)addPoMedia.onclick=function(){saveToHistory();t.settings.media.push({type:"image",src:"",alt:"Media "+(t.settings.media.length+1),poster:""});renderProductOfferEditor();renderCanvas();};
            settings.querySelectorAll(".poFeature").forEach(function(inp){inp.addEventListener("input",function(){var idx=Number(inp.getAttribute("data-idx"));if(isNaN(idx)||!t.settings.features[idx])return;saveToHistory();t.settings.features[idx]=String(inp.value||"").trim()||("Feature "+(idx+1));renderCanvas();});});
            settings.querySelectorAll(".poDelete").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||t.settings.features.length<=1)return;saveToHistory();t.settings.features.splice(idx,1);t.settings.features=normalizeFeatureList(t.settings.features);renderProductOfferEditor();renderCanvas();});});
            settings.querySelectorAll(".poMoveUp").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||idx<=0)return;saveToHistory();var tmp=t.settings.features[idx-1];t.settings.features[idx-1]=t.settings.features[idx];t.settings.features[idx]=tmp;renderProductOfferEditor();renderCanvas();});});
            settings.querySelectorAll(".poMoveDown").forEach(function(btn){btn.addEventListener("click",function(){var idx=Number(btn.getAttribute("data-idx"));if(isNaN(idx)||idx>=t.settings.features.length-1)return;saveToHistory();var tmp=t.settings.features[idx+1];t.settings.features[idx+1]=t.settings.features[idx];t.settings.features[idx]=tmp;renderProductOfferEditor();renderCanvas();});});
            var addPoFeature=document.getElementById("addPoFeature"); if(addPoFeature)addPoFeature.onclick=function(){saveToHistory();t.settings.features.push("Feature "+(t.settings.features.length+1));renderProductOfferEditor();renderCanvas();};
            var allowedActions=isCheckoutProductEditor?["checkout"]:(isOfferProductEditor?["offer_accept","offer_decline","link"]:["next_step","step","link"]);
            var productAction=String(t.settings.ctaActionType||"").trim().toLowerCase();
            if(allowedActions.indexOf(productAction)<0)productAction=isOfferProductEditor?"offer_accept":"next_step";
            if(isCheckoutProductEditor){productAction="checkout";t.settings.ctaLink="#";t.settings.ctaActionStepSlug="";}
            else if(isOfferProductEditor&&productAction!=="link"){productAction="offer_accept";t.settings.ctaLink="#";t.settings.ctaActionStepSlug="";}
            t.settings.ctaActionType=productAction;
            var poCtaLabelField=document.getElementById("poCtaLabel"),poCtaLinkField=document.getElementById("poCtaLink");
            if(poCtaLabelField&&poCtaLinkField){
                var productStepOptions=steps.filter(function(s){return String(s.id)!==String(state.sid);}).map(function(s){
                    var optionLabel=escapeSidebarHtml(pageDisplayLabel(s));
                    return '<option value="'+String(s.slug||"").replace(/"/g,'&quot;')+'">'+optionLabel+'</option>';
                }).join("")||'<option value="">No other pages found</option>';
                var productActionOptions=isCheckoutProductEditor?'<option value="checkout">Checkout submit</option>':(isOfferProductEditor?'<option value="offer_accept">Accept offer</option><option value="offer_decline">Decline offer</option><option value="link">Custom URL</option>':'<option value="next_step">Smart next page</option><option value="step">Specific step</option><option value="link">Custom URL</option>');
                poCtaLabelField.insertAdjacentHTML("afterend",'<label>Button action</label><select id="poCtaAction">'+productActionOptions+'</select><div id="poCtaStepWrap" style="display:none;"><label>Target page</label><select id="poCtaStep">'+productStepOptions+'</select></div><div class="meta" id="poCtaMeta" style="margin:6px 0 0;"></div>');
                var poCtaAction=document.getElementById("poCtaAction"),poCtaStep=document.getElementById("poCtaStep"),poCtaStepWrap=document.getElementById("poCtaStepWrap"),poCtaMeta=document.getElementById("poCtaMeta"),poCtaLinkLabel=poCtaLinkField.previousElementSibling;
                function syncPo(){
                    var action=String(t.settings.ctaActionType||"next_step");
                    poCtaAction.value=action;
                    poCtaStepWrap.style.display=action==="step"?"block":"none";
                    poCtaLinkField.style.display=action==="link"?"block":"none";
                    if(poCtaLinkLabel)poCtaLinkLabel.style.display=action==="link"?"block":"none";
                    poCtaMeta.textContent=isCheckoutProductEditor?'Checkout product buttons submit payment directly.':(isOfferProductEditor?(action==="offer_decline"?'This button submits the decline path for the current offer page.':(action==="link"?'Use a custom URL only if you want to leave the offer flow.':'This button submits the accept path for the current offer page.')):(action==="step"?'This button opens the selected next page and carries the product price.':(action==="link"?'Use a custom URL only if you want to leave the normal funnel flow.':'Smart next page usually carries buyers to checkout with this product price.')));
                }
                poCtaAction.addEventListener("change",function(){saveToHistory();t.settings.ctaActionType=String(poCtaAction.value||"next_step");if(t.settings.ctaActionType!=="step")t.settings.ctaActionStepSlug="";if(t.settings.ctaActionType!=="link")t.settings.ctaLink="#";syncPo();renderCanvas();});
                poCtaStep.addEventListener("change",function(){saveToHistory();t.settings.ctaActionStepSlug=String(poCtaStep.value||"");var targetStep=steps.find(function(s){return String(s.slug||"")===String(poCtaStep.value||"");});t.settings.ctaLink=targetStep?buildFunnelStepHref(targetStep):"#";renderCanvas();});
                bind("poCtaLink",(t.settings&&t.settings.ctaLink)||"#",v=>{t.settings.ctaLink=v;renderCanvas();},{undo:true});
                syncPo();
            }
            mountSpacingControls();
        }
        renderProductOfferEditor();
    } else if(selKind==="el"&&t.type==="pricing"){
        t.settings=t.settings||{};
        t.settings.features=normalizeFeatureList(t.settings.features);
        var padDef=[18,18,18,18],marDef=[0,0,0,0];
        function renderPricingEditor(){
            var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
            var currentPricingStepType=templateStepType(cur());
            var isCheckoutPricingEditor=currentPricingStepType==="checkout";
            var isOfferPricingEditor=currentPricingStepType==="upsell"||currentPricingStepType==="downsell";
            var feats=t.settings.features||[];
            var featCards=feats.map(function(f,idx){
                var val=String(f||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
                return '<div class="menu-item-card" data-idx="'+idx+'"><div class="menu-item-head"><strong>Feature '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="priceMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button><button type="button" class="priceMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button><button type="button" class="priceDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button></div></div><label>Text</label><input class="priceFeature" data-idx="'+idx+'" value="'+val+'"></div>';
            }).join("");
            var pricingContentMeta=isCheckoutPricingEditor
                ? '<div class="meta" style="margin:0 0 10px;">These values are fallback details for checkout. If the buyer selected a pricing card on the sales page, that selected plan and amount will replace these values automatically.</div>'
                : '';
            var pricingActionSection=isCheckoutPricingEditor
                ? '<div class="menu-split"></div><div class="menu-section-title">Payment Action</div><div class="meta" style="margin:0 0 10px;">This checkout pricing card is automatically the Pay Now button. Buyers do not need any extra action setup here.</div><label>Payment button label</label><input id="priceCtaLabel"><label>Button color</label><input id="priceCtaBg" type="color"><label>Button text color</label><input id="priceCtaText" type="color">'
                : '<div class="menu-split"></div><div class="menu-section-title">Call to Actions</div><label>Button label</label><input id="priceCtaLabel"><label>Button link</label><input id="priceCtaLink" placeholder="https://..."><label>Button color</label><input id="priceCtaBg" type="color"><label>Button text color</label><input id="priceCtaText" type="color">';
            settings.innerHTML='<div class="menu-section-title">Content</div>'+pricingContentMeta+'<label>Plan name</label><input id="pricePlan"><label>'+(isCheckoutPricingEditor?'Fallback sale price':'Sale price')+'</label><input id="priceValue" placeholder="49"><label>'+(isCheckoutPricingEditor?'Fallback regular price (after countdown)':'Regular price (after countdown)')+'</label><input id="priceRegular" placeholder="79"><label>Period</label><input id="pricePeriod" placeholder="/month"><label>Subtitle</label><input id="priceSubtitle"><label>Badge</label><input id="priceBadge" placeholder="Popular"><label>Promo key (legacy)</label><input id="pricePromo" placeholder="spring-sale"><div class="menu-split"></div><div class="menu-section-title">Features</div>'+featCards+'<button type="button" id="addPriceFeature" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add feature</button>'+spacingControlsHtml(pad,mar)+pricingActionSection+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Text color</label><input id="priceTextColor" type="color"><label>Background color</label><input id="priceBg" type="color"><label>Border</label><input id="priceBorder">'+radiusHelpLabelHtml("priceRadiusHelp","Border radius")+'<div class="px-wrap"><input id="priceRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="priceShadow">'+posControls+moveControls+remove;
            var priceCtaLabelField=document.getElementById("priceCtaLabel");
            var priceCtaLinkField=document.getElementById("priceCtaLink");
            if(priceCtaLabelField&&priceCtaLinkField){
                var pricingStepType=templateStepType(cur());
                var isCheckoutPricingStep=pricingStepType==="checkout";
                var isOfferPricingStep=pricingStepType==="upsell"||pricingStepType==="downsell";
                var pricingStepOptions=steps.filter(function(s){return String(s.id)!==String(state.sid);}).map(function(s){
                    var optionLabel=escapeSidebarHtml(pageDisplayLabel(s));
                    return '<option value="'+String(s.slug||"").replace(/"/g,'&quot;')+'">'+optionLabel+'</option>';
                }).join("");
                var pricingStepDisabled=pricingStepOptions==="";
                if(pricingStepOptions==="")pricingStepOptions='<option value="">No other pages found</option>';
                var pricingActionOptions=isCheckoutPricingStep
                    ? '<option value="checkout">Checkout submit</option>'
                    : (isOfferPricingStep
                    ? '<option value="offer_accept">Accept offer</option><option value="offer_decline">Decline offer</option><option value="link">Custom URL</option>'
                    : '<option value="next_step">Smart next page</option><option value="step">Specific step</option><option value="link">Custom URL</option>');
                var pricingActionMeta=isCheckoutPricingStep
                    ? 'Checkout-step pricing buttons always submit payment to PayMongo.'
                    : (isOfferPricingStep
                    ? 'On upsell and downsell pages, pricing buttons should usually record an offer decision instead of acting like checkout.'
                    : 'On sales pages, pricing buttons automatically carry the selected plan to checkout.');
                priceCtaLabelField.insertAdjacentHTML("afterend",'<label>Button action</label><select id="priceCtaAction">'+pricingActionOptions+'</select><div id="priceCtaStepWrap" style="display:none;"><label>Target page</label><select id="priceCtaStep"'+(pricingStepDisabled?' disabled':'')+'>'+pricingStepOptions+'</select></div><div class="meta" id="priceCtaMeta" style="margin:6px 0 0;">'+pricingActionMeta+'</div>');
            }
            bind("pricePlan",(t.settings&&t.settings.plan)||"",v=>{t.settings=t.settings||{};t.settings.plan=v;renderCanvas();},{undo:true});
            bindCurrency("priceValue",(t.settings&&t.settings.price)||"",v=>{t.settings=t.settings||{};t.settings.price=v;renderCanvas();},{undo:true});
            bindCurrency("priceRegular",(t.settings&&t.settings.regularPrice)||"",v=>{t.settings=t.settings||{};t.settings.regularPrice=v;renderCanvas();},{undo:true});
            bind("pricePeriod",(t.settings&&t.settings.period)||"",v=>{t.settings=t.settings||{};t.settings.period=v;renderCanvas();},{undo:true});
            bind("priceSubtitle",(t.settings&&t.settings.subtitle)||"",v=>{t.settings=t.settings||{};t.settings.subtitle=v;renderCanvas();},{undo:true});
            bind("priceBadge",(t.settings&&t.settings.badge)||"",v=>{t.settings=t.settings||{};t.settings.badge=v;renderCanvas();},{undo:true});
            bind("pricePromo",(t.settings&&t.settings.promoKey)||"",v=>{t.settings=t.settings||{};t.settings.promoKey=v;renderCanvas();},{undo:true});
            settings.querySelectorAll(".priceFeature").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!t.settings.features[idx])return;
                    saveToHistory();
                    t.settings.features[idx]=String(inp.value||"").trim()||("Feature "+(idx+1));
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".priceDelete").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.features))return;
                    if(t.settings.features.length<=1)return;
                    saveToHistory();
                    t.settings.features.splice(idx,1);
                    t.settings.features=normalizeFeatureList(t.settings.features);
                    renderPricingEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".priceMoveUp").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||idx<=0||!Array.isArray(t.settings.features))return;
                    saveToHistory();
                    var tmp=t.settings.features[idx-1];
                    t.settings.features[idx-1]=t.settings.features[idx];
                    t.settings.features[idx]=tmp;
                    renderPricingEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".priceMoveDown").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.features)||idx>=t.settings.features.length-1)return;
                    saveToHistory();
                    var tmp=t.settings.features[idx+1];
                    t.settings.features[idx+1]=t.settings.features[idx];
                    t.settings.features[idx]=tmp;
                    renderPricingEditor();
                    renderCanvas();
                });
            });
            var addPriceFeature=document.getElementById("addPriceFeature");
            if(addPriceFeature)addPriceFeature.onclick=function(){
                saveToHistory();
                t.settings.features=t.settings.features||[];
                var nextIndex=t.settings.features.length+1;
                t.settings.features.push("Feature "+nextIndex);
                renderPricingEditor();
                renderCanvas();
            };
            bind("priceCtaLabel",(t.settings&&t.settings.ctaLabel)||"",v=>{t.settings=t.settings||{};t.settings.ctaLabel=v;renderCanvas();},{undo:true});
            t.settings=t.settings||{};
            var pricingStepType=templateStepType(cur());
            var isCheckoutPricingStep=pricingStepType==="checkout";
            var isOfferPricingStep=pricingStepType==="upsell"||pricingStepType==="downsell";
            var allowedPricingActions=isCheckoutPricingStep?["checkout"]:(isOfferPricingStep?["offer_accept","offer_decline","link"]:["next_step","step","link"]);
            var curPricingAction=String(t.settings.ctaActionType||"").trim().toLowerCase();
            if(allowedPricingActions.indexOf(curPricingAction)<0){
                var legacyPricingLink=String(t.settings.ctaLink||"").trim();
                curPricingAction=(legacyPricingLink!==""&&legacyPricingLink!=="#")?"link":(isOfferPricingStep?"offer_accept":"next_step");
            }
            if(isCheckoutPricingStep){
                curPricingAction="checkout";
                if(String(t.settings.ctaLabel||"").trim()==="")t.settings.ctaLabel="Pay Now";
                t.settings.ctaLink="#";
                t.settings.ctaActionStepSlug="";
            }else if(isOfferPricingStep){
                if(String(t.settings.ctaLabel||"").trim()==="")t.settings.ctaLabel="Yes, Add This Offer";
                if(curPricingAction!=="link"){
                    curPricingAction="offer_accept";
                    t.settings.ctaLink="#";
                }
                t.settings.ctaActionStepSlug="";
            }else if(curPricingAction==="checkout"){
                var smartTarget=choosePricingTargetStep(steps,cur(),String(t.settings.ctaLabel||"").trim()||t.settings.plan);
                if(smartTarget&&String(smartTarget.id||"")!==String((cur()&&cur().id)||"")){
                    curPricingAction="step";
                    t.settings.ctaActionStepSlug=String(smartTarget.slug||"");
                    t.settings.ctaLink=buildFunnelStepHref(smartTarget);
                }else{
                    curPricingAction="next_step";
                    t.settings.ctaActionStepSlug="";
                    t.settings.ctaLink="#";
                }
            }
            t.settings.ctaActionType=curPricingAction;
            if(typeof t.settings.ctaActionStepSlug!=="string")t.settings.ctaActionStepSlug="";
            var priceCtaAction=document.getElementById("priceCtaAction");
            var priceCtaStep=document.getElementById("priceCtaStep");
            var priceCtaStepWrap=document.getElementById("priceCtaStepWrap");
            var priceCtaMeta=document.getElementById("priceCtaMeta");
            var priceCtaLink=document.getElementById("priceCtaLink");
            var priceCtaLinkLabel=priceCtaLink?priceCtaLink.previousElementSibling:null;
            function syncPriceCtaControls(){
                var action=String((t.settings&&t.settings.ctaActionType)||"next_step").trim().toLowerCase();
                if(priceCtaAction)priceCtaAction.value=action;
                if(priceCtaAction)priceCtaAction.disabled=isCheckoutPricingStep;
                if(priceCtaStep)priceCtaStep.value=String((t.settings&&t.settings.ctaActionStepSlug)||"");
                if(priceCtaStepWrap)priceCtaStepWrap.style.display=action==="step"?"block":"none";
                if(priceCtaLink)priceCtaLink.style.display=action==="link"?"block":"none";
                if(priceCtaLinkLabel)priceCtaLinkLabel.style.display=action==="link"?"block":"none";
                if(priceCtaMeta){
                    priceCtaMeta.textContent=isCheckoutPricingStep
                        ? "Checkout-step pricing buttons always submit payment to PayMongo."
                        : (isOfferPricingStep
                        ? (action==="link"
                            ? "Use a custom URL only when you really want to leave the normal offer flow."
                            : (action==="offer_decline"
                                ? "This pricing button will submit the offer decline flow for the current upsell or downsell page."
                                : "This pricing button will submit the offer accept flow for the current upsell or downsell page."))
                        : (action==="step"
                        ? "This pricing button will open the chosen page and carry the selected plan and price."
                        : (action==="link"
                        ? "Use a custom URL only when you really want to leave the normal funnel flow."
                        : "Smart next page will send buyers to the best next step, usually checkout, and carry the selected pricing.")));
                }
            }
            if(priceCtaAction){
                priceCtaAction.addEventListener("change",function(){
                    saveToHistory();
                    t.settings=t.settings||{};
                    t.settings.ctaActionType=String(priceCtaAction.value||"next_step");
                    if(t.settings.ctaActionType!=="step")t.settings.ctaActionStepSlug="";
                    if(t.settings.ctaActionType!=="link")t.settings.ctaLink="#";
                    if(isCheckoutPricingStep&&String(t.settings.ctaLabel||"").trim()==="")t.settings.ctaLabel="Pay Now";
                    if(isOfferPricingStep&&t.settings.ctaActionType!=="link")t.settings.ctaActionStepSlug="";
                    syncPriceCtaControls();
                    renderCanvas();
                });
            }
            if(priceCtaStep){
                priceCtaStep.addEventListener("change",function(){
                    saveToHistory();
                    t.settings=t.settings||{};
                    t.settings.ctaActionStepSlug=String(priceCtaStep.value||"");
                    var targetStep=steps.find(function(s){return String(s.slug||"")===String(priceCtaStep.value||"");});
                    t.settings.ctaLink=targetStep?buildFunnelStepHref(targetStep):"#";
                    renderCanvas();
                });
            }
            syncPriceCtaControls();
            bind("priceCtaLink",(t.settings&&t.settings.ctaLink)||"#",v=>{t.settings=t.settings||{};t.settings.ctaLink=v;renderCanvas();},{undo:true});
            bind("priceCtaBg",(t.settings&&t.settings.ctaBgColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.ctaBgColor=v;renderCanvas();},{undo:true});
            bind("priceCtaText",(t.settings&&t.settings.ctaTextColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.ctaTextColor=v;renderCanvas();},{undo:true});
            bind("priceTextColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
            bind("priceBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("priceBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
            bindPx("priceRadius",(t.style&&t.style.borderRadius)||"18px",v=>sty().borderRadius=v,{undo:true});
            bind("priceShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
            mountSpacingControls();
            bindRadiusHelpButton("priceRadiusHelp");
        }
        renderPricingEditor();
    } else if(selKind==="el"&&(t.type==="checkout_summary"||t.type==="physical_checkout_summary")){
        t.settings=t.settings||{};
        t.settings.features=normalizeFeatureList(t.settings.features);
        var cspadDef=[22,22,22,22],csmarDef=[0,0,0,0];
        var isPhysicalSummary=t.type==="physical_checkout_summary";
        function renderCheckoutSummaryEditor(){
            var pad=parseSpacing(t.style&&t.style.padding,cspadDef),mar=parseSpacing(t.style&&t.style.margin,csmarDef);
            var feats=(t.settings.features||[]).map(function(f,idx){
                var val=String(f||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
                return '<div class="menu-item-card" data-idx="'+idx+'"><div class="menu-item-head"><strong>Summary item '+(idx+1)+'</strong><div class="menu-item-actions"><button type="button" class="csMoveUp" data-idx="'+idx+'" title="Move up"><i class="fas fa-arrow-up"></i></button><button type="button" class="csMoveDown" data-idx="'+idx+'" title="Move down"><i class="fas fa-arrow-down"></i></button><button type="button" class="csDelete menu-del" data-idx="'+idx+'" title="Delete"><i class="fas fa-trash"></i></button></div></div><label>Text</label><input class="csFeature" data-idx="'+idx+'" value="'+val+'"></div>';
            }).join("");
            settings.innerHTML='<div class="menu-section-title">'+(isPhysicalSummary?'Physical Checkout Summary':'Checkout Summary')+'</div><div class="meta" style="margin:0 0 10px;">'+(isPhysicalSummary?'Best used on physical-product checkout pages. Selected product images, cart items, and totals will replace these fallback values on the live page.':'Best used on checkout pages. The selected plan from the sales page will automatically replace these fallback values on the live page.')+'</div><label>Eyebrow</label><input id="csHeading"><label>'+(isPhysicalSummary?'Fallback product name':'Fallback plan name')+'</label><input id="csPlan"><label>'+(isPhysicalSummary?'Fallback total':'Fallback price')+'</label><input id="csPrice" placeholder="29"><label>Fallback regular price</label><input id="csRegular" placeholder="49"><label>Period</label><input id="csPeriod" placeholder="'+(isPhysicalSummary?'':'/month')+'"><label>Subtitle</label><input id="csSubtitle"><label>Badge</label><input id="csBadge" placeholder="'+(isPhysicalSummary?'Order Summary':'Selected Plan')+'"><div class="menu-split"></div><div class="menu-section-title">Summary Items</div>'+feats+'<button type="button" id="addCsFeature" class="fb-btn primary" style="width:100%;margin:6px 0 10px;">Add item</button>'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Payment Button</div><label>Button label</label><input id="csCtaLabel"><label>Button color</label><input id="csCtaBg" type="color"><label>Button text color</label><input id="csCtaText" type="color"><div class="menu-split"></div><div class="menu-section-title">Style</div><label>Text color</label><input id="csTextColor" type="color"><label>Background color</label><input id="csBg" type="color"><label>Border</label><input id="csBorder">'+radiusHelpLabelHtml("csRadiusHelp","Border radius")+'<div class="px-wrap"><input id="csRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="csShadow">'+posControls+moveControls+remove;
            bind("csHeading",(t.settings&&t.settings.heading)||(isPhysicalSummary?"Review Your Order":"Order Summary"),v=>{t.settings=t.settings||{};t.settings.heading=v;renderCanvas();},{undo:true});
            bind("csPlan",(t.settings&&t.settings.plan)||"",v=>{t.settings=t.settings||{};t.settings.plan=v;renderCanvas();},{undo:true});
            bindCurrency("csPrice",(t.settings&&t.settings.price)||"",v=>{t.settings=t.settings||{};t.settings.price=v;renderCanvas();},{undo:true});
            bindCurrency("csRegular",(t.settings&&t.settings.regularPrice)||"",v=>{t.settings=t.settings||{};t.settings.regularPrice=v;renderCanvas();},{undo:true});
            bind("csPeriod",(t.settings&&t.settings.period)||"",v=>{t.settings=t.settings||{};t.settings.period=v;renderCanvas();},{undo:true});
            bind("csSubtitle",(t.settings&&t.settings.subtitle)||"",v=>{t.settings=t.settings||{};t.settings.subtitle=v;renderCanvas();},{undo:true});
            bind("csBadge",(t.settings&&t.settings.badge)||"",v=>{t.settings=t.settings||{};t.settings.badge=v;renderCanvas();},{undo:true});
            bind("csCtaLabel",(t.settings&&t.settings.ctaLabel)||(isPhysicalSummary?"Place Order":"Pay Now"),v=>{t.settings=t.settings||{};t.settings.ctaLabel=v;renderCanvas();},{undo:true});
            bind("csCtaBg",(t.settings&&t.settings.ctaBgColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.ctaBgColor=v;renderCanvas();},{undo:true});
            bind("csCtaText",(t.settings&&t.settings.ctaTextColor)||"#ffffff",v=>{t.settings=t.settings||{};t.settings.ctaTextColor=v;renderCanvas();},{undo:true});
            bind("csTextColor",(t.style&&t.style.color)||"#240E35",v=>sty().color=v,{undo:true});
            bind("csBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("csBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
            bindPx("csRadius",(t.style&&t.style.borderRadius)||"20px",v=>sty().borderRadius=v,{undo:true});
            bind("csShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
            settings.querySelectorAll(".csFeature").forEach(function(inp){
                inp.addEventListener("input",function(){
                    var idx=Number(inp.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.features)||!t.settings.features[idx])return;
                    saveToHistory();
                    t.settings.features[idx]=String(inp.value||"").trim()||("Summary item "+(idx+1));
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".csDelete").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.features)||t.settings.features.length<=1)return;
                    saveToHistory();
                    t.settings.features.splice(idx,1);
                    t.settings.features=normalizeFeatureList(t.settings.features);
                    renderCheckoutSummaryEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".csMoveUp").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||idx<=0||!Array.isArray(t.settings.features))return;
                    saveToHistory();
                    var tmp=t.settings.features[idx-1];
                    t.settings.features[idx-1]=t.settings.features[idx];
                    t.settings.features[idx]=tmp;
                    renderCheckoutSummaryEditor();
                    renderCanvas();
                });
            });
            settings.querySelectorAll(".csMoveDown").forEach(function(btn){
                btn.addEventListener("click",function(){
                    var idx=Number(btn.getAttribute("data-idx"));
                    if(isNaN(idx)||!Array.isArray(t.settings.features)||idx>=t.settings.features.length-1)return;
                    saveToHistory();
                    var tmp=t.settings.features[idx+1];
                    t.settings.features[idx+1]=t.settings.features[idx];
                    t.settings.features[idx]=tmp;
                    renderCheckoutSummaryEditor();
                    renderCanvas();
                });
            });
            var addCsFeature=document.getElementById("addCsFeature");
            if(addCsFeature)addCsFeature.onclick=function(){
                saveToHistory();
                t.settings.features=t.settings.features||[];
                t.settings.features.push("Summary item "+(t.settings.features.length+1));
                renderCheckoutSummaryEditor();
                renderCanvas();
            };
            mountSpacingControls();
            bindRadiusHelpButton("csRadiusHelp");
        }
        renderCheckoutSummaryEditor();
    } else if(selKind==="el"&&t.type==="countdown"){
        t.settings=t.settings||{};
        var padDef=[16,16,16,16],marDef=[0,0,0,0];
        function escHtml(v){return String(v||"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/\"/g,"&quot;");}
        function pricingLabel(item,idx){
            var plan=String((item.settings&&item.settings.plan)||"").trim();
            var price=String((item.settings&&item.settings.price)||"").trim();
            if(price==="")price=String((item.settings&&item.settings.regularPrice)||"").trim();
            var label=plan!==""?plan:("Pricing "+(idx+1));
            if(price!=="")label+=" - "+price;
            return label;
        }
        function buildPricingOptions(){
            var list=collectElementsByType("pricing");
            var linkedIds=getLinkedPricingIds(t);
            var labels=[];
            list.forEach(function(el,idx){
                var id=String((el&&el.id)||"");
                if(linkedIds.indexOf(id)>=0)labels.push(pricingLabel(el,idx));
            });
            var missing=linkedIds.filter(function(id){
                return !list.some(function(el){return String((el&&el.id)||"")===id;});
            });
            var optionsHtml="";
            if(list.length===0){
                optionsHtml='<div class="meta">No pricing components found</div>';
            }else{
                optionsHtml=list.map(function(el,idx){
                    var id=String((el&&el.id)||"");
                    var label=pricingLabel(el,idx);
                    var checked=(linkedIds.indexOf(id)>=0);
                    return '<label class="inline-check"><input type="checkbox" class="cdPricingCheck" value="'+escHtml(id)+'"'+(checked?' checked':'')+'> '+escHtml(label)+'</label>';
                }).join("");
            }
            var currentLabel=linkedIds.length?labels.join(", "):"Not linked";
            return {optionsHtml:optionsHtml,hasPricing:list.length>0,linkedIds:linkedIds,currentLabel:currentLabel,missing:missing,linkedCount:linkedIds.length};
        }
        function renderCountdownEditor(){
            var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
            var pricingData=buildPricingOptions();
            var isPicking=!!(state.linkPick&&String(state.linkPick.sourceId||"")===String(t.id||""));
            var linkMeta="";
            if(pricingData.linkedCount>0){
                linkMeta='Linked to: <strong>'+pricingData.linkedCount+' pricing</strong>';
                if(pricingData.currentLabel!==""){
                    linkMeta+=' ('+escHtml(pricingData.currentLabel)+')';
                }
                linkMeta+='.';
            }else{
                linkMeta='Linked to: <strong>Not linked</strong>. Add a pricing component.';
            }
            if(pricingData.missing.length){
                linkMeta+=' Missing: <strong>'+escHtml(pricingData.missing.join(", "))+'</strong>.';
            }
            var pickLabel=isPicking?'Done picking':'Pick on canvas';
            var linkHtml='<div class="menu-section-title">Connection</div><div class="meta">'+linkMeta+'</div><div class="fb-link-list">'+pricingData.optionsHtml+'</div><div class="fb-link-row"><button type="button" id="cdPickPricing" class="fb-btn'+(isPicking?' danger':'')+'"'+(pricingData.hasPricing?'':' disabled')+'>'+pickLabel+'</button></div><div class="fb-link-actions"><button type="button" id="cdClearPricing" class="fb-btn"'+(pricingData.linkedCount>0?'':' disabled')+'>Clear links</button></div>';
            settings.innerHTML='<div class="menu-section-title">Content</div><label>End date/time</label><input id="cdEnd" type="datetime-local"><label>Label</label><input id="cdLabel"><label>Expired message</label><input id="cdExpired">'+linkHtml+'<div class="menu-split"></div><div class="menu-section-title">Legacy (optional)</div><label>Promo key</label><input id="cdPromo" placeholder="spring-sale">'+spacingControlsHtml(pad,mar)+'<div class="menu-split"></div><div class="menu-section-title">Style</div><label>Number color</label><input id="cdNumberColor" type="color"><label>Label color</label><input id="cdLabelColor" type="color"><label>Item spacing</label><div class="px-wrap"><input id="cdGap" type="number" step="1"><span class="px-unit">px</span></div><label>Background color</label><input id="cdBg" type="color"><label>Border</label><input id="cdBorder">'+radiusHelpLabelHtml("cdRadiusHelp","Border radius")+'<div class="px-wrap"><input id="cdRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div><label>Shadow</label><input id="cdShadow">'+posControls+moveControls+remove;
            bind("cdEnd",formatDateTimeLocal((t.settings&&t.settings.endAt)||""),v=>{t.settings=t.settings||{};t.settings.endAt=v;renderCanvas();},{undo:true});
            bind("cdLabel",(t.settings&&t.settings.label)||"Offer ends in",v=>{t.settings=t.settings||{};t.settings.label=v;renderCanvas();},{undo:true});
            bind("cdExpired",(t.settings&&t.settings.expiredText)||"Offer ended",v=>{t.settings=t.settings||{};t.settings.expiredText=v;renderCanvas();},{undo:true});
            bind("cdPromo",(t.settings&&t.settings.promoKey)||"",v=>{t.settings=t.settings||{};t.settings.promoKey=v;renderCanvas();},{undo:true});
            bind("cdNumberColor",(t.settings&&t.settings.numberColor)||"#240E35",v=>{t.settings=t.settings||{};t.settings.numberColor=v;renderCanvas();},{undo:true});
            bind("cdLabelColor",(t.settings&&t.settings.labelColor)||"#64748b",v=>{t.settings=t.settings||{};t.settings.labelColor=v;renderCanvas();},{undo:true});
            bind("cdGap",(t.settings&&t.settings.itemGap)!=null?String(t.settings.itemGap):"8",v=>{t.settings=t.settings||{};var n=Number(v);t.settings.itemGap=isNaN(n)?8:Math.max(0,n);renderCanvas();},{undo:true});
            bind("cdBg",(t.style&&t.style.backgroundColor)||"#ffffff",v=>sty().backgroundColor=v,{undo:true});
            bind("cdBorder",(t.style&&t.style.border)||"1px solid #E6E1EF",v=>sty().border=v,{undo:true});
            bindPx("cdRadius",(t.style&&t.style.borderRadius)||"16px",v=>sty().borderRadius=v,{undo:true});
            bind("cdShadow",(t.style&&t.style.boxShadow)||"0 12px 24px rgba(15,23,42,.08)",v=>sty().boxShadow=v,{undo:true});
            var linkChecks=settings.querySelectorAll(".cdPricingCheck");
            if(linkChecks&&linkChecks.length){
                linkChecks.forEach(function(chk){
                    chk.addEventListener("change",function(){
                        saveToHistory();
                        var ids=Array.from(settings.querySelectorAll(".cdPricingCheck:checked")).map(function(n){return String(n.value||"");});
                        var cleanIds=setLinkedPricingIds(t,ids);
                        // If the countdown promoKey is empty, auto-copy it from the first linked pricing.
                        // If the user already provided a manual promoKey, do not override it.
                        var curPromo=String((t.settings&&t.settings.promoKey)||"").trim();
                        if(curPromo==="" && cleanIds && cleanIds.length){
                            var firstId=String(cleanIds[0]||"").trim();
                            if(firstId!==""){
                                var pEl=findElementById(firstId);
                                var pPromo=String((pEl&&pEl.settings&&pEl.settings.promoKey)||"").trim();
                                if(pPromo!==""){
                                    t.settings=t.settings||{};
                                    t.settings.promoKey=pPromo;
                                }
                            }
                        }
                        renderCanvas();
                        renderCountdownEditor();
                    });
                });
            }
            var pickBtn=document.getElementById("cdPickPricing");
            if(pickBtn){
                pickBtn.onclick=function(){
                    if(state.linkPick&&String(state.linkPick.sourceId||"")===String(t.id||"")){
                        state.linkPick=null;
                        renderCanvas();
                        renderSettings();
                        return;
                    }
                    startPricingLink(t.id);
                };
            }
            var clearBtn=document.getElementById("cdClearPricing");
            if(clearBtn){
                clearBtn.onclick=function(){
                    if(!getLinkedPricingIds(t).length)return;
                    saveToHistory();
                    setLinkedPricingIds(t,[]);
                    state.linkPick=null;
                    renderCanvas();
                    renderCountdownEditor();
                };
            }
            mountSpacingControls();
            bindRadiusHelpButton("cdRadiusHelp");
        }
        renderCountdownEditor();
    } else if(selKind==="el"){
        const rich=(t.type==="text"||t.type==="heading");
        var padDef=[0,0,0,0],marDef=[0,0,0,0];
        var pad=parseSpacing(t.style&&t.style.padding,padDef),mar=parseSpacing(t.style&&t.style.margin,marDef);
        var textTypographyControls=(t.type==="text"||t.type==="heading")
            ? '<label>Line height</label><input id="lh" placeholder="1.5"><label>Letter spacing</label><div class="px-wrap"><input id="ls" type="number" step="0.1"><span class="px-unit">px</span></div>'
            : '';
        var sizeBlock='<div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>â†”</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>â†”</span></button><span>Link</span></div></div></div>';
        var buttonBgControl=(t.type==="button")?'<label>Button color</label><input id="btnBg" type="color">':'';
        var buttonWrapBgControl=(t.type==="button")?'<label>Background color</label><input id="btnWrapBg" type="color">':'';
        var buttonTextStyleControl=(t.type==="button")?'<label>Text style</label><div class="menu-style-row"><button type="button" id="btnBold" class="menu-align-btn" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button><button type="button" id="btnItalic" class="menu-align-btn" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button></div>':'';
        var buttonRadiusControl=(t.type==="button")
            ? ('<label>Button shape</label><select id="btnShape"><option value="square">Square</option><option value="rounded">Rounded</option><option value="pill">Pill</option><option value="custom">Custom</option></select>'
                + radiusHelpLabelHtml("btnRadiusHelp","Border radius")
                + '<div class="px-wrap"><input id="btnRadius" type="number" min="0" step="1"><span class="px-unit">px</span></div>')
            : '';
        var buttonStepOptions=(t.type==="button")?steps.filter(function(s){return String(s.id)!==String(state.sid);}).map(function(s){
            var sl=String(s.slug||"").replace(/"/g,'&quot;');
            var optionLabel=escapeSidebarHtml(pageDisplayLabel(s));
            return '<option value="'+sl+'">'+optionLabel+'</option>';
        }).join(''):'';
        var buttonStepDisabled=false;
        if(t.type==="button" && buttonStepOptions===""){
            buttonStepOptions='<option value="">No other pages found</option>';
            buttonStepDisabled=true;
        }
        var buttonActionControl=(t.type==="button")
            ? helpLabelHtml("buttonAction","Button action")+'<select id="btnAction"><option value="next_step">Next step</option><option value="step">Specific step</option><option value="link">Custom URL</option><option value="checkout">Checkout submit</option><option value="offer_accept">Accept offer</option><option value="offer_decline">Decline offer</option></select><div id="btnStepWrap" style="display:none;">'+helpLabelHtml("buttonTarget","Target page")+'<select id="btnStep"'+(buttonStepDisabled?' disabled':'')+'>'+buttonStepOptions+'</select></div><div id="btnLinkWrap" style="display:none;">'+helpLabelHtml("buttonTarget","Link URL")+'<input id="btnLink" placeholder="/contact or https://example.com"></div>'
            : '';
        var sharedBgControls=(t.type==="button")?'':'<label>Background color</label><input id="bg" type="color"><label>Background image URL</label><input id="bgImg" placeholder="https://..."><label>Upload background image</label><input id="bgUp" type="file" accept="image/*">';
        settings.innerHTML='<div class="menu-section-title">Content</div>'+(rich?'<div class="rt-box"><div class="rt-tools"><button id="rtBold" type="button" title="Bold (Ctrl+B)"><b>B</b></button><button id="rtItalic" type="button" title="Italic (Ctrl+I)"><i>I</i></button><button id="rtUnderline" type="button"><u>U</u></button></div><div id="contentRt" class="rt-editor" contenteditable="true"></div></div>':'<label>Content</label><textarea id="content" rows="4"></textarea>')+buttonActionControl+'<div class="menu-split"></div><div class="menu-section-title">Layout</div>'+helpLabelHtml("textAlignment","Alignment")+'<select id="a"><option value="">Default</option><option>left</option><option>center</option><option>right</option></select><div class="menu-split"></div><div class="menu-section-title">Spacing</div>'+sizeBlock+'<div class="menu-split"></div><div class="menu-section-title">Style</div>'+buttonWrapBgControl+buttonBgControl+buttonRadiusControl+buttonTextStyleControl+sharedBgControls+'<label>Color</label><input id="co" type="color"><label>Font size</label><div class="px-wrap"><input id="fs" type="number" step="1"><span class="px-unit">px</span></div>'+textTypographyControls+fontSelectHtml('ff')+posControls+moveControls+remove;
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
            mountBackgroundAssetLibrary("bgUp","bgAssetLibraryBtn","bgImg","Background Asset Library");
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
            var btnShape=document.getElementById("btnShape");
            var btnRadiusInput=document.getElementById("btnRadius");
            function getButtonShape(){
                var raw=String((t.style&&t.style.borderRadius)||"").trim().toLowerCase();
                if(raw===""||raw==="0"||raw==="0px")return "square";
                if(raw==="999px"||raw==="9999px")return "pill";
                var n=parseFloat(raw);
                if(!isNaN(n) && n>0 && n<=40)return "rounded";
                return "custom";
            }
            function syncButtonShapeUi(){
                if(btnShape)btnShape.value=getButtonShape();
                if(btnRadiusInput){
                    btnRadiusInput.disabled=!!btnShape && btnShape.value!=="custom";
                }
            }
            if(btnShape){
                syncButtonShapeUi();
                btnShape.addEventListener("change",function(){
                    saveToHistory();
                    var selected=String(btnShape.value||"rounded");
                    if(selected==="square")sty().borderRadius="0px";
                    else if(selected==="rounded")sty().borderRadius="16px";
                    else if(selected==="pill")sty().borderRadius="999px";
                    else if(!String((t.style&&t.style.borderRadius)||"").trim())sty().borderRadius="16px";
                    syncButtonShapeUi();
                    renderCanvas();
                });
            }
            if(btnRadiusInput){
                btnRadiusInput.addEventListener("input",function(){
                    if(btnShape && btnShape.value==="custom"){
                        btnShape.value=getButtonShape();
                        if(btnShape.value!=="custom")btnShape.value="custom";
                    }
                });
            }
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
        settings.innerHTML='<label>Background color</label><input id="bg" type="color"><div class="size-position"><div class="size-label">Size and position</div><label class="size-label">Padding</label><div class="size-grid"><div class="fld"><label>T</label><input id="pTop" type="number" value="'+pad[0]+'"></div><div class="fld"><label>R</label><input id="pRight" type="number" value="'+pad[1]+'"></div><div class="fld"><label>B</label><input id="pBottom" type="number" value="'+pad[2]+'"></div><div class="fld"><label>L</label><input id="pLeft" type="number" value="'+pad[3]+'"></div><div class="size-link"><button type="button" id="linkPad" title="Link padding"><span>â†”</span></button><span>Link</span></div></div><label class="size-label">Margin</label><div class="size-grid"><div class="fld"><label>T</label><input id="mTop" type="number" value="'+mar[0]+'"></div><div class="fld"><label>R</label><input id="mRight" type="number" value="'+mar[1]+'"></div><div class="fld"><label>B</label><input id="mBottom" type="number" value="'+mar[2]+'"></div><div class="fld"><label>L</label><input id="mLeft" type="number" value="'+mar[3]+'"></div><div class="size-link"><button type="button" id="linkMar" title="Link margin"><span>â†”</span></button><span>Link</span></div></div></div><label>Gap</label><div class="px-wrap"><input id="g" type="number" step="1"><span class="px-unit">px</span></div>'+remove;
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
    bindGenericHelpButtons();
    mountPositionControls();
    const btnMoveUp=document.getElementById("btnMoveUp");if(btnMoveUp)btnMoveUp.onclick=()=>moveSelectedBySelection(-1);
    const btnMoveDown=document.getElementById("btnMoveDown");if(btnMoveDown)btnMoveDown.onclick=()=>moveSelectedBySelection(1);
    const btnDel=document.getElementById("btnDeleteSelected");if(btnDel)btnDel.onclick=()=>removeSelected();
}

function render(){
    var step=cur();
    if(step){
        state.layout=repairDefaultPricingFlowLayout(state.layout,step,steps);
        if(String(step.template||"").trim()!==""&&String(step.template||"").trim()!=="simple"){
            state.layout=repairTemplateFlowLayout(state.layout,step,steps);
        }
    }
    renderCanvas();
    renderSettings();
    if(state.sel||state.carouselSel)showLeftPanel("settings");
}

function escapeSidebarHtml(value){
    return String(value||"")
        .replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;")
        .replace(/"/g,"&quot;")
        .replace(/'/g,"&#39;");
}
const sidebarComponentMeta={
    section:{desc:"Main page area that holds rows and freeform blocks."},
    row:{desc:"Horizontal container for columns and arranged content."},
    column:{desc:"Flexible column lane inside a row layout."},
    spacer:{desc:"Adds breathing room between nearby elements."},
    heading:{desc:"Large headline text for hooks and section titles."},
    text:{desc:"Paragraph copy, bullets, or supporting details."},
    button:{desc:"Clickable action button for navigation or checkout."},
    icon:{desc:"Single icon accent for lists, proof, or features."},
    image:{desc:"Visual block for screenshots, mockups, or photos."},
    video:{desc:"Responsive video frame with a clean embed area."},
    carousel:{desc:"Multi-slide media or content showcase block."},
    menu:{desc:"Navigation links for headers and simple site menus."},
    form:{desc:"Lead capture form for opt-ins, bookings, or checkout."},
    testimonial:{desc:"Social proof card with quote and author details."},
    review_form:{desc:"Thank-you form that captures customer reviews for approval."},
    reviews:{desc:"Display block for approved funnel reviews."},
    faq:{desc:"Question-and-answer block for objections and clarity."},
    pricing:{desc:"Offer card with plan, price, features, and button."},
    product_offer:{desc:"Marketplace-style product card with mixed media, price, and buy button."},
    checkout_summary:{desc:"Compact checkout confirmation with selected plan and pay button."},
    shipping_details:{desc:"Dedicated shipping and customer details form for physical-product checkout."},
    physical_checkout_summary:{desc:"Physical-product order summary with cart-aware details and a shipping modal before payment."},
    countdown:{desc:"Urgency timer for expiring offers or launches."}
};
function sidebarPreviewMarkup(type){
    switch(String(type||"")){
        case "section":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-frame'><span class='fb-comp-preview-shell'></span></span></span>";
        case "row":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-grid fb-comp-preview-grid--three'><span class='fb-comp-preview-cell'></span><span class='fb-comp-preview-cell'></span><span class='fb-comp-preview-cell'></span></span></span>";
        case "column":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-grid fb-comp-preview-grid--two'><span class='fb-comp-preview-cell'></span><span class='fb-comp-preview-cell'></span></span></span>";
        case "spacer":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-spacer'><span class='fb-comp-preview-spacer-label'>Gap</span></span></span>";
        case "heading":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-stack'><span class='fb-comp-preview-line is-dark lg'></span><span class='fb-comp-preview-line md'></span></span></span>";
        case "text":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-stack'><span class='fb-comp-preview-line full'></span><span class='fb-comp-preview-line full'></span><span class='fb-comp-preview-line md'></span></span></span>";
        case "button":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-button-wrap'><span class='fb-comp-preview-btn'>Click</span></span></span>";
        case "icon":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-icon-wrap'><span class='fb-comp-preview-icon-chip'><i class='fas fa-star'></i></span><span class='fb-comp-preview-line md'></span></span></span>";
        case "image":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-media'><span class='fb-comp-preview-media-sun'></span><span class='fb-comp-preview-media-ridge'></span></span></span>";
        case "video":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-media'><span class='fb-comp-preview-media-sun'></span><span class='fb-comp-preview-media-ridge'></span><span class='fb-comp-preview-media-play'><i class='fas fa-play'></i></span></span></span>";
        case "carousel":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-carousel'><span class='fb-comp-preview-slide'></span><span class='fb-comp-preview-slide is-tall'></span><span class='fb-comp-preview-slide'></span></span></span>";
        case "menu":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-button-wrap'><span class='fb-comp-preview-nav'><span class='fb-comp-preview-nav-item is-wide'></span><span class='fb-comp-preview-nav-item is-mid'></span><span class='fb-comp-preview-nav-item is-cta'></span></span></span></span>";
        case "form":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-form'><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-btn'>Submit</span></span></span></span>";
        case "shipping_details":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-stack'><span class='fb-comp-preview-line sm is-dark'></span><span class='fb-comp-preview-line md'></span></span><span class='fb-comp-preview-form'><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-input'></span></span></span></span>";
        case "testimonial":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-stack'><span class='fb-comp-preview-line full'></span><span class='fb-comp-preview-line md'></span></span><span class='fb-comp-preview-avatar-row'><span class='fb-comp-preview-avatar'></span><span class='fb-comp-preview-line sm'></span></span></span></span>";
        case "review_form":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-line sm is-dark'></span><span class='fb-comp-preview-line md'></span><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-input'></span><span class='fb-comp-preview-btn'>Review</span></span></span>";
        case "reviews":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-line md is-dark'></span><span class='fb-comp-preview-avatar-row'><span class='fb-comp-preview-avatar'></span><span class='fb-comp-preview-line sm'></span></span><span class='fb-comp-preview-line full'></span><span class='fb-comp-preview-line md'></span></span></span>";
        case "faq":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card'><span class='fb-comp-preview-faq-item'><span class='fb-comp-preview-faq-badge'>?</span><span class='fb-comp-preview-line md'></span></span><span class='fb-comp-preview-faq-item'><span class='fb-comp-preview-faq-badge'>?</span><span class='fb-comp-preview-line lg'></span></span></span></span>";
        case "pricing":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card fb-comp-preview-card--pricing'><span class='fb-comp-preview-pill'>Popular</span><span class='fb-comp-preview-line sm is-dark'></span><span class='fb-comp-preview-price-row'><span class='fb-comp-preview-price'>49</span><span class='fb-comp-preview-period'>/mo</span></span><span class='fb-comp-preview-line md'></span><span class='fb-comp-preview-line sm'></span><span class='fb-comp-preview-btn'>Buy Now</span></span></span>";
        case "product_offer":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card fb-comp-preview-card--pricing'><span class='fb-comp-preview-media'></span><span class='fb-comp-preview-pill'>Best Seller</span><span class='fb-comp-preview-line sm is-dark'></span><span class='fb-comp-preview-price-row'><span class='fb-comp-preview-price'>299</span></span><span class='fb-comp-preview-line md'></span><span class='fb-comp-preview-btn'>Buy Now</span></span></span>";
        case "checkout_summary":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-card fb-comp-preview-card--pricing'><span class='fb-comp-preview-pill'>Selected</span><span class='fb-comp-preview-line sm is-dark'></span><span class='fb-comp-preview-price-row'><span class='fb-comp-preview-price'>29</span><span class='fb-comp-preview-period'>/mo</span></span><span class='fb-comp-preview-line md'></span><span class='fb-comp-preview-line sm'></span><span class='fb-comp-preview-btn'>Pay Now</span></span></span>";
        case "countdown":
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-timer'><span class='fb-comp-preview-timer-box'><span><span class='fb-comp-preview-timer-num'>12</span><br><span class='fb-comp-preview-timer-unit'>Hr</span></span></span><span class='fb-comp-preview-timer-box'><span><span class='fb-comp-preview-timer-num'>08</span><br><span class='fb-comp-preview-timer-unit'>Min</span></span></span><span class='fb-comp-preview-timer-box'><span><span class='fb-comp-preview-timer-num'>43</span><br><span class='fb-comp-preview-timer-unit'>Sec</span></span></span><span class='fb-comp-preview-timer-box'><span><span class='fb-comp-preview-timer-num'>05</span><br><span class='fb-comp-preview-timer-unit'>Day</span></span></span></span></span>";
        default:
            return "<span class='fb-comp-drag-ghost__preview' aria-hidden='true'><span class='fb-comp-preview-stack'><span class='fb-comp-preview-line full'></span><span class='fb-comp-preview-line md'></span></span></span>";
    }
}
var _sidebarDragGhost=null;
function clearSidebarDragGhost(){
    if(_sidebarDragGhost&&_sidebarDragGhost.parentNode){
        _sidebarDragGhost.parentNode.removeChild(_sidebarDragGhost);
    }
    _sidebarDragGhost=null;
}
function buildSidebarDragGhost(source){
    clearSidebarDragGhost();
    if(!source)return null;
    var type=String(source.dataset.c||"").trim();
    var icon=source.querySelector("i");
    var iconHtml=icon?icon.outerHTML:"";
    var label=String(source.textContent||type).trim()||type;
    var meta=sidebarComponentMeta[type]||{};
    var ghost=document.createElement("div");
    ghost.className="fb-comp-drag-ghost";
    ghost.innerHTML=""
        + "<span class='fb-comp-drag-ghost__head'>"
        + "<span class='fb-comp-drag-ghost__icon'>"+iconHtml+"</span>"
        + "<span class='fb-comp-drag-ghost__copy'>"
        + "<span class='fb-comp-drag-ghost__label'>"+escapeSidebarHtml(label)+"</span>"
        + "<span class='fb-comp-drag-ghost__desc'>"+escapeSidebarHtml(meta.desc||"Drag into the funnel to add this block.")+"</span>"
        + "</span>"
        + "</span>"
        + sidebarPreviewMarkup(type);
    document.body.appendChild(ghost);
    _sidebarDragGhost=ghost;
    return ghost;
}

var _sidebarDragActive=false;
document.querySelectorAll(".fb-lib button").forEach(b=>{
    if(b.dataset.compTemplate){
        b.draggable=false;
        b.onclick=()=>{applyComponentTemplate(String(b.dataset.compTemplate||""));};
        return;
    }
    b.ondragstart=e=>{
        _sidebarDragActive=true;
        if(e.dataTransfer){
            e.dataTransfer.effectAllowed="copy";
            e.dataTransfer.setData("c",b.dataset.c||"");
            e.dataTransfer.setData("text/plain",b.dataset.c||"");
            if(typeof e.dataTransfer.setDragImage==="function"){
                var ghost=buildSidebarDragGhost(b);
                if(ghost){
                    var hotX=Math.max(0,Math.round((ghost.offsetWidth||240)/2));
                    var hotY=Math.max(0,Math.round((ghost.offsetHeight||120)/2));
                    e.dataTransfer.setDragImage(ghost,hotX,hotY);
                }
            }
        }
    };
    b.ondragend=e=>{clearSidebarDragGhost();setTimeout(()=>{_sidebarDragActive=false;},100);};
    b.onclick=()=>{if(_sidebarDragActive){_sidebarDragActive=false;return;}addComponent(b.dataset.c||"");render();};
});

wireStepManagement();
var fbGrid=document.getElementById("fbGrid"),fbComponentsHide=document.getElementById("fbComponentsHide"),fbComponentsShow=document.getElementById("fbComponentsShow");
var fbTabComponents=document.getElementById("fbTabComponents"),fbTabSettings=document.getElementById("fbTabSettings"),fbTabTemplates=document.getElementById("fbTabTemplates"),fbTabHistory=document.getElementById("fbTabHistory"),
    fbLeftPanelComponents=document.getElementById("fbLeftPanelComponents"),fbLeftPanelSettings=document.getElementById("fbLeftPanelSettings"),fbLeftPanelTemplates=document.getElementById("fbLeftPanelTemplates"),fbLeftPanelHistory=document.getElementById("fbLeftPanelHistory");
function showLeftPanel(panel){
    if(fbLeftPanelSettings)fbLeftPanelSettings.classList.add("hidden");
    if(fbLeftPanelComponents)fbLeftPanelComponents.classList.add("hidden");
    if(fbLeftPanelTemplates)fbLeftPanelTemplates.classList.add("hidden");
    if(fbLeftPanelHistory)fbLeftPanelHistory.classList.add("hidden");
    if(fbTabComponents)fbTabComponents.classList.remove("active");
    if(fbTabSettings)fbTabSettings.classList.remove("active");
    if(fbTabTemplates)fbTabTemplates.classList.remove("active");
    if(fbTabHistory)fbTabHistory.classList.remove("active");

    if(panel==="settings"){
        if(fbLeftPanelSettings)fbLeftPanelSettings.classList.remove("hidden");
        if(fbTabSettings)fbTabSettings.classList.add("active");
    }else if(panel==="templates"){
        if(fbLeftPanelTemplates)fbLeftPanelTemplates.classList.remove("hidden");
        if(fbTabTemplates)fbTabTemplates.classList.add("active");
        renderTemplateLibrary();
    }else if(panel==="history"){
        if(fbLeftPanelHistory)fbLeftPanelHistory.classList.remove("hidden");
        if(fbTabHistory)fbTabHistory.classList.add("active");
        if(typeof renderHistoryDrawer==="function")renderHistoryDrawer();
    }else{
        if(fbLeftPanelComponents)fbLeftPanelComponents.classList.remove("hidden");
        if(fbTabComponents)fbTabComponents.classList.add("active");
    }
}
if(fbTabComponents)fbTabComponents.onclick=()=>showLeftPanel("components");
if(fbTabSettings)fbTabSettings.onclick=()=>showLeftPanel("settings");
if(fbTabTemplates)fbTabTemplates.onclick=()=>showLeftPanel("templates");
if(fbTabHistory)fbTabHistory.onclick=()=>showLeftPanel("history");
var _canvasLockedWidth=0;
var _canvasInnerWidth=0;
var _canvasContentWidth=0;
function lockCanvasWidth(){
    if(!canvas)return;
    if(fbGrid&&fbGrid.classList.contains("components-hidden"))return;
    var w=canvas.offsetWidth;
    if(w>200){
        _canvasLockedWidth=w;
        var innerW=canvas.clientWidth||0; // includes padding, excludes scrollbar
        if(innerW>0)_canvasInnerWidth=innerW;
        var canvasStyle=window.getComputedStyle?window.getComputedStyle(canvas):null;
        var padX=canvasStyle?((parseFloat(canvasStyle.paddingLeft)||0)+(parseFloat(canvasStyle.paddingRight)||0)):0;
        var borderX=canvasStyle?((parseFloat(canvasStyle.borderLeftWidth)||0)+(parseFloat(canvasStyle.borderRightWidth)||0)):0;
        var contentW=Math.round(w-padX-borderX);
        if(contentW<=0 && innerW>0)contentW=Math.round(innerW-padX);
        if(contentW>0)_canvasContentWidth=contentW;
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
        drawLinkWires();
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
    if(_canvasLockedWidth<=0 && canvas){
        var liveW=canvas.offsetWidth;
        if(liveW>200){
            _canvasLockedWidth=liveW;
            var liveInnerW=canvas.clientWidth||0;
            if(liveInnerW>0)_canvasInnerWidth=liveInnerW;
            var liveCS=window.getComputedStyle?window.getComputedStyle(canvas):null;
            var livePadX=liveCS?((parseFloat(liveCS.paddingLeft)||0)+(parseFloat(liveCS.paddingRight)||0)):0;
            var liveBorderX=liveCS?((parseFloat(liveCS.borderLeftWidth)||0)+(parseFloat(liveCS.borderRightWidth)||0)):0;
            var liveContentW=Math.round(liveW-livePadX-liveBorderX);
            if(liveContentW<=0 && liveInnerW>0)liveContentW=Math.round(liveInnerW-livePadX);
            if(liveContentW>0)_canvasContentWidth=liveContentW;
        }
    }
    var prefs=editorPrefs();
    if(_canvasLockedWidth>0)prefs.canvasWidth=_canvasLockedWidth;
    if(_canvasInnerWidth>0)prefs.canvasInnerWidth=_canvasInnerWidth;
    if(_canvasContentWidth>0)prefs.canvasContentWidth=_canvasContentWidth;
    var layoutToSave=withMeasuredSectionStageWidths(state.layout);
    var canvasBg=normalizeCanvasBgValue(prefs.canvasBg||"");
    var requestHeaders={"Content-Type":"application/json","X-CSRF-TOKEN":csrf,"Accept":"application/json"};
    function saveStepLayout(stepId,layout,bg,skipRevision){
        return fetch(saveUrl,{
            method:"POST",
            headers:requestHeaders,
            body:JSON.stringify({step_id:stepId,layout_json:layout,background_color:bg,skip_revision:!!skipRevision})
        }).then(function(r){
            if(!r.ok)throw new Error("Save failed");
            return r.json();
        });
    }
    saveMsg.textContent=_autoSaveMode?"Autosaving...":"Saving...";
    return saveStepLayout(s.id,layoutToSave,canvasBg,false)
        .then(function(p){
            s.layout_json=p.layout_json||clone(layoutToSave);
            s.background_color=(p&&typeof p.background_color==="string"&&p.background_color.trim()!=="")?p.background_color.trim():null;
            s.revision_history=normalizeRevisionHistory(p&&p.revision_history);
            var others=steps.filter(function(step){return +step.id!==+s.id;});
            if(!others.length)return null;
            var jobs=others.map(function(step){
                var stepLayout=(step.layout_json&&typeof step.layout_json==="object")?step.layout_json:defaults(step.type);
                stepLayout=withCanvasBgInLayout(stepLayout,canvasBg);
                return saveStepLayout(step.id,stepLayout,canvasBg,true).then(function(resp){
                    step.layout_json=resp.layout_json||clone(stepLayout);
                    step.background_color=(resp&&typeof resp.background_color==="string"&&resp.background_color.trim()!=="")?resp.background_color.trim():null;
                    return true;
                });
            });
            return Promise.all(jobs);
        })
        .then(function(){
            saveMsg.textContent=(_autoSaveMode?"Autosaved ":"Saved all pages ")+new Date().toLocaleTimeString();
            if(typeof renderHistoryDrawer==="function")renderHistoryDrawer();
        });
}
document.getElementById("saveBtn").onclick=()=>{
    persistCurrentStep().catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
function bindPublishForm(){
    var form=document.getElementById("builderPublishForm");
    var btn=document.getElementById("builderPublishBtn");
    var descriptionInput=document.getElementById("builderTemplateDescription");
    var publishDescriptionInput=document.getElementById("builderPublishDescription");
    if(!form||!btn)return;
    var submitting=false;
    form.addEventListener("submit",function(e){
        if(submitting)return;
        e.preventDefault();
        if(descriptionInput&&publishDescriptionInput){
            publishDescriptionInput.value=descriptionInput.value;
        }
        submitting=true;
        btn.disabled=true;
        var originalLabel=btn.innerHTML;
        btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Saving...';
        flushAutoSave()
            .then(function(){ return persistCurrentStep(); })
            .then(function(){ form.submit(); })
            .catch(function(){
                submitting=false;
                btn.disabled=false;
                btn.innerHTML=originalLabel;
                saveMsg.textContent="Save failed";
                showBuilderToast("Save failed before publish. Please try again.","error");
            });
    });
}
function withPreviewDeviceParam(url, device){
    if(!url)return url;
    if(url.indexOf("preview_device=")>=0)return url;
    var sep=url.indexOf("?")>=0?"&":"?";
    return url+sep+"preview_device="+encodeURIComponent(device||"desktop");
}

document.getElementById("previewBtn").onclick=()=>{
    const s=cur();if(!s)return;
    flushAutoSave()
        .then(()=>persistCurrentStep())
        .then(()=>{
            try{localStorage.setItem("fbPreviewDevice","desktop");}catch(_e){}
            var url=withPreviewDeviceParam(previewTpl.replace("__STEP__",String(s.id)),"desktop");
            window.open(url,"_blank");
        })
        .catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
};
var testFlowBtn=document.getElementById("testFlowBtn");
if(testFlowBtn){
    testFlowBtn.onclick=()=>{
        const s=cur();if(!s||!testFlowTpl)return;
        flushAutoSave()
            .then(()=>persistCurrentStep())
            .then(()=>{
                try{localStorage.setItem("fbPreviewDevice","desktop");}catch(_e){}
                var url=withPreviewDeviceParam(testFlowTpl.replace("__STEP__",String(s.id)),"desktop");
                window.open(url,"_blank");
            })
            .catch(()=>{saveMsg.textContent="Save failed";alert("Save failed.");});
    };
}
document.addEventListener("keydown",e=>{
    const key=String(e.key||"").toLowerCase();
    const ae=document.activeElement;
    const isTextField=!!(ae && (ae.tagName==="INPUT" || ae.tagName==="TEXTAREA" || ae.isContentEditable));
    if(key==="escape"){
        if(state.linkPick){state.linkPick=null;renderCanvas();renderSettings();return;}
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
    if((key==="y"||(key==="z"&&e.shiftKey))&&(e.ctrlKey||e.metaKey)&&!isTextField){
        e.preventDefault();
        redo();
    }
});
document.addEventListener("dragend",function(){clearSidebarDragGhost();clearFreeDropGuides();clearDropPreview();});
document.addEventListener("drop",function(){clearSidebarDragGhost();clearFreeDropGuides();clearDropPreview();});

initDimTipHover();
initContextMenu();
bindSharedTemplateEditModal();
bindPublishForm();
loadStep(state.sid);
})();
</script>
@endsection
