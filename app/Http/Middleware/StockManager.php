<?php

namespace App\Http\Middleware;

use Closure;

class StockManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(\Auth::user()->id != '153' && !\Auth::user()->hasRole('администратор'))
        {
            abort(403);  
        }
        return $next($request);
    }
}
