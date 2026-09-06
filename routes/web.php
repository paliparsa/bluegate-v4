<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ServiceController;
use App\Http\Controllers\App\ShopController;
use App\Http\Controllers\App\WalletController;
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
});
Route::prefix('admin')->middleware(['auth','admin'])->name('admin.')->group(function(){
 Route::get('/',[AdminController::class,'dashboard'])->name('dashboard');
 Route::get('/nodes',[AdminController::class,'nodes'])->name('nodes');
 Route::get('/products',[AdminController::class,'products'])->name('products');
});
require __DIR__.'/subscription.php';
