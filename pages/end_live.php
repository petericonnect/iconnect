<?php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$stream_id = $data['stream_id'] ?? 0;

if($stream_id){
    $stmt = $conn->prepare("DELETE FROM live_streams WHERE id=?");
    $stmt->bind_param("i", $stream_id);
    if($stmt->execute()){
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Could not end live.']);
    }
}else{
    echo json_encode(['status'=>'error','message'=>'Invalid stream ID.']);
}
