<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Models\Candidate;
use App\Models\CandidateKeyword;

class OpinionCrawlerService
{
    private GeminiSentimentService $ai;

    public function __construct(GeminiSentimentService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Run the Google News RSS crawler for all active candidates and their keywords.
     * @param int $limitPerCandidate Maximum number of new articles to process per candidate per run.
     */
    public function runCrawler(int $limitPerCandidate = 10): int
    {
        $candidates = Candidate::all();
        $pdo = Database::getInstance();
        $newRecordsCount = 0;

        foreach ($candidates as $candidate) {
            $keywords = CandidateKeyword::getByCandidate((int)$candidate['id']);
            
            // 如果沒有額外設定關鍵字，至少用本名去搜尋
            $searchTerms = [$candidate['name']];
            foreach ($keywords as $kw) {
                if ($kw['is_active']) {
                    $searchTerms[] = $kw['keyword'];
                }
            }

            // 整合搜尋字串 (例如："潘炩禕" OR "潘炩依")
            $queryParts = array_map(fn($t) => '"' . $t . '"', array_unique($searchTerms));
            $rawQuery = implode(' OR ', $queryParts) . ' AND "屏東"';
            $query = urlencode($rawQuery);
            
            $rssUrl = "https://news.google.com/rss/search?q={$query}&hl=zh-TW&gl=TW&ceid=TW:zh-Hant";
            
            // 加入 Context 設定以帶上 User-Agent，避免被 Google News 阻擋
            $options = [
                'http' => [
                    'method' => 'GET',
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n"
                ]
            ];
            $context = stream_context_create($options);

            $xmlString = @file_get_contents($rssUrl, false, $context);
            if (!$xmlString) {
                error_log("Failed to fetch RSS for candidate ID: " . $candidate['id']);
                continue;
            }

            $xml = @simplexml_load_string($xmlString);
            if (!$xml || !isset($xml->channel->item)) {
                continue;
            }

            $insertedForThisCandidate = 0;

            foreach ($xml->channel->item as $item) {
                if ($insertedForThisCandidate >= $limitPerCandidate) {
                    break;
                }
                
                $title = (string)$item->title;
                $link = (string)$item->link;
                $pubDate = date('Y-m-d H:i:s', strtotime((string)$item->pubDate));
                $sourceName = (string)($item->source ?? 'Google News');
                
                // 去除可能夾帶在 RSS 裡的 HTML tag 作為摘要
                $description = strip_tags((string)$item->description);
                $excerpt = mb_substr($description, 0, 300);

                // Check if this URL is already in our DB
                $stmt = $pdo->prepare('SELECT id FROM opinions WHERE url = ?');
                $stmt->execute([$link]);
                if ($stmt->fetch()) {
                    continue; // 已經抓過這篇了
                }

                // Call Gemini for Sentiment Analysis
                $sentiment = $this->ai->analyze($candidate['name'], $title, $excerpt);

                // Insert into DB
                $insertStmt = $pdo->prepare('
                    INSERT INTO opinions (candidate_id, source_type, source_name, title, url, content_excerpt, sentiment, published_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                
                $insertStmt->execute([
                    $candidate['id'],
                    'news',
                    $sourceName,
                    $title,
                    $link,
                    $excerpt,
                    $sentiment,
                    $pubDate
                ]);

                $newRecordsCount++;
                $insertedForThisCandidate++;
            }
        }

        return $newRecordsCount;
    }
}
