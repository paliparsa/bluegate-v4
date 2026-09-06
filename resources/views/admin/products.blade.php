@extends('layouts.app')
@section('title','Catalog Manager — BlueGate')
@section('content')
<div class="admin-hero"><div><div class="admin-kicker">PRODUCT OPERATIONS</div><h2 style="margin:7px 0 6px">Catalog Manager</h2><div class="muted">محصولات، پلن‌ها، Trial و قیمت‌گذاری.</div></div><span class="pill">{{ $products->count() }} PRODUCTS</span></div>

<div class="card panel-card" style="margin-bottom:18px">
 <div class="panel-title"><div><h3>محصول جدید</h3><span class="muted">یک خانواده سرویس جدید به Catalog اضافه کن.</span></div><span class="pill">NEW PRODUCT</span></div>
 <form method="post" action="{{ route('admin.products.store') }}">@csrf
  <div class="form-grid"><div class="field"><label>نام</label><input class="input" name="name" required></div><div class="field"><label>Slug</label><input class="input" name="slug" required></div><div class="field"><label>Sort</label><input class="input" type="number" name="sort_order" value="0"></div></div>
  <div class="field"><label>توضیحات</label><textarea class="input" rows="3" name="description"></textarea></div>
  <div class="switch-row"><label><input type="checkbox" name="active" value="1" checked> Active</label></div>
  <button class="btn primary">ساخت محصول</button>
 </form>
</div>

@foreach($products as $product)
<section class="card catalog-product">
 <div class="catalog-head"><div><div class="admin-kicker">PRODUCT · {{ strtoupper($product->slug) }}</div><h3 style="font-size:22px;margin:6px 0">{{ $product->name }}</h3><p class="muted">{{ $product->description }}</p></div><span class="pill {{ $product->active?'ok':'' }}">{{ $product->active?'ACTIVE':'DISABLED' }}</span></div>
 <form method="post" action="{{ route('admin.products.update',$product->id) }}">@csrf @method('PUT')
  <div class="form-grid"><div class="field"><label>نام</label><input class="input" name="name" value="{{ $product->name }}" required></div><div class="field"><label>Slug</label><input class="input" name="slug" value="{{ $product->slug }}" required></div><div class="field"><label>Sort</label><input class="input" type="number" name="sort_order" value="{{ $product->sort_order }}"></div></div>
  <div class="field"><textarea class="input" rows="2" name="description">{{ $product->description }}</textarea></div>
  <div class="switch-row"><label><input type="checkbox" name="active" value="1" @checked($product->active)> Active</label><button class="btn">ذخیره محصول</button></div>
 </form>

 <div class="plan-grid">
  @foreach($plans->get($product->id,collect()) as $plan)
  <div class="plan-card"><form method="post" action="{{ route('admin.plans.update',$plan->id) }}">@csrf @method('PUT')
   <div class="service-head"><span class="pill {{ $plan->active?'ok':'' }}">{{ $plan->active?'ACTIVE':'OFF' }}</span>@if($plan->trial_enabled)<span class="pill">TRIAL</span>@endif</div>
   <div class="field"><label>نام پلن</label><input class="input" name="name" value="{{ $plan->name }}"></div>
   <div class="price">{{ number_format((float)$plan->base_price) }} <small style="font-size:11px;color:var(--muted)">تومان</small></div>
   <div class="service-meta"><div class="field"><label>GB</label><input class="input" type="number" name="traffic_gb" value="{{ $plan->traffic_gb }}"></div><div class="field"><label>Days</label><input class="input" type="number" name="duration_days" value="{{ $plan->duration_days }}"></div></div>
   <div class="field"><label>Price</label><input class="input" type="number" name="base_price" value="{{ (float)$plan->base_price }}"></div>
   <input type="hidden" name="device_limit" value="{{ $plan->device_limit }}"><input type="hidden" name="sort_order" value="{{ $plan->sort_order }}">
   @if($plan->unlimited_traffic)<input type="hidden" name="unlimited_traffic" value="1">@endif
   @if($plan->unlimited_duration)<input type="hidden" name="unlimited_duration" value="1">@endif
   @if($plan->renewable)<input type="hidden" name="renewable" value="1">@endif
   <div class="switch-row"><label><input type="checkbox" name="trial_enabled" value="1" @checked($plan->trial_enabled)> Trial</label><label><input type="checkbox" name="active" value="1" @checked($plan->active)> Active</label></div>
   <button class="btn primary" style="width:100%">ذخیره پلن</button>
  </form></div>
  @endforeach
  <div class="plan-card"><div class="admin-kicker">NEW PLAN</div><h3 style="margin-top:6px">افزودن پلن</h3>
   <form method="post" action="{{ route('admin.plans.store') }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
    <div class="field"><input class="input" name="name" placeholder="نام پلن" required></div>
    <div class="service-meta"><div class="field"><input class="input" type="number" name="traffic_gb" placeholder="GB"></div><div class="field"><input class="input" type="number" name="duration_days" placeholder="روز"></div></div>
    <div class="service-meta"><div class="field"><input class="input" type="number" name="device_limit" placeholder="Device"></div><div class="field"><input class="input" type="number" name="base_price" placeholder="قیمت" required></div></div>
    <div class="switch-row"><label><input type="checkbox" name="active" value="1" checked> Active</label><label><input type="checkbox" name="renewable" value="1" checked> Renewable</label><label><input type="checkbox" name="trial_enabled" value="1"> Trial</label><label><input type="checkbox" name="unlimited_traffic" value="1"> Unlimited GB</label><label><input type="checkbox" name="unlimited_duration" value="1"> Unlimited time</label></div>
    <button class="btn primary" style="width:100%">افزودن پلن</button>
   </form>
  </div>
 </div>
</section>
@endforeach
@endsection