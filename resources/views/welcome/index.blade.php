@extends('layouts.base')
@section('title','BluePing Standard & Pro — سرویس مناسب استفاده شما')
@section('body')
<main>
<section class="container hero-service">
 <div class="hero">
  <div>
   <div class="eyebrow">BLUEPING · STANDARD & PRO</div>
   <h1>دو سرویس.<br><span class="gradient-text">دو سطح از تجربه اتصال.</span></h1>
   <p>BluePing را ساده نگه داشتیم: <strong>Standard</strong> برای استفاده روزمره و اقتصادی، و <strong>Pro</strong> برای زمانی که کیفیت و پایداری اتصال اولویت بالاتری دارد. انتخابت را بر اساس نوع استفاده انجام بده، نه اسم‌های پیچیده.</p>
   <div class="hero-actions"><a class="btn primary" href="#services">Standard یا Pro؟</a><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">مشاهده پلن‌ها</a></div>
   <div class="trust-row"><span class="trust-chip">✓ تحویل خودکار</span><span class="trust-chip">✓ چند لوکیشن</span><span class="trust-chip">✓ Subscription اختصاصی</span><span class="trust-chip">✓ مدیریت از پنل</span></div>
  </div>
  <div class="hero-card network-console">
   <div class="console-head"><div><div class="admin-kicker">BLUEPING NETWORK</div><h3 style="margin:5px 0">انتخاب ساده‌تر</h3></div><span class="pill ok"><i class="network-dot"></i>ONLINE</span></div>
   <div class="console-map">
    <div class="route-line"><b>Standard</b><div class="route-track"><i style="width:76%"></i></div><span class="muted">اقتصادی</span></div>
    <div class="route-line"><b>Pro</b><div class="route-track"><i style="width:94%"></i></div><span class="muted">پایدارتر</span></div>
   </div>
   <div class="mini"><span class="muted">BluePing philosophy</span><b>Standard برای روزمره · Pro برای کیفیت بیشتر</b></div>
  </div>
 </div>
</section>

<section id="services" class="container section">
 <div class="eyebrow">CHOOSE YOUR BLUEPING</div><h2>کدوم سرویس برای تو مناسبه؟</h2>
 <p class="section-lead">فقط دو انتخاب داری؛ تفاوت اصلی در سطح سرویس و نوع استفاده است.</p>
 <div class="grid" style="grid-template-columns:repeat(2,1fr)">
  <article class="card service-card">
   <div class="service-head"><span class="pill">STANDARD</span><span class="muted">Everyday</span></div>
   <div><h3 style="font-size:25px">BluePing Standard</h3><p class="muted" style="line-height:1.9">سرویس اقتصادی برای استفاده معمول اینترنت؛ وقتی یک اتصال ساده، چندلوکیشن و قیمت منطقی می‌خواهی.</p></div>
   <div class="fit-list"><span class="fit">تلگرام و واتساپ</span><span class="fit">وب‌گردی و استفاده روزمره</span><span class="fit">استفاده عادی شبکه‌های اجتماعی</span><span class="fit">انتخاب اقتصادی‌تر</span></div>
   <a class="btn" href="{{ auth()->check()?route('app.buy'):route('register') }}">پلن‌های Standard</a>
  </article>
  <article class="card service-card" style="border-color:rgba(78,167,255,.42);box-shadow:0 18px 55px rgba(33,125,224,.12)">
   <div class="service-head"><span class="pill ok">PRO</span><span class="pill">پیشنهاد برای استفاده جدی‌تر</span></div>
   <div><h3 style="font-size:25px">BluePing Pro</h3><p class="muted" style="line-height:1.9">سطح بالاتر BluePing برای کسی که پایداری و کیفیت مسیر برایش مهم‌تر از کمترین قیمت ممکن است.</p></div>
   <div class="fit-list"><span class="fit">استفاده مداوم‌تر</span><span class="fit">پایداری بالاتر مسیر</span><span class="fit">مناسب کاربری حساس‌تر به کیفیت</span><span class="fit">اولویت کیفیت نسبت به قیمت</span></div>
   <a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">پلن‌های Pro</a>
  </article>
 </div>
</section>

<section id="compare" class="container section">
 <div class="eyebrow">STANDARD VS PRO</div><h2>تفاوت‌ها در یک نگاه</h2>
 <div class="card compare-wrap"><table class="compare">
  <tr><th>ویژگی</th><th>Standard</th><th class="best">Pro</th></tr>
  <tr><td>مناسب برای</td><td>استفاده روزمره</td><td class="best">استفاده جدی‌تر</td></tr>
  <tr><td>اولویت اصلی</td><td>قیمت اقتصادی</td><td class="best">کیفیت و پایداری</td></tr>
  <tr><td>پیام‌رسان و وب</td><td>✓</td><td class="best">✓</td></tr>
  <tr><td>چند لوکیشن</td><td>✓</td><td class="best">✓</td></tr>
  <tr><td>استفاده مداوم</td><td>مناسب</td><td class="best">پیشنهاد بهتر</td></tr>
  <tr><td>هزینه</td><td>کمتر</td><td class="best">بالاتر</td></tr>
 </table></div>
