<?php

namespace app\common\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;

class Curd extends Command
{
    protected function configure()
    {
        $this->setName('curd')->addOption('table', null, Option::VALUE_REQUIRED, '表名')->setDescription('一键生成增删改查操作 命令：php think curd --table admin_user');
    }

    protected function execute(Input $input, Output $output)
    {
        $database = env('database.database');
        $prefix = env('database.prefix');

        if ($input->hasOption('table')) {
            $table = $input->getOption('table');
            $table = strpos($table, $prefix) !== false ? $table : $prefix.$table;
        }
        else {
            exit('必须传table参数'."\n");
        }

        $sql = "select COLUMN_NAME name, DATA_TYPE type, CHARACTER_MAXIMUM_LENGTH lenght, COLUMN_COMMENT comment, COLUMN_KEY index_key from INFORMATION_SCHEMA.COLUMNS where table_schema = '".$database."' AND table_name = '".$table."'";
        $tmp = Db::query($sql);
        if (empty($tmp)){
            exit('table不存在'."\n");
        }

        $db_table = $table;
        $table = str_replace($prefix, '', $table);
        $model = $this->convertUnderLine($table);
        $minModel = lcfirst($model);
        $lineModel = $this->uncamelize($model);

        //创建model
        $path = root_path().'/app/model';
        $file = $model.'.php';
        $template = root_path().'/template/curd/model.txt';
        $content = file_get_contents($template);

        $attr = '';
        $configMap = config('map');
        foreach ($tmp as $k1 => $v1){
            $commentArr = $this->convertComment($v1);
            if ($commentArr[2] > 0){
                $underName = $this->convertUnderLine($v1['name']);
                $field = $v1['name'].'_map';
                $configMap[$lineModel][$field] = $commentArr[1];
                $this->buildMap($configMap);
                $mapText = 'config(\'map.'.$lineModel.'\')[\''.$v1['name'].'_map\']';
                $attr = $attr."\n"."\n".'public function get'.$underName.'TextAttr($value, $data)'."\n".'{'."\n".'$map = '.$mapText.';'."\n".'return $map[$data[\''.$v1['name'].'\']];'."\n".'}';
            }
        }

        $search = ['{$model}', '{$attr}'];
        $replace = [$model, $attr];
        $content = str_replace($search, $replace, $content);
        $this->createPathFile($path, $file, $content);

        //创建controller
        $path = root_path().'/app/admin/controller';
        $file = $model.'Controller.php';
        $template = root_path().'/template/curd/controller.txt';
        $content = file_get_contents($template);
        $validate = $cond = '';
        foreach ($tmp as $k1 => $v1){
            //搜索条件
            if (!empty($v1['index_key'])){
                $condKey = $v1['name'] == 'id' ? $lineModel.'_id' : $v1['name'];
                $cond = $cond.'if (isset($req[\''.$condKey.'\']) && $req[\''.$condKey.'\'] !== \'\'){'."\n".'$builder->where(\''.$v1['name'].'\', $req[\''.$condKey.'\']);'."\n".'}'."\n";
            }

            if (in_array($v1['name'], ['update_time', 'create_time', 'id', 'create_at', 'update_at', 'delete_time', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            $va = 'require';
            if ($v1['name'] == 'phone'){
                $va .= '|mobile';
            }
            elseif ($v1['name'] == 'email'){
                $va .= '|email';
            }
            elseif (strpos($v1['name'], 'url') !== false){
                $va .= '|url';
            }
            elseif (strpos($v1['type'], 'int') !== false){
                if (strpos($v1['name'], 'id') !== false){
                    $va .= '|number';
                }
                else {
                    $va .= '|integer';
                }
            }
            elseif ($v1['type'] == 'float' || $v1['type'] == 'double' || $v1['type'] == 'decimal'){
                $va .= '|float';
            }
            elseif (strpos($v1['type'], 'time') !== false){
                $va .= '|date';
            }
            elseif(strpos($v1['type'], 'char') !== false) {
                $va .= '|max:'.$v1['lenght'];
            }

            if (!empty($va)){
                $commentArr = $this->convertComment($v1);
                $comment = $commentArr[0];
                if (empty($validate)){
                    $validate = "'".$v1['name']."|".$comment."' => '".$va."',";
                }
                else {
                    $validate = $validate."\n\t\t\t'".$v1['name']."|".$comment."' => '".$va."',";
                }
            }
        }
        $search = ['{$model}', '{$minModel}', '{$validate}', '{$cond}'];
        $replace = [$model, $minModel, $validate, $cond];
        $content = str_replace($search, $replace, $content);
        $this->createPathFile($path, $file, $content);

        //创建列表view
        $path = root_path().'/app/admin/view/'.$table;
        $file = $table.'_list.html';
        $template = root_path().'/template/curd/list.html';
        $content = file_get_contents($template);
        $ther = '';
        $tbod = '';
        $search = '';
        foreach ($tmp as $k1 => $v1){
            if (in_array($v1['name'], ['update_time', 'update_at', 'updated_at'])){
                continue;
            }
            $commentArr = $this->convertComment($v1);
            $comment = $commentArr[0];
            $ther = !empty($ther) ? $ther."\n" : '';
            $ther = $ther."<th>$comment</th>";

            if($commentArr[2] == 1){
                $key = $v1['name'].'_text';
                $td = '{$v[\''.$key.'\']}';
            }
            elseif($commentArr[2] == 2){
                $td = '<div class="switch">'."\n".'<div class="onoffswitch">'."\n".'<input type="checkbox" <?php echo $v[\''.$v1['name'].'\'] == 1 ? \'checked\' : \'\'; ?> class="onoffswitch-checkbox" id="'.$v1['name'].'{$v[\'id\']}">'."\n".'<label class="onoffswitch-label" for="'.$v1['name'].'{$v[\'id\']}" onclick="change'.$model.'({$v[\'id\']}, \''.$v1['name'].'\')">'."\n".'<span class="onoffswitch-inner"></span>'."\n".'<span class="onoffswitch-switch"></span>'."\n".'</label>'."\n".'</div>'."\n".'</div>';
            }
            else {
                $key = $v1['name'] == 'create_time' ? 'create_date' : $v1['name'];
                $td = '{$v[\''.$key.'\']}';
            }
            $tbod = !empty($tbod) ? $tbod."\n" : '';
            $tbod = $tbod.'<td>'.$td.'</td>';

            if (!empty($v1['index_key'])){
                $commentArr = $this->convertComment($v1);
                $comment = $commentArr[0];
                $input_type = $commentArr[2];
                if ($input_type > 0){
                    $option = '<option value="">搜索'.$comment.'</option>'."\n".'<?php foreach (config(\'map.'.$lineModel.'\')[\''.$v1['name'].'_map\'] as $k => $v) { ?>'."\n".'<option <?php if (isset($req[\''.$v1['name'].'\']) && $req[\''.$v1['name'].'\'] == $k){echo \'selected = "selected"\';} ?> value="{$k}">{$v}</option>'."\n".'<?php } ?>';

                    $search = $search."\n".'<select name="'.$v1['name'].'" class="form-control">'."\n".$option."\n".'</select>';
                }
                else {
                    $condKey = $v1['name'] == 'id' ? $lineModel.'_id' : $v1['name'];
                    $search = $search."\n".'<input type="text" value="{$req[\''.$condKey.'\']??\'\'}" name="'.$condKey.'" placeholder="搜索'.$comment.'" class="form-control">';
                }
            }
        }
        $strSearch = ['{$ther}', '{$tbod}', '{$model}', '{$search}'];
        $replace = [$ther, $tbod, $model, $search];
        $content = str_replace($strSearch, $replace, $content);
        $this->createPathFile($path, $file, $content);

        //创建展示view
        $path = root_path().'/app/admin/view/'.$table;
        $file = 'show_'.$table.'.html';
        $template = root_path().'/template/curd/show.html';
        $content = file_get_contents($template);
        $ele = '';
        foreach ($tmp as $k1 => $v1){
            if (in_array($v1['name'], ['update_time', 'create_time', 'id', 'create_at', 'update_at', 'created_at', 'updated_at'])) {
                continue;
            }
            $commentArr = $this->convertComment($v1);
            $comment = $commentArr[0];
            $input_type = $commentArr[2];

            if ($input_type == 1){
                $select = '<?php foreach (config(\'map.'.$lineModel.'\')[\''.$v1['name'].'_map\'] as $k => $v) { ?>'."\n".'<option <?php if(isset($data[\''.$v1['name'].'\']) && $data[\''.$v1['name'].'\'] == $k){ ?> selected="selected" <?php } ?> value="{$k}">{$v}</option>'."\n".'<?php } ?>';
                $html = '<div class="row dd_input_group">'."\n".'<div class="form-group">'."\n".'<label class="col-xs-4 col-sm-2 col-md-2 col-lg-1 control-label dd_input_l">'.$comment.'</label>'."\n".'<div class="col-xs-8 col-sm-6 col-md-4 col-lg-4">'."\n".'<select name="'.$v1['name'].'" class="form-control">'."\n".$select."\n".'</select>'."\n".'</div>'."\n".'<div class="col-xs-12 col-sm-4 col-md-6 col-lg-6 dd_ts">*</div>'."\n".'</div>'."\n".'</div>';
            }
            elseif($input_type == 2) {
                $map = $commentArr[1];
                $radio = '';
                foreach ($map as $k2 => $v2) {
                    $radio = $radio.'<label class="dd_radio_lable">'."\n".'<input <?php if(isset($data[\''.$v1['name'].'\']) && $data[\''.$v1['name'].'\'] == \''.$k2.'\'){ ?> checked <?php } ?> type="radio" name="'.$v1['name'].'" value="'.$k2.'" class="dd_radio"><span>'.$v2.'</span>'."\n".'</label>'."\n";
                }

                $html = '<div class="row dd_input_group">'."\n".'<div class="form-group">'."\n".'<label class="col-xs-4 col-sm-2 col-md-2 col-lg-1 control-label dd_input_l">'.$comment.'</label>'."\n".'<div class="col-xs-7 col-sm-6 col-md-4 col-lg-4">'."\n".'<div class="dd_radio_lable_left">'."\n".$radio.'</div>'."\n".'</div>'."\n".'<div class="col-xs-1 col-sm-4 col-md-6 col-lg-6 dd_ts"> *</div>'."\n".'</div>'."\n".'</div>';
            }
            else {
                $html = '<div class="row dd_input_group">'."\n".'<div class="form-group">'."\n".'<label class="col-xs-4 col-sm-2 col-md-2 col-lg-1 control-label dd_input_l">'.$comment.'</label>'."\n".'<div class="col-xs-7 col-sm-6 col-md-4 col-lg-4">'."\n".'<input type="text" name="'.$v1['name'].'" class="form-control" placeholder="请输入'.$comment.'" value="{$data[\''.$v1['name'].'\']??\'\'}">'."\n".'</div>'."\n".'<div class="col-xs-1 col-sm-4 col-md-6 col-lg-6 dd_ts">*</div>'."\n".'</div>'."\n".'</div>';
            }

            $ele = !empty($ele) ? $ele."\n" : '';
            $ele = $ele.$html;
        }
        $search = ['{$ele}', '{$model}', '{$minModel}'];
        $replace = [$ele, $model, $minModel];
        $content = str_replace($search, $replace, $content);
        $this->createPathFile($path, $file, $content);

        //生成菜单
        $this->buildMenu($model);

        //生成权限
        $this->buildRule($db_table, $model, $minModel);

        echo '创建成功'."\n";
    }

    //生成菜单
    private function buildMenu($model)
    {
        $menu = config('menu');
        $menu['请改名称'.$model]['icon'] = 'fa-user';
        $menu['请改名称'.$model]['url'] = 'admin/'.$model.'/'.lcfirst($model).'List';
        $str = var_export($menu, true);
        $path = root_path().'/config';
        $str = '<?php'."\n"."\n".'return '.$str.';'."\n";
        $this->createPathFile($path, 'menu.php', $str);
    }

    //生成map
    private function buildMap($map)
    {
        $str = var_export($map, true);
        $path = root_path().'/config';
        $str = '<?php'."\n"."\n".'return '.$str.';'."\n";
        $this->createPathFile($path, 'map.php', $str);
    }

    //将下划线命名转换为驼峰式命名
    private function convertUnderLine($str, $ucfirst = true)
    {
        $str = ucwords(str_replace('_', ' ', $str));

        $str = str_replace(' ','',lcfirst($str));

        return $ucfirst ? ucfirst($str) : $str;
    }

    //将驼峰式命名转换为下划线命名
    function uncamelize($camelCaps, $separator = '_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }

    //创建路径文件当遇到不存在的路径就新建文件夹
    private function createPathFile($path, $file, $content)
    {
        $path_file = $path.'/'.$file;
        if (!is_dir($path)) {
            //$path = iconv("UTF-8", "GBK", $path);//防止中文乱码
            mkdir($path, 0777, true);
            file_put_contents($path_file, $content);
        }
        else {
            file_put_contents($path_file, $content);
            /*if (!file_exists($path_file)){
                file_put_contents($path_file, $content);
            }
            else {
                echo $path_file."文件已存在，请先删除再执行命令\n";
                //exit_out(null, 4001, $path_file.'文件已存在，请先删除再执行命令');
            }*/
        }

        return true;
    }

    //转化分解类型是tinyint的内容
    private function convertComment($v1)
    {
        $commentArr = explode('(', trim($v1['comment']));
        $comment = trim($commentArr[0]);
        $map = [];
        $input_type = 0;//input框类型 0.text 1.select 2.radio
        if ($v1['type'] == 'tinyint'){
            if (!empty(trim($commentArr[1]))){
                $commentContent = str_replace(')', '', trim($commentArr[1]));
                if (!empty($commentContent)){
                    $commentContentArr = explode(' ', $commentContent);
                    if (!empty($commentContentArr)){
                        foreach ($commentContentArr as $k2 => $v2){
                            $v2Arr = explode('.', trim($v2));
                            $mapKey = trim($v2Arr[0]);
                            if (isset($v2Arr[1])){
                                $mapValue = trim($v2Arr[1]);
                                $map[$mapKey] = $mapValue;
                            }
                        }
                    }
                }
            }
            //判断是select框还是radio框
            if (count($map) == 2 && !empty($map['0']) && !empty($map['1'])){
                $input_type = 2;
            }
            else {
                $input_type = 1;
            }
        }

        return [$comment, $map, $input_type];
    }

    //生成权限
    private function buildRule($db_table, $model, $minModel)
    {
        $database = env('database.database');
        $sql = "SELECT TABLE_NAME,TABLE_COMMENT FROM information_schema.TABLES WHERE table_schema='".$database."' and TABLE_NAME = '".$db_table."'";
        $tmp = Db::query($sql);
        $title = str_replace('表', '', $tmp[0]['TABLE_COMMENT']);
        $menu_title = $title.'管理';

        $rule = config('rule');
        $rule[$menu_title] = [
            [
                'name' => $model.'/'.$minModel.'List',
                'title' => '查看'.$title.'列表',
            ],
            [
                'name' => $model.'/add'.$model,
                'title' => '添加'.$title,
            ],
            [
                'name' => $model.'/edit'.$model,
                'title' => '编辑'.$title,
            ],
            [
                'name' => $model.'/del'.$model,
                'title' => '删除'.$title,
            ],
            [
                'name' => $model.'/change'.$model,
                'title' => '改变'.$title.'状态',
            ],
        ];

        $str = var_export($rule, true);
        $path = root_path().'/config';
        $str = '<?php'."\n"."\n".'return '.$str.';'."\n";
        $this->createPathFile($path, 'rule.php', $str);
    }
}
