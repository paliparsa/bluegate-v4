@extends('layouts.base')

@section('body')
<main class="container shell">
    <aside class="card sidebar">
        <div class="muted" style="padding:8px 12px 14px">
            {{ auth()->user()->name }}
        </div>

        <nav class="side">
            <a href="{{ route('app.dashboard') }}">خانه</a>
            <a href="{{ route('app.services') }}">سرویس‌های من</a>
            <a href="{{ route('app.buy') }}">خرید سرویس</a>
            <a href="{{ route('app.wallet') }}">کیف پول</a>
            <a href="{{ route('app.orders') }}">سفارش‌ها</a>

            @if(in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                <a href="{{ route('admin.dashboard') }}">پنل مدیریت</a>
                <a href="{{ route('admin.nodes') }}">مدیریت نودها</a>
                <a href="{{ route('admin.wallets') }}">عملیات کیف پول</a>
            @endif
        </nav>
    </aside>

    <section class="content">
        @if(session('success'))
            <div class="alert success" style="margin-top:20px">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert" style="margin-top:20px">{{ session('error') }}</div>
        @endif

        @yield('content')
    </section>
</main>
@endsection
