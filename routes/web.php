<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn () => response()->json(['name'=>'BlueGate V4','status'=>'ok']));

require __DIR__.'/subscription.php';
