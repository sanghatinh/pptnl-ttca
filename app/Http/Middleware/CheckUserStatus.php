<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user && $user->status !== 'active') {
            auth()->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is inactive'
            ], 403);
        }

        return $next($request);
    }
}