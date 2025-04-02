<?php
require_once __DIR__ . '/MazeGenerator.php';
require_once __DIR__ . '/Database.php';


class GameManager {
    private $db;
    private $playerId;
    private $gameId;

    public function __construct(int $playerId) {
        $this->db = Database::getInstance()->getConnection();
        $this->playerId = $playerId;
    }

    public function setGameId(int $gameId): void {
        $this->gameId = $gameId;
    }

    public function start(int $mazeId): int {
        $stmt = $this->db->prepare(
            "INSERT INTO games (player_id, maze_id, current_x, current_y) 
            VALUES (:pid, :mid, 0, 0) 
            RETURNING game_id"
        );
        
        $stmt->execute([
            ':pid' => $this->playerId,
            ':mid' => $mazeId
        ]);
        
        $this->gameId = $stmt->fetch()['game_id'];
        return $this->gameId;
    }

    public function movePlayer(string $direction): array {
      if (!$this->gameId) {
          throw new Exception("Game ID no configurado");
      }
  
      $game = $this->getCurrentGame();
      $maze = json_decode($game['maze_structure'], true);
      $currentPos = [$game['current_x'], $game['current_y']];
      
      $newPos = $this->calculateNewPosition($currentPos, $direction);
      
      if (!$this->isValidMove($maze, $newPos)) {
          throw new Exception(
              "Choque con pared en [" . $newPos[0] . ", " . $newPos[1] . "]. " .
              "Posición actual: [" . $currentPos[0] . ", " . $currentPos[1] . "]"
          );
      }
      
      $this->updatePosition($newPos);
      
      return [
          'status' => 'success',
          'new_position' => $newPos,
          'moves_count' => $game['moves_count'] + 1,
          'is_completed' => $this->checkVictory($newPos, $game['exit_x'], $game['exit_y'])
      ];
  }
    private function getCurrentGame(): array {
        $stmt = $this->db->prepare(
            "SELECT g.*, m.structure as maze_structure, m.exit_x, m.exit_y 
            FROM games g
            JOIN mazes m ON g.maze_id = m.maze_id
            WHERE g.game_id = :id AND g.player_id = :pid"
        );
        $stmt->execute([':id' => $this->gameId, ':pid' => $this->playerId]);
        return $stmt->fetch();
    }

    private function calculateNewPosition(array $current, string $direction): array {
      $directions = [
          'up'    => [-1, 0], 
          'right' => [0, 1],   
          'down'  => [1, 0],   
          'left'  => [0, -1]   
      ];
      
      return [
          $current[0] + $directions[$direction][0], 
          $current[1] + $directions[$direction][1]   
      ];
  }
    private function isValidMove(array $maze, array $position): bool {
        [$x, $y] = $position;
        return isset($maze[$x][$y]) && $maze[$x][$y] === 0;
    }

    private function updatePosition(array $position): void {
        $stmt = $this->db->prepare(
            "UPDATE games 
            SET current_x = :x, current_y = :y, moves_count = moves_count + 1 
            WHERE game_id = :id"
        );
        $stmt->execute([
            ':x' => $position[0],
            ':y' => $position[1],
            ':id' => $this->gameId
        ]);
    }

    private function checkVictory(array $position, int $exitX, int $exitY): bool {
        if ($position[0] == $exitX && $position[1] == $exitY) {
            $this->db->prepare(
                "UPDATE games SET is_completed = true WHERE game_id = :id"
            )->execute([':id' => $this->gameId]);
            
            $gameData = $this->getCurrentGame();
            
            $rankingSystem = new RankingSystem();
            $rankingSystem->updatePlayerRanking($this->playerId, $gameData['moves_count']);
            
            return true;
        }
        return false;
    }
    public function getStatus(): array {
      $stmt = $this->db->prepare(
          "SELECT 
              g.current_x, 
              g.current_y, 
              g.moves_count,
              g.is_completed,
              m.size,
              m.exit_x,
              m.exit_y,
              m.structure as maze_structure
          FROM games g
          JOIN mazes m ON g.maze_id = m.maze_id
          WHERE g.game_id = :id AND g.player_id = :pid"
      );
      
      $stmt->execute([':id' => $this->gameId, ':pid' => $this->playerId]);
      $data = $stmt->fetch();
  
      if (!$data) {
          throw new Exception("Partida no encontrada");
      }
  
      $maze = json_decode($data['maze_structure'], true);
      $currentPos = [$data['current_x'], $data['current_y']];
      $exitPos = [$data['exit_x'], $data['exit_y']];
  
      $mazeWithPosition = $maze;
  
      $mazeWithPosition[$currentPos[0]][$currentPos[1]] = 2; 
      $mazeWithPosition[$exitPos[0]][$exitPos[1]] = 3;        
  
      return [
          'status' => 'success',
          'current_position' => $currentPos,
          'moves_count' => $data['moves_count'],
          'is_completed' => (bool)$data['is_completed'],
          'maze_size' => $data['size'],
          'exit_position' => $exitPos,
          'distance_to_exit' => $this->calculateDistance($currentPos, $exitPos),
          'maze' => $mazeWithPosition, 
          'legend' => [               
              '0' => 'pasillo',
              '1' => 'pared',
              '2' => 'tú',
              '3' => 'salida'
          ]
      ];
  }
  
  private function calculateDistance(array $current, array $exit): float {
      return round(sqrt(
          pow($exit[0] - $current[0], 2) + 
          pow($exit[1] - $current[1], 2)
      ), 2);
  }

  public function getAllPlayerGames(): array {
    $stmt = $this->db->prepare(
        "SELECT 
            g.game_id,
            g.current_x,
            g.current_y,
            g.moves_count,
            g.is_completed,
            m.size,
            m.difficulty,
            m.exit_x,
            m.exit_y
        FROM 
            games g
        JOIN 
            mazes m ON g.maze_id = m.maze_id
        WHERE 
            g.player_id = :pid
        ORDER BY 
            g.game_id DESC" 
    );
    
    $stmt->execute([':pid' => $this->playerId]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'status' => 'success',
        'player_id' => $this->playerId,
        'total_games' => count($games),
        'completed_games' => array_reduce($games, function($carry, $game) {
            return $carry + ($game['is_completed'] ? 1 : 0);
        }, 0),
        'games' => $games
    ];
}
public function restart(int $gameId): array {
    $stmt = $this->db->prepare(
        "SELECT m.start_x, m.start_y, m.structure 
         FROM games g
         JOIN mazes m ON g.maze_id = m.maze_id
         WHERE g.game_id = :game_id AND g.player_id = :player_id"
    );
    $stmt->execute([':game_id' => $gameId, ':player_id' => $this->playerId]);
    $gameData = $stmt->fetch();

    if (!$gameData) {
        throw new Exception("Partida no encontrada o no pertenece al jugador");
    }

    $this->db->prepare(
        "UPDATE games 
         SET current_x = :start_x, 
             current_y = :start_y, 
             moves_count = 0,
             is_completed = false
         WHERE game_id = :game_id"
    )->execute([
        ':start_x' => $gameData['start_x'],
        ':start_y' => $gameData['start_y'],
        ':game_id' => $gameId
    ]);

    return [
        'status' => 'success',
        'message' => 'Partida reiniciada correctamente',
        'new_position' => [$gameData['start_x'], $gameData['start_y']],
        'maze_structure' => json_decode($gameData['structure'], true)
    ];
}
}
