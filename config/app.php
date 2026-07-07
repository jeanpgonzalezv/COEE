<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | COEE — Central de Operaciones y Emergencias Escolares
    | Sistema web de comunicación interna para establecimientos educacionales.
    | Proyecto desarrollado por Jean Gonzalez, Nicolas _____ y Yordan _____
    | Asignatura TPY1101 — Analista Programador — DUOC UC
    |
    */

    'name' => env('APP_NAME', 'COEE - Central de Operaciones y Emergencias Escolares'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    | Entorno de desarrollo local con Laragon: http://localhost:8000
    |
    */

    'url' => env('APP_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone — COEE
    |--------------------------------------------------------------------------
    |
    | Zona horaria fijada a Chile Continental para que las alertas, tickets
    | y reportes del sistema COEE reflejen la hora real del establecimiento
    | educacional, evitando el desfase con servidores en UTC.
    |
    */

    'timezone' => 'America/Santiago',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration — COEE
    |--------------------------------------------------------------------------
    |
    | Idioma español de Chile para mensajes de validación, fechas y textos
    | nativos de Laravel, consistente con la interfaz del sistema COEE.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];