<?php
session_start();
include 'db.php';

// Verification layer
if (!isset($_SESSION['user_id'])) { 
    exit("Unauthorized access"); 
}

$user = $_SESSION['user_id'];
$q = mysqli_query($conn, "SELECT role FROM users WHERE id='$user'");
$data = mysqli_fetch_assoc($q);

if (!$data || $data['role'] != "admin") { 
    die("Access Denied"); 
}

// FIX: Cast directly to an integer for bulletproof numeric security
$target_user = (int)$_GET['id']; 

// Sanitize the action string just in case
$action = mysqli_real_escape_string($conn, $_GET['action']);

$new_status = ($action === 'ban') ? 'Banned' : 'Active';

// Execute the update
$update = mysqli_query($conn, "UPDATE users SET status='$new_status' WHERE id='$target_user'");

// Bounce back smoothly to the admin dashboard
header("Location: admin.php"); 
exit();
?>

