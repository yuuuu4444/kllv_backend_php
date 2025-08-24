<?php
    require_once __DIR__ . '/../../common/env_init.php';

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) session_start();

    // 1) 先把所有 PHP 警告/notice 轉成 Exception（要在任何可能出錯之前設定）
    set_error_handler(function ($severity, $message, $file, $line) {
        // 避免被 @ 抑制的錯誤
        if (!(error_reporting() & $severity)) return;
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    // 2) 全域例外處理：任何未捕捉例外都用 JSON 回
    set_exception_handler(function ($e) {
        http_response_code(500);
        echo json_encode(['status'=>'error', 'message'=>$e->getMessage()]);
        exit;
    });

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status'=>'error','message'=>'Only POST allowed']);
        exit;
    }

    $ct = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
    $isJson = stripos($ct, 'application/json') === 0;

    if ($isJson) {
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        // 這個分支無法處理檔案（沒有 $_FILES），只更新文字與刪圖(若你傳的是路徑清單)
        $_POST = $input; // 讓下面共用同套欄位名稱
    }

    $post_no = isset($_POST['post_no']) ? intval($_POST['post_no']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category_no = isset($_POST['category_no']) ? intval($_POST['category_no']) : 0;
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    // $author_id = isset($_POST['author_id']) ? trim($_POST['author_id']) : '';
    $login_id = $_SESSION['user_id'] ?? '';

    if (!$post_no || !$title || !$category_no || !$content) {
        echo json_encode(['status'=>'error','message'=>'參數不足']);
        exit;
    }

    
    if ($login_id === '') { // 未登入
        http_response_code(401);
        echo json_encode(['status'=>'error','message'=>'未登入']);
        exit;
    }
    
    $defaultBanner = '/uploads/community/post_default/community_banner.png';
    $PUBLIC_BASE = '/uploads/community';
    $uploadsRoot = realpath(__DIR__ . '/../../uploads/community');

    

    $remove_paths = $_POST['remove_image_paths'] ?? [];
    if (!is_array($remove_paths)) $remove_paths = [$remove_paths];

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $mysqli->begin_transaction();

        $owner = null;
        $posted_at = null;
        $chk = $mysqli->prepare('SELECT author_id, posted_at FROM community_posts WHERE post_no = ? LIMIT 1');
        $chk->bind_param('i', $post_no);
        $chk->execute();
        $chk->bind_result($owner, $posted_at);
        if (!$chk->fetch()) { throw new Exception('找不到貼文'); }
        $chk->close();
        // if ($owner !== $author_id) throw new Exception('無權編輯此貼文');
        /* 改：以 Session 身分比對作者 */
        if ($owner !== $login_id) throw new Exception('無權編輯此貼文');

        $subdir = $posted_at ? date('Y/m', strtotime($posted_at)) : date('Y/m');
        $baseDir = __DIR__ . '/../../uploads/community/' . $subdir;

        if (!is_dir($baseDir) && !@mkdir($baseDir, 0775, true)) {
            throw new Exception('建立上傳目錄失敗');
        }

        $sql = "UPDATE community_posts
                SET title = ?, category_no = ?, content = ?, updated_at = NOW()
                WHERE post_no = ? AND author_id = ?";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("sisis", $title, $category_no, $content, $post_no, $login_id);
        $stmt->execute();
        $stmt->close();

        foreach ($remove_paths as $rel) {
            $rel = trim((string)$rel);
            if ($rel === '') continue;
            $path = parse_url($rel, PHP_URL_PATH) ?: $rel;
            if (strpos($path, $PUBLIC_BASE) !== 0) continue;
    
            $abs = realpath(__DIR__ . '/../../' . ltrim($path, '/'));
            if ($abs && strpos($abs, $uploadsRoot) === 0 && is_file($abs)) {
                @unlink($abs);
            }
    
            $del = $mysqli->prepare("DELETE FROM community_posts_images WHERE post_no = ? AND image_path = ? LIMIT 1");
            $del->bind_param("is", $post_no, $path);
            $del->execute();
            $del->close();
        }

        if (!$isJson && !empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $maxSize = 8 * 1024 * 1024;
            $allow   = ['jpg','jpeg','png','webp'];
    
            $stmtInsertImg = $mysqli->prepare("INSERT INTO community_posts_images(post_no, image_path) VALUES (?, '')");
            $stmtUpdateImg = $mysqli->prepare("UPDATE community_posts_images SET image_path = ? WHERE image_no = ?");
    
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) { 
                $err = $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE;
                if ($err === UPLOAD_ERR_NO_FILE) continue;  // 此槽未上傳
                if ($err !== UPLOAD_ERR_OK)      continue;  // 其他錯誤先跳過
    
                $tmp = $_FILES['images']['tmp_name'][$i];
                $name = $_FILES['images']['name'][$i];
                $size = intval($_FILES['images']['size'][$i]);
                if ($size <= 0 || $size > $maxSize) continue;
    
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if ($ext === '') $ext = 'jpg'; // 沒附檔名時給預設
                if (!in_array($ext, $allow, true)) continue;
    
                $stmtInsertImg->bind_param('i', $post_no);
                $stmtInsertImg->execute();
                if ($stmtInsertImg->affected_rows <= 0) {
                    throw new Exception('建立 community_post_images 記錄失敗');
                }
                $image_no = $mysqli->insert_id;
    
                $safeName   = $post_no . '_' . $image_no . '.' . $ext;
                $destPath   = $baseDir . '/' . $safeName;
                $publicPath = '/uploads/community/' . $subdir . '/' . $safeName;
    
                if (!@move_uploaded_file($tmp, $destPath)) {
                    // 上傳失敗回滾這筆影像記錄
                    $undo = $mysqli->prepare('DELETE FROM community_posts_images WHERE image_no = ? LIMIT 1');
                    $undo->bind_param('i', $image_no);
                    $undo->execute();
                    $undo->close();
                    continue;
                }
    
                $stmtUpdateImg->bind_param('si', $publicPath, $image_no);
                $stmtUpdateImg->execute();
            }
            $stmtInsertImg->close();
            $stmtUpdateImg->close();
        }
    
        $imgs = [];
        $q = $mysqli->prepare("SELECT image_path FROM community_posts_images WHERE post_no = ? ORDER BY image_no");
        $q->bind_param("i", $post_no);
        $q->execute();
        $q->bind_result($image_path);
        while ($q->fetch()) { 
            $imgs[] = $image_path; 
        }
        $q->close();
    
        $banner = $imgs[0] ?? $defaultBanner;
        $updB = $mysqli->prepare("UPDATE community_posts SET image=? WHERE post_no = ?");
        $updB->bind_param("si", $banner, $post_no);
        $updB->execute();
        $updB->close();

        /* 帶回作者名稱，方便前端直接更新畫面 */
        
        $author_name = '';
        $au = $mysqli->prepare("SELECT fullname FROM users WHERE user_id = ? LIMIT 1");
        $au->bind_param('s', $login_id);
        $au->execute();
        $au->bind_result($author_name);
        $au->fetch();
        $au->close();
        
    
        $mysqli->commit();
    
        echo json_encode([
            'status'=>'success',
            'data'=>[
                'post_no'=>$post_no,
                'image'=>$banner,
                'images'=>$imgs,
                // 'author_name' => $author_name,
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
?>