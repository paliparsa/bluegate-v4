@extends('layouts.app')
@section('title','خرید BluePing — BlueGate')
@section('content')
<div class="top-title">
 <div><span class="muted" style="font-size:11px">BLUEPING PURCHASE BUILDER</span><h2>سرویس رو بر اساس نیازت بساز</h2><div class="muted">Standard یا Pro، سپس مدت، حجم، دستگاه و لوکیشن؛ قیمت نهایی از پلن واقعی Catalog انتخاب می‌شود.</div></div>
 <form method="post" action="{{ route('app.trial.claim') }}">@csrf<button class="btn">تست رایگان</button></form>
</div>

<div class="recommend-box">
 <div class="service-head"><div><div class="admin-kicker">SMART RECOMMENDATION</div><h3 style="margin:5px 0">نمی‌دونی Standard یا Pro؟</h3></div><span class="pill">۱۰ ثانیه</span></div>
 <p class="muted">فقط اولویتت رو بگو؛ انتخاب پیشنهادی روی Builder اعمال میشه.</p>
 <div class="recommend-actions">
  <button type="button" class="btn ghost rec-btn" data-rec="standard">قیمت اقتصادی مهم‌تره</button>
  <button type="button" class="btn ghost rec-btn" data-rec="standard">استفاده روزمره دارم</button>
  <button type="button" class="btn ghost rec-btn" data-rec="pro">کیفیت و پایداری مهم‌تره</button>
  <button type="button" class="btn ghost rec-btn" data-rec="pro">استفاده مداوم و جدی دارم</button>
 </div>
 <div id="recommendResult" class="recommend-result"></div>
</div>

@php
$catalog = $products->map(function($product) use ($plans) {
    $label = strtolower(($product->name ?? '').' '.($product->slug ?? ''));
    $type = str_contains($label,'pro') ? 'pro' : 'standard';
    return [
        'id'=>$product->id,'name'=>$product->name,'slug'=>$product->slug,'type'=>$type,
        'description'=>$product->description,
        'plans'=>$plans->get($product->id,collect())->map(fn($p)=>[
            'id'=>$p->id,'name'=>$p->name,'traffic_gb'=>$p->unlimited_traffic?null:(int)$p->traffic_gb,
            'unlimited_traffic'=>(bool)$p->unlimited_traffic,'duration_days'=>$p->unlimited_duration?null:(int)$p->duration_days,
            'unlimited_duration'=>(bool)$p->unlimited_duration,'device_limit'=>$p->device_limit?(int)$p->device_limit:null,
            'price'=>(float)$p->base_price,'currency'=>$p->currency,
        ])->values()
    ];
})->values();
@endphp

@if($products->isEmpty())
<div class="card empty"><h3>پلن Standard یا Pro فعالی پیدا نشد</h3><p>از Catalog Manager حداقل یک Product فعال با نام/Slug شامل Standard یا Pro بساز.</p></div>
@else
<form id="builderForm" method="post" action="{{ route('app.buy.order') }}">@csrf
<input type="hidden" id="plan_id" name="plan_id" value="">
<input type="hidden" id="location_id" name="location_id" value="">

