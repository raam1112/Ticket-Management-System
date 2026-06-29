<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Services\SlaService;
use App\Notifications\TicketNotification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        // Validate who can post internal notes
        $isInternal = $request->boolean('is_internal');
        if ($isInternal && !$user->hasAnyRole(['agent', 'team_lead', 'admin'])) {
            $isInternal = false;
        }

        $request->validate([
            'body'      => 'required|string|min:2|max:5000',
            'parent_id' => 'nullable|exists:ticket_comments,id',
        ]);

        // Sanitize body
        $body = htmlspecialchars($request->body, ENT_QUOTES, 'UTF-8');

        $comment = $ticket->allComments()->create([
            'user_id'     => $user->id,
            'body'        => $body,
            'is_internal' => $isInternal,
            'parent_id'   => $request->parent_id,
        ]);

        // Handle attached files
        if ($request->hasFile('attachments')) {
            $allowedMimes = config('etms.uploads.allowed_mimes', []);
            foreach ($request->file('attachments') as $file) {
                if (!$file->isValid()) continue;
                $filename = \Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path     = $file->storeAs('tickets/' . $ticket->id . '/comments', $filename, 'local');

                $ticket->attachments()->create([
                    'comment_id'    => $comment->id,
                    'uploaded_by'   => $user->id,
                    'filename'      => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                    'disk'          => 'local',
                    'path'          => $path,
                ]);
            }
        }

        // Record first response SLA for agents
        if ($user->hasAnyRole(['agent', 'team_lead', 'admin']) && !$isInternal) {
            app(SlaService::class)->recordFirstResponse($ticket);
        }

        // Log history
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'actor_id'  => $user->id,
            'action'    => $isInternal ? 'note_added' : 'comment_added',
        ]);

        // Update ticket last activity
        $ticket->touch();

        // Dispatch notifications
        if (!$isInternal) {
            if ($user->hasRole('user') && $ticket->assignee) {
                // User commented, notify Agent
                $ticket->assignee->notify(new TicketNotification($ticket, 'Comment Added', "{$user->name} added a comment to your ticket."));
            } elseif ($user->hasAnyRole(['agent', 'team_lead', 'admin']) && $ticket->creator) {
                // Agent commented, notify User
                $ticket->creator->notify(new TicketNotification($ticket, 'Comment Added', "{$user->name} replied to your ticket."));
            }
        }

        $comment->load('author', 'attachments');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id'          => $comment->id,
                    'body'        => $comment->body,
                    'is_internal' => $comment->is_internal,
                    'author'      => $comment->author->name,
                    'avatar_url'  => $comment->author->avatar_url,
                    'created_at'  => $comment->created_at->diffForHumans(),
                    'attachments' => $comment->attachments->map(fn($a) => [
                        'id'           => $a->id,
                        'original_name'=> $a->original_name,
                        'human_size'   => $a->human_size,
                        'download_url' => $a->download_url,
                        'is_image'     => $a->is_image,
                    ]),
                ],
            ], 201);
        }

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Comment added.');
    }

    public function destroy(TicketComment $comment)
    {
        $user = auth()->user();
        $canDelete = $user->hasAnyRole(['admin', 'team_lead']) || $comment->user_id === $user->id;
        abort_unless($canDelete, 403);

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Comment deleted.');
    }
}
