@extends('layouts.app')
@section('title','Reseller API — BlueGate')
@section('content')
<div class="top-title"><div><h2>Reseller Center</h2><div class="muted">API فروش و مدیریت کلیدها</div></div></div>

@if(session('api_key_plain'))
<div class="alert success"><strong>API Key — فقط همین یک‌بار:</strong><br><code style="word-break:break-all">{{ session('api_key_plain') }}</code></div>
@endif

<div class="stats">
 <div class="card stat"><span class="muted">تخفیف ریسلر</span><b>{{ number_format((float)$profile->discount_percent,1) }}%</b></div>
 <div class="card stat"><span class="muted">موجودی</span><b>{{ number_format((float)($wallet->balance_cached??0)) }}</b></div>
 <div class="card stat"><span class="muted">API</span><b>{{ $profile->api_enabled?'ON':'OFF' }}</b></div>
</div>

<div class="card section"><h3>ساخت API Key</h3>
<form method="post" action="{{ route('app.reseller.keys.create') }}">@csrf
<div class="field"><label>نام کلید</label><input class="input" name="name" placeholder="Website / Bot / Panel" required></div>
<div style="display:flex;gap:12px;flex-wrap:wrap">
@foreach(['catalog.read','balance.read','orders.read','orders.write','services.read'] as $ability)
<label><input type="checkbox" name="abilities[]" value="{{ $ability }}" checked> {{ $ability }}</label>
@endforeach
</div><button class="btn primary">ساخت کلید</button></form></div>

<div class="card table-wrap"><table class="table"><tr><th>نام</th><th>Prefix</th><th>آخرین استفاده</th><th>وضعیت</th><th></th></tr>
@foreach($keys as $k)<tr><td>{{ $k->name }}</td><td><code>{{ $k->token_prefix }}...</code></td><td>{{ $k->last_used_at?:'—' }}</td><td>{{ $k->revoked_at?'Revoked':'Active' }}</td>
<td>@if(!$k->revoked_at)<form method="post" action="{{ route('app.reseller.keys.revoke',$k->id) }}">@csrf<button class="btn">باطل کردن</button></form>@endif</td></tr>@endforeach
</table></div>

<div class="card section"><h3>API Base</h3><code>{{ url('/api/v1/reseller') }}</code>
<p class="muted">Authorization: Bearer bg_live_... · برای POST /orders هدر Idempotency-Key الزامی است.</p></div>
@endsection