@extends('layouts.app')

@section('title', 'Reporting Center')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Reporting Center</h1>
</div>

<div class="row">
    <!-- Report Generator -->
    <div class="col-lg-4">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-primary text-white d-flex align-items-center" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h6 class="m-0 font-weight-bold" style="font-size: 16px;"><i class="fas fa-file-export me-2"></i> Generate New Report</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Report Name</label>
                        <input type="text" name="name" class="form-control" style="border-radius: 8px;" required placeholder="e.g. Q3 Ticket Summary">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Report Type</label>
                        <select name="type" class="form-select" style="border-radius: 8px;" required>
                            <option value="tickets">Ticket Summary Report</option>
                            <option value="open_tickets">Open Ticket Report</option>
                            <option value="closed_tickets">Closed Ticket Report</option>
                            @if(auth()->user()->hasAnyRole(['admin', 'team_lead']))
                            <option value="sla">SLA Compliance Report</option>
                            <option value="agents">Agent Performance Report</option>
                            <option value="escalations">Escalation Report</option>
                            <option value="categories">Category-wise Ticket Report</option>
                            <option value="user_activity">User Activity Report</option>
                            <option value="resolution_time">Resolution Time Analysis Report</option>
                            <option value="monthly_stats">Monthly Ticket Statistics Report</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Date Range (Optional)</label>
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;">
                            <span class="input-group-text bg-light text-muted border-0">to</span>
                            <input type="date" name="date_to" class="form-control" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-weight-bold">Format</label>
                        <select name="format" class="form-select" style="border-radius: 8px;" required>
                            <option value="pdf">PDF Document (.pdf)</option>
                            <option value="csv">CSV Data (.csv)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary bg-gradient w-100 rounded-pill py-2 font-weight-bold mt-2 shadow-sm" style="letter-spacing: 0.5px;">
                        <i class="fas fa-cogs me-2"></i> Generate Report
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Generated Reports History -->
    <div class="col-lg-8">
        <div class="card shadow-lg mb-4" style="border-radius: 16px; border: none; overflow: hidden;">
            <div class="card-header py-4 bg-white d-flex align-items-center justify-content-between" style="border-bottom: 1px solid #f1f3f9;">
                <h6 class="m-0 font-weight-bold text-primary" style="font-size: 16px;"><i class="fas fa-history text-primary me-2"></i>Generated Reports History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                        <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="px-4 py-3 border-0 rounded-top-left">Report Name</th>
                                <th class="py-3 border-0">Type</th>
                                <th class="py-3 border-0 text-center">Format</th>
                                <th class="py-3 border-0 text-center">Status</th>
                                <th class="py-3 border-0">Generated At</th>
                                <th class="px-4 py-3 border-0 text-center rounded-top-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($reports as $report)
                                <tr style="transition: all 0.2s ease;">
                                    <td class="px-4 py-3 border-bottom-0">
                                        <span class="text-dark font-weight-bold">{{ $report->name }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-gray-700 font-weight-medium">
                                        {{ ucfirst(str_replace('_', ' ', $report->type)) }}
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 py-1 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $report->format }}</span>
                                    </td>
                                    <td class="py-3 border-bottom-0 text-center">
                                        @if($report->status === 'completed')
                                            <span class="badge bg-success bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Ready</span>
                                        @elseif($report->status === 'processing')
                                            <span class="badge bg-warning text-dark bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;"><i class="fas fa-spinner fa-spin me-1"></i> Processing</span>
                                        @else
                                            <span class="badge bg-danger bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Failed</span>
                                        @endif
                                    </td>
                                    <td class="py-3 border-bottom-0 text-gray-500 font-weight-medium">
                                        <i class="far fa-calendar-alt me-2 text-primary"></i> {{ $report->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 border-bottom-0 text-center">
                                        @if($report->status === 'completed' && $report->file_path)
                                            <a href="{{ route('reports.download', $report) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1 bg-gradient shadow-sm font-weight-bold" style="font-size: 0.75rem;"><i class="fas fa-download me-1"></i> Download</a>
                                        @else
                                            <button class="btn btn-sm btn-light rounded-pill px-3 py-1 text-muted font-weight-bold" style="font-size: 0.75rem;" disabled>Unavailable</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="fas fa-clipboard-list text-primary mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <h5>No Reports Yet</h5>
                                            <p class="mb-0">You haven't generated any reports yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reports->hasPages())
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-center">
                    {{ $reports->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
