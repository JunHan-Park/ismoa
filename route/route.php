<?php

Route::get('/', 'HomeController@getHome');

Route::get('/lang/{locale}', function($locale){
    Env::setLocale($locale);

    back();
});