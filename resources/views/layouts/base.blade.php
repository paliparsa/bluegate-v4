<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'BlueGate')</title>
    <meta name="theme-color" content="#07111f">
    <style>
:root{--bg:#07111f;--panel:#0d1a2b;--soft:#13243a;--line:#203651;--text:#f5f8fc;--muted:#9fb1c7;--blue:#55a7ff;--ok:#4fd39b;--warn:#ffcc66;--danger:#ff7a8a;--shadow:0 18px 60px rgba(0,0,0,.25)}*{box-sizing:border-box}body{margin:0;font-family:Tahoma,Arial,sans-serif;background:linear-gradient(180deg,#07111f 0,#091624 45%,#07111f 100%);color:var(--text);min-height:100vh}a{text-decoration:none;color:inherit}.container{width:min(1120px,calc(100% - 32px));margin:auto}.nav{height:72px;display:flex;align-items:center;justify-content:space-between}.brand{font-weight:900;font-size:21px;letter-spacing:.2px}.brand span{color:var(--blue)}.navlinks{display:flex;gap:10px;align-items:center}.btn{border:1px solid var(--line);background:var(--panel);color:#fff;padding:11px 16px;border-radius:12px;cursor:pointer;font-weight:700}.btn.primary{background:linear-gradient(135deg,#268cff,#66b5ff);color:#04111f;border:0}.btn.ghost{background:transparent}.hero{padding:72px 0 52px;display:grid;grid-template-columns:1.15fr .85fr;gap:34px;align-items:center}.eyebrow{color:#82bdff;font-weight:800;margin-bottom:14px}.hero h1{font-size:clamp(38px,7vw,72px);line-height:1.05;margin:0 0 18px}.hero p{font-size:18px;line-height:2;color:var(--muted);max-width:720px}.hero-card,.card{background:linear-gradient(180deg,rgba(17,34,55,.95),rgba(11,25,42,.95));border:1px solid var(--line);border-radius:22px;box-shadow:var(--shadow)}.hero-card{padding:26px}.metric{display:flex;justify-content:space-between;padding:17px 0;border-bottom:1px solid var(--line)}.metric:last-child{border-bottom:0}.section{padding:34px 0}.section h2{font-size:30px;margin:0 0 8px}.section-lead{color:var(--muted);margin-bottom:22px}.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.card{padding:22px}.card h3{margin:0 0 8px}.muted{color:var(--muted)}.price{font-size:27px;font-weight:900;margin:18px 0}.pill{display:inline-flex;padding:7px 10px;border-radius:999px;background:#102943;color:#8bc6ff;font-size:12px}.footer{padding:50px 0;color:var(--muted);text-align:center}.auth-wrap{min-height:calc(100vh - 72px);display:grid;place-items:center;padding:30px}.auth{width:min(440px,100%);padding:28px}.field{margin:14px 0}.field label{display:block;margin-bottom:8px;color:#c8d6e8}.input{width:100%;border:1px solid var(--line);background:#091522;color:#fff;border-radius:12px;padding:13px}.alert{padding:12px 14px;border-radius:12px;margin:12px 0;background:#3b1b25;color:#ffc2ca}.success{background:#123629;color:#a7f1cf}.shell{display:grid;grid-template-columns:240px 1fr;min-height:calc(100vh - 72px);gap:20px;padding-bottom:30px}.sidebar{padding:18px;height:max-content;position:sticky;top:20px}.side a{display:block;padding:12px 13px;border-radius:10px;color:#c4d2e3;margin:4px 0}.side a:hover,.side a.active{background:#132b45;color:#fff}.content{min-width:0}.top-title{display:flex;justify-content:space-between;align-items:center;margin:24px 0 16px}.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.stat{padding:18px}.stat b{font-size:26px;display:block;margin-top:8px}.table-wrap{overflow:auto}.table{width:100%;border-collapse:collapse}.table th,.table td{padding:13px;border-bottom:1px solid var(--line);text-align:right;white-space:nowrap}.progress{height:9px;background:#17283d;border-radius:999px;overflow:hidden}.progress i{display:block;height:100%;background:#58aaff}.empty{text-align:center;padding:45px;color:var(--muted)}@media(max-width:850px){.hero{grid-template-columns:1fr}.grid,.stats{grid-template-columns:1fr 1fr}.shell{grid-template-columns:1fr}.sidebar{position:static}.side{display:flex;overflow:auto}.side a{white-space:nowrap}}@media(max-width:560px){.navlinks .hide-sm{display:none}.grid,.stats{grid-template-columns:1fr}.hero{padding-top:38px}.hero h1{font-size:43px}.container{width:min(100% - 22px,1120px)}}
</style>
    @stack('head')
</head>
<body>
    <header class="container">
        <nav class="nav">
            <a class="brand" href="/"><span>Blue</span>Gate</a>

            <div class="navlinks">
                @auth
                    <a class="btn ghost hide-sm" href="{{ route('app.dashboard') }}">داشبورد</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn" type="submit">خروج</button>
                    </form>
                @else
                    <a class="btn ghost" href="{{ route('login') }}">ورود</a>
                    <a class="btn primary" href="{{ route('register') }}">ساخت حساب</a>
                @endauth
            </div>
        </nav>
    </header>

    @yield('body')

    <footer class="footer">BlueGate V4 · Service Platform</footer>
</body>
</html>
