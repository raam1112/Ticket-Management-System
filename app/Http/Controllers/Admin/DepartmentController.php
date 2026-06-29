<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return view('admin.departments.index', ['departments' => Department::withCount('users')->get()]);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'code' => 'required|string|max:20|unique:departments,code']);
        Department::create($request->only('name','code','description') + ['is_active' => true]);
        return back()->with('success', 'Department created.');
    }
    public function update(Request $request, Department $department)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $department->update($request->only('name','description','is_active'));
        return back()->with('success', 'Department updated.');
    }
    public function destroy(Department $department) { $department->delete(); return back()->with('success','Deleted.'); }
}
