<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $modelName, string $ownerColumn): Response
    {
        $model = $request->route($modelName);

        $user = Auth::user();
        
        $ownerId = match(true){
            $user->isTeacher() => $user->teacherProfile->id,
            $user->isStudent() => $user->studentProfile->id,
            $user->isParent() => $user->parentProfile->id,
            default => null,
        };

        if(!$ownerId || $model->{$ownerColumn} != $ownerId){
            return response()->json(['message' => 'Unauthorized: You do not own this resource.'], 403);
        }
        return $next($request);
    }
}
