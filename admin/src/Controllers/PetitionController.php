<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;

/**
 * Admin: manage citizen petitions (連署提案).
 */
final class PetitionController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        $pdo       = Database::getInstance();
        $petitions = $pdo->query('SELECT * FROM petitions ORDER BY created_at DESC')->fetchAll();

        $this->render('petitions/index', [
            'petitions' => $petitions,
        ]);
    }

    public function create(): void
    {
        $this->auth->requireAuth();
        
        $this->render('petitions/form');
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        
        $title       = trim($this->postString('title'));
        $description = trim($this->postString('description'));
        $town        = trim($this->postString('town'));
        $status      = trim($this->postString('status'));
        $targetCount = max(1, (int)$this->postString('target_count', '100'));

        if ($title === '') {
            $this->redirect('/admin/petitions/create');
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO petitions (title, description, town, status, target_count, current_count) VALUES (?, ?, ?, ?, ?, 0)');
        $stmt->execute([$title, $description, $town, $status, $targetCount]);

        $this->redirect('/admin/petitions');
    }

    public function edit(int $id): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM petitions WHERE id = ?');
        $stmt->execute([$id]);
        $petition = $stmt->fetch();

        if (!$petition) {
            $this->redirect('/admin/petitions');
        }

        $this->render('petitions/form', [
            'petition' => $petition,
        ]);
    }

    public function update(int $id): void
    {
        $this->auth->requireAuth();
        
        $title       = trim($this->postString('title'));
        $description = trim($this->postString('description'));
        $town        = trim($this->postString('town'));
        $status      = trim($this->postString('status'));
        $targetCount = max(1, (int)$this->postString('target_count', '100'));

        if ($title === '') {
            $this->redirect("/admin/petitions/{$id}/edit");
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE petitions SET title = ?, description = ?, town = ?, status = ?, target_count = ? WHERE id = ?');
        $stmt->execute([$title, $description, $town, $status, $targetCount, $id]);

        $this->redirect('/admin/petitions');
    }

    public function destroy(int $id): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM petitions WHERE id = ?');
        $stmt->execute([$id]);
        $this->redirect('/admin/petitions');
    }

    public function show(int $id): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM petitions WHERE id = ?');
        $stmt->execute([$id]);
        $petition = $stmt->fetch();
        
        if (!$petition) {
            $this->redirect('/admin/petitions');
        }

        // 撈取連署名單
        $sigStmt = $pdo->prepare('SELECT line_user_id, line_display_name, line_picture_url, town, created_at FROM petition_signatures WHERE petition_id = ? ORDER BY created_at DESC');
        $sigStmt->execute([$id]);
        $signatures = $sigStmt->fetchAll();

        $this->render('petitions/show', [
            'petition'   => $petition,
            'signatures' => $signatures,
        ]);
    }

    public function updateStatus(int $id): void
    {
        $this->auth->requireAuth();
        $allowed = ['審核中', '公開連署', '已達標', '已列管'];
        $status  = $this->postString('status');

        if (!in_array($status, $allowed, true)) {
            $this->redirect('/admin/petitions');
        }

        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE petitions SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        $this->redirect('/admin/petitions');
    }
}
