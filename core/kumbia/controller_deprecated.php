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
 * @package    ControllerDeprecated
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * ControllerDeprecated es la clase base de ApplicationController
 *
 * ApplicationController extiende de esta clase
 * Se eliminará despues de la beta2.
 * Se mantiene para portar apps fácilmente de 0.5 y beta1
 *
 * @category   Kumbia
 * @package    ControllerDeprecated
 * @deprecated Antiguo controller (legacy) Se eliminará despues de la beta2
 * @todo  En salir la beta2 se eliminará
 */
class ControllerDeprecated
{

    /**
     * Modelos a cargar
     *
     * @var array
     *
     * @ deprecated
     * */
    public $models;
    /**
     * Libs a cargar
     *
     * @var array
     *
     * @ deprecated
     * */
    public $libs;
    /**
     * Modelos cargados
     *
     * @var array
     * @ deprecated
     */
    private $_loaded_models = array();
    /**
     * Nombre del modulo actual
     *
     * @var string
     */
    public $module_name;
    /**
     * Nombre del controlador actual
     *
     * @var string
     */
    public $controller_name;
    /**
     * Nombre de la acción actual
     *
     * @var string
     */
    public $action_name;
    /**
     * Limita la cantidad correcta de
     * parametros de una action
     *
     * @var bool
     */
    public $limit_params = TRUE;
    /**
     * Nombre del scaffold a usar
     *
     * @var string
     */
    public $scaffold;

    /**
     * Constructor
     *
     * @param string $module modulo al que pertenece el controlador
     * @param string $controller nombre del controlador
     * @param string $action nombre de la accion
     * @param array $parameters parametros enviados por url
     * */
    public function __construct($module, $controller, $action, $parameters)
    {
        //TODO: enviar un objeto
        $this->module_name = $module;
        $this->controller_name = $controller;
        $this->parameters = $parameters;
        $this->action_name = $action;
        //$this->cache['group'] = "$controller.$action";//.$id";
        //deprecated
        if ($this->libs) {
            // Carga las librerias indicadas
            foreach ($this->libs as $lib) {
                Load::lib($lib);
            }
        }

        //Carga de modelos
        if ($this->models) {
            call_user_func_array(array($this, 'models'), $this->models);
        }
    }

    /**
     * Carga los modelos
     *
     * @param string $model
     */
    protected function models($model)
    {
        $args = func_get_args();
        foreach ($args as $model) {
            $file = APP_PATH . "models/$model.php";
            if (is_file($file)) {
                include_once $file;
                $Model = Util::camelcase(basename($model));
                $this->$Model = new $Model();
                $this->_loaded_models[] = $Model;
            } elseif (is_dir(APP_PATH . "models/$model")) {
                foreach (new DirectoryIterator(APP_PATH . "models/$model") as $file) {
                    if ($file->isDot() || $file->isDir()) {
                        continue;
                    }
                    if ($file->isFile()) {
                        include_once $file->getPathname();
                        $Model = Util::camelcase(basename($file->getFilename(), '.php'));
                        $this->$Model = new $Model();
                        $this->_loaded_models[] = $Model;
                    }
                }
            } else {
                throw new KumbiaException("Modelo $model no encontrado");
            }
        }
    }

    /**
     * Asigna cacheo de vistas o template
     *
     * @param $time tiempo de vida de cache
     * @param $type tipo de cache (view, template)
     *
     * @deprecated Ahora se usa <code>View::cache()</code>, ya que esta cache es de view
     */
    protected function cache($time, $type = 'view', $group = FALSE)
    {
        View::cache($time, $type, $group);
    }

    /**
     * Hace el enrutamiento desde un controlador a otro, o desde
     * una acción a otra.
     *
     * Ej:
     * <code>
     * return $this->route_to("controller: clientes", "action: consultar", "id: 1");
     * </code>
     * @deprecated Mejor usar return Router::route_to()
     */
    protected function route_to()
    {
        Router::route_to(implode(',', func_get_args()));
        //call_user_func_array(array('Router', 'route_to'), func_get_args());
    }

    /**
     * Obtiene un valor del arreglo $_POST
     *
     * @param string $var
     * @return mixed
     */
    protected function post($var)
    {
        // Si hay mas de un argumento, toma los demas como filtros
        if (func_num_args() > 1) {
            return call_user_func_array(array('Request', 'filter'), func_get_args());
        }
        return Input::post($var);
    }

    /**
     * Obtiene un valor del arreglo $_GET
     *
     * @param string $param_name
     * @return mixed
     */
    protected function get($variable = NULL)
    { //FILTER_SANITIZE_STRING
        if ($variable) {
            
        } else {
            $value = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        }
        //$value = filter_has_var(INPUT_GET, $variable) ? $_GET[$variable] : NULL;

        /**
         * Si hay mas de un argumento, toma los demas como filtros
         */
        if (func_num_args() > 1) {
            $args = func_get_args();
            $args[0] = $value;

            if (is_string($value)) {
                return call_user_func_array(array('Filter', 'get'), $args);
            } else {
                return call_user_func_array(array('Filter', 'get_array'), $args);
            }
        }
        return Input::get($variable);
    }

