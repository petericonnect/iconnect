<?php
session_start();
require_once __DIR__ . "/../db.php"; // connect to DB

// Fetch all items for Store
$sql = "SELECT s.*, u.username FROM sells s 
        JOIN users u ON s.user_id = u.id 
        ORDER BY s.created_at DESC";
$items = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Store</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f2f2f2;}
.container{padding:15px; max-width:1000px;margin:0 auto;}
.store-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:15px;}
.item-card{background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;}
.item-card:hover{transform:scale(1.02);}
.item-card img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:15px 15px 0 0;display:block;}
.item-card .info{padding:10px;}
.item-card .info h3{margin:0;font-size:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.item-card .info p{margin:5px 0;font-size:14px;color:#555;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.item-card .info .price{font-weight:bold;color:red;font-size:15px;}
</style>
</head>
<body>

<div class="container">
<h2 style="text-align:center;color:red;margin-bottom:20px;">Store</h2>
<div class="store-grid">
<?php if($items->num_rows>0): while($row=$items->fetch_assoc()): ?>
<div class="item-card" onclick='openItemModal(<?= json_encode([
    "title"=>$row['title'],
    "price"=>$row['price'],
    "description"=>$row['description']??"",
    "seller"=>$row['username']??"Unknown",
    "image"=>$row['image']??"pages/sell/uploads/default-item.jpg"
]) ?>)'>
    <img src="<?= htmlspecialchars($row['image'] ?? 'pages/sell/uploads/default-item.jpg') ?>" alt="<?= htmlspecialchars($row['title']) ?>">
    <div class="info">
        <h3><?= htmlspecialchars($row['title']) ?></h3>
        <p class="price">$<?= htmlspecialchars($row['price']) ?></p>
        <p><?= htmlspecialchars($row['description']??'') ?></p>
    </div>
</div>
<?php endwhile; else: ?>
<p style="text-align:center;padding:20px;">No items found in the store.</p>
<?php endif; ?>
</div>
</div>

<!-- ITEM MODAL -->
<div id="itemModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);justify-content:center;align-items:center;z-index:2000;">
<div class="modal-content" style="background:#fff;padding:20px;border-radius:15px;max-width:400px;width:90%;text-align:center;position:relative;">
<span class="close" onclick="closeItemModal()" style="position:absolute;top:10px;right:15px;cursor:pointer;font-size:24px;">×</span>
<h2 id="modalTitle"></h2>
<p id="modalSeller" style="color:#555;"></p>
<p id="modalPrice" style="font-weight:bold;"></p>
<p id="modalDesc" style="color:#333;"></p>
<img id="modalImage" src="" style="width:100%;border-radius:10px;margin-top:10px;">
</div>
</div>

<script>
// Modal functions
const itemModal = document.getElementById('itemModal');
const modalTitle = document.getElementById('modalTitle');
const modalSeller = document.getElementById('modalSeller');
const modalPrice = document.getElementById('modalPrice');
const modalDesc = document.getElementById('modalDesc');
const modalImage = document.getElementById('modalImage');

function openItemModal(item){
    modalTitle.innerText = item.title;
    modalSeller.innerText = "Seller: "+item.seller;
    modalPrice.innerText = "Price: $"+item.price;
    modalDesc.innerText = item.description;
    modalImage.src = item.image;
    itemModal.style.display = "flex";
}

function closeItemModal(){
    itemModal.style.display = "none";
}
</script>

</body>
</html>
