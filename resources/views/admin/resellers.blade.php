@extends('layouts.app')
@section('title','Resellers — BlueGate Admin')
@section('content')
<div class="top-title"><div><h2>Reseller Manager</h2><div class="muted">تخفیف، API access و اعتبار ریسلرها</div></div></div>
<div class="card" style="margin-bottom:18px"><form method="post" action="{{ route('admin.resellers.store') }}">@csrf
<div class="grid"><div class="field"><label>Email</label><input class="input" type="email" name="email" required></div>
<div class="field"><label>Discount %</label><input class="input" type="number" step="0.01" name="discount_percent" value="10" required></div>
<div class="field"><label>Credit limit</label><input class="input" type="number" name="credit_limit" value="0"></div></div>
<label><input type="checkbox" name="api_enabled" value="1" checked> API</label>
<label><input type="checkbox" name="active" value="1" checked> Active</label>
<button class="btn primary">فعال‌سازی Reseller</button></form></div>

@foreach($resellers as $r)
<div class="card" style="margin-bottom:12px"><form method="post" action="{{ route('admin.resellers.update',$r->id) }}">@csrf @method('PUT')
<strong>{{ $r->name }} — {{ $r->email }}</strong>
<div class="grid"><div class="field"><label>Discount %</label><input class="input" type="number" step="0.01" name="discount_percent" value="{{ $r->discount_percent }}"></div>
<div class="field"><label>Credit limit</label><input class="input" type="number" name="credit_limit" value="{{ $r->credit_limit }}"></div></div>
<label><input type="checkbox" name="api_enabled" value="1" @checked($r->api_enabled)> API</label>
<label><input type="checkbox" name="active" value="1" @checked($r->active)> Active</label>
<button class="btn">ذخیره</button></form></div>
@endforeach
{{ $resellers->links() }}
@endsection