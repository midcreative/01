<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Config\Database;
use App\Layout\AdminLayout;

/**
 * Admin: manage homepage stats dashboard numbers.
 */
final class StatsController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    public function index(): void
    {
        $this->auth->requireAuth();
        $pdo   = Database::getInstance();
        $stats = $pdo->query('SELECT * FROM stats ORDER BY sort_order ASC')->fetchAll();

        ob_start(); ?>
        <form method="POST" action="/admin/stats/update" class="space-y-4 max-w-2xl">
            <p class="text-sm text-slate-400 mb-6">修改以下數字後，儲存將立即更新首頁數據看板。</p>

            <div class="bg-white rounded-3xl border border-slate-50 overflow-hidden shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                        <tr>
                            <th class="text-left px-6 py-4 font-black">指標名稱</th>
                            <th class="text-left px-4 py-4 font-black w-32">數值</th>
                            <th class="text-left px-4 py-4 font-black w-20">單位</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php foreach ($stats as $s): ?>
                        <tr>
                            <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($s['stat_label']) ?></td>
                            <td class="px-4 py-3">
                                <input type="number" name="values[<?= $s['stat_key'] ?>]" value="<?= (int)$s['stat_value'] ?>" min="0"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-100 bg-slate-50 text-slate-800 font-bold focus:outline-none focus:border-[#66C2A5] text-center">
                            </td>
                            <td class="px-4 py-4 text-slate-400"><?= htmlspecialchars($s['stat_unit']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="px-8 py-3 bg-[#66C2A5] text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">
                儲存數據
            </button>
        </form>
        <?php
        AdminLayout::render('數據看板管理', ob_get_clean() ?: '', 'stats');
    }

    public function update(): void
    {
        $this->auth->requireAuth();
        $pdo    = Database::getInstance();
        $values = $_POST['values'] ?? [];

        $stmt = $pdo->prepare('UPDATE stats SET stat_value = ? WHERE stat_key = ?');
        foreach ($values as $key => $value) {
            $stmt->execute([(int)$value, (string)$key]);
        }
        $this->redirect('/admin/stats');
    }
}
