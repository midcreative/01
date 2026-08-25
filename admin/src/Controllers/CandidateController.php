<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Auth;
use App\Models\Candidate;
use App\Models\CandidateKeyword;

class CandidateController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        
        $candidates = Candidate::all();
        
        // 為了列表顯示，預先載入各自的關鍵字
        foreach ($candidates as &$candidate) {
            $candidate['keywords'] = CandidateKeyword::getByCandidate((int)$candidate['id']);
        }
        
        $this->render('opinion/candidates', [
            'title' => '候選人與追蹤關鍵字管理',
            'candidates' => $candidates
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        
        $name = trim((string)($_POST['name'] ?? ''));
        $party = trim((string)($_POST['party'] ?? ''));
        $type = (string)($_POST['type'] ?? 'other');
        
        if ($name === '') {
            $this->redirect('/admin/candidates');
        }

        Candidate::create([
            'name' => $name,
            'party' => $party,
            'type' => $type
        ]);

        $this->redirect('/admin/candidates');
    }

    public function update(int $id): void
    {
        $this->auth->requireAuth();
        
        $name = trim((string)($_POST['name'] ?? ''));
        $party = trim((string)($_POST['party'] ?? ''));
        $type = (string)($_POST['type'] ?? 'other');
        
        if ($name === '') {
            $this->redirect('/admin/candidates');
        }

        Candidate::update($id, [
            'name' => $name,
            'party' => $party,
            'type' => $type
        ]);

        $this->redirect('/admin/candidates');
    }

    public function delete(int $id): void
    {
        $this->auth->requireAuth();
        
        Candidate::delete($id);

        $this->redirect('/admin/candidates');
    }

    // --- Keywords ---
    
    public function storeKeyword(): void
    {
        $this->auth->requireAuth();
        
        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        $keyword = trim((string)($_POST['keyword'] ?? ''));
        $type = (string)($_POST['type'] ?? 'alias');
        
        if ($candidateId === 0 || $keyword === '') {
            $this->redirect('/admin/candidates');
        }

        CandidateKeyword::create([
            'candidate_id' => $candidateId,
            'keyword' => $keyword,
            'type' => $type,
            'is_active' => 1
        ]);

        $this->redirect('/admin/candidates');
    }

    public function deleteKeyword(int $id): void
    {
        $this->auth->requireAuth();
        
        CandidateKeyword::delete($id);

        $this->redirect('/admin/candidates');
    }
}
