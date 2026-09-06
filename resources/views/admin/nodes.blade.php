@extends('layouts.app')
@section('title','Node Manager — BlueGate')
@section('content')
<div class="admin-hero">
 <div><div class="admin-kicker">INFRASTRUCTURE</div><h2 style="margin:7px 0 6px">Node Manager</h2><div class="muted">Health، Sync Inbounds، Failover و وضعیت فروش هر نود.</div></div>
 <span class="pill">{{ $nodes->count() }} NODES</span>
</div>

<div class="card panel-card" style="margin-bottom:18px">
 <div class="panel-title"><div><h3>افزودن 3x-ui Node</h3><span class="muted">اطلاعات پنل و Public Host را وارد کن.</span></div><span class="pill">NEW NODE</span></div>
 <form method="post" action="{{ route('admin.nodes.store') }}">@csrf
 <div class="form-grid">
  <div class="field"><label>نام</label><input class="input" name="name" required></div>
  <div class="field"><label>Slug</label><input class="input" name="slug" required placeholder="de-01"></div>
  <div class="field"><label>Location</label><select class="input" name="location_id"><option value="">بدون لوکیشن</option>@foreach($locations as $l)<option value="{{ $l->id }}">{{ $l->flag }} {{ $l->name }}</option>@endforeach</select></div>
  <div class="field"><label>Panel URL</label><input class="input" name="panel_url" required placeholder="https://panel.example.com:2053"></div>
  <div class="field"><label>Public Host / IP</label><input class="input" name="public_host" placeholder="de1.example.com"></div>
  <div class="field"><label>Username</label><input class="input" name="username" required></div>
  <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
 </div>
 <button class="btn primary">ذخیره Node</button>
 </form>
</div>

@if($nodes->count())
<div class="node-grid">
@foreach($nodes as $n)
<div class="card node-card">
 <div class="node-top">
  <div><div class="admin-kicker">{{ strtoupper($n->slug) }}</div><h3 style="margin:5px 0">{{ $n->name }}</h3><span class="muted">{{ $n->location?->flag }} {{ $n->location?->name ?? 'No location' }}</span></div>
  <div class="node-status"><i class="status-dot {{ $n->status==='online'?'online':'offline' }}"></i>{{ strtoupper($n->status) }}</div>
 </div>
 <div class="node-meta">
  <div class="mini"><span class="muted">Failures</span><b>{{ $n->failure_count ?? 0 }}</b></div>
  <div class="mini"><span class="muted">Sales</span><b>{{ $n->sales_enabled?'ON':'OFF' }}</b></div>
  <div class="mini"><span class="muted">Weight</span><b>{{ $n->weight ?? 100 }}</b></div>
 </div>
 <div class="mini" style="margin-bottom:13px"><span class="muted">Panel</span><b style="font-size:12px;direction:ltr;text-align:left;overflow:hidden;text-overflow:ellipsis">{{ $n->panel_url }}</b></div>
 <div class="node-actions">
  <form method="post" action="{{ route('admin.nodes.health',$n) }}">@csrf<button class="btn">Health Check</button></form>
  <form method="post" action="{{ route('admin.nodes.sync',$n) }}">@csrf<button class="btn primary">Sync Inbounds</button></form>
  <form method="post" action="{{ route('admin.nodes.failover',$n) }}" onsubmit="return confirm('تمام سرویس‌های فعال این نود منتقل شوند؟')">@csrf<button class="btn danger">Failover</button></form>
 </div>
 <div class="muted" style="font-size:11px;margin-top:12px">Last heartbeat: {{ $n->last_heartbeat_at ?: '—' }}</div>
</div>
@endforeach
</div>
@else<div class="card empty"><h3>هنوز Node ثبت نشده</h3><p>اولین 3x-ui Node را از فرم بالا اضافه کن.</p></div>@endif
@endsection