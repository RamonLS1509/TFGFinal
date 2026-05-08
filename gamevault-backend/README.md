# GameVault — Backend

API REST desarrollada con **Laravel 11** + **MySQL** + **Laravel Sanctum** como parte del Trabajo Fin de Máster del Máster en Full Stack Developer.

## Tecnologías

- PHP 8.2+
- Laravel 11
- MySQL 8
- Laravel Sanctum (autenticación SPA)
- PHPUnit (testing)

## Requisitos previos

- PHP >= 8.2
- Composer
- MySQL 8
- Extensiones PHP: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json

## Instalación

```bash
# 1. Clonar el repositorio
git clone <repo-url>
cd gamevault-backend

# 2. Instalar dependencias
composer install

# 3. Copiar y configurar entorno
cp .env.example .env
# Editar .env con tus credenciales de MySQL

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Crear la base de datos
mysql -u root -p -e "CREATE DATABASE gamevault CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Arrancar el servidor
php artisan serve
```

El servidor quedará disponible en `http://localhost:8000`.

## Variables de entorno clave

| Variable | Descripción |
|---|---|
| `DB_DATABASE` | Nombre de la base de datos |
| `DB_USERNAME` | Usuario de MySQL |
| `DB_PASSWORD` | Contraseña de MySQL |
| `SANCTUM_STATEFUL_DOMAINS` | Dominio del frontend (localhost:5173) |
| `FRONTEND_URL` | URL completa del frontend |

## Usuarios de prueba (seeder)

| Rol | Email | Contraseña |
|---|---|---|
| Admin | admin@steam.test | password |


## Recursos implementados

### Autenticación (Sanctum SPA)
- `POST /api/register` — Registro
- `POST /api/login` — Login
- `POST /api/logout` — Logout (auth)
- `GET  /api/me` — Perfil del usuario autenticado

### Juegos (público)
- `GET    /api/games` — Listado con filtros (search, genre, platform, max_price, sort)
- `GET    /api/games/{id}` — Detalle de un juego

### Juegos (admin)
- `POST   /api/games` — Crear juego
- `PUT    /api/games/{id}` — Actualizar juego
- `DELETE /api/games/{id}` — Eliminar juego

### Biblioteca (auth)
- `GET    /api/library` — Mi biblioteca
- `POST   /api/library` — Añadir juego
- `GET    /api/library/{id}` — Detalle de entrada
- `PUT    /api/library/{id}` — Actualizar (horas jugadas)
- `DELETE /api/library/{id}` — Eliminar de biblioteca

### Wishlist (auth)
- `GET    /api/wishlist` — Mi wishlist
- `POST   /api/wishlist` — Añadir juego
- `PUT    /api/wishlist/{id}` — Cambiar prioridad
- `DELETE /api/wishlist/{id}` — Eliminar de wishlist

## Testing

```bash
php artisan test --testdox
```

Los tests usan SQLite en memoria. Cubren: autenticación, CRUD de juegos, biblioteca y wishlist.
