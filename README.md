# SteamClone — Trabajo Fin de Máster

Aplicación web Full Stack inspirada en Steam, desarrollada como TFM del Máster en Full Stack Developer.

## Descripción

SteamClone permite a los usuarios explorar un catálogo de videojuegos, gestionar su biblioteca personal de juegos adquiridos y mantener una lista de deseos. Los administradores pueden gestionar el catálogo completo.

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Frontend | Vue 3, Vite, Pinia, Vue Router, Tailwind CSS |
| Backend | Laravel 11, PHP 8.2 |
| Base de datos | MySQL 8 |
| Autenticación | Laravel Sanctum (SPA cookies) |
| Testing BE | PHPUnit |
| Testing FE | Vitest + Vue Test Utils |

## Recursos implementados

1. **Juegos** — CRUD completo (admin), listado público con filtros y paginación, detalle de juego
2. **Biblioteca** — Gestión de los juegos adquiridos por el usuario
3. **Wishlist** — Lista de deseos con prioridades

## Instalación rápida

Ver los README de cada carpeta:
- [Backend/README.md](./Backend/README.md)
- [Frontend/README.md](./Frontend/README.md)

## Funcionalidades destacadas

- Área pública: catálogo con filtros por género, precio y búsqueda
- Área privada: biblioteca personal y wishlist
- Panel de administración: CRUD de juegos
- Autenticación segura con cookies HttpOnly
- Diseño responsive dark-mode
- 24 tests en backend + 30 tests en frontend