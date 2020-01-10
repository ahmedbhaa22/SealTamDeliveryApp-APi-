<?php

namespace App\Http\Middleware;

use Closure;

class DashBoardAccess
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
        $miniDashboard = ($request->hasHeader('miniDashboard')) ? $request->header('miniDashboard') : 0;


        if (($miniDashboard!=0 && auth()->user()->isSuperDashBoardAdmin())
            ||auth()->user()->admin->mini_dashboard_id ==$miniDashboard
            ||($miniDashboard==0 && auth()->user()->isSuperDashBoardAdmin())) {
            $request->request->add(['dashboardId' =>$miniDashboard]);
            return $next($request);
        } else {
            return response()->json(null, 401);
        }
    }
}
