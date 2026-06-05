<?php

namespace App\admin\middleware;

use Closure;
class MaxPageLimit{

    public function handle(Closure $next)
    {
        if($_GET['limit'] && $_GET['limit']>100){
            $_GET['limit'] = 100;
        }
        return $next();
    }
}