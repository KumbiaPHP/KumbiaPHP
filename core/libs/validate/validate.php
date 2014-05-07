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
 * Validate es una Clase que realiza validaciones Lógicas
 * 
 * @category   KumbiaPHP
 * @package    validate 
 * @copyright  Copyright (c) 2005-2014 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
require dirname(__FILE__).'/validations.php';
class Validate
{
    /**
     * Objeto a validar
     * @var Object
     */
    protected $obj = null;

    /**
     * Mensajes de error almacenados
     * @var array
     */
    protected $messages = array();

    /**
     * Reglas a a seguir para la validación
     * @var array
     */
    protected $rules = array();
	/**
	 * Contructor
	 * @param Object $obj Objeto a validar
	 */
	
	/**
	 * Almacena si la variable a validar es un objeto antes de convertirlo
	 * @var boolean
	 */
	protected $is_obj = false;
    
    /**
     * El parametro $rules debe contener esta forma
     *  array(
     *   'user' => //este es el nombre del campo
     *      array(
     *          'alpha' =>  //nombre del filtro
     *          null, //parametros pasados (en array o null si no se requiere)
     *          'lenght' => array('min'=>4, 'max'=>10)
     *      )
     * )
     * @param mixed $obj Objecto o Array a validar
     * @param array $rules Aray de reglas a validar
     */
    public function __construct($obj, Array $rules){
    	$this->is_obj = is_object($obj);
    	$this->obj = (object)$obj;
    	$this->rules = $rules;
    }

    /**
     * Ejecuta las validaciones
     * @return bool Devuelve true si todo es válido
     */
    public function exec(){
    	/*Recorrido por todos los campos*/
    	foreach ($this->rules as $field => $fRule){
    		$value = self::getValue($this->obj, $field);
    		/*Regla individual para cada campo*/
    		foreach ($fRule as $ruleName => $param) {
                $ruleName = self::getRuleName($ruleName, $param);
    			$param =  self::getParams($param);
    			/*Es una validación de modelo*/
    			if($ruleName[0] == '@'){
                    $this->modelRule($ruleName, $param, $field);
    			}elseif(!Validations::$ruleName($value, $param)){
    				$this->addError($param, $field);
    			}
    		}
    	}
    	/*Si no hay errores devuelve true*/
    	return empty($this->messages);
    }

    /**
     * Ejecuta una validación de modelo
     * @param string $rule nombre de la regla
     * @param array $param
     * @param strin $field Nombre del campo
     * @return bool 
     */
    protected function modelRule($rule, $param, $field){
        if(!$this->is_obj){
            trigger_error('No se puede ejecutar una validacion de modelo en un array', E_USER_WARNING);
            return false;
        }
        $ruleName = ltrim($rule, '@');
        $obj = $this->obj;
        if(!method_exists($obj, $ruleName)){
            trigger_error('El metodo para la validacion no existe', E_USER_WARNING);
            return false;
        }
        if(!$obj->$ruleName($field, $param)){ 
           $this->addError($param, $field);
        }
        return true;
    }

    /**
     * Agrega un nuevo error
     * @param Array $param parametros
     * @param string Nombre del campo
     */
    protected function addError(Array $param, $field){
         $this->messages[] = isset($param['error']) ?
                $param['error']: "El campo '$field' no es válido";
    }

    /**
     * Devuelve el nombre de la regla
     * @param string $rulename
     * @param mixed $param
     * @return string
     */
    protected static function getRuleName($ruleName, $param){
         /*Evita tener que colocar un null cuando no se pasan parametros*/
        return is_integer($ruleName) && is_string($param)?$param:$ruleName;
    }

    /**
     * Devuelve los parametros para la regla
     * @param mixed $param
     * @return array
     */
    protected static function getParams($param){
        return is_array($param)?$param:array();
    }

    /**
     * Devuelve el valor de un campo
     * @param object $obj
     * @param string $field
     * @return mixed
     */
    protected static function getValue($obj, $field){
        return !empty($obj->$field)?$obj->$field:null;//obtengo el valor del campo
    }

    /**
     * Devuelve los mensajes de error
     * 
     */
    public function getMessages(){
        return $this->messages;
    }

    public static function fail($obj, Array $rules){
        $val = new self($obj, $rules);
        return $val->exec() ? false:$val->getMessages();
    }
}
