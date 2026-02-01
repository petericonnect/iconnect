<?php
session_start();
require_once __DIR__ . "/../db.php";

header('Content-Type: application/json');

// Check if file is sent
if(!isset($_FILES['video'])){
    echo json_encode(["status"=>"error","message"=>"No file sent"]);
    exit;
}

$video = $_FILES['video'];

// Validate video
$allowed = ['mp4','webm','ogg','mov'];
$ext = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed)){
    echo json_encode(["status"=>"error","message"=>"Invalid file type"]);
    exit;
}

// Create uploads folder if not exists
$uploadDir = __DIR__ . "/uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$filename = uniqid() . "." . $ext;
$target = $uploadDir . $filename;

// Move uploaded file
if(move_uploaded_file($video['tmp_name'], $target)){
    // Save video info in DB
    $stmt = $conn->prepare("INSERT INTO videos (user_id, title, video_path, category, created_at) VALUES (?, ?, ?, ?, NOW())");
    $title = pathinfo($video['name'], PATHINFO_FILENAME);
    $category = "All"; // default category
    $video_path = "pages/uploads/".$filename; // path relative to index.php
    $stmt->bind_param("isss", $_SESSION['user_id'], $title, $video_path, $category);
    if($stmt->execute()){
        echo json_encode([
            "status"=>"success",
            "message"=>"Video uploaded successfully",
            "video" => [
                "title"=>$title,
                "video_path"=>$video_path,
                "category"=>$category
            ]
        ]);
    } else {
        echo json_encode(["status"=>"error","message"=>"DB insert failed"]);
    }
} else {
    echo json_encode(["status"=>"error","message"=>"Failed to move uploaded file"]);
}


