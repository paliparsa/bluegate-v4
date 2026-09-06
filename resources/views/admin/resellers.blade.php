@extends('layouts.app')
@section('title','Reseller Manager — BlueGate')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">B2B CHANNEL</div><h2 style="margin:7px 0 6px">Reseller Manager</h2><div class="muted">تخفیف، Credit Limit و دسترسی API فروشندگان.</div></div><span class="pill">{{ $resellers->total() }} RESELLERS</span></div>
<div class="card panel-card" style="margin-bottom:18px">
 <div class="panel-title"><div><h3>فعال‌سازی Reseller</h3><span class="muted">کاربر موجود را به فروشنده تبدیل کن.</span></div><span class="pill">NEW</span></div>
 <form method="post" action="{{ route('admin.resellers.store') }}">@csrf
  <div class="form-grid"><div class="field"><label>Email</label><input class="input" type="email" name="email" required></div><div class="field"><label>Discount %</label><input class="input" type="number" step="0.01" name="discount_percent" value="10" required></div><div class="field"><label>Credit Limit</label><input class="input" type="number" name="credit_limit" value="0"></div></div>
  <div class="switch-row"><label><input type="checkbox" name="api_enabled" value="1" checked> API Enabled</label><label><input type="checkbox" name="active" value="1" checked> Active</label></div>
  <button class="btn primary">فعال‌سازی Reseller</button>
 </form>
</div>
<div class="grid">
@foreach($resellers as $r)
<div class="card service-card"><div class="service-head"><div><span class="admin-kicker">RESELLER #{{ $r->id }}</span><h3 style="margin-top:5px">{{ $r->name }}</h3><span class="muted">{{ $r->email }}</span></div><span class="pill {{ $r->active?'ok':'' }}">{{ $r->active?'ACTIVE':'DISABLED' }}</span></div>
<form method="post" action="{{ route('admin.resellers.update',$r->id) }}">@csrf @method('PUT')
<div class="service-meta"><div class="field"><label>Discount %</label><input class="input" type="number" step="0.01" name="discount_percent" value="{{ $r->discount_percent }}"></div><div class="field"><label>Credit Limit</label><input class="input" type="number" name="credit_limit" value="{{ $r->credit_limit }}"></div></div>
<div class="switch-row"><label><input type="checkbox" name="api_enabled" value="1" @checked($r->api_enabled)> API</label><label><input type="checkbox" name="active" value="1" @checked($r->active)> Active</label></div>
<button class="btn primary">ذخیره تنظیمات</button>
</form></div>
@endforeach
</div><div style="margin-top:16px">{{ $resellers->links() }}</div>
@endsection