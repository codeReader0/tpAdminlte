<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 2022/3/22
 * Time: 4:27 下午
 */

namespace app\common\behavior;

use Closure;

class CrossDomain
{
    public function handle($request, Closure $next)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Max-Age: 1800');
        header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, Origin, Accept, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, token, lang');
        if (strtoupper($request->method()) == "OPTIONS") {
            exit();
        }

        return $next($request);
    }
}
