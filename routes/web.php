<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/about/{name?}', function ($name ='insert you name and trust me!!') {
    return view ('about', compact('name') );
}); #route parameter = Part of the URL becomes a variable that Laravel gives to your code.


//3ndna / ma3naha trouhi safha oukhra 
//tani /about/{name} koun nzid fi url /about/lili
//aya function ($name) w return name ha toukrj fi safha


/* mvc model - view - controller 

Controller: decides what happens

Model: talks to the data

Blade: shows pages (often not needed for APIs)

*/