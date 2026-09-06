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
 Route::get('/products',[AdminController::class,'products'])->name('products');
 Route::get('/payments',[AdminController::class,'payments'])->name('payments');
});
require __DIR__.'/subscription.php';
