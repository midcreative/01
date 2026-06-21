<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;
use App\Layout\AdminLayout;

/**
 * Admin: manage volunteer job listings and applications.
 */
final class VolunteerController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $jobs = $pdo->query(
            'SELECT vj.*, (SELECT COUNT(*) FROM volunteer_applications va WHERE va.job_id = vj.id) AS app_count
             FROM volunteer_jobs vj ORDER BY vj.created_at DESC'
        )->fetchAll();

        ob_start(); ?>
        <div class="flex items-center justify-between mb-6">
            <p class="text-slate-400 text-sm">共 <?= count($jobs) ?> 個職缺</p>
            <a href="/admin/volunteers/create" class="px-5 py-2.5 bg-[#66C2A5] text-white rounded-2xl text-sm font-black hover:bg-[#57A891] transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> 新增職缺
            </a>
        </div>
        <div class="bg-white rounded-3xl border border-slate-50 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-6 py-4 font-black">職缺名稱</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">地區</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">截止日</th>
                        <th class="text-left px-4 py-4 font-black">報名數</th>
                        <th class="text-left px-4 py-4 font-black">狀態</th>
                        <th class="px-4 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($jobs as $job): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($job['title']) ?></td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell"><?= htmlspecialchars($job['town'] ?? '—') ?></td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell"><?= htmlspecialchars($job['deadline'] ?? '無限制') ?></td>
                        <td class="px-4 py-4">
                            <a href="/admin/volunteers/<?= $job['id'] ?>/apps" class="font-black text-[#66C2A5] hover:underline">
                                <?= (int)$job['app_count'] ?> 人
                            </a>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black <?= $job['is_active'] ? 'bg-[#E0F2ED] text-[#2D7A60]' : 'bg-slate-100 text-slate-400' ?>">
                                <?= $job['is_active'] ? '招募中' : '已關閉' ?>
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <a href="/admin/volunteers/<?= $job['id'] ?>/edit" class="p-2 text-slate-400 hover:text-[#66C2A5] transition-colors inline-block">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        AdminLayout::render('志工招募管理', ob_get_clean() ?: '', 'volunteer');
    }

    public function create(): void
    {
        $this->auth->requireAuth();
        ob_start();
        $this->renderForm(null);
        AdminLayout::render('新增志工職缺', ob_get_clean() ?: '', 'volunteer');
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO volunteer_jobs (title, description, town, required_num, deadline, is_active)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $this->postString('title'),
            $this->postString('description'),
            $this->postString('town', '全部地區'),
            $this->postInt('required_num', 1),
            $this->postString('deadline') ?: null,
            isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->redirect('/admin/volunteers');
    }

    public function edit(int $id): void
    {
        $this->auth->requireAuth();
        $pdo = Database::getInstance();
        $job = $pdo->prepare('SELECT * FROM volunteer_jobs WHERE id = ?');
        $job->execute([$id]);
        $job = $job->fetch() ?: $this->redirect('/admin/volunteers');
        ob_start();
        $this->renderForm($job);
        AdminLayout::render('編輯志工職缺', ob_get_clean() ?: '', 'volunteer');
    }

    public function update(int $id): void
    {
        $this->auth->requireAuth();
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE volunteer_jobs SET title=?, description=?, town=?, required_num=?, deadline=?, is_active=? WHERE id=?'
        );
        $stmt->execute([
            $this->postString('title'),
            $this->postString('description'),
            $this->postString('town', '全部地區'),
            $this->postInt('required_num', 1),
            $this->postString('deadline') ?: null,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);
        $this->redirect('/admin/volunteers');
    }

    public function applications(int $jobId): void
    {
        $this->auth->requireAuth();
        $pdo   = Database::getInstance();
        $job   = $pdo->prepare('SELECT * FROM volunteer_jobs WHERE id = ?');
        $job->execute([$jobId]);
        $job   = $job->fetch();
        $apps  = $pdo->prepare('SELECT * FROM volunteer_applications WHERE job_id = ? ORDER BY applied_at DESC');
        $apps->execute([$jobId]);
        $apps  = $apps->fetchAll();

        ob_start(); ?>
        <div class="mb-6">
            <h2 class="font-black text-slate-700">「<?= htmlspecialchars($job['title'] ?? '') ?>」報名名單</h2>
            <p class="text-sm text-slate-400 mt-1">共 <?= count($apps) ?> 人報名</p>
        </div>
        <div class="bg-white rounded-3xl border border-slate-50 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-6 py-4 font-black">姓名</th>
                        <th class="text-left px-4 py-4 font-black">電話</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">Email</th>
                        <th class="text-left px-4 py-4 font-black">狀態</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">報名時間</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($apps as $app): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($app['name']) ?></td>
                        <td class="px-4 py-4 text-slate-500"><?= htmlspecialchars($app['phone']) ?></td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell"><?= htmlspecialchars($app['email'] ?? '—') ?></td>
                        <td class="px-4 py-4">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black
                                <?= $app['status'] === '已接受' ? 'bg-[#E0F2ED] text-[#2D7A60]' : ($app['status'] === '已婉謝' ? 'bg-red-50 text-red-500' : 'bg-orange-50 text-orange-600') ?>">
                                <?= htmlspecialchars($app['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-400 hidden md:table-cell"><?= htmlspecialchars($app['applied_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
        AdminLayout::render('志工報名名單', ob_get_clean() ?: '', 'volunteer');
    }

    private function renderForm(?array $job): void
    {
        $towns = ['全部地區', '潮州鎮', '新埤鄉', '內埔鄉', '萬巒鄉', '竹田鄉', '枋寮鄉'];
        $action = $job ? "/admin/volunteers/{$job['id']}" : '/admin/volunteers/store';
        ?>
        <form method="POST" action="<?= $action ?>" class="space-y-5 max-w-2xl">
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">職缺名稱 *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($job['title'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 font-medium focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">地區</label>
                    <select name="town" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                        <?php foreach ($towns as $t): ?>
                        <option value="<?= $t ?>" <?= ($job['town'] ?? '全部地區') === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">招募人數</label>
                    <input type="number" name="required_num" min="1" value="<?= (int)($job['required_num'] ?? 1) ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                </div>
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">截止日期（留空表示不限）</label>
                <input type="date" name="deadline" value="<?= htmlspecialchars($job['deadline'] ?? '') ?>"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
            </div>
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">職缺說明</label>
                <textarea name="description" rows="4"
                          class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5] resize-none"><?= htmlspecialchars($job['description'] ?? '') ?></textarea>
            </div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?= ($job['is_active'] ?? 1) ? 'checked' : '' ?> class="w-5 h-5 accent-[#66C2A5]">
                <span class="font-bold text-slate-700">開放招募中</span>
            </label>
            <div class="flex gap-3">
                <button type="submit" class="px-8 py-3 bg-[#66C2A5] text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">
                    <?= $job ? '儲存變更' : '新增職缺' ?>
                </button>
                <a href="/admin/volunteers" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all">取消</a>
            </div>
        </form>
        <?php
    }
}
