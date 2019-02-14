<?php
/**
 * KumbiaPHP Web Framework
 * Parámetros de configuracion de la aplicacion
 */
return [
    'application' => [
        /**
         * name: es el nombre de la aplicacion
         */
        'name' => 'KUMBIAPHP PROJECT',
        /**
         * database: base de datos a utilizar
         */
        'database' => 'development',
        /**
         * dbdate: formato de fecha por defecto de la aplicacion
         */
        'dbdate' => 'YYYY-MM-DD',
        /**
         * debug: muestra los errores en pantalla (On/off)
         */
        'debug' => 'On',
        /**
         * log_exceptions: muestra las excepciones en pantalla (On/off)
         */
        'log_exceptions' => 'On',
        /**
         * cache_template: descomentar para habilitar cache de template
         */
        //'cache_template' => 'On',
        /**
         * cache_driver: driver para la cache (file, sqlite, memsqlite)
         */
        'cache_driver' => 'file',
        /**
         * metadata_lifetime: tiempo de vida de la metadata en cache
         */
        'metadata_lifetime' => '+1 year',
        /**
         * namespace_auth: espacio de nombres por defecto para Auth
         */
        'namespace_auth' => 'default',
        /**
         * routes: descomentar para activar routes en routes.php
         */
        //'routes' => '1',
    ],
];
