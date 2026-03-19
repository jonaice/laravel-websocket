# Documentación del Proyecto: WebSockets con Laravel 13

Este documento describe la arquitectura, la configuración de infraestructura y el flujo de trabajo para el desarrollo y despliegue del proyecto.

##  Stack Tecnológico
- **Framework Core:** Laravel 13 (PHP 8.4+)
- **Base de Datos:** MySQL 8.4
- **Caché y Colas:** Redis (imprescindible para el escalado de WebSockets)
- **WebSockets:** Laravel Reverb (Servidor WebSocket nativo de Laravel)
- **Infraestructura:** Docker (Entornos aislados para Desarrollo y Producción)
- **Frontend Assets:** Vite + Vue/JS Vanilla (con Laravel Echo y Pusher-js)

---

##  Arquitectura de Docker (Desarrollo vs Producción)

El proyecto está diseñado para tener una paridad estricta entre desarrollo y producción utilizando contenedores, pero optimizado para las necesidades de cada entorno. Para gestionar esto, usamos el script interactivo `./start.sh`.

### Entorno de Desarrollo (`dev`)
Utiliza **Laravel Sail** (`compose.yaml`).
- **Propósito:** Levantar rápidamente el proyecto local y editar código en tiempo real (autorecarga, Xdebug, etc).
- **Inicio:** `./start.sh dev` (Usa el archivo `.env.dev`).
- **Servicios:** MySQL, Redis, y un contenedor robusto de PHP (`sail-8.5/app`).

### Entorno de Producción (`prod`)
Utiliza `docker-compose.prod.yml` y `Dockerfile.prod`.
- **Propósito:** Ejecutar el código de manera optimizada, segura y rápida.
- **Inicio:** `./start.sh prod` (Usa el archivo `.env.prod`).
- **Servicios:**
  - **MySQL & Redis:** Usan volúmenes persistentes (`prod-mysql` y `prod-redis`).
  - **App (PHP-FPM + Supervisor):** La imagen de producción instala dependencias limpias (sin paquetes `--dev`), compila assets de Node, almacena la caché de rutas/vistas (`php artisan config:cache`), e inicia **Supervisor**.
  - **Supervisor (`docker/prod/supervisord.conf`):** Se encarga de mantener siempre vivos tres procesos críticos:
    1. `php-fpm` (Procesador principal web)
    2. `queue:work` (Trabajador de colas asíncronas para disparar eventos WebSocket)
    3. `reverb:start` (El servidor WebSocket en el puerto 8080)

> **Nota sobre el Proxy de Producción:** El contenedor contenedor de la aplicación no incluye Nginx. Está configurado para exponer el puerto HTTP/FPM en el puerto **9000** y los WebSockets en el **8080**. Se asume que en el servidor físico host existe un Proxy Inverso (Nginx, Traefik, Apache) que recibe el tráfico de los puertos 80/443 de Internet y lo redirige (Proxy Pass) a estos puertos del contenedor.

---

##  Flujo de Trabajo Común (Comandos Útiles)

Asegúrate de ejecutar estos comandos en la ruta raíz de este directorio.

### 1. Iniciar o Cambiar Entornos
Todo se maneja mediante el script principal:
```bash
./start.sh dev   # Levanta entorno de Desarrollo
./start.sh prod  # Levanta entorno de Producción
```

### 2. Iniciar el Servidor de WebSockets (Desarrollo)
En desarrollo, necesitas correr Reverb manualmente para ver el output de debug en otra terminal:
```bash
./vendor/bin/sail artisan reverb:start --debug
```
*(En Producción, Supervisor hace esto automáticamente de fondo y no te tienes que preocupar).*

### 3. Compilar los Assets Frontend
Para compilar en tiempo real los cambios en tu JavaScript/CSS en desarrollo:
```bash
./vendor/bin/sail npm run dev
```

### 4. Interactuar con Artisan y Composer
Ya que tu proyecto corre en Docker, no uses el `php` instalado en tu máquina. Usa siempre Sail:
- **Crear un evento WebSocket:** `./vendor/bin/sail artisan make:event NuevoMensajeEvent`
- **Instalar un paquete:** `./vendor/bin/sail composer require laravel/sanctum`
- **Correr migraciones:** `./vendor/bin/sail artisan migrate`

---

>  Proyecto configurado, estructurado y listo para el desarrollo intensivo de WebSockets en Laravel 13.
