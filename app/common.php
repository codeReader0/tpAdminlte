<?php

use app\model\AdminUser;
use app\model\AuthRule;
use app\model\AdminHandleLog;
use app\common\exception\ExitOutException;
use think\facade\Cache;
use think\facade\Filesystem;

//统一输出格式话的json数据
if (!function_exists('out')) {
    function out($data = null, $code = 200, $msg = 'success', $e = false)
    {
        $req = request()->param();
        $module = app('http')->getName();
        if ($module === 'admin'){
            $action = request()->action();
            $controller = request()->controller();

            $path = $controller . '/' . $action;
            $authRule = AuthRule::where('name', $path)->find();
            if (!empty(session('admin_user')['id']) && !empty($authRule['id']) && $code == 200){
                if (!empty($req['password'])){
                    $req['password'] = '******';
                }
                if (!empty($req['password_confirm'])){
                    $req['password_confirm'] = '******';
                }

                $response_body = $data === null ? 'success' : json_encode($data, JSON_UNESCAPED_UNICODE);
                if (mb_strlen($response_body) > 5000) {
                    $response_body = mb_substr($response_body, 0, 5000);
                }
                $request_body = json_encode($req, JSON_UNESCAPED_UNICODE);
                if (mb_strlen($request_body) > 5000) {
                    $request_body = mb_substr($request_body, 0, 5000);
                }
                $add = [
                    'admin_user_id' => session('admin_user')['id'],
                    'auth_rule_id' => $authRule['id'],
                    'request_body' => $request_body,
                    'response_body' => $response_body
                ];
                AdminHandleLog::create($add);
            }
        }

        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];

        if ($e !== false) {
            if ($e instanceof Exception) {
                $errMsg = $e->getFile().'文件第'.$e->getLine().'行错误：'.$e->getMessage();
                trace([$msg => $errMsg], 'error');
            }
            else {
                trace([$msg => $e], 'error');
            }
        }

        return json($out);
    }
}

if (!function_exists('exit_out')) {
    function exit_out($data = null, $code = 200, $msg = 'success', $e = false)
    {
        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];

        if ($e !== false) {
            if ($e instanceof Exception) {
                $errMsg = $e->getFile().'文件第'.$e->getLine().'行错误：'.$e->getMessage();
                trace([$msg => $errMsg], 'error');
            }
            else {
                trace([$msg => $e], 'error');
            }
        }

        $msg = json_encode($out, JSON_UNESCAPED_UNICODE);

        throw new ExitOutException($msg);
    }
}

if (!function_exists('auth_show_judge')) {
    function auth_show_judge($path, $is_return_bool = false)
    {
        if (config('app.is_open_auth')){
            if (!AdminUser::checkAuth(session('admin_user')['id'], $path)) {
                return $is_return_bool ? false : 'style="display: none;"';
            }

            return $is_return_bool ? true : '';
        }

        return true;
    }
}

if (!function_exists('auth_show_navigation')) {
    function auth_show_navigation()
    {
        $menu = config('menu');
        foreach ($menu as $k => $v){
            if (is_array($v['url'])){
                foreach ($v['url'] as $k1 => $v1){
                    $path = str_replace('admin/', '', $v1['url']);
                    if (!auth_show_judge($path, true)){
                        unset($menu[$k]['url'][$k1]);
                    }
                }
                if (empty($menu[$k]['url'])){
                    unset($menu[$k]);
                }
            }
            else {
                $path = str_replace('admin/', '', $v['url']);
                if (!auth_show_judge($path, true)){
                    unset($menu[$k]);
                }
            }
        }

        return $menu;
    }
}

/**
 * 创建(导出)Excel数据表格
 * @param  array   $list 要导出的数组格式的数据
 * @param  array   $header Excel表格的表头
 * @param  string  $title Excel表格标题
 * @param  string  $filename 导出的Excel表格数据表的文件名 不带后缀
 * 比如:
 * $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
 * $header = array('id'=>'编号','username'=>'姓名','sex'=>'性别','age'=>'年龄');
 * @return [array] [数组]
 */
