<?php
require_once __DIR__."/../db.php";
$items=$conn->query(
"SELECT s.*,u.username FROM sells s JOIN users u ON s.user_id=u.id ORDER BY created_at DESC"
);
?>

<h2>Store</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fill,200px);gap:15px">
<?php while($i=$items->fetch_assoc()): ?>
<div style="background:#fff;padding:10px;border-radius:10px">
<img src="<?= $i['image'] ?>" style="width:100%;border-radius:10px">
<b><?= $i['title'] ?></b><br>
$<?= $i['price'] ?><br>
Seller: <?= $i['username'] ?>
</div>
<?php endwhile; ?>
</div>
