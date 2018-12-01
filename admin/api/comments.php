<?php
/**
 * Created by PhpStorm.
 * User: fangz
 * Date: 2018/9/28
 * Time: 14:50
 *
 * 作用：接收客户端的 AJAX 请求，返回评论数据
 */

//载入封装的所有函数
require_once '../../functions.php';

//取得客户端传递过来的分页页码
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);

$length = 3;
//根据页码计算越过多少条
$offset = ($page - 1) * $page;

$sql = sprintf('SELECT
	comments.*,
   posts.title as posts_title
from comments
inner join posts on comments.post_id = posts.id
order by comments.created desc 
limit %d , %d;' , $offset , $length);

//windows常用的编程字体 Consolas,其他常用的编程字体还有 Fira Code , Source Code Pro

//查询所有的评论数据
$comments = xiu_fetch_all($sql);

//因为网络之间传输的只能是字符串，
//所有我们先将数据转换成字符串(序列化)
$json = json_encode($comments);

//设置一下响应的响应体类型为 JSON
header('Content-Type: application/json');

//响应给客户端
echo $json;
