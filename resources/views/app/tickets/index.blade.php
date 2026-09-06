@extends('layouts.app')
@section('title','پشتیبانی — BlueGate')
@section('content')
<div class="top-title"><div><h2>پشتیبانی</h2><div class="muted">تیکت جدید بساز یا گفتگوهای قبلی را ادامه بده.</div></div></div>
<div class="card" style="margin-bottom:18px"><h3>تیکت جدید</h3>
<form method="post" action="{{ route('app.tickets.store') }}">@csrf
<div class="grid"><div class="field"><label>موضوع</label><input class="input" name="subject" required></div>
<div class="field"><label>بخش</label><select class="input" name="department"><option value="support">پشتیبانی</option><option value="technical">فنی</option><option value="sales">فروش</option><option value="billing">مالی</option></select></div>
<div class="field"><label>اولویت</label><select class="input" name="priority"><option value="normal">عادی</option><option value="low">کم</option><option value="high">زیاد</option></select></div></div>
<div class="field"><label>پیام</label><textarea class="input" name="message" rows="5" required></textarea></div><button class="btn primary">ثبت تیکت</button></form></div>
<div class="card table-wrap"><table class="table"><tr><th>شماره</th><th>موضوع</th><th>وضعیت</th><th>آخرین پیام</th></tr>
@forelse($tickets as $t)<tr><td><a href="{{ route('app.tickets.show',$t->id) }}">{{ $t->number }}</a></td><td>{{ $t->subject }}</td><td><span class="pill">{{ $t->status }}</span></td><td>{{ $t->last_message_at }}</td></tr>@empty<tr><td colspan="4">تیکتی نداری.</td></tr>@endforelse
</table></div>{{ $tickets->links() }}
@endsection