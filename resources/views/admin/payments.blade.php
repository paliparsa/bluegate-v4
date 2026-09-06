@extends('layouts.app')
@section('title','پرداخت‌ها — BlueGate')
@section('content')
<div class="top-title"><div><h2>Payment Center</h2><div class="muted">تراکنش‌های آنلاین و وضعیت Verify</div></div></div>
<div class="card table-wrap">
<table class="table"><tr><th>کاربر</th><th>درگاه</th><th>مبلغ</th><th>وضعیت</th><th>Authority</th><th>Ref ID</th><th>زمان</th></tr>
@forelse($payments as $p)
<tr><td>{{ $p->email }}</td><td>{{ $p->gateway }}</td><td>{{ number_format((float)$p->amount) }}</td>
<td><span class="pill">{{ $p->status }}</span></td><td>{{ $p->authority ?: '—' }}</td><td>{{ $p->transaction_id ?: '—' }}</td><td>{{ $p->created_at }}</td></tr>
@empty<tr><td colspan="7">تراکنشی ثبت نشده.</td></tr>@endforelse
</table><div style="padding:14px">{{ $payments->links() }}</div></div>
@endsection
