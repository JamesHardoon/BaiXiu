<?php

// 载入配置文件
require_once '../config.php';

// 给用户找一个箱子（如果你之前有就用之前的，没有给个新的）
session_start();

function login()
{
    // 1. 接收并校验
    // 2. 持久化
    // 3. 响应
    if (empty($_POST['email'])) {
        $GLOBALS['message'] = '请填写邮箱';
        return;
    }
    if (empty($_POST['password'])) {
        $GLOBALS['message'] = '请填写密码';
        return;
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // 当客户端提交过来的完整的表单信息就应该开始对其进行数据校验
    $conn = mysqli_connect(XIU_DB_HOST, XIU_DB_USER, XIU_DB_PASS, XIU_DB_NAME);
    if (!$conn) {
        exit('<h1>连接数据库失败</h1>');
    }

    $query = mysqli_query($conn, "select * from users where email = '{$email}' limit 1;");

    if (!$query) {
        $GLOBALS['message'] = '登录失败，请重试！';
        return;
    }

    // 获取登录用户
    $user = mysqli_fetch_assoc($query);

    if (!$user) {
        // 用户名不存在
//    $GLOBALS['message'] = '邮箱与密码不匹配';
        $GLOBALS['message'] = '用户名不存在';
        return;
    }

    // 一般密码是加密存储的
//  if ($user['password'] !== md5($password)) {
    if ($user['password'] !== $password) {
        // 密码不正确
//    $GLOBALS['message'] = '邮箱与密码不匹配';
        $GLOBALS['message'] = '密码不正确';
        return;
    }

    // 存一个登录标识
    // $_SESSION['is_logged_in'] = true;
    //为了后续可以直接获取当前登录用户的信息,这里直接将用户信息放到session中==================================
//    $_SESSION['current_login_user_id'] = $user['id'];
    $_SESSION['current_login_user'] = $user;

    // 一切OK 可以跳转
    header('Location: ../admin/');//这里是因为把baixiu目录放在了www下面,所以所有的路径名都要到上一层去
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    login();
}

//退出功能
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    //删除登录标识
    unset($_SESSION['current_login_user']);
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Sign in &laquo; Admin</title>
    <link rel="stylesheet" href="../static/assets/vendors/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../static/assets/vendors/animate/animate.css">
    <link rel="stylesheet" href="../static/assets/css/admin.css">
</head>
<body>
<div class="login">
    <!-- 可以通过在 form 上添加 novalidate 取消浏览器自带的校验功能 -->
    <!-- autocomplete="off" 关闭客户端的自动完成功能 -->
    <form class="login-wrap<?php echo isset($message) ? ' shake animated' : '' ?>"
          action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off" novalidate>
        <img class="avatar" src="../static/assets/img/default.png">
        <!-- 作为一个优秀的页面开发人员，必须考虑一个页面的不同状态下展示的内容不一样的情况 -->
        <!-- 有错误信息时展示 -->
        <?php if (isset($message)): ?>
            <div class="alert alert-danger">
                <strong>错误！</strong> <?php echo $message; ?>
            </div>
        <?php endif ?>
        <div class="form-group">
            <label for="email" class="sr-only">邮箱</label>
            <input id="email" name="email" type="email" class="form-control" placeholder="邮箱" autofocus
                   value="<?php echo empty($_POST['email']) ? '' : $_POST['email'] ?>">
        </div>
        <div class="form-group">
            <label for="password" class="sr-only">密码</label>
            <input id="password" name="password" type="password" class="form-control" placeholder="密码">
        </div>
        <button class="btn btn-primary btn-block">登 录</button>
    </form>
</div>
<script src="../static/assets/vendors/jquery/jquery.js"></script>
<!--    用户输入完邮箱显示对应的图像-->
<script>
    $(function ($) {
        //    入口函数的作用
        //    1.单独作用域
        //    2.确保页面加载过后再执行

        //    目标:在用户输入完邮箱后,在页面上展示出这个邮箱对应的头像
        //    实现:
        //    1.让邮箱文本框失去焦点,并且能够拿到文本框中填写的邮箱地址
        //    2.获取文本框中邮箱对应的头像地址值,展示到页面的img标签中

        //定义一个正则表达式,来验证邮箱的格式
        var emailFormat = /^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/;

        //定义文本框失去焦点函数
        $('#email').on('blur', function ( ) {
            //获取文本框中的值
            // console.log($(this).val());
            var value = $(this).val();
            //忽略文本框为空或者不是一个邮箱的情况
            if (!value || !emailFormat.test(value)) return;
            //用户输入一个合理的邮箱地址
            // 获取这个邮箱对应的头像地址
            // 因为客户端的 JS 无法直接连接数据库,应该通过 JS 发送 AJAX 请求,告诉服务端的某个接口
            // 让这个接口帮助客户端获取头像地址
            // jQuery AJAX 方法 $.get()
            $.get('../admin/api/avatar.php' , { email : value } , function (res) {
                //希望 res => 这个邮箱对应的头像地址
                if ( !res ) return;
                // 展示到上面的img标签中
                //  $('.avatar').fadeOut().attr('src' , res).fadeIn();
                //  采用回调函数的形式,展示头像的显示方式
                $('.avatar').fadeOut(function () {
                    // 等到淡出完成在加载图像
                    $(this).on('load' , function () {
                        // 头像加载完成后再加载fadeIn()函数
                        $(this).fadeIn();
                    }).attr('src' , res);
                    //在原来的数据库将原来存头像的路径static/uploads/avatar.jpg改为了../static/uploads/avatar.jpg
                });
            });
        });
    });
</script>
</body>
</html>
