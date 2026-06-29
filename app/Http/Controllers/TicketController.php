<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketHistory;
use App\Models\TicketPriority;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\TicketNotification;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService    $ticketService,
        protected AssignmentService $assignmentService
    ) {}

    // ── List Tickets ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Ticket::with(['category', 'priority', 'creator', 'assignee'])
                       ->forUser($user);

        // Filters
        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('priority_id')) $query->where('priority_id', $request->priority_id);
        if ($request->filled('assigned_to')) $query->where('assigned_to', $request->assigned_to);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('reference_number', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $tickets    = $query->paginate(config('etms.pagination.tickets', 20))->withQueryString();
        $categories = TicketCategory::active()->get();
        $priorities = TicketPriority::active()->get();
        $agents     = User::byRole('agent')->active()->get();

        return view('tickets.index', compact('tickets', 'categories', 'priorities', 'agents'));
    }

    // ── Show Create Form ──────────────────────────────────────────────────────

    public function create()
    {
        $categories = TicketCategory::active()->get();
        $priorities = TicketPriority::active()->get();
        return view('tickets.create', compact('categories', 'priorities'));
    }

    // ── Store Ticket ──────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|min:10|max:255',
            'description' => 'required|string|min:20|max:10000',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'tags'        => 'nullable|string',
        ]);

        $validated['tags'] = $request->filled('tags')
            ? array_map('trim', explode(',', $request->tags))
            : null;

        $ticket = $this->ticketService->create($validated, auth()->user());

        // Handle attachments
        if ($request->hasFile('attachments')) {
            $this->storeAttachments($ticket, $request->file('attachments'));
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'ticket' => $ticket->load('category', 'priority')], 201);
        }

        return redirect()->route('tickets.show', $ticket)
                         ->with('success', "Ticket {$ticket->reference_number} created successfully.");
    }

    // ── Show Single Ticket ────────────────────────────────────────────────────

    public function show(Ticket $ticket)
    {
        $this->authorizeTicketView($ticket);

        $ticket->load([
            'category', 'priority', 'creator', 'assignee', 'department',
            'publicComments.author', 'publicComments.replies.author',
            'publicComments.attachments',
            'attachments.uploader',
            'history.actor',
            'escalations.escalatedBy',
        ]);

        // Load internal comments separately for agents/admins/TLs
        $internalComments = [];
        $user = auth()->user();
        if ($user->hasAnyRole(['agent', 'team_lead', 'admin'])) {
            $internalComments = $ticket->allComments()
                ->where('is_internal', true)
                ->with('author')
                ->get();
        }

        $agents = User::byRole('agent')
            ->active()
            ->with(['department'])
            ->withCount(['assignedTickets as active_tickets_count' => function ($q) {
                $q->whereNotIn('status', ['resolved', 'closed', 'cancelled']);
            }])
            ->orderByRaw("CASE availability_status
                WHEN 'available' THEN 1
                WHEN 'busy' THEN 2
                ELSE 3 END")
            ->orderBy('active_tickets_count')
            ->get();

        return view('tickets.show', compact('ticket', 'internalComments', 'agents'));
    }

    // ── Edit Ticket ───────────────────────────────────────────────────────────

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicketEdit($ticket);
        $categories = TicketCategory::active()->get();
        $priorities = TicketPriority::active()->get();
        return view('tickets.edit', compact('ticket', 'categories', 'priorities'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicketEdit($ticket);
        $validated = $request->validate([
            'title'       => 'required|string|min:10|max:255',
            'description' => 'required|string|min:20',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
        ]);
        $oldPriority = $ticket->priority_id;
        $ticket->update($validated);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => auth()->id(),
            'action'    => 'updated',
        ]);

        if ($oldPriority !== $ticket->priority_id) {
            $priorityName = TicketPriority::find($ticket->priority_id)->name;
            if ($ticket->creator) {
                $ticket->creator->notify(new TicketNotification($ticket, 'Priority Changed', "The priority of your ticket has been changed to {$priorityName}."));
            }
            if ($ticket->assignee) {
                $ticket->assignee->notify(new TicketNotification($ticket, 'Priority Changed', "Ticket #{$ticket->reference_number} priority changed to {$priorityName}."));
            }
        } else {
            if ($ticket->creator && $ticket->creator->id !== auth()->id()) {
                $ticket->creator->notify(new TicketNotification($ticket, 'Ticket Updated', "Your ticket has been updated by " . auth()->user()->name . "."));
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket)
    {
        abort_unless(auth()->user()->hasRole('admin'), 403);
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted.');
    }

    // ── Workflow Actions (AJAX-friendly) ──────────────────────────────────────

    public function assign(Request $request, Ticket $ticket)
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'team_lead']), 403);
        $request->validate(['agent_id' => 'required|exists:users,id']);

        $agent = User::findOrFail($request->agent_id);
        $this->assignmentService->assignTo($ticket, $agent, auth()->user(), $request->note);

        return $this->jsonOrRedirect($ticket, 'Ticket assigned to ' . $agent->name);
    }

    public function accept(Ticket $ticket)
    {
        abort_unless(auth()->user()->hasRole('agent') && $ticket->assigned_to === auth()->id(), 403);
        $this->assignmentService->accept($ticket, auth()->user());
        return $this->jsonOrRedirect($ticket, 'Ticket accepted and moved to In Progress.');
    }

    public function reject(Request $request, Ticket $ticket)
    {
        abort_unless(auth()->user()->hasRole('agent') && $ticket->assigned_to === auth()->id(), 403);
        $request->validate(['reason' => 'required|string|max:500']);
        $this->assignmentService->reject($ticket, auth()->user(), $request->reason);
        return $this->jsonOrRedirect($ticket, 'Assignment rejected.');
    }

    public function escalate(Request $request, Ticket $ticket)
    {
        abort_unless(auth()->user()->hasAnyRole(['agent', 'team_lead', 'admin']), 403);
        $request->validate(['reason' => 'required|string|max:1000']);
        $this->ticketService->escalate($ticket, auth()->user(), $request->reason);
        return $this->jsonOrRedirect($ticket, 'Ticket escalated.');
    }

    public function resolve(Request $request, Ticket $ticket)
    {
        abort_unless(auth()->user()->hasAnyRole(['agent', 'team_lead', 'admin']), 403);
        $request->validate(['resolution_note' => 'required|string|min:10|max:2000']);
        $this->ticketService->resolve($ticket, auth()->user(), $request->resolution_note, $request->get('resolution_level', 'standard'));
        return $this->jsonOrRedirect($ticket, 'Ticket resolved successfully.');
    }

    public function close(Ticket $ticket)
    {
        abort_unless(auth()->user()->hasAnyRole(['team_lead', 'admin']), 403);
        $this->ticketService->close($ticket, auth()->user());
        return $this->jsonOrRedirect($ticket, 'Ticket closed.');
    }

    public function reopen(Ticket $ticket)
    {
        $user = auth()->user();
        $canReopen = ($ticket->created_by === $user->id) || $user->hasRole('admin');
        abort_unless($canReopen, 403);

        if (!$this->ticketService->reopen($ticket, $user)) {
            return back()->with('error', 'Ticket cannot be reopened (7-day window has passed).');
        }
        return $this->jsonOrRedirect($ticket, 'Ticket reopened.');
    }

    public function cancel(Ticket $ticket)
    {
        abort_unless($ticket->canBeCancelledBy(auth()->user()), 403);
        $this->ticketService->cancel($ticket, auth()->user());
        return redirect()->route('tickets.index')->with('success', 'Ticket cancelled.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        abort_unless(auth()->user()->hasAnyRole(['agent', 'team_lead', 'admin']), 403);
        $request->validate(['status' => 'required|string']);

        $changed = $this->ticketService->changeStatus($ticket, $request->status, auth()->user(), $request->note);
        $msg = $changed ? 'Status updated.' : 'Invalid status transition.';
        $code = $changed ? 200 : 422;

        if (request()->expectsJson()) {
            return response()->json(['success' => $changed, 'message' => $msg, 'status' => $ticket->fresh()->status], $code);
        }
        return back()->with($changed ? 'success' : 'error', $msg);
    }

    public function history(Ticket $ticket)
    {
        $this->authorizeTicketView($ticket);
        $history = $ticket->history()->with('actor')->get();
        return response()->json($history->map(fn($h) => [
            'description' => $h->description,
            'icon'        => $h->icon,
            'created_at'  => $h->created_at->diffForHumans(),
            'actor'       => $h->actor?->name ?? 'System',
        ]));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function authorizeTicketView(Ticket $ticket): void
    {
        $user = auth()->user();
        $allowed = match ($user->primary_role) {
            'admin'     => true,
            'team_lead' => true,
            'agent'     => $ticket->assigned_to === $user->id || $ticket->created_by === $user->id,
            default     => $ticket->created_by === $user->id,
        };
        abort_unless($allowed, 403);
    }

    protected function authorizeTicketEdit(Ticket $ticket): void
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('admin') ||
            ($ticket->created_by === $user->id && in_array($ticket->status, ['open'])),
            403
        );
    }

    protected function storeAttachments(Ticket $ticket, array $files): void
    {
        $allowedMimes = config('etms.uploads.allowed_mimes', []);
        $maxSizeMb    = config('etms.uploads.max_size_mb', 10);

        foreach ($files as $file) {
            if (!$file->isValid()) continue;
            if ($file->getSize() > $maxSizeMb * 1024 * 1024) continue;
            if (!in_array($file->getMimeType(), $allowedMimes)) continue;

            $filename = \Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('tickets/' . $ticket->id, $filename, 'local');

            $ticket->attachments()->create([
                'uploaded_by'   => auth()->id(),
                'filename'      => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'disk'          => 'local',
                'path'          => $path,
            ]);
        }
    }

    protected function jsonOrRedirect(Ticket $ticket, string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return redirect()->route('tickets.show', $ticket)->with('success', $message);
    }
}
