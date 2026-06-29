@php
    $statuses = [
        'available' => ['color' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Available'],
        'busy' => ['color' => 'warning', 'icon' => 'fa-clock', 'label' => 'Busy'],
        'offline' => ['color' => 'secondary', 'icon' => 'fa-minus-circle', 'label' => 'Offline'],
        'on_leave' => ['color' => 'primary', 'icon' => 'fa-plane', 'label' => 'On Leave']
    ];
@endphp

@foreach($statuses as $statusKey => $config)
    <div class="col-md-3 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h6 class="font-weight-bold text-{{ $config['color'] }} mb-0">
                    <i class="fas {{ $config['icon'] }} me-2"></i>{{ $config['label'] }} 
                    <span class="badge bg-{{ $config['color'] }} rounded-pill ms-2">{{ isset($agentAvailability[$statusKey]) ? $agentAvailability[$statusKey]->count() : 0 }}</span>
                </h6>
            </div>
            <div class="card-body p-3">
                @if(isset($agentAvailability[$statusKey]) && $agentAvailability[$statusKey]->count() > 0)
                    <div class="d-flex flex-column gap-3">
                        @foreach($agentAvailability[$statusKey] as $agent)
                            <div class="d-flex align-items-center p-2 rounded-3 bg-light border border-light-subtle">
                                <img src="{{ $agent->avatar_url }}" class="rounded-circle me-3 border border-2 border-white shadow-sm" width="40" height="40">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.9rem;">{{ $agent->name }}</div>
                                    <div class="text-muted small text-truncate">{{ $agent->department?->name ?? 'No Dept' }}</div>
                                </div>
                                <div class="text-end ms-2">
                                    <span class="badge {{ $agent->active_tickets_count >= $agent->agent_capacity ? 'bg-danger' : 'bg-primary' }} rounded-pill" title="Active Tickets">
                                        {{ $agent->active_tickets_count }}/{{ $agent->agent_capacity }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">
                        No agents currently {{ strtolower($config['label']) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach
