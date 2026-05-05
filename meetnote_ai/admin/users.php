<?php
include("../includes/auth.php");
include("../includes/db.php");

if($_SESSION['role'] != 'admin'){
    die("Access Denied");
}

// Delete user
if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    $conn->query("DELETE FROM users WHERE id=$id");

    header("Location: admin_dashboard.php");
}
?>