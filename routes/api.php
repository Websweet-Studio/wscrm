<?php

use App\Http\Controllers\Admin\ThirdPartyPluginController;
use App\Http\Controllers\Api\ChatCompletionsController;
use Illuminate\Support\Facades\Route;

Route::get('plugins', [ThirdPartyPluginController::class, 'publicIndex']);

// Demo embed tracking beacon
Route::post('demo-embed/track', [App\Http\Controllers\DemoWebsiteController::class, 'trackEmbed']);

// Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
Route::get('v1', function () {
    return response()->json([
        'name' => 'WSCRM AI API',
        'chat_completions' => url('/api/v1/chat/completions'),
        'models' => url('/api/v1/models'),
        'auth' => 'Authorization: Bearer <api_key customer>',
    ]);
});

Route::get('v1/models', [ChatCompletionsController::class, 'models']);

Route::post('v1/chat/completions', [ChatCompletionsController::class, 'chat'])
    ->middleware('throttle:30,1');
