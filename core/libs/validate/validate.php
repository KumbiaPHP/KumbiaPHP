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
class Validate
{
	/**
	 * Constantes para definir los patrones
	 */
  
	/*
	 * El valor deber ser solo letras y números
	 */
	const IS_ALPHANUM = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
	 
    /**
     * Almacena la Expresion Regular
     *
     * @var String
     */
    public static $regex = NULL;

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
    		$value = static::getValue($this->obj, $field);
    		/*Regla individual para cada campo*/
    		foreach ($fRule as $ruleName => $param) {
                $ruleName = static::getRuleName($ruleName, $param);
    			$param =  static::getParams($param);
    			/*Es una validación de modelo*/
    			if($ruleName[0] == '@'){
                    $this->modelRule($ruleName, $param, $field);
    			}elseif(!self::$ruleName($value, $param)){ 
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
        if(!$obj->$ruleName($param)){ 
           $this->addError($param, $field);
        }

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

    protected static function getValue($obj, $field){
        return isset($obj->$field)?$obj->$field:null;//obtengo el valor del campo
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


    /**
     * Valida que sea numérico
     * @param  mixed $check Valor a ser chequeado
     * @return bool        
     */
    public static function numeric($check){
        return is_numeric($check);
    }

    /**
     * Valida que int
     *
     * @param int $check
     * @return bool
     */
    public static function int($check)
    {
        return filter_var($check, FILTER_VALIDATE_INT);
    }
    
    /**
     * Valida que una cadena este entre un rango.
     * Los espacios son contados
     * Retorna true si el string $value se encuentra entre min and max
     *
     * @param string $value
     * @param array $param
     * @return bool
     */
    public static function maxlength($value, $param)
    {
        $max= isset($param['max'])?$param['max']:0;
        return !isset($value[$max]);
    }

    /**
     * Valida que es un número se encuentre 
     * en un rango minímo y máximo
     * 
     * @param int $value
     * @param int $min
     * @param int $max
     */
    public static function range($value, $param)
    {
        $min = isset($param['min']) ? $param['min'] : 0;
        $max = isset($param['max']) ? $param['max'] : 10;
        $int_options = array('options' => array('min_range'=>$min, 'max_range'=>$max));
        return filter_var($value, FILTER_VALIDATE_INT, $int_options);
    }

    /**
     * Valida que un valor se encuentre en una lista
     * Retorna true si el string $value se encuentra en la lista $list
     *
     * @param string $value
     * @param array $param
     * @return bool
     */
    public static function selet($value, $param)
    {
        $list = isset($param['list']) && is_array($param['list']) ? $param['list'] : array();
        return in_array($value, $list);
    }
    
    /**
     * Valida que una cadena sea un mail
     * @param string $mail
     * @return bool
     */
    public static function email($mail)
    {
        return filter_var($mail, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Valida URL
     *
     * @param string $url
     * @return bool
     */
    public static function url($url, $param)
    {
        $flag = isset($param['flag'])? $param['flag'] : 0;
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED | $flag);
    }
    
    /**
     * Valida que sea una IP, por defecto v4
     * TODO: Revisar este método
     * @param String $ip
     * @return bool
     */
    public static function ip($ip, $flags = FILTER_FLAG_IPV4)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }
    
    /**
     * Valida que un string no sea null
     *
     * @param string $check
     * @return bool
     */
    public static function required($check)
    {
        return !empty($check) && $check!='0';
    }
    
    /**
     * Valida que un String sea alpha-num (incluye caracteres acentuados)
     * TODO: Revisar este método
     * 
     * @param string $string
     * @return bool
     */
    public static function alphanum($string)
    {
        return self::pattern($string, self::IS_ALPHANUM);
    }
    
    /**
     * Valida una fecha
     * TODO: REVISAR EL ESTANDAR
     * @param string $value fecha a validar acorde al formato indicado
     * @param string $format formato de fecha. acepta: d-m-y, y-m-d, m-d-y, donde el "-" puede ser cualquier caracter 
     *                       de separacion incluso un espacio en blanco o ".", exceptuando (d,m,y o números).
     * @return boolean
     */
    public static function date($value, $format = 'd-m-y')
    {
        // busca el separador removiendo los caracteres de formato
        $separator = str_replace(array('d' , 'm' , 'y'), '', $format);
        $separator = $separator[0]; // el separador es el primer caracter
        if ($separator && substr_count($value, $separator) == 2) {
            switch (str_replace($separator, '', $format)) {
                case 'dmy':
                    list ($day, $month, $year) = explode($separator, $value);
                    break;
                case 'mdy':
                    list ($month, $day, $year) = explode($separator, $value);
                    break;
                case 'ymd':
                    list ($year, $month, $day) = explode($separator, $value);
                    break;
                default:
                    return false;
            }
            if (ctype_digit($month) && ctype_digit($day) && ctype_digit($year)) {
                return checkdate($month, $day, $year);
            }
        }
        return false;
    }
    
    /**
     * Valida un string dada una Expresion Regular
     *
     * @param string $check
     * @param string $regex
     * @return bool
     */
    public static function pattern($check, $param)
    {
        $regex = isset($param['regex'])? $param['regex'] : '/.*/';
        return filter_var($check, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $regex)));
    }
    
    /**
     * Valida si es un número decimal
     * 
     * @param string $value
     * @param string $decimal
     * @return boolean
     */
    public static function decimal($value, $param)
    {
        $decimal = isset($param['decimal'])? $param['decimal'] : ',';
		return filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => array('decimal' => $decimal)));
	}
}
