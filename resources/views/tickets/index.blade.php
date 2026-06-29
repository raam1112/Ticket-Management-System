@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tickets</h1>
    <a href="{{ route('tickets.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Create Ticket
    </a>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter"></i> Filter Tickets</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('tickets.index') }}" method="GET" class="row gx-3 gy-2 align-items-center">
            
            <div class="col-sm-3">
                <label class="visually-hidden">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    @foreach(['open','assigned','in_progress','pending_user','escalated','under_review','resolved','closed','reopened','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-sm-3">
                <label class="visually-hidden">Category</label>
                <select class="form-select" name="category_id">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-sm-2">
                <label class="visually-hidden">Priority</label>
                <select class="form-select" name="priority_id">
                    <option value="">All Priorities</option>
                    @foreach($priorities as $pri)
                        <option value="{{ $pri->id }}" {{ request('priority_id') == $pri->id ? 'selected' : '' }}>{{ $pri->display_name }}</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->hasAnyRole(['admin', 'team_lead']))
            <div class="col-sm-2">
                <label class="visually-hidden">Agent</label>
                <select class="form-select" name="assigned_to">
                    <option value="">All Agents</option>
                    <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('assigned_to') == $agent->id ? 'selected' : '' }}>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Ticket List -->
<div class="card shadow mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ref #</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Category</th>
                        @if(auth()->user()->hasAnyRole(['admin','team_lead','agent']))
                        <th>Creator</th>
                        @endif
                        <th>Assigned To</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="ticket-row" onclick="window.location='{{ route('tickets.show', $ticket) }}'">
                            <td class="font-weight-bold">{{ $ticket->reference_number }}</td>
                            <td>
                                {{ Str::limit($ticket->title, 40) }}
                                @if($ticket->is_sla_breached)
                                    <i class="fas fa-exclamation-triangle text-danger ms-1" title="SLA Breached"></i>
                                @endif
                            </td>
                            <td><span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span></td>
                            <td>
                                @if($ticket->priority)
                                    <span style="color: {{ $ticket->priority->color }}"><i class="fas {{ $ticket->priority->icon }}"></i> {{ $ticket->priority->display_name }}</span>
                                @else
                                    <span class="text-muted">None</span>
                                @endif
                            </td>
                            <td>{{ $ticket->category?->name ?? 'None' }}</td>
                            @if(auth()->user()->hasAnyRole(['admin','team_lead','agent']))
                            <td>{{ $ticket->creator?->name }}</td>
                            @endif
                            <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                            <td class="small">{{ $ticket->created_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No tickets found matching your criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-white pt-3 border-0">
        {{ $tickets->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
