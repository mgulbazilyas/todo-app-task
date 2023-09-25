<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateWithJWTSession extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards): Response
    {

        if ($request->expectsJson()) {
            if ($this->auth->guard('api')->check()) {
                Auth::setUser($this->auth->guard('api')->user());
                return $next($request);
            }
        }

        if ($this->auth->guard('web')->check()) {
            Auth::setUser($this->auth->guard('web')->user());
            return $next($request);
        }

        return redirect()->route('login');
    }
}
