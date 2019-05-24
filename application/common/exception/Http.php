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

class Http extends Handle
{
    public function render(Exception $e)
    {
        $msg = $e->getMessage();
        $out = json_decode($msg, true);
        if (!$out || !is_array($out) || empty($out['code'])){
            $out = ['code' => 500, 'msg' => $e->getMessage(), 'data' => null];
        }

        return json($out);
    }
}
