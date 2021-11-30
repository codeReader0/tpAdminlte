<?php

namespace app\common\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Request;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        ExitOutException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if ($e instanceof ValidateException) {
            if (request()->isAjax() || request()->isJson() || request()->isPost()) {
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

                $type = 'view';
                $response = Response::create(config('jump.dispatch_error_tmpl'), $type)->assign($result)->header([]);

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
