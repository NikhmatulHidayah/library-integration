<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookImageController;

Route::post('/upload-book-image', [BookImageController::class, 'upload']);

