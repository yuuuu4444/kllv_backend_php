<?php
    require_once __DIR__ . '/../../common/env_init.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        $location    = trim($_POST['location'] ?? '');
        $category_no = filter_input(INPUT_POST, 'category_no', FILTER_VALIDATE_INT) ?: 0;
        $description = trim($_POST['description'] ?? '');
        $reporter    = trim($_POST['reporter_id'] ?? '');
        $reported_at = trim($_POST['reported_at'] ?? date('Y-m-d'));
        $status      = filter_input(INPUT_POST, 'status', FILTER_VALIDATE_INT);
    
        if ($status === null) {
            $status = 0; // 預設為 0
        }
    
        if ($location === '' || $category_no === 0 || $description === '' || $reporter === '') {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'缺少必要欄位']);
            exit;
        }

        /* 交易開始 */
        $mysqli->begin_transaction();
    
        $chk = $mysqli->prepare("SELECT category_name 
                                FROM repair_categories 
                                WHERE category_no = ? 
                                LIMIT 1");
        $chk->bind_param("i", $category_no);
        $chk->execute();
        $chk->bind_result($category_name);   // 綁定欄位到變數
        $hasCat = $chk->fetch();             // 取得一筆，回傳 true/false
        $chk->close();

        // 查目前 repair_no 已有幾張圖（理論上新案件應該是 0）
        $chkimg = $mysqli->prepare("SELECT COUNT(*) FROM repair_images WHERE repair_no = ?");
        $chkimg->bind_param('i', $repair_no);
        $chkimg->execute();
        $chkimg->bind_result($imgCount);
        $chkimg->fetch();
        $chkimg->close();

        // 從現有數量+1 開始命名
        $seq = $imgCount + 1;
    
        if (!$hasCat) {
            // ====== 這段改掉 fetch_all()，改用 while + fetch_assoc() ======
            $all = [];
            $result = $mysqli->query("SELECT category_no, category_name FROM repair_categories ORDER BY category_no");
            while ($row = $result->fetch_assoc()) {
                $all[] = $row;
            }
    
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => '無效的分類（category 不存在）',
                'request_category' => $category_no,
                'categories' => $all
            ]);
            exit;
        }
    
        
        $sql = "INSERT INTO repair
                (location, category_no, description, reporter_id, reported_at, status)
                VALUES (?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sisssi", $location, $category_no, $description, $reporter, $reported_at, $status);
        $stmt->execute();
    
        $repair_no   = $mysqli->insert_id;
        $repair_code = 'RR' . str_pad((string)$repair_no, 5, '0', STR_PAD_LEFT);

        $uploaded = [];
        $maxSize  = 5 * 1024 * 1024; // 5MB
        $maxCount = 3;
        $savedCount = 0;

        $hasFiles = isset($_FILES['images']) && is_array($_FILES['images']['name']);

        if ($hasFiles) {
            $stmtInsertImg = $mysqli->prepare("INSERT INTO repair_images (repair_no, image_path) VALUES (?, '')");
            $stmtUpdateImg = $mysqli->prepare("UPDATE repair_images SET image_path = ? WHERE image_no = ?");

            $countAll = count($_FILES['images']['name']);
            for ($i = 0; $i < $countAll && $savedCount < $maxCount; $i++) { 
                $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($err === UPLOAD_ERR_NO_FILE) continue;  // 此槽未上傳
                if ($err !== UPLOAD_ERR_OK)      continue;  // 其他錯誤先跳過

                $tmp   = $_FILES['images']['tmp_name'][$i];
                $size  = intval($_FILES['images']['size'][$i]);
                $name  = $_FILES['images']['name'][$i];

                if ($size <= 0 || $size > $maxSize) continue;

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ext === '') $ext = 'jpg'; // 沒附檔名時給預設

                // 先插入一筆空 path，取 image_no
                $stmtInsertImg->bind_param('i', $repair_no);
                $stmtInsertImg->execute();
                if ($stmtInsertImg->affected_rows <= 0) {
                    throw new Exception('建立 repair_images 記錄失敗');
                }
                $image_no = $mysqli->insert_id;

                // 組目錄與檔名：/uploads/repair/YYYY/MM/repairNo_imageNo.ext
                $subdir   = date('Y/m');
                $baseDir  = __DIR__ . '/../../uploads/repair/' . $subdir;
                if (!is_dir($baseDir)) {
                    if (!mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
                        throw new Exception('建立上傳目錄失敗：' . $baseDir);
                    }
                }

                $safeName   = $repair_no . '_' . $seq . '.' . $ext;
                $destPath   = $baseDir . '/' . $safeName;
                $publicPath = '/uploads/repair/' . $subdir . '/' . $safeName;

                // 搬檔
                if (!move_uploaded_file($tmp, $destPath)) {
                    // 搬檔失敗，丟錯讓交易回滾；也可選擇刪除剛插入的那筆 repair_images
                    throw new Exception('檔案搬移失敗');
                }

                // 更新 image_path
                $stmtUpdateImg->bind_param('si', $publicPath, $image_no);
                $stmtUpdateImg->execute();

                $uploaded[] = [
                    'image_no'   => $image_no,
                    'image_path' => $publicPath,
                ];
                $savedCount++;
                $seq++;
            }
        }

        /* 交易提交 */
        $mysqli->commit();
    
        echo json_encode([
            'status'  => 'success',
            'message' => '建立成功',
            'data' => [
                'repair_no'     => $repair_no,
                'repair_code'   => $repair_code,
                'location'      => $location,
                'category'      => $category_no,
                'category_name' => $category_name,
                'description'   => $description,
                'reporter_id'   => $reporter,
                'reported_at'   => $reported_at,
                'status'        => $status,
                'images'        => $uploaded,
            ]
        ]);
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(['status' => 'error', 'message' => '只允許 POST 請求']);
    }
?>