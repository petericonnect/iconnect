
<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../db.php";

// Handle form submission
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 1; // fallback user_id for testing

    // Handle image upload
    $imagePath = 'pages/sell/uploads/default-item.jpg';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid().'_'.time().'.'.$ext;
        $targetDir = __DIR__."/sells/uploads/";
        if(!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $targetFile = $targetDir.$filename;
        if(move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)){
            $imagePath = "pages/sell/uploads/".$filename;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO sells (user_id, title, description, price, image) VALUES (?,?,?,?,?)");
    $stmt->bind_param("issds", $user_id, $title, $description, $price, $imagePath);
    if($stmt->execute()){
        $message = "Item posted successfully!";
    } else {
        $message = "Failed to post item: ".$stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post an Item - Sell</title>
<style>
body{
    margin:0;
    font-family:Arial,sans-serif;
    background:linear-gradient(135deg,#f0f4ff,#d9e6ff);
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.sell-form-container{
    background:#fff;
    padding:30px 25px;
    border-radius:20px;
    box-shadow:0 0 20px rgba(0,0,0,0.2);
    max-width:400px;
    width:90%;
    position:relative;
    animation:fadeIn 0.5s ease;
}
.sell-form-container h2{
    text-align:center;
    margin-bottom:20px;
    color:#333;
}
.sell-form-container input,
.sell-form-container textarea{
    width:100%;
    padding:10px 12px;
    margin-bottom:15px;
    border-radius:10px;
    border:1px solid #ccc;
    outline:none;
    font-size:14px;
}
.sell-form-container input:focus,
.sell-form-container textarea:focus{
    border-color:#ff4d4d;
    box-shadow:0 0 8px rgba(255,77,77,0.3);
}
.sell-form-container button{
    width:100%;
    padding:12px;
    background:linear-gradient(90deg,#ff4d4d,#ff9999);
    color:#fff;
    font-size:16px;
    font-weight:bold;
    border:none;
    border-radius:10px;
    cursor:pointer;
    transition:0.3s;
}
.sell-form-container button:hover{
    background:linear-gradient(90deg,#ff6666,#ffb3b3);
}
.message{
    text-align:center;
    margin-bottom:15px;
    color:green;
    font-weight:bold;
}
@keyframes fadeIn{
    0%{opacity:0;transform:translateY(-20px);}
    100%{opacity:1;transform:translateY(0);}
}
</style>
</head>
<body>

<div class="sell-form-container">
    <h2>Post an Item</h2>
    <?php if($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Item Title" required>
        <textarea name="description" rows="4" placeholder="Item Description" required></textarea>
        <input type="number" name="price" placeholder="Price (USD)" step="0.01" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Post Item</button>
    </form>
</div>

</body>
</html>
