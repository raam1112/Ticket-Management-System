<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(protected SlaService $slaService) {}

    public function index()
    {
        $user = auth()->user();
        $role = $user->primary_role;

        return match ($role) {
            'admin'     => $this->adminDashboard(),
            'team_lead' => $this->teamLeadDashboard($user),
            'agent'     => $this->agentDashboard($user),
            default     => $this->userDashboard($user),
        };
    }

    // ─── User Dashboard ───────────────────────────────────────────────────────

    private function userDashboard(User $user)
    {
        $stats = [
            'total'    => Ticket::where('created_by', $user->id)->count(),
            'open'     => Ticket::where('created_by', $user->id)->whereIn('status', ['open', 'assigned', 'in_progress', 'reopened'])->count(),
            'resolved' => Ticket::where('created_by', $user->id)->where('status', 'resolved')->count(),
            'closed'   => Ticket::where('created_by', $user->id)->where('status', 'closed')->count(),
            'pending'  => Ticket::where('created_by', $user->id)->where('status', 'pending_user')->count(),
        ];

        $recentTickets = Ticket::where('created_by', $user->id)
            ->with(['category', 'priority'])
            ->latest()->take(10)->get();

        $activeTicket = Ticket::where('created_by', $user->id)
            ->whereNotIn('status', ['closed', 'resolved'])
            ->latest('updated_at')
            ->first();

        return view('dashboard.user', compact('stats', 'recentTickets', 'activeTicket'));
    }

    // ─── Agent Dashboard ──────────────────────────────────────────────────────

    private function agentDashboard(User $user)
    {
        $stats = [
            'assigned' => Ticket::where('assigned_to', $user->id)->count(),
            'open'     => Ticket::where('assigned_to', $user->id)->whereIn('status', ['assigned', 'in_progress', 'reopened'])->count(),
            'resolved' => Ticket::where('assigned_to', $user->id)->where('status', 'resolved')->count(),
            'breached' => $this->slaService->getBreachedTicketsQuery()->where('assigned_to', $user->id)->count(),
        ];

        $recentTickets = Ticket::where('assigned_to', $user->id)
            ->with(['category', 'priority', 'creator'])
            ->latest()->take(10)->get();

        return view('dashboard.agent', compact('stats', 'recentTickets'));
    }

    // ─── Team Lead Dashboard ──────────────────────────────────────────────────

    private function teamLeadDashboard(User $user)
    {
        $stats = [
            'unassigned' => Ticket::whereNull('assigned_to')->count(),
            'open'       => Ticket::open()->count(),
            'escalated'  => Ticket::where('escalation_count', '>', 0)->count(),
            'breached'   => $this->slaService->getBreachedTicketsQuery()->count(),
        ];

        $recentEscalations = Ticket::where('escalation_count', '>', 0)
            ->with(['category', 'priority', 'assignee'])
            ->latest()->take(5)->get();

        $agentAvailability = User::byRole('agent')
            ->active()
            ->with(['department'])
            ->withCount(['assignedTickets as active_tickets_count' => function ($q) {
                $q->whereNotIn('status', ['resolved', 'closed', 'cancelled']);
            }])
            ->get()
            ->groupBy('availability_status');

        return view('dashboard.team_lead', compact('stats', 'recentEscalations', 'agentAvailability'));
    }

    // ─── Admin Dashboard ──────────────────────────────────────────────────────

    private function adminDashboard()
    {
        $stats = [
            'total_tickets' => Ticket::count(),
            'open_tickets'  => Ticket::open()->count(),
            'unassigned'    => Ticket::whereNull('assigned_to')->count(),
            'breached_sla'  => $this->slaService->getBreachedTicketsQuery()->count(),
            'total_users'   => User::count(),
            'total_agents'  => User::byRole('agent')->count(),
        ];

        $agentAvailability = User::byRole('agent')
            ->active()
            ->with(['department'])
            ->withCount(['assignedTickets as active_tickets_count' => function ($q) {
                $q->whereNotIn('status', ['resolved', 'closed', 'cancelled']);
            }])
            ->get()
            ->groupBy('availability_status');

        // Category distribution
        $categoryStats = DB::table('tickets')
            ->join('ticket_categories', 'tickets.category_id', '=', 'ticket_categories.id')
            ->select('ticket_categories.name', DB::raw('count(*) as count'))
            ->groupBy('ticket_categories.name')
            ->pluck('count', 'name')->toArray();

        // Priority distribution
        $priorityStats = DB::table('tickets')
            ->join('ticket_priorities', 'tickets.priority_id', '=', 'ticket_priorities.id')
            ->select('ticket_priorities.name', DB::raw('count(*) as count'))
            ->groupBy('ticket_priorities.name')
            ->pluck('count', 'name')->toArray();

        // Volume trend (last 7 days)
        $volumeTrend = DB::table('tickets')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')->toArray();

        // Top agents by resolved tickets
        $topAgents = User::byRole('agent')
            ->withCount(['assignedTickets as resolved_count' => function ($q) {
                $q->where('status', 'resolved');
            }])
            ->orderByDesc('resolved_count')
            ->take(5)->get();

        return view('dashboard.admin', compact(
            'stats', 'categoryStats', 'priorityStats', 'volumeTrend', 'topAgents', 'agentAvailability'
        ));
    }
}
