<?php
/*
 * categories.php服务端逻辑:
 *
 * 1.列表数据查询(任何时候)
 *  查询数据
 *  $categories = xiu_fetch_all('select * from categories');
 *
 * 2.新增数据( 包含空表单返回，还有 POST 回发)
 *  -必须是POST请求
 *  -并且没有在 URL 中传递 ID
 *
 * 3.更新(编辑)( 包含拿到有数据的表单，以及 POST 回发)
 *  -必须是 POST 请求
 *  -并且在 URL 中传递了 ID
 *
 * 所以，以上总共包含五种类型请求
 * */
//确保用户登录了才能访问此页面
require_once '../functions.php';

xiu_get_current_user();

function add_category(){
        //1.校验
        if (empty($_POST['name']) || empty($_POST['slug'])) {
            $GLOBALS['message'] = '请完整填写表单!';
            //失败
            $GLOBALS['success'] = false;
            return;
        }
        //2.持久化
        //接收并保存
        $name = $_POST['name'];
        $slug = $_POST['slug'];

        //调用封装好的xiu_execute()方法,values里面的顺序要跟数据库里面一致
        // insert into categories values (null, 'slug', 'name');
        $rows = xiu_execute("insert into categories values (null , '{$slug}' , '{$name}');");

    $GLOBALS['success'] = $rows > 0;
    $GLOBALS['message'] = $rows <= 0 ? '添加失败!' : '添加成功!';
    //3.响应
    //这里的响应就是渲染页面
}

function edit_category () {
    //执行到edit_category()函数时，能够拿到$current_edit_category全局变量，这个里面的id就是我们下面sql语句中需要修改的id
    //若不想更新，则可以拿原有的数据，首先申明一下全局变量
    global $current_edit_category;

//    //只有当编辑并点保存时，才会触发执行
//    //1.校验
//    if (empty($_POST['name']) || empty($_POST['slug'])) {
//        $GLOBALS['message'] = '请完整填写表单!';
//        //失败
//        $GLOBALS['success'] = false;
//        return;
//    }
// //这里不用添加判断，因为如果没有提交值，则可以使用原有的值，如果提交了值，则就可以使用以提交过来的值

    //2.持久化
    //接收并保存
    $id = $current_edit_category['id'];
    $name = empty($_POST['name']) ? $current_edit_category['name'] : $_POST['name'];

    //同步数据
    $current_edit_category['name'] = $name;
    $slug = empty($_POST['slug']) ? $current_edit_category['slug'] : $_POST['slug'];
    $current_edit_category['slug'] = $slug;

    //调用封装好的xiu_execute()方法,values里面的顺序要跟数据库里面一致
    // update categories set slug = '' , name = ''where id ='';
    $rows = xiu_execute("update categories set slug = '{$slug}' , name = '{$name}'where id ={$id}");

    $GLOBALS['success'] = $rows > 0;
    $GLOBALS['message'] = $rows <= 0 ? '更新失败!' : '更新成功!';
    //3.响应
    //这里的响应就是渲染页面
}

//判断是否为需要编辑的数据
//======================================================================================================
//判断是编辑主线还是添加主线
if (empty($_GET['id'])) {
    //添加
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        add_category();
    }
} else {
    //编辑
    // 客户端通过 URL 传递了一个  ID
    // =>意味着,客户端是要来拿一个修改数据的表单
    // =>即,需要拿到用户想要的数据

    //数据库查询,并接收
    $current_edit_category = xiu_fetch_one('select * from categories where id = ' . $_GET['id']);//存在有sql注入问题,这里不考虑

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        edit_category();
    }
}
//======================================================================================================

//如果修改操作和查询操作在一起,一定先做修改,再查询
//服务端添加数据
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //一旦表单提交请求并且没有通过 URL 提交 ID ,就意味着是要添加数据
    if (empty($_GET['id'])) {
        add_category();
    } else {
        edit_category();
    }
}

//查询数据
$categories = xiu_fetch_all('select * from categories');



