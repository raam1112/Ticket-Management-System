<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketPriority;
use App\Services\SlaService;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function __construct(protected SlaService $slaService) {}

    public function index()
    {
        $policies   = SlaPolicy::with(['category', 'priority'])->get();
        $categories = TicketCategory::active()->get();
        $priorities = TicketPriority::active()->get();
        return view('admin.sla.index', compact('policies', 'categories', 'priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                    => 'required|string|max:150',
            'category_id'             => 'nullable|exists:ticket_categories,id',
            'priority_id'             => 'nullable|exists:ticket_priorities,id',
            'response_time_hours'     => 'required|integer|min:1',
            'resolution_time_hours'   => 'required|integer|min:1',
            'escalation_after_hours'  => 'required|integer|min:0',
            'business_hours_only'     => 'boolean',
        ]);

        SlaPolicy::create($request->only([
            'name', 'category_id', 'priority_id',
            'response_time_hours', 'resolution_time_hours',
            'escalation_after_hours', 'business_hours_only', 'is_active',
        ]));
        return redirect()->route('admin.sla.index')->with('success', 'SLA policy created.');
    }

    public function update(Request $request, SlaPolicy $slaPolic)
    {
        $request->validate([
            'response_time_hours'    => 'required|integer|min:1',
            'resolution_time_hours'  => 'required|integer|min:1',
            'escalation_after_hours' => 'required|integer|min:0',
        ]);
        $slaPolic->update($request->only('name','response_time_hours','resolution_time_hours','escalation_after_hours','business_hours_only','is_active'));
        return back()->with('success', 'SLA policy updated.');
    }

    public function destroy(SlaPolicy $slaPolic)
    {
        $slaPolic->delete();
        return back()->with('success', 'SLA policy deleted.');
    }

    public function monitor()
    {
        $slaAtRisk    = $this->slaService->getAtRiskTickets(2);
        $slaBreached  = $this->slaService->getBreachedTickets();
        $compliance   = $this->slaService->getComplianceRate();

        return view('admin.sla.monitor', compact('slaAtRisk', 'slaBreached', 'compliance'));
    }
}
