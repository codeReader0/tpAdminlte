<?php

namespace app\common\validate;

use think\Container;
use think\Db;
use think\exception\ClassNotFoundException;
use think\Validate;

class Custom extends Validate
{
    /**
     * 验证是否唯一
     * @access public
     * @param  mixed     $value  字段值
     * @param  mixed     $rule  验证规则 格式：数据表,字段名,排除ID,主键名
     * @param  array     $data  数据
     * @param  string    $field  验证字段名
     * @return bool
     */
    public function unique($value, $rule, $data, $field)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }

        if (false !== strpos($rule[0], '\\')) {
            // 指定模型类
            $db = new $rule[0];
        } else {
            try {
                $db = Container::get('app')->model($rule[0]);
            } catch (ClassNotFoundException $e) {
                $db = Db::name($rule[0]);
            }
        }

        $map = [];
        if (isset($data[$field])) {
            $map[] = [$field, '=', $data[$field]];
        }

        $key = isset($rule[1]) ? $rule[1] : $field;
        if (strpos($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                if (isset($data[$key])) {
                    $map[] = [$key, '=', $data[$key]];
                }
            }
        } elseif (strpos($key, '=')) {
            parse_str($key, $tmp);
            $tmpArr = [];
            foreach ($tmp as $k => $v) {
                $tmpArr[$k][0] = $k;
                $tmpArr[$k][1] = '=';
                $tmpArr[$k][2] = $v;
            }
            $map = array_merge($map, array_values($tmpArr));
        }

        $pk = !empty($rule[3]) ? $rule[3] : $db->getPk();

        if (is_string($pk)) {
            if (isset($rule[2])) {
                $map[] = [$pk, '<>', $rule[2]];
            } elseif (isset($data[$pk])) {
                $map[] = [$pk, '<>', $data[$pk]];
            }
        }

        $tableFields = $db->getTableFields();
        if (in_array('delete_time', $tableFields)) {
            $map[] = ['delete_time', '=', 0];
        }
        if ($db->field($pk)->where($map)->find()) {
            return false;
        }

        return true;
    }

    /**
     * 验证是否存在
     * @access public
     * @param  mixed     $value  字段值
     * @param  mixed     $rule  验证规则 格式：数据表,字段名
     * @param  array     $data  数据
     * @param  string    $field  验证字段名
     * @return bool
     */
    public function exist($value, $rule, $data, $field)
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        $key = isset($rule[1]) ? $rule[1] : $field;

        $map[] = [$key, '=', $data[$field]];
        $db = Db::name($rule[0]);
        $pk = $db->getPk();
        $tableFields = $db->getTableFields();
        if (in_array('delete_time', $tableFields)) {
            $map[] = ['delete_time', '=', 0];
        }
        if (!$db->field($pk)->where($map)->find()) {
            return $field.'不存在';
        }

        return true;
    }
}