?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Categories &laquo; Admin</title>
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
            <h1>分类目录</h1>
        </div>
        <!-- 有错误信息时展示 -->
        <?php if (isset($message)): ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>成功！</strong><?php echo $message; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <strong>错误！</strong><?php echo $message; ?>
                </div>
            <?php endif ?>
        <?php endif ?>

        <div class="row">
            <div class="col-md-4">
                <?php if (isset($current_edit_category)): ?>
                <!-- 编辑 -->
                    <!--  动态添加新分类目录 -->
                    <!--  指向当前页面本身,发数据method="post" -->
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $current_edit_category['id']; ?>" method="post">
                    <!--
                        <?php echo $_SERVER['PHP_SELF']; ?>是指当前页面，
                        ?id=<?php echo $current_edit_category['id']; ?>是指当前编辑的那条数据的id
                    -->
                        <h2>编辑《<?php echo $current_edit_category['name']; ?>》</h2>
                        <div class="form-group">
                            <label for="name">名称</label>
                            <input id="name" class="form-control" name="name" type="text" placeholder="分类名称" value="<?php echo $current_edit_category['name']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="slug">别名</label>
                            <input id="slug" class="form-control" name="slug" type="text" placeholder="slug" value="<?php echo $current_edit_category['slug']; ?>">
                            <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">保存</button>
                        </div>
                    </form>
                <?php else: ?>
                    <!--  动态添加新分类目录 -->
                    <!--  指向当前页面本身,发数据method="post" -->
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <h2>添加新分类目录</h2>
                        <div class="form-group">
                            <label for="name">名称</label>
                            <input id="name" class="form-control" name="name" type="text" placeholder="分类名称">
                        </div>
                        <div class="form-group">
                            <label for="slug">别名</label>
                            <input id="slug" class="form-control" name="slug" type="text" placeholder="slug">
                            <p class="help-block">https://zce.me/category/<strong>slug</strong></p>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">添加</button>
                        </div>
                    </form>
                <?php endif ?>
            </div>
            <div class="col-md-8">
                <div class="page-action">
                    <!-- show when multiple checked -->
                    <!--            <a  id="btn_delete" class="btn btn-danger btn-sm" href="javascript:;" style="display: none">批量删除</a>-->
                    <a id="btn_delete" class="btn btn-danger btn-sm" href="../admin/category-delete.php" style="display: none">批量删除</a>
                    <!-- 这里最好使用物理路径 ../admin/category-delete.php-->
                </div>
                <table class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th class="text-center" width="40"><input type="checkbox"></th>
                        <th>名称</th>
                        <th>Slug</th>
                        <th class="text-center" width="100">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!--  遍历$categories数组 -->
                    <?php foreach ($categories as $item): ?>
                        <tr>
                            <td class="text-center"><input type="checkbox" data-id="<?php echo $item['id']; ?>"></td>
                            <td><?php echo $item['name']; ?></td> <!--  出现中文显示到页面上变为???问题,编码格式问题 -->
                            <td><?php echo $item['slug']; ?></td>
                            <td class="text-center">
                                <a href="../admin/categories.php?id=<?php echo $item['id']; ?>" class="btn btn-info btn-xs">编辑</a>
                                <!-- <a href="javascript:;" class="btn btn-danger btn-xs">删除</a> -->
                                <a href="../admin/category-delete.php?id=<?php echo $item['id']; ?>"
                                   class="btn btn-danger btn-xs">删除</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $current_page = 'categories'; ?>
<?php include 'inc/sidebar.php'; ?>

<script src="../static/assets/vendors/jquery/jquery.js"></script>
<script src="../static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script>
    //1.不要重复的去使用无意义的选择操作,应该采用变量去本地化
    $(function ($) {//jQuery入口函数
        //选择时机:在表格中的任意一个 checkbox 被选中的状态时
        var $tbodyCheckboxs = $('tbody input');
        var $btnDelete = $('#btn_delete');

        //##  version2 ===========================================================================
        //定义一个数组记录被选中的
        var allCheckeds = [];
        $tbodyCheckboxs.on('change', function () {
            //一下三种方式都能获取到id
            //  this.dataset['id'];//原生DOM的方式去操作
            //  console.log($(this).attr('data-id'));//获取到的是字符串
            //  console.log($(this).data('id'));//获取到的是数字
            var id = $(this).data('id');

            //根据有没有选中这个 checkbox 来决定是添加还是移除
            if ($(this).prop('checked')) {
                // allCheckeds.push(id);
                //es5新增的数组属性includes(),若里面已经存在了id，则不需要再添加
                // allCheckeds.indexOf(id) || allCheckeds.push(id);//这种方法也可行，没有兼容问题
                allCheckeds.includes(id) || allCheckeds.push(id);//有兼容问题
                // console.log(allCheckeds);
            } else {
                allCheckeds.splice(allCheckeds.indexOf(id) , 1);//从索引开始,移除一个元素
            }
            //根据剩下多少选中的 checkbox 来决定是否显示 批量删除
            allCheckeds.length ? $btnDelete.fadeIn() : $btnDelete.fadeOut();
            // $btnDelete.prop('href' , '../admin/categories.php?id=' + allCheckeds);
            $btnDelete.prop('search' , '?id=' + allCheckeds);
        });

        //找一个合适的时机，来做一个合适的事
        //全选与全不选
        $('thead input').on('change' , function () {
           //1.获取当前的选中状态
            //prop(),里面只有一个参数是获取，里面传入第二个参数表示设置
            var checked = $(this).prop('checked');
            //2.将该状态设置给标题中的每一个
            //trigger() 方法触发被选元素的指定事件类型。
            $tbodyCheckboxs.prop('checked' , checked).trigger('change');
        });

        // ## version1 ==========================================================================
        // $tbodyCheckboxs.on('change' , function () {
        //    //有任意一个 CheckBox 选中就显示, 反之则隐藏
        //     var flag = false;
        //     $tbodyCheckboxs.each(function (i , item) {
        //        //attr 和 prop 的区别:
        //        // - attr 访问的是 元素属性
        //         // - prop 访问的是元素对应的 DOM 属性
        //         // console.log($(item).attr('checked'));//使用 attr 会返回undefined
        //         // console.log($(item).prop('checked'));//使用 prop 会返回 true/false
        //         if ($(item).prop('checked')) {
        //               flag = true;
        //         }
        //     });
        //     flag ? $btnDelete.fadeIn() : $btnDelete.fadeOut();
        // });
    });

</script>
<script>NProgress.done()</script>
</body>
</html>
