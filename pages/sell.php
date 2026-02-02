<?php
session_start();
if(!isset($_SESSION['user_id'])){
    // redirect to login if user is not logged in
    header("Location: index.php?page=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell Item</title>
<style>
body{margin:0;font-family:Arial,sans-serif;background:#f2f2f2;}
.container{max-width:500px;margin:50px auto;background:#fff;padding:25px;border-radius:15px;box-shadow:0 3px 15px rgba(0,0,0,0.1);}
h2{text-align:center;color:red;margin-bottom:25px;}
form label{display:block;margin-top:15px;font-weight:bold;color:#333;}
form input[type="text"], form input[type="number"], form textarea, form select{width:100%;padding:10px;margin-top:5px;border-radius:10px;border:1px solid #ccc;outline:none;}
form textarea{resize:none;height:80px;}
form input[type="file"]{margin-top:10px;}
form button{margin-top:20px;width:100%;padding:12px;background:red;color:#fff;border:none;border-radius:10px;font-size:16px;cursor:pointer;transition:0.2s;}
form button:hover{transform:scale(1.02);}
#previewImage{margin-top:10px;width:100%;border-radius:10px;display:none;}
#sellStatus{margin-top:10px;color:red;text-align:center;}
</style>
</head>
<body>

<div class="container">
<h2>Sell Your Item</h2>
<form id="sellForm">
<label for="title">Title</label>
<input type="text" id="title" name="title" placeholder="Item name" required>

<label for="description">Description</label>
<textarea id="description" name="description" placeholder="Describe your item" required></textarea>

<label for="price">Price ($)</label>
<input type="number" id="price" name="price" placeholder="Enter price" min="0" step="0.01" required>

<label for="quantity">Quantity</label>
<input type="number" id="quantity" name="quantity" placeholder="How many units?" min="1" value="1" required>

<label for="condition">Condition</label>
<select id="condition" name="condition" required>
<option value="">--Select Condition--</option>
<option value="New">New</option>
<option value="Used">Used</option>
<option value="Refurbished">Refurbished</option>
</select>

<label for="category">Category</label>
<select id="category" name="category" required>
<option value="">--Select Category--</option>
<option value="Music">Music</option>
<option value="Tech">Tech</option>
<option value="Gaming">Gaming</option>
<option value="Education">Education</option>
<option value="Trending">Trending</option>
<option value="Latest">Latest</option>
<option value="Comedy">Comedy</option>
<option value="Sports">Sports</option>
<option value="News">News</option>
<option value="Art">Art</option>
<option value="Live Stream">Live Stream</option>
</select>

<label for="tags">Tags (comma-separated)</label>
<input type="text" id="tags" name="tags" placeholder="Optional tags">

<label for="image">Upload Image</label>
<input type="file" id="image" name="image" accept="image/*" required>
<img id="previewImage" src="" alt="Preview Image">

<button type="submit">Sell Item</button>
<div id="sellStatus"></div>
</form>
</div>

<script>
// Image preview
document.getElementById('image').addEventListener('chang
