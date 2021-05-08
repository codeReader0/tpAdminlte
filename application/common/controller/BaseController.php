<?php
/**
 * Created by PhpStorm.
 * User: zilongs
 * Date: 19-7-5
 * Time: 下午4:07
 */

namespace app\common\controller;

use think\Controller;

class BaseController extends Controller
{
    //验证失败是否抛出异常
    protected $failException = true;
}
