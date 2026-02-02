<?php
if(session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../db.php";

$category = $_GET['category'] ?? 'All';
$search   = $_GET['search'] ?? '';

$topCategories = ["All","Music","Tech","Gaming","Education","Trending","Latest","Comedy","Sports","News","Art"];
$uploadCategories = ["Music","Tech","Gaming","Education","Trending","Latest","Comedy","Sports","News","Art","Live Stream"];

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
    $sql="SELECT u.id as user_id, u.username, ls.video_path 
          FROM users u JOIN live_streams ls ON ls.user_id=u.id";
    $videos=$conn->query($sql);
}elseif($category==="Sell" || $category==="Store"){
    $sql="SELECT s.*, u.username FROM sells s 
          JOIN users u ON s.user_id=u.id ORDER BY s.created_at DESC";
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
.video-card{background:#fff;border-radius:15px;overflow:hidden;box-shadow:0 2px 5px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s ease;}
.video-card:hover{transform:scale(1.03);}
.video-card video,.video-card img{width:100%;height:100%;aspect-ratio:1/1;object-fit:cover;display:block;cursor:pointer;}
.video-card .title{padding:8px;font-weight:bold;font-size:14px;text-align:center;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
#itemModal,#uploadModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);justify-content:center;align-items:center;z-index:2000;}
#itemModal .modal-content,#uploadModal .modal-content{background:#fff;padding:20px;border-radius:15px;max-width:400px;width:90%;text-align:center;position:relative;}
#itemModal .close,#uploadModal .close{position:absolute;top:10px;right:15px;cursor:pointer;font-size:24px;}
#bottomNavWrapper{position:fixed;bottom:0;width:100%;height:60px;background:#fff;box-shadow:0 -2px 5px rgba(0,0,0,0.1);z-index:900;}
#bottomNav{display:flex;justify-content:space-around;align-items:center;height:100%;}
#bottomNav span{cursor:pointer;text-align:center;font-size:14px;color:#333;transition:0.2s;}
#bottomNav span:hover,#bottomNav span.active{color:red;font-weight:bold;}
#floatingUpload{position:fixed;left:50%;transform:translateX(-50%);bottom:30px;width:60px;height:60px;background:red;color:white;font-size:36px;font-weight:bold;border-radius:50%;display:flex;justify-content:center;align-items:center;cursor:pointer;box-shadow:0 4px 10px rgba(0,0,0,0.3);z-index:1000;}
</style>
</head>
<body>

<!-- SEARCH -->
<div class="search-bar">
<input type="text" id="searchInput" placeholder="Search videos or items..." value="<?= htmlspecialchars($search) ?>">
<button onclick="searchVideos()">Search</button>
</div>

<!-- CATEGORIES -->
<div class="categories">
<?php foreach($topCategories as $cat): ?>
<span class="<?= ($cat===$category)?'active':'' ?>" onclick="window.location='index.php?page=home&category=<?= $cat ?>'"><?= $cat ?></span>
<?php endforeach; ?>
</div>

<!-- VIDEO GRID -->
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
<video src="<?= $row['video_path'] ?>" autoplay muted loop playsinline></video>
<div class="title"><?= $row['username'] ?> 🔴 LIVE</div>
<?php elseif($category==="Sell" || $category==="Store"): ?>
<img src="<?= htmlspecialchars($row['image'] ?? 'pages/sell/uploads/default-item.jpg') ?>">
<div class="title"><?= htmlspecialchars($row['title']) ?></div>
<?php else: ?>
<video src="<?= $row['video_path'] ?>" muted playsinline></video>
<div class="title"><?= htmlspecialchars($row['title']) ?></div>
<?php endif; ?>
</div>
<?php endwhile; else: ?>
<p style="padding:20px;text-align:center;">No content found.</p>
<?php endif; ?>
</div>

<!-- ITEM MODAL -->
<div id="itemModal">
<div class="modal-content">
<span class="close" onclick="closeItemModal()">×</span>
<h2 id="modalTitle"></h2>
<p id="modalSeller"></p>
<p id="modalPrice"></p>
<p id="modalDesc"></p>
<img id="modalImage" style="width:100%;border-radius:10px;">
</div>
</div>

<!-- UPLOAD MODAL -->
<div id="uploadModal">
<div class="modal-content">
<span class="close" onclick="closeUploadModal()">×</span>
<h2>Upload Video</h2>
<form id="uploadForm">
<label for="videoFile">Choose Video:</label><br>
<input type="file" id="videoFile" name="video" accept="video/*" required><br><br>
<label for="videoCategory">Select Category:</label><br>
<select id="videoCategory" name="category" required>
<option value="">--Choose Category--</option>
<?php foreach($uploadCategories as $cat){
    echo "<option value='$cat'>$cat</option>";
} ?>
</select><br><br>
<button type="submit" style="background:red;color:white;padding:8px 15px;border:none;border-radius:10px;cursor:pointer;">Upload</button>
</form>
<div id="uploadStatus" style="margin-top:10px;color:red;text-align:center;"></div>
</div>
</div>

<!-- FLOATING UPLOAD BUTTON -->
<div id="floatingUpload" onclick="openUpload()">+</div>

<!-- BOTTOM NAV -->
<div id="bottomNavWrapper">
<div id="bottomNav">
   <span onclick="window.location='pages/index.php?page=home&category=All'" class="<?= ($category=='All')?'active':'' ?>">Home</span>
<span onclick="window.location='pages/index.php?page=home&category=Go Live'" class="<?= ($category=='Go Live')?'active':'' ?>">Go Live</span>
<div style="width:60px;"></div>
<span onclick="window.location='pages/sell.php'" class="<?= ($category=='Sell')?'active':'' ?>">Sell</span>
<span onclick="window.location='pages/index.php?page=home&category=Store'" class="<?= ($category=='Store')?'active':'' ?>">Store</span>


</div>
</div>

<script>
/* VIDEO CLICK TO PLAY + SOUND TOGGLE */
document.querySelectorAll('.video-card video').forEach(video=>{
    video.addEventListener('click', e=>{
        e.stopPropagation();
        document.querySelectorAll('.video-card video').forEach(v=>{
            if(v !== video){
                v.pause();
                v.muted = true;
            }
        });
        if(video.paused){
            video.play();
        } else {
            video.muted = !video.muted;
        }
    });
});

/* ITEM MODAL FUNCTIONS */
function openItemModal(item){
    modalTitle.innerText=item.title;
    modalSeller.innerText="Seller: "+item.seller;
    modalPrice.innerText="Price: $"+item.price;
    modalDesc.innerText=item.description;
    modalImage.src=item.image;
    itemModal.style.display="flex";
}
function closeItemModal(){ itemModal.style.display="none"; }

/* SEARCH FUNCTION */
function searchVideos(){
    let term = searchInput.value.trim();
    if(term)
        window.location='sell.php?page=home&category=<?= $category ?>&search='+encodeURIComponent(term);
}

/* UPLOAD MODAL FUNCTIONS */
function openUpload(){ document.getElementById("uploadModal").style.display="flex"; }
function closeUploadModal(){ document.getElementById("uploadModal").style.display="none"; }
document.getElementById("uploadForm").addEventListener("submit", function(e){
    e.preventDefault();
    const file = document.getElementById("videoFile").files[0];
    const category = document.getElementById("videoCategory").value;
    if(!file) return alert("Please select a video.");
    if(!category) return alert("Please select a category.");
    let formData = new FormData();
    formData.append("video", file);
    formData.append("category", category);
    fetch("pages/upload.php",{method:"POST",body:formData})
    .then(res=>res.json())
    .then(data=>{
        if(data.status==="success"){ alert("Video uploaded!"); location.reload(); }
        else{ document.getElementById("uploadStatus").innerText="Upload failed: "+data.message; }
    }).catch(err=>{
        document.getElementById("uploadStatus").innerText="Upload failed: "+err.message;
    });
});
</script>
</body>
</html>
