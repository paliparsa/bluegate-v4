@extends('layouts.app')
@section('title','تیکت‌ها — BlueGate Admin')
@section('content')
<div class="top-title"><div><h2>Support Desk</h2><div class="muted">تیکت‌های کاربران</div></div></div>
<div class="card table-wrap"><table class="table"><tr><th>شماره</th><th>کاربر</th><th>موضوع</th><th>بخش</th><th>اولویت</th><th>وضعیت</th></tr>
@forelse($tickets as $t)<tr><td><a href="{{ route('admin.tickets.show',$t->id) }}">{{ $t->number }}</a></td><td>{{ $t->email }}</td><td>{{ $t->subject }}</td><td>{{ $t->department }}</td><td>{{ $t->priority }}</td><td><span class="pill">{{ $t->status }}</span></td></tr>
@empty<tr><td colspan="6">تیکتی نیست.</td></tr>@endforelse</table></div>{{ $tickets->links() }}
@endsection