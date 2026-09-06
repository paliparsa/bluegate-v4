<?php
use App\Http\Controllers\Subscription\SubscriptionController;
use Illuminate\Support\Facades\Route;
Route::get('/s/{token}', SubscriptionController::class)->where('token', '[A-Za-z0-9_-]+');
