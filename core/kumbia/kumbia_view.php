<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   View
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Renderer de vistas.
 *
 * @category   View
 */
class KumbiaView
{
    /**
     * Contenido.
     *
     * @var string|null
     */
    protected static $_content;
    /**
     * Vista a renderizar.
     *
     * @var string
     * */
    protected static $_view;
    /**
     * Template.
     *
     * @var string|null
     */
    protected static $_template = 'default';
    /**
     * Indica el tipo de salida generada por el controlador.
     *
     * @var string
     */
    protected static $_response;
    /**
     * Indica el path al que se le añadira la constante correspondiente.
     *
     * @var string
     */
    protected static $_path;
    /**
     * Número de minutos que será cacheada la vista actual.
     *
     * type: tipo de cache (view, template)
     * time: tiempo de vida de cache
     *
     * @var array
     */
    protected static $_cache = array('type' => false, 'time' => false, 'group' => false);

    /**
     * Datos del Controlador actual.
     *
     * @var array
     */
    protected static $_controller;

    /**
     * Cambia el view y opcionalmente el template.
     *
     * @param string|null    $view     nombre del view a utilizar sin .phtml
     * @param string|null    $template opcional nombre del template a utilizar sin .phtml
     * 
     * @return void
     */
    public static function select($view, $template = '')
    {
        self::$_view = $view;

        // verifica si se indico template
        if ($template !== '') {
            self::$_template = $template;
        }
    }

    /**
     * Asigna el template para la vista.
     *
     * @param string|null $template nombre del template a utilizar sin .phtml
     * 
     * @return void
     */
    public static function template($template)
    {
        self::$_template = $template;
    }

    /**
     * Indica el tipo de Respuesta dada por el controlador
     * buscando el view con esa extension.
     * ej. View::response('xml');
     * buscara: views/controller/action.xml.phtml.
     *
     * @param string        $response
     * @param string|null   $template Opcional nombre del template sin .phtml
     * 
     * @return void
     */
    public static function response($response, $template = null)
    {
        self::$_response = $response;

        // verifica si se indico template
        if ($template !== null) {
            self::$_template = $template;
        }
    }

    /**
     * Asigna el path de la vista.
     *
     * @param string $path path de la vista sin extension .phtml
     * 
     * @return void
     */
    public static function setPath($path)
    {
        self::$_path = $path.'/';
    }

    /**
     * Obtiene el path para vista incluyendo la extension .phtml.
     *
     * @return string
     */
    public static function getPath()
    {
        if (self::$_response) {
            return self::$_path.self::$_view.'.'.self::$_response.'.phtml';
        }

        return self::$_path.self::$_view.'.phtml';
    }

    /**
     * Obtiene un atributo de KumbiaView.
     *
     * @param string $atribute nombre de atributo (template, response, path, etc)
     * 
     * @return mixed
     */
    public static function get($atribute)
    {
        return self::${"_$atribute"};
    }

    /**
     * Asigna cacheo de vistas o template.
     *
     * @param string|null  $time Tiempo de vida de cache
     * @param string        $type Tipo de cache (view, template)
     * @param string        $group Grupo de pertenencia de cache
     *
     * @return bool En producción y cache de view
     */
    public static function cache($time, $type = 'view', $group = 'kumbia.view')
    {
        if ($time === null) { //TODO borrar cache
            return self::$_cache['type'] = false;
        }
        self::$_cache['type'] = $type;
        self::$_cache['time'] = $time;
        self::$_cache['group'] = $group;
        //Si está en producción para view
        if (PRODUCTION && $type === 'view') {
            return self::getCache(); //TRUE si está cacheada
        }

        return false;
    }

    /**
     * Obtiene la cache de view.
     *
     * @return bool
     */
    protected static function getCache()
    {
        // el contenido permanece nulo si no hay nada cacheado o la cache expiro
        self::$_content = Cache::driver()->get(Router::get('route'), self::$_cache['group']);

        return self::$_content !== null;
    }

    /**
     * Obtiene el view.
     *
     * @return string path del view
     */
    protected static function getView()
    {
        $file = APP_PATH.'views/'.self::getPath();
        //Si no existe el view y es scaffold
        if (!is_file($file) && ($scaffold = self::$_controller['scaffold'])) {
            $file = APP_PATH."views/_shared/scaffolds/$scaffold/".self::$_view.'.phtml';
        }

        return $file;
    }

