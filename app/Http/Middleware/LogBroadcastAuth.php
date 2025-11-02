<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogBroadcastAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('Broadcast auth request', [
            'user_id' => optional($request->user())->id,
            'channel_name' => $request->input('channel_name'),
            'socket_id' => $request->input('socket_id'),
            'path' => $request->path(),
        ]);

        $response = $next($request);

        Log::info('Broadcast auth response', [
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
