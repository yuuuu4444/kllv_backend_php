<?php
require_once __DIR__ . '/../../common/env_init.php';

// --- Session登入檢查 ---
// 使用前台Session不需要session_name()
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => '請先登入才能報名']);
    exit;
}
// --- 登入檢查結束 ---

if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => '只允許 POST 請求']);
    exit;
}

// Session中獲取已登入使用者ID
$loggedInUserId = $_SESSION['user_id'];
$input = json_decode(file_get_contents("php://input"), true);

// 驗證必要的欄位是否存在
$event_no = filter_var($input['event_no'] ?? null, FILTER_VALIDATE_INT);
$participants = $input['participants'] ?? []; // 參與者列表
$payment_no = filter_var($input['payment_no'] ?? null, FILTER_VALIDATE_INT);
$fee_total = filter_var($input['fee_total'] ?? null, FILTER_VALIDATE_INT);
$p_total = count($participants);

// if (!$event_no || empty($participants) || !$payment_no || $fee_total === null) {
//     http_response_code(400);
//     echo json_encode(['status' => 'error', 'message' => '缺少必要的報名資訊']);
//     exit;
// }

if (!$event_no || empty($participants)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '缺少活動編號或參與者資訊']);
    exit;
}
if ($fee_total > 0 && !$payment_no) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '付費活動缺少付款方式']);
    exit;
}

$initial_status = 0; // 預設狀態為0(未完成)
// payment_no:1=信用卡,2=銀行轉帳,3=現金繳費
if ($payment_no === 1 || $fee_total === 0) {
    $initial_status = 1;
}


// 使用資料庫交易
$mysqli->begin_transaction();
try {
    // 1.新增訂單到 events_regs (訂單主表)
    $sql_reg = "INSERT INTO events_regs 
                (event_no, participant_id, p_total, fee_total, payment_no, registered_at, status) 
                VALUES (?, ?, ?, ?, ?, NOW(), ?)"; 
    
    $stmt_reg = $mysqli->prepare($sql_reg);
    // bind_param類型多了一個i，代表最後的status
    $payment_no_for_db = $payment_no ? (int)$payment_no : null;
    $fee_total_for_db = $fee_total !== null ? (int)$fee_total : 0;

    $stmt_reg->bind_param("isiiii", $event_no, $loggedInUserId, $p_total, $fee_total_for_db, $payment_no_for_db, $initial_status);
    $stmt_reg->execute();
    // 獲取剛剛新增的訂單編號(reg_no)
    $reg_no = $mysqli->insert_id;

    // 2.將所有參與者逐一新增到events_regs_plist(參與者明細表)
    $sql_plist = "INSERT INTO events_regs_plist
                (reg_no, participant_id, phone_number, id_number, birth_date, econtact_name, econtact_phone, econtact_relation)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_plist = $mysqli->prepare($sql_plist);

    foreach ($participants as $person) {
        // 從前端傳來的陣列中取出每個參與者資料
        $participant_id     = $person['user_id'];
        $phone_number       = $person['phone'];
        $id_number          = $person['id_number'];
        $birth_date         = $person['birth_date'];
        $econtact_name      = $person['emergency_name'];
        $econtact_phone     = $person['emergency_phone'];
        $econtact_relation  = $person['emergency_relation'];

        $stmt_plist->bind_param("isssssss", 
            $reg_no, // 每筆明細都關聯到同一個訂單編號
            $participant_id, 
            $phone_number,
            $id_number,
            $birth_date,
            $econtact_name,
            $econtact_phone,
            $econtact_relation
        );
        $stmt_plist->execute();
    }

    // 3.如果前面所有步驟都沒出錯，正式提交交易
    $mysqli->commit();
    echo json_encode([
        'status' => 'success',
        'message' => '活動報名成功！',
        'data' => [
            'reg_no' => $reg_no
        ]
    ]);

} catch (mysqli_sql_exception $exception) {
    // 4.如果try區塊中有任何一個 SQL 指令失敗
    $mysqli->rollback(); // 立刻復原，把這筆交易所有已寫入資料取消
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => '報名失敗，資料庫操作錯誤',
        'error_detail' => $exception->getMessage() // 顯示詳細錯誤方便除錯
    ]);
}
?>