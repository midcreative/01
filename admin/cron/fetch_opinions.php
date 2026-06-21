<?php
/**
 * Cron Job: Fetch Public Opinions from Google News RSS.
 * Usage: php admin/cron/fetch_opinions.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Services\GeminiSentimentService;
use App\Services\OpinionCrawlerService;

// Ensure this script is only run from CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("This script can only be run from the command line.");
}

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

date_default_timezone_set('Asia/Taipei');

echo "[" . date('Y-m-d H:i:s') . "] Starting Opinion Crawler...\n";

try {
    // æ¸¬è©¦è³‡æ?åº«é€??
    Database::getInstance();
    
    $aiService = new GeminiSentimentService();
    $crawlerService = new OpinionCrawlerService($aiService);

    $newRecordsCount = 0;
    
    // ?ƒæ…®??AI API ?¯èƒ½??Rate Limitï¼Œå??œåœ¨ Crawler ?§éƒ¨æ²’æ? sleepï¼ŒåŸ·è¡Œæ??“å¯?½æ?å¾ˆå¿«
    // ?¥æ—¥å¾Œé??µå??–å€™é¸äººå?å¤šï??™è£¡?¯å?ä¸?try-catch ?…è£¹?®ä??™é¸äººï??¿å?ä¸€?¯å…¨??    
    // Run the crawler
    $newRecordsCount += $crawlerService->runCrawler();

    echo "[" . date('Y-m-d H:i:s') . "] Crawler finished. Inserted {$newRecordsCount} new records.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
