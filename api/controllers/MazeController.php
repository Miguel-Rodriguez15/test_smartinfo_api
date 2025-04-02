<?php
require_once __DIR__ . '/../models/MazeGenerator.php';
require_once __DIR__ . '/../models/Database.php';

class MazeController
{
  public function generate()
  {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];

    try {
      $generator = new MazeGenerator();
      $size = $data['size'] ?? 5;
      $difficulty = $data['difficulty'] ?? 'medium';

      $maze = $generator->create($size, $difficulty);

      return [
        'status' => 'success',
        'maze_id' => $this->storeMaze($maze, $size, $difficulty),
        'size' => $size,
        'difficulty' => $difficulty,
        'start' => [0, 0],
        'exit' => [$size - 1, $size - 1]
      ];

    } catch (Exception $e) {
      http_response_code(400);
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
  }
  private function storeMaze(array $maze, int $size, string $difficulty): int
  {
    $stmt = Database::getInstance()->getConnection()->prepare(
      "INSERT INTO mazes 
      (size, structure, start_x, start_y, exit_x, exit_y, difficulty) 
      VALUES (:size, :structure, :start_x, :start_y, :exit_x, :exit_y, :difficulty) 
      RETURNING maze_id"
    );

    $stmt->execute([
      ':size' => $size,
      ':structure' => json_encode($maze),
      ':start_x' => 0,    
      ':start_y' => 0,
      ':exit_x' => $size - 1,  
      ':exit_y' => $size - 1,
      ':difficulty' => $difficulty
    ]);

    return $stmt->fetch()['maze_id'];
  }
}