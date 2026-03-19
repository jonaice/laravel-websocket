<?php

use Illuminate\Support\Facades\Route;
use App\Events\MessageSent;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome'); 
});

Route::get('/chat', function () {
    return view('chat');
});

Route::post('/chat/message', function (Request $request) {
    MessageSent::dispatch($request->input('message', 'Mensaje vacío'));
    return response()->json(['status' => 'success']);
});
