<?php
require_once __DIR__ . '/../models/RankingSystem.php';
class RankingController {
    public function getTopPlayers($limit = 10) {
        $ranking = new RankingSystem();
        return [
            'status' => 'success',
            'ranking' => $ranking->getTopPlayers($limit)
        ];
    }
}