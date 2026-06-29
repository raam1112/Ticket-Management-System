<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }

    public function fetchUnread()
    {
        $unread = auth()->user()->unreadNotifications()->take(4)->get();
        $count = auth()->user()->unreadNotifications()->count();
        
        return response()->json([
            'count' => $count,
            'notifications' => $unread->map(function($n) {
                return [
                    'id' => $n->id,
                    'message' => $n->data['message'] ?? 'New Notification',
                    'time' => $n->created_at->diffForHumans(),
                    'url' => route('notifications.index')
                ];
            })
        ]);
    }
}
