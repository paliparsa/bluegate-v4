@extends('layouts.app')
@section('title','راهنمای اتصال — BlueGate')
@section('content')

<div class="top-title">
 <div>
  <span class="muted" style="font-size:11px">BLUEPING SETUP CENTER</span>
  <h2>اتصال در چند مرحله ساده</h2>
  <div class="muted">دستگاهت رو انتخاب کن، Hiddify رو نصب کن و Subscription سرویس رو وارد کن.</div>
 </div>
 @if($selected)<a class="btn ghost" href="{{ route('app.services.show',$selected->id) }}">بازگشت به سرویس</a>@endif
</div>

@if($services->isEmpty())
<div class="card empty">
 <h3>هنوز سرویس فعالی نداری</h3>
 <p>بعد از خرید و فعال‌شدن سرویس، لینک Subscription و Quick Connect اینجا نمایش داده می‌شود.</p>
 <a class="btn primary" href="{{ route('app.buy') }}">خرید BluePing</a>
</div>
@else

<div class="setup-service-select card" style="padding:16px;margin-bottom:16px">
 <div><strong>سرویس برای اتصال:</strong><div class="muted" style="font-size:11px">اگر چند سرویس داری یکی رو انتخاب کن.</div></div>
 <select class="input" onchange="if(this.value) location.href='{{ route('app.setup') }}?service='+encodeURIComponent(this.value)">
  @foreach($services as $service)
   <option value="{{ $service->id }}" @selected($selected?->id===$service->id)>
    {{ substr($service->id,0,8) }} · {{ strtoupper($service->status) }}
   </option>
  @endforeach
 </select>
</div>

<div class="setup-hero">
 <section class="card">
  <div class="admin-kicker">RECOMMENDED CLIENT</div>
  <h2 style="margin:7px 0">Hiddify</h2>
  <p class="muted" style="line-height:1.9">برای اینکه آموزش بین دستگاه‌ها یکدست باشه، BlueGate فعلاً Hiddify رو به‌عنوان کلاینت پیشنهادی معرفی می‌کنه. Subscription لینک BlueGate رو مستقیماً می‌تونی داخلش Import کنی.</p>

  <div class="platform-grid" style="margin-top:17px">
   <a class="platform-card" href="https://play.google.com/store/apps/details?id=app.hiddify.com" target="_blank" rel="noopener noreferrer">
    <span class="platform-icon">🤖</span><strong>Android</strong><span class="muted">Google Play</span>
   </a>
   <a class="platform-card" href="https://apps.apple.com/app/hiddify-proxy-vpn/id6596777532" target="_blank" rel="noopener noreferrer">
    <span class="platform-icon">📱</span><strong>iPhone / iPad</strong><span class="muted">App Store</span>
   </a>
   <a class="platform-card" href="https://github.com/hiddify/hiddify-app/releases/latest" target="_blank" rel="noopener noreferrer">
    <span class="platform-icon">🪟</span><strong>Windows</strong><span class="muted">Official Releases</span>
   </a>
   <a class="platform-card" href="https://github.com/hiddify/hiddify-app/releases/latest" target="_blank" rel="noopener noreferrer">
    <span class="platform-icon">🍎</span><strong>macOS</strong><span class="muted">Official Releases</span>
   </a>
  </div>
 </section>

 <aside class="quick-connect-card">
  <div class="admin-kicker">QUICK CONNECT</div>
  <h2 style="margin:7px 0">{{ $selectedProduct->name ?? 'BluePing Service' }}</h2>
  @if($subscriptionUrl)
   <p class="muted">اگر Hiddify روی دستگاه نصب است، اول One‑Click Import را امتحان کن. اگر باز نشد، Subscription را کپی کن و دستی Import کن.</p>
   <div class="quick-connect-actions">
    <a class="btn primary" href="{{ $hiddifyDeepLink }}">باز کردن در Hiddify</a>
    <button class="btn" type="button" onclick="copySetupSub(this)">کپی Subscription</button>
   </div>
   <input id="setupSub" class="input" readonly value="{{ $subscriptionUrl }}" style="margin-top:12px;direction:ltr;text-align:left">
   <div class="setup-note" style="margin-top:14px">این لینک خصوصی سرویس توئه. داخل کانال، گروه یا سایت عمومی منتشرش نکن.</div>
  @else
   <p class="muted">Subscription این سرویس هنوز آماده نشده.</p>
  @endif
 </aside>
</div>

