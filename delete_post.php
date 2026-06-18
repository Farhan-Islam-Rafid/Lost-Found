<?php

session_start();
include 'db.php';

$id=$_GET['id'];

mysqli_query(
$conn,
"DELETE FROM products
WHERE Id='$id'"
);

header("Location:admin.php");

?>