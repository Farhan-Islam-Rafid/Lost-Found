<?php
include 'db.php';

// id নেওয়া
$id = $_GET['id'];

// delete query
mysqli_query($conn, "DELETE FROM products WHERE Id='$id'");

// আবার dashboard এ পাঠানো
header("Location: dashboard.php");
exit();
?>