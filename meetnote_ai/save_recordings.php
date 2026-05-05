<?php
include("includes/auth.php");
include("includes/db.php");
session_start();

if(isset($_FILES['audio'])){
    $file = "recording_".time().".mp3";
    $path = "uploads/audio/".$file;

    move_uploaded_file($_FILES['audio']['tmp_name'], $path);

    $stmt = $conn->prepare("INSERT INTO files (user_id, file_name) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $file);
    $stmt->execute();

    echo "Saved";
}
?>