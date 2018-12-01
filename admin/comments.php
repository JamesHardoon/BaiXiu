<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>Comments &laquo; Admin</title>
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
            <h1>所有评论</h1>
        </div>
        <!-- 有错误信息时展示 -->
        <!-- <div class="alert alert-danger">
          <strong>错误！</strong>发生XXX错误
        </div> -->
        <div class="page-action">
            <!-- show when multiple checked -->
            <div class="btn-batch" style="display: none">
                <button class="btn btn-info btn-sm">批量批准</button>
                <button class="btn btn-warning btn-sm">批量拒绝</button>
                <button class="btn btn-danger btn-sm">批量删除</button>
            </div>
            <ul class="pagination pagination-sm pull-right">
<!--                <li><a href="#">上一页</a></li>-->
<!--                <li><a href="#">1</a></li>-->
<!--                <li><a href="#">2</a></li>-->
<!--                <li><a href="#">3</a></li>-->
<!--                <li><a href="#">下一页</a></li>-->

            </ul>
        </div>
        <table class="table table-striped table-bordered table-hover">
            <thead>
            <tr>
                <th class="text-center" width="40"><input type="checkbox"></th>
                <th>作者</th>
                <th>评论</th>
                <th>评论在</th>
                <th>提交于</th>
                <th>状态</th>
                <th class="text-center" width="150">操作</th>
            </tr>
            </thead>
            <tbody>
            <!-- <tr class="danger">
               <td class="text-center"><input type="checkbox"></td>
               <td>大大</td>
               <td>楼主好人，顶一个</td>
               <td>《Hello world》</td>
               <td>2016/10/07</td>
               <td>未批准</td>
               <td class="text-center">
                 <a href="post-add.php" class="btn btn-info btn-xs">批准</a>
                 <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
               </td>
             </tr>
             <tr>
               <td class="text-center"><input type="checkbox"></td>
               <td>大大</td>
               <td>楼主好人，顶一个</td>
               <td>《Hello world》</td>
               <td>2016/10/07</td>
               <td>已批准</td>
               <td class="text-center">
                 <a href="post-add.php" class="btn btn-warning btn-xs">驳回</a>
                 <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
               </td>
             </tr>
             <tr>
               <td class="text-center"><input type="checkbox"></td>
               <td>大大</td>
               <td>楼主好人，顶一个</td>
               <td>《Hello world》</td>
               <td>2016/10/07</td>
               <td>已批准</td>
               <td class="text-center">
                 <a href="post-add.php" class="btn btn-warning btn-xs">驳回</a>
                 <a href="javascript:;" class="btn btn-danger btn-xs">删除</a>
               </td>
             </tr>-->

            </tbody>
        </table>
    </div>
</div>

<?php $current_page = 'comments'; ?>
<?php include 'inc/sidebar.php'; ?>

<!-- 使用script标签做模板,因为它里面有着色，且不在页面上显示出来 -->
<script id="comments_tmpl" type="text/x-jsrender">
      {{!-- 循环遍历，这就是jsrender的注释形式 --}}
      {{for comments}}
         {{!-- <tr><td>{{:#index}}</td><td>{{:content}}</td></tr> --}}
          <tr {{if status == 'held'}} class="warning"{{else status == 'rejected'}} class="danger"{{/if}} data-id="{{:id}}">
               <td class="text-center"><input type="checkbox"></td>
               <td>{{:author}}</td>
               <td>{{:content}}</td>
               <td>《{{:posts_title}}》</td>
               <td>{{:created}}</td>
               <td>{{:status}}</td>
               <td class="text-center">
                 {{if status == 'held'}}
                    <a href="post-add.php" class="btn btn-info btn-xs">批准</a>
                    <a href="post-add.php" class="btn btn-warning btn-xs">拒绝</a>
                 {{/if}}
                 <a href="javascript:;" class="btn btn-danger btn-xs btn-delete">删除</a>
               </td>
         </tr>
      {{/for}}
</script>
<script src="../static/assets/vendors/jquery/jquery.js"></script>
<script src="../static/assets/vendors/bootstrap/js/bootstrap.js"></script>
<script src="../static/assets/vendors/jsrender/jsrender.js"></script>
<!-- 引入分页插件twbs-pagination -->
<script src="../static/assets/vendors/twbs-pagination/jquery.twbsPagination.js"></script>

<script>
    //发送 AJAX 请求获取列表数据
    // $.getJSON('../admin/api/comments.php', { page : 1 }, function (res) {
    //     //请求得到响应过后自动执行
    //     //console.log(res);
    //     //将数据渲染到页面上(使用jsrender模块引擎)
    //     //准备一个模板使用的数据
    //     /*var data = { };
    //     data.comments = res;
    //     //将数据渲染到页面上
    //     var html = $('#comments_tmpl').render(data);
    //     console.log(html);*/
    //     //将数据渲染到页面上，写法与上面的相同，更简洁
    //     var html = $('#comments_tmpl').render({ comments : res });
    //     $('tbody').html(html);
    // });



    var currentPage = 1;

    function loadPageData(page) {
        $.getJSON('../admin/api/comments.php', { page : page }, function (res) {
            if (page > res.total_page) {
                loadPageData(res.total_pages);
                return false;
            }

            //第一次回调时没有初始化分页组件
            //第二次调用这个组件不会重新渲染分页组件，解决方案就是使用'destroy'
            $('.pagination').twbsPagination('destroy');//============================

            $('.pagination').twbsPagination({//============================
                startPage: page,
                totalPages: res.total_pages,
                visiablePages: 5,
                initiateStartPageClick: false,
                onPageClick: function (e ,page) {
                    //第一次初始化时就会触发一次
                    //点击分页代码会在这里执行
                    loadPageData(page);
                }
            });

            //渲染数据
            var html = $('#comments_tmpl').render({ comments : res });
            $('tbody').fadeOut(function () {
                $(this).html(html).fadeIn();
            });
            currentPage = page;
        });
    }

    // function loadPageData(page) {
    //     $('tbody').fadeOut();
    //     $.getJSON('../admin/api/comments.php', { page : page }, function (res) {
    //         var html = $('#comments_tmpl').render({ comments : res });
    //         $('tbody').html(html).fadeIn();
    //     });
    // }

    loadPageData(currentPage);

    //twbs-pagination分页插件的使用
    $('.pagination').twbsPagination({
        totalPages: 3,//============================
        visiablePages: 3,//============================
        onPageClick: function (e ,page) {
            //第一次初始化时就会触发一次
            loadPageData(page);
        }
    });

    //删除功能
    //=============================
    //由于删除按钮是动态添加的，而且执行动态添加的代码是在此之后执行的
    //使用委托事件的方式
    $('tbody').on('click' , '.btn-delete' , function () {
        //删除单条数据的时机
        // console.log(this);
        // 1.拿到需要删除的数据 ID
        var $tr = $(this).parent().parent();
        var id  = $tr.data('id');
        // 2.发送一个 AJAX 请求，告诉服务端要删除哪一条具体的数据
        $.get('../admin/api/comment-delete.php' , { id : id } , function (res) {
            //console.log(res);
            if (!res) return;
            // 3.根据服务端返回的删除是否成功决定是否在界面上移除这个元素
            // 4.重新再载入当前这一页的数据
            loadPageData(page);

            //$tr.remove();
        });
    });

</script>
<script>NProgress.done()</script>
</body>
</html>
