<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->hasRole('agent') && $user->availability_status === 'available') {
                if ($user->last_activity_at && $user->last_activity_at->diffInMinutes(now()) >= 15) {
                    $user->update(['availability_status' => 'busy']);
                    \App\Models\AgentStatusHistory::create([
                        'user_id' => $user->id,
                        'status' => 'busy',
                        'reason' => 'Auto-away (Inactive for 15+ mins)'
                    ]);
                }
            }
            
            $user->update(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}
