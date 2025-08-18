<?php
    // 錯誤回報設定 //請複製 
    error_reporting(E_ALL);
    ini_set("display_errors",1);

    require_once __DIR__ . '/../../common/env_init.php';

    $sql = "SELECT category_no, category_name FROM events_categories 
            ORDER BY category_no ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($category_no, $category_name);

    $data = [];
    while ($stmt->fetch()) {
        $data[] = [
            'category_no' => $category_no,
            'category_name' => $category_name,
        ];
    }
    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>