<?php
/**
 * MeetNote AI - Whisper API Integration Class
 * Handles transcription using OpenAI Whisper API
 */

namespace MeetNoteAI;

class WhisperAPI
{
    private $apiKey;
    private $apiUrl = 'https://api.openai.com/v1/audio/transcriptions';
    private $model = 'whisper-1';
    private $chunkSize = 25 * 1024 * 1024; // 25MB for Whisper API limit

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Transcribe audio file
     * 
     * @param string $audioFile Path to audio file
     * @param string $language Language code (optional)
     * @return array Transcription result
     */
    public function transcribe($audioFile, $language = null)
    {
        if (!file_exists($audioFile)) {
            return [
                'success' => false,
                'error' => 'Audio file not found'
            ];
        }

        $fileSize = filesize($audioFile);

        // Handle large files by splitting into chunks
        if ($fileSize > $this->chunkSize) {
            return $this->transcribeChunked($audioFile, $language);
        }

        return $this->transcribeSingle($audioFile, $language);
    }

    /**
     * Transcribe single audio file (< 25MB)
     * 
     * @param string $audioFile Path to audio file
     * @param string $language Language code (optional)
     * @return array Transcription result
     */
    private function transcribeSingle($audioFile, $language = null)
    {
        $fileHandle = fopen($audioFile, 'r');

        if (!$fileHandle) {
            return [
                'success' => false,
                'error' => 'Cannot open audio file'
            ];
        }

        $boundary = '----WebKitFormBoundary' . bin2hex(random_bytes(16));
        $body = '';

        // Add form fields
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Disposition: form-data; name="model"' . "\r\n\r\n";
        $body .= $this->model . "\r\n";

        if ($language) {
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="language"' . "\r\n\r\n";
            $body .= $language . "\r\n";
        }

        // Add file
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . basename($audioFile) . '"' . "\r\n";
        $body .= 'Content-Type: audio/mpeg' . "\r\n\r\n";
        $body .= file_get_contents($audioFile) . "\r\n";
        $body .= '--' . $boundary . '--' . "\r\n";

        fclose($fileHandle);

        return $this->makeRequest($body, $boundary);
    }

    /**
     * Transcribe chunked audio file (> 25MB)
     * 
     * @param string $audioFile Path to audio file
     * @param string $language Language code (optional)
     * @return array Transcription result with all chunks combined
     */
    private function transcribeChunked($audioFile, $language = null)
    {
        $chunks = [];
        $handle = fopen($audioFile, 'rb');

        if (!$handle) {
            return [
                'success' => false,
                'error' => 'Cannot open audio file'
            ];
        }

        while (!feof($handle)) {
            $chunk = fread($handle, $this->chunkSize);
            if (strlen($chunk) > 0) {
                $chunks[] = $chunk;
            }
        }

        fclose($handle);

        $transcriptions = [];

        foreach ($chunks as $index => $chunkData) {
            $boundary = '----WebKitFormBoundary' . bin2hex(random_bytes(16));
            $body = '';

            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="model"' . "\r\n\r\n";
            $body .= $this->model . "\r\n";

            if ($language) {
                $body .= '--' . $boundary . "\r\n";
                $body .= 'Content-Disposition: form-data; name="language"' . "\r\n\r\n";
                $body .= $language . "\r\n";
            }

            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="file"; filename="chunk_' . $index . '.mp3"' . "\r\n";
            $body .= 'Content-Type: audio/mpeg' . "\r\n\r\n";
            $body .= $chunkData . "\r\n";
            $body .= '--' . $boundary . '--' . "\r\n";

            $result = $this->makeRequest($body, $boundary);

            if ($result['success']) {
                $transcriptions[] = $result['text'];
            } else {
                return $result;
            }
        }

        return [
            'success' => true,
            'text' => implode(' ', $transcriptions),
            'language' => $language
        ];
    }

    /**
     * Make API request to OpenAI
     * 
     * @param string $body Request body
     * @param string $boundary Multipart boundary
     * @return array API response
     */
    private function makeRequest($body, $boundary)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Bearer ' . $this->apiKey,
                    'Content-Type: multipart/form-data; boundary=' . $boundary
                ],
                'content' => $body,
                'timeout' => 3600
            ]
        ]);

        try {
            $response = file_get_contents($this->apiUrl, false, $context);

            if ($response === false) {
                return [
                    'success' => false,
                    'error' => 'API request failed'
                ];
            }

            $result = json_decode($response, true);

            if (isset($result['error'])) {
                return [
                    'success' => false,
                    'error' => $result['error']['message'] ?? 'Unknown error'
                ];
            }

            return [
                'success' => true,
                'text' => $result['text'] ?? '',
                'language' => $result['language'] ?? null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Set model version
     * 
     * @param string $model Model name
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Get supported languages
     * 
     * @return array Array of language codes and names
     */
    public static function getSupportedLanguages()
    {
        return [
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
        ];
    }
}
?>
