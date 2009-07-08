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
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Establece política de informe de errores
 */
error_reporting(E_ALL ^ E_STRICT);
define('START_TIME', microtime(1));
/**
 * Define el APP_PATH
 *
 * APP_PATH:
 * - Ruta al directorio de la aplicación (por defecto la ruta al directorio app)
 * - Esta ruta se utiliza para cargar los archivos de la aplicación
 **/
define('APP_PATH', dirname(dirname(__FILE__)) . '/');

/**
 * Define el nombre de la APP
 * 
 */
define('APP', basename(APP_PATH));

/**
 * Define el CORE_PATH
 *
 * CORE_PATH:
 * - Ruta al directorio que contiene el núcleo de Kumbia (por defecto la ruta al directorio core/kumbia)
 **/
define('CORE_PATH', dirname(APP_PATH) . '/core/');

/**
 * Define el URL_PATH
 *
 * URL_PATH:
 * - Path para generar la Url en los links a acciones y controladores
 * - Esta ruta la utiliza Kumbia como base para generar las Urls para acceder de lado de
 *   cliente (con el navegador web) y es relativa al DOCUMENT_ROOT del servidor web
 **/
if($_SERVER['QUERY_STRING']) {
	define('URL_PATH', substr($_SERVER['REQUEST_URI'], 0, -strlen($_SERVER['QUERY_STRING']) + 4));
} else {
	define('URL_PATH', $_SERVER['REQUEST_URI']);
}

/**
 * Define el PUBLIC_PATH
 *
 * PUBLIC_PATH:
 * - Ruta al directorio public de la aplicación (por defecto ruta al directorio app/public)
 * - Esta ruta la utiliza el cliente (el navegador web) para acceder a los recursos
 *   y es relativa al DOCUMENT_ROOT del servidor web
 **/
define('PUBLIC_PATH', URL_PATH);

/**
 * @see KumbiaException
 */
require CORE_PATH . 'kumbia/kumbia_exception.php';
/**
 * Inicializar el ExceptionHandler
 */
set_exception_handler(array('KumbiaException', 'handle_exception'));

/**
 * @see Config
 */
require CORE_PATH . 'kumbia/config.php';
/**
 * @see Cache
 **/
require CORE_PATH . 'libraries/cache/cache.php';
/**
 * Lee la configuracion
 */
$config = Config::read('config.ini');

/**
 * Obtiene la url
 **/
$url = isset($_GET['url']) ? $_GET['url'] : '';

/**
 * Asigna el driver para cache
 **/
Cache::set_driver($config['application']['cache_driver']);

/**
 * Desactiva la cache
 **/
if(!$config['application']['production']) {
	Cache::active(false);
} elseif ($template = Cache::get($url, 'kumbia.templates')) { //verifica cache de template para la url
	echo $template;
	echo '<!-- Tiempo: '.round(microtime(1)-START_TIME,4).' seg. -->';
	exit(0);
}

/**
 * Inicia la sesion
 **/
session_start();

/**
 * @see Kumbia
 */
require CORE_PATH . 'kumbia/kumbia.php';
/**
 * Atender la petición
 */
Kumbia::main($url);
