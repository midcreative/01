<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Represents a Candidate for Public Opinion Monitoring (舆情监测).
 */
final class Candidate
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query('SELECT * FROM candidates ORDER BY FIELD(type, "self", "main_opponent", "other"), id ASC')->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(int $id): ?array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM candidates WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            INSERT INTO candidates (name, party, type)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            $data['name'],
            $data['party'] ?? null,
            $data['type'] ?? 'other',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            UPDATE candidates
            SET name = ?, party = ?, type = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['name'],
            $data['party'] ?? null,
            $data['type'] ?? 'other',
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM candidates WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
