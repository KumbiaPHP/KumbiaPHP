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
 * @category  Kumbia
 * @package   Controller
 * @copyright Copyright (c) 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright Copyright (c) 2007-2009 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */
/**
 * ApplicationController Es la clase principal para controladores de Kumbia
 *
 * @category  Kumbia
 * @package   Controller
 * @copyright Copyright (c) 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright Copyright (c) 2007-2009 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */
class Controller 
{
	/**
	 * Modelos a cargar
	 *
	 * @var array
	 **/
	public $models = array();
	/**
	 * Indica si el controlador es persistente
	 *
	 * @var array
	 **/
	public $persistent = false;
	/**
	 * Indica el tipo de salida generada por el controlador
	 *
	 * @var string
	 */
	public $response = '';
    
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
	 * Nombre del primer parametro despues de action
	 * en la URL
	 *
	 * @var string
	 */
	public $id;
    /**
     * Template
     *
     * @var string
     */
    public $template = 'default';
	/**
	 * Numero de minutos que ser&aacute; cacheada la vista actual
	 *
	 * type: tipo de cache (view, template)
	 * time: tiempo de vida de cache
	 *
	 * @var array
	 */
	public $cache = array('type' => false, 'time' => false);
	/**
	 * Logger implicito del controlador
	 *
	 * @var string
	 */
	protected $logger;

	/**
	 * Vista a renderizar
	 *
	 * @var string
	 **/
	public $view = null;

	/**
	 * Constructor
	 *
	 **/
	public function __construct() 
	{
		$this->load_models();
	}

