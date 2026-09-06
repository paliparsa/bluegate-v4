@extends('layouts.app')
@section('title','مدیریت سرویس — BlueGate')
@section('content')
<div class="top-title">
 <div><span class="muted" style="font-size:11px">SERVICE CONTROL</span><h2>{{ $product->name ?? 'BluePing Service' }}</h2><div class="muted">{{ $plan->name ?? 'سرویس سفارشی' }} · {{ substr($s->id,0,8) }}</div></div>
 <span class="pill {{ $s->status==='active'?'ok':'' }}"><i class="network-dot"></i>{{ strtoupper($s->status) }}</span>
</div>

<div class="grid">
 <div class="card"><h3>مصرف ترافیک</h3>
 @php($limit=$s->traffic_limit_bytes ?: 0) @php($used=$s->traffic_used_bytes ?: 0) @php($pct=$limit?min(100,round($used/$limit*100)):0)
 <div class="price">{{ number_format($used/1073741824,1) }} GB</div><div class="progress"><i style="width:{{ $pct }}%"></i></div>
 <p class="muted">از {{ $limit?number_format($limit/1073741824,1).' GB':'نامحدود' }}</p></div>
 <div class="card"><h3>لوکیشن</h3><div class="price">{{ $location->flag ?? '🌐' }} {{ $location->name ?? 'Auto' }}</div><p class="muted">{{ $location->city ?? 'انتخاب هوشمند نود' }}</p></div>
 <div class="card"><h3>انقضا</h3><div class="price" style="font-size:22px">{{ $s->expires_at ?? 'بدون تاریخ' }}</div><p class="muted">شروع: {{ $s->starts_at ?? '—' }}</p></div>
</div>

<div class="section"><div class="card"><div class="service-head"><div><span class="muted" style="font-size:11px">QUICK CONNECT</span><h3 style="margin-top:5px">Subscription اختصاصی</h3></div><span class="pill">PRIVATE LINK</span></div>
@if($subscriptionUrl)
 <p class="muted">لینک اختصاصی BlueGate؛ جزئیات 3x-ui برای کاربر مخفی می‌ماند.</p>
 <div class="sub-box"><input id="suburl" class="input" readonly value="{{ $subscriptionUrl }}">
 <div style="display:flex;gap:10px;flex-wrap:wrap">
  <button class="btn primary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('suburl').value);this.innerText='کپی شد ✓'">Copy Subscription</button>
  <button class="btn" type="button" onclick="toggleQr()">QR Code</button>
 </div></div>
 <div id="qrbox" style="display:none;margin-top:18px;background:#fff;width:max-content;padding:14px;border-radius:14px"><div id="qrcode"></div></div>
@else <p class="muted">Subscription هنوز برای این سرویس ساخته نشده است.</p> @endif
</div></div>

<div class="grid">
 @if($plan?->renewable)
 <div class="card"><h3>تمدید سرویس</h3><p class="muted">همان پلن فعلی برای یک دوره دیگر تمدید می‌شود.</p>
 <div class="price">{{ number_format((float)$plan->base_price) }} تومان</div>
 <form method="post" action="{{ route('app.services.renew',$s->id) }}">@csrf
  <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
  <button class="btn primary">تمدید با کیف پول</button>
 </form></div>
 @endif

 @if(!($plan?->unlimited_traffic ?? false))
 <div class="card"><h3>افزایش حجم</h3><p class="muted">هر گیگ {{ number_format($trafficPrice) }} تومان</p>
 <form method="post" action="{{ route('app.services.traffic',$s->id) }}">@csrf
  <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
  <div class="field"><select class="input" name="gb">
   <option value="10">10 GB — {{ number_format($trafficPrice*10) }} تومان</option>
   <option value="25">25 GB — {{ number_format($trafficPrice*25) }} تومان</option>
   <option value="50">50 GB — {{ number_format($trafficPrice*50) }} تومان</option>
   <option value="100">100 GB — {{ number_format($trafficPrice*100) }} تومان</option>
  </select></div>
  <button class="btn primary">خرید حجم</button>
 </form></div>
 @endif

 <div class="card"><h3>تغییر لوکیشن</h3><p class="muted">{{ $locationPrice>0 ? number_format($locationPrice).' تومان' : 'رایگان' }} · انتقال خودکار بین نودها</p>
 <form method="post" action="{{ route('app.services.location',$s->id) }}">@csrf
  <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
  <div class="field"><select class="input" name="location_id" required>
   @foreach($locations as $loc)<option value="{{ $loc->id }}" @selected($s->current_location_id===$loc->id)>{{ $loc->flag }} {{ $loc->name }} {{ $loc->city ? '— '.$loc->city : '' }}</option>@endforeach
  </select></div>
  <button class="btn primary">تغییر لوکیشن</button>
 </form></div>
</div>

@if($subscriptionUrl)
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let qrMade=false;
function toggleQr(){
 const box=document.getElementById('qrbox'); box.style.display=box.style.display==='none'?'block':'none';
 if(!qrMade){ new QRCode(document.getElementById('qrcode'),{text:document.getElementById('suburl').value,width:220,height:220}); qrMade=true; }
}
</script>
@endif
@endsection
