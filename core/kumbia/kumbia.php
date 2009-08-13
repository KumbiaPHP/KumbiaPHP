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
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
 
/**
 * @see Dispatcher
 */
require CORE_PATH . 'kumbia/dispatcher.php';
/**
 * @see Flash
 */
require CORE_PATH . 'libs/flash/flash.php';
/**
 * @see Util
 */
require CORE_PATH . 'kumbia/util.php';
/**
 * @see Controller
 */
require CORE_PATH . 'kumbia/controller.php';
/**
 * @see ApplicationController
 */
require APP_PATH . 'application.php';    
/**
 * @see Router
 */
require CORE_PATH . 'kumbia/router.php';
 
/**
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Kumbia
{
    /**
     * Almacena la version actual del Framework
     *
     */
    const KUMBIA_VERSION = '1.0 Beta 1';
    /**
     * Almacena datos compartidos en la aplicacion
     *
     * @var array
     */
    static public $data = array();
    /**
     * Función Principal donde se ejecutan los controladores
     *
	 * @params string $url url
     * @return boolean
     */
    public static function main($url)
	{
		/**
		 * El Router analiza la url
		 **/
		Router::rewrite($url);
		
		/**
		 * Ciclo del enrutador
		 */
		$controller = Dispatcher::execute();
		while (Router::getRouted()) {
			Router::setRouted(false);
			$controller = Dispatcher::execute();
		}
		
		/**
		 * Renderiza la vista
		 **/
		if($controller->view || $controller->template) {
			require CORE_PATH . 'kumbia/view.php';
			View::render($controller, $url);
		} else {
			ob_end_flush();
		}
		
		// Fin del request
		exit();
    }
}