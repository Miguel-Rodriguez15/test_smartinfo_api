<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['password'])) {
            return ['status' => 'error', 'message' => 'Email y password requeridos'];
        }
    
        $user = new User();
        if ($user->register($data['username'], $data['email'], $data['password'])) {
            $token = $user->login($data['email'], $data['password']);
            
            $playerId = $user->getIdByEmail($data['email']);
            
            if ($playerId) {
                $rankingSystem = new RankingSystem();
                $rankingSystem->initializeRankingForPlayer($playerId);
                
                return [
                    'status' => 'success', 
                    'token' => $token,
                    'message' => 'Usuario registrado y autenticado',
                    'player_id' => $playerId
                ];
            }
        }
        return ['status' => 'error', 'message' => 'Error al registrar'];
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = new User();
        
        if ($token = $user->login($data['email'], $data['password'])) {
            return ['status' => 'success', 'token' => $token];
        }
        return ['status' => 'error', 'message' => 'Credenciales inválidas'];
    }
}