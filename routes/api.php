<?php

use App\Http\Controllers\Admin\ThirdPartyPluginController;
use App\Http\Controllers\Api\ChatCompletionsController;
use Illuminate\Support\Facades\Route;

Route::get('plugins', [ThirdPartyPluginController::class, 'publicIndex']);

// Endpoint OpenAI-compatible utk customer (Hermes agent, code editor, dsb).
Route::post('v1/chat/completions', [ChatCompletionsController::class, 'chat']);
