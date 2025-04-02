<?php
require_once __DIR__ . '/../models/GameManager.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class GameController {
    public function start() {
        $payload = AuthMiddleware::verifyToken(); // Asignar el resultado
        $data = json_decode(file_get_contents('php://input'), true);
        
        $game = new GameManager($payload['sub']);
        $gameId = $game->start($data['maze_id'] ?? null); // Pasar maze_id
        
        return [
            'status' => 'success',
            'game_id' => $gameId,
            'message' => 'Juego iniciado'
        ];
    }

    public function move(string $direction, int $gameId) {
      $payload = AuthMiddleware::verifyToken();
      $game = new GameManager($payload['sub']);
      $game->setGameId($gameId); // Configurar el ID del juego
      
      return $game->movePlayer($direction);
  }

  public function status() {
    $payload = AuthMiddleware::verifyToken();
    $gameId = $_GET['game_id'] ?? null;

    if (!$gameId) {
        throw new Exception("Se requiere game_id");
    }

    $game = new GameManager($payload['sub']);
    $game->setGameId($gameId);
    
    return $game->getStatus();
}
public function getUserGames() {
    $payload = AuthMiddleware::verifyToken();
    $playerId = $payload['sub'];
    
    $game = new GameManager($playerId);
    return $game->getAllPlayerGames();
}
    
public function restartGame(int $gameId) {
    $payload = AuthMiddleware::verifyToken();
    $playerId = $payload['sub'];
    
    $game = new GameManager($playerId);
    return $game->restart($gameId);
}
}