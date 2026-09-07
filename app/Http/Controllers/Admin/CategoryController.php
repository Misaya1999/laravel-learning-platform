<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function category()
    {
        $category = Category::latest()->paginate(15);

        return view('Admin.Category', compact('category'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:category,name',
        ]);

        Category::create($data);

        return back()->with('success', 'Thêm category thành công.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:category,name,' . $category->id,
        ]);

        $category->update($data);

        return back()->with('success', 'Cập nhật category thành công.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Xóa category thành công.');
    }
}
