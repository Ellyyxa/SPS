<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentMoodCheckIn
{
    /**
     * Send students without a mood entry for today to the daily check-in.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'student' && ! $user->moods()->whereDate('date', today())->exists()) {
            return redirect()->route('moods.create');
        }

        return $next($request);
    }
}
