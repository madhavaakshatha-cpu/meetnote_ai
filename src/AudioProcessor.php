<?php
/**
 * MeetNote AI - Audio Processor Class
 * Handles audio/video processing with FFmpeg
 */

namespace MeetNoteAI;

class AudioProcessor
{
    private $ffmpegPath;
    private $tempDir;
    private $processTimeout;

    public function __construct($ffmpegPath, $tempDir, $processTimeout = 3600)
    {
        $this->ffmpegPath = $ffmpegPath;
        $this->tempDir = rtrim($tempDir, '/') . '/';
        $this->processTimeout = $processTimeout;
    }

    /**
     * Extract audio from video file
     * 
     * @param string $inputFile Video file path
     * @param string $fileId Unique file ID
     * @return string|false Output audio file path or false on error
     */
    public function extractAudio($inputFile, $fileId)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $outputFile = $this->tempDir . $fileId . '_audio.mp3';

        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($inputFile) . 
                   ' -q:a 5 -n ' . escapeshellarg($outputFile) . ' 2>&1';

        exec($command, $output, $returnCode);

        return ($returnCode === 0 && file_exists($outputFile)) ? $outputFile : false;
    }

    /**
     * Remove background noise from audio
     * 
     * @param string $inputFile Audio file path
     * @param string $fileId Unique file ID
     * @return string|false Output audio file path or false on error
     */
    public function removeNoise($inputFile, $fileId)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $outputFile = $this->tempDir . $fileId . '_denoised.mp3';

        // Use FFmpeg with audio filters for noise reduction
        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($inputFile) . 
                   ' -af "anlmdn=f=8:t=0.002:om=o" -q:a 5 -n ' . escapeshellarg($outputFile) . ' 2>&1';

        exec($command, $output, $returnCode);

        return ($returnCode === 0 && file_exists($outputFile)) ? $outputFile : false;
    }

    /**
     * Get audio duration
     * 
     * @param string $audioFile Audio file path
     * @return int|false Duration in seconds or false on error
     */
    public function getAudioDuration($audioFile)
    {
        if (!file_exists($audioFile)) {
            return false;
        }

        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($audioFile) . 
                   ' 2>&1 | grep Duration';

        exec($command, $output);

        if (!empty($output) && preg_match('/Duration: (\d+):(\d+):(\d+)/', $output[0], $matches)) {
            return $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
        }

        return false;
    }

    /**
     * Convert audio to WAV format
     * 
     * @param string $inputFile Audio file path
     * @param string $fileId Unique file ID
     * @return string|false Output WAV file path or false on error
     */
    public function convertToWav($inputFile, $fileId)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $outputFile = $this->tempDir . $fileId . '_converted.wav';

        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($inputFile) . 
                   ' -acodec pcm_s16le -ar 16000 -n ' . escapeshellarg($outputFile) . ' 2>&1';

        exec($command, $output, $returnCode);

        return ($returnCode === 0 && file_exists($outputFile)) ? $outputFile : false;
    }

    /**
     * Split audio into chunks
     * 
     * @param string $audioFile Audio file path
     * @param string $fileId Unique file ID
     * @param int $chunkDuration Duration of each chunk in seconds
     * @return array Array of chunk file paths
     */
    public function splitAudioChunks($audioFile, $fileId, $chunkDuration = 600)
    {
        if (!file_exists($audioFile)) {
            return [];
        }

        $duration = $this->getAudioDuration($audioFile);
        if ($duration === false) {
            return [];
        }

        $chunks = [];
        $chunkCount = ceil($duration / $chunkDuration);

        for ($i = 0; $i < $chunkCount; $i++) {
            $startTime = $i * $chunkDuration;
            $outputFile = $this->tempDir . $fileId . '_chunk_' . $i . '.wav';

            $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($audioFile) . 
                       ' -ss ' . intval($startTime) . ' -t ' . intval($chunkDuration) . 
                       ' -acodec pcm_s16le -ar 16000 -n ' . escapeshellarg($outputFile) . ' 2>&1';

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($outputFile)) {
                $chunks[] = $outputFile;
            }
        }

        return $chunks;
    }

    /**
     * Get audio metadata
     * 
     * @param string $audioFile Audio file path
     * @return array Audio metadata
     */
    public function getAudioMetadata($audioFile)
    {
        if (!file_exists($audioFile)) {
            return [];
        }

        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($audioFile) . ' 2>&1';
        exec($command, $output);

        $metadata = [
            'duration' => 0,
            'bitrate' => 0,
            'sample_rate' => 0,
            'channels' => 0
        ];

        $outputStr = implode('\n', $output);

        if (preg_match('/Duration: (\d+):(\d+):(\d+)/', $outputStr, $matches)) {
            $metadata['duration'] = $matches[1] * 3600 + $matches[2] * 60 + $matches[3];
        }

        if (preg_match('/(\d+) kb\/s/', $outputStr, $matches)) {
            $metadata['bitrate'] = intval($matches[1]);
        }

        if (preg_match('/(\d+) Hz/', $outputStr, $matches)) {
            $metadata['sample_rate'] = intval($matches[1]);
        }

        if (preg_match('/(mono|stereo)/', $outputStr, $matches)) {
            $metadata['channels'] = $matches[1] === 'mono' ? 1 : 2;
        }

        return $metadata;
    }

    /**
     * Normalize audio levels
     * 
     * @param string $inputFile Audio file path
     * @param string $fileId Unique file ID
     * @return string|false Output audio file path or false on error
     */
    public function normalizeAudio($inputFile, $fileId)
    {
        if (!file_exists($inputFile)) {
            return false;
        }

        $outputFile = $this->tempDir . $fileId . '_normalized.mp3';

        // Use FFmpeg with loudnorm filter
        $command = escapeshellcmd($this->ffmpegPath) . ' -i ' . escapeshellarg($inputFile) . 
                   ' -af loudnorm -q:a 5 -n ' . escapeshellarg($outputFile) . ' 2>&1';

        exec($command, $output, $returnCode);

        return ($returnCode === 0 && file_exists($outputFile)) ? $outputFile : false;
    }

    /**
     * Clean up temporary files
     * 
     * @param string $fileId Unique file ID
     */
    public function cleanupTempFiles($fileId)
    {
        $tempFiles = glob($this->tempDir . $fileId . '_*');
        foreach ($tempFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
?>
