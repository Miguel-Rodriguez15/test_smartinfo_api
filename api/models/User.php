<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../utils/JWTGenerator.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO players (username, email, password_hash) 
            VALUES (:username, :email, :password)"
        );
        
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword
        ]);
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare(
            "SELECT player_id, username, password_hash FROM players 
            WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return JWTGenerator::generateToken($user['player_id'], $user['username']);
        }
        return false;
    }

    public function getIdByEmail(string $email): ?int {
        $stmt = $this->db->prepare("SELECT player_id FROM players WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['player_id'] : null;
    }
}