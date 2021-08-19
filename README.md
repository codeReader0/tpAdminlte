# tpAdminlte
根据tp5.1结合adminlte2.4 创建的通用性后台UI框架 可一键生成curd对应的控制器，模型，页面。

完全根据数据库来建立curd模型

## 命令： php think curd --table user

## **主要特性**
- 自动生成input框是select类型的数据 必须要求字段的类型为tinyint 注释格式是：订单状态(1.未付款 2.已付款 3.已收货 4.已退款)
- 自动生成input框是radio类型的数据 必须要求字段的类型为tinyint 注释格式是：是否冻结(0.否 1.是) 注意：radio类型和select的区别在于radio只能有两个值，而且值只能是0和1 否则则会生成select框  radio还能在列表中生成快捷开关
- 其余类型均生成text类型框
- 注意字段注释只写页面展示的该字段的名称。其余的辅助注释都应该写在一个括号里面。
- 会根据数据库的索引自动生成列表搜索条件
- 会根据字段类型生成控制器验证模型
- 生成的完全是基于thinkphp5.1和adminlte2.4 的控制器，模型，以及页面(基于jquery和bootstrap) 生成后可随意改动
- 如果是phpstorm 那么control + alt + i (mac是：command + option + l)可以格式化自动生成的代码

## **后续支持**
- 一键生成api文档 根据数据库里面字段的注释
- 一键生成数据库字典
- 图片，富文本，时间，大内容

## **下载安装**
- 将代码clone下来，在根目录执行composer install
- 根据.example.env示例创建.env文件
- 运行根目录下的init.sql
