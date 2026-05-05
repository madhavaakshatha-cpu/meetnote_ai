<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "meetnote_ai";

$mysqli = mysqli_init();
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
$mysqli->real_connect($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    die("Database connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . ".\nPlease ensure MySQL is running and the database is created.");
}

$conn = $mysqli;
?>