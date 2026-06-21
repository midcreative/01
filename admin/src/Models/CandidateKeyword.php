<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Represents a Keyword/Alias for a Candidate.
 */
final class CandidateKeyword
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getByCandidate(int $candidateId): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM candidate_keywords WHERE candidate_id = ? ORDER BY type, id ASC');
        $stmt->execute([$candidateId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            INSERT INTO candidate_keywords (candidate_id, keyword, type, is_active)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            (int)$data['candidate_id'],
            $data['keyword'],
            $data['type'] ?? 'alias',
            (int)($data['is_active'] ?? 1),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            UPDATE candidate_keywords
            SET keyword = ?, type = ?, is_active = ?
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['keyword'],
            $data['type'] ?? 'alias',
            (int)($data['is_active'] ?? 1),
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('DELETE FROM candidate_keywords WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
