@extends('layouts.app')
@section('title','مدیریت — BlueGate')
@section('content')
<div class="top-title"><div><h2>مدیریت BlueGate</h2><div class="muted">کنترل اولیه پلتفرم</div></div></div><div class="stats"><div class="card stat"><span class="muted">کاربران</span><b>{{ $stats['users'] }}</b></div><div class="card stat"><span class="muted">سرویس فعال</span><b>{{ $stats['services'] }}</b></div><div class="card stat"><span class="muted">سفارش‌ها</span><b>{{ $stats['orders'] }}</b></div><div class="card stat"><span class="muted">نودها</span><b>{{ $stats['nodes'] }}</b></div></div><div class="section grid">
<a class="card" href="{{ route('admin.analytics') }}"><h3>Analytics</h3><p class="muted">فروش، API، Failover و سلامت زیرساخت</p></a>
<a class="card" href="{{ route('admin.resellers') }}"><h3>Resellers</h3><p class="muted">API، تخفیف و Credit Limit</p></a>
<a class="card" href="{{ route('admin.products') }}"><h3>محصولات و پلن‌ها</h3><p class="muted">مشاهده Catalog فعلی</p></a><a class="card" href="{{ route('admin.nodes') }}"><h3>Node Manager</h3><p class="muted">مشاهده نودهای 3x-ui</p></a><div class="card"><h3>ساخت Super Admin</h3><p class="muted"><code>php artisan bluegate:make-admin email@example.com</code></p></div></div>
@endsection

