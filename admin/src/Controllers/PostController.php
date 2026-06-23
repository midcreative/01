<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Layout\AdminLayout;
use App\Models\Post;
use App\Models\Category;
use App\Models\Town;

/**
 * Admin CRUD for 服務日記 (Posts).
 */
final class PostController extends BaseController
{
    public function __construct(private readonly Auth $auth) {}

    /** List all posts. */
    public function index(): void
    {
        $this->auth->requireAuth();
        $posts = Post::all();

        ob_start(); ?>
        <div class="flex items-center justify-between mb-6">
            <p class="text-slate-400 text-sm">共 <?= count($posts) ?> 篇</p>
            <a href="/admin/posts/create" class="px-5 py-2.5 bg-[#66C2A5] text-white rounded-2xl text-sm font-black hover:bg-[#57A891] transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i> 新增日記
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-50 overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-400 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="text-left px-6 py-4 font-black">標題</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">類別</th>
                        <th class="text-left px-4 py-4 font-black hidden md:table-cell">鄉鎮</th>
                        <th class="text-left px-4 py-4 font-black hidden lg:table-cell">發布日期</th>
                        <th class="px-4 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($posts as $post): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 max-w-xs truncate"><?= htmlspecialchars($post['title']) ?></div>
                            <?php if (!$post['is_published']): ?>
                            <span class="text-[10px] font-black text-slate-400 uppercase">草稿</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell"><?= htmlspecialchars((string)($post['category_name'] ?? '未分類')) ?></td>
                        <td class="px-4 py-4 text-slate-500 hidden md:table-cell"><?= htmlspecialchars((string)($post['town_name'] ?? '未指定')) ?></td>
                        <td class="px-4 py-4 text-slate-400 hidden lg:table-cell"><?= htmlspecialchars((string)($post['published_at'] ?? '—')) ?></td>
                        <td class="px-4 py-4 flex items-center gap-2 justify-end">
                            <a href="/admin/posts/<?= $post['id'] ?>/edit" class="p-2 text-slate-400 hover:text-[#66C2A5] transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="/admin/posts/<?= $post['id'] ?>/delete" onsubmit="return confirm('確定刪除此篇日記？')">
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($posts)): ?>
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-300 text-sm">尚無服務日記，點擊右上角新增</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
        AdminLayout::render('服務日記管理', ob_get_clean() ?: '', 'posts');
    }

    /** Show create form. */
    public function create(): void
    {
        $this->auth->requireAuth();
        ob_start();
        $this->renderForm(null);
        AdminLayout::render('新增服務日記', ob_get_clean() ?: '', 'posts');
    }

    /** Handle POST store. */
    public function store(): void
    {
        $this->auth->requireAuth();
        $data           = $this->buildDataFromPost();
        $data['slug']   = Post::generateSlug($data['title']) . '-' . time();
        Post::create($data);
        $this->redirect('/admin/posts');
    }

    /** Show edit form. */
    public function edit(int $id): void
    {
        $this->auth->requireAuth();
        $post = Post::findById($id) ?: $this->redirect('/admin/posts');
        ob_start();
        $this->renderForm($post);
        AdminLayout::render('編輯服務日記', ob_get_clean() ?: '', 'posts');
    }

    public function update(int $id): void
    {
        $this->auth->requireAuth();
        $existingPost = Post::findById($id);
        
        $data = $this->buildDataFromPost($existingPost ?: null);
        
        if ($existingPost && !empty($existingPost['slug'])) {
            $data['slug'] = $existingPost['slug'];
        } else {
            $data['slug'] = Post::generateSlug($data['title']) . '-' . time();
        }
        
        Post::update($id, $data);
        $this->redirect('/admin/posts');
    }

    /** Handle POST delete. */
    public function destroy(int $id): void
    {
        $this->auth->requireAuth();
        Post::delete($id);
        $this->redirect('/admin/posts');
    }

    /** Build post data array from $_POST and handle file upload. */
    private function buildDataFromPost(?array $existingPost = null): array
    {
        $coverImage = $existingPost['cover_image'] ?? '';

        // Handle File Upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['cover_image']['tmp_name'];
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES['cover_image']['name']));
            
            // The directory relative to document root
            $uploadDirRel = '/uploads/posts/';
            
            // The absolute path on disk where we save it. 
            // Depending on the exact web root layout, index.php is at 01web/admin/index.php
            // So __DIR__ is 01web/admin/src/Controllers.
            // Absolute path to 01web/uploads/posts
            $uploadDirAbs = dirname(__DIR__, 3) . $uploadDirRel;
            
            if (!is_dir($uploadDirAbs)) {
                mkdir($uploadDirAbs, 0755, true);
            }
            
            $targetPath = $uploadDirAbs . $fileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $coverImage = $uploadDirRel . $fileName;
            }
        }

        return [
            'title'        => $this->postString('title'),
            'category_id'  => (int)($_POST['category_id'] ?? 0),
            'town_id'      => (int)($_POST['town_id'] ?? 0),
            'excerpt'      => $this->postString('excerpt'),
            'content'      => $_POST['content'] ?? '',
            'cover_image'  => $coverImage,
            'published_at' => $this->postString('published_at', date('Y-m-d')),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];
    }

    /** Render shared create/edit form. */
    private function renderForm(?array $post): void
    {
        $isEdit = $post !== null;
        $categories = Category::all();
        $towns      = Town::all();
        $action     = $isEdit ? "/admin/posts/{$post['id']}" : '/admin/posts/store';
        ?>
        <!-- Quill Editor CSS -->
        <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet">

        <form method="POST" action="<?= $action ?>" enctype="multipart/form-data" class="space-y-6 max-w-4xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">標題 *</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white focus:outline-none focus:border-[#66C2A5] focus:ring-2 focus:ring-[#66C2A5]/20 text-slate-800 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">服務主軸</label>
                    <select name="category_id" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                        <option value="0">請選擇...</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($post['category_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">鄉鎮</label>
                    <select name="town_id" class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                        <option value="0">請選擇...</option>
                        <?php foreach ($towns as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($post['town_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">發布日期</label>
                    <input type="date" name="published_at" value="<?= htmlspecialchars($post['published_at'] ?? date('Y-m-d')) ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">摘要（SEO meta description）</label>
                    <textarea name="excerpt" rows="2" maxlength="160"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 font-medium focus:outline-none focus:border-[#66C2A5] resize-none"
                              placeholder="50-160 字，AI 引用時的主要依據"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">封面圖片檔案上傳</label>
                    <?php if (!empty($post['cover_image'])): ?>
                    <div class="mb-3">
                        <img src="<?= htmlspecialchars($post['cover_image']) ?>" alt="目前封面" class="h-24 object-cover rounded-xl border border-slate-200">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/*"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-100 bg-white text-slate-800 focus:outline-none focus:border-[#66C2A5] file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-[#E0F2ED] file:text-[#4A937F] hover:file:bg-[#66C2A5] hover:file:text-white transition-all">
                    <p class="text-[10px] text-slate-400 mt-2">支援 JPG, PNG, GIF。若不更改請保持空白。</p>
                </div>
            </div>

            <!-- Quill Editor -->
            <div>
                <label class="block text-xs font-black text-slate-500 uppercase tracking-widest mb-2">內文</label>
                <div id="quill-editor" class="bg-white rounded-2xl border border-slate-100 min-h-[300px]"><?= $post['content'] ?? '' ?></div>
                <input type="hidden" name="content" id="quill-content">
            </div>

            <!-- Publish toggle -->
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_published" value="1" <?= ($post['is_published'] ?? 0) ? 'checked' : '' ?>
                       class="w-5 h-5 rounded accent-[#66C2A5]">
                <span class="font-bold text-slate-700">立即發布（取消勾選為草稿）</span>
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="px-8 py-3 bg-[#66C2A5] text-white rounded-2xl font-black text-sm hover:bg-[#57A891] transition-all">
                    <?= $isEdit ? '儲存變更' : '新增日記' ?>
                </button>
                <a href="/admin/posts" class="px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl font-bold text-sm hover:bg-slate-200 transition-all">取消</a>
            </div>
        </form>

        <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js"></script>
        <script>
            const quill = new Quill('#quill-editor', { theme: 'snow', modules: { toolbar: [
                ['bold','italic','underline'],
                [{ header: [2, 3, false] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'], ['clean']
            ]}});
            document.querySelector('form').addEventListener('submit', () => {
                document.getElementById('quill-content').value = quill.root.innerHTML;
            });
        </script>
        <?php
    }
}
