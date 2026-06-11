<?php
session_start();
require_once 'config/server.php';
require_once 'config/image_helper.php';
require_once 'config/date_helper.php';

// Get page from query string, default to homepage
$page = $_GET['page'] ?? 'homepage';

$routes = [
    'homepage'       => 'src/posts/homepage.php',
    'all_feed'       => 'src/posts/all_feed.php',
    'my_post'        => 'src/posts/my_post.php',
    'post'           => 'src/posts/post.php',
    'create_post'    => 'src/posts/create_post.php',
    'edit_post'      => 'src/posts/edit_post.php',
    'delete_post'    => 'src/posts/delete_post.php',
    'comment'        => 'src/posts/comment.php',
    'edit_comment'   => 'src/posts/edit_comment.php',
    'delete_comment' => 'src/posts/delete_comment.php',
    'toggle_like'    => 'src/posts/toggle_like.php',
    'declare_detail' => 'src/posts/declare_detail.php',
    'search_results' => 'src/posts/search_results.php',
    
    'login'          => 'src/auth/login.php',
    'logout'         => 'src/auth/logout.php',
    'register'       => 'src/auth/register.php',
    
    'profile'        => 'src/user/profile.php',
    'edit_profile'   => 'src/user/edit_profile.php',
    'edit_password'  => 'src/user/edit_password.php',
    
    'admin'          => 'src/admin/admin.php',
    'add_category'   => 'src/admin/add_category.php',
    'manage_users'   => 'src/admin/manage_users.php',
    'feedbyadmin'    => 'src/admin/feedbyadmin.php',
    'edit_user'      => 'src/admin/edit_user.php',
    'delete_user'    => 'src/admin/delete_user.php',
];

if (array_key_exists($page, $routes)) {
    require_once $routes[$page];
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>ขออภัย ไม่พบหน้าที่คุณต้องการเรียกใช้</p>";
}
?>
