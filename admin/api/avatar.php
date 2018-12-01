<?php
/**
 * Created by PhpStorm.
 * User: fangz
 * Date: 2018/9/22
 * Time: 11:15
 *
 * 根据用户邮箱获取用户头像
 * email => image
 */

require_once '../../config.php';

//1.接收传过来的邮箱
if (empty($_GET['email'])) {
    exit('缺少必要参数');
}
$email = $_GET['email'];

//2.查询对应的头像地址
$conn = mysqli_connect(XIU_DB_HOST ,XIU_DB_USER , XIU_DB_PASS , XIU_DB_NAME);

if(!$conn) {
  exit('数据库连接失败');
};

$res = mysqli_query($conn , "select avatar from users where email = '{$email}' limit 1;");

if (!$res) {
    exit('查询失败');
}

$row = mysqli_fetch_assoc($res);

//3.echo
echo $row['avatar'];

