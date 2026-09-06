@extends('layouts.app')
@section('title','سفارش‌ها — BlueGate')
@section('content')
<div class="top-title"><div><h2>سفارش‌ها</h2><div class="muted">پرداخت امن از کیف پول و تحویل خودکار</div></div></div>
<div class="card table-wrap">
@if($orders->count())
<table class="table"><tr><th>شماره</th><th>وضعیت</th><th>مبلغ</th><th>عملیات</th></tr>
@foreach($orders as $o)
<tr>
<td>{{ $o->order_number }}</td>
<td><span class="pill">{{ $o->status }}</span></td>
<td>{{ number_format((float)($o->status==='pending_payment' ? $o->payable : $o->subtotal)) }} تومان</td>
<td>
@if($o->status==='pending_payment')
<div style="display:flex;gap:8px;flex-wrap:wrap">
<form method="post" action="{{ route('app.orders.wallet',$o->id) }}">@csrf
<button class="btn" type="submit">پرداخت با کیف پول</button>
</form>
@if((float)$o->payable > 0)
<form method="post" action="{{ route('app.orders.zarinpal',$o->id) }}">@csrf
<button class="btn primary" type="submit">پرداخت آنلاین</button>
</form>
@endif
</div>
@elseif($o->status==='active')<span style="color:var(--ok)">تحویل شد</span>
@elseif($o->status==='provisioning_failed')<span style="color:var(--danger)">ساخت سرویس ناموفق</span>
@endif
</td></tr>
@endforeach</table>
<div style="padding:14px">{{ $orders->links() }}</div>
@else<div class="empty">سفارشی نداری.</div>@endif
</div>
@endsection
