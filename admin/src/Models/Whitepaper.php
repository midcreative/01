<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;

/**
 * Whitepaper model — handles DB operations for whitepaper_pillars table.
 */
final class Whitepaper
{
    /** Fetch all active pillars for the front-end, sorted by sort_order. */
    public static function allActive(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('SELECT * FROM whitepaper_pillars WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll();
    }

    /** Fetch all pillars for the admin panel. */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('SELECT * FROM whitepaper_pillars ORDER BY sort_order ASC')->fetchAll();
    }

    /** Find a pillar by ID. */
    public static function findById(int $id): array|false
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM whitepaper_pillars WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Create a new pillar. Returns new ID. */
    public static function create(array $data): int
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'INSERT INTO whitepaper_pillars (title, subtitle, category_tag, icon_name, theme_color, description, bullet_points, sort_order, is_active)
             VALUES (:title, :subtitle, :category_tag, :icon_name, :theme_color, :description, :bullet_points, :sort_order, :is_active)'
        );
        $stmt->execute(self::sanitize($data));
        return (int) $pdo->lastInsertId();
    }

    /** Update an existing pillar. */
    public static function update(int $id, array $data): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare(
            'UPDATE whitepaper_pillars SET title=:title, subtitle=:subtitle, category_tag=:category_tag,
             icon_name=:icon_name, theme_color=:theme_color, description=:description,
             bullet_points=:bullet_points, sort_order=:sort_order, is_active=:is_active
             WHERE id = :id'
        );
        $stmt->execute(array_merge(self::sanitize($data), ['id' => $id]));
    }

    /** Delete a pillar. */
    public static function delete(int $id): void
    {
        $pdo  = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM whitepaper_pillars WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** Reorder pillars. 
     * @param array<int> $ids Array of pillar IDs in the desired order.
     */
    public static function reorder(array $ids): void
    {
        $pdo = Database::getInstance();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE whitepaper_pillars SET sort_order = ? WHERE id = ?');
            foreach ($ids as $index => $id) {
                // sort_order is 1-indexed based on array position
                $stmt->execute([$index + 1, (int)$id]);
            }
            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** @return array<string,mixed> */
    private static function sanitize(array $data): array
    {
        return [
            'title'         => trim((string) ($data['title'] ?? '')),
            'subtitle'      => trim((string) ($data['subtitle'] ?? '')),
            'category_tag'  => trim((string) ($data['category_tag'] ?? '')),
            'icon_name'     => trim((string) ($data['icon_name'] ?? 'layer')),
            'theme_color'   => trim((string) ($data['theme_color'] ?? 'brand-green')),
            'description'   => trim((string) ($data['description'] ?? '')),
            'bullet_points' => trim((string) ($data['bullet_points'] ?? '')), // Expected to be JSON encoded string or multiline text
            'sort_order'    => (int) ($data['sort_order'] ?? 0),
            'is_active'     => (int) ($data['is_active'] ?? 1),
        ];
    }
}