<div class="builder-shell">
 <div class="card builder-main">
  <div class="step">
   <div class="step-head"><div><span class="step-num">1</span> <strong>نوع سرویس</strong></div><span class="muted">Standard / Pro</span></div>
   <div id="productChoices" class="choice-grid"></div>
  </div>

  <div class="step">
   <div class="step-head"><div><span class="step-num">2</span> <strong>مدت سرویس</strong></div><span class="muted">بر اساس پلن‌های موجود</span></div>
   <div id="durationChoices" class="choice-row"></div>
  </div>

  <div class="step">
   <div class="step-head"><div><span class="step-num">3</span> <strong>حجم</strong></div><span class="muted">ترافیک پلن</span></div>
   <div id="trafficChoices" class="choice-row"></div>
  </div>

  <div class="step">
   <div class="step-head"><div><span class="step-num">4</span> <strong>تعداد دستگاه</strong></div><span class="muted">Device limit</span></div>
   <div id="deviceChoices" class="choice-row"></div>
  </div>

  <div class="step">
   <div class="step-head"><div><span class="step-num">5</span> <strong>لوکیشن</strong></div><span class="muted">Node توسط BlueGate انتخاب می‌شود</span></div>
   @if($locations->count())
   <div class="choice-grid" id="locationChoices">
    @foreach($locations as $loc)
    <label class="choice location-choice" data-location="{{ $loc->id }}">
     <input type="radio" name="location_radio" value="{{ $loc->id }}">
     <div><strong>{{ $loc->flag }} {{ $loc->name }}</strong><small>{{ $loc->city ?: strtoupper($loc->country_code) }}</small></div>
    </label>
    @endforeach
   </div>
   @else
   <div class="builder-alert">در حال حاضر هیچ لوکیشن سالم و قابل فروش وجود ندارد. ابتدا Node Health را بررسی کن.</div>
   @endif
  </div>

  <div class="step">
   <div class="step-head"><div><span class="step-num">6</span> <strong>کد تخفیف</strong></div><span class="muted">اختیاری</span></div>
   <input class="input" name="coupon_code" placeholder="کد تخفیف">
  </div>
 </div>

 <aside class="card builder-summary">
  <div class="admin-kicker">ORDER SUMMARY</div>
  <div id="summaryProduct" class="summary-product">—</div>
  <div id="summaryPlan" class="muted">پلن را انتخاب کن</div>
  <div id="summaryPrice" class="summary-price">— <small>تومان</small></div>
  <div class="summary-lines">
   <div class="summary-line"><span>مدت</span><strong id="summaryDuration">—</strong></div>
   <div class="summary-line"><span>حجم</span><strong id="summaryTraffic">—</strong></div>
   <div class="summary-line"><span>دستگاه</span><strong id="summaryDevice">—</strong></div>
   <div class="summary-line"><span>لوکیشن</span><strong id="summaryLocation">—</strong></div>
  </div>
  <div class="builder-alert" style="margin:15px 0">BlueGate فقط لوکیشن را از تو می‌گیرد؛ انتخاب Node سالم و کم‌بار پشت صحنه انجام می‌شود.</div>
  <button id="submitBuilder" class="btn primary" style="width:100%" disabled>ساخت سفارش</button>
  <div id="builderHint" class="muted" style="font-size:11px;text-align:center;margin-top:10px">انتخاب‌ها را کامل کن.</div>
 </aside>
</div>
</form>
@endif
@endsection

@if(!$products->isEmpty())
@push('scripts')
<script>
const catalog = @json($catalog);
const state = {productId:null,duration:undefined,traffic:undefined,devices:undefined,location:null,plan:null};

const els = {
 products:document.getElementById('productChoices'), durations:document.getElementById('durationChoices'),
 traffic:document.getElementById('trafficChoices'), devices:document.getElementById('deviceChoices'),
 plan:document.getElementById('plan_id'), location:document.getElementById('location_id'),
 submit:document.getElementById('submitBuilder'), hint:document.getElementById('builderHint')
};
const uniq = a => [...new Set(a.map(v=>v===null?'__null__':String(v)))].map(v=>v==='__null__'?null:Number(v));
const product = () => catalog.find(p=>p.id===state.productId);
const fmt = n => new Intl.NumberFormat('fa-IR').format(Number(n||0));
const durationLabel = v => v===null?'نامحدود':`${v} روز`;
const trafficLabel = v => v===null?'نامحدود':`${v} GB`;
const deviceLabel = v => v===null?'بدون محدودیت':`${v} دستگاه`;

