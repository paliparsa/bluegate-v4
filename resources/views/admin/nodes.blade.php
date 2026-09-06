@extends('layouts.app')
@section('title','نودها — BlueGate')
@section('content')
<div class="top-title"><div><h2>Node Manager</h2><div class="muted">اتصال مستقیم 3x-ui، Health و Sync Inbound</div></div></div>
<div class="card" style="margin-bottom:18px"><h3>افزودن 3x-ui</h3>
<form method="post" action="{{ route('admin.nodes.store') }}">@csrf
<div class="grid">
<div class="field"><label>نام</label><input class="input" name="name" required></div>
<div class="field"><label>Slug</label><input class="input" name="slug" required placeholder="de-01"></div>
<div class="field"><label>Panel URL</label><input class="input" name="panel_url" required placeholder="https://panel.example.com:2053"></div>
<div class="field"><label>VPN Public Host/IP</label><input class="input" name="public_host" placeholder="de1.example.com"></div>
<div class="field"><label>Username</label><input class="input" name="username" required></div>
<div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
<div class="field"><label>Location</label><select class="input" name="location_id"><option value="">بدون لوکیشن</option>@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->flag }} {{ $l->name }}</option>@endforeach</select></div>
</div><button class="btn primary">ذخیره نود</button></form></div>
<div class="card table-wrap"><table class="table"><tr><th>نود</th><th>وضعیت</th><th>Failure</th><th>Panel</th><th>فروش</th><th>عملیات</th></tr>
@forelse($nodes as $n)<tr><td>{{ $n->name }}</td><td><span class="pill">{{ $n->status }}</span></td><td>{{ $n->failure_count ?? 0 }}</td><td>{{ $n->panel_url }}</td><td>{{ $n->sales_enabled?'فعال':'خاموش' }}</td><td style="display:flex;gap:7px;flex-wrap:wrap">
<form method="post" action="{{ route('admin.nodes.health',$n) }}">@csrf<button class="btn">Health</button></form>
<form method="post" action="{{ route('admin.nodes.sync',$n) }}">@csrf<button class="btn primary">Sync Inbounds</button></form>
<form method="post" action="{{ route('admin.nodes.failover',$n) }}" onsubmit="return confirm('تمام سرویس‌های فعال این نود منتقل شوند؟')">@csrf<button class="btn">Failover</button></form>
</td></tr>@empty<tr><td colspan="6">هنوز نودی ثبت نشده.</td></tr>@endforelse</table></div>
@endsection
