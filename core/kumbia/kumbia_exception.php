<?php
/**
 * Kumbia PHP Framework
 *
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
 * @category Kumbia
 * @package  Exceptions
 * 
 * @author   Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version  SVN:$id
 * @see      Object
 */
/**
 * Clase principal de Implementacion de Excepciones
 *
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
		require_once CORE_PATH.'messages/flash.php';
		/**
		 * @see Helpers
		 */
		require_once CORE_PATH.'helpers/helpers.php';
	
		header('HTTP/1.1 404 Not Found');
		$config = Config::read('config.ini');
		extract(Router::get_vars());
		
		ob_start();
		if(!$config['application']['production']) {
			$show_trace = $e->_show_trace;
			$models = implode(' ,', array_keys(Kumbia::$models));
			$boot = Config::read('boot.ini');
			include CORE_PATH . 'view/views/exception.phtml';
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
		
		/**
		 * Verifico si se cargo el Dispatcher
		 **/
		if(class_exists('Dispatcher')) {
			$controller = Dispatcher::get_controller();
			if(!$controller || $controller->response != 'view') {
				Kumbia::$content = $content;
				include CORE_PATH . 'view/templates/default.phtml';
			} else {
				echo $content;
			}
		} else {
			Kumbia::$content = $content;
			include CORE_PATH . 'view/templates/default.phtml';
		}
	}
}