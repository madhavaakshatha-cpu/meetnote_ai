<?php
include("config.php");
include("includes/db.php");
include("includes/auth.php");

$file = $_GET['file'];
$type = $_GET['type']; // 'summary', 'transcript', or 'original'

$stmt = $conn->prepare("SELECT transcript, summary FROM files WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $file, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("File not found or access denied.");
}
$row = $result->fetch_assoc();

$content = "";
$filename = "";
$isFile = false;

if ($type == 'summary') {
    $content = $row['summary'];
    $filename = "summary_" . $file . ".txt";
    if (!$content) {
        die("Summary not available yet.");
    }
} elseif ($type == 'transcript') {
    $content = $row['transcript'];
    $filename = "transcript_" . $file . ".txt";
    if (!$content) {
        die("Transcript not available yet.");
    }
} elseif ($type == 'original') {
    // Download original audio/video file
    $uploadDir = __DIR__ . "/uploads/audio/";
    $filePath = $uploadDir . $file;
    
    if (!file_exists($filePath)) {
        die("Original file not found.");
    }
    
    $filename = $file;
    $isFile = true;
} else {
    die("Invalid type.");
}

// Handle file download
if ($isFile) {
    $filePath = __DIR__ . "/uploads/audio/" . $file;
    
    // Get file size
    $filesize = filesize($filePath);
    
    // Detect MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    // Set headers for file download
    header('Content-Type: ' . $mimetype);
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Content-Length: ' . $filesize);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Read and output file
    readfile($filePath);
} else {
    // Text download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    
    echo $content;
}
exit;
?>