<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admins bypass subscription check
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')
                ->with('warning', 'You need an active subscription to access jobs.');
        }

        return $next($request);
    }
}