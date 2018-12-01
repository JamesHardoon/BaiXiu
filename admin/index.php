<?php
//
//// 校验数据当前访问用户的 箱子（session）有没有登录的登录标识
//session_start();
//
//if (empty($_SESSION['current_login_user'])) {
//  // 没有当前登录用户信息，意味着没有登录
//  header('Location: /admin/login.php');
//}

//直接引入公共的functions.php页面
require_once '../functions.php';

//判断用户是否登录一定要最先操作
xiu_get_current_user();

//获取界面所需要的数据
//重复的操作一定要封装起来

//文章
$posts_count = xiu_fetch_one('select count(1) as num from posts; ')['num'];
//var_dump($posts_count['num']);

//草稿
$posts_count_drafted = xiu_fetch_one('select count(1) as num from posts where status = "drafted"; ')['num'];
//var_dump($posts_count['num']);

//分类
$categories_count = xiu_fetch_one('select count(1) as num from categories; ')['num'];
//var_dump($categories_count['num']);

//评论
$comments_count = xiu_fetch_one('select count(1) as num from comments; ')['num'];
//var_dump($comments_count['num']);

//待审核评论
$comments_count_approved = xiu_fetch_one('select count(1) as num from comments where status = "approved"; ')['num'];
//var_dump($comments_count['num']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Dashboard &laquo; Admin</title>
    <link rel="stylesheet" href="../static/assets/vendors/bootstrap/css/bootstrap.css">
    <link rel="stylesheet" href="../static/assets/vendors/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="../static/assets/vendors/nprogress/nprogress.css">
    <link rel="stylesheet" href="../static/assets/css/admin.css">
    <script src="../static/assets/vendors/nprogress/nprogress.js"></script>
</head>
<body>
<script>NProgress.start()</script>

<div class="main">
    <?php include 'inc/navbar.php'; ?>

    <div class="container-fluid">
        <div class="jumbotron text-center">
            <h1>One Belt, One Road</h1>
            <p>Thoughts, stories and ideas.</p>
            <p><a class="btn btn-primary btn-lg" href="post-add.php" role="button">写文章</a></p>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">站点内容统计：</h3>
                    </div>
                    <ul class="list-group">
                        <!--              <li class="list-group-item"><strong>10</strong>篇文章（<strong>2</strong>篇草稿）</li>-->
                        <!--              <li class="list-group-item"><strong>6</strong>个分类</li>-->
                        <!--              <li class="list-group-item"><strong>5</strong>条评论（<strong>1</strong>条待审核）</li>-->
                        <li class="list-group-item">
                            <strong><?php echo $posts_count ?></strong>篇文章（<strong><?php echo $posts_count_drafted ?></strong>篇草稿）
                        </li>
                        <li class="list-group-item"><strong><?php echo $categories_count ?></strong>个分类</li>
                        <li class="list-group-item">
                            <strong><?php echo $comments_count ?></strong>条评论（<strong><?php echo $comments_count_approved ?></strong>条待审核）
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 可以在这里添加一个canvs画布,用于添加图标 -->
            <canvs id="chart"></canvs>

            <div class="col-md-4"></div>
            <div class="col-md-4"></div>
        </div>
    </div>
</div>

<?php $current_page = 'index'; ?>
<?php include 'inc/sidebar.php'; ?>

<script src="../static/assets/vendors/jquery/jquery.js"></script>
<script src="../static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script>NProgress.done()</script>

</body>
</html>
