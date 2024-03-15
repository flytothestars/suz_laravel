<?php

namespace App\Http\Middleware;

use Closure;

class IsMyRequest
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
        if(\Auth::user()->hasAnyRole('администратор|супер-администратор|диспетчер'))
        {
            return $next($request);
        }
        else
        {
            if(!\Auth::user()->requests()->contains('id', $request->id))
            {
                abort(403);
            }
        }
        return $next($request);
    }
}
