<?php


use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/', function () {
    return view('welcome');
});
Route::get("/get-updates", function () {
    $updates = Telegram::getUpdates();
    return $updates;
});
