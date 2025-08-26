<?php
    require_once __DIR__ . '/../../common/env_init.php';

    // $sql = "SELECT 
    //             e.event_no, e.title, e.location, e.category_no, e.image, 
    //             e.description, e.fee_per_person, e.p_limit, e.start_date, 
    //             e.end_date, e.reg_deadline, e.created_at, e.status,
    //             ec.category_name
    //         FROM events AS e
    //         LEFT JOIN events_categories AS ec ON e.category_no = ec.category_no
    //         ORDER BY e.start_date DESC";
    $sql = "SELECT 
                e.*,
                ec.category_name
            FROM events AS e
            LEFT JOIN events_categories AS ec ON e.category_no = ec.category_no
            WHERE e.status = 2 -- AND e.is_deleted = 0 -- 假設有is_deleted欄位
            ORDER BY e.start_date DESC";

    $result = $mysqli->query($sql);
    $data = [];
    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
    }

    // $stmt = $mysqli->prepare($sql);
    // $stmt->execute();

    // $stmt->bind_result(
    //     $event_no, $title, $location, $category_no, $image,
    //     $description, $fee_per_person, $p_limit, $start_date,
    //     $end_date, $reg_deadline, $created_at, $status,
    //     $category_name
    // );

    // $data = [];
    // while ($stmt->fetch()) {
    //     $data[] = [
    //         'event_no'        => $event_no,
    //         'title'           => $title,
    //         'location'        => $location,
    //         'category_no'     => $category_no,
    //         'image'           => $image,
    //         'description'     => $description,
    //         'fee_per_person'  => $fee_per_person,
    //         'p_limit'         => $p_limit,
    //         'start_date'      => $start_date,
    //         'end_date'        => $end_date,
    //         'reg_deadline'    => $reg_deadline,
    //         'created_at'      => $created_at,
    //         'status'          => $status,
    //         'category_name'   => $category_name,
    //     ];
    // }

    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
?>