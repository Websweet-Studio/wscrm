<?php

use App\Http\Controllers\Admin\ThirdPartyPluginController;
use App\Http\Controllers\Api\ChatCompletionsController;
use Illuminate\Support\Facades\Route;

Route::get('plugins', [ThirdPartyPluginController::class, 'publicIndex']);

// Demo embed tracking beacon
Route::post('demo-embed/track', [App\Http\Controllers\DemoWebsiteController::class, 'trackEmbed']);

// Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
Route::post('v1/chat/completions', [ChatCompletionsController::class, 'chat'])
    ->middleware('throttle:30,1');
