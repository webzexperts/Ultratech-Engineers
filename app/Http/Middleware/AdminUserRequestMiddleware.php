<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Session;
class AdminUserRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->id != null && auth()->user()->id != "")
        {
            $access = DB::table('user_locations')->leftJoin('admin', 'admin.id', 'user_locations.user_id')->where('user_locations.user_id', auth()->user()->id)->get();
           
            $getLocation =  Session::get('getLocationId');
            
    
            if($access->count() > 1 && auth()->user()->id != 1)
            {
                return $next($request);
                // if(($getLocation != '' && $getLocation != 0 && $getLocation != null))
                // {
                //     if(auth()->user()->id == 1){
                //             return $next($request);
                //     }
                //     else{
                //         return $next($request);
                //       //  return redirect(route('selectLocation'));
                //     }
                   
                // }else{
                //     return $next($request);
                //     //return redirect(route('selectLocation'));
                // }
            }
            
            else if(auth()->user()->id != 1){
              return redirect()->route('dashboard');
            }
            
            else{
                return $next($request);
            }
        }else{
            return $next($request);
        }
    }
}