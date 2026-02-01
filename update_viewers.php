<?php
require_once '../db.php';
session_start();
$data = json_decode(file_get_contents('php://input'), true);
$stream_id = $data['stream_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;
$session_id = session_id();

if(!$stream_id || !$user_id) exit(json_encode(['status'=>'error']));

// Add viewer if not exists
$stmt = $conn->prepare("SELECT id FROM live_viewers WHERE stream_id=? AND session_id=?");
$stmt->bind_param("is",$stream_id,$session_id);
$stmt->execute();
$stmt->store_result();

if($stmt->num_rows===0){
    $stmt2 = $conn->prepare("INSERT INTO live_viewers (stream_id,user_id,session_id) VALUES (?,?,?)");
    $stmt2->bind_param("iis",$stream_id,$user_id,$session_id);
    $stmt2->execute();
}

// Get unique viewers
$result = $conn->query("SELECT COUNT(DISTINCT session_id) as viewers FROM live_viewers WHERE stream_id=$stream_id");
$viewers = $result->fetch_assoc()['viewers'];

// Get last 5 messages
$msgRes = $conn->query("SELECT lc.message, u.username FROM live_chat lc JOIN users u ON lc.user_id=u.id WHERE lc.stream_id=$stream_id ORDER BY lc.created_at ASC LIMIT 5");
$messages = [];
while($row = $msgRes->fetch_assoc()) $messages[]=$row;

echo json_encode(['status'=>'success','viewers'=>$viewers,'messages'=>$messages]);
?>
