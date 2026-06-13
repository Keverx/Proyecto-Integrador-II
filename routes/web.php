<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tacho-qr', function () {
    return view('tacho-qr');
});