/*if (!function_exists('create_excel')) {
    function create_excel($list, $header, $filename, $title = '0')
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($title);

        $i = 1;
        foreach ($header as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($i, 1, $value);
            $i++;
        }

        $row = 2; //从第二行开始
        foreach ($list as $item) {
            $column = 1;
            foreach ($header as $k => $v) {
                $worksheet->setCellValueByColumnAndRow($column, $row, ' '.$item[$k]??'');
                $column++;
            }

            $row++;
        }

        $fileType = 'Xlsx';

        //1.下载到服务器
        //$writer = IOFactory::createWriter($spreadsheet, $fileType');
        //$writer->save($filename.'.'.$fileType);

        //2.输出到浏览器
        $writer = IOFactory::createWriter($spreadsheet, $fileType);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');

        exit;
    }
}*/

//AES加密
if (!function_exists('aes_encrypt')) {
    function aes_encrypt($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        $key = config('config.aes_key');
        $iv  = config('config.aes_iv');

        $cipher_text = openssl_encrypt($data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $cipher_text = base64_encode($cipher_text);

        return urlencode($cipher_text);
    }
}

//AES解密
if (!function_exists('aes_decrypt')) {
    function aes_decrypt($encryptData)
    {
        $encryptData = urldecode($encryptData);
        $encryptData = base64_decode($encryptData);

        $key = config('config.aes_key');
        $iv  = config('config.aes_iv');

        $original_plaintext = openssl_decrypt($encryptData, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return json_decode($original_plaintext, true);
    }
}

//上传文件
if (!function_exists('upload_file')) {
    function upload_file($name, $is_must = true, $is_return_url = true)
    {
        if (!empty(request()->file()[$name])){
            $file = request()->file()[$name];
            $savename = Filesystem::putFile('', $file);

            if ($is_return_url){
                $img_url = request()->domain().'/storage/'.$savename;
                if (!empty(env('common.img_domain', ''))) {
                    $img_url = env('common.img_domain').'/storage/'.$savename;
                }
            }
            else {
                $img_url = public_path().'storage/'.$savename;
            }

            return $img_url;
        }
        else {
            if ($is_must){
                exit_out(null, 11002, '文件不能为空');
            }
        }

        return '';
    }
}

//检测重复请求 超过就禁止访问 有用户flag就针对用户flag 没有flag就针对ip地址(ip的话注意反代情况，可能每个用户请求的ip都是反代服务器的ip,当然可以配置一波反代服务器使得业务服务器获取到真实用户ip) 最小只能设置1s一次请求 不支持1s以下 如果开启了redis可以支持毫秒级
if (!function_exists('check_repeat_request')) {
    function check_repeat_request($time, $limit, $flag = '')
    {
        $action = request()->action();
        if (!empty($flag)) {
            $key = $action.$flag;
        }
        else {
            $ip = request()->ip();
            $key = $action.$ip;
        }

        $req = request()->param();
        if (!empty($req)) {
            ksort($req);
            $reqMd5 = md5(json_encode($req));
            $key = $key.$reqMd5;
        }

        //如果开启了redis可以支持毫秒级
        if (config('cache.redis_cache_switch')) {
            $time = $time * 1000;
            $redis = Cache::store('redis')->handler();
            if ($redis->exists($key)) {
                $redis->incrby($key, 1);
                $count = $redis->get($key);
                if ($count > $limit) {
                    exit_out(null, 11003, '操作过于频繁，请稍后重试');
                }
            }
            else {
                $redis->set($key, 1);
                $redis->pexpire($key, $time);
            }
        }
        else {
            if ($time < 1) {
                $time = 1;
            }
            if (Cache::has($key)) {
                if ($time == 1 && $limit == 1) {
                    exit_out(null, 11003, '操作过于频繁，请稍后重试');
                }
                Cache::inc($key);
                $count = Cache::get($key);
                if($count > $limit) {
                    exit_out(null, 11003, '操作过于频繁，请稍后重试');
                }
            }
            else {
                Cache::set($key, 1, $time);
            }
        }

        return true;
    }
}

