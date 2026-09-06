@extends('layouts.base')
@section('title','BlueGate — شبکه پایدار، مدیریت ساده')
@section('body')
<main>
<section class="container hero">
 <div><div class="eyebrow">BLUEPING · POWERED BY BLUEGATE</div>
 <h1><span class="gradient-text">اتصال پایدار.</span><br>کنترل کامل.</h1>
 <p>خرید، تحویل خودکار، Subscription اختصاصی و مدیریت سرویس در یک تجربه ساده. BlueGate بین شما و پیچیدگی زیرساخت می‌ایستد.</p>
 <div class="hero-actions"><a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">شروع با BlueGate</a><a class="btn ghost" href="{{ route('status') }}"><i class="network-dot"></i>وضعیت شبکه</a></div>
 </div>
 <div class="hero-card">
  <div class="service-head"><div><span class="pill ok">● NETWORK ONLINE</span><h3 style="font-size:23px;margin:15px 0 4px">BluePing Network</h3><span class="muted">Smart delivery infrastructure</span></div></div>
  <div class="metric"><span class="muted">Provisioning</span><b style="color:var(--ok)">Automatic</b></div>
  <div class="metric"><span class="muted">Subscription Gateway</span><b>Private</b></div>
  <div class="metric"><span class="muted">Multi-node Routing</span><b>Active</b></div>
 </div>
</section>
<section class="container section">
 <div class="eyebrow">WHY BLUEGATE</div><h2>از خرید تا اتصال، بدون دردسر</h2><p class="section-lead">یک پنل برای تمام چرخه عمر سرویس.</p>
 <div class="feature-strip"><div class="feature"><b>تحویل خودکار</b><span class="muted">سرویس بعد از پرداخت به‌صورت خودکار Provision می‌شود.</span></div><div class="feature"><b>مدیریت یک‌جا</b><span class="muted">حجم، انقضا، لوکیشن و تمدید را از پنل کنترل کن.</span></div><div class="feature"><b>زیرساخت چندنودی</b><span class="muted">انتخاب نود و Failover در لایه BlueGate مدیریت می‌شود.</span></div></div>
</section>
<section class="container section"><div class="top-title"><div><h2>پلن‌های BluePing</h2><div class="muted">متناسب با نوع استفاده‌ات انتخاب کن.</div></div></div>
<div class="grid">@forelse($products as $product)<article class="card service-card"><div class="service-head"><span class="pill">{{ $product->category }}</span><span class="muted">BluePing</span></div><div><h3 style="font-size:21px">{{ $product->name }}</h3><p class="muted" style="line-height:1.8">{{ $product->description }}</p></div>@php($first=$plans->get($product->id)?->first())@if($first)<div class="price">از {{ number_format((float)$first->base_price) }} <small style="font-size:12px;color:var(--muted)">تومان</small></div>@endif<a class="btn primary" href="{{ auth()->check()?route('app.buy'):route('register') }}">مشاهده پلن‌ها</a></article>@empty<div class="card empty">هنوز پلنی تعریف نشده است.</div>@endforelse</div>
</section></main>
@endsection