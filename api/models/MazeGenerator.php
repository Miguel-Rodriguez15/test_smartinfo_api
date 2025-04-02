<?php
class MazeGenerator {
    public function create(int $size, string $difficulty = 'medium'): array {
        if ($size < 5 || $size > 25) {
            throw new Exception("El tamaño debe estar entre 5 y 25");
        }
        if ($size % 2 == 0) {
            throw new Exception("El tamaño debe ser un número impar");
        }

        $grid = array_fill(0, $size, array_fill(0, $size, 1));
        
        $complexity = match($difficulty) {
            'easy' => 0.3,  
            'hard' => 0.7,  
            default => 0.5  
        };

        $this->carvePaths($grid, $complexity);
        
        
        $grid[0][0] = 0;
        $grid[$size-1][$size-1] = 0;
        
        return $grid;
    }

    private function carvePaths(array &$grid, float $complexity): void {
        $size = count($grid);
        $stack = [[0, 0]];
        $visited = array_fill(0, $size, array_fill(0, $size, false));
        $visited[0][0] = true;

        $directions = [
            [0, -1], [1, 0], [0, 1], [-1, 0]
        ];

        while (!empty($stack)) {
            [$x, $y] = end($stack);
            $neighbors = [];

            foreach ($directions as [$dx, $dy]) {
                $nx = $x + $dx * 2; 
                $ny = $y + $dy * 2;

                if ($nx >= 0 && $nx < $size && 
                    $ny >= 0 && $ny < $size && 
                    !$visited[$nx][$ny]) {
                    $neighbors[] = [$nx, $ny, $dx, $dy];
                }
            }

            if (empty($neighbors)) {
                array_pop($stack);
                continue;
            }

            [$nx, $ny, $dx, $dy] = $neighbors[array_rand($neighbors)];
            
            
            $grid[$x + $dx][$y + $dy] = 0;
            $grid[$nx][$ny] = 0;
            $visited[$nx][$ny] = true;
            $stack[] = [$nx, $ny];
            
            if (rand(0, 100) < ($complexity * 100)) {
                shuffle($stack);
            }
        }
    }
}