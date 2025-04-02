<?php
require_once __DIR__ . '/../config/jwt.php';

class JWTGenerator {
    public static function generateToken($playerId, $username) {
        $config = JWTConfig::$settings;
        $header = json_encode(['typ' => 'JWT', 'alg' => $config['algorithm']]);
        $payload = json_encode([
            'sub' => $playerId,
            'name' => $username,
            'iat' => time(),
            'exp' => time() + $config['expire_seconds']
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $config['secret_key'], true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validateToken($token) {
      $config = JWTConfig::$settings;
      
      $parts = explode('.', $token);
      if (count($parts) !== 3) {
          throw new Exception('Formato de token inválido');
      }
  
      $signature = hash_hmac('sha256', "$parts[0].$parts[1]", $config['secret_key'], true);
      $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
      
      if ($base64Signature !== $parts[2]) {
          throw new Exception('Firma inválida');
      }
  
      $payload = json_decode(base64_decode($parts[1]), true);
      if (isset($payload['exp']) && $payload['exp'] < time()) {
          throw new Exception('Token expirado');
      }
  
      return $payload;
  }
}