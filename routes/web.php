<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    info('Welcome page accessed');

    return view('welcome');
});
