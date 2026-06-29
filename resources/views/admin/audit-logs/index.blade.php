@extends('layouts.app')

@section('title', 'System Audit Logs')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800" style="font-weight: 300;">System Audit Logs</h1>
</div>

<div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
    <div class="card-header py-4 bg-white border-bottom-0">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row gx-2 gy-2 align-items-center">
            <div class="col-sm-3">
                <select name="event" class="form-select" style="border-radius: 8px;">
                    <option value="">All Events</option>
                    @foreach($events as $e)
                        <option value="{{ $e }}" {{ request('event') == $e ? 'selected' : '' }}>{{ Str::title(str_replace('_', ' ', $e)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;"><i class="far fa-calendar-alt text-muted"></i></span>
                    <input type="date" name="date_from" class="form-control border-start-0 ps-0" style="border-radius: 0 8px 8px 0;" value="{{ request('date_from') }}">
                </div>
            </div>
            <div class="col-sm-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 8px 0 0 8px;"><i class="far fa-calendar-check text-muted"></i></span>
                    <input type="date" name="date_to" class="form-control border-start-0 ps-0" style="border-radius: 0 8px 8px 0;" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary bg-gradient rounded-pill px-4 shadow-sm font-weight-bold" style="letter-spacing: 0.5px;">Filter</button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-light rounded-pill px-4 shadow-sm ms-1">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 font-monospace small" style="border-collapse: separate; border-spacing: 0;">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="px-4 py-3 border-0 rounded-top-left">Timestamp</th>
                        <th class="py-3 border-0">User</th>
                        <th class="py-3 border-0 text-center">Event</th>
                        <th class="py-3 border-0 text-center">IP Address</th>
                        <th class="px-4 py-3 border-0 rounded-top-right">Context (Entity)</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($logs as $log)
                        <tr style="transition: all 0.2s ease;">
                            <td class="px-4 py-3 border-bottom-0 text-muted"><i class="far fa-clock me-1"></i>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 border-bottom-0 font-weight-bold text-dark">{{ $log->user?->name ?? 'System/Guest' }} <br><span class="text-muted font-weight-normal">{{ $log->user?->email }}</span></td>
                            <td class="py-3 border-bottom-0 text-center"><span class="badge bg-secondary bg-gradient rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">{{ $log->event }}</span></td>
                            <td class="py-3 border-bottom-0 text-center text-muted"><i class="fas fa-network-wired me-1" style="opacity: 0.5;"></i>{{ $log->ip_address }}</td>
                            <td class="px-4 py-3 border-bottom-0">
                                @if($log->auditable_type)
                                    <span class="text-primary font-weight-bold">{{ class_basename($log->auditable_type) }}</span> <span class="text-muted">#{{ $log->auditable_id }}</span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted border-0">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <i class="fas fa-shield-alt text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5>No Audit Logs Found</h5>
                                    <p class="mb-0">System events will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">{{ $logs->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
