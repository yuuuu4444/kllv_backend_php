<?php
    require_once __DIR__ . '/../../common/env_init.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status'=>'error','message'=>'Only POST allowed']);
        exit;
    }

    /* 把請求內容打到 PHP error log，方便確認實際收到什麼 */
    $raw = file_get_contents('php://input');
    error_log('[reply_save] RAW BODY: ' . $raw);
    error_log('[reply_save] POST: ' . print_r($_POST, true));

    $input = json_decode($raw, true);
    if (!is_array($input) || empty($input)) {
        // 如果不是 JSON，就退回吃表單（multipart/x-www-form-urlencoded 或 FormData）
        $input = $_POST;
    }

    $title = isset($input['title']) ? trim($input['title']) : '';
    $category_no = isset($input['category_no']) ? intval($input['category_no']) : 0;
    $content = isset($input['content']) ? trim($input['content']) : '';
    $author_id = isset($input['author_id']) ? trim($input['author_id']) : '';
    $defaultBanner = '/uploads/community/post_default/community_banner.png';
    $image = isset($input['image']) ? trim($input['image']) : $defaultBanner;

    if ($title === '' || $category_no === 0 || $content === '' || $author_id === '') {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'缺少必要欄位']);
        exit;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $mysqli->begin_transaction();
    
        $chkId = $mysqli->prepare('SELECT 1 FROM users WHERE user_id = ? AND is_deleted = 0 LIMIT 1');
        $chkId->bind_param("s", $author_id);
        $chkId->execute();
        $chkId->store_result();
        if ($chkId->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'author_id 不存在']);
            exit;
        }
        $chkId->close();
    
        $chkCat = $mysqli->prepare('SELECT 1 FROM community_posts_categories WHERE category_no = ? LIMIT 1');
        $chkCat->bind_param("i", $category_no);
        $chkCat->execute();
        $chkCat->store_result();
        if ($chkCat->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['status'=>'error','message'=>'category_no 不存在']);
            exit;
        }
        $chkCat->close();
    
        $sql = "INSERT INTO community_posts(title, category_no, content, image, author_id, is_deleted, posted_at)
                VALUES (?, ?, ?, ?, ?, 0, NOW())";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sisss", $title, $category_no, $content, $image, $author_id);
        $stmt->execute();
        $post_no = $stmt->insert_id;
        $stmt->close();
        
        $uploaded = [];
        $maxSize = 8 * 1024 * 1024;
        $maxCount = 10;
        $savedCount = 0;
    
        $hasFiles = isset($_FILES['images']) && is_array($_FILES['images']['name']);
    
        if ($hasFiles) {
            $stmtInsertImg = $mysqli->prepare("INSERT INTO community_posts_images(post_no, image_path) VALUES (?, '')");
            $stmtUpdateImg = $mysqli->prepare("UPDATE community_posts_images SET image_path = ? WHERE image_no = ?");
    
            $countAll = count($_FILES['images']['name']);
            for ($i = 0; $i < $countAll && $savedCount < $maxCount; $i++) { 
                $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($err === UPLOAD_ERR_NO_FILE) continue;  // 此槽未上傳
                if ($err !== UPLOAD_ERR_OK)      continue;  // 其他錯誤先跳過
    
                $tmp = $_FILES['images']['tmp_name'][$i];
                $size = intval($_FILES['images']['size'][$i]);
                $name = $_FILES['images']['name'][$i];
    
                if ($size <= 0 || $size > $maxSize) continue;
    
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ext === '') $ext = 'jpg'; // 沒附檔名時給預設
    
                $stmtInsertImg->bind_param('i', $post_no);
                $stmtInsertImg->execute();
                if ($stmtInsertImg->affected_rows <= 0) {
                    throw new Exception('建立 community_post_images 記錄失敗');
                }
                $image_no = $mysqli->insert_id;
    
                $subdir   = date('Y/m');
                $baseDir  = __DIR__ . '/../../uploads/community/' . $subdir;
                if (!is_dir($baseDir)) {
                    if (!mkdir($baseDir, 0775, true) && !is_dir($baseDir)) {
                        throw new Exception('建立上傳目錄失敗：' . $baseDir);
                    }
                }
    
                $safeName   = $post_no . '_' . $image_no . '.' . $ext;
                $destPath   = $baseDir . '/' . $safeName;
                $publicPath = '/uploads/community/' . $subdir . '/' . $safeName;
    
                if (!move_uploaded_file($tmp, $destPath)) {
                    throw new Exception('檔案搬移失敗');
                }
    
                $stmtUpdateImg->bind_param('si', $publicPath, $image_no);
                $stmtUpdateImg->execute();
    
                $uploaded[] = [
                    'image_no'   => $image_no,
                    'image_path' => $publicPath,
                ];
                $savedCount++;
            }
        }
    
        // 有上傳就把第一張當image，回寫主表；沒上傳維持default
        if (!empty($uploaded)) {
            $firstUrl = $uploaded[0]['image_path'];
            $stmtUpdBanner = $mysqli->prepare("UPDATE community_posts SET image = ? WHERE post_no = ?");
            $stmtUpdBanner->bind_param('si', $firstUrl, $post_no);
            $stmtUpdBanner->execute();
        }
    
        $mysqli->commit();
    
        $data = $mysqli->query("SELECT * FROM community_posts WHERE post_no = {$post_no}")->fetch_assoc();
        $data['images'] = array_column($uploaded, 'image_path');
        echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);

    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>