function selectProduct(id){
 state.productId=id; state.duration=undefined; state.traffic=undefined; state.devices=undefined; state.plan=null;
 renderProducts(); renderDimensions(); updateSummary();
}
function renderProducts(){
 els.products.innerHTML=catalog.map(p=>`<button type="button" class="choice ${p.id===state.productId?'active':''}" onclick="selectProduct('${p.id}')"><strong>BluePing ${p.type==='pro'?'Pro':'Standard'}</strong><small>${p.type==='pro'?'کیفیت و پایداری بالاتر':'اقتصادی و مناسب استفاده روزمره'}</small></button>`).join('');
}
function possiblePlans(filters={}){
 const p=product(); if(!p) return [];
 return p.plans.filter(x =>
  (filters.duration===undefined || x.duration_days===filters.duration) &&
  (filters.traffic===undefined || x.traffic_gb===filters.traffic) &&
  (filters.devices===undefined || x.device_limit===filters.devices)
 );
}
function chips(target, values, selected, formatter, handler){
 target.innerHTML=values.map(v=>`<button type="button" class="chip-choice ${(v===selected)?'active':''}" onclick="${handler}(${v===null?'null':v})">${formatter(v)}</button>`).join('');
}
function renderDimensions(){
 const p=product();
 if(!p){els.durations.innerHTML=els.traffic.innerHTML=els.devices.innerHTML='';return;}
 const durations=uniq(p.plans.map(x=>x.duration_days));
 if(state.duration!==undefined && !durations.some(x=>x===state.duration)) state.duration=undefined;
 chips(els.durations,durations,state.duration,durationLabel,'setDuration');

 const afterDuration=possiblePlans(state.duration===undefined?{}:{duration:state.duration});
 const traffic=uniq(afterDuration.map(x=>x.traffic_gb));
 if(state.traffic!==undefined && !traffic.some(x=>x===state.traffic)) state.traffic=undefined;
 chips(els.traffic,traffic,state.traffic,trafficLabel,'setTraffic');

 const filters={}; if(state.duration!==undefined)filters.duration=state.duration;if(state.traffic!==undefined)filters.traffic=state.traffic;
 const afterTraffic=possiblePlans(filters);
 const devices=uniq(afterTraffic.map(x=>x.device_limit));
 if(state.devices!==undefined && !devices.some(x=>x===state.devices)) state.devices=undefined;
 chips(els.devices,devices,state.devices,deviceLabel,'setDevices');
 resolvePlan();
}
function setDuration(v){state.duration=v;state.traffic=undefined;state.devices=undefined;renderDimensions();updateSummary()}
function setTraffic(v){state.traffic=v;state.devices=undefined;renderDimensions();updateSummary()}
function setDevices(v){state.devices=v;resolvePlan();renderDimensions();updateSummary()}
function resolvePlan(){
 const p=product(); if(!p){state.plan=null;return;}
 const candidates=p.plans.filter(x=>x.duration_days===state.duration && x.traffic_gb===state.traffic && x.device_limit===state.devices);
 state.plan=candidates[0]||null;
 els.plan.value=state.plan?.id||'';
}
document.querySelectorAll('[data-location]').forEach(label=>{
 label.addEventListener('click',()=>{
  document.querySelectorAll('[data-location]').forEach(x=>x.classList.remove('active'));
  label.classList.add('active'); state.location=label.dataset.location; els.location.value=state.location;
  const radio=label.querySelector('input'); if(radio)radio.checked=true; updateSummary();
 });
});
function updateSummary(){
 const p=product();
 document.getElementById('summaryProduct').textContent=p?`BluePing ${p.type==='pro'?'Pro':'Standard'}`:'—';
 document.getElementById('summaryPlan').textContent=state.plan?.name||'ترکیب پلن را کامل کن';
 document.getElementById('summaryPrice').innerHTML=state.plan?`${fmt(state.plan.price)} <small>تومان</small>`:'— <small>تومان</small>';
 document.getElementById('summaryDuration').textContent=state.plan?durationLabel(state.plan.duration_days):(state.duration!==undefined?durationLabel(state.duration):'—');
 document.getElementById('summaryTraffic').textContent=state.plan?trafficLabel(state.plan.traffic_gb):(state.traffic!==undefined?trafficLabel(state.traffic):'—');
 document.getElementById('summaryDevice').textContent=state.plan?deviceLabel(state.plan.device_limit):(state.devices!==undefined?deviceLabel(state.devices):'—');
 const loc=document.querySelector(`[data-location="${state.location}"] strong`);
 document.getElementById('summaryLocation').textContent=loc?loc.textContent.trim():'—';
 const ready=!!state.plan && !!state.location;
 els.submit.disabled=!ready;
 els.hint.textContent=ready?'آماده ساخت سفارش و انتخاب روش پرداخت.':'انتخاب‌ها را کامل کن.';
}
document.querySelectorAll('.rec-btn').forEach(btn=>btn.addEventListener('click',()=>{
 const wanted=btn.dataset.rec;
 const p=catalog.find(x=>x.type===wanted);
 if(!p)return;
 selectProduct(p.id);
 const box=document.getElementById('recommendResult'); box.style.display='block';
 box.innerHTML=`<strong>پیشنهاد ما: BluePing ${wanted==='pro'?'Pro':'Standard'}</strong><div class="muted" style="margin-top:4px">${wanted==='pro'?'چون کیفیت و پایداری را در اولویت گذاشتی.':'چون استفاده اقتصادی و روزمره برایت مهم‌تر است.'}</div>`;
 document.getElementById('builderForm').scrollIntoView({behavior:'smooth',block:'start'});
}));
renderProducts();
if(catalog.length) selectProduct(catalog[0].id);
updateSummary();
</script>
@endpush
@endif
