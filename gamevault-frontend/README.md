# GameVault — Frontend

SPA desarrollada con **Vue 3** + **Vite** + **Pinia** + **Tailwind CSS** como parte del Trabajo Fin de Máster del Máster en Full Stack Developer.

## Tecnologías

- Vue 3 (Composition API)
- Vue Router 4
- Pinia (gestión de estado)
- Axios (comunicación con API)
- Tailwind CSS (estilos)
- Vitest + Vue Test Utils (testing)

## Requisitos previos

- Node.js >= 18
- npm >= 9
- Backend corriendo en `http://localhost:8000`

## Instalación

```bash
# 1. Instalar dependencias
npm install

# 2. Arrancar en desarrollo
npm run dev
```

La aplicación quedará disponible en `http://localhost:5173`.

## Scripts disponibles

| Comando | Descripción |
|---|---|
| `npm run dev` | Servidor de desarrollo |
| `npm run build` | Build de producción |
| `npm run preview` | Preview del build |
| `npm run test:unit` | Ejecutar tests con Vitest |