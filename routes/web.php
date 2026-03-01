<?php

use App\Models\Books;
use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BooksController;


// Route::get('/', function () {
//     return view('users.books');
// });

Route::resource('books', BooksController::class);

// แก้เป็น

Route::get('/', function () {
    $books = Books::all(); 
    $categories = Category::all();

    return view('users.books', compact('books', 'categories'));
});