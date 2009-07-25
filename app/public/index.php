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
 * Establece polí­tica de informe de errores
 */
error_reporting(E_ALL ^ E_STRICT);
define('START_TIME', microtime(1));

/**
 * Define el APP_PATH
 *
 * APP_PATH:
 * - Ruta al directorio de la aplicación (por defecto la ruta al directorio app)
 * - Esta ruta se utiliza para cargar los archivos de la aplicaciÃ³n
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
 * Define el PUBLIC_PATH
 *
 * PUBLIC_PATH:
 * - Path para generar la Url en los links a acciones y controladores
 * - Esta ruta la utiliza Kumbia como base para generar las Urls para acceder de lado de
 *   cliente (con el navegador web) y es relativa al DOCUMENT_ROOT del servidor web
 **/
if ($_SERVER['QUERY_STRING']) {
    define('PUBLIC_PATH', substr($_SERVER['REQUEST_URI'], 0, - strlen($_SERVER['QUERY_STRING']) + 5));
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

/**
 * Obtiene la url
 **/
$url = isset($_GET['url']) ? $_GET['url'] : '/';

/**
 * Carga el gestor de arranque
 *
 * @see Bootstrap
 **/
require CORE_PATH . 'kumbia/bootstrap.php';