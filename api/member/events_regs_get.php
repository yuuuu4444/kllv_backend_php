<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../common/env_init.php';

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    throw new Exception("僅支援 GET 方法", 405);
}

// 假設的登入者ID (可以是主帳號或子帳號)
$loggedInUserId = 'user_account_001';

$response = [];
$stmt_regs = null;

try {
    $sql_regs = "SELECT 
            r.reg_no,
            r.event_no,
            e.title,
            e.start_date,
            e.end_date,
            e.location,
            r.p_total,
            r.fee_total,
            r.payment_no,
            r.status,
            r.cancel_reason_no,
            r.registered_at
        FROM  events_regs r
        JOIN  events e ON r.event_no = e.event_no
        JOIN  events_regs_plist pl ON r.reg_no = pl.reg_no
        WHERE  pl.participant_id = ? 
        GROUP BY r.reg_no
        ORDER BY r.registered_at DESC";
    

    $stmt_regs = $mysqli->prepare($sql_regs);
    if ($stmt_regs === false) {
        throw new Exception("資料庫準備失敗 (主查詢): " . $mysqli->error, 500);
    }
    
    $stmt_regs->bind_param('s', $loggedInUserId);
    $stmt_regs->execute();
    
    $stmt_regs->store_result();
    $stmt_regs->bind_result(
        $reg_no, 
        $event_no, 
        $title, 
        $start_date, 
        $end_date, 
        $location, 
        $p_total, 
        $fee_total, 
        $payment_no, 
        $status, 
        $cancel_reason_no, 
        $registered_at
    );
    
    $userEvents = [];
    while ($stmt_regs->fetch()) {
        $eventData = [
            'reg_no'            => $reg_no,
            'event_no'          => $event_no,
            'title'             => $title,
            'activity_date'     => date('Y-m-d', strtotime($start_date)), // 衍生出純日期
            'activity_time'     => date('H:i', strtotime($start_date)) . '~' . date('H:i', strtotime($end_date)), // 衍生出時間區間
            'location'          => $location,
            'p_total'           => $p_total,
            'fee_total'         => $fee_total,
            'payment_no'        => $payment_no,
            'status'            => $status,
            'cancel_reason_no'  => $cancel_reason_no,
            'registered_at'     => $registered_at,
            'participants'      => [] // 準備存放參加人列表
        ];

        // 針對每一筆報名，再去查詢對應的參加人列表
        $sql_participants = "SELECT
                pl.plist_no,
                pl.participant_id,
                u.fullname,
                u.email,
                u.phone_number
            FROM events_regs_plist pl
            JOIN users u ON pl.participant_id = u.user_id
            WHERE pl.reg_no = ?
        ";
        $stmt_participants = $mysqli->prepare($sql_participants);
        $stmt_participants->bind_param('i', $reg_no);
        $stmt_participants->execute();
        $stmt_participants->store_result();
        $stmt_participants->bind_result($plist_no, $participant_id, $fullname, $email, $phone_number);
        
        // 遍歷該次報名的所有參加人
        while ($stmt_participants->fetch()) {
            $eventData['participants'][] = [
                'plist_no'        => $plist_no,
                'reg_no'          => $reg_no,
                'participant_id'  => $participant_id,
                'fullname'        => $fullname,
                'email'           => $email,
                'phone_number'    => $phone_number
            ];
        }
        $stmt_participants->close();

        $userEvents[] = $eventData;
    }
    
    $response = ['status' => 'success', 'data' => $userEvents];

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response = ["status" => "error", "message" => $e->getMessage()];
} finally {
    if ($stmt_regs !== null) {
        $stmt_regs->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
echo json_encode($response);
?>