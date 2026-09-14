<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/coach.php';
require __DIR__.'/progress.php';
require __DIR__.'/library.php';