<section class="section">
 <div class="admin-kicker">DEVICE GUIDE</div>
 <h2>دستگاهت رو انتخاب کن</h2>
 <div class="device-tabs">
  <button class="device-tab active" type="button" data-device="android">🤖 Android</button>
  <button class="device-tab" type="button" data-device="ios">📱 iPhone / iPad</button>
  <button class="device-tab" type="button" data-device="windows">🪟 Windows</button>
  <button class="device-tab" type="button" data-device="macos">🍎 macOS</button>
 </div>

 <div class="card">
  <div class="device-panel active" data-panel="android">
   <h3>Android</h3>
   <div class="steps">
    <div class="guide-step"><span class="n">1</span><div><b>Hiddify رو نصب کن</b><p>از Google Play یا صفحه رسمی Releases نصبش کن.</p></div></div>
    <div class="guide-step"><span class="n">2</span><div><b>Quick Connect رو بزن</b><p>اگر صفحه Import داخل Hiddify باز شد، Profile رو تأیید کن.</p></div></div>
    <div class="guide-step"><span class="n">3</span><div><b>اگر باز نشد، دستی Import کن</b><p>Subscription رو Copy کن، داخل Hiddify روی + بزن و Add from clipboard / URL رو انتخاب کن.</p></div></div>
    <div class="guide-step"><span class="n">4</span><div><b>Connect</b><p>Profile BlueGate رو انتخاب کن و اتصال رو روشن کن.</p></div></div>
   </div>
  </div>

  <div class="device-panel" data-panel="ios">
   <h3>iPhone / iPad</h3>
   <div class="steps">
    <div class="guide-step"><span class="n">1</span><div><b>Hiddify رو از App Store نصب کن</b><p>بعد از نصب، اجازه VPN Configuration رو در اولین اتصال تأیید کن.</p></div></div>
    <div class="guide-step"><span class="n">2</span><div><b>One‑Click Import</b><p>از همین صفحه «باز کردن در Hiddify» رو بزن.</p></div></div>
    <div class="guide-step"><span class="n">3</span><div><b>روش دستی</b><p>Subscription رو Copy کن و از بخش Add Profile داخل Hiddify وارد کن.</p></div></div>
    <div class="guide-step"><span class="n">4</span><div><b>اتصال</b><p>Profile اضافه‌شده رو انتخاب کن و VPN رو روشن کن.</p></div></div>
   </div>
  </div>

  <div class="device-panel" data-panel="windows">
   <h3>Windows</h3>
   <div class="steps">
    <div class="guide-step"><span class="n">1</span><div><b>نسخه Windows رو بگیر</b><p>از Releases رسمی Hiddify نسخه مناسب Windows رو نصب کن.</p></div></div>
    <div class="guide-step"><span class="n">2</span><div><b>Import لینک</b><p>Quick Connect رو امتحان کن؛ اگر برنامه باز بود و Import انجام نشد، لینک رو دستی Paste کن.</p></div></div>
    <div class="guide-step"><span class="n">3</span><div><b>Profile BlueGate</b><p>بعد از Import، Profile جدید باید در لیست برنامه دیده بشه.</p></div></div>
    <div class="guide-step"><span class="n">4</span><div><b>Connect / System Proxy</b><p>اتصال رو روشن کن و در صورت نیاز حالت System Proxy یا TUN رو از داخل کلاینت فعال کن.</p></div></div>
   </div>
  </div>

  <div class="device-panel" data-panel="macos">
   <h3>macOS</h3>
   <div class="steps">
    <div class="guide-step"><span class="n">1</span><div><b>Hiddify رو نصب کن</b><p>نسخه macOS رو از Releases رسمی بگیر.</p></div></div>
    <div class="guide-step"><span class="n">2</span><div><b>Subscription رو وارد کن</b><p>با Quick Connect یا Copy/Paste لینک BlueGate رو Import کن.</p></div></div>
    <div class="guide-step"><span class="n">3</span><div><b>اجازه‌های سیستم</b><p>اگر macOS برای Network Extension یا VPN Permission درخواست داد، تأییدش کن.</p></div></div>
    <div class="guide-step"><span class="n">4</span><div><b>Connect</b><p>Profile رو انتخاب و اتصال رو فعال کن.</p></div></div>
   </div>
  </div>
 </div>
</section>

<section class="section">
 <div class="card">
  <div class="service-head"><div><div class="admin-kicker">TROUBLESHOOTING</div><h3 style="margin-top:5px">وصل نشد؟ این سه مورد رو اول چک کن</h3></div><a class="btn ghost" href="{{ route('app.tickets') }}">ارسال تیکت</a></div>
  <div class="feature-strip">
   <div class="feature"><b>1. Update Profile</b><span class="muted">Subscription رو Refresh/Update کن تا آخرین مسیرها دریافت بشن.</span></div>
   <div class="feature"><b>2. Change Location</b><span class="muted">از صفحه سرویس یک لوکیشن دیگه انتخاب کن و دوباره تست کن.</span></div>
   <div class="feature"><b>3. Restart Network</b><span class="muted">Wi‑Fi/Mobile Data یا Airplane Mode رو یک‌بار خاموش و روشن کن.</span></div>
  </div>
 </div>
</section>

@endif
@endsection

@push('scripts')
<script>
function copySetupSub(button){
 const input=document.getElementById('setupSub');
 if(!input)return;
 navigator.clipboard.writeText(input.value).then(()=>{
   const old=button.textContent; button.textContent='کپی شد ✓';
   setTimeout(()=>button.textContent=old,1800);
 });
}
document.querySelectorAll('.device-tab').forEach(tab=>{
 tab.addEventListener('click',()=>{
  document.querySelectorAll('.device-tab').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.device-panel').forEach(x=>x.classList.remove('active'));
  tab.classList.add('active');
  document.querySelector(`[data-panel="${tab.dataset.device}"]`)?.classList.add('active');
 });
});
const ua=navigator.userAgent.toLowerCase();
let detected='android';
if(/iphone|ipad|ipod/.test(ua))detected='ios';
else if(/windows/.test(ua))detected='windows';
else if(/macintosh|mac os x/.test(ua))detected='macos';
document.querySelector(`.device-tab[data-device="${detected}"]`)?.click();
</script>
@endpush
