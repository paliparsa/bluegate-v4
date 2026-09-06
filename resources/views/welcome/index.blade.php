@extends('layouts.base')
@section('title','BluePing by BlueGate — سرویس متناسب با شرایط اینترنت')
@section('body')
<main>
<section class="container hero-service">
 <div class="hero">
  <div>
   <div class="eyebrow">BLUEPING · NETWORK SERVICES</div>
   <h1>یک VPN برای همه<br><span class="gradient-text">شرایط کافی نیست.</span></h1>
   <p>BluePing چند سطح سرویس برای نیازهای متفاوت دارد؛ از اتصال اقتصادی روزمره تا مسیرهای Pro و سرویس‌های آماده برای شرایط اختلال. به‌جای خرید تصادفی، سرویسی را انتخاب کن که برای نوع استفاده‌ات ساخته شده.</p>
   <div class="hero-actions"><a class="btn primary" href="#services">سرویس مناسب من کدام است؟</a><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">مشاهده پلن‌های قابل خرید</a></div>
   <div class="trust-row"><span class="trust-chip">✓ تحویل خودکار</span><span class="trust-chip">✓ Subscription اختصاصی</span><span class="trust-chip">✓ مدیریت حجم و زمان</span><span class="trust-chip">✓ پشتیبانی بعد از خرید</span></div>
  </div>
  <div class="hero-card network-console">
   <div class="console-head"><div><div class="admin-kicker">BLUEPING NETWORK</div><h3 style="margin:5px 0">مسیر مناسب، برای نیاز مناسب</h3></div><span class="pill ok"><i class="network-dot"></i>ONLINE</span></div>
   <div class="console-map">
    <div class="route-line"><b>Standard</b><div class="route-track"><i style="width:72%"></i></div><span class="muted">روزمره</span></div>
    <div class="route-line"><b>Pro</b><div class="route-track"><i style="width:92%"></i></div><span class="muted">پایدارتر</span></div>
    <div class="route-line"><b>Tunnel</b><div class="route-track"><i style="width:86%"></i></div><span class="muted">اختلال</span></div>
    <div class="route-line"><b>Emergency</b><div class="route-track"><i style="width:100%"></i></div><span class="muted">بحران</span></div>
   </div>
   <div class="mini"><span class="muted">Pro locations</span><b>🇸🇪 Sweden · 🇬🇧 UK · 🇹🇷 Türkiye · 🇩🇪 Germany Pro</b></div>
  </div>
 </div>
</section>

<section id="services" class="container section">
 <div class="eyebrow">CHOOSE BY USE CASE</div><h2>کدوم BluePing برای تو ساخته شده؟</h2>
 <p class="section-lead">تفاوت سرویس‌ها فقط قیمت نیست؛ مسیر اتصال و کاربردشون متفاوته.</p>
 <div class="selector">
  <article class="selector-card"><div class="selector-icon">🔵</div><h3>Standard</h3><p>انتخاب اقتصادی برای استفاده عادی اینترنت و کارهای روزمره.</p><div class="fit-list"><span class="fit">تلگرام و واتساپ</span><span class="fit">وب‌گردی و استفاده روزانه</span><span class="fit">چند لوکیشن</span></div><span class="pill">ECONOMY</span></article>
  <article class="selector-card recommended"><div class="selector-icon">⚡</div><h3>Pro</h3><p>برای وقتی که پایداری مسیر و کیفیت اتصال برات مهم‌تر از کمترین قیمت است.</p><div class="fit-list"><span class="fit">سوئد، انگلیس و ترکیه</span><span class="fit">مسیر اختصاصی Germany Pro</span><span class="fit">مناسب استفاده سنگین‌تر</span></div><span class="pill ok">RECOMMENDED</span></article>
  <article class="selector-card"><div class="selector-icon">🟣</div><h3>Tunnel</h3><p>مسیر جایگزین برای زمان‌هایی که اینترنت عادی با اختلال شدیدتری روبه‌رو می‌شود.</p><div class="fit-list"><span class="fit">شرایط اختلال</span><span class="fit">مسیر متفاوت از اقتصادی</span><span class="fit">قابلیت تبدیل در شرایط ملی</span></div><span class="pill">RESILIENT</span></article>
  <article class="selector-card"><div class="selector-icon">🛡️</div><h3>Emergency / ملی</h3><p>سرویس ذخیره برای شرایط محدودیت شدید؛ پلن مستقیم ملی تا پایان حجم محدودیت زمانی ندارد.</p><div class="fit-list"><span class="fit">آماده برای شرایط بحرانی</span><span class="fit">بدون انقضای زمانی تا اتمام حجم</span><span class="fit">مناسب نگهداری به‌عنوان Backup</span></div><span class="pill">BACKUP</span></article>
 </div>
