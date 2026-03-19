#!/bin/bash

# Este script permite alternar fácilmente entre entornos

if [ -z "$1" ]; then
    echo "❌ Error: Debes proveer un entorno. Uso: ./start.sh [dev|prod]"
    exit 1
fi

if [ "$1" == "dev" ]; then
    echo "🟢 Preparando entorno de DESARROLLO (Laravel Sail)..."
    
    if [ -f ".env.dev" ]; then
        cp .env.dev .env
        echo "✅ Archivo .env.dev copiado a .env"
    fi
    
    # Detener servicios de producción si estuvieran corriendo (para liberar puertos)
    if [ -f "docker-compose.prod.yml" ]; then
        docker compose -f docker-compose.prod.yml down
    fi
    
    # Levantar contenedores de desarrollo en segundo plano
    ./vendor/bin/sail up -d
    echo " Entorno de desarrollo iniciado. Recuerda correr './vendor/bin/sail npm run dev' en otra terminal para los assets."
    
elif [ "$1" == "prod" ]; then
    echo "🔴 Preparando entorno de PRODUCCIÓN..."
    
    if [ -f ".env.prod" ]; then
        cp .env.prod .env
        echo "✅ Archivo .env.prod copiado a .env"
    fi
    
    # Detener sail (desarrollo) si estuviera corriendo
    ./vendor/bin/sail down
    
    # Construir imagen ligera de producción e iniciar
    docker compose -f docker-compose.prod.yml up -d --build
    echo " Entorno de producción iniciado. Nginx, PHP-FPM, Colas y Reverb listos."
    
else
    echo "❌ Opción inválida. El entorno debe ser 'dev' o 'prod'."
    exit 1
fi
