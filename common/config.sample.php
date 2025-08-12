<?php
// config.sample.php
// 這是設定檔的範本。請將此檔案複製一份並改名為 config.php，
// 然後填入您自己本地端的開發環境設定。
// config.php 不會被上傳到 Git。

// --- 資料庫設定 ---
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'kllv_db');
define('DB_PORT', 8889);

// --- CORS 跨域設定 ---
// 您前端 Vue 開發伺服器的網址
define('ALLOWED_ORIGINS', [
    'http://127.0.0.1:5500',
    'http://localhost:5500'
]);
?>