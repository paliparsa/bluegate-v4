@extends('layouts.app')
@section('title','Payment Center — BlueGate')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">FINANCE</div><h2 style="margin:7px 0 6px">Payment Center</h2><div class="muted">تراکنش‌ها، Verify و شناسه‌های درگاه.</div></div><span class="pill">{{ $payments->total() }} PAYMENTS</span></div>
<div class="card panel-card table-wrap">
 <div class="panel-title"><h3>تراکنش‌های اخیر</h3><span class="pill">ZARINPAL / WALLET</span></div>
 <table class="table"><tr><th>کاربر</th><th>درگاه</th><th>مبلغ</th><th>وضعیت</th><th>Authority</th><th>Ref ID</th><th>زمان</th></tr>
 @forelse($payments as $p)<tr>
  <td>{{ $p->email }}</td><td>{{ strtoupper($p->gateway) }}</td><td><strong>{{ number_format((float)$p->amount) }}</strong></td>
  <td><span class="pill {{ in_array($p->status,['verified','paid'],true)?'ok':'' }}">{{ $p->status }}</span></td>
  <td><code>{{ $p->authority ?: '—' }}</code></td><td><code>{{ $p->transaction_id ?: '—' }}</code></td><td>{{ $p->created_at }}</td>
 </tr>@empty<tr><td colspan="7"><div class="empty">تراکنشی ثبت نشده.</div></td></tr>@endforelse
 </table><div style="padding-top:14px">{{ $payments->links() }}</div>
</div>
@endsection