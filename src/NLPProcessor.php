<?php
/**
 * MeetNote AI - NLP Processor Class
 * Handles text processing, summarization, and note generation
 */

namespace MeetNoteAI;

class NLPProcessor
{
    /**
     * Generate summary from transcription
     * 
     * @param string $text Transcription text
     * @param float $ratio Summary ratio (0.0-1.0)
     * @return string Summary text
     */
    public function generateSummary($text, $ratio = 0.3)
    {
        $text = $this->cleanText($text);
        $sentences = $this->splitSentences($text);

        if (empty($sentences)) {
            return '';
        }

        $summaryCount = max(1, (int)ceil(count($sentences) * $ratio));
        $scores = $this->scoresentences($sentences);

        arsort($scores);
        $topSentenceIndices = array_slice(array_keys($scores), 0, $summaryCount);
        sort($topSentenceIndices);

        $summary = [];
        foreach ($topSentenceIndices as $index) {
            $summary[] = $sentences[$index];
        }

        return implode(' ', $summary);
    }

    /**
     * Extract key points from text
     * 
     * @param string $text Transcription text
     * @param int $maxPoints Maximum number of key points
     * @return array Array of key points
     */
    public function extractKeyPoints($text, $maxPoints = 10)
    {
        $text = $this->cleanText($text);
        $sentences = $this->splitSentences($text);

        if (empty($sentences)) {
            return [];
        }

        $scores = $this->scoreSentences($sentences);
        arsort($scores);

        $topSentenceIndices = array_slice(array_keys($scores), 0, $maxPoints);
        sort($topSentenceIndices);

        $keyPoints = [];
        foreach ($topSentenceIndices as $index) {
            $sentence = trim($sentences[$index]);
            if (!empty($sentence)) {
                $keyPoints[] = $sentence;
            }
        }

        return $keyPoints;
    }

    /**
     * Generate formatted notes
     * 
     * @param string $text Transcription text
     * @param array $keyPoints Key points to highlight
     * @return string Formatted notes in HTML
     */
    public function generateNotes($text, $keyPoints = [])
    {
        $text = $this->cleanText($text);
        $sentences = $this->splitSentences($text);

        $html = '<div class="notes-content">';

        foreach ($sentences as $index => $sentence) {
            $sentence = trim($sentence);

            if (empty($sentence)) {
                continue;
            }

            $isKeyPoint = false;
            foreach ($keyPoints as $keyPoint) {
                if (stripos($sentence, substr($keyPoint, 0, 20)) !== false) {
                    $isKeyPoint = true;
                    break;
                }
            }

            if ($isKeyPoint) {
                $html .= '<p class="key-point highlight"><strong>★ ' . htmlspecialchars($sentence) . '</strong></p>';
            } else {
                $html .= '<p>' . htmlspecialchars($sentence) . '</p>';
            }

            if (($index + 1) % 5 === 0) {
                $html .= '<hr class="section-divider" />';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Highlight key points in text
     * 
     * @param string $text Transcription text
     * @param array $keyPoints Key points to highlight
     * @return string Text with highlighted key points
     */
    public function highlightKeyPoints($text, $keyPoints = [])
    {
        foreach ($keyPoints as $keyPoint) {
            $pattern = '/(' . preg_quote($keyPoint, '/') . ')/i';
            $text = preg_replace($pattern, '<mark>$1</mark>', $text);
        }

        return $text;
    }

    /**
     * Generate text statistics
     * 
     * @param string $text Transcription text
     * @return array Statistics
     */
    public function getTextStatistics($text)
    {
        $text = $this->cleanText($text);
        $words = str_word_count($text);
        $sentences = count($this->splitSentences($text));
        $paragraphs = count(array_filter(explode("\n", $text)));
        $characters = strlen($text);

        $avgWordsPerSentence = $sentences > 0 ? round($words / $sentences, 2) : 0;
        $readingTime = ceil($words / 200); // Average reading speed

        return [
            'words' => $words,
            'characters' => $characters,
            'sentences' => $sentences,
            'paragraphs' => $paragraphs,
            'avg_words_per_sentence' => $avgWordsPerSentence,
            'reading_time_minutes' => $readingTime
        ];
    }

    /**
     * Clean text by removing extra whitespace and special characters
     * 
     * @param string $text
     * @return string Cleaned text
     */
    private function cleanText($text)
    {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * Split text into sentences
     * 
     * @param string $text
     * @return array Array of sentences
     */
    private function splitSentences($text)
    {
        $text = preg_replace('/([.!?])\s+/', '$1|', $text);
        $sentences = explode('|', $text);

        $cleanSentences = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (!empty($sentence) && strlen($sentence) > 5) {
                $cleanSentences[] = $sentence;
            }
        }

        return $cleanSentences;
    }

    /**
     * Score sentences based on importance
     * 
     * @param array $sentences Array of sentences
     * @return array Scored sentences
     */
    private function scoreSentences($sentences)
    {
        $scores = [];
        $words = [];

        // Build word frequency map
        foreach ($sentences as $sentence) {
            $sentenceWords = str_word_count(strtolower($sentence), 1);

            foreach ($sentenceWords as $word) {
                if (strlen($word) > 3) { // Ignore short words
                    $words[$word] = isset($words[$word]) ? $words[$word] + 1 : 1;
                }
            }
        }

        // Score sentences
        $maxScore = 0;
        foreach ($sentences as $index => $sentence) {
            $score = 0;
            $sentenceWords = str_word_count(strtolower($sentence), 1);

            foreach ($sentenceWords as $word) {
                $score += isset($words[$word]) ? $words[$word] : 0;
            }

            $scores[$index] = $score;
            $maxScore = max($maxScore, $score);
        }

        // Normalize scores
        if ($maxScore > 0) {
            foreach ($scores as $index => $score) {
                $scores[$index] = $score / $maxScore;
            }
        }

        return $scores;
    }

    /**
     * Get language detection (placeholder)
     * 
     * @param string $text
     * @return string Language code
     */
    public function detectLanguage($text)
    {
        // This is a simple placeholder
        // For production, use a proper language detection library
        return 'en';
    }
}
?>
