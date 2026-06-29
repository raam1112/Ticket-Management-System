@extends('layouts.app')

@section('title', $ticket->reference_number . ' - ' . $ticket->title)

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        {{ $ticket->reference_number }}
        <span class="badge {{ $ticket->status_badge_class }} ms-2" style="font-size: 14px;">{{ $ticket->status_label }}</span>
        @if($ticket->is_sla_breached)
            <span class="badge bg-danger ms-1" style="font-size: 12px;"><i class="fas fa-exclamation-triangle"></i> SLA Breached</span>
        @endif
    </h1>
    <div>
        @if($ticket->canBeCancelledBy(auth()->user()))
            <form action="{{ route('tickets.cancel', $ticket) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('Are you sure you want to cancel this ticket?');">Cancel Ticket</button>
            </form>
        @endif
        @if(auth()->user()->hasAnyRole(['admin']) || (auth()->user()->id === $ticket->created_by && $ticket->status === 'open'))
            <a href="{{ route('tickets.edit', $ticket) }}" class="btn btn-sm btn-secondary shadow-sm ms-2"><i class="fas fa-edit fa-sm text-white-50"></i> Edit</a>
        @endif
        <a href="{{ route('tickets.index') }}" class="btn btn-sm btn-light shadow-sm ms-2 border"><i class="fas fa-arrow-left fa-sm text-secondary"></i> Back</a>
    </div>
</div>

<div class="row mb-1">
    <div class="col-12">
        <x-ticket-tracker :ticket="$ticket" />
    </div>
</div>

