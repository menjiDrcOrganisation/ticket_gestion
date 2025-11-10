<?php
use Illuminate\Support\Facades\Route;
 
 
 Route::get('/scanneur', function () {
    return view('scanneur.scanneur');
});
?>