</section>

<section id="compare" class="container section">
 <div class="top-title"><div><div class="eyebrow">COMPARE</div><h2>فرق سرویس‌ها در یک نگاه</h2></div></div>
 <div class="card compare-wrap"><table class="compare">
 <tr><th>ویژگی</th><th>Standard</th><th class="best">Pro</th><th>Tunnel</th><th>Emergency / ملی</th></tr>
 <tr><td>کاربرد اصلی</td><td>روزمره</td><td class="best">پایداری بیشتر</td><td>اختلال شدید</td><td>شرایط بحرانی</td></tr>
 <tr><td>سطح هزینه</td><td>اقتصادی</td><td class="best">بالاتر</td><td>میانی/ویژه</td><td>بر اساس حجم</td></tr>
 <tr><td>چند لوکیشن</td><td>✓</td><td class="best">✓</td><td>بسته به پلن</td><td>بسته به زیرساخت</td></tr>
 <tr><td>Germany Pro</td><td>—</td><td class="best">✓</td><td>—</td><td>—</td></tr>
 <tr><td>برای اختلال گسترده</td><td>اولویت نیست</td><td class="best">مقاوم‌تر</td><td>✓</td><td>✓</td></tr>
 <tr><td>محدودیت زمانی</td><td>طبق پلن</td><td class="best">طبق پلن</td><td>طبق پلن</td><td>خرید مستقیم: ندارد</td></tr>
 </table></div>
</section>

<section class="container section">
 <div class="eyebrow">REAL SCENARIOS</div><h2>بر اساس کاری که می‌کنی انتخاب کن</h2>
 <div class="scenario-grid">
  <article class="card scenario"><span class="num">01 · DAILY</span><h3>فقط اتصال روزمره می‌خوام</h3><p>اگر مصرفت تلگرام، پیام‌رسان، وب و کارهای عادیه و دنبال قیمت اقتصادی هستی، Standard نقطه شروع منطقیه.</p><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">دیدن Standard</a></article>
  <article class="card scenario"><span class="num">02 · QUALITY</span><h3>پایداری برام مهم‌تره</h3><p>برای استفاده مداوم‌تر و زمانی که مسیر باکیفیت‌تر می‌خوای، Pro با لوکیشن‌های بین‌المللی و Germany Pro انتخاب اصلیه.</p><a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">انتخاب Pro</a></article>
  <article class="card scenario"><span class="num">03 · BACKUP</span><h3>برای اختلال آماده می‌خوام</h3><p>اگر یک اتصال Backup برای زمان محدودیت‌های شدید می‌خوای، Tunnel یا سرویس مستقیم Emergency/ملی را کنار سرویس اصلی نگه دار.</p><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">سرویس‌های ویژه</a></article>
 </div>
</section>

