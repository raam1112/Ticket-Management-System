<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['category', 'priority', 'department', 'assignee'])
            ->when(!$request->user()->hasAnyRole(['admin', 'team_lead']), function ($q) use ($request) {
                $q->where('user_id', $request->user()->id)
                  ->orWhere('assigned_to', $request->user()->id);
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(15);
        return response()->json($tickets);
    }

    public function show(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['admin', 'team_lead']) && 
            $ticket->user_id !== $request->user()->id && 
            $ticket->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->load(['category', 'priority', 'department', 'assignee', 'comments.user']);
        return response()->json($ticket);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority_id' => 'required|exists:priorities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $this->ticketService->createTicket(
            $request->all(),
            $request->user()->id
        );

        return response()->json(['message' => 'Ticket created successfully', 'ticket' => $ticket], 201);
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['admin', 'team_lead']) && $ticket->assigned_to !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|string',
            'priority_id' => 'sometimes|exists:priorities,id',
            'department_id' => 'sometimes|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket->update($request->only(['status', 'priority_id', 'department_id']));
        
        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket]);
    }
}