</section>

<section class="container section">
 <div class="eyebrow">QUICK DECISION</div><h2>هنوز بین Standard و Pro موندی؟</h2>
 <div class="scenario-grid" style="grid-template-columns:repeat(2,1fr)">
  <article class="card scenario"><span class="num">01 · VALUE</span><h3>مصرف معمولی دارم</h3><p>اگر بیشتر برای پیام‌رسان، وب‌گردی و استفاده عادی اینترنت سرویس می‌خواهی و قیمت برایت مهم است، Standard انتخاب منطقی‌تری است.</p><a class="btn" href="{{ auth()->check()?route('app.buy'):route('register') }}">Standard رو انتخاب می‌کنم</a></article>
  <article class="card scenario"><span class="num">02 · QUALITY</span><h3>کیفیت اتصال برام مهم‌تره</h3><p>اگر بیشتر از سرویس استفاده می‌کنی یا ترجیح می‌دهی برای سطح بالاتر اتصال هزینه بیشتری پرداخت کنی، Pro انتخاب مناسب‌تری است.</p><a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">Pro رو انتخاب می‌کنم</a></article>
 </div>
</section>

<section class="container section">
 <div class="product-band"><div><div class="admin-kicker">ONE SUBSCRIPTION · MULTIPLE LOCATIONS</div><h3>لوکیشن را بدون خرید سرویس جدا مدیریت کن.</h3><div class="muted">پلن‌های BluePing از داخل پنل مدیریت می‌شوند؛ Subscription، QR، حجم مصرفی، زمان سرویس و عملیات حساب در یک جا قرار دارند.</div></div><a class="btn" href="{{ auth()->check()?route('app.dashboard'):route('register') }}">مشاهده پنل</a></div>
</section>

<section class="container section">
 <div class="eyebrow">LIVE CATALOG</div>
 <div class="top-title"><div><h2>پلن‌های قابل خرید</h2><div class="muted">قیمت‌ها مستقیماً از Catalog خوانده می‌شوند.</div></div><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">فروشگاه کامل</a></div>
 <div class="grid">
 @forelse($products as $product)
  @php($label=strtolower($product->name.' '.$product->slug))
  @if(str_contains($label,'standard') || (str_contains($label,'pro') && !str_contains($label,'boost')))
   <article class="card service-card"><div class="service-head"><span class="pill">{{ str_contains($label,'pro')?'PRO':'STANDARD' }}</span><span class="muted">BluePing</span></div><div><h3 style="font-size:21px">{{ $product->name }}</h3><p class="muted" style="line-height:1.8">{{ $product->description }}</p></div>@php($first=$plans->get($product->id)?->first())@if($first)<div class="price">از {{ number_format((float)$first->base_price) }} <small style="font-size:12px;color:var(--muted)">تومان</small></div>@endif<a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">انتخاب پلن</a></article>
  @endif
 @empty<div class="card empty">Catalog هنوز پلن فعالی ندارد.</div>
 @endforelse
 </div>
</section>

<section id="faq" class="container section">
 <div class="eyebrow">FAQ</div><h2>سوال‌های قبل از خرید</h2>
 <div class="faq">
  <details><summary>Standard و Pro دقیقاً چه فرقی دارن؟</summary><p>Standard روی اقتصادی‌بودن و استفاده روزمره تمرکز دارد؛ Pro برای کسی است که حاضر است برای سطح بالاتر کیفیت و پایداری هزینه بیشتری پرداخت کند.</p></details>
  <details><summary>برای تلگرام و وب‌گردی کدوم کافیه؟</summary><p>برای استفاده عادی، Standard انتخاب اصلی و اقتصادی‌تر است.</p></details>
  <details><summary>چه زمانی Pro بگیرم؟</summary><p>وقتی استفاده‌ات مداوم‌تر است یا کیفیت و پایداری اتصال نسبت به اختلاف قیمت اهمیت بیشتری دارد.</p></details>
  <details><summary>بعد از خرید چه چیزی دریافت می‌کنم؟</summary><p>سرویس در پنل BlueGate تحویل داده می‌شود و Subscription و QR به همراه اطلاعات حجم و زمان سرویس در دسترس قرار می‌گیرد.</p></details>
 </div>
</section>

<section class="container section"><div class="support-band"><div><div class="admin-kicker">NEED HELP CHOOSING?</div><h2 style="margin:6px 0">Standard یا Pro؟</h2><div class="muted">اگر هنوز مطمئن نیستی، نوع استفاده‌ات را برای پشتیبانی بفرست تا انتخاب راحت‌تر شود.</div></div><a class="btn primary" href="{{ auth()->check()?route('app.tickets'):route('register') }}">پشتیبانی BlueGate</a></div></section>
</main>
@endsection