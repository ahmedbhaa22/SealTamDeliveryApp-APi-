<?php

namespace App\Http\Middleware;

use Closure;

class current_application_type
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $currentApplication)
    {
        $request->request->add(['currentApplicationUser' =>$currentApplication]);

        if (auth()->user() && auth()->user()->UserType != $currentApplication) {
            return response()->json(null, 401);
        }
        // continue request
        return $next($request);
    }
}
