@extends('layouts.app')
@section('title','دعوت دوستان — BlueGate')
@section('content')
<div class="top-title"><div><h2>دعوت دوستان</h2><div class="muted">از خریدهای واجد شرایط دوستانت اعتبار بگیر.</div></div></div>
<div class="grid">
 <div class="card"><h3>کد معرف</h3><div class="price">{{ $code }}</div>
 <div class="field"><input id="refurl" class="input" readonly value="{{ route('register',['ref'=>$code]) }}"></div>
 <button class="btn primary" onclick="navigator.clipboard.writeText(document.getElementById('refurl').value)">کپی لینک دعوت</button></div>
 <div class="card"><h3>آمار</h3><div class="price">{{ $stats['invited'] }}</div><p class="muted">کاربر دعوت‌شده</p><div class="price">{{ number_format((float)$stats['earned']) }} تومان</div><p class="muted">اعتبار کسب‌شده</p></div>
</div>
<div class="card section"><h3>اتصال Telegram</h3>
@if(auth()->user()->telegram_id)<p class="muted">Telegram متصل است ✓</p>
@else<form method="post" action="{{ route('app.telegram.link') }}">@csrf<button class="btn">ساخت لینک اتصال Telegram</button></form>
@if(session('telegram_link'))<p><a class="btn primary" target="_blank" href="{{ session('telegram_link') }}">باز کردن Bot و اتصال</a></p>@endif
@endif</div>
@endsection