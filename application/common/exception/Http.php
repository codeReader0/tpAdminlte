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
    protected $ignoreReport = [
        '\\app\\common\\exception\\ExitOutException',
    ];

    public function render(Exception $e)
    {
        //参数验证错误
        if ($e instanceof ValidateException) {
            $out = ['code' => 10000, 'message' => $e->getError(), 'data' => null];
            return json($out);
        }

        //自定义异常错误
        if ($e instanceof ExitOutException) {
            $msg = $e->getMessage();
            $out = json_decode($msg, true);
            if (!empty($out)) {
                return json($out);
            }
        }

        $out = ['code' => 500, 'message' => $e->getMessage(), 'data' => null];

        return json($out);
    }
}
