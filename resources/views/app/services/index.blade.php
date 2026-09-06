@extends('layouts.app')
@section('title','سرویس‌های من — BlueGate')
@section('content')
<div class="top-title"><div><h2>سرویس‌های من</h2><div class="muted">مدیریت سرویس‌های فعال و قبلی</div></div><a class="btn primary" href="{{ route('app.buy') }}">سرویس جدید</a></div><div class="card table-wrap">@if($services->count())<table class="table"><tr><th>شناسه</th><th>وضعیت</th><th>مصرف</th><th>انقضا</th><th></th></tr>@foreach($services as $s)<tr><td>{{ substr($s->id,0,8) }}</td><td>{{ $s->status }}</td><td>{{ number_format(($s->traffic_used_bytes ?? 0)/1073741824,1) }} GB</td><td>{{ $s->expires_at ?? '—' }}</td><td><a class="btn" href="{{ route('app.services.show',$s->id) }}">مدیریت</a></td></tr>@endforeach</table><div style="padding:14px">{{ $services->links() }}</div>@else<div class="empty">سرویسی برای نمایش وجود ندارد.</div>@endif</div>
@endsection

