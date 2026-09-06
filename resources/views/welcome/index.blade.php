@extends('layouts.base')
@section('title','BluePing — اینترنت ساده‌تر')
@section('body')
<main>
<section class="container hero">
 <div>
  <div class="eyebrow">BLUEPING</div>
  <h1>فقط انتخاب کن.<br>وصل شو.</h1>
  <p>دو سرویس برای دو مدل استفاده: Standard برای مصرف روزمره، Pro برای وقتی که کیفیت و پایداری برات مهم‌تره.</p>
  <div class="hero-actions">
   <a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">خرید سرویس</a>
   <a class="btn ghost" href="#compare">مقایسه Standard و Pro</a>
  </div>
 </div>
 <div class="hero-card">
  <div class="metric"><span class="muted">Standard</span><b>روزمره و اقتصادی</b></div>
  <div class="metric"><span class="muted">Pro</span><b>کیفیت و پایداری بیشتر</b></div>
  <div class="metric"><span class="muted">تحویل</span><b>خودکار از پنل</b></div>
  <div class="metric"><span class="muted">اتصال</span><b>Subscription اختصاصی</b></div>
 </div>
</section>

<section id="services" class="container section">
 <div class="top-title"><div><div class="eyebrow">SERVICES</div><h2>دو انتخاب، بدون شلوغ‌کاری</h2></div></div>
 <div class="product-pair">
  <article class="card product-panel">
   <span class="pill">STANDARD</span>
   <h3>BluePing Standard</h3>
   <p class="muted">برای پیام‌رسان، وب‌گردی و استفاده عادی روزانه.</p>
   <ul><li>اقتصادی‌تر</li><li>چند لوکیشن</li><li>مدیریت از پنل</li><li>مناسب استفاده معمول</li></ul>
   <a class="btn" href="{{ auth()->check()?route('app.buy'):route('register') }}">انتخاب Standard</a>
  </article>
  <article class="card product-panel" style="border-color:#315a7b">
   <span class="pill ok">PRO</span>
   <h3>BluePing Pro</h3>
   <p class="muted">برای استفاده مداوم‌تر و وقتی کیفیت اتصال اولویت بالاتری داره.</p>
   <ul><li>پایداری بیشتر</li><li>چند لوکیشن</li><li>مدیریت از پنل</li><li>مناسب استفاده جدی‌تر</li></ul>
   <a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">انتخاب Pro</a>
  </article>
 </div>
</section>

<section id="compare" class="container section">
 <div class="top-title"><div><div class="eyebrow">COMPARE</div><h2>کدوم مناسب توئه؟</h2></div></div>
 <div style="overflow:auto;border-radius:14px">
 <table class="compare-table">
  <tr><th>ویژگی</th><th>Standard</th><th>Pro</th></tr>
  <tr><td>استفاده روزمره</td><td>عالی</td><td>عالی</td></tr>
  <tr><td>اولویت قیمت</td><td>بیشتر</td><td>کمتر</td></tr>
  <tr><td>استفاده مداوم</td><td>مناسب</td><td>بهتر</td></tr>
  <tr><td>اولویت کیفیت</td><td>عادی</td><td>بیشتر</td></tr>
 </table>
 </div>
</section>

<section class="container section">
 <div class="top-title"><div><div class="eyebrow">PLANS</div><h2>پلن‌های فعال</h2></div><a class="btn ghost" href="{{ auth()->check()?route('app.buy'):route('register') }}">همه پلن‌ها</a></div>
 <div class="grid">
 @forelse($products as $product)
  @php($label=strtolower($product->name.' '.$product->slug))
  @if(str_contains($label,'standard') || (str_contains($label,'pro') && !str_contains($label,'boost')))
   @php($first=$plans->get($product->id)?->first())
   <article class="card service-card">
    <div class="service-head"><strong>{{ $product->name }}</strong><span class="pill">{{ str_contains($label,'pro')?'PRO':'STANDARD' }}</span></div>
    <p class="muted" style="font-size:12px;line-height:1.8">{{ $product->description }}</p>
    @if($first)<div class="price">از {{ number_format((float)$first->base_price) }} <span class="muted" style="font-size:11px">تومان</span></div>@endif
    <a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">خرید</a>
   </article>
  @endif
 @empty
  <div class="card empty">فعلاً پلنی برای فروش ثبت نشده.</div>
 @endforelse
 </div>
</section>

<section id="faq" class="container section">
 <div class="top-title"><div><div class="eyebrow">FAQ</div><h2>سوال‌های پرتکرار</h2></div></div>
 <div class="list-card">
  <details class="list-row"><summary>برای استفاده معمول کدوم رو بگیرم؟</summary><p class="muted">Standard برای استفاده روزمره انتخاب اقتصادی‌تریه.</p></details>
  <details class="list-row"><summary>Pro برای چه کسیه؟</summary><p class="muted">برای استفاده مداوم‌تر یا وقتی کیفیت اتصال برات مهم‌تر از اختلاف قیمته.</p></details>
  <details class="list-row"><summary>بعد از خرید چی می‌گیرم؟</summary><p class="muted">سرویس داخل پنل فعال میشه و لینک Subscription، QR و مدیریت سرویس در اختیارت قرار می‌گیره.</p></details>
 </div>
</section>
</main>
@endsection