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
        $this->requirePost();
        
        $name = trim($_POST['name'] ?? '');
        $party = trim($_POST['party'] ?? '');
        $type = $_POST['type'] ?? 'other';
        
        if ($name === '') {
            $this->redirect('/admin/candidates', 'error', '候選人姓名不得為空');
        }

        Candidate::create([
            'name' => $name,
            'party' => $party,
            'type' => $type
        ]);

        $this->redirect('/admin/candidates', 'success', '候選人建立成功');
    }

    public function update(array $params): void
    {
        $this->auth->requireAuth();
        $this->requirePost();
        
        $id = (int)$params['id'];
        $name = trim($_POST['name'] ?? '');
        $party = trim($_POST['party'] ?? '');
        $type = $_POST['type'] ?? 'other';
        
        if ($name === '') {
            $this->redirect('/admin/candidates', 'error', '候選人姓名不得為空');
        }

        Candidate::update($id, [
            'name' => $name,
            'party' => $party,
            'type' => $type
        ]);

        $this->redirect('/admin/candidates', 'success', '候選人更新成功');
    }

    public function delete(array $params): void
    {
        $this->auth->requireAuth();
        $this->requirePost();
        
        $id = (int)$params['id'];
        Candidate::delete($id);

        $this->redirect('/admin/candidates', 'success', '候選人已刪除');
    }

    // --- Keywords ---
    
    public function storeKeyword(): void
    {
        $this->auth->requireAuth();
        $this->requirePost();
        
        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        $keyword = trim($_POST['keyword'] ?? '');
        $type = $_POST['type'] ?? 'alias';
        
        if ($candidateId === 0 || $keyword === '') {
            $this->redirect('/admin/candidates', 'error', '資料不完整');
        }

        CandidateKeyword::create([
            'candidate_id' => $candidateId,
            'keyword' => $keyword,
            'type' => $type,
            'is_active' => 1
        ]);

        $this->redirect('/admin/candidates', 'success', '關鍵字新增成功');
    }

    public function deleteKeyword(array $params): void
    {
        $this->auth->requireAuth();
        $this->requirePost();
        
        $id = (int)$params['id'];
        CandidateKeyword::delete($id);

        $this->redirect('/admin/candidates', 'success', '關鍵字已刪除');
    }
}