	/**
	 * Asigna cacheo de vistas o template
	 *
	 * @param $time tiempo de vida de cache
	 * @param $type tipo de cache (view, template)
	 */
	public function cache($time, $type='view')
    {
		if($time !== false) {
			$this->cache['type'] = $type;
			$this->cache['time'] = $time;
		} else {
			$this->cache['type'] = false;
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
	public function route_to()
    {
		$args = func_get_args();
    	return call_user_func_array(array('Router', 'route_to'), $args);
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
			return call_user_func_array(array('Filter', 'get'), $args);
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
			return call_user_func_array(array('Filter', 'get'), $args);
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
			return call_user_func_array(array('Filter', 'get'), $args);
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
		$success = true;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_POST[$f[0]][$f[1]]) ) {
				$success = false;
				break;
			} elseif(!isset($_POST[$f[0]])) {
				$success = false;
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
		$success = true;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_GET[$f[0]][$f[1]]) ) {
				$success = false;
				break;
			} elseif(!isset($_GET[$f[0]])) {
				$success = false;
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
		$success = true;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_REQUEST[$f[0]][$f[1]]) ) {
				$success = false;
				break;
			} elseif(!isset($_REQUEST[$f[0]])) {
				$success = false;
				break;
			}
		}
		return $success;
	}

	/**
	 * Sube un archivo al directorio img/upload si esta en $_FILES
	 *
	 * @param string $name
	 * @return string
	 */
	protected function upload_image($name, $new_name='')
    {
		if(isset($_FILES[$name])){
			if(!$new_name) {
				$new_name = $_FILES[$name]['name'];
			}
			move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars("public/img/upload/$new_name"));
			return urlencode(htmlspecialchars("upload/$new_name"));
		} else {
			return urlencode($this->request($name));
		}
	}

	/**
	 * Sube un archivo al directorio $dir si esta en $_FILES
	 *
	 * @param string $name
	 * @return string
	 */
	protected function upload_file($name, $dir, $new_name='')
    {
		if($_FILES[$name]){
			if(!$new_name) {
				$new_name = $_FILES[$name]['name'];
			}
			return move_uploaded_file($_FILES[$name]['tmp_name'], htmlspecialchars("$dir/$new_name"));
		} else {
			return false;
		}
	}

	/**
	 * Indica si un controlador va a ser persistente, en este
	 * caso los valores internos son automaticamente almacenados
	 * en sesion y disponibles cada vez que se ejecute una acción
	 * en el controlador
	 *
	 * @param boolean $value
	 */
	protected function set_persistance($value)
    {
		$this->persistance = $value;
	}

	/**
	 * Redirecciona la ejecución a otro controlador en un
	 * tiempo de ejecución determinado
	 *
	 * @param string $controller
	 * @param integer $seconds
	 */
	protected function redirect($controller, $seconds=0.5)
    {
		$seconds*=1000;
		if(headers_sent()){
			print "
				<script type='text/javascript'>
					window.setTimeout(\"window.location='".KUMBIA_PATH."$controller'\", $seconds);
				</script>\n";
		} else {
			header('Location: '.KUMBIA_PATH."$controller");
		}
	}

	/**
	 * Indica si el request es AJAX
	 *
	 *
	 * @return Bolean
	 */
	public function is_ajax()
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
	}

	/**
	 * Reescribir este metodo permite controlar las excepciones generadas en un controlador
	 *
	 * @param Exception $e
	 */
	protected function exceptions($exception)
    {
		throw $exception;
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
			$msg = print_r($msg, true);
		}
		if(!$this->logger){
			$this->logger = new Logger($this->controller_name.'.txt');
		}
		$this->logger->log($msg, $type);
	}


	/**
	 * Carga los campos de un registro activerecord como atributos del controlador
	 * @param record ActiveRecord o string registro activerecord a cargar, si es un string este debe corresponder al nombre de un modelo
	 * Soporta argumento variable
	 * 
	 * field: campos a cargar separados por coma
	 * except: campos que no se cargaran separados por coma
	 * suffix: sufijo para el atributo en el controlador
	 * preffix: prefijo para el atributo en el controlador
	 */
	protected function load_record($record) 
    {
		$params = get_params(func_get_args());
		if(isset($params['field'])) {
			$fields = array_map('trim', explode(',', $params['field']));
		}
		if(isset($params['except'])) {
			$excepts = array_map('trim', explode(',', $params['except']));
		}
		
		/**
		 * Cargo selectivamente por cada registro los atributos correspondientes a los campos
		 **/
		if(isset($fields) && isset($excepts)) {
			for($i=0; isset($params[$i]); $i++) {
				/**
				 * Si es un string creo una nueva instancia de modelo
				 **/
				if(is_string($params[$i])) {
					$model = ucfirst(camelize($params[$i]));
					$record = new $model();
					$record->dump_model();
				} else {
					$record = $params[$i];
				}
				
				foreach($fields as $field) {
					if(!in_array($field, $excepts)) {
						$property = $field;
						if(isset($params['suffix'])) {
							$property.=$params['suffix']; 
						}
						if(isset($params['preffix'])) {
							$property = $params['preffix'].$property; 
						}
						
						if(isset($record->$field)) {
							$this->$property = $record->$field;
						} else {
							$this->$property = '';
						}
					}
				}
			}
		} elseif(isset($fields)) {
			for($i=0; isset($params[$i]); $i++) {
				/**
				 * Si es un string creo una nueva instancia de modelo
				 **/
				if(is_string($params[$i])) {
					$model = ucfirst(camelize($params[$i]));
					$record = new $model();
					$record->dump_model();
				} else {
					$record = $params[$i];
				}
				
				foreach($fields as $field) {
					$property = $field;
					if(isset($params['suffix'])) {
						$property.=$params['suffix']; 
					}
					if(isset($params['preffix'])) {
						$property = $params['preffix'].$property; 
					}
					
					if(isset($record->$field)) {
						$this->$property = $record->$field;
					} else {
						$this->$property = '';
					}
				}
			}
		} elseif(isset($excepts)) {
			for($i=0; isset($params[$i]); $i++) {
				/**
				 * Si es un string creo una nueva instancia de modelo
				 **/
				if(is_string($params[$i])) {
					$model = ucfirst(camelize($params[$i]));
					$record = new $model();
					$record->dump_model();
				} else {
					$record = $params[$i];
				}
				
				foreach($record->fields as $field) {
					if(!in_array($field, $excepts)) {
						$property = $field;
						if(isset($params['suffix'])) {
							$property.=$params['suffix']; 
						}
						if(isset($params['preffix'])) {
							$property = $params['preffix'].$property; 
						}

						if(isset($record->$field)) {
							$this->$property = $record->$field;
						} else {
							$this->$property = '';
						}
					}
				}
			}
		} else {
			for($i=0; isset($params[$i]); $i++) {
				/**
				 * Si es un string creo una nueva instancia de modelo
				 **/
				if(is_string($params[$i])) {
					$model = ucfirst(camelize($params[$i]));
					$record = new $model();
					$record->dump_model();
				} else {
					$record = $params[$i];
				}
				
				foreach($record->fields as $field) {
					$property = $field;
					if(isset($params['suffix'])) {
						$property.=$params['suffix']; 
					}
					if(isset($params['preffix'])) {
						$property = $params['preffix'].$property; 
					}
					
					if(isset($record->$field)) {
						$this->$property = $record->$field;
					} else {
						$this->$property = '';
					}
				}
			}
		}
	}
	
	/**
	 * Asigna valor null a los atributos indicados en el controlador
	 */
	protected function nullify() 
    {
		$args = func_get_args();
		foreach($args as $f) {
			$this->$f = null;
		}
	}

	/**
	 * Aplica los filtros indicados
	 *
	 * @param mixed $s
	 * @return mixed
	 */
	protected function filter($s) 
    {
		$filter = Filter::get_instance();
		$args = func_get_args();
		return call_user_func_array(array($filter, 'apply_filter'), $args);
	}

	/**
	 * Al deserializar asigna 0 a los tiempos del cache
	 */
	public function __wakeup()
    {
		$this->logger = false;
		$this->cache(false);
		$this->view = null;
		$this->load_models();
	}
	/**
	 * Visualiza una vista en el controlador actual
	 *
	 * @param string $view
	 */
	public function render($view){
		$this->view = $view;
	}

	/**
	 * Visualiza una vista parcial en el controlador actual
	 *
	 * controller: controlador de donde tomara la vista
	 * @param string $partial parcial a mostrar, soporta formato controller/view
	 */
	protected function render_partial(){
		$params = func_get_args();
		call_user_func_array('render_partial',$params);
	}
    /**
     * BeforeFilter
     * 
     * @return bool
     */
    public function before_filter()
    {
        return;
    }
    /**
     * AfterFilter
     * 
     * @return bool
     */
    public function after_filter()
    {
        return;
    }
	/**
     * Initialize
     * 
     * @return bool
     */
    public function initialize()
    {
        return;
    }
    /**
     * Finalize
     * 
     * @return bool
     */
    public function finalize()
    {
        return;
    }
	/**
	 * Carga el modelo en el controlador
	 *
	 * @param string $model
	 **/
	public function load_models($model=null)
	{
		$models = $model ? func_get_args() : $this->models;
		foreach($models as $model) {
			if(!isset(Kumbia::$models[$model])) {
				if(!class_exists($model)) {
					$model_file = Kumbia::$active_models_dir.'/'.uncamelize(lcfirst($model)).'.php';
					if(!file_exists($model_file))
						throw new KumbiaException("No existe el modelo \"$model\"");
					require_once $model_file;
				}
				Kumbia::$models[$model] = new $model();
			}
			$this->$model = Kumbia::$models[$model];
		}
	}
}