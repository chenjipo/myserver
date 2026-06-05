<?php
namespace App\admin\middleware;

use Closure;
class Test {

    public function handle(Closure $next)
    {
        return $next();
    }
}