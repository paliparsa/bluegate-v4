@extends('layouts.app')
@section('title','مدیریت — BlueGate')
@section('content')
<div class="admin-hero">
 <div><div class="admin-kicker">BLUEGATE CONTROL CENTER</div><h2 style="margin:7px 0 6px">مرکز مدیریت</h2><div class="muted">نمای سریع فروش، کاربران و وضعیت زیرساخت.</div></div>
 <div class="admin-actions"><a class="btn primary" href="{{ route('admin.nodes') }}">Node Manager</a><a class="btn" href="{{ route('admin.analytics') }}">Analytics</a></div>
</div>
<div class="kpi-grid">
 <div class="card kpi"><span class="label">کاربران</span><span class="value">{{ $stats['users'] }}</span><span class="hint">Registered accounts</span></div>
 <div class="card kpi"><span class="label">سرویس فعال</span><span class="value">{{ $stats['services'] }}</span><span class="hint">Provisioned & active</span></div>
 <div class="card kpi"><span class="label">سفارش‌ها</span><span class="value">{{ $stats['orders'] }}</span><span class="hint">All orders</span></div>
 <div class="card kpi"><span class="label">نودها</span><span class="value">{{ $stats['nodes'] }}</span><span class="hint">Infrastructure nodes</span></div>
</div>
<div class="panel-grid section">
 <div class="card panel-card"><div class="panel-title"><h3>عملیات سریع</h3><span class="pill">ADMIN</span></div>
  <div class="feature-strip">
   <a class="feature" href="{{ route('admin.products') }}"><b>Catalog</b><span class="muted">محصولات، پلن‌ها و Trial</span></a>
   <a class="feature" href="{{ route('admin.resellers') }}"><b>Resellers</b><span class="muted">API و Credit Limit</span></a>
   <a class="feature" href="{{ route('admin.tickets') }}"><b>Support Desk</b><span class="muted">تیکت‌های باز کاربران</span></a>
  </div>
  <div class="feature-strip">
   <a class="feature" href="{{ route('admin.payments') }}"><b>Payments</b><span class="muted">Verify و تراکنش‌ها</span></a>
   <a class="feature" href="{{ route('admin.wallets') }}"><b>Wallet Ops</b><span class="muted">شارژ دستی کاربران</span></a>
   <a class="feature" href="{{ route('admin.coupons') }}"><b>Coupons</b><span class="muted">کمپین و تخفیف</span></a>
  </div>
 </div>
 <div class="card panel-card"><div class="panel-title"><h3>System Access</h3><span class="pill ok">READY</span></div>
  <p class="muted">ساخت Super Admin از CLI:</p><div class="codebox">php artisan bluegate:make-admin email@example.com</div>
  <p class="muted" style="margin-top:14px">برای سلامت زیرساخت و Failover از Node Manager استفاده کن.</p><a class="btn ghost" href="{{ route('admin.nodes') }}">باز کردن Node Manager</a>
 </div>
</div>
@endsection