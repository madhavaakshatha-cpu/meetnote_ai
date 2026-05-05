<?php
include("includes/auth.php");
include("includes/db.php");

if(isset($_POST['upload'])){

    // Validate upload
    if(!isset($_FILES['file']) || $_FILES['file']['error'] != 0){
        die("❌ File upload error");
    }

    $file = $_FILES['file']['name'];
    $tmp  = $_FILES['file']['tmp_name'];

    // Allowed types
    $allowed = ['mp3', 'wav', 'm4a', 'mp4', 'avi'];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        die("❌ Invalid file type");
    }

    // Unique name
    $newFileName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $file);

    // Folder (relative to project root)
    $uploadDir = __DIR__ . "/uploads/audio/";   // absolute path (safer)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fullPath = $uploadDir . $newFileName;

    // Move file
    if(!move_uploaded_file($tmp, $fullPath)){
        die("❌ Failed to save file (check folder permission)");
    }

    // Save in DB
    $lang = $_POST['language'];
    $stmt = $conn->prepare("INSERT INTO files (user_id, file_name) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $newFileName);
    $stmt->execute();

    // Redirect to processing
    header("Location: api/whisper.php?file=".urlencode($newFileName)."&lang=".urlencode($lang));
    exit();
}
?>

<?php include("includes/header.php"); ?>

<div class="container mt-5">
  <div class="card p-4 text-dark">

    <h3>Upload Audio / Video 🎧🎥</h3>

    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="file" class="form-control mb-3" required>

      <input type="text" name="language" class="form-control mb-3"
             placeholder="Enter output language (e.g., English, Kannada, Hindi)" required>

      <button name="upload" class="btn btn-primary w-100">Upload</button>
    </form>

    <p class="mt-3 text-muted">
      Supports: mp3, wav, m4a, mp4, avi
    </p>

  </div>

  <!-- Display previously uploaded files -->
  <div class="card p-4 text-dark mt-5">
    <h3>Your Uploaded Files 📁</h3>
    
    <table class="table table-striped">
      <thead>
        <tr>
          <th>File Name</th>
          <th>Status</th>
          <th>Download Options</th>
        </tr>
      </thead>
      <tbody>
<?php
// Fetch user's files
$stmt = $conn->prepare("SELECT id, file_name, transcript, summary FROM files WHERE user_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<tr><td colspan='3' class='text-center text-muted'>No files uploaded yet</td></tr>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['file_name']) . "</strong></td>";
        
        // Status
        if ($row['transcript'] && $row['summary']) {
            echo "<td><span class='badge bg-success'>✓ Completed</span></td>";
        } elseif ($row['transcript']) {
            echo "<td><span class='badge bg-warning'>⏳ Processing</span></td>";
        } else {
            echo "<td><span class='badge bg-info'>📥 Uploaded</span></td>";
        }
        
        // Download buttons (3 options)
        echo "<td>";
        
        // Button 1: Download Original File
        echo "<a href='preview.php?file=" . urlencode($row['file_name']) . "&type=original' class='btn btn-sm btn-outline-primary me-1' target='_blank' title='View original file'>📥 Original</a> ";
        
        // Button 2: Download Transcript
        if ($row['transcript']) {
            echo "<a href='preview.php?file=" . urlencode($row['file_name']) . "&type=transcript' class='btn btn-sm btn-outline-success me-1' target='_blank' title='View transcript'>📄 Transcript</a> ";
        } else {
            echo "<button class='btn btn-sm btn-outline-secondary me-1' disabled>📄 Transcript</button> ";
        }
        
        // Button 3: Download Summary
        if ($row['summary']) {
            echo "<a href='preview.php?file=" . urlencode($row['file_name']) . "&type=summary' class='btn btn-sm btn-outline-info' target='_blank' title='View summary'>📝 Summary</a>";
        } else {
            echo "<button class='btn btn-sm btn-outline-secondary' disabled>📝 Summary</button>";
        }
        
        echo "</td>";
        echo "</tr>";
    }
}
?>
      </tbody>
    </table>
  </div>

</div>

</body>
</html>