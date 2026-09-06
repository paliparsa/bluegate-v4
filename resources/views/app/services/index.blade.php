@extends('layouts.app')
@section('title','سرویس‌های من — BlueGate')
@section('content')
<div class="top-title">
 <div><h2>سرویس‌های من</h2><div class="muted">مصرف، انقضا و مدیریت اتصال</div></div>
 <a class="btn primary" href="{{ route('app.buy') }}">خرید سرویس</a>
</div>

@if($services->count())
<div class="list-card">
 @foreach($services as $s)
  @php($limit=$s->traffic_limit_bytes??0) @php($used=$s->traffic_used_bytes??0) @php($pct=$limit?min(100,round($used/$limit*100)):0)
  <a class="list-row" href="{{ route('app.services.show',$s->id) }}">
   <div style="min-width:0">
    <div style="display:flex;align-items:center;gap:8px"><strong>BluePing · {{ substr($s->id,0,8) }}</strong><span class="pill {{ $s->status==='active'?'ok':'' }}">{{ $s->status==='active'?'فعال':$s->status }}</span></div>
    <div class="muted" style="font-size:11px;margin-top:6px">{{ $s->expires_at ? 'انقضا: '.$s->expires_at : 'بدون تاریخ انقضا' }}</div>
   </div>
   <div style="width:min(220px,40%)">
    <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:6px"><span class="muted">مصرف</span><strong>{{ number_format($used/1073741824,1) }} GB</strong></div>
    <div class="progress"><i style="width:{{ $pct }}%"></i></div>
   </div>
  </a>
 @endforeach
</div>
@else
<div class="card empty"><h3>هنوز سرویسی نداری</h3><p class="muted">اولین سرویس BluePing رو بساز.</p><a class="btn primary" href="{{ route('app.buy') }}">خرید سرویس</a></div>
@endif
@endsection