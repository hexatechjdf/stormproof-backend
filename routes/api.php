<?php

use App\Http\Controllers\SsoAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\StripeWebhookController;
use App\Http\Controllers\Webhook\CompanyCamWebhookController;

Route::post('v1/webhooks/stripe', [StripeWebhookController::class, 'handle']);
Route::post('v1/webhooks/companycam', [CompanyCamWebhookController::class, 'handle']);
Route::post('/validate-sso', [SsoAuthController::class, 'validateToken'])->name('validate.sso');