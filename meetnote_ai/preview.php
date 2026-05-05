<?php
include("includes/auth.php");
include("includes/db.php");
include("includes/header.php");

$file = $_GET['file'] ?? '';
$type = $_GET['type'] ?? '';

if (!$file || !$type) {
    die("Invalid request");
}

// Verify user owns this file
$stmt = $conn->prepare("SELECT transcript, summary, file_name FROM files WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $file, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("File not found or access denied");
}

$row = $result->fetch_assoc();
$content = "";
$title = "";
$icon = "";
$downloadFilename = "";

if ($type == 'transcript') {
    $content = $row['transcript'];
    $title = "📄 Transcript";
    $icon = "📄";
    $downloadFilename = "transcript_" . $file . ".txt";
    if (!$content) {
        die("Transcript not available yet. Processing...");
    }
} elseif ($type == 'summary') {
    $content = $row['summary'];
    $title = "📝 Summary & Notes";
    $icon = "📝";
    $downloadFilename = "summary_" . $file . ".txt";
    if (!$content) {
        die("Summary not available yet. Processing...");
    }
} elseif ($type == 'original') {
    // Original file preview/download
    $uploadDir = __DIR__ . "/uploads/audio/";
    $filePath = $uploadDir . $file;
    
    if (!file_exists($filePath)) {
        die("Original file not found");
    }
    
    // For original file, just redirect to download
    $filesize = filesize($filePath);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    header('Content-Type: ' . $mimetype);
    header('Content-Disposition: inline; filename="' . basename($file) . '"');
    header('Content-Length: ' . $filesize);
    readfile($filePath);
    exit;
} else {
    die("Invalid type");
}
?>

<div class="container mt-5">
  <div class="card p-5 text-dark shadow-lg">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
      <h2><?php echo $title; ?></h2>
      <button class="btn btn-primary" onclick="downloadFile()">📥 Download</button>
    </div>

    <!-- File Info -->
    <div class="alert alert-info mb-4">
      <strong>File:</strong> <?php echo htmlspecialchars($row['file_name']); ?><br>
      <strong>Generated:</strong> <?php echo date('M d, Y H:i:s'); ?>
    </div>

    <!-- Content Display -->
    <div class="content-box p-4 mb-4" style="background: #f8f9fa; border-radius: 8px; max-height: 600px; overflow-y: auto; border: 1px solid #dee2e6;">
      <pre style="white-space: pre-wrap; word-wrap: break-word; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;"><?php echo htmlspecialchars($content); ?></pre>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex gap-2">
      <button class="btn btn-success" onclick="downloadFile()">📥 Download as .txt</button>
      <button class="btn btn-secondary" onclick="copyToClipboard()">📋 Copy to Clipboard</button>
      <button class="btn btn-danger" onclick="window.close()">❌ Close</button>
    </div>

  </div>
</div>

<script>
  function downloadFile() {
    const filename = "<?php echo htmlspecialchars($downloadFilename); ?>";
    const content = `<?php echo htmlspecialchars(addslashes($content)); ?>`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    a.remove();
  }

  function copyToClipboard() {
    const content = document.querySelector('.content-box pre').textContent;
    navigator.clipboard.writeText(content).then(() => {
      alert('✓ Copied to clipboard!');
    }).catch(() => {
      alert('❌ Failed to copy. Try manual selection.');
    });
  }
</script>

</body>
</html>
