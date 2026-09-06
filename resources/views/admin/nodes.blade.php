@extends('layouts.app')
@section('title','نودها — BlueGate Admin')
@section('content')
<div class="top-title"><div><h2>Node Manager</h2><div class="muted">در این فاز نمایش آماده است؛ فرم افزودن/Sync در فاز Provisioning اضافه می‌شود.</div></div></div><div class="card table-wrap">@if($nodes->count())<table class="table"><tr><th>نام</th><th>لوکیشن</th><th>Provider</th><th>وضعیت</th><th>فروش</th></tr>@foreach($nodes as $n)<tr><td>{{ $n->name }}</td><td>{{ $n->location_name ?? '—' }}</td><td>{{ $n->provider_type }}</td><td>{{ $n->status }}</td><td>{{ $n->sales_enabled?'فعال':'متوقف' }}</td></tr>@endforeach</table>@else<div class="empty">هنوز نودی تعریف نشده است.</div>@endif</div>
@endsection

