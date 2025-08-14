<?php
// 錯誤回報設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 引入建立的設定檔
require_once __DIR__ . '/config_g5.php';

try {
    // 使用 config.php 中定義的常數來連線
    $mysqli = new mysqli(
        DB_HOST, 
        DB_USER, 
        DB_PASS, 
        DB_NAME,
        DB_PORT
    );
    // echo "資料庫連線成功！！";
} catch (mysqli_sql_exception $e) {
    echo '資料庫連線錯誤：' . $e->getMessage();
    exit();
}
?>


