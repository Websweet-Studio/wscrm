<?php

use App\Http\Controllers\Admin\ThirdPartyPluginController;
use Illuminate\Support\Facades\Route;

Route::get('plugins', [ThirdPartyPluginController::class, 'publicIndex']);
