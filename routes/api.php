<?php

use App\Http\Controllers\TerminalCommandController;
use Illuminate\Support\Facades\Route;


Route::post('/command/{command}', [TerminalCommandController::class, 'show'])->where('command', '[A-Za-z\-]+');
