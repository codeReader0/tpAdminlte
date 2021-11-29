<?php

namespace app\common\validate;

use think\Db;
use think\Validate;

class Custom extends Validate
{
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
            return $field.lang('不存在');
        }

        return true;
    }

    /**
     * 添加字段验证规则
     * @access protected
     * @param  string|array  $name  字段名称或者规则数组
     * @param  mixed         $rule  验证规则或者字段描述信息
     * @return $this
     */
    public function rule($name, $rule = '')
    {
        if (is_array($name)) {
            $this->rule = $name;
            if (is_array($rule)) {
                $this->field = $rule;
            }
        } else {
            $this->rule[$name] = $rule;
        }

        return $this;
    }
}
