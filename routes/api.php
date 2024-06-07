<?php

use App\Http\Controllers\TerminalCommandController;
use Illuminate\Support\Facades\Route;

Route::post('/command/{command}', [TerminalCommandController::class, 'show'])
    ->where('command', '[0-9A-Za-z\-]+');

Route::get('/seeYouSpaceCowboys', [TerminalCommandController::class, 'space']);
