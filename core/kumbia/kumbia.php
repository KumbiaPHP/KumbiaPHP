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
final class Kumbia
{
    /**
     * Almacena la version actual del Framework
     *
     */
    const KUMBIA_VERSION = '1.0';
    /**
     * Almacena datos compartidos en la aplicacion
     *
     * @var array
     */
    static public $data = array();
	/**
	 * Inicia la aplicacion
	 *
	 **/
    public static function init_application()
	{
		/**
		 * Carga del boot.ini
		 */
		Load::boot();
	
		/**
         * @see Controller
         */
        require CORE_PATH . 'kumbia/controller/application/application.php';
        	
        /**
         * @see ApplicationController
         */
        include_once APP_PATH . 'application.php';
		
		/**
		 * Lee la configuracion
		 **/
		$config = Config::read('config.ini');
		
		/**
		 * Iniciando I18n
		 **/
		bindtextdomain('default', APP_PATH . 'locale/'); 
		textdomain('default'); 
		if(isset($config['application']['locale']) && $config['application']['locale']) {
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
	 	define('APP_CHARSET', strtoupper($config['application']['charset']));
    }
    /**
     * Función Principal donde se ejecutan los controladores
     *
	 * @params string $url url
     * @return boolean
     */
    public static function main($url)
	{
		/**
		 * @see Router
		 */
		require CORE_PATH . 'kumbia/router/router.php';
		/**
		 * El Router analiza la url
		 **/
		Router::rewrite($url);
		
    	/**
         * @see Dispatcher
         */
        require CORE_PATH . 'kumbia/dispatcher/dispatcher.php';
        /**
         * @see Flash
         */
        require CORE_PATH . 'extensions/messages/flash.php';
        /**
         * @see Utils
         */
        require CORE_PATH . 'kumbia/util/utils.php';
        /**
         * @see Util
         */
        require CORE_PATH . 'kumbia/util/util.php';
		/**
         * @see Load
         */
        require CORE_PATH . 'kumbia/load.php';
		
		/**
		 * Kumbia reinicia las variables de aplicación cuando cambiamos
		 * entre una aplicación y otra. Init Application define KUMBIA_PATH
		 */
		self::init_application();
	
		/**
		 * Iniciar el buffer de salida
		 */
		ob_start();
		
		/**
		 * Ciclo del enrutador
		 */
		$controller = Dispatcher::execute();
		while (Router::get_routed()) {
			Router::set_routed(false);
			$controller = Dispatcher::execute();
		}
		
		/**
		 * Renderiza la vista
		 **/
		if($controller->view || $controller->template) {
			require_once CORE_PATH . 'kumbia/view.php';
			View::render($controller, $url);
		} else {
			ob_end_flush();
		}
		
		// Fin del request
		exit(0);
    }
    /**
     * Imprime los CSS cargados mediante stylesheet_link_tag
     *
     */
    public static function stylesheet_link_tags()
	{
        $imports = self::$data['KUMBIA_CSS_IMPORTS'];
        if ($imports && is_array($imports)) {
            foreach ($imports as $css) {
                echo $css;
            }
        } else {
            echo $imports;
        }
    }
}