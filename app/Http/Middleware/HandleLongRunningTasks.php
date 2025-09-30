<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleLongRunningTasks
{
    /**
     * Handle an incoming request for long-running tasks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Increase execution time and memory limit for long-running tasks
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '1024M'); // 1GB
        
        // Disable output buffering to prevent timeouts
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        return $next($request);
    }
}