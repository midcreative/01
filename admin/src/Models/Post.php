<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Post (服務日記) model — handles all DB operations for posts table.
 */
class Post
{
    /** Fetch all published posts, newest first. */
    public static function allPublished(?int $townId = null, ?int $categoryId = null): array
    {
        $pdo  = Database::getInstance();
        $sql  = '
            SELECT p.id, p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
                   c.name AS category_name, c.color_theme AS category_color,
                   t.name AS town_name
            FROM posts p
            LEFT JOIN post_categories c ON p.category_id = c.id
            LEFT JOIN post_towns t ON p.town_id = t.id
            WHERE p.is_published = 1
        ';
        $params = [];

        if ($townId) {
            $sql .= ' AND p.town_id = ?';
            $params[] = $townId;
        }
        if ($categoryId) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY p.published_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Fetch all posts for admin list (published + drafts). */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('
            SELECT p.id, p.title, p.slug, p.is_published, p.published_at, p.created_at,
                   c.name AS category_name, t.name AS town_name
            FROM posts p
            LEFT JOIN post_categories c ON p.category_id = c.id
            LEFT JOIN post_towns t ON p.town_id = t.id
            ORDER BY p.created_at DESC
        ')->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Find a single post by its slug (for front-end detail page). */
    public static function findBySlug(string $slug): array|false
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT p.*, c.name AS category_name, c.color_theme AS category_color, t.name AS town_name
            FROM posts p
            LEFT JOIN post_categories c ON p.category_id = c.id
            LEFT JOIN post_towns t ON p.town_id = t.id
            WHERE p.slug = ? AND p.is_published = 1 LIMIT 1
        ');
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Find a post by ID (admin edit). */
    public static function findById(int $id): array|false
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Insert a new post. Returns the new ID. */
    public static function create(array $data): int
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('
            INSERT INTO posts (title, slug, category_id, town_id, excerpt, content, cover_image, published_at, is_published)
            VALUES (:title, :slug, :category_id, :town_id, :excerpt, :content, :cover_image, :published_at, :is_published)
        ');
        $stmt->execute(self::sanitize($data));
        return (int) $pdo->lastInsertId();
    }

    /** Update an existing post by ID. */
    public static function update(int $id, array $data): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('
            UPDATE posts SET title=:title, slug=:slug, category_id=:category_id, town_id=:town_id,
            excerpt=:excerpt, content=:content, cover_image=:cover_image, published_at=:published_at, is_published=:is_published
            WHERE id = :id
        ');
        $stmt->execute(array_merge(self::sanitize($data), ['id' => $id]));
    }

    /** Delete a post by ID. */
    public static function delete(int $id): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Generate a URL-safe slug from a title. */
    public static function generateSlug(string $title): string
    {
        $slug = preg_replace('/[^a-z0-9\-]/i', '-', $title) ?? '';
        $slug = strtolower(trim(preg_replace('/-+/', '-', $slug) ?? '', '-'));
        return $slug ?: 'post-' . time();
    }

    /** @return array<string,mixed> */
    private static function sanitize(array $data): array
    {
        return [
            'title'        => trim((string) ($data['title']        ?? '')),
            'slug'         => trim((string) ($data['slug']         ?? '')),
            'category_id'  => (int) ($data['category_id']          ?? 0) ?: null,
            'town_id'      => (int) ($data['town_id']              ?? 0) ?: null,
            'excerpt'      => trim((string) ($data['excerpt']      ?? '')),
            'content'      => (string) ($data['content']           ?? ''),
            'cover_image'  => trim((string) ($data['cover_image']  ?? '')),
            'published_at' => (string) ($data['published_at']      ?? date('Y-m-d')),
            'is_published' => (int) ($data['is_published']         ?? 0),
        ];
    }
}
