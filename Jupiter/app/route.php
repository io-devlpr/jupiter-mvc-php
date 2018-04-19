<?php

use \framework\kernel\Route as Route;

Route::get("/", "IndexController@index");
Route::get("/something", "IndexController@action");
Route::get("/{name}/{title}/{joke:\d}", "IndexController@show");
Route::get("/{book-name}/{book-author}/{library:\w+\:0\d+}", "IndexController@book");