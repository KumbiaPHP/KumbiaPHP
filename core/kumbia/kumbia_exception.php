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
 * Clase principal para el manejo de excepciones
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class KumbiaException extends Exception {
    /**
     * Codigo de error de la Excepcion
     */
    protected $error_code = 0;
    /**
     * Mostrar Trace o no
     *
     * @var boolean
     */
    protected $_show_trace = true;
    /**
     * Constructor de la clase;
     *
     */
    public function __construct($message, $error_code = 0, $show_trace = true) {
        $this->show_trace = $show_trace;
        if (is_numeric($error_code)) {
            parent::__construct($message, $error_code);
        } else {
            $this->error_code = $error_code;
            parent::__construct($message, 0);
        }
    }
    	
	/**
	 * Maneja las excepciones no capturadas
	 *
	 **/
	public static function handle_exception($e)
	{
		/**
		 * @see Flash
		 */
		require_once CORE_PATH . 'libraries/flash/flash.php';
	
		header('HTTP/1.1 404 Not Found');
		$config = Config::read('config.ini');
		extract(Router::get(), EXTR_OVERWRITE);
		
		ob_start();
		if(!$config['application']['production']) {
			$show_trace = $e->_show_trace;
			$boot = Config::read('boot.ini');
			include CORE_PATH . 'views/errors/exception.phtml';
		} else {
			include APP_PATH . 'views/errors/404.phtml';
		}
		$content = ob_get_clean();
		
		/**
		 * Verifica si ya se habia iniciado captura del buffer
		 * y en ese caso la termina
		 **/
		if(ob_get_level()) {
			ob_end_clean();
		}
		require_once CORE_PATH . 'kumbia/view.php';
		/**
		 * Verifico si se cargo el Dispatcher
		 **/
		if(class_exists('Dispatcher')) {
			$controller = Dispatcher::get_controller();
			if(!$controller || $controller->response != 'view') {
				echo $content;
				include CORE_PATH . 'views/templates/default.phtml';
			} else {
				echo $content;
			}
		} else {
			echo $content;
			include CORE_PATH . 'views/templates/default.phtml';
		}
	}
}