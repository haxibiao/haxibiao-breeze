<?php

namespace Haxibiao\Breeze\Http\Middleware;

use Closure;
use Haxibiao\Breeze\User;
use Illuminate\Support\Facades\Auth;

class AuthAdmin
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
        if ($user = Auth::user()) {
            if ($user->role_id < User::ADMIN_STATUS) {
                $request->session()->flash('message', '需要本站管理员身份!');
                return abort(403);
            }
        }
        return $next($request);
    }
}
