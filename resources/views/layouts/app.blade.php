@extends('layouts.base')
@section('body')
<main class="container shell">
<aside class="card sidebar">
 <div class="user-chip"><strong>{{ auth()->user()->name }}</strong><span class="muted">{{ auth()->user()->email }}</span></div>
 <nav class="side">
  <div class="side-section">حساب</div>
  <a class="{{ request()->routeIs('app.dashboard')?'active':'' }}" href="{{ route('app.dashboard') }}">خانه</a>
  <a class="{{ request()->routeIs('app.services*')?'active':'' }}" href="{{ route('app.services') }}">سرویس‌های من</a>
  <a class="{{ request()->routeIs('app.buy*')?'active':'' }}" href="{{ route('app.buy') }}">خرید سرویس</a>
  <a class="{{ request()->routeIs('app.setup')?'active':'' }}" href="{{ route('app.setup') }}">راهنمای اتصال</a>
  <a class="{{ request()->routeIs('app.wallet*')?'active':'' }}" href="{{ route('app.wallet') }}">کیف پول</a>
  <a class="{{ request()->routeIs('app.orders*')?'active':'' }}" href="{{ route('app.orders') }}">سفارش‌ها</a>
  <div class="side-section">ارتباط</div>
  <a class="{{ request()->routeIs('app.tickets*')?'active':'' }}" href="{{ route('app.tickets') }}">پشتیبانی</a>
  <a class="{{ request()->routeIs('app.notifications*')?'active':'' }}" href="{{ route('app.notifications') }}">اعلان‌ها</a>
  <a class="{{ request()->routeIs('app.referral*')?'active':'' }}" href="{{ route('app.referral') }}">دعوت دوستان</a>
  @if(auth()->user()->role === 'reseller')<a class="{{ request()->routeIs('app.reseller*')?'active':'' }}" href="{{ route('app.reseller') }}">Reseller API</a>@endif
  @if(in_array(auth()->user()->role,['admin','super_admin'],true))
   <div class="side-section">مدیریت</div>
   <a href="{{ route('admin.dashboard') }}">داشبورد مدیریت</a>
   <a href="{{ route('admin.nodes') }}">نودها</a>
   <a href="{{ route('admin.products') }}">محصولات</a>
   <a href="{{ route('admin.payments') }}">پرداخت‌ها</a>
   <a href="{{ route('admin.wallets') }}">کیف پول‌ها</a>
   <a href="{{ route('admin.coupons') }}">تخفیف‌ها</a>
   <a href="{{ route('admin.tickets') }}">تیکت‌ها</a>
   <a href="{{ route('admin.resellers') }}">Resellers</a>
   <a href="{{ route('admin.analytics') }}">Analytics</a>
  @endif
 </nav>
</aside>
<section class="content">
 @if(session('success'))<div class="alert success" style="margin-top:18px">{{ session('success') }}</div>@endif
 @if(session('error'))<div class="alert" style="margin-top:18px">{{ session('error') }}</div>@endif
 @yield('content')
</section>
</main>
<nav class="mobile-bar">
<a class="{{ request()->routeIs('app.dashboard')?'active':'' }}" href="{{ route('app.dashboard') }}">خانه</a>
<a class="{{ request()->routeIs('app.services*')?'active':'' }}" href="{{ route('app.services') }}">سرویس‌ها</a>
<a class="{{ request()->routeIs('app.buy*')?'active':'' }}" href="{{ route('app.buy') }}">خرید</a>
<a class="{{ request()->routeIs('app.setup')?'active':'' }}" href="{{ route('app.setup') }}">اتصال</a>
<a class="{{ request()->routeIs('app.tickets*')?'active':'' }}" href="{{ route('app.tickets') }}">پشتیبانی</a>
</nav>
@endsection