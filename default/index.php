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
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Este codigo solo se ejecutará cuando no esté el mod_rewrite activado
 */ 
  
/**
 * Indicar si la aplicacion se encuentra en produccion
 * directamente desde el index.php
 */
//define('PRODUCTION', TRUE);

/**
 * Establece polí­tica de informe de errores
 */
//error_reporting(0); // Usar este en producción, no envia errores
error_reporting(E_ALL ^ E_STRICT); // Comentar en producción
//comentar la siguiente linea en producción
ini_set('display_errors', 'On'); 

/**
 * Define marca de tiempo en que inicio el Request
 */
define('START_TIME', microtime(1));

/**
 * Define el APP_PATH
 *
 * APP_PATH:
 * - Ruta al directorio de la aplicación (por defecto la ruta al directorio app)
 * - Esta ruta se utiliza para cargar los archivos de la aplicacion
 */
define('APP_PATH', dirname(__FILE__) . '/app/');

/**
 * Define el CORE_PATH
 *
 * CORE_PATH:
 * - Ruta al directorio que contiene el núcleo de Kumbia (por defecto la ruta al directorio core)
 */
define('CORE_PATH', dirname(dirname(APP_PATH)) . '/core/');

/**
 * Define el PUBLIC_PATH
 *
 * PUBLIC_PATH:
 * - Path para genera la Url en los links a acciones y controladores
 * - Esta ruta la utiliza Kumbia como base para generar las Urls para acceder de lado de
 *   cliente (con el navegador web) y es relativa al DOCUMENT_ROOT del servidor web
 */
define('PUBLIC_PATH', $_SERVER['SCRIPT_NAME'] . '/');

/**
 * Define el URL_PATH
 *
 * PUBLIC_PATH:
 * - Url que utilizan los helpers que obtienen recursos de la parte publica de la App
 * - Esta ruta la utiliza Kumbia como base para generar las Urls para los js, los css,
 *   y todos los archivos que se encuentre en public , imagenes , documentos , etc...
 * - Puede hacerse independiente del PUBLIC_PATH para casos donde no se pueda 
 *   usar el mod_rewrite, usar internacionalización, entre otros.
 */
define('URL_PATH', dirname(PUBLIC_PATH) . '/');

/**
 * Obtiene la url
 */
$url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

/**
 * Usar esta y comentar la de arriba si no se tiene el mod_rewrite activado
 */
//$url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

/**
 * Carga el gestor de arranque
 * Por defecto el bootstrap del core
 *
 * @see Bootstrap
 */
//require APP_PATH . 'libs/bootstrap.php'; //bootstrap de app
require CORE_PATH . 'kumbia/bootstrap.php'; //bootstrap del core 
