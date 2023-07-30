<?php

namespace Dipantry\Analytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Analytics
{
    public function handle(Request $request, Closure $next)
    {
        $uri = str_replace($request->root(), '', $request->url()) ?: '/';

        $response = $next($request);


    }
}