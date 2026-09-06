@extends('layouts.app')
@section('title',$ticket->number.' — BlueGate')
@section('content')
<div class="top-title"><div><h2>{{ $ticket->subject }}</h2><div class="muted">{{ $ticket->number }} · {{ $ticket->status }}</div></div></div>
@foreach($messages as $m)<div class="card" style="margin-bottom:10px"><strong>{{ $m->sender_type==='admin'?'BlueGate Support':'شما' }}</strong><p style="white-space:pre-wrap">{{ $m->message }}</p><div class="muted">{{ $m->created_at }}</div></div>@endforeach
@if($ticket->status!=='closed')<div class="card"><form method="post" action="{{ route('app.tickets.reply',$ticket->id) }}">@csrf<div class="field"><textarea class="input" rows="5" name="message" required></textarea></div><button class="btn primary">ارسال پاسخ</button></form></div>@endif
@endsection