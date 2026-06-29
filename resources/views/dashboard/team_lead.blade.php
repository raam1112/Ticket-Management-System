@extends('layouts.app')

@section('title', 'Team Lead Dashboard')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">Team Lead Overview</h1>
</div>

<div class="row">
    <!-- Unassigned Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #858796;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-2" style="letter-spacing: 1px;">Unassigned Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['unassigned'] }}</div>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-inbox fa-lg text-secondary"></i>
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

    <!-- Escalated Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #e74a3b;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-2" style="letter-spacing: 1px;">Escalated Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['escalated'] }}</div>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-fire fa-lg text-danger"></i>
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
                        <i class="fas fa-clock fa-lg text-danger"></i>
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
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-fire text-danger me-2"></i>Recent Escalations</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="px-4 py-3 border-0 rounded-top-left">ID</th>
                                <th class="py-3 border-0">Title</th>
                                <th class="py-3 border-0">Assignee</th>
                                <th class="py-3 border-0 text-center">Priority</th>
                                <th class="py-3 border-0 text-center">Escalation Level</th>
                                <th class="px-4 py-3 border-0 rounded-top-right">Created</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($recentEscalations as $ticket)
                                @php
                                    $priorityColor = match(strtolower($ticket->priority->name ?? '')) {
                                        'urgent', 'high' => 'danger',
                                        'medium' => 'warning',
                                        'low' => 'info',
                                        default => 'secondary'
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
                                                {{ substr($ticket->assignee->name ?? 'U', 0, 1) }}
                                            </div>
                                            <span class="text-gray-700 font-weight-medium">{{ $ticket->assignee->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-{{ $priorityColor }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $ticket->priority->name ?? 'None' }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-danger bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;"><i class="fas fa-level-up-alt me-1"></i> Level {{ $ticket->escalation_count }}</span>
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
                                            <p class="mb-0">There are currently no escalated tickets.</p>
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

<div class="row mt-4">
    <div class="col-12">
        <h5 class="font-weight-bold text-dark mb-4">Live Agent Availability</h5>
    </div>
    
    <div id="live-agent-grid" class="row w-100 m-0">
        @include('partials.agent_availability_grid')
    </div>
</div>

@push('js')
<script>
    function fetchLiveAgentAvailability() {
        $.ajax({
            url: "{{ route('dashboard.agent-availability') }}",
            type: "GET",
            success: function(html) {
                $('#live-agent-grid').html(html);
            }
        });
    }
    // Poll every 5 seconds
    setInterval(fetchLiveAgentAvailability, 5000);
</script>
@endpush
@endsection
