@extends('layouts.app')
@section('title','کدهای تخفیف — BlueGate Admin')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">GROWTH</div><h2 style="margin:7px 0 6px">Coupon Center</h2><div class="muted">BlueGate Admin Operations</div></div><span class="pill">ADMIN</span></div>
<div class="card" style="margin-bottom:18px"><form method="post" action="{{ route('admin.coupons.store') }}">@csrf
<div class="grid">
<div class="field"><label>Code</label><input class="input" name="code" required></div>
<div class="field"><label>نام</label><input class="input" name="name" required></div>
<div class="field"><label>نوع</label><select class="input" name="type"><option value="percent">درصد</option><option value="fixed">مبلغ ثابت</option></select></div>
<div class="field"><label>مقدار</label><input class="input" type="number" step="0.01" name="value" required></div>
<div class="field"><label>سقف تخفیف</label><input class="input" type="number" name="max_discount"></div>
<div class="field"><label>حداقل سفارش</label><input class="input" type="number" name="min_order"></div>
<div class="field"><label>کل استفاده</label><input class="input" type="number" name="max_uses"></div>
<div class="field"><label>هر کاربر</label><input class="input" type="number" name="max_uses_per_user" value="1" required></div>
<div class="field"><label>شروع</label><input class="input" type="datetime-local" name="starts_at"></div>
<div class="field"><label>پایان</label><input class="input" type="datetime-local" name="ends_at"></div>
</div><label><input type="checkbox" name="active" value="1" checked> فعال</label><button class="btn primary">ساخت کد</button></form></div>
<div class="card table-wrap"><table class="table"><tr><th>کد</th><th>نوع</th><th>مقدار</th><th>فعال</th><th>انقضا</th><th></th></tr>
@forelse($coupons as $c)<tr><td><strong>{{ $c->code }}</strong></td><td>{{ $c->type }}</td><td>{{ number_format((float)$c->value) }}</td><td>{{ $c->active?'بله':'خیر' }}</td><td>{{ $c->ends_at ?: '—' }}</td>
<td><form method="post" action="{{ route('admin.coupons.toggle',$c->id) }}">@csrf<button class="btn">{{ $c->active?'غیرفعال':'فعال' }}</button></form></td></tr>
@empty<tr><td colspan="6">کدی ساخته نشده.</td></tr>@endforelse</table></div>{{ $coupons->links() }}
@endsection