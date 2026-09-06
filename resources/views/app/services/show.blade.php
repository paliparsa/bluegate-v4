@extends('layouts.app')
@section('title','مدیریت سرویس — BlueGate')
@section('content')
<div class="top-title">
 <div><h2>{{ $product->name ?? 'BluePing' }}</h2><div class="muted">{{ $plan->name ?? 'سرویس' }} · {{ substr($s->id,0,8) }}</div></div>
 <span class="pill {{ $s->status==='active'?'ok':'' }}"><i class="network-dot"></i>{{ $s->status==='active'?'فعال':$s->status }}</span>
</div>

@php($limit=$s->traffic_limit_bytes ?: 0)
@php($used=$s->traffic_used_bytes ?: 0)
@php($pct=$limit?min(100,round($used/$limit*100)):0)

<div class="metric-row">
 <div class="metric-cell"><span>مصرف</span><b>{{ number_format($used/1073741824,1) }}</b><small class="muted"> GB</small></div>
 <div class="metric-cell"><span>حجم کل</span><b>{{ $limit?number_format($limit/1073741824,1):'∞' }}</b><small class="muted"> {{ $limit?'GB':'' }}</small></div>
 <div class="metric-cell"><span>لوکیشن</span><b style="font-size:16px">{{ $location->flag ?? '🌐' }} {{ $location->name ?? 'Auto' }}</b></div>
 <div class="metric-cell"><span>انقضا</span><b style="font-size:13px">{{ $s->expires_at ?? 'نامحدود' }}</b></div>
</div>

<section class="section">
 <div class="card" style="padding:18px">
  <div class="service-head"><div><h3 style="margin:0 0 5px">اتصال سریع</h3><div class="muted" style="font-size:12px">لینک خصوصی این سرویس</div></div><a class="btn ghost" href="{{ route('app.setup',['service'=>$s->id]) }}">راهنما</a></div>
  @if($subscriptionUrl)
   <div class="sub-box" style="margin-top:14px">
    <input id="suburl" class="input" readonly value="{{ $subscriptionUrl }}">
    <div style="display:flex;gap:7px;flex-wrap:wrap">
     @if($hiddifyDeepLink)<a class="btn primary" href="{{ $hiddifyDeepLink }}">باز کردن در Hiddify</a>@endif
     <button class="btn" type="button" onclick="copySub(this)">کپی لینک</button>
     <button class="btn" type="button" onclick="toggleQr()">QR</button>
    </div>
   </div>
   <div id="qrbox" style="display:none;margin-top:14px;background:#fff;width:max-content;padding:12px;border-radius:10px"><div id="qrcode"></div></div>
  @else
   <p class="muted">لینک اتصال هنوز آماده نشده.</p>
  @endif
 </div>
</section>

<section class="section">
 <div class="top-title"><div><h2>مدیریت سرویس</h2></div></div>
 <div class="grid">
  @if($plan?->renewable)
  <div class="card" style="padding:18px">
   <h3 style="margin-top:0">تمدید</h3>
   <p class="muted" style="font-size:12px">تمدید با همین پلن</p>
   <div class="price">{{ number_format((float)$plan->base_price) }} <span class="muted" style="font-size:11px">تومان</span></div>
   <form method="post" action="{{ route('app.services.renew',$s->id) }}">@csrf
    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
    <button class="btn primary">تمدید با کیف پول</button>
   </form>
  </div>
  @endif

  @if(!($plan?->unlimited_traffic ?? false))
  <div class="card" style="padding:18px">
   <h3 style="margin-top:0">افزایش حجم</h3>
   <p class="muted" style="font-size:12px">{{ number_format($trafficPrice) }} تومان برای هر گیگ</p>
   <form method="post" action="{{ route('app.services.traffic',$s->id) }}">@csrf
    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
    <div class="field"><select class="input" name="gb">
     <option value="10">10 GB — {{ number_format($trafficPrice*10) }} تومان</option>
     <option value="25">25 GB — {{ number_format($trafficPrice*25) }} تومان</option>
     <option value="50">50 GB — {{ number_format($trafficPrice*50) }} تومان</option>
     <option value="100">100 GB — {{ number_format($trafficPrice*100) }} تومان</option>
    </select></div>
    <button class="btn primary">خرید حجم</button>
   </form>
  </div>
  @endif

  <div class="card" style="padding:18px">
   <h3 style="margin-top:0">تغییر لوکیشن</h3>
   <p class="muted" style="font-size:12px">{{ $locationPrice>0 ? number_format($locationPrice).' تومان' : 'رایگان' }}</p>
   <form method="post" action="{{ route('app.services.location',$s->id) }}">@csrf
    <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::uuid() }}">
    <div class="field"><select class="input" name="location_id" required>
     @foreach($locations as $loc)<option value="{{ $loc->id }}" @selected($s->current_location_id===$loc->id)>{{ $loc->flag }} {{ $loc->name }} {{ $loc->city ? '— '.$loc->city : '' }}</option>@endforeach
    </select></div>
    <button class="btn">تغییر لوکیشن</button>
   </form>
  </div>
 </div>
</section>

@if($subscriptionUrl)
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let qrMade=false;
function copySub(button){
 navigator.clipboard.writeText(document.getElementById('suburl').value).then(()=>{
  const old=button.textContent;button.textContent='کپی شد ✓';setTimeout(()=>button.textContent=old,1500);
 });
}
function toggleQr(){
 const box=document.getElementById('qrbox');box.style.display=box.style.display==='none'?'block':'none';
 if(!qrMade){new QRCode(document.getElementById('qrcode'),{text:document.getElementById('suburl').value,width:210,height:210});qrMade=true;}
}
</script>
@endif
@endsection