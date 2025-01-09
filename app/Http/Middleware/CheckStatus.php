<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = auth()->user();
//            return $next($request);
            if ($user->status  && $user->ev  && $user->sv) {
                return $next($request);
            } else {
                if ($request->is('api/*')) {
                    $notify[] = 'You need to verify your account first.';
                    return response()->json([
                        'message' => ['error' => $notify],
                        'data' => [
                            'is_ban' => $user->status,
                            'email_verified' => $user->ev,
                            'mobile_verified' => $user->sv,
                        ],
                    ]);
                } else {
                    return to_route('login');
//                    return to_route('');
                }
            }
        }
        abort(403);
    }
}
