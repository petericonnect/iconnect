<?php
session_start();
require_once __DIR__ . "/../db.php";

if(!isset($_SESSION['user_id'])){
    echo json_encode(['status'=>'error','message'=>'Not logged in']);
    exit;
}

$user_id=$_SESSION['user_id'];
$title=$_POST['title'] ?? '';
$price=$_POST['price'] ?? 0;
$description=$_POST['description'] ?? '';
$image_path="pages/sell/uploads/default-item.jpg";

// Handle image upload
if(isset($_FILES['image']) && $_FILES['image']['error']===0){
    $ext=pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
    $filename=uniqid('item_').'.'.$ext;

    $uploadDir=__DIR__."/sell/uploads/";
    if(!is_dir($uploadDir)) mkdir($uploadDir,0777,true);

    $target=$uploadDir.$filename;
    if(move_uploaded_file($_FILES['image']['tmp_name'],$target)){
        $image_path="pages/sell/uploads/".$filename;
    }
}

$stmt=$conn->prepare("INSERT INTO sells (user_id,title,price,description,image) VALUES (?,?,?,?,?)");
$stmt->bind_param("isdss",$user_id,$title,$price,$description,$image_path);

if($stmt->execute()){
    echo json_encode(['status'=>'success','message'=>'Item posted!']);
}else{
    echo json_encode(['status'=>'error','message'=>$conn->error]);
}
