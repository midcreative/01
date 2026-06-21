<?php

declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * Represents a crawled Public Opinion (Mentions).
 */
final class Opinion
{
    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public static function search(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $pdo = Database::getInstance();
        $query = 'SELECT o.*, c.name as candidate_name FROM opinions o LEFT JOIN candidates c ON o.candidate_id = c.id WHERE 1=1';
        $params = [];

        if (!empty($filters['candidate_id'])) {
            $query .= ' AND o.candidate_id = ?';
            $params[] = $filters['candidate_id'];
        }
        
        if (!empty($filters['sentiment'])) {
            $query .= ' AND o.sentiment = ?';
            $params[] = $filters['sentiment'];
        }

        if (!empty($filters['source_type'])) {
            $query .= ' AND o.source_type = ?';
            $params[] = $filters['source_type'];
        }

        $query .= ' ORDER BY o.published_at DESC LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSentimentStats(?int $candidateId = null, int $days = 7): array
    {
         $pdo = Database::getInstance();
         $query = '
            SELECT sentiment, COUNT(*) as count 
            FROM opinions 
            WHERE published_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
         ';
         $params = [$days];

         if ($candidateId !== null) {
             $query .= ' AND candidate_id = ?';
             $params[] = $candidateId;
         }

         $query .= ' GROUP BY sentiment';

         $stmt = $pdo->prepare($query);
         $stmt->execute($params);
         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $stats = ['positive' => 0, 'neutral' => 0, 'negative' => 0];
         foreach ($results as $row) {
             $stats[$row['sentiment']] = (int)$row['count'];
         }
         return $stats;
    }
}
