<?php

namespace app\common\exception;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\View;
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
        $module = app('http')->getName();
        if ($module == 'api') {
            $out = ['code' => 500, 'msg' => $e->getMessage(), 'data' => null];
            //验证错误
            if ($e instanceof ValidateException) {
                $out['code'] = 10000;
            }
            //自定义异常错误
            if ($e instanceof ExitOutException) {
                $msg = $e->getMessage();
                $out = json_decode($msg, true);
            }
            //保存错误日志
            $this->saveErrorLog($e);
            //跳转类的错误
            if ($e instanceof HttpResponseException) {
                View::engine()->layout(false);
                return parent::render($request, $e);
            }

            return json($out);
        }
        else {
            if ($request->isAjax()) {
                $out = ['code' => 500, 'msg' => $e->getMessage(), 'data' => null];
                //验证错误
                if ($e instanceof ValidateException) {
                    $out['code'] = 10000;
                }
                //自定义异常错误
                if ($e instanceof ExitOutException) {
                    $msg = $e->getMessage();
                    $out = json_decode($msg, true);
                }
                //保存错误日志
                $this->saveErrorLog($e);

                return json($out);
            }
            else {
                //保存错误日志
                $this->saveErrorLog($e);

                View::engine()->layout(false);
                return parent::render($request, $e);
            }
        }
    }

    private function saveErrorLog($e)
    {
        if (!$this->isIgnoreReport($e)) {
            $controller = request()->controller();
            $action = request()->action();
            $api = $controller . '/' . $action;
            trace([$api.'接口错误' => $e->getTraceAsString(), '请求参数' => request()->post(), '响应msg' => $e->getMessage()], 'error');
        }
    }
}
