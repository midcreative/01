<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Represents a post category (服務主軸).
 */
final class Category
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('SELECT * FROM post_categories ORDER BY sort_order ASC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM post_categories WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            INSERT INTO post_categories (name, color_theme, sort_order)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            $data['name'],
            $data['color_theme'] ?? 'bg-slate-50 text-slate-600 border-slate-100',
            (int)($data['sort_order'] ?? 0),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            UPDATE post_categories
            SET name = ?, color_theme = ?, sort_order = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['name'],
            $data['color_theme'] ?? 'bg-slate-50 text-slate-600 border-slate-100',
            (int)($data['sort_order'] ?? 0),
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM post_categories WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
