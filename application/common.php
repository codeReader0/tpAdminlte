<?php

use auth\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\model\AuthRule;
use app\model\AdminHandleLog;
use app\common\exception\ExitOutException;

//统一输出格式话的json数据
if (!function_exists('out')) {
    function out($data = null, $code = 200, $msg = 'success')
    {
        $req = request()->param();
        $module = request()->module();
        if ($module === 'admin'){
            $action = request()->action();
            $controller = request()->controller();

            $path = $controller . '/' . $action;
            $authRule = AuthRule::get(['name' => $path]);

            if (!empty(session('admin_user')['id']) && !empty($authRule['id']) && $code == 200){
                if (!empty($req['password'])){
                    $req['password'] = '******';
                }
                if (!empty($req['password_confirm'])){
                    $req['password_confirm'] = '******';
                }
                $response_body = $data === null ? 'success' : json_encode($data, JSON_UNESCAPED_UNICODE);
                $add = [
                    'admin_user_id' => session('admin_user')['id'],
                    'auth_rule_id' => $authRule['id'],
                    'request_body' => json_encode($req, JSON_UNESCAPED_UNICODE),
                    'response_body' => $response_body
                ];
                AdminHandleLog::create($add);
            }
        }

        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];

        return json($out);
    }
}

if (!function_exists('exit_out')) {
    function exit_out($data = null, $code = 200, $msg = 'success')
    {
        $out = ['code' => $code, 'msg' => $msg, 'data' => $data];
        $msg = json_encode($out, JSON_UNESCAPED_UNICODE);
        throw new ExitOutException($msg);
    }
}

if (!function_exists('auth_show_judge')) {
    function auth_show_judge($path, $is_return_bool = false)
    {
        if (config('is_open_auth')){
            $auth = new Auth();
            if(!$auth->check($path, session('admin_user')['id'])){
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
        $menu = config('menu.');
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
 * $header = array('编号','姓名','性别','年龄');
 * @return [array] [数组]
 */
if (!function_exists('create_excel')) {
    function create_excel($list, $header, $filename, $title = '0')
    {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle($title);

        foreach ($header as $key => $value) {
            $worksheet->setCellValueByColumnAndRow($key+1, 1, $value);
        }

        $row = 2; //从第二行开始
        foreach ($list as $item) {
            $column = 1;
            foreach ($item as $value) {
                $worksheet->setCellValueByColumnAndRow($column, $row, $value);
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
        if($fileType == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
            header('Cache-Control: max-age=0');
        }
        else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
            header('Cache-Control: max-age=0');
        }

        $writer->save('php://output');

        exit;
    }
}
