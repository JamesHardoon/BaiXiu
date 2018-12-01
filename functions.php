<?php
/**
 * Created by PhpStorm.
 * User: fangz
 * Date: 2018/9/22
 * Time: 15:46
 *
 * 封装大家公用的函数
 */

//载入配置文件
require_once 'config.php';

session_start();

/*
 * 获取当前用户登录信息,如果没有获取到则自动跳转到登录页面
 *定义函数时,一定要注意:函数名与内置函数冲突问题,一般添加一个项目的前缀
 * 这里使用xiu_get_current_user
 *
 * 判断一个函数是否被定义:
 * JS中,判断方式: typeof fn === 'function'
 * PHP中,判断方式: function_exists('get_current_user);
 * */
function xiu_get_current_user () {
    if (empty($_SESSION['current_login_user'])) {
        //没有当前用户信息,以为这没有登录
        header('location: /admin/login.php');
        exit();//没有必要再执行之后的代码
    }
    return $_SESSION['current_login_user'];
}

/*
 *通过一个数据库查询获取多条数据
 * 返回的是一个索引数组,索引数组嵌套关联数组
 * */

function xiu_fetch_all ($sql) {
    $conn = mysqli_connect(XIU_DB_HOST , XIU_DB_USER , XIU_DB_PASS , XIU_DB_NAME);

    if (!$conn) {
        exit('连接失败');
    }

    $query = mysqli_query($conn , $sql);

    if (!$query) {
        //查询失败
        return false;
    }

    //定义一个空数组
    $result = array();

    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }

    //关闭连接
    //不管有没有写下面关闭连接的代码,php默认是关闭连接的
    mysqli_free_result($query);
    mysqli_close($conn);

    return $result;
}

/*
 * 获取单条数据
 * 返回的是一个关联数组
 */
function xiu_fetch_one ($sql) {
    $res = xiu_fetch_all($sql);
    return isset($res[0]) ? $res[0] : null;
}

/*
 * 非查询的查询语句,就是执行一个增删改语句
 *
 * */
function xiu_execute ($sql) {
    $conn = mysqli_connect(XIU_DB_HOST , XIU_DB_USER , XIU_DB_PASS , XIU_DB_NAME);
    if (!$conn) {
        exit('连接失败');
    }
    $query = mysqli_query($conn , $sql);
    if (!$query) {
        //查询失败
        return false;
    }

    //对于增删改类的操作都是获取受影响行数
    $affected_rows = mysqli_affected_rows($conn);

    //关闭连接
    mysqli_close($conn);

    //return $result;
}