    /**
     * Obtiene un valor del arreglo $_REQUEST
     *
     * @param string $param_name
     * @return mixed
     */
    protected function request($param_name)
    {
        /**
         * Verifica si posee el formato form.field, en ese caso accede al array $_REQUEST['form']['field']
         * */
        $param_name = explode('.', $param_name);
        if (count($param_name) > 1) {
            $value = isset($_REQUEST[$param_name[0]][$param_name[1]]) ? $_REQUEST[$param_name[0]][$param_name[1]] : NULL;
        } else {
            $value = isset($_REQUEST[$param_name[0]]) ? $_REQUEST[$param_name[0]] : NULL;
        }

        /**
         * Si hay mas de un argumento, toma los demas como filtros
         */
        if (func_num_args() > 1) {
            $args = func_get_args();
            $args[0] = $value;

            if (is_string($value)) {
                return call_user_func_array(array('Filter', 'get'), $args);
            } else {
                return call_user_func_array(array('Filter', 'get_array'), $args);
            }
        }
        return $value;
    }

    /**
     * Verifica si existe el elemento indicado en $_POST
     *
     * @param string elemento a verificar
     * @return boolean
     *
     * @deprecated Ahora se usa <code>Input::hasPost()</code>
     * */
    protected function has_post($var)
    {
        return Input::hasPost($var);
    }

    /**
     * Verifica si existe el elemento indicado en $_GET
     *
     * @param string elemento a verificar
     * @return boolean
     *
     * @deprecated Ahora se usa <code>Input::hasGet()</code>
     * */
    protected function has_get($var)
    {
        return Input::hasGet($var);
    }

    /**
     * Verifica si existe el elemento indicado en $_REQUEST
     *
     * @param string elemento a verificar (soporta varios elementos simultaneos)
     * @return boolean
     *
     * @deprecated Ahora se usa <code>Input::hasRequest()</code>
     * */
    protected function has_request($var)
    {
        return Input::hasRequest($var);
    }

    /**
     * Redirecciona la ejecución a otro controlador en un
     * tiempo de ejecución determinado
     *
     * @param string $controller
     * @param integer $seconds
     *
     * @deprecated Ahora se usa <code>return Router::redirect()</code>
     */
    protected function redirect($controller, $seconds=NULL)
    {
        Router::redirect($controller, $seconds);
    }

    /**
     * Indica si el request es AJAX
     *
     * @return Bolean
     * @deprecated Ahora se usa <code>Input::isAjax()</code>
     */
    protected function is_ajax()
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * Indica el tipo de Respuesta dada por el controlador
     *
     * @param string $type
     *
     * @deprecated Ahora se usa <code>View::response()</code>
     */
    protected function set_response($type, $template = FALSE)
    {
        View::response($type, $template);
    }

    /**
     * Visualiza una vista en el controlador actual
     *
     * @param string $view nombre del view a utilizar sin .phtml
     * @param string $template	opcional nombre del template a utilizar sin .phtml
     *
     * @deprecated Ahora se usa <code>View::select()</code>
     */
    protected function render($view, $template = FALSE)
    {
        View::select($view, $template);
    }

    /**
     * BeforeFilter
     *
     * @return bool
     */
    protected function before_filter()
    {
        
    }

    /**
     * AfterFilter
     *
     * @return bool
     */
    protected function after_filter()
    {
        
    }

    /**
     * Initialize
     *
     * @return bool
     */
    protected function initialize()
    {
        
    }

    /**
     * Finalize, si se usa tambien en el Application Controller se debe llamar a este tambien
     * parent::finalize()
     * 
     * @return bool
     */
    protected function finalize()
    {
        //Elimino del controlador los modelos inyectados
        foreach ($this->_loaded_models as $model) {
            unset($this->$model);
        }

        //Limpia el buffer de modelos inyectados
        $this->_loaded_models = array();

        if (isset($this->template)) {
            View::template($this->template);
        }
        //if(isset($this->view)) {
        //	View::select($this->view);
        //}
    }

    /**
     * Persistencia de datos en el controlador
     *
     * @param string $var
     * @param string $value
     * @return mixed
     *
     * Ejemplos:
     * Haciendo persistente un dato
     *    $this->set_persistent('data', 'valor');
     * */
    protected function set_persistent($var, $value=NULL)
    {
        $_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var] = $value;
    }

    /**
     * Obtiene la Persistencia de datos en el controlador
     *
     * @param string $var
     * @return mixed
     *
     * Ejemplos:
     *
     * Leyendo el dato persistente
     *    $valor = $this->get_persistent('data');
     * */
    protected function get_persistent($var)
    {
        return $_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var];
    }

    /**
     * Destruye la persistencia de un Dato en el controller
     *
     * @param string $var
     */
    protected function destroy_persistent($var)
    {
        $args = func_get_args();
        foreach ($args as $var) {
            if (isset($_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var])) {
                unset($_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var]);
            }
        }
    }

    /**
     * Ejecuta los callback filter
     *
     * @param boolean $init filtros de inicio
     * @return void
     */
    final public function k_callback($init = FALSE)
    {
        if ($init) {
            if ($this->initialize() !== FALSE) {
                return $this->before_filter();
            }
            return FALSE;
        }

        $this->after_filter();
        $this->finalize();
    }

}
