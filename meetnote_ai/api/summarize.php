<?php
include("../config.php");
include("../includes/db.php");

$file = $_GET['file'];
$lang = $_GET['lang'];

$stmt = $conn->prepare("SELECT transcript FROM files WHERE file_name = ?");
$stmt->bind_param("s", $file);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$text = $row['transcript'];

$data = [
"model"=>"gpt-4o-mini",
"messages"=>[
  [
   "role"=>"user",
   "content"=>"Analyze this conversation.
Separate speakers if possible (Speaker 1, Speaker 2).
Give summary and key points.
Translate everything into ".$lang.":\n".$text
  ]
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
    echo "Error: " . curl_error($ch);
    exit;
}
curl_close($ch);

$output = json_decode($result, true);
if (isset($output['error'])) {
    echo "API Error: " . $output['error']['message'];
    exit;
}
$summary = $output['choices'][0]['message']['content'];

// Save summary
$stmt = $conn->prepare("UPDATE files SET summary = ? WHERE file_name = ?");
$stmt->bind_param("ss", $summary, $file);
$stmt->execute();

include("../includes/header.php");
?>

<h2>📄 Notes (<?php echo htmlspecialchars($lang); ?>)</h2>
<div style='background:white;color:black;padding:20px;border-radius:10px'>
<?php echo nl2br(htmlspecialchars($summary)); ?>
</div>

<a href="../download.php?file=<?php echo urlencode($file); ?>&type=summary" class="btn btn-success mt-3">Download Summary</a>
<a href="../download.php?file=<?php echo urlencode($file); ?>&type=transcript" class="btn btn-info mt-3">Download Transcript</a>
<a href="../user_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>

</body>
</html>
?>

<hr>

<h3>💬 Chat with Notes</h3>

<input type="text" id="question" class="form-control mb-2" placeholder="Ask something...">
<button onclick="ask()" class="btn btn-primary">Ask</button>

<div id="chatResult" class="mt-3"></div>

<script>
function ask(){
    let q = document.getElementById("question").value;

    let formData = new FormData();
    formData.append("question", q);
    formData.append("file", "<?php echo $file; ?>");

    fetch("../chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("chatResult").innerHTML = data;
    });
}
</script>