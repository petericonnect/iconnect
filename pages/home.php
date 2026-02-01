<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../db.php";

$category = $_GET['category'] ?? 'All';
$search = $_GET['search'] ?? '';

$topCategories = ["All","Music","Tech","Gaming","Education","Trending","Latest","Comedy","Sports","News","Art"];

if($category==="Trending"){
    $sql="SELECT * FROM videos ORDER BY views DESC";
    if($search) $sql="SELECT * FROM videos WHERE title LIKE '%$search%' ORDER BY views DESC";
    $videos=$conn->query($sql);
}elseif($category==="Latest"){
    $sql="SELECT * FROM videos ORDER BY created_at DESC";
    if($search) $sql="SELECT * FROM videos WHERE title LIKE '%$search%' ORDER BY created_at DESC";
    $videos=$conn->query($sql);
}elseif($category==="All"){
    $sql="SELECT * FROM videos";
    if($search) $sql="SELECT * FROM videos WHERE title LIKE '%$search%'";
    $videos=$conn->query($sql);
}elseif($category==="Go Live"){
    $sql="SELECT u.id as user_id, u.username FROM users u JOIN live_streams ls ON ls.user_id=u.id";
    $videos=$conn->query($sql);
}elseif($category==="Sell" || $category==="Store"){
    $sql="SELECT s.*, u.username FROM sells s JOIN users u ON s.user_id=u.id ORDER BY s.created_at DESC";
    $videos=$conn->query($sql);
}else{
    $stmt=$conn->prepare("SELECT * FROM videos WHERE category=?");
    $stmt->bind_param("s",$category);
    $stmt->execute();
    $videos=$stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iConnect Home</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f2f2f2;}
