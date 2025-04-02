# 🎮 Laberinto API - Documentación Técnica

## 📋 Tabla de Contenidos
1. [Estructura del Proyecto](#-estructura-del-proyecto)
2. [Requisitos](#-requisitos)
3. [Configuración](#%EF%B8%8F-configuración)
4. [Endpoints](#-endpoints)
5. [Ejemplos de Uso](#-ejemplos-de-uso)


## 🏗️ Estructura del Proyecto
```
└── 📁prueba_tecnica_smartinfo
    └── 📁api
        └── 📁config
            └── database.php
            └── jwt.php
        └── 📁controllers
            └── AuthController.php
            └── GameController.php
            └── MazeController.php
            └── RankingController.php
            └── TestController.php
        └── 📁middleware
            └── AuthMiddleware.php
        └── 📁models
            └── Database.php
            └── GameManager.php
            └── MazeGenerator.php
            └── RankingSystem.php
            └── User.php
        └── routes.php
        └── 📁utils
            └── JWTGenerator.php
    └── 📁public
        └── .htaccess
        └── index.php
        └── test-db.php
    └── README.md
```

# ⚙️ Requisitos

- PHP 8.0+
- PostgreSQL 13+
- Apache 

# ⚙️ Endpoints

### Nota: todos los endpoint tienen al incio la palabra api

## Registrar usuario
``` curl -X POST /register  '{"username":"user", "email":"user@mail.com", "password":"pass"}'```

## Login
``` curl -X POST /login  '{"username":"user", "password":"pass"}'```

## Generar laberinto  
``` curl -X POST /mazes  "Authorization: Bearer {token}"  '{"size":7, "difficulty":"medium"}'```

## Iniciar el juego laberinto  
``` curl -X POST /games/start  "Authorization: Bearer {token}"  '{"maze_id": 9}'```

## Mover jugador
``` curl -X POST /move  "Authorization: Bearer {token}"  '{"direction": "right"}'```
## Consultar el juego de  jugador
``` curl -X POST /games/status?game_id={game_id}  "Authorization: Bearer {token}"  '{'```
## Ranking
```curl -X GET /ranking  "Authorization: Bearer {token}" ```

## Partidas de un usuario
```curl -X GET /games/user  "Authorization: Bearer {token}" ```

## Reiniciar un juego
```curl -X POST /games/restart  "Authorization: Bearer {token}" '{"game_id":1}'``` 

# 📌 Notas
- Todos los endpoints retornan JSON

- Códigos HTTP importantes:

  200: Éxito

  400: Error en solicitud

  401: No autorizado

  500: Error interno

# 📌 Como correr el proyecto

1. Clonar el repositorio
2. Configurar la base de datos en el archivo **config/database.php**
3. Configurar el servidor web
4. Iniciar el servidor
5. Ejecutar el siguiente comando: ```php -S localhost:8000 -t public```

