<?php
include("config.php");
include("includes/db.php");
include("includes/auth.php");
include("includes/header.php");

$file = $_GET['file'] ?? $_POST['file'] ?? '';

if (!$file) {
    die("No file specified.");
}

// Check if file belongs to user
$stmt = $conn->prepare("SELECT transcript FROM files WHERE file_name = ? AND user_id = ?");
$stmt->bind_param("si", $file, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("File not found or access denied.");
}
$row = $result->fetch_assoc();
$context = $row['transcript'];

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $_POST['question'];

    $data = [
    "model"=>"gpt-4o-mini",
    "messages"=>[
      ["role"=>"system","content"=>"Answer based only on this:\n".$context],
      ["role"=>"user","content"=>$question]
    ]
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer ".API_KEY
    ]);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $message = "Error: " . curl_error($ch);
    } else {
        curl_close($ch);
        $output = json_decode($result, true);
        if (isset($output['error'])) {
            $message = "API Error: " . $output['error']['message'];
        } else {
            $message = $output['choices'][0]['message']['content'];
        }
    }
}
?>

<h2>Chat with Transcript: <?php echo htmlspecialchars($file); ?></h2>

<form method="POST">
<input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
<div class="mb-3">
    <label for="question" class="form-label">Ask a question about the transcript:</label>
    <textarea name="question" id="question" class="form-control" rows="3" required></textarea>
</div>
<button type="submit" class="btn btn-primary">Ask</button>
</form>

<?php if ($message): ?>
<div class="mt-3">
    <h4>Response:</h4>
    <div class="alert alert-info"><?php echo nl2br(htmlspecialchars($message)); ?></div>
</div>
<?php endif; ?>

<a href="user_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

</body>
</html>
?>