<?php

require_once '../functions.php';

xiu_get_current_user();

//接收筛选参数
//======================================================================
$where = '1 = 1';
$search = '';

//分类筛选
if (isset($_GET['category']) && $_GET['category'] !== 'all') {
    $where .= ' and posts.category_id = ' . $_GET['category'];
    $search .= ' &category = ' . $_GET['category'];
}

//状态筛选
if (isset($_GET['status']) && $_GET['status'] !== 'all') {
    //因为posts.ststus得到的是字符串，不是数字，所有不能像上面一样使用.的形式进行拼接
    $where .= " and posts.status = '{$_GET['status']}'";
    $search .= '&status=' . $_GET['status'];
}


//处理分页参数
//=======================================================================
//定义每页显示20条数据
$size = 20;
$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
// 必须 >= 1 && <= 总页数

// $page = $page < 1 ? 1 : $page;

if ($page < 1) {
    //跳转到第一页
    header('Location: ../admin/posts.php?page=1'.$search);
}

//只要处理分页功能一定会用到最大页码数
//总页数
$total_count = (int)xiu_fetch_one("select count(1) as count from posts
inner join categories on posts.category_id = categories.id
inner join users on posts.user_id = users.id
where {$where};")['count'];
$total_pages = (int)ceil($total_count / $size);

// $page = $page > $total_pages ? $total_pages : $page;
if ($page > $total_pages) {
    //跳转到最后一页
    header('Location:../admin/posts.php?page= ' . $total_pages . $serach);
}

//获取全部数据
//=======================================================================
/*//$posts = xiu_fetch_all('select * from posts;');
//关联数据查询
//使用这种方式，只需要查询一个数据库，
//若使用get_category()和get_user()方式，则需要查询9次数据库，所有使用这种方式更加有效率
//根据文章的创建时间降序排序
// order by posts.created desc
// 越过0条取20条数据,即一页显示20条数据
// limit 0 , 20
//linit 20 , 20表示越过20条数据，取20条数据，即第二页(一页显示20数据)*/
//计算出越过多少条
$offset = ($page - 1) * $size;

$posts = xiu_fetch_all("SELECT 
	posts.id,
	posts.title,
    users.nickname as user_name,
	categories.name as category_name,
	posts.created,
	posts.status 
FROM `posts` 
inner join categories on posts.category_id = categories.id
inner join users on posts.user_id = users.id
where {$where}
order by posts.created desc
limit {$offset} , {$size} ;");

//查询全部的分类数据
//=======================================================================
$categories = xiu_fetch_all('select * from categories;');

//处理分页页码
//=======================================================================
/*
 *  实现分页功能目标：
 *  1.当前页码显示高亮
 *  2.左侧和右侧各有两个页码
 *  3.开始页码不能小于1
 *  4.结束页码不能大于最大页数
 *  5.当前页码不为1时，显示上一页
 *  6.当前页码不为最大值时，显示下一页
 *  7.当开始页码不等于1时，显示省略号
 *  8.当结束页码不等于最大时，显示省略号
 * */
$visiables = 5;
//计算最大和最小展示的页码
$begin = $page - ($visiables - 1) / 2;
$end = $begin + $visiables - 1;

//重点考虑合理性问题
// begin > 0  end <= total_pages
$begin = $begin < 1 ? 1 : $begin; // 确保了 begin 不会小于 1
$end = $begin + $visiables - 1; // 因为上一行可能导致 begin 变化，这里同步两者关系
$end = $end > $total_pages ? $total_pages : $end; // 确保了 end 不会大于 total_pages
$begin = $end - $visiables + 1; // 因为 上一行 可能改变了 end，也就有可能打破 begin 和 end 的关系
$begin = $begin < 1 ? 1 : $begin; // 确保不能小于 1
/*//=======================================================================

//总页数
$total_count = (int)xiu_fetch_one('select count(1) as num from posts;')['num'];
$total_page = (int)ceil($total_count / $size);

//计算页码开始

$visiables = 5;//$visiables表示页面上显示的页码的可见个数
$region = ($visiables - 1) / 2;//$region表示左右区间
$begin = $page - $region;//开始页码
$end = $begin + $visiables;//结束页码+1

//可能出现 $begin 和 $end 的不合理的情况
//$begin 必须 > 0
//确保 $begin 最小为1
if ($begin < 1) {
    $begin = 1;
    //begin 的修改意味着必须要修改 end ,必须确保 $begin 和 $end 之间相差 4
    $end = $begin + $visiables;
}

// $end 必须 <= 最大页数
//如何获取最大页数
//$total_pages = ceil($totals_count / $size);//向上取整
if ($end > $total_page + 1) {
    // end 超出范围
    $end = $total_page + 1;
    // end 修改意味着必须修改 begin
    $begin = $end - $visiables;
    //再判断，
    if ($begin < 1) {
        $begin = 1;
    }
}*/

//处理数据格式转换
//=======================================================================
/*
 *  转换状态显示
 *  @param String $status   英文状态
 *  @return String          中文状态
 * */
//状态
function convert_status ($status) {
    $dict = array(
        'published'=>'已发布',
        'drafted'=>'草稿',
        'trashed'=>'回收站',
    );
    //判断是否存在
    return isset($dict[$status]) ? $dict[$status] : '未知';
}

//发布时间
function convert_date ($created) {
    // 2017-07-01 08:08:00
    //时间戳
    $timestamp = strtotime($created);
    //<br>中的r需要转译一下，这样日期和时间就能换行显示
    return date('Y年m月d日<b\r>H:i:s' , $timestamp);
}

//分类
//通过关联数据查询的形式，直接查询到所需要的数据，不再需要写两个函数

////如果通过这种方式的话会导致每一行数据产生一次查询数据库的操作，导致操作数据库过去频繁
//function get_category ($category_id) {
//    //获取关联数组中name的键,这里sql语句不能使用单引号，要使用双引号
//    return xiu_fetch_one("select name from categories where id = {$category_id}")['name'];
//}
//
////作者
//function get_user ($user_id) {
//    return xiu_fetch_one("select nickname from users where id = {$user_id}")['nickname'];
//}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>Posts &laquo; Admin</title>
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
      <div class="page-title">
        <h1>所有文章</h1>
        <a href="post-add.php" class="btn btn-primary btn-xs">写文章</a>
      </div>
      <!-- 有错误信息时展示 -->
      <!-- <div class="alert alert-danger">
        <strong>错误！</strong>发生XXX错误
      </div> -->
      <div class="page-action">
        <!-- show when multiple checked -->
        <a class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>
        <!--  让表单action到当前页面，GET提交 -->
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <select name="category" class="form-control input-sm">
            <option value="all">所有分类</option>
            <?php foreach ($categories as $item): ?>
            <option
                value="<?php echo $item['id']; ?>"
                <?php echo isset($_GET['category']) && $_GET['category'] == $item['id'] ? 'selected' : ''; ?>
            >
                <?php echo $item['name']; ?></option>
            <?php endforeach ?>
          </select>
          <select name="status" class="form-control input-sm">
            <option value="all">所有状态</option>
            <option value="drafted"
                <?php echo isset($_GET['status']) && $_GET['status'] == 'drafted' ? 'selected' : ''; ?>
            >草稿</option>
            <option value="published"
                <?php echo isset($_GET['status']) && $_GET['status'] == 'published' ? 'selected' : ''; ?>
            >已发布</option>
            <option value="trashed"
                <?php echo isset($_GET['status']) && $_GET['status'] == 'trashed' ? 'selected' : ''; ?>
            >回收站</option>
          </select>
          <button class="btn btn-default btn-sm">筛选</button>
        </form>
        <ul class="pagination pagination-sm pull-right">
          <li><a href="#">上一页</a></li>
<!--          <li><a href="#">1</a></li>-->
<!--          <li><a href="#">2</a></li>-->
<!--          <li><a href="#">3</a></li>-->
            <!--  要确保 $begin 和 $end 之间相差 4 ，故 $i < $end 不能 $i <= $end -->
            <?php for ($i = $begin; $i <= $end; $i++): ?>
                <li <?php echo $i === $page ? 'class= "active"' : ''; ?>>
                    <a href="?page=<?php echo $i.$search; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor ?>
          <li><a href="#">下一页</a></li>
        </ul>
      </div>
      <table class="table table-striped table-bordered table-hover">
        <thead>
          <tr>
            <th class="text-center" width="40"><input type="checkbox"></th>
            <th>标题</th>
            <th>作者</th>
            <th>分类</th>
            <th class="text-center">发表时间</th>
            <th class="text-center">状态</th>
            <th class="text-center" width="100">操作</th>
          </tr>
        </thead>
        <tbody>
<!--          <tr>-->
<!--            <td class="text-center"><input type="checkbox"></td>-->
<!--            <td>随便一个名称</td>-->
<!--            <td>小小</td>-->
<!--            <td>潮科技</td>-->
<!--            <td class="text-center">2016/10/07</td>-->
<!--            <td class="text-center">已发布</td>-->
<!--            <td class="text-center">-->
<!--              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>-->
<!--              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>-->
<!--            </td>-->
<!--          </tr>-->
<!--          <tr>-->
<!--            <td class="text-center"><input type="checkbox"></td>-->
<!--            <td>随便一个名称</td>-->
<!--            <td>小小</td>-->
<!--            <td>潮科技</td>-->
<!--            <td class="text-center">2016/10/07</td>-->
<!--            <td class="text-center">已发布</td>-->
<!--            <td class="text-center">-->
<!--              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>-->
<!--              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>-->
<!--            </td>-->
<!--          </tr>-->
<!--          <tr>-->
<!--            <td class="text-center"><input type="checkbox"></td>-->
<!--            <td>随便一个名称</td>-->
<!--            <td>小小</td>-->
<!--            <td>潮科技</td>-->
<!--            <td class="text-center">2016/10/07</td>-->
<!--            <td class="text-center">已发布</td>-->
<!--            <td class="text-center">-->
<!--              <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>-->
<!--              <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>-->
<!--            </td>-->
<!--          </tr>-->
            <?php foreach ($posts as $item): ?>
            <tr>
                <td class="text-center"><input type="checkbox"></td>
                <td><?php echo $item['title']; ?></td>
<!--                <td>--><?php //echo get_user($item['user_id']); ?><!--</td>-->
<!--                <td>--><?php //echo get_category($item['category_id']); ?><!--</td>-->
                <td><?php echo $item['user_name']; ?></td>
                <td><?php echo $item['category_name']; ?></td>
                <td class="text-center"><?php echo convert_date($item['created']); ?></td>
                <!-- 一旦输出的判断或者转换逻辑过于复杂，不建议直接写在混编位置 -->
                <td class="text-center"><?php echo convert_status($item['status']); ?></td>
                <td class="text-center">
                  <a href="javascript:;" class="btn btn-default btn-xs">编辑</a>
                  <a href="post-delete.php?id=<?php echo $item['id']; ?>" class="btn btn-danger btn-xs">删除</a>
                </td>
            </tr>
            <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php $current_page = 'posts'; ?>
  <?php include 'inc/sidebar.php'; ?>

  <script src="../static/assets/vendors/jquery/jquery.js"></script>
  <script src="../static/assets/vendors/bootstrap/js/bootstrap.js"></script>
  <script>NProgress.done()</script>
</body>
</html>
