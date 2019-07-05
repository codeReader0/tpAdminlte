<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 19-7-5
 * Time: 下午4:07
 */

namespace app\common\controller;

use think\Controller;
use think\exception\ValidateException;

class BaseController extends Controller
{
    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @param  mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $val, $message = [], $batch = false, $callback = null)
    {
        $validate = $val;
        $field_map = [];
        if (is_array($val)) {
            $validate = [];
            foreach ($val as $k => $v){
                $tmp = explode('|', $k);
                $validate[$tmp[0]] = $v;
                if (!empty($tmp[1])){
                    $field_map[$tmp[0]] = $tmp[1];
                }
            }
            $v = $this->app->validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = $this->app->validate($validate);
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            $msg = $v->getError();
            if (!empty($field_map)){
                foreach ($field_map as $k => $v){
                    if (strpos($msg, $k) !== false){
                        $msg = str_replace($k, $v, $msg);
                        break;
                    }
                }
            }

            throw new ValidateException($msg);
        }

        return true;
    }
}
