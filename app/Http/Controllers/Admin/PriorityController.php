<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index()
    {
        return view('admin.priorities.index', ['priorities' => TicketPriority::orderBy('sort_order')->get()]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:50|unique:ticket_priorities,name',
            'display_name'       => 'required|string',
            'color'              => 'required|string|size:7',
            'icon'               => 'nullable|string|max:50',
            'sla_hours_response' => 'required|integer|min:1',
            'sla_hours_resolve'  => 'required|integer|min:1',
            'sort_order'         => 'nullable|integer',
            'is_active'          => 'boolean',
        ]);
        TicketPriority::create($request->only([
            'name', 'display_name', 'color', 'icon',
            'sla_hours_response', 'sla_hours_resolve', 'sort_order', 'is_active',
        ]));
        return back()->with('success','Priority created.');
    }
    public function update(Request $request, TicketPriority $priority)
    {
        $priority->update($request->only('display_name','color','sla_hours_response','sla_hours_resolve','sort_order','is_active'));
        return back()->with('success','Priority updated.');
    }
    public function destroy(TicketPriority $priority) { $priority->delete(); return back()->with('success','Deleted.'); }
}
