<?php
require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/MazeController.php';
require_once __DIR__ . '/controllers/GameController.php';
require_once __DIR__ . '/controllers/RankingController.php';


$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');
try {
  switch ("$request_method:$request_path") {
    case 'POST:/api/register':
      echo json_encode((new AuthController())->register());
      break;

    case 'POST:/api/login':
      echo json_encode((new AuthController())->login());
      break;

    case 'GET:/api/protected':
      $user_data = AuthMiddleware::verifyToken();
      echo json_encode([
        'status' => 'success',
        'message' => 'Hello World protegido!',
        'user' => $user_data
      ]);
      break;
    case 'POST:/api/mazes': 
      echo json_encode((new MazeController())->generate());
      break;

    case 'POST:/api/games/start':
      echo json_encode((new GameController())->start());
      break;

    case 'POST:/api/move':
      $data = json_decode(file_get_contents('php://input'), true);
      echo json_encode((new GameController())->move($data['direction'], $data['game_id']));
      break;

    case 'GET:/api/games/status':
      echo json_encode((new GameController())->status());
      break;

    case 'GET:/api/ranking':
      echo json_encode((new RankingController())->getTopPlayers());
      break;

    case 'GET:/api/games/user':
      echo json_encode((new GameController())->getUserGames());
      break;

    case 'POST:/api/games/restart':
      $data = json_decode(file_get_contents('php://input'), true);
      echo json_encode((new GameController())->restartGame($data['game_id']));
      break;

    default:
      http_response_code(404);
      echo json_encode(['status' => 'error', 'message' => 'Ruta no encontrada']);
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}