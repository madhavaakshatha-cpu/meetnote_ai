<?php
/**
 * MeetNote AI - Configuration Constants
 */

define('APP_NAME', 'MeetNote AI');
define('APP_VERSION', '1.0.0');

// Directories
define('BASE_DIR', dirname(dirname(__FILE__)));
define('SRC_DIR', BASE_DIR . '/src');
define('PUBLIC_DIR', BASE_DIR . '/public');
define('CONFIG_DIR', BASE_DIR . '/config');
define('UPLOAD_DIR', BASE_DIR . '/uploads/');
define('TEMP_DIR', BASE_DIR . '/temp/');
define('RESULTS_DIR', BASE_DIR . '/results/');
define('LOGS_DIR', BASE_DIR . '/logs/');

// API Configuration
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('OPENAI_MODEL', getenv('OPENAI_MODEL') ?: 'whisper-1');

// FFmpeg Configuration
define('FFMPEG_PATH', getenv('FFMPEG_PATH') ?: '/usr/bin/ffmpeg');

// File Upload Settings
define('MAX_FILE_SIZE', getenv('MAX_FILE_SIZE') ?: 5368709120); // 5GB
define('ALLOWED_AUDIO_FORMATS', ['mp3', 'wav', 'flac', 'ogg', 'aac', 'm4a', 'wma']);
define('ALLOWED_VIDEO_FORMATS', ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm']);

// Processing Settings
define('PROCESS_TIMEOUT', getenv('PROCESS_TIMEOUT') ?: 3600);
define('REMOVE_NOISE', getenv('REMOVE_NOISE') ?: 1);
define('HIGHLIGHT_POINTS', getenv('HIGHLIGHT_POINTS') ?: 1);
define('AUDIO_CHUNK_DURATION', 600); // 10 minutes

// Whisper API Settings
define('WHISPER_CHUNK_SIZE', 25 * 1024 * 1024); // 25MB

// Supported Languages
define('SUPPORTED_LANGUAGES', [
    'en' => 'English',
    'es' => 'Spanish',
    'fr' => 'French',
    'de' => 'German',
    'it' => 'Italian',
    'pt' => 'Portuguese',
    'nl' => 'Dutch',
    'ru' => 'Russian',
    'ja' => 'Japanese',
    'ko' => 'Korean',
    'zh' => 'Chinese (Mandarin)',
    'ar' => 'Arabic',
    'hi' => 'Hindi',
    'tr' => 'Turkish',
    'pl' => 'Polish'
]);

// Logging Configuration
define('LOG_DIR', getenv('LOG_DIR') ?: LOGS_DIR);
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'info');
define('LOG_FILE', getenv('LOG_FILE') ?: 'app.log');

// Application Settings
define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DEBUG', getenv('DEBUG') ?: true);

// Ensure required directories exist
$requiredDirs = [UPLOAD_DIR, TEMP_DIR, RESULTS_DIR, LOGS_DIR];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>
