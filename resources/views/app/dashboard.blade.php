@extends('layouts.app')
@section('title','داشبورد — BlueGate')
@section('content')
<div class="top-title"><div><h2>سلام، {{ explode(' ',auth()->user()->name)[0] }} 👋</h2><div class="muted">همه‌چیز برای مدیریت اتصال‌هایت اینجاست.</div></div><a class="btn primary" href="{{ route('app.buy') }}">+ سرویس جدید</a></div>
<div class="stats">
 <div class="card stat"><span class="muted">موجودی کیف پول</span><b>{{ number_format((float)($wallet->balance_cached??0)) }}</b><small>تومان</small></div>
 <div class="card stat"><span class="muted">سرویس فعال</span><b>{{ $active }}</b><small>اتصال فعال</small></div>
 <div class="card stat"><span class="muted">سفارش‌های اخیر</span><b>{{ $orders->count() }}</b><small>آخرین فعالیت‌ها</small></div>
 <div class="card stat"><span class="muted">وضعیت شبکه</span><b style="font-size:19px;color:var(--ok)"><i class="network-dot"></i>Operational</b><small>BlueGate Core</small></div>
</div>
<div class="section">
 <div class="top-title"><div><h2>سرویس‌های اخیر</h2><div class="muted">وضعیت و مصرف را سریع ببین.</div></div><a class="btn ghost" href="{{ route('app.services') }}">همه سرویس‌ها</a></div>
 @if($services->count())<div class="grid">@foreach($services as $s)
 @php($limit=$s->traffic_limit_bytes??0) @php($used=$s->traffic_used_bytes??0) @php($pct=$limit?min(100,round($used/$limit*100)):0)
 <a class="card service-card" href="{{ route('app.services.show',$s->id) }}"><div class="service-head"><strong>BluePing · {{ substr($s->id,0,6) }}</strong><span class="pill {{ $s->status==='active'?'ok':'' }}">{{ $s->status }}</span></div>
 <div class="service-meta"><div class="mini"><span class="muted">مصرف</span><b>{{ number_format($used/1073741824,1) }} GB</b></div><div class="mini"><span class="muted">انقضا</span><b style="font-size:12px">{{ $s->expires_at??'نامحدود' }}</b></div></div>
 <div><div class="progress"><i style="width:{{ $pct }}%"></i></div><div class="muted" style="font-size:11px;margin-top:7px">{{ $limit?$pct.'٪ مصرف شده':'بدون محدودیت حجم' }}</div></div></a>
 @endforeach</div>@else<div class="card empty"><h3>هنوز سرویسی نداری</h3><p>اولین اتصال BluePing را بساز.</p><a class="btn primary" href="{{ route('app.buy') }}">انتخاب پلن</a></div>@endif
</div>
<div class="quick-grid section"><div class="card"><h3>دسترسی سریع</h3><div class="feature-strip"><a class="feature" href="{{ route('app.wallet') }}"><b>کیف پول</b><span class="muted">موجودی و تراکنش‌ها</span></a><a class="feature" href="{{ route('app.tickets') }}"><b>پشتیبانی</b><span class="muted">ارسال و پیگیری تیکت</span></a><a class="feature" href="{{ route('app.referral') }}"><b>دعوت دوستان</b><span class="muted">اعتبار معرفی دریافت کن</span></a></div></div>
<div class="card"><span class="pill ok">BLUEGATE STATUS</span><h3 style="margin-top:15px">شبکه آماده است</h3><p class="muted" style="line-height:1.8">Provisioning و Subscription Gateway در دسترس‌اند.</p><a class="btn ghost" href="{{ route('status') }}">مشاهده وضعیت</a></div></div>
@endsection