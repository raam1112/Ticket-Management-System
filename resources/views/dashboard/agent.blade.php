@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Agent Dashboard</h1>
</div>

<div class="row">
    <!-- Assigned Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #4e73df;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-2" style="letter-spacing: 1px;">Assigned Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['assigned'] }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-tasks fa-lg text-primary"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-2" style="letter-spacing: 1px;">Open & In Progress</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['open'] }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-clock fa-lg text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Breached Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #e74a3b;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-2" style="letter-spacing: 1px;">SLA Breached</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['breached'] }}</div>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
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
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-tasks text-primary me-2"></i>Recent Assigned Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="px-4 py-3 border-0 rounded-top-left">ID</th>
                                <th class="py-3 border-0">Title</th>
                                <th class="py-3 border-0">Creator</th>
                                <th class="py-3 border-0 text-center">Priority</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="px-4 py-3 border-0 rounded-top-right">SLA Due</th>
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
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3 font-weight-bold shadow-sm" style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ substr($ticket->creator->name ?? 'U', 0, 1) }}
                                            </div>
                                            <span class="text-gray-700 font-weight-medium">{{ $ticket->creator->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-{{ $priorityColor }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $ticket->priority->name ?? 'None' }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-{{ $statusColor }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                                    </td>
                                    <td class="px-4 py-3 border-bottom-0 text-gray-500 font-weight-medium">
                                        @if($ticket->sla_resolve_at)
                                            <i class="fas fa-clock me-2 text-primary"></i> {{ $ticket->sla_resolve_at->diffForHumans() }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <h5>All Caught Up!</h5>
                                            <p class="mb-0">There are currently no assigned tickets.</p>
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
