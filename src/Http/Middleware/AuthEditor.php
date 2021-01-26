<?php

namespace Haxibiao\Breeze\Http\Middleware;

use Closure;
use Haxibiao\Breeze\User;
use Illuminate\Support\Facades\Auth;

class AuthEditor
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
            if ($user->role_id < User::EDITOR_STATUS) {
                $request->session()->flash('message', '需要本站编辑身份!');
            }
            return abort(403);
        }
        return $next($request);
    }
}
