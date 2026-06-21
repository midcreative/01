<?php

declare(strict_types=1);

namespace App\Services;

class GeminiSentimentService
{
    private string $apiKey;
    private string $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
    }

    /**
     * Analyze text sentiment towards a specific candidate.
     * Returns: 'positive', 'neutral', or 'negative'
     */
    public function analyze(string $candidateName, string $title, string $content): string
    {
        if (empty($this->apiKey)) {
            return 'neutral';
        }
        $prompt = "請分析以下新聞標題與內容，判斷針對政治人物「{$candidateName}」的感情傾向。\n";
        $prompt .= "新聞標題：{$title}\n";
        $prompt .= "新聞內容摘要：{$content}\n\n";
        $prompt .= "請只回覆以下三個英文單字之一（不要有任何標點符號或其他解釋）：\n";
        $prompt .= "positive\nneutral\nnegative";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1, // 低溫度以獲得更確定性的單詞回覆
                'maxOutputTokens' => 10,
            ]
        ];

        $ch = curl_init($this->endpoint . '?key=' . $this->apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 避免長時間卡死報 500
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            // 如遇 API 錯誤，保守預設為中立
            error_log("Gemini API Error: HTTP $httpCode - " . ($response ?: 'No response'));
            return 'neutral';
        }

        $result = json_decode($response, true);
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $text = strtolower(trim($text));

        if (in_array($text, ['positive', 'negative', 'neutral'])) {
            return $text;
        }

        // 防呆：如果 AI 還是囉唆回了一堆，找看看關鍵字
        if (strpos($text, 'positive') !== false) return 'positive';
        if (strpos($text, 'negative') !== false) return 'negative';
        
        return 'neutral';
    }
}
