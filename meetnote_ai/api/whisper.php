<?php
include("../config.php");
include("../includes/db.php");

$file = $_GET['file'] ?? '';
$lang = $_GET['lang'] ?? 'en-US';
$transcript = $_POST['transcript'] ?? '';

// If transcript is submitted from frontend
if ($transcript) {
    // Save to database
    $stmt = $conn->prepare("UPDATE files SET transcript = ? WHERE file_name = ?");
    $stmt->bind_param("ss", $transcript, $file);
    $stmt->execute();
    
    // Redirect to summary generation
    header("Location: summarize.php?file=" . urlencode($file) . "&lang=" . urlencode($lang));
    exit();
}

// Show Web Speech API interface for recording/transcription
include("../includes/header.php");
?>

<div class="container mt-5">
  <div class="card p-5 text-dark shadow-lg">
    <div class="text-center mb-5">
      <h2>🎤 Voice Recognition</h2>
      <p class="text-success"><strong>✅ 100% FREE - No API Key Needed!</strong></p>
      <p class="text-muted">Using your browser's built-in speech recognition</p>
    </div>

    <!-- File Info -->
    <div class="alert alert-info mb-4">
      <strong>📁 File:</strong> <?php echo htmlspecialchars($file); ?><br>
      <strong>🌍 Language:</strong> <?php echo htmlspecialchars($lang); ?>
    </div>

    <!-- Transcript Display -->
    <div id="transcript-box" class="p-4 mb-4" style="background: #f8f9fa; border-radius: 8px; min-height: 200px; border: 2px solid #dee2e6; overflow-y: auto; max-height: 400px;">
      <p class="text-muted" id="status" style="text-align: center; font-size: 18px;">
        👇 Click 'Start Recording' to begin transcription
      </p>
      <p id="result" style="white-space: pre-wrap; display: none; font-size: 16px; line-height: 1.8;"></p>
    </div>

    <!-- Control Buttons -->
    <div class="d-grid gap-2 mb-3">
      <button class="btn btn-lg btn-primary" id="startBtn" onclick="startListening()" style="font-size: 18px;">
        🎙️ Start Recording
      </button>
      
      <button class="btn btn-lg btn-danger" id="stopBtn" onclick="stopListening()" style="display: none; font-size: 18px;">
        ⏹️ Stop Recording
      </button>
    </div>

    <!-- Submit Button -->
    <button class="btn btn-lg btn-success w-100" id="submitBtn" onclick="submitTranscript()" style="display: none; font-size: 18px;" disabled>
      ✅ Save & Generate Summary
    </button>

    <!-- Info -->
    <div class="alert alert-warning mt-4" role="alert">
      <strong>💡 Tips:</strong>
      <ul class="mb-0">
        <li>Speak clearly and at a normal pace</li>
        <li>The transcription happens in real-time</li>
        <li>Click "Stop Recording" when done</li>
        <li>Works best in Chrome, Edge, or Safari browsers</li>
      </ul>
    </div>
  </div>
</div>

<script>
  // Web Speech API
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  
  if (!SpeechRecognition) {
    document.getElementById('status').innerHTML = '❌ Your browser does not support Web Speech API. Please use Chrome, Edge, or Safari.';
    document.getElementById('startBtn').disabled = true;
  }
  
  const recognition = new (SpeechRecognition || function() {})();
  let finalTranscript = "";
  let isListening = false;
  
  // Convert language code
  const langMap = {
    'english': 'en-US',
    'hindi': 'hi-IN',
    'kannada': 'kn-IN',
    'tamil': 'ta-IN',
    'telugu': 'te-IN',
    'marathi': 'mr-IN',
    'spanish': 'es-ES',
    'french': 'fr-FR',
    'german': 'de-DE',
    'chinese': 'zh-CN',
    'japanese': 'ja-JP',
    'korean': 'ko-KR'
  };
  
  const userLang = "<?php echo htmlspecialchars($lang); ?>".toLowerCase();
  const recognitionLang = langMap[userLang] || userLang || 'en-US';
  
  recognition.continuous = true;
  recognition.interimResults = true;
  recognition.lang = recognitionLang;
  
  recognition.onstart = function() {
    isListening = true;
    finalTranscript = "";
    document.getElementById('startBtn').style.display = 'none';
    document.getElementById('stopBtn').style.display = 'block';
    document.getElementById('result').style.display = 'block';
    document.getElementById('result').innerHTML = "";
    document.getElementById('status').innerHTML = "🎙️ Listening... Speak now!";
    document.getElementById('status').style.color = "green";
  };
  
  recognition.onresult = function(event) {
    let interimTranscript = "";
    
    for (let i = event.resultIndex; i < event.results.length; i++) {
      const transcript = event.results[i][0].transcript;
      
      if (event.results[i].isFinal) {
        finalTranscript += transcript + " ";
      } else {
        interimTranscript += transcript;
      }
    }
    
    document.getElementById('result').innerHTML = 
      finalTranscript + '<span style="color: #999; font-style: italic;">' + interimTranscript + '</span>';
  };
  
  recognition.onerror = function(event) {
    document.getElementById('status').innerHTML = "❌ Error: " + event.error;
    document.getElementById('status').style.color = "red";
  };
  
  recognition.onend = function() {
    isListening = false;
    document.getElementById('startBtn').style.display = 'block';
    document.getElementById('stopBtn').style.display = 'none';
    document.getElementById('status').innerHTML = "✅ Recording stopped!";
    document.getElementById('status').style.color = "green";
    document.getElementById('submitBtn').style.display = 'block';
    document.getElementById('submitBtn').disabled = finalTranscript.trim().length === 0;
  };
  
  function startListening() {
    finalTranscript = "";
    document.getElementById('result').innerHTML = "";
    document.getElementById('submitBtn').style.display = 'none';
    recognition.start();
  }
  
  function stopListening() {
    recognition.stop();
  }
  
  function submitTranscript() {
    if (!finalTranscript.trim()) {
      alert("No speech detected. Please try again.");
      return;
    }
    
    document.getElementById('status').innerHTML = "⏳ Saving transcript...";
    document.getElementById('submitBtn').disabled = true;
    
    // Submit via form
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="transcript" value="' + finalTranscript.replace(/"/g, '&quot;') + '">';
    document.body.appendChild(form);
    form.submit();
  }
</script>

</body>
</html>