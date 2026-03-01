<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BooksController extends Controller
{
    public function index()
    {
        $books = Books::with('category')->get(); // ใช้ with เพื่อลด query
        $categories = Category::all();
        return view('users.Books', compact('books', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'author' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // จัดการหมวดหมู่
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $categoryId = $category->id;
        } else {
            $categoryId = $request->category_id;
        }

        // จัดการรูปภาพ (ย้ายเข้า public/images)
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
        }

        Books::create([
            'name' => $request->name,
            'price' => $request->price,
            'author' => $request->author,
            'image' => $imageName, // เก็บแค่ชื่อไฟล์
            'category_id' => $categoryId
        ]);

        return redirect()->route('books.index')->with('success', 'เพิ่มหนังสือเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id) // เปลี่ยนมารับเป็น $id ก่อนเพื่อความชัวร์
    {
        $book = Books::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'author' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        // เตรียมข้อมูลที่จะอัปเดต (ไม่เอา image เข้าไปก่อน)
        $data = $request->only(['name', 'price', 'author', 'category_id']);

        // จัดการหมวดหมู่ใหม่ (ถ้ามี)
        if ($request->filled('new_category')) {
            $category = Category::create(['name' => $request->new_category]);
            $data['category_id'] = $category->id;
        }

        // ส่วนสำคัญ: ตรวจสอบการอัปโหลดไฟล์
        if ($request->hasFile('image')) {
            // 1. ลบรูปเก่าทิ้งจาก public/images
            if ($book->image && File::exists(public_path('images/' . $book->image))) {
                File::delete(public_path('images/' . $book->image));
            }

            // 2. รับไฟล์ใหม่
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

            // 3. ย้ายไฟล์ไปที่ public/images
            $image->move(public_path('images'), $imageName);

            // 4. บันทึกชื่อไฟล์ใหม่ลงใน array data
            $data['image'] = $imageName;
        }

        // อัปเดตข้อมูลลง Database
        $book->update($data);

        return redirect()->route('books.index')->with('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $book = Books::findOrFail($id);

        if ($book->image && File::exists(public_path('images/' . $book->image))) {
            File::delete(public_path('images/' . $book->image));
        }

        $book->delete();
        return redirect()->route('books.index')->with('success', 'ลบหนังสือเรียบร้อยแล้ว');
    }
}