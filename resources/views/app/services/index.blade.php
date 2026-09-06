@extends('layouts.app')
@section('title','سرویس‌های من — BlueGate')
@section('content')
<div class="top-title"><div><h2>سرویس‌های من</h2><div class="muted">اتصال‌ها، مصرف و وضعیت هر سرویس</div></div><a class="btn primary" href="{{ route('app.buy') }}">+ خرید سرویس</a></div>
@if($services->count())<div class="grid">@foreach($services as $s)
@php($limit=$s->traffic_limit_bytes??0) @php($used=$s->traffic_used_bytes??0) @php($pct=$limit?min(100,round($used/$limit*100)):0)
<a class="card service-card" href="{{ route('app.services.show',$s->id) }}">
<div class="service-head"><div><span class="muted" style="font-size:11px">BLUEPING SERVICE</span><h3 style="margin-top:5px">{{ substr($s->id,0,8) }}</h3></div><span class="pill {{ $s->status==='active'?'ok':'' }}">{{ strtoupper($s->status) }}</span></div>
<div class="service-meta"><div class="mini"><span class="muted">مصرف</span><b>{{ number_format($used/1073741824,1) }} GB</b></div><div class="mini"><span class="muted">انقضا</span><b style="font-size:12px">{{ $s->expires_at??'نامحدود' }}</b></div></div>
<div class="progress"><i style="width:{{ $pct }}%"></i></div><span class="muted" style="font-size:11px">برای مدیریت سرویس کلیک کن ←</span></a>
@endforeach</div>@else<div class="card empty"><h3>لیست سرویس‌ها خالیه</h3><p>بعد از اولین خرید، سرویس اینجا نمایش داده می‌شود.</p><a class="btn primary" href="{{ route('app.buy') }}">خرید اولین سرویس</a></div>@endif
@endsection