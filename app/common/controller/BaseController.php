<?php
declare (strict_types = 1);

namespace app\common\controller;

use liliuwei\think\Jump;
use think\App;
use think\exception\ValidateException;
use think\facade\View;
use think\Request;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    use Jump;
    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    /**
     * 验证数据
     * @access protected
     * @param  mixed        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, array $message = [], bool $batch = false)
    {
        $rule = $validate;
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (empty($data) || !is_array($data)) {
            $data = request()->param();
        }
        $v->failException(true)->check($data);

        if (is_array($rule)) {
            $keys = [];
            foreach ($rule as $k => $v) {
                $arr = explode('|', $k);
                $keys[$arr[0]] = $arr[0];
            }
            $data = array_intersect_key($data, $keys);
        }

        return $data;
    }

    /**
     * 加载模板输出
     * @access protected
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @return mixed
     */
    protected function fetch($template = '', $vars = [])
    {
        return View::fetch($template, $vars);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param  string|array $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
     */
    protected function assign($name, $value = null)
    {
        View::assign($name, $value);

        return $this;
    }
}