<div class="row">
    <!-- Main Content: Ticket Body & Comments -->
    <div class="col-lg-8">
        
        <!-- Ticket Description -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ $ticket->title }}</h6>
                <div class="text-muted small">
                    Created {{ $ticket->created_at->format('M d, Y H:i') }} by {{ $ticket->creator->name }}
                </div>
            </div>
            <div class="card-body">
                <div class="ticket-description" style="white-space: pre-wrap;">{{ $ticket->description }}</div>
                
                @if($ticket->attachments->whereNull('comment_id')->count() > 0)
                    <hr>
                    <h6 class="font-weight-bold">Attachments:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($ticket->attachments->whereNull('comment_id') as $attachment)
                            <a href="{{ route('attachments.download', $attachment) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas {{ $attachment->is_image ? 'fa-image' : 'fa-paperclip' }}"></i> {{ $attachment->original_name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Communications / Comments -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Conversation History</h6>
            </div>
            <div class="card-body p-0">
                
                <div class="p-4" style="max-height: 600px; overflow-y: auto;">
                    @php
                        // Merge public and internal comments (already loaded), sort by created_at
                        $allComments = $ticket->publicComments->concat($internalComments ?? [])->sortBy('created_at');
                    @endphp

                    @forelse($allComments as $comment)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <img class="rounded-circle" src="{{ $comment->author->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($comment->author->name).'&background=random' }}" alt="{{ $comment->author->name }}" style="width: 40px; height: 40px;">
                            </div>
                            <div class="flex-grow-1 ms-3 comment-box {{ $comment->is_internal ? 'comment-internal' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>
                                        {{ $comment->author->name }} 
                                        @if($comment->is_internal)
                                            <span class="badge bg-warning text-dark ms-2"><i class="fas fa-lock text-dark"></i> Internal Note</span>
                                        @endif
                                    </strong>
                                    <span class="text-muted small">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <div style="white-space: pre-wrap;">{{ $comment->body }}</div>
                                
                                @if($comment->attachments->count() > 0)
                                    <div class="mt-2 pt-2 border-top">
                                        @foreach($comment->attachments as $attachment)
                                            <a href="{{ route('attachments.download', $attachment) }}" class="badge bg-secondary text-decoration-none me-1 p-2">
                                                <i class="fas fa-paperclip"></i> {{ $attachment->original_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">No comments yet.</div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                @if(in_array($ticket->status, ['open','assigned','in_progress','pending_user','reopened','under_review','escalated']))
                    <div class="p-4 border-top bg-light">
                        <form action="{{ route('comments.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <textarea name="body" class="form-control" rows="3" required placeholder="Type your reply here..."></textarea>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <input type="file" name="attachments[]" class="form-control form-control-sm" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                                </div>
                                <div>
                                    @if(auth()->user()->hasAnyRole(['admin','team_lead','agent']))
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" name="is_internal" id="isInternal" value="1">
                                            <label class="form-check-label text-warning font-weight-bold" for="isInternal"><i class="fas fa-lock"></i> Internal Note</label>
                                        </div>
                                    @endif
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-reply"></i> Send Reply</button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-3 bg-light text-center border-top">
                        <span class="text-muted"><i class="fas fa-lock"></i> This ticket is {{ $ticket->status }}. No new comments can be added.</span>
                        @if($ticket->status === 'resolved' && (auth()->user()->id === $ticket->created_by || auth()->user()->hasRole('admin')))
                            <br><br>
                            <form action="{{ route('tickets.reopen', $ticket) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">Reopen Ticket</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar: Properties & Actions -->
    <div class="col-lg-4">
        
        <!-- Workflow Actions (Agents/Admins) -->
        @if(auth()->user()->hasAnyRole(['admin','team_lead','agent']))
            <div class="card shadow mb-4 border-left-success">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Agent Actions</h6>
                </div>
                <div class="card-body">
                    
                    @if($ticket->status === 'assigned' && $ticket->assigned_to === auth()->id())
                        <form action="{{ route('tickets.accept', $ticket) }}" method="POST" class="mb-2">
                            @csrf
                            <button class="btn btn-success w-100"><i class="fas fa-check"></i> Accept Assignment</button>
                        </form>
                        <form action="{{ route('tickets.reject', $ticket) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason for rejection" required>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                            </div>
                        </form>
                        <hr>
                    @endif

                    @if(auth()->user()->hasAnyRole(['admin','team_lead']) && $ticket->status === 'open')
                        <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="mb-3">
                            @csrf
                            <label class="form-label small font-weight-bold text-primary mb-2"><i class="fas fa-sitemap"></i> {{ $ticket->assigned_to ? 'Reassign Ticket' : 'Smart Assignment Panel' }}</label>
                            
                            <div class="list-group mb-3 shadow-sm border-0 rounded-3" style="max-height: 250px; overflow-y: auto;">
                                @foreach($agents as $agent)
                                    @php
                                        $isHidden = $agent->availability_status === 'offline' || $agent->availability_status === 'on_leave';
                                        $isFull = $agent->active_tickets_count >= $agent->agent_capacity;
                                        if ($isHidden) continue;
                                        
                                        $statusBadge = match($agent->availability_status) {
                                            'available' => '<span class="badge bg-success rounded-pill" style="font-size: 0.6rem;">Available</span>',
                                            'busy' => '<span class="badge bg-warning text-dark rounded-pill" style="font-size: 0.6rem;">Busy</span>',
                                            default => ''
                                        };
                                        $capacityBadge = $isFull ? '<span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.6rem;">At Capacity</span>' : '';
                                    @endphp
                                    <label class="list-group-item list-group-item-action d-flex align-items-center p-2" style="cursor: pointer; transition: all 0.2s; border-left: 3px solid {{ $agent->availability_status === 'available' ? '#1cc88a' : '#f6c23e' }};">
                                        <input class="form-check-input me-3" type="radio" name="agent_id" value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'checked' : '' }} required>
                                        <img src="{{ $agent->avatar_url }}" class="rounded-circle me-2 shadow-sm" width="36" height="36" style="border: 2px solid #fff;">
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="font-weight-bold text-dark text-truncate" style="font-size: 0.85rem;">{{ $agent->name }}</span>
                                                <div>{!! $statusBadge !!} {!! $capacityBadge !!}</div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-muted text-truncate" style="font-size: 0.75rem;">{{ $agent->department?->name ?? 'No Dept' }}</small>
                                                <small class="text-primary font-weight-bold" style="font-size: 0.75rem;">{{ $agent->active_tickets_count }}/{{ $agent->agent_capacity }} Tickets</small>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            
                            <button class="btn btn-sm btn-primary w-100 font-weight-bold shadow-sm rounded-pill"><i class="fas fa-paper-plane me-1"></i> {{ $ticket->assigned_to ? 'Reassign Ticket' : 'Assign Ticket' }}</button>
                        </form>
                        <hr>
                    @endif

                    @if(in_array($ticket->status, ['open','assigned','in_progress','pending_user','reopened','under_review','escalated']))
                        <button class="btn btn-outline-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#statusModal">Update Status</button>
                        
                        <button class="btn btn-outline-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#escalateModal">Escalate Ticket</button>

                        <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#resolveModal">Resolve Ticket</button>
                    @endif

                    @if($ticket->status === 'resolved' && auth()->user()->hasAnyRole(['admin','team_lead']))
                        <form action="{{ route('tickets.close', $ticket) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-dark w-100">Close Ticket</button>
                        </form>
                    @endif

                </div>
            </div>
        @endif

        <!-- Properties -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Properties</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted d-block small font-weight-bold">Category</span>
                    <span>{{ $ticket->category?->name ?? 'None' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-muted d-block small font-weight-bold">Priority</span>
                    @if($ticket->priority)
                        <span style="color: {{ $ticket->priority->color }}"><i class="fas {{ $ticket->priority->icon }}"></i> {{ $ticket->priority->display_name }}</span>
                    @else
                        <span>None</span>
                    @endif
                </div>
                <div class="mb-3">
                    <span class="text-muted d-block small font-weight-bold">Assigned To</span>
                    <span>
                        @if($ticket->assignee)
                            <img src="{{ $ticket->assignee->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($ticket->assignee->name).'&background=random' }}" class="rounded-circle mr-1" style="width:20px;">
                            {{ $ticket->assignee->name }}
                        @else
                            <span class="badge bg-secondary">Unassigned</span>
                        @endif
                    </span>
                </div>
                <div class="mb-3">
                    <span class="text-muted d-block small font-weight-bold">Department</span>
                    <span>{{ $ticket->department?->name ?? 'None' }}</span>
                </div>
                <div class="mb-0">
                    <span class="text-muted d-block small font-weight-bold">Tags</span>
                    @if($ticket->tags)
                        @foreach(is_array($ticket->tags) ? $ticket->tags : json_decode($ticket->tags, true) as $tag)
                            <span class="badge bg-light text-dark border">{{ trim($tag) }}</span>
                        @endforeach
                    @else
                        <span class="text-muted small">No tags</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- SLA Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">SLA Status</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="small font-weight-bold text-muted mb-1">First Response</div>
                        @if($ticket->sla_response_met === 1)
                            <span class="text-success"><i class="fas fa-check-circle"></i> Met ({{ $ticket->first_response_at->format('M d, H:i') }})</span>
                        @elseif($ticket->sla_response_met === 0)
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Breached</span>
                        @else
                            @if($ticket->sla_response_at)
                                @if(now()->gt($ticket->sla_response_at))
                                    <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Breached (Due: {{ $ticket->sla_response_at->format('M d, H:i') }})</span>
                                @else
                                    <span class="text-warning"><i class="fas fa-clock"></i> Due: {{ $ticket->sla_response_at->format('M d, H:i') }}</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        @endif
                    </li>
                    <li class="list-group-item">
                        <div class="small font-weight-bold text-muted mb-1">Resolution</div>
                        @if($ticket->sla_resolve_met === 1)
                            <span class="text-success"><i class="fas fa-check-circle"></i> Met ({{ $ticket->resolved_at->format('M d, H:i') }})</span>
                        @elseif($ticket->sla_resolve_met === 0)
                            <span class="text-danger"><i class="fas fa-times-circle"></i> Breached</span>
                        @else
                            @if($ticket->sla_resolve_at)
                                @if(now()->gt($ticket->sla_resolve_at))
                                    <span class="text-danger"><i class="fas fa-exclamation-circle"></i> Breached (Due: {{ $ticket->sla_resolve_at->format('M d, H:i') }})</span>
                                @else
                                    <span class="text-warning"><i class="fas fa-clock"></i> Due: {{ $ticket->sla_resolve_at->diffForHumans() }}</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        @endif
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- Modals for Agent Actions -->
@if(auth()->user()->hasAnyRole(['admin','team_lead','agent']))

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.status', $ticket) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $allowed = config('etms.status_transitions')[$ticket->status] ?? [];
                    @endphp
                    @if(empty($allowed))
                        <div class="alert alert-warning">No valid status transitions from the current status.</div>
                    @else
                        <div class="mb-3">
                            <label class="form-label">New Status</label>
                            <select name="status" class="form-select" required>
                                @foreach($allowed as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal Note (Optional)</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if(!empty($allowed))
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resolve Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.resolve', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Resolve Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Resolution Note (Sent to user)</label>
                        <textarea name="resolution_note" class="form-control" rows="4" required minlength="10"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Resolved</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Escalate Modal -->
<div class="modal fade" id="escalateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tickets.escalate', $ticket) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-level-up-alt"></i> Escalate Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Escalating a ticket notifies Team Leads and Administrators. The ticket status will change to "Escalated".
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Escalation</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Escalate Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endif
@endsection
