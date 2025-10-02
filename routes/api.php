<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SourceController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ArticleController;


Route::get('sources', [SourceController::class, 'index']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);
