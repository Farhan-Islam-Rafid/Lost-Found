<?php
include 'db.php';
session_start();
$user_id = $_SESSION['user_id'];

if (isset($_POST['add'])) {

  $name = $_POST['name'];
  $user_id = $_SESSION['user_id'];
}
$desc = $_POST['description'];
$image = $_FILES['image']['name'];
$type = $_POST['type'];
$location = $_POST['location'];
$contact = $_POST['contact'];
$question = $_POST['question'];
$answer = $_POST['answer'];

move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);

$sql = "INSERT INTO products (user_id, name, description, image, type, location, contact, question, answer)
VALUES ('$user_id', '$name', '$desc', '$image', '$type', '$location', '$contact', '$question', '$answer')";

mysqli_query($conn, $sql);

header("Location: index.php");
