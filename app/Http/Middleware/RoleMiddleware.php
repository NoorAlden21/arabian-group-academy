<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,string $roles): Response
    {
        if(!$request->user() || !$request->user()->hasAnyRole(explode('|',$roles))){
            return response()->json([
                'message' => 'You do not have the permission to access this resource.'
            ],403);
        }
        return $next($request);
    }
}
