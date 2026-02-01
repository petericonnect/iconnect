<?php
session_start();
define("BASE_PATH", __DIR__);
require_once BASE_PATH . "/db.php";

// Mock logged-in user
$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1;

// Determine page
$page = $_GET['page'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>iConnect Dashboard</title>
<style>
/* ===== GENERAL ===== */
body { margin:0; font-family:sans-serif; background:#f5f5f5; }
nav { background:#fff; padding:10px 20px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 5px rgba(0,0,0,0.1);}
nav .logo { font-weight:bold; font-size:20px; color:red; cursor:pointer;}
.active { font-weight:bold; color:red; }

/* ===== SEARCH BAR ===== */
.search-bar { display:flex; justify-content:center; padding:15px; background:#fff; border-bottom:1px solid #ddd; }
.search-bar input { width:300px; padding:10px 14px; border:1px solid #ccc; border-radius:20px 0 0 20px; outline:none; font-size:14px;}
.search-bar input:focus { border-color:red; }
.search-bar button { padding:10px 16px; border:1px solid red; background:red; color:white; cursor:pointer; border-radius:0 20px 20px 0; font-size:14px;}
.search-bar button:hover { background:#cc0000; }

/* ===== CATEGORIES ===== */
.categories { display:flex; gap:10px; padding:10px 20px; overflow-x:auto; white-space:nowrap; background:#fff; border-bottom:1px solid #ddd; }
.categories span { cursor:pointer; padding:6px 12px; border-radius:20px; transition:0.2s; }
.categories span:hover { background:red; color:white; }

/* ===== VIDEO / ITEM GRID ===== */
.video-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:15px; padding:15px; }
.video-card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 5px rgba(0,0,0,0.1); cursor:pointer; }
.video-card video { width:100%; height:150px; object-fit:cover; }
.video-card .title { padding:8px; font-weight:bold; font-size:14px; }

/* ===== GO LIVE MODAL ===== */
#goLiveModal { position: fixed; inset:0; background: rgba(0,0,0,0.85); display:none; align-items:center; justify-content:center; z-index:999;}
.live-box { position:relative; background:black; padding:15px; border-radius:12px; width:90%; max-width:400px; text-align:center;}
.live-box video { width:100%; border-radius:10px; margin-bottom:10px;}
.live-header { display:flex; justify-content:space-between; color:white; margin-bottom:10px; font-weight:bold; }
.live-badge { color:red; }
#heartsContainer { position:absolute; bottom:60px; right:20px; pointer-events:none; }

/* Floating chat */
#floatingChat { position:absolute; top:10px; left:10px; width:250px; display:flex; flex-direction:column-reverse; gap:5px; pointer-events:none; z-index:10; }
.floating-message { background: rgba(0,0,0,0.6); color:white; padding:5px 10px; border-radius:15px; font-size:14px; animation: floatFade 4s forwards; }
@keyframes floatFade { 0% { opacity:1; transform: translateY(0) scale(1); } 50% { opacity:1; transform: translateY(-20px) scale(1.1); } 100% { opacity:0; transform: translateY(-50px) scale(1.2); } }

/* Chat input */
.chat-input-container { position:absolute; bottom:10px; left:10px; display:flex; gap:5px; }
.chat-input-container input { padding:8px 12px; border-radius:20px; border:none; width:180px; }
.chat-input-container button { padding:8px 12px; border-radius:20px; border:none; background:red; color:white; cursor:pointer; }

/* Live actions */
.live-actions { margin-top:10px; display:flex; justify-content:center; gap:10px; }
.live-actions button { padding:10px 16px; border:none; border-radius:20px; font-weight:bold; cursor:pointer; }
.live-actions button:first-of-type { background:red; color:white; }

/* SELL/STORE MODAL */
#itemModal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:998;}
#itemModal .modal-box { background:#fff; padding:20px; border-radius:10px; width:90%; max-width:400px; position:relative;}
#itemModal .close-btn { position:absolute; top:10px; right:15px; font-size:20px; cursor:pointer; }

/* Responsive */
@media(max-width:768px){ .video-grid { grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); } }
@media(max-width:480px){ .video-grid { grid-template-columns:repeat(2,1fr); } }
</style>
</head>
<body>

<nav>
    <div class="logo" onclick="window.location='index.php'">iConnect</div>
</nav>

<?php include BASE_PATH . "/pages/{$page}.php"; ?>

<!-- ===== GO LIVE MODAL ===== -->
<div id="goLiveModal">
    <div class="live-box">
        <div class="live-header">
            <span class="live-badge">🔴 LIVE</span>
            <span class="viewer-count">0 viewers</span>
        </div>
        <video id="liveVideo" autoplay muted playsinline></video>
        <div id="heartsContainer"></div>
        <button class="heart-btn" onclick="sendHeart()">❤️</button>
        <div id="floatingChat"></div>
        <div class="chat-input-container">
            <input type="text" id="chatInput" placeholder="Say something..." />
            <button onclick="sendChat()">Send</button>
        </div>
        <div class="live-actions">
            <button onclick="startLive()">Start Live</button>
            <button onclick="endLive()">End Live</button>
        </div>
    </div>
</div>

<!-- ===== SELL / STORE MODAL ===== -->
<div id="itemModal">
    <div class="modal-box">
        <span class="close-btn" onclick="closeItemModal()">✖</span>
        <h2 id="itemTitle"></h2>
        <p><strong>Price:</strong> <span id="itemPrice"></span></p>
        <p id="itemDescription"></p>
        <p><strong>Seller:</strong> <span id="itemSeller"></span></p>
        <button style="background:red; color:white; padding:10px 15px; border:none; border-radius:8px; cursor:pointer;">Buy Now</button>
    </div>
</div>

<script>
// ===== GLOBAL =====
let stream, mediaRecorder, recordedChunks = [];
let streamingLive = false;
let streamerId = <?= $_SESSION['user_id'] ?>;

// ===== GO LIVE =====
function openGoLive(){
    document.getElementById("goLiveModal").style.display="flex";
    navigator.mediaDevices.getUserMedia({video:true,audio:true})
    .then(s=>{stream=s; document.getElementById("liveVideo").srcObject=stream;})
    .catch(err=>{alert("Camera access denied"); endLive();});
}

function closeGoLive(){ endLive(); document.getElementById("goLiveModal").style.display="none"; }

function startLive(){
    if(!stream) return alert("Camera not ready");
    streamingLive = true;
    mediaRecorder = new MediaRecorder(stream);
    mediaRecorder.ondataavailable = e=>{ if(e.data.size>0) recordedChunks.push(e.data); };
    mediaRecorder.onstop = ()=>{
        let blob = new Blob(recordedChunks,{type:'video/webm'});
        recordedChunks=[];
        let fd=new FormData(); fd.append('video',blob,'live_record.webm');
        fetch('save_live.php',{method:'POST',body:fd}).then(res=>res.text()).then(console.log).catch(console.error);
    };
    mediaRecorder.start();
    joinViewer();
    fetchChat();
}

function endLive(){
    streamingLive=false;
    if(mediaRecorder && mediaRecorder.state!=="inactive") mediaRecorder.stop();
    if(stream) stream.getTracks().forEach(track=>track.stop());
    document.getElementById("floatingChat").innerHTML="";
}

// ===== HEARTS =====
function sendHeart(){
    const heart=document.createElement("span");
    heart.innerText="❤️";
    heart.style.position="absolute";
    heart.style.fontSize=Math.floor(Math.random()*20+20)+"px";
    heart.style.bottom="0px";
    heart.style.right=Math.floor(Math.random()*50)+"px";
    heart.style.opacity=1;
    heart.style.transition="all 2s ease-out";
    document.getElementById("heartsContainer").appendChild(heart);
    setTimeout(()=>{heart.style.transform="translateY(-150px) scale(1.5)"; heart.style.opacity=0;},50);
    setTimeout(()=>heart.remove(),2000);
}

// ===== VIEWER COUNT =====
function joinViewer(){]
    if(!streamingLive) return;
    fetch('update_viewers.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'streamer_id='+streamerId+'&action=join'})
    .then(res=>res.json()).then(data=>{ document.querySelector(".viewer-count").innerText=data.viewers+" viewers"; })
    .catch(console.error);
    setTimeout(joinViewer,2000);
}
window.addEventListener('beforeunload',()=>{ navigator.sendBeacon('update_viewers.php','streamer_id='+streamerId+'&action=leave'); });

// ===== FLOATING CHAT =====
function showFloatingMessage(user,message){
    if(!streamingLive) return;
    const chatContainer = document.getElementById("floatingChat");
    const msg = document.createElement("div");
    msg.classList.add("floating-message");
    msg.innerText = user+": "+message;
    msg.style.left = Math.floor(Math.random()*150)+"px";
    chatContainer.appendChild(msg);
    setTimeout(()=> msg.remove(), 4000);
}
function fetchChat(){
    if(!streamingLive) return;
    fetch('live_chat.php?streamer_id='+streamerId)
        .then(res=>res.json())
        .then(data => {
            const lastMessages = data.slice(-5);
            lastMessages.forEach(msg => showFloatingMessage(msg.user,msg.message));
        }).catch(console.error);
    setTimeout(fetchChat,2000);
}
function sendChat(){
    let input=document.getElementById("chatInput");
    let msg=input.value.trim();
    if(!msg) return;
    fetch('live_chat.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'streamer_id='+streamerId+'&message='+encodeURIComponent(msg)
    }).then(res=>{
        input.value='';
        fetchChat();
    });
}

// ===== SELL/STORE MODAL =====
function openItemModal(item){
    document.getElementById("itemTitle").innerText = item.title;
    document.getElementById("itemPrice").innerText = item.price;
    document.getElementById("itemDescription").innerText = item.description;
    document.getElementById("itemSeller").innerText = item.seller;
    document.getElementById("itemModal").style.display = "flex";
}
function closeItemModal(){ document.getElementById("itemModal").style.display="none"; }

// ===== UPLOAD =====
function openUpload(){
    let input = document.createElement("input");
    input.type = "file";
    input.accept = "video/*";
    input.onchange = e => {
        let file = e.target.files[0];
        if(!file) return;
        let formData = new FormData();
        formData.append('video', file);
        fetch('pages/upload.php', {method:'POST', body: formData})
        .then(res => res.json())
        .then(data => {
            if(data.status==='success'){ alert('Video uploaded!'); location.reload(); }
            else alert('Upload failed: '+data.message);
        }).catch(console.error);
    };
    input.click();
}
</script>
</body>
</html>
