<?php
    // 錯誤回報設定 //請複製 
    error_reporting(E_ALL);
    ini_set("display_errors",1);

    /*
     * -----------------------------------------------------------------
     * 舊方法 / 手動切換 (已註解停用)
     * -----------------------------------------------------------------
     * 這是原本需要手動切換註解的寫法。
     */
    /*
        // require_once __DIR__ . '/../../common/cors.php';
        // require_once __DIR__ . '/../../common/conn.php';
        // require_once __DIR__ . '/../../common/cors_g5.php';
        // require_once __DIR__ . '/../../common/conn_g5.php';
    */

    /*
     * -----------------------------------------------------------------
     * 新方法 / 自動判斷 (目前使用中)
     * -----------------------------------------------------------------
     * 現在只需要引入這一個檔案，即可自動判斷環境。
     */
    require_once __DIR__ . '/../../common/env_init.php'; //請複製 

    //以下請依樣畫葫蘆開發api
    $sql = "SELECT * FROM repair_categories
            ORDER BY category_no";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($category_no, $category_name);
     //老師上課用get_result，但我們的伺服器無法解析，故改寫

    $data = [];
    while ($stmt->fetch()) {
    $data[] = [
        'category_no' => $category_no,
        'category_name' => $category_name,
    ];
    }

    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>