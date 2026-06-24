<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
});
Route::get('/privacy', function () {
    return view('privacy');
});
Route::get('/terms', function () {
    return view('terms');
});
Route::get('/safety', function () {
    return view('safety');
});
Route::get('/contact', function () {
    return view('contact');
});
Route::get('/delete-account', function () {
    return view('delete-account');
});
