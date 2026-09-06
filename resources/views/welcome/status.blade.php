@extends('layouts.base')
@section('title','وضعیت شبکه — BlueGate')
@section('body')<main class="container section"><h2>وضعیت زیرساخت</h2><p class="section-lead">نمای عمومی وضعیت BlueGate؛ Health Check دقیق API از مسیر <code>/api/v1/health</code> در دسترس است.</p><div class="grid"><div class="card"><h3>Website</h3><p style="color:var(--ok)">● Operational</p></div><div class="card"><h3>API</h3><p style="color:var(--ok)">● Operational</p></div><div class="card"><h3>Subscription</h3><p style="color:var(--ok)">● Operational</p></div></div></main>@endsection
