<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class AdminRole {
    public function handle(Request $request, Closure $next, ...$roles){
        $user=$request->user();
        abort_unless($user && (!isset($user->is_active) || $user->is_active), 403);
        if ($roles && !in_array($user->role ?? 'editor',$roles,true)) abort(403,'You do not have permission to perform this action.');
        return $next($request);
    }
}
