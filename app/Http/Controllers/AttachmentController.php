<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'attachments'   => 'required|array|max:5',
            'attachments.*' => [
                'file',
                'max:' . (config('etms.uploads.max_size_mb', 10) * 1024),
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip,txt',
            ],
        ]);

        $stored = [];
        foreach ($request->file('attachments') as $file) {
            if (!$file->isValid()) continue;

            $filename = \Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('tickets/' . $ticket->id, $filename, 'local');

            $att = $ticket->attachments()->create([
                'uploaded_by'   => auth()->id(),
                'filename'      => $filename,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'file_size'     => $file->getSize(),
                'disk'          => 'local',
                'path'          => $path,
            ]);
            $stored[] = $att;
        }

        return response()->json(['success' => true, 'attachments' => $stored], 201);
    }

    public function download(TicketAttachment $attachment)
    {
        // Authorization: only allow access to ticket participants
        $ticket = $attachment->ticket;
        $user   = auth()->user();

        $allowed = $user->hasAnyRole(['admin', 'team_lead']) ||
                   $ticket->created_by === $user->id ||
                   $ticket->assigned_to === $user->id;

        abort_unless($allowed, 403);

        if (!Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function destroy(TicketAttachment $attachment)
    {
        $user = auth()->user();
        $canDelete = $user->hasAnyRole(['admin', 'team_lead']) ||
                     $attachment->uploaded_by === $user->id;
        abort_unless($canDelete, 403);

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return response()->json(['success' => true]);
    }
}
