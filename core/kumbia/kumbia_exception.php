<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 *
 * @copyright  Copyright (c) 2005 - 2021 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase principal para el manejo de excepciones.
 *
 * @category   Kumbia
 */
class KumbiaException extends Exception
{
    /**
     * View de error de la Excepción.
     *
     * @var string
     */
    protected $view = 'exception';

    /**
     * Error 404 para los siguientes views.
     *
     * @var array
     */
    protected static $view404 = ['no_controller', 'no_action', 'num_params', 'no_view'];

    /**
     * Path del template de exception.
     *
     * @var string
     */
    protected $template = 'views/templates/exception.phtml';

    /**
     * Constructor de la clase;.
     *
     * @param string $message mensaje
     * @param string $view    vista que se mostrara
     */
    public function __construct(string $message = '', string $view = 'exception')
    {
        $this->view = $view;
        parent::__construct($message);
    }

    /**
     * Maneja las excepciones no capturadas.
     *
     * @param Exception|KumbiaException $e
     * 
     * @return void
     * */
    public static function handleException($e)
    {
        self::setStatus($e);
        if (PRODUCTION || self::untrustedIp()) {
            self::cleanBuffer();
            include APP_PATH.'views/_shared/errors/404.phtml';

            return;
        }
        // show developer info in development and trusted IPs
        self::showDev($e);
    }

    /**
     * Is not localhost or trusted ip ?
     *
     * @return bool
     */
    private static function untrustedIp(): bool
    {
        $trusted = ['127.0.0.1', '::1']; // Localhost ip
        // check for old aplications
        if (is_file(APP_PATH.'config/exception.php')) {
            $trusted = array_merge( $trusted, (array) Config::get('exception.trustedIp'));
        }
        
        return !in_array($_SERVER['REMOTE_ADDR'], $trusted);
    }

    /**
     * Maneja las excepciones no capturadas.
     *
     * @param Exception|KumbiaException $e
     * 
     * @return void
     * */
    private static function showDev($e)
    {
        $data = Router::get();
        array_walk_recursive($data, function (&$value) {
                $value = htmlspecialchars($value, ENT_QUOTES, APP_CHARSET);
            });
        extract($data, EXTR_OVERWRITE);
        // Registra la autocarga de helpers
        spl_autoload_register('kumbia_autoload_helper', true, true);

        $Controller = Util::camelcase($controller);
        ob_start();
        
        $view = $e instanceof self ? $e->view : 'exception';
        $tpl =  $e instanceof self ? $e->template : 'views/templates/exception.phtml';
        //Fix problem with action name in REST
        $action = $e->getMessage() ?: $action;
        $action = htmlspecialchars($action, ENT_QUOTES, APP_CHARSET);

        include CORE_PATH."views/errors/{$view}.phtml";

        $content = ob_get_clean();
        self::cleanBuffer();
        include CORE_PATH.$tpl;
    }

    /**
     * cleanBuffer
     * termina los buffers abiertos.
     */
    private static function cleanBuffer()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * Añade el status de error http.
     *
     * @param Exception $e
     * */
    private static function setStatus($e)
    {
        if ($e instanceof self && in_array($e->view, self::$view404)) {
            http_response_code(404);

            return;
        }
        http_response_code(500);  
    }
}
