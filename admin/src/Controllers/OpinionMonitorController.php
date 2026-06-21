<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\Candidate;
use App\Models\Opinion;
use App\Services\GeminiSentimentService;
use App\Services\OpinionCrawlerService;

class OpinionMonitorController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function dashboard(): void
    {
        $this->auth->requireAuth();
        
        $candidates = Candidate::all();
        
        // Find self candidate
        $selfCandidateId = null;
        $opponentCandidates = [];
        foreach ($candidates as $c) {
            if ($c['type'] === 'self') {
                $selfCandidateId = (int)$c['id'];
            } elseif ($c['type'] === 'main_opponent') {
                $opponentCandidates[] = $c;
            }
        }
        
        $selfStats = Opinion::getSentimentStats($selfCandidateId);
        
        // Fetch stats for opponents for chart
        $opponentStatsList = [];
        foreach ($opponentCandidates as $opp) {
            $opponentStatsList[] = [
                'name' => $opp['name'],
                'stats' => Opinion::getSentimentStats((int)$opp['id'])
            ];
        }

        // Fetch recent hot opinions (e.g., last 5 opinions)
        $recentOpinions = Opinion::search([], 5);

        $this->render('opinion/dashboard', [
            'title' => '輿情戰情室',
            'candidates' => $candidates,
            'selfStats' => $selfStats,
            'opponentStatsList' => $opponentStatsList,
            'recentOpinions' => $recentOpinions
        ]);
    }

    public function list(): void
    {
        $this->auth->requireAuth();
        
        $candidateId = isset($_GET['candidate_id']) ? (int)$_GET['candidate_id'] : null;
        $sentiment = $_GET['sentiment'] ?? null;
        $sourceType = $_GET['source_type'] ?? null;
        
        $candidates = Candidate::all();
        $opinions = Opinion::search([
            'candidate_id' => $candidateId,
            'sentiment' => $sentiment,
            'source_type' => $sourceType
        ], 50);

        $this->render('opinion/list', [
            'title' => '輿情資料庫',
            'candidates' => $candidates,
            'opinions' => $opinions,
            'filters' => [
                'candidate_id' => $candidateId,
                'sentiment' => $sentiment,
                'source_type' => $sourceType
            ]
        ]);
    }
    
    public function fetch(): void
    {
        $this->auth->requireAuth();
        
        try {
            $aiService = new GeminiSentimentService();
            $crawlerService = new OpinionCrawlerService($aiService);
            
            // Limit to 5 per candidate for manual fetch to avoid browser timeout
            $newRecordsCount = $crawlerService->runCrawler(5);
            
            $_SESSION['flash_message'] = "手動更新完成，共新增 {$newRecordsCount} 筆輿情紀錄。";
            $_SESSION['flash_type'] = "success";
        } catch (\Throwable $e) {
            $_SESSION['flash_message'] = "更新失敗: " . $e->getMessage();
            $_SESSION['flash_type'] = "error";
            error_log((string)$e);
        }
        
        $this->redirect('/admin/opinion/dashboard');
    }
}
