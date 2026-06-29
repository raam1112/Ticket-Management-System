<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = TicketCategory::orderBy('sort_order')->get();
        return view('admin.categories.index', compact('categories'));
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'icon' => 'nullable|string', 'color' => 'nullable|string|size:7']);
        $slug = \Str::slug($request->name);
        TicketCategory::create(array_merge($request->only('name','description','icon','color','sort_order'), ['slug' => $slug, 'is_active' => true]));
        return back()->with('success', 'Category created.');
    }
    public function update(Request $request, TicketCategory $category)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $category->update($request->only('name','description','icon','color','sort_order','is_active'));
        return back()->with('success', 'Category updated.');
    }
    public function destroy(TicketCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }
}
