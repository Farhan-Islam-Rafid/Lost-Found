<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) { exit("Unauthorized access"); }
// Add your admin role validation checks here...

$target_user = mysqli_real_escape_string($conn, $_GET['id']);
$message = mysqli_real_escape_string($conn, $_GET['msg']);

// Example storage update logic:
// mysqli_query($conn, "INSERT INTO warnings (user_id, message, created_at) VALUES ('$target_user', '$message', NOW())");

header("Location: admin.php");
exit();
?>