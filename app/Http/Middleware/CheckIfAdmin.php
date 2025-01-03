<?php

namespace Bo\Base\Http\Middleware;

use Closure;

class CheckIfAdmin
{
    /**
     * Checked that the logged in user is an administrator.
     *
     * --------------
     * VERY IMPORTANT
     * --------------
     * If you have both regular users and admins inside the same table, change
     * the contents of this method to check that the logged in user
     * is an admin, and not a regular user.
     *
     * Additionally, in Laravel 7+, you should change app/Providers/RouteServiceProvider::HOME
     * which defines the route where a logged in user (but not admin) gets redirected
     * when trying to access an admin route. By default it's '/home' but BoCMS
     * does not have a '/home' route, use something you've built for your users
     * (again - users, not admins).
     *
     * @param [type] $user [description]
     * @return bool [description]
     */
    private function checkIfUserIsAdmin($user): bool
    {
        return ($user->is_admin == 1 || $user->is_admin == 2);
//        return true;
    }

    /**
     * Answer to unauthorized access request.
     *
     * @param [type] $request [description]
     * @return [type] [description]
     */
    private function respondToUnauthorizedRequest($request, bool $admin = false)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response(trans('bo::base.unauthorized'), 401);
        } else {
            if($admin) return redirect()->guest(bo_url('login'))->withErrors([
                'not_admin' => trans('bo::base.not_admin')
            ]);
            return redirect()->guest(bo_url('login'));
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (bo_auth()->guest()) {
            return $this->respondToUnauthorizedRequest($request);
        }

        if (!$this->checkIfUserIsAdmin(bo_user())) {

            //If not admin => logout
            bo_auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->respondToUnauthorizedRequest($request, true);
        }

        return $next($request);
    }
}
