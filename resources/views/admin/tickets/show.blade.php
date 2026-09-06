@extends('layouts.app')
@section('title',$ticket->number.' — Admin')
@section('content')
<div class="top-title"><div><h2>{{ $ticket->subject }}</h2><div class="muted">{{ $ticket->number }} · {{ $ticket->email }}</div></div><span class="pill">{{ $ticket->status }}</span></div>
@foreach($messages as $m)<div class="card" style="margin-bottom:10px"><strong>{{ $m->sender_type==='admin'?'BlueGate Support':'User' }}</strong><p style="white-space:pre-wrap">{{ $m->message }}</p><div class="muted">{{ $m->created_at }}</div></div>@endforeach
<div class="card"><form method="post" action="{{ route('admin.tickets.reply',$ticket->id) }}">@csrf
<div class="field"><textarea class="input" rows="6" name="message" required></textarea></div>
<div class="field"><select class="input" name="status"><option value="answered">پاسخ داده شد</option><option value="open">باز</option><option value="closed">بسته</option></select></div>
<button class="btn primary">ارسال پاسخ</button></form></div>
@endsection