<?php
/**
 * Created by PhpStorm.
 * User: fangz
 * Date: 2018/9/23
 * Time: 9:54
 *
 * 根据客户端传过来的ID删除对应数据
 */

//引入文件
require_once '../functions.php';

//判断是否为空
if (empty($_GET['id'])) {
    exit('缺少必要参数');
}

//接收ID
//$id = (int)$_GET['id'];
$id = $_GET['id'];
// ==>'1 or 1 = 1'
//这种形式就是sql注入
//delete from categories where id = 1 or 1 = 1;//1 = 1肯定为真,
//那么where子句相当没有,这时会把categories里面的数据全面删除掉
//解决方法: 先判断是否为数字,若是数据则继续,反之,则不继续
//$id = (int) $_GET['id'];//用这种方式解决以上问题,一般返回 or 前面的数字

//以字符串拼接的方式,接收受影响行
//支持批量删除
$rows = xiu_execute('delete from categories where id in  ('. $id .');');

//if ($rows > 0) { }

//删除数据后重新跳转回categories页面
header('location: ../admin/categories.php');//还是因为目录的问题,必须加上..