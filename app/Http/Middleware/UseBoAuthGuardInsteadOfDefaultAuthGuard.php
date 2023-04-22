<?php

namespace Bo\Base\Http\Middleware;

use Closure;

class UseBoAuthGuardInsteadOfDefaultAuthGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        app('auth')->setDefaultDriver(config('bo.base.guard'));

        return $next($request);
    }
}
