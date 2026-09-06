@extends('layouts.app')
@section('title','خرید سرویس — BlueGate')
@section('content')
<div class="top-title">
 <div><h2>خرید سرویس</h2><div class="muted">پلن مناسب را انتخاب کن؛ کد تخفیف را همان موقع اعمال کن.</div></div>
 <form method="post" action="{{ route('app.trial.claim') }}">@csrf<button class="btn">دریافت تست رایگان</button></form>
</div>
@foreach($products as $product)
<section class="section">
 <h2 style="font-size:22px">{{ $product->name }}</h2><p class="section-lead">{{ $product->description }}</p>
 <div class="grid">
 @foreach($plans->get($product->id,collect()) as $plan)
 <form class="card" method="post" action="{{ route('app.buy.order') }}">@csrf
  <input type="hidden" name="plan_id" value="{{ $plan->id }}">
  <span class="pill">{{ $plan->duration_days }} روز</span>
  @if($plan->trial_enabled)<span class="pill">Trial</span>@endif
  <h3 style="margin-top:14px">{{ $plan->name }}</h3>
  <p class="muted">{{ $plan->unlimited_traffic ? 'ترافیک نامحدود' : (($plan->traffic_gb ?? 0).' GB ترافیک') }} · {{ $plan->device_limit ?? '∞' }} دستگاه</p>
  <div class="price">{{ number_format((float)$plan->base_price) }} تومان</div>
  <div class="field"><input class="input" name="coupon_code" placeholder="کد تخفیف (اختیاری)"></div>
  <button class="btn primary" style="width:100%">ساخت سفارش</button>
 </form>
 @endforeach
 </div>
</section>
@endforeach
@endsection
