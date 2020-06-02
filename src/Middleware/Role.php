<?php

namespace sdwru\LaravelFirebaseAuth\Middleware;

use Closure;

class Role
{

    public function handle($request, Closure $next, ...$roles)
    {
        if(!$request->user()->hasRole($roles)) {
             abort(403);
        }
        
        return $next($request);
    }
}
