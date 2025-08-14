<?php
/**
 * =================================================================
 * 全自動環境初始化檔案 (env_init.php)
 * =================================================================
 * 目的：
 *   此檔案會自動偵測當前的執行環境（本地開發或遠端伺服器），
 *   並載入對應的資料庫連線(conn)與跨域政策(CORS)設定。
 * 
 * 如何運作：
 *   1. 定義一個包含所有本地開發用主機名稱的列表 (DEV_HOSTS)。
 *   2. 抓取當前 PHP 腳本執行的主機名稱 ($_SERVER['HTTP_HOST'])。
 *   3. 判斷主機名稱是否存在於 DEV_HOSTS 列表中。
 *      - 如果是，則載入 common/local/ 資料夾內的設定檔。
 *      - 如果不是，則預設為伺服器環境，載入 common/server/ 資料夾內的設定檔。
 *
 * 開發者須知：
 *   在開發任何新的 API 檔案時，您只需要在檔案最上方引入這一個檔案即可，
 *   無需再手動切換任何設定。
 */

// 統一的錯誤回報設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定義本地開發環境的主機名稱列表
const DEV_HOSTS = ['127.0.0.1', 'localhost'];

// 取得目前的主機名稱 (例如 'localhost:8888' 或 'tibamef2e.com')
$current_host = $_SERVER['HTTP_HOST'] ?? '';

// 為了避免 port 號干擾判斷，我們將其移除 (例如 'localhost:8888' -> 'localhost')
$current_host_without_port = preg_replace('/:\d+$/', '', $current_host);

// 判斷當前環境並引入對應的設定檔
if (in_array($current_host_without_port, DEV_HOSTS)) {
    // --- 當前在本地開發環境 ---
    // 載入 local 資料夾內的設定檔
    require_once __DIR__ . '/local/cors.php';
    require_once __DIR__ . '/local/conn.php';
} else {
    // --- 當前在伺服器生產環境 ---
    // 載入 server 資料夾內的設定檔
    require_once __DIR__ . '/server/cors_g5.php';
    require_once __DIR__ . '/server/conn_g5.php';
}
?>