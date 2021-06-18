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
use think\Response;

class Http extends Handle
{
    protected $ignoreReport = [
        '\\app\\common\\exception\\ExitOutException',
        '\\think\\exception\\ValidateException',
        '\\think\\exception\\HttpException',
    ];

    public function render(Exception $e)
    {
        //参数验证错误
        if ($e instanceof ValidateException) {
            if (request()->isAjax()) {
                $out = ['code' => 10000, 'msg' => $e->getError(), 'data' => null];
                return json($out);
            }
            else {
                $result = [
                    'code' => 0,
                    'msg'  => $e->getError(),
                    'data' => '',
                    'url'  => 'javascript:history.back(-1);',
                    'wait' => 3,
                ];

                $response = Response::create($result, 'jump')->header([])->options(['jump_template' => config('dispatch_error_tmpl')]);

                return $response;
            }
        }

        //自定义异常错误
        if ($e instanceof ExitOutException) {
            $msg = $e->getMessage();
            $out = json_decode($msg, true);
            if (!empty($out)) {
                return json($out);
            }
        }

        $out = ['code' => 500, 'msg' => $e->getMessage(), 'data' => null];

        return json($out);
    }
}
