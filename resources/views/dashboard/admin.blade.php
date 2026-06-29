@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">System Overview</h1>
    <a href="{{ route('reports.index') }}" class="d-none d-sm-inline-block btn btn-primary bg-gradient shadow-sm px-4 py-2 rounded-pill font-weight-bold" style="letter-spacing: 0.5px;">
        <i class="fas fa-download fa-sm text-white-50 me-2"></i> Generate Report
    </a>
</div>

<div class="row">
    <!-- Users Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #4e73df;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-2" style="letter-spacing: 1px;">Total Users (Agents)</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['total_users'] }} <span class="h6 text-muted">({{ $stats['total_agents'] }})</span></div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Tickets Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body position-relative d-flex flex-column justify-content-center p-4">
                <div class="position-absolute" style="top: 0; left: 0; width: 4px; height: 100%; background-color: #36b9cc;"></div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-2" style="letter-spacing: 1px;">Total Tickets</div>
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['total_tickets'] }}</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-ticket-alt fa-lg text-info"></i>
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
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['open_tickets'] }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-clipboard-list fa-lg text-warning"></i>
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
                        <div class="h3 mb-0 font-weight-bold text-dark">{{ $stats['breached_sla'] }}</div>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="fas fa-fire fa-lg text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tickets by Category -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-chart-pie text-primary me-2"></i>Tickets by Category</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($categoryStats as $category => $count)
                        @php
                            $colors = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
                            $color = $colors[$loop->index % count($colors)];
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-0" style="border-bottom: 1px solid #f8f9fc !important; transition: all 0.2s ease;">
                            <span class="text-gray-700 font-weight-medium" style="font-size: 14px;"><i class="fas fa-circle text-{{ $color }} me-3" style="font-size: 10px;"></i> {{ $category }}</span>
                            <span class="badge bg-secondary rounded-pill px-3 py-1 text-white shadow-sm font-weight-bold" style="font-size: 0.75rem;">{{ $count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-5 border-0 text-muted">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-chart-bar text-primary mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                <p class="mb-0">No data available</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Top Performing Agents -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-trophy text-warning me-2"></i>Top Performing Agents</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topAgents as $agent)
                        @php
                            $colors = ['danger', 'success', 'warning', 'info', 'primary'];
                            $color = $colors[$loop->index % count($colors)];
                            $initials = collect(explode(' ', $agent->name))->map(fn($w) => substr($w, 0, 1))->take(2)->join('');
                        @endphp
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-0" style="border-bottom: 1px solid #f8f9fc !important; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-{{ $color }} bg-gradient shadow-sm text-white d-flex align-items-center justify-content-center me-3 font-weight-bold" style="width: 44px; height: 44px; font-size: 14px;">
                                    {{ strtoupper($initials) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 15px;">{{ $agent->name }}</h6>
                                    <small class="text-muted font-weight-medium">Information Technology</small>
                                </div>
                            </div>
                            <span class="badge bg-success bg-gradient rounded-pill text-white px-3 py-2 shadow-sm" style="font-size: 0.75rem; font-weight: 600;">{{ $agent->resolved_count }} Resolved</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-5 border-0 text-muted">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <i class="fas fa-users text-primary mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                <p class="mb-0">No agents found.</p>
                            </div>
                        </li>
                    @endforelse
                </ul>
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
