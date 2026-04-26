<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('/docs/scalar', 'docs.scalar')->name('docs.scalar');
