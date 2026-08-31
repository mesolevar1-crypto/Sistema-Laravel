<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', function () {
    return view('autenticacion.login');
})->name('login');

Route::get('/registro', function () {
    return view('autenticacion.registre');
})->name('registre');