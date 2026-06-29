@props(['ticket'])

@if($ticket)
@php
    // Determine the current step based on ticket status
    $status = strtolower($ticket->status);
    $step = 1;
    $isCancelled = ($status === 'cancelled');

    if ($isCancelled) {
        $step = 4;
    } elseif ($status === 'resolved' || $status === 'closed') {
        $step = 4;
    } elseif ($status === 'in_progress') {
        $step = 3;
    } elseif ($ticket->assigned_to) {
        $step = 2; // Assigned but open
    }
@endphp

<div class="card shadow-lg mb-4 ticket-tracker-card" style="border-radius: 16px; border: none; overflow: hidden; background: rgba(32, 33, 36, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1);">
    <div class="card-header py-4 d-flex align-items-center justify-content-between" style="background: transparent; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
        <h6 class="m-0 font-weight-bold" style="font-size: 16px; color: #e8eaed;"><i class="fas fa-satellite-dish me-2" style="color: #8ab4f8;"></i> Live Tracking: #{{ $ticket->reference_number }}</h6>
        <span class="badge {{ $isCancelled ? 'bg-danger' : 'bg-primary' }} bg-gradient rounded-pill px-3 py-2 shadow-sm font-weight-bold text-uppercase" style="font-size: 0.7rem;">{{ $isCancelled ? 'Cancelled Ticket' : 'Active Ticket' }}</span>
    </div>
    <div class="card-body p-5">
        
        <style>
            .tracker-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
                max-width: 800px;
                margin: 0 auto;
            }
            .tracker-line {
                position: absolute;
                top: 24px;
                left: 10%;
                right: 10%;
                height: 4px;
                background: #3c4043;
                z-index: 1;
                border-radius: 2px;
            }
            .tracker-progress {
                position: absolute;
                top: 24px;
                left: 10%;
                height: 4px;
                background: linear-gradient(90deg, #8ab4f8, #c58af9);
                z-index: 2;
                border-radius: 2px;
                transition: width 1s ease-in-out;
            }
            .tracker-progress.cancelled {
                background: linear-gradient(90deg, #8ab4f8, #f28b82);
            }
            
            /* Step width calculation based on current step */
            .step-width-1 { width: 0%; }
            .step-width-2 { width: 33.33%; }
            .step-width-3 { width: 66.66%; }
            .step-width-4 { width: 100%; }

            .tracker-step {
                position: relative;
                z-index: 3;
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 25%;
            }
            .tracker-icon {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: #3c4043;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                color: #9aa0a6;
                transition: all 0.3s ease;
                border: 4px solid #202124;
            }
            .tracker-icon.completed {
                background: #8ab4f8;
                color: #202124;
                box-shadow: 0 0 15px rgba(138, 180, 248, 0.4);
            }
            .tracker-icon.cancelled-icon {
                background: #f28b82;
                color: #202124;
                box-shadow: 0 0 15px rgba(242, 139, 130, 0.4);
            }
            .tracker-icon.active {
                background: #c58af9;
                color: #fff;
                box-shadow: 0 0 20px rgba(197, 138, 249, 0.6);
                animation: pulse 2s infinite;
            }
            .tracker-icon img {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                object-fit: cover;
                border: 2px solid #8ab4f8;
            }
            .tracker-label {
                margin-top: 12px;
                font-weight: 600;
                font-size: 0.9rem;
                color: #9aa0a6;
                text-align: center;
            }
            .tracker-label.active {
                color: #e8eaed;
            }
            .tracker-label.cancelled-label {
                color: #f28b82;
            }
            .tracker-subtext {
                font-size: 0.75rem;
                color: #5f6368;
                text-align: center;
                margin-top: 4px;
            }
            
            @keyframes pulse {
                0% { box-shadow: 0 0 0 0 rgba(197, 138, 249, 0.7); }
                70% { box-shadow: 0 0 0 15px rgba(197, 138, 249, 0); }
                100% { box-shadow: 0 0 0 0 rgba(197, 138, 249, 0); }
            }
            
            html[data-bs-theme="light"] .ticket-tracker-card {
                background: #ffffff !important;
                border: 1px solid #e3e6f0 !important;
            }
            html[data-bs-theme="light"] .tracker-line {
                background: #e3e6f0;
            }
            html[data-bs-theme="light"] .tracker-icon {
                background: #eaecf4;
                border-color: #fff;
            }
            html[data-bs-theme="light"] .card-header h6 {
                color: #4e73df !important;
            }
            html[data-bs-theme="light"] .tracker-label.active {
                color: #3a3b45;
            }
            html[data-bs-theme="light"] .tracker-label.cancelled-label {
                color: #e74a3b;
            }
            html[data-bs-theme="light"] .tracker-icon.cancelled-icon {
                background: #e74a3b;
                color: #fff;
                box-shadow: 0 0 15px rgba(231, 74, 59, 0.4);
            }
            html[data-bs-theme="light"] .tracker-progress.cancelled {
                background: linear-gradient(90deg, #4e73df, #e74a3b);
            }
            .tracker-time {
                font-size: 0.65rem;
                color: #8ab4f8;
                font-weight: bold;
                display: block;
                margin-top: 2px;
            }
            html[data-bs-theme="light"] .tracker-time {
                color: #4e73df;
            }
        </style>

        <div class="tracker-container">
            <div class="tracker-line"></div>
            <div class="tracker-progress step-width-{{ $step }} {{ $isCancelled ? 'cancelled' : '' }}"></div>

            <!-- Step 1: Created -->
            <div class="tracker-step">
                <div class="tracker-icon {{ $step >= 1 ? ($step == 1 && !$isCancelled ? 'active' : 'completed') : '' }}">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="tracker-label {{ $step >= 1 ? 'active' : '' }}">Created</div>
                <div class="tracker-subtext">
                    Logged
                    <span class="tracker-time">{{ $ticket->created_at->format('M d, H:i') }}</span>
                </div>
            </div>

            <!-- Step 2: Assigned -->
            <div class="tracker-step">
                <div class="tracker-icon {{ $step >= 2 ? ($step == 2 && !$isCancelled ? 'active' : 'completed') : '' }}">
                    @if($step >= 2 && $ticket->assignee)
                        <img src="{{ $ticket->assignee->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($ticket->assignee->name).'&background=random' }}" alt="Agent">
                    @else
                        <i class="fas fa-user-check"></i>
                    @endif
                </div>
                <div class="tracker-label {{ $step >= 2 ? 'active' : '' }}">Assigned</div>
                <div class="tracker-subtext">
                    @if($step >= 2 && $ticket->assignee)
                        {{ explode(' ', $ticket->assignee->name)[0] }}
                        @php
                            $assignRecord = $ticket->assignments()->latest()->first();
                        @endphp
                        @if($assignRecord && $assignRecord->created_at)
                            <span class="tracker-time">{{ \Carbon\Carbon::parse($assignRecord->created_at)->format('M d, H:i') }}</span>
                        @endif
                    @else
                        Pending
                    @endif
                </div>
            </div>

            <!-- Step 3: In Progress -->
            <div class="tracker-step">
                <div class="tracker-icon {{ $step >= 3 ? ($step == 3 && !$isCancelled ? 'active' : 'completed') : '' }}">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="tracker-label {{ $step >= 3 ? 'active' : '' }}">In Progress</div>
                <div class="tracker-subtext">
                    @if($step >= 3 && !$isCancelled)
                        @php
                            $endTime = ($step >= 4 && $ticket->resolved_at) ? $ticket->resolved_at : now();
                            $duration = $ticket->created_at->diffForHumans($endTime, true);
                        @endphp
                        Running for {{ $duration }}
                    @else
                        Waiting
                    @endif
                </div>
            </div>

            <!-- Step 4: Resolved / Cancelled -->
            <div class="tracker-step">
                @if($isCancelled)
                    <div class="tracker-icon cancelled-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="tracker-label cancelled-label">Cancelled</div>
                    <div class="tracker-subtext">Ticket Voided</div>
                @else
                    <div class="tracker-icon {{ $step >= 4 ? 'completed' : '' }}">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="tracker-label {{ $step >= 4 ? 'active' : '' }}">Resolved</div>
                    <div class="tracker-subtext">
                        @if($step >= 4 && $ticket->resolved_at)
                            Completed
                            <span class="tracker-time">{{ $ticket->resolved_at->format('M d, H:i') }}</span>
                        @else
                            Pending
                        @endif
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endif
