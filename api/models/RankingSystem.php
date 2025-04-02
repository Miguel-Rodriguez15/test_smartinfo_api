<?php
require_once __DIR__ . '/Database.php';

class RankingSystem {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
public function initializeRankingForPlayer(int $playerId): bool {
    $stmt = $this->db->prepare(
        "INSERT INTO rankings (player_id, victories, moves_record, last_victory)
         VALUES (:player_id, 0, NULL, NULL)
         ON CONFLICT (player_id) DO NOTHING"
    );
    return $stmt->execute([':player_id' => $playerId]);
}

    public function getTopPlayers(int $limit = 10, string $timeframe = 'all'): array {
        $timeframeConditions = [
            'all' => '1=1',
            'month' => "r.last_victory >= NOW() - INTERVAL '1 month'",
            'week' => "r.last_victory >= NOW() - INTERVAL '1 week'"
        ];
        
        $timeframeCondition = $timeframeConditions[$timeframe] ?? $timeframeConditions['all'];

        $query = "
            SELECT 
                p.player_id,
                p.username,
                r.victories,
                r.moves_record AS best_score,
                r.last_victory,
                (
                    SELECT COUNT(*) 
                    FROM games g 
                    WHERE g.player_id = p.player_id
                ) AS games_played,
                (
                    SELECT COUNT(*) 
                    FROM games g 
                    WHERE g.player_id = p.player_id AND g.is_completed = true
                ) AS games_won
            FROM 
                players p
            JOIN 
                rankings r ON p.player_id = r.player_id
            WHERE 
                r.victories > 0 AND
                {$timeframeCondition}
            ORDER BY 
                r.victories DESC,
                r.moves_record ASC,
                r.last_victory DESC
            LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function updatePlayerRanking(int $playerId, int $movesCount): bool {
        $this->db->beginTransaction();
        
        try {
            $checkStmt = $this->db->prepare(
                "SELECT 1 FROM rankings WHERE player_id = :player_id"
            );
            $checkStmt->execute([':player_id' => $playerId]);
            
            if ($checkStmt->fetch()) {
                $query = "
                    UPDATE rankings 
                    SET 
                        victories = victories + 1,
                        moves_record = LEAST(moves_record, :moves_count),
                        last_victory = NOW()
                    WHERE 
                        player_id = :player_id";
            } else {
                $query = "
                    INSERT INTO rankings 
                        (player_id, victories, moves_record, last_victory)
                    VALUES 
                        (:player_id, 1, :moves_count, NOW())";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':player_id' => $playerId,
                ':moves_count' => $movesCount
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating ranking: " . $e->getMessage());
            return false;
        }
    }
}