@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Your Notifications</h1>
    @if(auth()->user()->unreadNotifications->count() > 0)
        <form action="{{ route('notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm"><i class="fas fa-check-double fa-sm"></i> Mark All as Read</button>
        </form>
    @endif
</div>

<div class="card shadow mb-4">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <li class="list-group-item d-flex justify-content-between align-items-center p-4 {{ empty($notification->read_at) ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle {{ empty($notification->read_at) ? 'bg-primary' : 'bg-secondary' }} text-white rounded-circle p-3 me-4 text-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <div class="font-weight-bold {{ empty($notification->read_at) ? 'text-gray-900' : 'text-muted' }}">
                                {{ $notification->data['message'] ?? 'You have a new notification.' }}
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                @if(isset($notification->data['ticket_id']))
                                    <span class="mx-2">|</span>
                                    <a href="{{ route('tickets.show', $notification->data['ticket_id']) }}" class="text-primary text-decoration-none">View Ticket</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if(empty($notification->read_at))
                        <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-light border" title="Mark as Read"><i class="fas fa-check text-success"></i></button>
                        </form>
                    @endif
                </li>
            @empty
                <li class="list-group-item text-center py-5">
                    <div class="text-muted mb-3"><i class="fas fa-bell-slash fa-3x"></i></div>
                    <h5 class="font-weight-bold text-gray-800">All Caught Up!</h5>
                    <p class="text-muted">You don't have any notifications right now.</p>
                </li>
            @endforelse
        </ul>
    </div>
    @if($notifications->hasPages())
        <div class="card-footer bg-white border-0 py-3">{{ $notifications->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
