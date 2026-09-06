<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ServiceController;
use App\Http\Controllers\App\ShopController;
use App\Http\Controllers\App\WalletController;
use App\Http\Controllers\App\CheckoutController;
use App\Http\Controllers\App\PaymentController;
use App\Http\Controllers\App\ServiceOperationController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\CommerceController;
use App\Http\Controllers\App\GrowthController;
use App\Http\Controllers\App\TicketController;
use App\Http\Controllers\App\TelegramLinkController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function(){
 $products=DB::table('products')->where('active',true)->orderBy('sort_order')->limit(4)->get();
 $plans=DB::table('plans')->where('active',true)->orderBy('base_price')->get()->groupBy('product_id');
 return view('welcome.index',compact('products','plans'));
})->name('home');
Route::get('/status', fn()=>view('welcome.status'))->name('status');

Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'showLogin'])->name('login');
 Route::post('/login',[AuthController::class,'login'])->name('login.post');
 Route::get('/register',[AuthController::class,'showRegister'])->name('register');
 Route::post('/register',[AuthController::class,'register'])->name('register.post');
});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth')->name('logout');

Route::prefix('app')->middleware('auth')->name('app.')->group(function(){
 Route::get('/',DashboardController::class)->name('dashboard');
 Route::get('/services',[ServiceController::class,'index'])->name('services');
 Route::get('/services/{id}',[ServiceController::class,'show'])->name('services.show');
 Route::get('/buy',[ShopController::class,'index'])->name('buy');
 Route::post('/buy',[ShopController::class,'order'])->name('buy.order');
 Route::get('/wallet',[WalletController::class,'index'])->name('wallet');
 Route::get('/orders',[WalletController::class,'orders'])->name('orders');
 Route::post('/orders/{id}/wallet',[CheckoutController::class,'payWallet'])->name('orders.wallet');
 Route::post('/orders/{id}/zarinpal',[PaymentController::class,'start'])->name('orders.zarinpal');
 Route::post('/services/{id}/renew',[ServiceOperationController::class,'renew'])->name('services.renew');
 Route::post('/services/{id}/traffic',[ServiceOperationController::class,'traffic'])->name('services.traffic');
 Route::post('/services/{id}/location',[ServiceOperationController::class,'location'])->name('services.location');
 Route::get('/referral',[GrowthController::class,'referral'])->name('referral');
 Route::post('/trial',[GrowthController::class,'trial'])->name('trial.claim');
 Route::get('/notifications',[GrowthController::class,'notifications'])->name('notifications');
 Route::post('/notifications/{id}/read',[GrowthController::class,'readNotification'])->name('notifications.read');
 Route::get('/tickets',[TicketController::class,'index'])->name('tickets');
 Route::post('/tickets',[TicketController::class,'store'])->name('tickets.store');
 Route::get('/tickets/{id}',[TicketController::class,'show'])->name('tickets.show');
 Route::post('/tickets/{id}/reply',[TicketController::class,'reply'])->name('tickets.reply');
 Route::post('/telegram/link',[TelegramLinkController::class,'create'])->name('telegram.link');
});
Route::get('/payments/zarinpal/callback/{payment}/{token}',[PaymentController::class,'callback'])->name('payments.zarinpal.callback');

Route::prefix('admin')->middleware(['auth','admin'])->name('admin.')->group(function(){
 Route::get('/',[AdminController::class,'dashboard'])->name('dashboard');
 Route::get('/nodes',[OperationsController::class,'nodes'])->name('nodes');
 Route::post('/nodes',[OperationsController::class,'storeNode'])->name('nodes.store');
 Route::post('/nodes/{node}/health',[OperationsController::class,'health'])->name('nodes.health');
 Route::post('/nodes/{node}/sync',[OperationsController::class,'sync'])->name('nodes.sync');
 Route::get('/wallets',[OperationsController::class,'wallets'])->name('wallets');
 Route::post('/wallets/credit',[OperationsController::class,'credit'])->name('wallets.credit');
 Route::get('/products',[CommerceController::class,'products'])->name('products');
 Route::post('/products',[CommerceController::class,'storeProduct'])->name('products.store');
 Route::put('/products/{id}',[CommerceController::class,'updateProduct'])->name('products.update');
 Route::post('/plans',[CommerceController::class,'storePlan'])->name('plans.store');
 Route::put('/plans/{id}',[CommerceController::class,'updatePlan'])->name('plans.update');
 Route::get('/coupons',[CommerceController::class,'coupons'])->name('coupons');
 Route::post('/coupons',[CommerceController::class,'storeCoupon'])->name('coupons.store');
 Route::post('/coupons/{id}/toggle',[CommerceController::class,'toggleCoupon'])->name('coupons.toggle');
 Route::get('/tickets',[CommerceController::class,'tickets'])->name('tickets');
 Route::get('/tickets/{id}',[CommerceController::class,'ticket'])->name('tickets.show');
 Route::post('/tickets/{id}/reply',[CommerceController::class,'replyTicket'])->name('tickets.reply');
 Route::get('/payments',[AdminController::class,'payments'])->name('payments');
});
Route::post('/telegram/webhook/{secret}',TelegramWebhookController::class)
 ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
 ->name('telegram.webhook');
require __DIR__.'/subscription.php';
