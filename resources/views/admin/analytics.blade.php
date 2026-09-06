@extends('layouts.app')
@section('title','Analytics — BlueGate Admin')
@section('content')
<div class="top-title"><div><h2>Analytics</h2><div class="muted">نمای ۳۰ روز اخیر فروش، API و زیرساخت</div></div></div>
<div class="stats">
<div class="card stat"><span class="muted">Revenue 30d</span><b>{{ number_format($summary['revenue30']) }}</b></div>
<div class="card stat"><span class="muted">Orders 30d</span><b>{{ $summary['orders30'] }}</b></div>
<div class="card stat"><span class="muted">Active Services</span><b>{{ $summary['active_services'] }}</b></div>
<div class="card stat"><span class="muted">Online Nodes</span><b>{{ $summary['active_nodes'] }}</b></div>
<div class="card stat"><span class="muted">Resellers</span><b>{{ $summary['resellers'] }}</b></div>
<div class="card stat"><span class="muted">API Calls 24h</span><b>{{ $summary['api_calls24'] }}</b></div>
<div class="card stat"><span class="muted">Failovers 30d</span><b>{{ $summary['failovers30'] }}</b></div>
</div>
<div class="card section table-wrap"><h3>Daily</h3><table class="table"><tr><th>تاریخ</th><th>سفارش</th><th>Revenue</th></tr>
@foreach($series as $d)<tr><td>{{ $d['date'] }}</td><td>{{ $d['orders'] }}</td><td>{{ number_format($d['revenue']) }}</td></tr>@endforeach</table></div>
<div class="card section table-wrap"><h3>Node Health</h3><table class="table"><tr><th>Node</th><th>Location</th><th>Status</th><th>Failures</th><th>Heartbeat</th></tr>
@foreach($nodes as $n)<tr><td>{{ $n->name }}</td><td>{{ $n->location?:'—' }}</td><td>{{ $n->status }}</td><td>{{ $n->failure_count }}</td><td>{{ $n->last_heartbeat_at }}</td></tr>@endforeach</table></div>
@endsection