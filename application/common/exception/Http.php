<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 19-5-23
 * Time: 上午11:18
 */

namespace app\common\exception;

use Exception;
use think\exception\Handle;
use think\exception\ValidateException;

class Http extends Handle
{
    public function render(Exception $e)
    {
        //参数验证错误
        if ($e instanceof ValidateException) {
            $out = ['code' => 10000, 'msg' => $e->getError(), 'data' => null];
            return json($out);
        }

        $msg = $e->getMessage();
        $out = json_decode($msg, true);
        if (!$out || !is_array($out) || empty($out['code'])){
            $msg = $e->getMessage();
            if (!config('app_debug')){
                $msg = '抱歉，服务异常～';
            }
            $out = ['code' => 500, 'msg' => $msg, 'data' => null];
        }

        return json($out);
    }
}
