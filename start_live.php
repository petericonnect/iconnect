<?php
require_once '../db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? 0;

if(!$user_id) exit(json_encode(['status'=>'error','message'=>'Not logged in']));

// Insert live stream
$stmt = $conn->prepare("INSERT INTO live_streams (user_id) VALUES (?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stream_id = $conn->insert_id;

echo json_encode(['status'=>'success','stream_id'=>$stream_id]);
?>
