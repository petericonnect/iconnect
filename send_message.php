<?php
require_once '../db.php';
session_start();
$data = json_decode(file_get_contents('php://input'), true);

$stream_id = $data['stream_id'] ?? 0;
$message = trim($data['message'] ?? '');
$user_id = $_SESSION['user_id'] ?? 0;

if(!$stream_id || !$user_id || !$message) exit(json_encode(['status'=>'error']));

$stmt = $conn->prepare("INSERT INTO live_chat (stream_id,user_id,message) VALUES (?,?,?)");
$stmt->bind_param("iis",$stream_id,$user_id,$message);
$stmt->execute();

echo json_encode(['status'=>'success']);
?>