<section id="emergency" class="container section">
 <div class="emergency-banner">
  <div><div class="eyebrow">WHEN THE NETWORK CHANGES</div><h2>BluePing فقط برای روزهای عادی طراحی نشده.</h2><p class="section-lead">در اطلاع‌رسانی‌های BlueGate، سرویس‌های Tunnel برای شرایط اختلال در نظر گرفته شده‌اند و در سناریوی ملی‌شدن اینترنت، باقی‌مانده سرویس Tunnel می‌تواند بر اساس تعرفه سرویس ملی معادل‌سازی شود. خرید مستقیم سرویس ملی نیز تا پایان حجم، محدودیت زمانی ندارد.</p><a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">آماده‌کردن سرویس Backup</a></div>
  <div class="emergency-steps"><div class="emergency-step"><b>Normal</b><div class="muted">Standard / Pro برای استفاده معمول</div></div><div class="emergency-step"><b>Network disruption</b><div class="muted">Pro / Tunnel برای مسیر مقاوم‌تر</div></div><div class="emergency-step"><b>Emergency</b><div class="muted">سرویس ملی به‌عنوان اتصال ذخیره</div></div></div>
 </div>
</section>

<section class="container section">
 <div class="eyebrow">AVAILABLE PLANS</div><div class="top-title"><div><h2>پلن‌های قابل خرید</h2><div class="muted">قیمت و موجودی از Catalog خود BlueGate خوانده می‌شود.</div></div><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">فروشگاه کامل</a></div>
 <div class="grid">@forelse($products as $product)<article class="card service-card"><div class="service-head"><span class="pill">{{ $product->category }}</span><span class="muted">BluePing</span></div><div><h3 style="font-size:21px">{{ $product->name }}</h3><p class="muted" style="line-height:1.8">{{ $product->description }}</p></div>@php($first=$plans->get($product->id)?->first())@if($first)<div class="price">از {{ number_format((float)$first->base_price) }} <small style="font-size:12px;color:var(--muted)">تومان</small></div>@endif<a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">انتخاب پلن</a></article>@empty<div class="card empty">Catalog هنوز پلن فعالی ندارد.</div>@endforelse</div>
</section>

<section class="container section">
 <div class="product-band"><div><div class="admin-kicker">BLUEGATE ECOSYSTEM</div><h3>BluePing برای اتصال؛ BlueGate برای مدیریت.</h3><div class="muted">بعد از خرید، حجم، زمان، Subscription، QR و سرویس‌ها را از پنل مدیریت می‌کنی.</div></div><a class="btn" href="{{ auth()->check()?route('app.dashboard'):route('register') }}">ورود به پنل</a></div>
</section>

<section id="faq" class="container section"><div class="eyebrow">FAQ</div><h2>قبل از خرید</h2>
 <div class="faq">
  <details><summary>Standard یا Pro؟</summary><p>اگر اولویتت قیمت اقتصادی و استفاده روزمره است Standard؛ اگر پایداری و مسیرهای بیشتر اهمیت بالاتری دارد Pro.</p></details>
  <details><summary>برای زمان اختلال چه سرویسی بگیرم؟</summary><p>BlueGate در اطلاع‌رسانی‌های کانال، Pro و Tunnel را برای اختلالات شدیدتر معرفی کرده و سرویس Emergency/ملی را به‌عنوان گزینه Backup ارائه می‌کند.</p></details>
  <details><summary>سرویس ملی تاریخ انقضا دارد؟</summary><p>طبق معرفی فعلی BlueGate، سرویس‌هایی که مستقیماً به‌صورت ملی خریداری می‌شوند محدودیت زمانی ندارند و تا پایان حجم باقی می‌مانند.</p></details>
  <details><summary>بعد از خرید چطور سرویس را مدیریت کنم؟</summary><p>در پنل BlueGate می‌توانی Subscription، QR، حجم مصرفی، زمان انقضا و عملیات سرویس را مشاهده کنی.</p></details>
 </div>
</section>

<section class="container section"><div class="support-band"><div><div class="admin-kicker">HUMAN SUPPORT</div><h2 style="margin:6px 0">مطمئن نیستی کدوم سرویس رو بگیری؟</h2><div class="muted">اول کاربردت رو مشخص کن؛ بعد سرویس مناسب رو انتخاب کن. خرید پایان پشتیبانی نیست.</div></div><a class="btn primary" href="{{ auth()->check()?route('app.tickets'):route('register') }}">مشاوره و پشتیبانی</a></div></section>
</main>
@endsection