    /**
     * Cachea el view o template.
     *
     * @param string $type view o template
     * 
     * @return void
     */
    protected static function saveCache($type)
    {
        // si esta en produccion y se cachea la vista
        if (PRODUCTION && self::$_cache['type'] === $type) {
            Cache::driver()->save(ob_get_contents(), self::$_cache['time'], Router::get('route'), self::$_cache['group']);
        }
    }

    /**
     * Renderiza la vista.
     *
     * @param Controller $controller
     * 
     * @return void
     */
    public static function render(Controller $controller)
    {
        if (!self::$_view && !self::$_template) {
            ob_end_flush();

            return; 
        }

        // Guarda los datos del controlador y los envia
        self::generate(self::$_controller = get_object_vars($controller));
    }

    /**
     * Genera la vista.
     *
     * @param array $controller
     * 
     * @return void
     */
    protected static function generate($controller)
    {
        // Registra la autocarga de helpers
        spl_autoload_register('kumbia_autoload_helper', true, true);
        // Mapea los atributos del controller en el scope
        extract($controller, EXTR_OVERWRITE);

        // carga la vista si tiene view y no esta cacheada
        if (self::$_view && self::$_content === null) {
            // Carga el contenido del buffer de salida
            self::$_content = ob_get_clean();
            // Renderizar vista
            ob_start();

            // carga la vista
            if (!include self::getView()) {
                throw new KumbiaException('Vista "'.self::getPath().'" no encontrada', 'no_view');
            }

            // si esta en produccion y se cachea la vista
            self::saveCache('view');

            // Verifica si hay template
            if (!self::$_template) {
                ob_end_flush();

                return;
            }

            self::$_content = ob_get_clean();
            ob_clean();
        }

        // Renderizar template
        if ($__template = self::$_template) {
            ob_start();

            // carga el template
            if (!include APP_PATH."views/_shared/templates/$__template.phtml") {
                throw new KumbiaException("Template $__template no encontrado");
            }

            // si esta en produccion y se cachea template
            self::saveCache('template');
            ob_end_flush();

            return;
        }

        echo self::$_content;
    }

    /**
     * Imprime el contenido del buffer.
     * 
     * @return void
     */
    public static function content()
    {
        if (isset($_SESSION['KUMBIA.CONTENT'])) {
            echo $_SESSION['KUMBIA.CONTENT'];
            unset($_SESSION['KUMBIA.CONTENT']);
        }
        echo self::$_content;
    }

    /**
     * Renderiza una vista parcial.
     *
     * @throw KumbiaException
     * @param  string            $partial vista a renderizar
     * @param  string            $__time  tiempo de cache
     * @param  array|string|null $params  variables para el partial
     * @param  string            $group   grupo de cache
     * @return void
     */
    public static function partial($partial, $__time = '', $params = null, $group = 'kumbia.partials')
    {
        if (PRODUCTION && $__time && !Cache::driver()->start($__time, $partial, $group)) {
            return;
        }

        //Verificando el partials en el dir app
        $__file = APP_PATH."views/_shared/partials/$partial.phtml";

        if (!is_file($__file)) {
            //Verificando el partials en el dir core
            $__file = CORE_PATH."views/partials/$partial.phtml";
        }

        if ($params) {
            if (is_string($params)) {
                $params = Util::getParams(explode(',', $params));
            }

            // carga los parametros en el scope
            extract($params, EXTR_OVERWRITE);
        }

        // carga la vista parcial
        if (!include $__file) {
            throw new KumbiaException('Vista Parcial "'.$__file.'" no encontrada', 'no_partial');
        }

        // se guarda en la cache de ser requerido
        if (PRODUCTION && $__time) {
            Cache::driver()->end();
        }
    }

    /**
     * Obtiene el valor de un atributo público o todos del controlador.
     *
     * @param string $var nombre de variable
     *
     * @return mixed valor de la variable
     */
    public static function getVar($var = '')
    {
        if (!$var) {
            return self::$_controller;
        }

        return isset(self::$_controller[$var]) ? self::$_controller[$var] : null;
    }
}

/**
 * Atajo para htmlspecialchars, por defecto toma el charset de la
 * aplicacion.
 *
 * @param string $string
 * @param string $charset
 *
 * @return string
 */
function h($string, $charset = APP_CHARSET)
{
    return htmlspecialchars($string, ENT_QUOTES, $charset);
}
