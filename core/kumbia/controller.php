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
 * ApplicationController Es la clase principal para controladores de Kumbia
 * 
 * @category   Kumbia
 * @package    Controller 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Controller 
{
	/**
	 * Modelos a cargar
	 *
	 * @var array
	 **/
	public $models;
    /**
	 * Libs a cargar
	 *
	 * @var array
	 **/
	public $libs;
	/**
	 * Indica el tipo de salida generada por el controlador
	 *
	 * @var string
	 */
	public $response;
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
     * Template
     *
     * @var string
     */
    public $template = 'default';
	/**
	 * Número de minutos que será cacheada la vista actual
	 *
	 * type: tipo de cache (view, template)
	 * time: tiempo de vida de cache
	 *
	 * @var array
	 */
	public $cache = array('type' => FALSE, 'time' => FALSE, 'group'=>FALSE);
	/**
	 * Logger implicito del controlador
	 *
	 * @var string
	 */
	public $logger;
	/**
	 * Vista a renderizar
	 *
	 * @var string
	 **/
	public $view;
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
	 **/
	public function __construct($module, $controller, $action, $parameters, $controller_path) {
		//TODO: enviar un objeto
		$this->module_name = $module;
		$this->controller_name = $controller;
		$this->parameters = $parameters;
		$this->view = $this->action_name = $action;
		$this->controller_path = $controller_path;
        $this->cache['group'] = "$controller.$action";//.$id";

        // Carga los utils indicados
        if($this->libs) {
			Load::lib($this->libs);
        }
        
	}	
	/**
	 * Asigna cacheo de vistas o template
	 *
	 * @param $time tiempo de vida de cache
	 * @param $type tipo de cache (view, template)
	 */
	protected function cache($time, $type='view', $group=FALSE)
    {
		if($time !== FALSE) {
			$this->cache['type'] = $type;
			$this->cache['time'] = $time;
			$this->cache['group'] = $group;
		} else {
			$this->cache['type'] = FALSE;
		}
	}
	/**
	 * Hace el enrutamiento desde un controlador a otro, o desde
	 * una acción a otra.
	 *
	 * Ej:
	 * <code>
	 * return $this->route_to("controller: clientes", "action: consultar", "id: 1");
	 * </code>
	 *
	 */
	protected function route_to()
    {
    	return call_user_func_array(array('Router', 'route_to'), func_get_args());
	}

	/**
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @param string $param_name
	 * @return mixed
	 */
	protected function post($param_name)
    {
		/**
		 * Verifica si posee el formato form.field, en ese caso accede al array $_POST['form']['field']
		 **/
		$param_name = explode('.', $param_name);
		if(count($param_name)>1) {
			$value = isset($_POST[$param_name[0]][$param_name[1]]) ? $_POST[$param_name[0]][$param_name[1]] : '';
		} else {
			$value = isset($_POST[$param_name[0]]) ? $_POST[$param_name[0]] : '';
		}
	
		/**
		 * Si hay mas de un argumento, toma los demas como filtros
		 */
		if(func_num_args()>1){
			$args = func_get_args();
			$args[0] = $value;
            
            if(is_string($value)) {
                return call_user_func_array(array('Filter', 'get'), $args);
            } else {
                return call_user_func_array(array('Filter', 'get_array'), $args);
            }
		}
		return $value;
	}

	/**
	 * Obtiene un valor del arreglo $_GET
	 *
	 * @param string $param_name
	 * @return mixed
	 */
	protected function get($param_name)
    {
		/**
		 * Verifica si posee el formato form.field, en ese caso accede al array $_GET['form']['field']
		 **/
		$param_name = explode('.', $param_name);
		if(count($param_name)>1) {
			$value = isset($_GET[$param_name[0]][$param_name[1]]) ? $_GET[$param_name[0]][$param_name[1]] : '';
		} else {
			$value = isset($_GET[$param_name[0]]) ? $_GET[$param_name[0]] : '';
		}
	
		/**
		 * Si hay mas de un argumento, toma los demas como filtros
		 */
		if(func_num_args()>1){
			$args = func_get_args();
			$args[0] = $value;
            
            if(is_string($value)) {
                return call_user_func_array(array('Filter', 'get'), $args);
            } else {
                return call_user_func_array(array('Filter', 'get_array'), $args);
            }
		}
		return $value;
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
		 **/
		$param_name = explode('.', $param_name);
		if(count($param_name)>1) {
			$value = isset($_REQUEST[$param_name[0]][$param_name[1]]) ? $_REQUEST[$param_name[0]][$param_name[1]] : '';
		} else {
			$value = isset($_REQUEST[$param_name[0]]) ? $_REQUEST[$param_name[0]] : '';
		}
	
		/**
		 * Si hay mas de un argumento, toma los demas como filtros
		 */
		if(func_num_args()>1){
			$args = func_get_args();
			$args[0] = $value;

            if(is_string($value)) {
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
	 * @param string $s elemento a verificar (soporta varios elementos simultaneos)
	 * @return boolean
	 **/
	protected function has_post($s) 
    {
		$success = TRUE;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_POST[$f[0]][$f[1]]) ) {
				$success = FALSE;
				break;
			} elseif(!isset($_POST[$f[0]])) {
				$success = FALSE;
				break;
			}
		}
		return $success;
	}

	/**
	 * Verifica si existe el elemento indicado en $_GET
	 *
	 * @param string $s elemento a verificar (soporta varios elementos simultaneos)
	 * @return boolean
	 **/
	protected function has_get($s) 
    {
		$success = TRUE;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_GET[$f[0]][$f[1]]) ) {
				$success = FALSE;
				break;
			} elseif(!isset($_GET[$f[0]])) {
				$success = FALSE;
				break;
			}
		}
		return $success;
	}

	/**
	 * Verifica si existe el elemento indicado en $_REQUEST
	 *
	 * @param string $s elemento a verificar (soporta varios elementos simultaneos)
	 * @return boolean
	 **/
	protected function has_request($s) 
    {
		$success = TRUE;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_REQUEST[$f[0]][$f[1]]) ) {
				$success = FALSE;
				break;
			} elseif(!isset($_REQUEST[$f[0]])) {
				$success = FALSE;
				break;
			}
		}
		return $success;
	}

	/**
	 * Redirecciona la ejecución a otro controlador en un
	 * tiempo de ejecución determinado
	 * DEPRECATED
	 * @param string $controller
	 * @param integer $seconds
	 */
	protected function redirect($controller, $seconds=NULL)
    {
		Router::redirect($controller,$seconds);
		//if(!$seconds) self::render(NULL,NULL);
	}

	/**
	 * Indica si el request es AJAX
	 *
	 *
	 * @return Bolean
	 */
	protected function is_ajax()
    {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}

	/**
	 * Indica el tipo de Respuesta dada por el controlador
	 *
	 * @param string $type
	 */
	protected function set_response($type)
    {
		$this->response = $type;
		if ($type != 'view'){ 
			$this->controller_path = "{$this->controller_path}/_$type";
		}
	}

	/**
	 * Crea un log sino existe y guarda un mensaje
	 *
	 * @param string $msg
	 * @param integer $type
	 */
	protected function log($msg, $type=Logger::DEBUG)
    {
		if(is_array($msg)){
			$msg = print_r($msg, TRUE);
		}
		if(!$this->logger){
			$this->logger = new Logger($this->controller_name.'.txt');
		}
		$this->logger->log($msg, $type);
	}
	
	/**
	 * Asigna valor NULL a los atributos indicados en el controlador
     *
     *  @param string $var
	 */
	protected function nullify($var) 
    {
		$args = func_get_args();
		foreach($args as $f) {
			$this->$f = NULL;
		}
	}
	/**
	 * Visualiza una vista en el controlador actual
	 *
	 * @param string $view nombre del view a utilizar sin .phtml
	 * @param string $template	opcional nombre del template a utilizar sin .phtml
	 */
	protected function render($view,$template = FALSE){
		$this->view = $view;
		if($template === FALSE) return;
		$this->template = $template;
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
     * Finalize
     * 
     * @return bool
     */
    protected function finalize()
    {
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
	 **/
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
     **/
	protected function get_persistent($var)
	{
        if(isset($_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var])) {
            return $_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var];
        } 
        return NULL;
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
    	    if(isset($_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var])) {
                unset($_SESSION['KUMBIA_CONTROLLER']["$this->module_name/$this->controller_name"][$var]);
            }
	    }
	}
	/**
	 * ejecuta los callback filter
	 *
	 * @param string $method
	 * @return void
	 */
    final public function k_callback($method) 
    { 
        return $this->$method(); 
    }
}