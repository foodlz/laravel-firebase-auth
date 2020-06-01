<?php

namespace sdwru\LaravelFirebaseAuth;

use Closure;

class RoleMiddleware
{

    public function handle($request, Closure $next, ...$role)
    {
        if(!$request->user()->hasRole($role)) {
             abort(403);
        }
        
        return $next($request);
    }
}
