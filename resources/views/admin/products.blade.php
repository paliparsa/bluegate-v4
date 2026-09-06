@extends('layouts.app')
@section('title','محصولات و پلن‌ها — BlueGate Admin')
@section('content')
<div class="top-title"><div><h2>Catalog Manager</h2><div class="muted">محصولات، پلن‌ها، قیمت و Trial را بدون ویرایش دیتابیس مدیریت کن.</div></div></div>

<div class="card" style="margin-bottom:18px"><h3>محصول جدید</h3>
<form method="post" action="{{ route('admin.products.store') }}">@csrf
<div class="grid">
<div class="field"><label>نام</label><input class="input" name="name" required></div>
<div class="field"><label>Slug</label><input class="input" name="slug" required></div>
<div class="field"><label>Sort</label><input class="input" type="number" name="sort_order" value="0"></div>
</div>
<div class="field"><label>توضیحات</label><textarea class="input" name="description"></textarea></div>
<label><input type="checkbox" name="active" value="1" checked> فعال</label>
<button class="btn primary">ساخت محصول</button></form></div>

@foreach($products as $product)
<section class="card" style="margin-bottom:18px">
<form method="post" action="{{ route('admin.products.update',$product->id) }}">@csrf @method('PUT')
<div class="grid">
<div class="field"><label>نام</label><input class="input" name="name" value="{{ $product->name }}" required></div>
<div class="field"><label>Slug</label><input class="input" name="slug" value="{{ $product->slug }}" required></div>
<div class="field"><label>Sort</label><input class="input" type="number" name="sort_order" value="{{ $product->sort_order }}"></div>
</div>
<div class="field"><textarea class="input" name="description">{{ $product->description }}</textarea></div>
<label><input type="checkbox" name="active" value="1" @checked($product->active)> فعال</label>
<button class="btn">ذخیره محصول</button></form>

<div class="table-wrap" style="margin-top:16px"><table class="table"><tr><th>پلن</th><th>حجم</th><th>مدت</th><th>قیمت</th><th>Trial</th><th>فعال</th><th>ویرایش</th></tr>
@foreach($plans->get($product->id,collect()) as $plan)
<tr><form method="post" action="{{ route('admin.plans.update',$plan->id) }}">@csrf @method('PUT')
<td><input class="input" name="name" value="{{ $plan->name }}"></td>
<td><input class="input" type="number" name="traffic_gb" value="{{ $plan->traffic_gb }}"></td>
<td><input class="input" type="number" name="duration_days" value="{{ $plan->duration_days }}"></td>
<td><input class="input" type="number" name="base_price" value="{{ (float)$plan->base_price }}"></td>
<td><input type="checkbox" name="trial_enabled" value="1" @checked($plan->trial_enabled)></td>
<td><input type="checkbox" name="active" value="1" @checked($plan->active)></td>
<td>
<input type="hidden" name="device_limit" value="{{ $plan->device_limit }}">
<input type="hidden" name="sort_order" value="{{ $plan->sort_order }}">
@if($plan->unlimited_traffic)<input type="hidden" name="unlimited_traffic" value="1">@endif
@if($plan->unlimited_duration)<input type="hidden" name="unlimited_duration" value="1">@endif
@if($plan->renewable)<input type="hidden" name="renewable" value="1">@endif
<button class="btn">ذخیره</button></td>
</form></tr>@endforeach
</table></div>

<div style="margin-top:16px"><h4>پلن جدید برای {{ $product->name }}</h4>
<form method="post" action="{{ route('admin.plans.store') }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
<div class="grid">
<div class="field"><input class="input" name="name" placeholder="نام پلن" required></div>
<div class="field"><input class="input" type="number" name="traffic_gb" placeholder="GB"></div>
<div class="field"><input class="input" type="number" name="duration_days" placeholder="روز"></div>
<div class="field"><input class="input" type="number" name="device_limit" placeholder="Device"></div>
<div class="field"><input class="input" type="number" name="base_price" placeholder="قیمت" required></div>
</div>
<label><input type="checkbox" name="active" value="1" checked> فعال</label>
<label><input type="checkbox" name="renewable" value="1" checked> تمدید</label>
<label><input type="checkbox" name="unlimited_traffic" value="1"> حجم نامحدود</label>
<label><input type="checkbox" name="unlimited_duration" value="1"> زمان نامحدود</label>
<label><input type="checkbox" name="trial_enabled" value="1"> پلن Trial</label>
<button class="btn primary">افزودن پلن</button></form></div>
</section>
@endforeach
@endsection