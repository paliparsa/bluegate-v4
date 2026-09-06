<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','BlueGate')</title>
<meta name="theme-color" content="#071018">
<style>
:root{
 --bg:#071018;--panel:#0d1822;--panel2:#101d28;--panel3:#14232f;
 --text:#f4f7fa;--muted:#8fa0ae;--line:#1d2b36;--line2:#293a47;
 --blue:#4aa3ff;--blue2:#74b9ff;--ok:#46ce96;--warn:#e7b75c;--danger:#f46f7e;
 --radius:16px;--radius-sm:11px;--shadow:0 18px 50px rgba(0,0,0,.22)
}
*{box-sizing:border-box}html{scroll-behavior:smooth}
body{margin:0;background:var(--bg);color:var(--text);font-family:Tahoma,Arial,sans-serif;min-height:100vh}
body:before{content:"";position:fixed;inset:0;pointer-events:none;background:
linear-gradient(rgba(255,255,255,.014) 1px,transparent 1px),
linear-gradient(90deg,rgba(255,255,255,.014) 1px,transparent 1px);
background-size:32px 32px;mask-image:linear-gradient(to bottom,rgba(0,0,0,.7),transparent 75%);z-index:-1}
a{text-decoration:none;color:inherit}button,input,select,textarea{font:inherit}.container{width:min(1180px,calc(100% - 32px));margin:auto}
.nav{height:68px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--line)}
.brand{display:flex;align-items:center;gap:10px;font-weight:900;font-size:20px}.brand-mark{width:28px;height:28px;border:1px solid #2d7ec9;border-radius:9px;position:relative;background:#0b1c2a}.brand-mark:after{content:"";position:absolute;inset:7px;border:2px solid var(--blue);border-radius:50%}.brand span{color:var(--blue)}
.navlinks,.landing-nav{display:flex;align-items:center;gap:8px}.landing-nav a{padding:8px 10px;color:#9eb0be;font-size:13px}.landing-nav a:hover{color:#fff}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid var(--line2);background:#111e29;color:#eef5fa;padding:10px 14px;border-radius:10px;cursor:pointer;font-weight:800;font-size:13px;transition:.15s}
.btn:hover{border-color:#3c5363;background:#152531}.btn.primary{background:#1977d3;border-color:#1977d3;color:#fff}.btn.primary:hover{background:#2586e3}.btn.ghost{background:transparent}.btn.danger{color:#ffb8c1;border-color:#663743}
.card{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius)}
.muted{color:var(--muted)}.pill{display:inline-flex;align-items:center;gap:6px;padding:5px 8px;border:1px solid var(--line2);border-radius:999px;color:#a8b7c3;font-size:11px;font-weight:800;background:#0b141d}.pill.ok{color:#76dfb4;border-color:#245340;background:#0d1c18}
.network-dot{width:7px;height:7px;border-radius:50%;background:var(--ok);display:inline-block}
.section{padding:34px 0}.section h2{margin:0 0 7px;font-size:26px}.section-lead{margin:0;color:var(--muted);line-height:1.8}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.top-title{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin:24px 0 16px}.top-title h2{margin:3px 0 4px;font-size:26px}.top-title .muted{font-size:13px}
.eyebrow,.admin-kicker{font-size:10px;letter-spacing:1.4px;color:#6faee8;font-weight:900}
.price{font-size:27px;font-weight:900;margin:14px 0}
.input{width:100%;border:1px solid var(--line2);background:#09131c;color:#fff;border-radius:10px;padding:12px 13px;outline:none}.input:focus{border-color:#3a86c8;box-shadow:0 0 0 3px rgba(60,142,216,.08)}
.field{margin:12px 0}.field label{display:block;font-size:12px;margin-bottom:7px;color:#a9bac8}
.alert{padding:12px 14px;border-radius:11px;border:1px solid #5d2d37;background:#201116;color:#ffb8c0}.alert.success{border-color:#245440;background:#0d1c18;color:#88e4bd}
.progress{height:6px;border-radius:999px;background:#162530;overflow:hidden}.progress i{display:block;height:100%;background:#2b8de5;border-radius:999px}
.empty{text-align:center;padding:44px 22px}.empty h3{margin-top:0}
.shell{display:grid;grid-template-columns:232px 1fr;gap:22px;min-height:calc(100vh - 68px);padding-bottom:34px}
.sidebar{padding:12px;height:max-content;position:sticky;top:14px}.user-chip{padding:10px 9px 13px;border-bottom:1px solid var(--line);margin-bottom:6px}.user-chip strong{display:block;font-size:13px}.user-chip span{font-size:11px}
.side-section{font-size:9px;letter-spacing:1.3px;color:#5f7484;font-weight:900;padding:14px 9px 5px}.side a{display:flex;align-items:center;padding:9px 10px;border-radius:9px;color:#9fb0bd;font-size:12px;margin:2px 0}.side a:hover{background:#111e28;color:#fff}.side a.active{background:#13283a;color:#fff}.side a.active:before{content:"";width:3px;height:15px;background:var(--blue);border-radius:3px;margin-left:8px}
.content{min-width:0}
.mobile-bar{display:none}
.metric-row{display:grid;grid-template-columns:repeat(4,1fr);border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--panel)}
.metric-cell{padding:16px 18px;border-left:1px solid var(--line)}.metric-cell:last-child{border-left:0}.metric-cell span{display:block;color:var(--muted);font-size:11px;margin-bottom:6px}.metric-cell b{font-size:22px}
.list-card{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--panel)}.list-row{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 16px;border-bottom:1px solid var(--line)}.list-row:last-child{border-bottom:0}.list-row:hover{background:#0f1c26}
.service-meta{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.mini{padding:10px 11px;border:1px solid var(--line);border-radius:10px;background:#0a141c}.mini span{font-size:10px}.mini b{display:block;font-size:12px;margin-top:4px}
.service-card{padding:16px}.service-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}.service-card h3{margin:4px 0 12px}.quick-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:12px}.feature-strip{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.feature{display:block;padding:13px;border:1px solid var(--line);border-radius:10px;background:#0a141c}.feature b{display:block;font-size:12px;margin-bottom:4px}.feature span{font-size:11px}
.sub-box{display:flex;gap:8px}.sub-box .input{direction:ltr;text-align:left}
.hero{padding:78px 0 50px;display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center}.hero h1{font-size:clamp(42px,6vw,70px);line-height:1.08;margin:8px 0 18px;letter-spacing:-1.8px}.hero p{font-size:16px;line-height:2;color:#9caebb;max-width:650px}.hero-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:24px}.hero-card{padding:0;overflow:hidden;background:#0a151e;border:1px solid var(--line);border-radius:16px}.hero-card .metric{display:flex;justify-content:space-between;padding:15px 17px;border-bottom:1px solid var(--line)}.hero-card .metric:last-child{border-bottom:0}
.compare-table{width:100%;border-collapse:collapse;border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--panel)}.compare-table th,.compare-table td{padding:14px 16px;border-bottom:1px solid var(--line);text-align:right}.compare-table tr:last-child td{border-bottom:0}.compare-table th{font-size:11px;color:#7f95a5}
.product-pair{display:grid;grid-template-columns:1fr 1fr;gap:12px}.product-panel{padding:22px}.product-panel h3{font-size:23px;margin:8px 0}.product-panel ul{margin:16px 0;padding:0;list-style:none}.product-panel li{padding:8px 0;border-bottom:1px solid var(--line);color:#a9b8c4;font-size:13px}.product-panel li:last-child{border-bottom:0}
.auth-wrap{min-height:calc(100vh - 68px);display:grid;place-items:center;padding:28px}.auth{width:min(430px,100%);padding:26px}
.footer{padding:42px 0;text-align:center;color:#667987;font-size:11px}

/* purchase builder */
.builder-shell{display:grid;grid-template-columns:1.1fr .9fr;gap:12px;align-items:start}.builder-main,.builder-summary{padding:18px}.builder-summary{position:sticky;top:14px}.step{padding:16px 0;border-bottom:1px solid var(--line)}.step:last-child{border-bottom:0}.step-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px}.step-num{width:24px;height:24px;display:inline-grid;place-items:center;border:1px solid #2a5f8f;border-radius:8px;color:#75b9f6;font-size:10px;font-weight:900}.choice-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}.choice{border:1px solid var(--line);background:#0a141c;border-radius:11px;padding:12px;cursor:pointer;color:#c7d3dc;text-align:right}.choice:hover{border-color:#345064}.choice.active{border-color:#347fc0;background:#0d2130}.choice strong{display:block;margin-bottom:3px}.choice small{color:#8193a1}.choice-row{display:flex;gap:7px;flex-wrap:wrap}.chip-choice{border:1px solid var(--line);background:#0a141c;border-radius:9px;padding:9px 11px;cursor:pointer;color:#aebdca;font-weight:800}.chip-choice.active{border-color:#347fc0;background:#0d2130;color:#fff}.recommend-box{padding:15px 16px;border:1px solid var(--line);border-radius:13px;background:#0b151e;margin-bottom:12px}.recommend-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:10px}.recommend-result{display:none;margin-top:10px;padding:10px;border-radius:9px;border:1px solid #244d3d;background:#0d1c18}.summary-product{font-size:22px;font-weight:900;margin:5px 0}.summary-price{font-size:31px;font-weight:900;margin:14px 0}.summary-price small{font-size:11px;color:var(--muted)}.summary-lines{display:grid}.summary-line{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid var(--line);font-size:12px}.summary-line span:first-child{color:var(--muted)}.builder-alert{padding:10px 11px;border:1px solid #4b4025;background:#18150b;color:#d8bb79;border-radius:9px;font-size:11px;line-height:1.8}.location-choice{display:flex;align-items:center;gap:8px}

/* setup */
.setup-hero{display:grid;grid-template-columns:1.1fr .9fr;gap:12px;margin:22px 0 14px}.setup-hero .card{padding:18px}.device-tabs{display:flex;gap:6px;flex-wrap:wrap;margin:12px 0}.device-tab{border:1px solid var(--line);background:#0a141c;color:#a7b7c4;border-radius:9px;padding:9px 11px;font-weight:800;cursor:pointer}.device-tab.active{border-color:#347fc0;background:#0d2130;color:#fff}.device-panel{display:none}.device-panel.active{display:block}.steps{display:grid;gap:7px;margin-top:12px}.guide-step{display:grid;grid-template-columns:30px 1fr;gap:9px;padding:11px;border:1px solid var(--line);border-radius:10px;background:#0a141c}.guide-step .n{width:27px;height:27px;display:grid;place-items:center;border:1px solid #315777;border-radius:8px;color:#79bdf8;font-size:10px;font-weight:900}.guide-step b{display:block;font-size:12px;margin-bottom:3px}.guide-step p{margin:0;color:var(--muted);font-size:11px;line-height:1.8}.quick-connect-card{padding:18px;border:1px solid #214434;background:#0b1815;border-radius:14px}.quick-connect-actions{display:flex;gap:7px;flex-wrap:wrap;margin-top:12px}.platform-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px}.platform-card{padding:13px;border:1px solid var(--line);border-radius:10px;background:#0a141c}.platform-card strong{display:block;font-size:12px;margin:6px 0}.platform-card .muted{font-size:10px}.platform-icon{font-size:20px}.setup-note{padding:10px 11px;border:1px solid #4b4025;background:#18150b;color:#d8bb79;border-radius:9px;font-size:11px;line-height:1.8}.setup-service-select{display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:13px}.setup-service-select select{max-width:420px}

@media(max-width:900px){
 .hero,.setup-hero,.builder-shell,.quick-grid{grid-template-columns:1fr}.grid{grid-template-columns:1fr 1fr}.shell{grid-template-columns:1fr}.sidebar{display:none}.builder-summary{position:static}.metric-row{grid-template-columns:1fr 1fr}.metric-cell:nth-child(2){border-left:0}.metric-cell:nth-child(-n+2){border-bottom:1px solid var(--line)}
 .mobile-bar{display:flex;position:fixed;bottom:9px;right:9px;left:9px;z-index:50;background:#0a151e;border:1px solid var(--line2);border-radius:14px;padding:6px;justify-content:space-around;box-shadow:var(--shadow)}.mobile-bar a{font-size:10px;color:#8193a1;padding:8px 9px;border-radius:8px}.mobile-bar a.active{background:#13283a;color:#fff}body{padding-bottom:68px}
}
@media(max-width:580px){
 .container{width:min(100% - 20px,1180px)}.hero{padding:46px 0 32px}.hero h1{font-size:42px}.grid,.product-pair,.platform-grid,.choice-grid,.feature-strip{grid-template-columns:1fr}.top-title{align-items:flex-start}.metric-row{grid-template-columns:1fr 1fr}.sub-box{flex-direction:column}.device-tabs{display:grid;grid-template-columns:1fr 1fr}.navlinks .hide-sm,.landing-nav{display:none}.card{border-radius:13px}
}

/* admin + reseller compatibility in the refreshed system */
.admin-hero{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:18px;border:1px solid var(--line);border-radius:14px;background:var(--panel);margin:22px 0 14px}.admin-actions{display:flex;gap:7px;flex-wrap:wrap}
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}.kpi{padding:14px;border:1px solid var(--line);border-radius:12px;background:var(--panel)}.kpi .label,.kpi .hint{color:var(--muted);font-size:10px}.kpi .value{font-size:22px;font-weight:900;margin:6px 0}
.panel-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:10px}.panel-card{padding:16px;border:1px solid var(--line);border-radius:12px;background:var(--panel)}.panel-title{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:12px}.panel-title h3{margin:0}
.node-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px}.node-card{padding:14px;border:1px solid var(--line);border-radius:12px;background:var(--panel)}.node-top{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.node-status{display:flex;align-items:center;gap:6px}.node-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:7px;margin:12px 0}.node-actions{display:flex;gap:6px;flex-wrap:wrap}.status-dot{width:7px;height:7px;border-radius:50%;background:#657785}.status-dot.online{background:var(--ok)}.status-dot.offline{background:var(--danger)}
.toolbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}.form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.switch-row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}.switch-row label{display:flex;gap:6px;align-items:center;color:#aebdca;font-size:12px}
.catalog-product{padding:16px;border:1px solid var(--line);border-radius:12px;background:var(--panel);margin-bottom:11px}.catalog-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.plan-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:12px}.plan-card{padding:12px;border:1px solid var(--line);border-radius:10px;background:#0a141c}
.dev-hero{display:grid;grid-template-columns:1fr .8fr;gap:10px}.dev-card{padding:16px;border:1px solid var(--line);border-radius:12px;background:var(--panel)}.codebox{direction:ltr;text-align:left;background:#050b10;border:1px solid var(--line);border-radius:10px;padding:12px;overflow:auto;color:#9dcaf0;font-family:Consolas,monospace;font-size:11px;line-height:1.7}.ability-list{display:flex;gap:6px;flex-wrap:wrap}.ability{padding:5px 7px;border:1px solid var(--line);border-radius:999px;font-size:10px;color:#90abc0}
.chart-bars{display:flex;align-items:end;gap:6px;height:160px;padding-top:16px}.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;gap:5px;min-width:7px}.bar{width:100%;max-width:20px;border-radius:4px 4px 2px 2px;background:#2b8de5;min-height:3px}.bar-label{font-size:9px;color:#718593}
@media(max-width:900px){.kpi-grid{grid-template-columns:1fr 1fr}.panel-grid,.dev-hero{grid-template-columns:1fr}.node-grid{grid-template-columns:1fr}.form-grid,.plan-grid{grid-template-columns:1fr 1fr}}
@media(max-width:580px){.kpi-grid,.form-grid,.plan-grid{grid-template-columns:1fr}.admin-hero{align-items:flex-start;flex-direction:column}.node-meta{grid-template-columns:1fr 1fr}}

.table-wrap{overflow:auto;border:1px solid var(--line);border-radius:12px}.table{width:100%;border-collapse:collapse;background:var(--panel)}.table th,.table td{padding:12px 13px;border-bottom:1px solid var(--line);text-align:right;white-space:nowrap}.table th{font-size:10px;color:#758998;font-weight:800}.table tr:last-child td{border-bottom:0}.table tr:hover td{background:#0f1b25}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:9px}.stat{padding:14px}.stat b{display:block;font-size:21px;margin:6px 0}.stat small{font-size:10px;color:var(--muted)}
@media(max-width:900px){.stats{grid-template-columns:1fr 1fr}}
@media(max-width:580px){.stats{grid-template-columns:1fr}}
</style>
@stack('head')
</head>
<body>
<header class="container"><nav class="nav">
<a class="brand" href="/"><i class="brand-mark"></i><span>Blue</span>Gate</a>
<div class="landing-nav"><a href="/#services">سرویس‌ها</a><a href="/#compare">مقایسه</a><a href="/#faq">سوالات</a></div>
<div class="navlinks">
@auth
<a class="btn ghost hide-sm" href="{{ route('app.dashboard') }}">پنل</a>
<form method="post" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">خروج</button></form>
@else
<a class="btn ghost" href="{{ route('login') }}">ورود</a>
<a class="btn primary" href="{{ route('register') }}">ساخت حساب</a>
@endauth
</div>
</nav></header>
@yield('body')
<footer class="footer">BlueGate · BluePing</footer>
@stack('scripts')
</body>
</html>