<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Database;
use App\Services\GeminiSentimentService;

// Ensure this script is only run via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Basic authentication (Simple token check)
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$expectedToken = 'Bearer ' . md5($_ENV['DB_PASS'] ?? 'default_secret'); // Using DB_PASS as a simple pre-shared secret for this script

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// We need to re-calculate expected token after loading env if we use env variables
$expectedToken = 'Bearer ' . md5($_ENV['DB_PASS'] ?? 'xin_robot_secret_2026');

if ($authHeader !== $expectedToken) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!isset($input['opinions']) || !is_array($input['opinions'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid data format']);
    exit;
}

date_default_timezone_set('Asia/Taipei');

try {
    $pdo = Database::getInstance();
    $aiService = new GeminiSentimentService();

    $newRecordsCount = 0;

    foreach ($input['opinions'] as $item) {
        // Validate required fields
        if (empty($item['candidate_id']) || empty($item['url']) || empty($item['title'])) {
            continue;
        }

        // Check if this URL is already in our DB
        $stmt = $pdo->prepare('SELECT id FROM opinions WHERE url = ?');
        $stmt->execute([$item['url']]);
        if ($stmt->fetch()) {
            continue; // Already processed
        }

        $candidateId = (int)$item['candidate_id'];
        $title = $item['title'];
        $link = $item['url'];
        $pubDate = $item['published_at'] ?? date('Y-m-d H:i:s');
        $sourceName = $item['source_name'] ?? 'Google News';
        $excerpt = mb_substr(strip_tags($item['description'] ?? ''), 0, 300);
        $candidateName = $item['candidate_name'] ?? '潘炩�?';

        // Call Gemini for Sentiment Analysis on the server
        $sentiment = $aiService->analyze($candidateName, $title, $excerpt);

        // Insert into DB
        $insertStmt = $pdo->prepare('
            INSERT INTO opinions (candidate_id, source_type, source_name, title, url, content_excerpt, sentiment, published_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ')';
        
        $insertStmt->execute([
            $candidateId,
            'news',
            $sourceName,
            $title,
            $link,
            $excerpt,
            $sentiment,
            $pubDate
        ]);

        $newRecordsCount++;
    }

    echo json_encode([
        'status' => 'success',
        'inserted' => $newRecordsCount
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => $e->getMessage()
    ]);
}