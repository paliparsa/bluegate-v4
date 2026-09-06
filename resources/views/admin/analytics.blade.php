@extends('layouts.app')
@section('title','Analytics — BlueGate Admin')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">BUSINESS + INFRASTRUCTURE</div><h2 style="margin:7px 0 6px">Analytics</h2><div class="muted">نمای ۳۰ روز اخیر فروش، API و سلامت شبکه.</div></div><span class="pill ok">LIVE DATA</span></div>
<div class="kpi-grid">
 <div class="card kpi"><span class="label">Revenue 30d</span><span class="value">{{ number_format($summary['revenue30']) }}</span><span class="hint">IRR / catalog unit</span></div>
 <div class="card kpi"><span class="label">Orders 30d</span><span class="value">{{ $summary['orders30'] }}</span><span class="hint">Created orders</span></div>
 <div class="card kpi"><span class="label">Active Services</span><span class="value">{{ $summary['active_services'] }}</span><span class="hint">Currently active</span></div>
 <div class="card kpi"><span class="label">Online Nodes</span><span class="value">{{ $summary['active_nodes'] }}</span><span class="hint">Healthy infrastructure</span></div>
 <div class="card kpi"><span class="label">Resellers</span><span class="value">{{ $summary['resellers'] }}</span><span class="hint">Active profiles</span></div>
 <div class="card kpi"><span class="label">API Calls 24h</span><span class="value">{{ $summary['api_calls24'] }}</span><span class="hint">Reseller API traffic</span></div>
 <div class="card kpi"><span class="label">Failovers 30d</span><span class="value">{{ $summary['failovers30'] }}</span><span class="hint">Completed migrations</span></div>
</div>
<div class="panel-grid section">
 <div class="card panel-card"><div class="panel-title"><h3>Revenue trend</h3><span class="pill">30 DAYS</span></div>
  @php($maxRev=max(1,(float)$series->max('revenue')))
  <div class="chart-bars">@foreach($series as $d)<div class="bar-col" title="{{ $d['date'] }} · {{ number_format($d['revenue']) }}"><div class="bar" style="height:{{ max(3,round(($d['revenue']/$maxRev)*145)) }}px"></div><span class="bar-label">{{ substr($d['date'],5) }}</span></div>@endforeach</div>
 </div>
 <div class="card panel-card"><div class="panel-title"><h3>Infrastructure health</h3><a class="btn ghost" href="{{ route('admin.nodes') }}">Node Manager</a></div>
  @forelse($nodes as $n)<div class="metric"><span><i class="status-dot {{ $n->status==='online'?'online':'offline' }}"></i> {{ $n->name }}</span><span class="muted">{{ $n->location?:'—' }} · Fail {{ $n->failure_count }}</span></div>@empty<div class="empty">Node data unavailable</div>@endforelse
 </div>
</div>
<div class="card panel-card table-wrap"><div class="panel-title"><h3>Daily performance</h3><span class="pill">ORDERS / REVENUE</span></div>
<table class="table"><tr><th>تاریخ</th><th>سفارش</th><th>Revenue</th></tr>@foreach($series as $d)<tr><td>{{ $d['date'] }}</td><td>{{ $d['orders'] }}</td><td>{{ number_format($d['revenue']) }}</td></tr>@endforeach</table></div>
@endsection