.search-bar{display:flex;justify-content:center;padding:10px;background:#fff;position:sticky;top:0;z-index:500;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
.search-bar input{width:70%;padding:8px;border-radius:20px;border:1px solid #ccc;outline:none;}
.search-bar button{padding:8px 15px;margin-left:5px;border:none;background:red;color:#fff;border-radius:20px;cursor:pointer;}
.categories{display:flex;overflow-x:auto;background:#fff;padding:8px;border-bottom:1px solid #ccc;}
.categories span{padding:6px 12px;margin-right:5px;cursor:pointer;border-radius:20px;white-space:nowrap;transition:0.2s;}
.categories span.active,.categories span:hover{background:red;color:#fff;}
.video-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:15px;padding:15px;}
.video-card{background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 2px 5px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;}
.video-card:hover{transform:scale(1.02);}
.video-card video,.video-card img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:15px;display:block;}
.video-card .title{padding:8px;font-weight:bold;font-size:14px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
#itemModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);justify-content:center;align-items:center;z-index:2000;}
#itemModal .modal-content{background:#fff;padding:20px;border-radius:15px;max-width:400px;width:90%;text-align:center;position:relative;}
#itemModal .close{position:absolute;top:10px;right:15px;cursor:pointer;font-size:24px;}
#bottomNavWrapper{position:fixed;bottom:0;width:100%;height:60px;background:#fff;box-shadow:0 -2px 5px rgba(0,0,0,0.1);z-index:900;}
#bottomNav{display:flex;justify-content:space-around;align-items:center;height:100%;position: relative;}
#bottomNav span{cursor:pointer;text-align:center;font-size:14px;color:#333;transition:0.2s;}
#bottomNav span:hover,#bottomNav span.active{color:red;font-weight:bold;}
#floatingUpload{position:absolute;left:50%;transform:translateX(-50%) translateY(-20%);bottom:30px;width:60px;height:60px;background:red;color:white;font-size:36px;font-weight:bold;border-radius:50%;display:flex;justify-content:center;align-items:center;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.3);z-index:1000;transition:transform 0.2s ease;}
#floatingUpload:hover{transform:translateX(-50%) translateY(-20%) scale(1.1);}
</style>
</head>
<body>

<!-- SEARCH -->
<div class="search-bar">
<input type="text" id="searchInput" placeholder="Search videos or items..." value="<?= htmlspecialchars($search) ?>">
<button onclick="searchVideos()">Search</button>
</div>

<!-- TOP CATEGORIES -->
<div class="categories">
<?php foreach($topCategories as $cat):
$active=($cat===$category)?'active':''; ?>
<span class="<?= $active ?>" onclick="window.location='index.php?page=home&category=<?= $cat ?>'"><?= $cat ?></span>
<?php endforeach; ?>
</div>

<!-- VIDEO / STORE GRID -->
<div class="video-grid">
<?php if($videos->num_rows>0): while($row=$videos->fetch_assoc()): ?>
<div class="video-card" 
<?php if($category==="Go Live"): ?>
onclick="openLiveStream(<?= $row['user_id'] ?>,'<?= addslashes($row['username']) ?>')"
<?php elseif($category==="Sell" || $category==="Store"): ?>
onclick='openItemModal(<?= json_encode([
"title"=>$row['title'],
"price"=>$row['price'],
"description"=>$row['description']??"",
"seller"=>$row['username']??"Unknown",
"image"=>$row['image']??"pages/sell/uploads/default-item.jpg"
]) ?>)'
<?php endif; ?>>
<?php if($category==="Go Live"): ?>
<video src="" autoplay muted loop></video>
<div class="title"><?= $row['username'] ?> 🔴 LIVE</div>
<?php elseif($category==="Sell" || $category==="Store"): ?>
<img src="<?= htmlspecialchars($row['image'] ?? 'pages/sell/uploads/default-item.jpg') ?>" alt="<?= htmlspecialchars($row['title']) ?>">
<div class="title"><?= htmlspecialchars($row['title']) ?></div>
<div style="padding:0 8px 4px 8px;font-weight:bold;">$<?= htmlspecialchars($row['price']) ?></div>
<div style="padding:0 8px 8px 8px;color:#555;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($row['description']) ?></div>
<?php else: ?>
<video src="<?= $row['video_path'] ?>" controls></video>
<div class="title"><?= htmlspecialchars($row['title']) ?></div>
<?php endif; ?>
</div>
<?php endwhile; else: ?>
<p style="padding:20px;text-align:center;">No content found in this category.</p>
<?php endif; ?>
</div>

<!-- ITEM MODAL -->
<div id="itemModal">
<div class="modal-content">
<span class="close" onclick="closeItemModal()">×</span>
<h2 id="modalTitle"></h2>
<p id="modalSeller" style="color:#555;"></p>
<p id="modalPrice" style="font-weight:bold;"></p>
<p id="modalDesc" style="color:#333;"></p>
<img id="modalImage" src="" style="width:100%;border-radius:10px;margin-top:10px;">
</div>
</div>

<!-- FLOATING UPLOAD BUTTON -->
<div id="floatingUpload" onclick="openUpload()">+</div>

<!-- BOTTOM NAV -->
<div id="bottomNavWrapper">
<div id="bottomNav">
<span onclick="window.location='index.php?page=home&category=All'" class="<?= ($category=='All')?'active':'' ?>">Home</span>
<span onclick="window.location='index.php?page=home&category=Go Live'" class="<?= ($category=='Go Live')?'active':'' ?>">Go Live</span>
<div id="floatingUpload"></div>
<span onclick="window.location='sell.php'" class="<?= ($category=='Sell')?'active':'' ?>">Sell</span>
<span onclick="window.location='index.php?page=home&category=Store'" class="<?= ($category=='Store')?'active':'' ?>">Store</span>
</div>
</div>

<script>
function openItemModal(item){
    document.getElementById("modalTitle").innerText=item.title;
    document.getElementById("modalPrice").innerText="Price: $"+item.price;
    document.getElementById("modalDesc").innerText=item.description;
    document.getElementById("modalSeller").innerText="Seller: "+item.seller;
    document.getElementById("modalImage").src=item.image;
    document.getElementById("itemModal").style.display="flex";
}
function closeItemModal(){document.getElementById("itemModal").style.display="none";}
function searchVideos(){
    let term=document.getElementById("searchInput").value.trim();
    if(!term) return;
    window.location='index.php?page=home&category=<?= $category ?>&search='+encodeURIComponent(term);
}
function openUpload(){
    let input=document.createElement("input");
    input.type="file"; input.accept="video/*";
    input.onchange=e=>{
        let file=e.target.files[0];
        if(!file) return;
        let formData=new FormData();
        formData.append('video',file);
        fetch('pages/upload.php',{method:'POST',body:formData})
        .then(res=>res.json())
        .then(data=>{
            if(data.status==='success'){
                alert('Video uploaded!');
                location.reload();
            } else alert('Upload failed: '+data.message);
        }).catch(err=>alert('Upload failed: '+err.message));
    };
    input.click();
}
</script>
</body>
</html>
