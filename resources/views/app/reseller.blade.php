@extends('layouts.app')
@section('title','Reseller API — BlueGate')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">DEVELOPER PLATFORM</div><h2 style="margin:7px 0 6px">Reseller Center</h2><div class="muted">API فروش، موجودی و کلیدهای دسترسی.</div></div><span class="pill {{ $profile->api_enabled?'ok':'' }}">{{ $profile->api_enabled?'API ONLINE':'API OFF' }}</span></div>
@if(session('api_key_plain'))<div class="alert success"><strong>API Key — فقط همین یک‌بار نمایش داده می‌شود:</strong><div class="codebox" style="margin-top:10px">{{ session('api_key_plain') }}</div></div>@endif
<div class="dev-hero">
 <div class="card dev-card"><div class="admin-kicker">ACCOUNT</div><h3 style="font-size:22px">Reseller Overview</h3>
  <div class="kpi-grid" style="grid-template-columns:repeat(3,1fr)">
   <div class="mini"><span class="muted">Discount</span><b>{{ number_format((float)$profile->discount_percent,1) }}%</b></div>
   <div class="mini"><span class="muted">Balance</span><b>{{ number_format((float)($wallet->balance_cached??0)) }}</b></div>
   <div class="mini"><span class="muted">Credit Limit</span><b>{{ number_format((float)$profile->credit_limit) }}</b></div>
  </div>
  <div class="feature-strip"><div class="feature"><b>Catalog API</b><span class="muted">قیمت‌های Reseller را مستقیم دریافت کن.</span></div><div class="feature"><b>Idempotent Orders</b><span class="muted">خرید API بدون Duplicate charge.</span></div><div class="feature"><b>Service Delivery</b><span class="muted">Provisioning خودکار بعد از سفارش.</span></div></div>
 </div>
 <div class="card dev-card"><div class="admin-kicker">API BASE</div><h3>Quick start</h3><div class="codebox">Authorization: Bearer bg_live_xxxxx
Idempotency-Key: unique-order-key

GET  {{ url('/api/v1/reseller/catalog') }}
GET  {{ url('/api/v1/reseller/balance') }}
POST {{ url('/api/v1/reseller/orders') }}</div></div>
</div>
<div class="panel-grid section">
 <div class="card panel-card"><div class="panel-title"><h3>ساخت API Key</h3><span class="pill">SCOPED ACCESS</span></div>
 <form method="post" action="{{ route('app.reseller.keys.create') }}">@csrf<div class="field"><label>نام کلید</label><input class="input" name="name" placeholder="Website / Bot / Panel" required></div>
 <div class="ability-list">@foreach(['catalog.read','balance.read','orders.read','orders.write','services.read'] as $ability)<label class="ability"><input type="checkbox" name="abilities[]" value="{{ $ability }}" checked> {{ $ability }}</label>@endforeach</div>
 <button class="btn primary" style="margin-top:14px">ساخت API Key</button></form></div>
 <div class="card panel-card"><div class="panel-title"><h3>API Security</h3><span class="pill ok">HASHED</span></div><p class="muted" style="line-height:1.9">کلید Plain فقط هنگام ساخت نمایش داده می‌شود. در دیتابیس فقط Hash نگهداری می‌شود و هر کلید Scope جداگانه دارد.</p></div>
</div>
<div class="card panel-card table-wrap"><div class="panel-title"><h3>API Keys</h3><span class="pill">{{ $keys->count() }} KEYS</span></div>
<table class="table"><tr><th>نام</th><th>Prefix</th><th>Abilities</th><th>آخرین استفاده</th><th>وضعیت</th><th></th></tr>
@foreach($keys as $k)<tr><td>{{ $k->name }}</td><td><code>{{ $k->token_prefix }}...</code></td><td>@foreach((array)json_decode($k->abilities??'[]',true) as $a)<span class="ability">{{ $a }}</span>@endforeach</td><td>{{ $k->last_used_at?:'—' }}</td><td><span class="pill {{ !$k->revoked_at?'ok':'' }}">{{ $k->revoked_at?'REVOKED':'ACTIVE' }}</span></td><td>@if(!$k->revoked_at)<form method="post" action="{{ route('app.reseller.keys.revoke',$k->id) }}">@csrf<button class="btn danger">Revoke</button></form>@endif</td></tr>@endforeach
</table></div>
@endsection