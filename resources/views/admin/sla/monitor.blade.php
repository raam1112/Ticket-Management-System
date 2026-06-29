@extends('layouts.app')

@section('title', 'SLA Monitor')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">SLA Live Monitor</h1>
    <a href="{{ route('admin.sla.index') }}" class="btn btn-sm btn-secondary shadow-sm"><i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Policies</a>
</div>

<div class="row">
    <!-- Compliance Score -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Global Compliance Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $compliance }}%</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- At Risk Count -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tickets At Risk (Next 2h)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($slaAtRisk) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Breached Count -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Actively Breached</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ count($slaBreached) }}</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-radiation fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Actively Breached Tickets -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-radiation"></i> Actively Breached Tickets</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Agent</th>
                                <th>Overdue By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slaBreached as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="font-weight-bold text-danger">{{ $ticket->reference_number }}</a><br>
                                        <small>{{ Str::limit($ticket->title, 30) }}</small>
                                    </td>
                                    <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                    <td class="font-weight-bold text-danger">{{ $ticket->sla_resolve_at->diffForHumans(null, true) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No actively breached tickets!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- At Risk Tickets -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-clock"></i> At Risk Tickets (Due soon)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Agent</th>
                                <th>Due In</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slaAtRisk as $ticket)
                                <tr>
                                    <td>
                                        <a href="{{ route('tickets.show', $ticket) }}" class="font-weight-bold text-warning-dark">{{ $ticket->reference_number }}</a><br>
                                        <small>{{ Str::limit($ticket->title, 30) }}</small>
                                    </td>
                                    <td>{{ $ticket->assignee?->name ?? 'Unassigned' }}</td>
                                    <td class="font-weight-bold text-warning-dark">{{ $ticket->sla_resolve_at->diffForHumans(null, true) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-success"><i class="fas fa-check-circle fa-2x mb-2"></i><br>No tickets currently at risk!</td>
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

@push('css')
<style>
    .text-warning-dark { color: #d39e00; }
</style>
@endpush
