<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbiaphp.com/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbiaphp.com so we can send you a copy immediately.
 * 
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2007-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2007-2008 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN:$id
 */

/**
 * Establece política de informe de errores
 */
error_reporting(E_ALL ^ E_STRICT);

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
 * Define el VENDORS_PATH
 *
 * VENDORS_PATH:
 * - Ruta al directorio de librerias compartidas de terceros (por defecto la ruta al directorio core/vendors)
 **/
define('VENDORS_PATH', CORE_PATH . 'vendors/');

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
 * @see Benchmark
 */
require CORE_PATH . 'extensions/benchmark/benchmark.php';

/**
 * Inicia el benchmark
 */
Benchmark::start_clock('kumbia');

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
require CORE_PATH . 'kumbia/config/config.php';
/**
 * @see Cache
 **/
require CORE_PATH . 'extensions/cache/cache.php';
/**
 * Lee la configuracion
 */
$config = Config::read('config.ini');

/**
 * Obtiene la url
 **/
$url = isset($_GET['url']) ? $_GET['url'] : '';

/**
 * Desactiva la cache
 **/
if(!$config['application']['production']) {
	Cache::active(false);
} elseif ($template = Cache::get($url, 'kumbia.templates')) { //verifica cache de template para la url
	echo $template;
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