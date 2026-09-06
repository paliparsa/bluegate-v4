@extends('layouts.app')
@section('title','کیف پول‌ها — BlueGate')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">FINANCE</div><h2 style="margin:7px 0 6px">Wallet Operations</h2><div class="muted">BlueGate Admin Operations</div></div><span class="pill">ADMIN</span></div>
<div class="card"><form method="post" action="{{ route('admin.wallets.credit') }}">@csrf
<div class="grid"><div class="field"><label>ایمیل کاربر</label><input class="input" type="email" name="email" required></div>
<div class="field"><label>مبلغ (تومان/IRR تنظیم فعلی)</label><input class="input" type="number" min="1" name="amount" required></div></div>
<button class="btn primary">افزایش موجودی</button></form></div>
@endsection
