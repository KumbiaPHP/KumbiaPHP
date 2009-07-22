<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * Este script ejecuta la carga de KumbiaPHP
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Iniciar el buffer de salida
 */
ob_start();

/**
 * En este caso se usa rewrite
 *
 **/
if(!isset($no_rewrite)) {
    /**
     * Define el PUBLIC_PATH
     *
     * PUBLIC_PATH:
     * - Path para generar la Url en los links a acciones y controladores
     * - Esta ruta la utiliza Kumbia como base para generar las Urls para acceder de lado de
     *   cliente (con el navegador web) y es relativa al DOCUMENT_ROOT del servidor web
     **/
    if ($_SERVER['QUERY_STRING']) {
        define('PUBLIC_PATH', substr($_SERVER['REQUEST_URI'], 0, - strlen($_SERVER['QUERY_STRING']) + 4));
    } else {
        define('PUBLIC_PATH', $_SERVER['REQUEST_URI']);
    }

    /**
     * Define el URL_PATH
     *
     * URL_PATH:
     * - Path utilizado para generar correctamente la url para acceder los controladores y acciones
     * - Este path puede modificarse para poder utilizar KumbiaPHP sin mod_rewrite.
     *
     *   Considerando que tu aplicacion se encuentre en /var/www/app
     *     Ejemplo:  define('URL_PATH', '/app/index.php?url=')
     *
     *   Para este caso falta tambien definir el PUBLIC_PATH como:
     *     Ejemplo: define('PUBLIC_PATH', '/app/public/')   
     **/
    define('URL_PATH', PUBLIC_PATH);
}

/**
 * Obtiene la url
 **/
$url = isset($_GET['url']) ? $_GET['url'] : '';
/**
 * Inicia la sesion
 **/
session_start();
/**
 * @see KumbiaException
 */
require CORE_PATH . 'kumbia/kumbia_exception.php';
/**
 * Inicializar el ExceptionHandler
 */
set_exception_handler(array('KumbiaException' , 'handle_exception'));
/**
 * @see Config
 */
require CORE_PATH . 'kumbia/config.php';
/**
 * Lee la configuracion
 */
$config = Config::read('config');
/**
 * @see Cache
 **/
require CORE_PATH . 'libraries/cache/cache.php';
/**
 * Asigna el driver para cache
 **/
if (isset($config['application']['cache_driver'])) {
    Cache::set_driver($config['application']['cache_driver']);
}
/**
 * Desactiva la cache
 **/
if (! $config['application']['production']) {
    Cache::active(false);
} elseif ($template = Cache::get($url, 'kumbia.templates')) { //verifica cache de template para la url
    echo $template;
    echo '<!-- Tiempo: ' . round(microtime(1) - START_TIME, 4) . ' seg. -->';
    exit(0);
}
/**
 * Asignando locale
 **/
if (isset($config['application']['locale'])) {
    setlocale(LC_ALL, $config['application']['locale']);
}
/**
 * Establecer el timezone para las fechas y horas
 */
if (isset($config['application']['timezone'])) {
    date_default_timezone_set($config['application']['timezone']);
}
/**
 * Establecer el charset de la app en la constante APP_CHARSET
 */
if (isset($config['application']['charset'])) {
    define('APP_CHARSET', strtoupper($config['application']['charset']));
} else {
    define('APP_CHARSET', 'UTF-8');
}
/**
 * @see Load
 */
require CORE_PATH . 'kumbia/load.php';
/**
 * Carga del boot.ini
 */
Load::boot();
/**
 * @see Kumbia
 */
require CORE_PATH . 'kumbia/kumbia.php';
/**
 * Atender la petición
 */
Kumbia::main($url);