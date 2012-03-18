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
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase principal para el manejo de excepciones
 *
 * @category   Kumbia
 * @package    Core
 */
class KumbiaException extends Exception
{

    /**
     * Codigo de error de la Excepcion
     *
     * @var string
     */
    protected $_view;

    /**
     * Constructor de la clase;
     *
     * @param string $message mensaje
     * @param string $view vista que se mostrara
     */
    public function __construct($message, $view = 'exception')
    {
        $this->_view = $view;
        parent::__construct($message);
    }

    /**
     * Maneja las excepciones no capturadas
     *
     * @param Exception $e
     * */
    public static function handle_exception($e)
    {
        if (isset($e->_view) && ($e->_view == 'no_controller' || $e->_view == 'no_action')) {
            header('HTTP/1.1 404 Not Found');
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }

        extract(Router::get(), EXTR_OVERWRITE);

        $Controller = Util::camelcase($controller);
        ob_start();
        if (PRODUCTION) {
            include APP_PATH . 'views/_shared/errors/404.phtml';
            return;
        } else {
            $Template = 'views/templates/exception.phtml';
            if (isset($e->_view)) {
                include CORE_PATH . "views/errors/{$e->_view}.phtml";
            } else {
                include CORE_PATH . "views/errors/exception.phtml";
            }
        }
        $content = ob_get_clean();

        // termina los buffers abiertos
        while (ob_get_level ()) {
            ob_end_clean();
        }

        // verifica si esta cargado el View
        if (class_exists('View')) {
            if (View::get('template') === NULL) {
                echo $content;
                exit;
            }
        }

        include CORE_PATH . $Template;
    }

}
