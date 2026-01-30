<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Category;
use Illuminate\Http\Request;

class BooksController extends Controller
{
    public function index()
    {
        $books = Books::all();
        $categories = Category::all();

        return view('users.Books', compact('books', 'categories'));
    }

    public function create()
    {
        return view('users.BookCreate');
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required',
            'price'  => 'required',
            'author' => 'required',
            'image'  => 'required|image'
        ]);

        if ($request->filled('new_category')) {
            $category = Category::create([
                'name' => $request->new_category
            ]);
            $categoryId = $category->id;
        } elseif ($request->filled('category_id')) {
            $categoryId = $request->category_id;
        } else {
            return back()->with('error', 'กรุณาเลือกหรือเพิ่มหมวดหมู่');
        }

        $imagePath = $request->file('image')->store('images', 'public');

        Books::create([
            'name'   => $request->name,
            'price'  => $request->price,
            'author' => $request->author,
            'image'  => $imagePath,
            'category_id' => $categoryId
        ]);

        return redirect()->route('books.index');
    }

    //แก้ไข
    public function edit($id)
    {
        $book = Books::findOrFail($id);
        $categories = Category::all();

        return view('users.BookEdit', compact('book', 'categories'));
    }


    public function update(Request $request, Books $book)
    {
        $data = $request->validate([
            'name'   => 'required',
            'price'  => 'required',
            'author' => 'required',
            'image'  => 'nullable|image',
            'category_id' => 'nullable'
        ]);

        // กรณีเพิ่มหมวดใหม่ระหว่างแก้
        if ($request->filled('new_category')) {
            $category = Category::create([
                'name' => $request->new_category
            ]);
            $data['category_id'] = $category->id;
        } elseif ($request->filled('category_id')) {
            $data['category_id'] = $request->category_id;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        $book->update($data);

        return redirect()->route('books.index');
    }


    // ลบ
    public function destroy($id)
    {
        Books::destroy($id);
        return redirect()->route('books.index');
    }
}
