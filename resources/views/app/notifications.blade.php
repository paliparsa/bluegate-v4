@extends('layouts.app')
@section('title','اعلان‌ها — BlueGate')
@section('content')
<div class="top-title"><div><h2>اعلان‌ها</h2><div class="muted">رویدادهای مهم حساب و سرویس‌ها</div></div></div>
@forelse($items as $n)
<div class="card" style="margin-bottom:12px;opacity:{{ $n->read_at?'0.7':'1' }}">
 <h3>{{ $n->title }}</h3><p>{{ $n->body }}</p><div class="muted">{{ $n->created_at }}</div>
 @if($n->action_url)<a class="btn" href="{{ $n->action_url }}">مشاهده</a>@endif
 @if(!$n->read_at)<form style="display:inline" method="post" action="{{ route('app.notifications.read',$n->id) }}">@csrf<button class="btn">خواندم</button></form>@endif
</div>
@empty<div class="card">اعلان جدیدی نداری.</div>@endforelse
<div>{{ $items->links() }}</div>
@endsection