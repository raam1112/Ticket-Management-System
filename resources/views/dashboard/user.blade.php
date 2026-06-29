@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800" style="font-weight: 300;">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
        <div class="text-muted small mt-2">
            @if(auth()->user()->pronouns)
                <span class="badge bg-secondary bg-opacity-10 text-secondary border me-2 px-2 py-1"><i class="fas fa-user-tag me-1"></i>{{ auth()->user()->pronouns }}</span>
            @endif
            @if(auth()->user()->location)
                <span class="text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i> {{ auth()->user()->location }}</span>
            @endif
        </div>
    </div>
    <a href="{{ route('tickets.create') }}" class="d-none d-sm-inline-block btn btn-primary bg-gradient shadow-sm px-4 py-2 rounded-pill font-weight-bold" style="letter-spacing: 0.5px;">
        <i class="fas fa-plus fa-sm text-white-50 me-2"></i> Create Ticket
    </a>
</div>

<div class="row">
    <!-- Total Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #4e73df;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-2" style="letter-spacing: 1px;">Total Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['total'] }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-ticket-alt fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Open Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #f6c23e;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-2" style="letter-spacing: 1px;">Open Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['open'] }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-folder-open fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resolved Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #1cc88a;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-2" style="letter-spacing: 1px;">Resolved</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['resolved'] }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Closed Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #858796;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-2" style="letter-spacing: 1px;">Closed</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['closed'] }}</div>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-archive fa-lg text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-clock text-primary me-2"></i>Recent Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="px-4 py-3 border-0 rounded-top-left">ID</th>
                                <th class="py-3 border-0">Title</th>
                                <th class="py-3 border-0">Category</th>
                                <th class="py-3 border-0 text-center">Priority</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="px-4 py-3 border-0 rounded-top-right">Created</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentTickets as $ticket)
                                @php
                                    $priorityColor = match(strtolower($ticket->priority->name ?? '')) {
                                        'urgent', 'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'info',
                                        default => 'secondary'
                                    };
                                    $statusColor = match(strtolower($ticket->status)) {
                                        'open' => 'primary',
                                        'in_progress' => 'warning text-dark',
                                        'resolved' => 'success',
                                        'closed' => 'secondary',
                                        default => 'dark'
                                    };
                                @endphp
                                <tr style="transition: all 0.2s ease;">
                                    <td class="px-4 py-3 border-bottom-0"><a href="{{ route('tickets.show', $ticket) }}" class="font-weight-bold text-primary text-decoration-none">#{{ $ticket->reference_number }}</a></td>
                                    <td class="py-3 border-bottom-0">
                                        <span class="text-dark font-weight-bold">{{ Str::limit($ticket->title, 40) }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tag text-muted me-2"></i>
                                            <span class="text-gray-700 font-weight-medium">{{ $ticket->category->name ?? 'None' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-{{ $priorityColor }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $ticket->priority->name ?? 'None' }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-{{ $statusColor }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                    </td>
                                    <td class="px-4 py-3 border-bottom-0 text-gray-500 font-weight-medium">
                                        <i class="far fa-calendar-alt me-2 text-primary"></i> {{ $ticket->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5>All Caught Up!</h5>
                                            <p class="mb-0">There are currently no recent tickets.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
