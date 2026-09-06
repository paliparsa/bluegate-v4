@extends('layouts.app')
@section('title','پنل — BlueGate')
@section('content')
<div class="top-title">
 <div><h2>{{ explode(' ',auth()->user()->name)[0] }}، خوش اومدی</h2><div class="muted">وضعیت حساب و سرویس‌هات</div></div>
 <a class="btn primary" href="{{ route('app.buy') }}">سرویس جدید</a>
</div>

<div class="metric-row">
 <div class="metric-cell"><span>کیف پول</span><b>{{ number_format((float)($wallet->balance_cached??0)) }}</b><small class="muted"> تومان</small></div>
 <div class="metric-cell"><span>سرویس فعال</span><b>{{ $active }}</b></div>
 <div class="metric-cell"><span>سفارش اخیر</span><b>{{ $orders->count() }}</b></div>
 <div class="metric-cell"><span>وضعیت</span><b style="font-size:15px;color:var(--ok)"><i class="network-dot"></i> فعال</b></div>
</div>

<section class="section">
 <div class="top-title"><div><h2>سرویس‌ها</h2></div><a class="btn ghost" href="{{ route('app.services') }}">نمایش همه</a></div>
 @if($services->count())
 <div class="list-card">
  @foreach($services as $s)
   @php($limit=$s->traffic_limit_bytes??0) @php($used=$s->traffic_used_bytes??0) @php($pct=$limit?min(100,round($used/$limit*100)):0)
   <a class="list-row" href="{{ route('app.services.show',$s->id) }}">
    <div><strong>BluePing · {{ substr($s->id,0,6) }}</strong><div class="muted" style="font-size:11px;margin-top:4px">{{ $limit ? number_format($used/1073741824,1).' GB مصرف شده' : 'حجم نامحدود' }}</div></div>
    <div style="min-width:130px;text-align:left"><span class="pill {{ $s->status==='active'?'ok':'' }}">{{ $s->status==='active'?'فعال':$s->status }}</span><div class="progress" style="margin-top:8px"><i style="width:{{ $pct }}%"></i></div></div>
   </a>
  @endforeach
 </div>
 @else
 <div class="card empty"><h3>هنوز سرویسی نداری</h3><p class="muted">یک پلن انتخاب کن و شروع کن.</p><a class="btn primary" href="{{ route('app.buy') }}">خرید سرویس</a></div>
 @endif
</section>

<section class="section">
 <div class="feature-strip">
  <a class="feature" href="{{ route('app.setup') }}"><b>راهنمای اتصال</b><span class="muted">Android، iPhone، Windows، macOS</span></a>
  <a class="feature" href="{{ route('app.wallet') }}"><b>کیف پول</b><span class="muted">موجودی و تراکنش‌ها</span></a>
  <a class="feature" href="{{ route('app.tickets') }}"><b>پشتیبانی</b><span class="muted">ارسال و پیگیری تیکت</span></a>
 </div>
</section>
@endsection