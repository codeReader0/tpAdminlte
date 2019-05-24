<?php

//统一输出格式话的json数据
if (!function_exists('out')) {
    function out($data = null, $code = 200, $msg = 'success')
    {
        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];

        return json($out);
    }
}

if (!function_exists('exit_out')) {
    function exit_out($data = null, $code = 200, $msg = 'success')
    {
        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];
        $msg = json_encode($out, JSON_UNESCAPED_UNICODE);
        throw new \Exception($msg);
    }
}
