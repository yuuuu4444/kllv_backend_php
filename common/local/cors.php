<?php 
// 引入建立的設定檔
require_once __DIR__ . '/config.php';

// 從設定檔讀取允許的網域列表
$allowed_origins = ALLOWED_ORIGINS;

// 抓到請求的來源網域
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 檢查來源是否存在於允許列表中
if ($origin && in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true"); // ✅ 讓 Cookie (PHPSESSID) 可用
    // 建議加上 Vary，讓快取分辨不同 Origin
    header('Vary: Origin');
}

// 允許所有網域連線
// header("Access-Control-Allow-Origin: " . "*");


// 允許的 HTTP 方法 
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");

// 允許的請求標頭 (Content-Type 是為了 JSON，Authorization 是為了未來登入)
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

// 如果是預檢請求，直接結束
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit();
}
?>