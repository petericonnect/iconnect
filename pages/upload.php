<?php
// Buffer output to prevent accidental whitespace breaking JSON
ob_start();
session_start();
require_once __DIR__ . "/../db.php";

// Set response type
header('Content-Type: application/json');

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo json_encode(["status"=>"error","message"=>"You must be logged in"]);
    exit;
}

// Check if file is sent
if(!isset($_FILES['video'])){
    echo json_encode(["status"=>"error","message"=>"No file sent"]);
    exit;
}

// Get uploaded file
$video = $_FILES['video'];

// Validate video extension
$allowed = ['mp4','webm','ogg','mov'];
$ext = strtolower(pathinfo($video['name'], PATHINFO_EXTENSION));
if(!in_array($ext, $allowed)){
    echo json_encode(["status"=>"error","message"=>"Invalid file type"]);
    exit;
}

// Validate category
$allowedCategories = ["Music","Tech","Gaming","Education","Trending","Latest","Comedy","Sports","News","Art","Live Stream"];
$category = trim($_POST['category'] ?? 'All');
if(!in_array($category, $allowedCategories)){
    $category = "All"; // fallback
}

// Create uploads folder if not exists
$uploadDir = __DIR__ . "/uploads/";
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Generate unique filename
$filename = uniqid() . "." . $ext;
$target = $uploadDir . $filename;

// Move uploaded file
if(move_uploaded_file($video['tmp_name'], $target)){

    // Path relative to index.php
    $video_path = "pages/uploads/".$filename;

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO videos (user_id, title, video_path, category, created_at) VALUES (?, ?, ?, ?, NOW())");
    $title = pathinfo($video['name'], PATHINFO_FILENAME);
    $stmt->bind_param("isss", $_SESSION['user_id'], $title, $video_path, $category);

    if($stmt->execute()){
        echo json_encode([
            "status" => "success",
            "message" => "Video uploaded successfully",
            "video" => [
                "title" => $title,
                "video_path" => $video_path,
                "category" => $category
            ]
        ]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Database insert failed"]);
    }

} else {
    echo json_encode(["status"=>"error","message"=>"Failed to move uploaded file"]);
}

// End output buffering
ob_end_flush();
