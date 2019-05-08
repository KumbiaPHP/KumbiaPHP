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
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
require_once __DIR__.'/controller.php';

/**
 * Controlador para manejar peticiones REST.
 *
 * Por defecto cada acción se llama como el método usado por el cliente
 * (GET, POST, PUT, DELETE, OPTIONS, HEADERS, PURGE...)
 * ademas se puede añadir mas acciones colocando delante el nombre del método
 * seguido del nombre de la acción put_cancel, post_reset...
 *
 * @category Kumbia
 *
 * @author kumbiaPHP Team
 */
class KumbiaRest extends Controller
{
    /**
     * Formato de entrada usado para interpretar los datos
     * enviados por el cliente.
     *
     * @var string MIME Type del formato
     */
    protected $_fInput;

    /**
     * Permite definir parser personalizados por MIME TYPE
     * Esto es necesario para interpretar las entradas
     * Se define como un MIME type como clave y el valor debe ser un
     * callback que devuelva los datos interpretado.
     */
    protected $_inputType = array(
        'application/json' => array('RestController', 'parseJSON'),
        'application/xml' => array('RestController', 'parseXML'),
        'text/xml' => array('RestController', 'parseXML'),
        'text/csv' => array('RestController', 'parseCSV'),
        'application/x-www-form-urlencoded' => array('RestController', 'parseForm'),
    );

    /**
     * Formato de salida enviada al cliente.
     *
     * @var string nombre del template a usar
     */
    protected $_fOutput;

    /**
     * Permite definir las salidas disponibles,
     * de esta manera se puede presentar la misma salida en distintos
     * formatos a requerimientos del cliente.
     */
    protected $_outputType = array(
        'application/json' => 'json',
        'application/xml' => 'xml',
        'text/xml' => 'xml',
        'text/csv' => 'csv',
    );

    /**
     * Constructor
     *
     * @param array $arg
     */
    public function __construct($arg)
    {
        parent::__construct($arg);
        $this->initREST();
    }

    /**
     * Hacer el router de la petición y envia los parametros correspondientes
     * a la acción, adema captura formatos de entrada y salida. 
     */
    protected function initREST()
    {
        /* formato de entrada */
        $this->_fInput = self::getInputFormat();
        $this->_fOutput = self::getOutputFormat($this->_outputType);
        View::select(null, $this->_fOutput);
        $this->rewriteActionName();
    }

    /**
     * Reescribe la acción.
     */
    protected function rewriteActionName()
    {
        /**
         * reescribimos la acción a ejecutar, ahora será el método de
         * la petición: get(:id), getAll , put, post, delete, etc.
         */
        $action = $this->action_name;
        $method = strtolower(Router::get('method'));
        $rewrite = "{$method}_{$action}";
        if ($this->actionExist($rewrite)) {
            $this->action_name = $rewrite;

            return;
        }
        if ($rewrite === 'get_index') {
            $this->action_name = 'getAll';

            return;
        }
        $this->action_name = $method;
        $this->parameters = ($action === 'index') ? $this->parameters : [$action] + $this->parameters;
    }

    /**
     * Verifica si existe la acción $name existe.
     *
     * @param string $name nombre de la acción
     *
     * @return bool
     */
    protected function actionExist($name)
    {
        if (method_exists($this, $name)) {
            return (new ReflectionMethod($this, $name))->isPublic();
        }

        return false;
    }

    /**
     * Retorna los parámetros de la petición el función del formato de entrada
     * de los mismos. Hace uso de los parser definidos en la clase.
     */
    protected function param()
    {
        $input = file_get_contents('php://input');
        $format = $this->_fInput;
        /* verifica si el formato tiene un parser válido */
        if (isset($this->_inputType[$format]) && is_callable($this->_inputType[$format])) {
            $result = call_user_func($this->_inputType[$format], $input);
            if ($result) {
                return $result;
            }
        }

        return $input;
    }

    /**
     * Envia un error al cliente junto con el mensaje.
     *
     * @param string $text  texto del error
     * @param int    $error Número del error HTTP
     *
     * @return array data de error
     */
    protected function error($text, $error = 400)
    {
        http_response_code((int) $error);

        return array('error' => $text);
    }

    /**
     * Retorna los formato aceptados por el cliente ordenados por prioridad
     * interpretando la cabecera HTTP_ACCEPT.
     *
     * @return array
     */
    protected static function accept()
    {
        /* para almacenar los valores acceptados por el cliente */
        $aTypes = array();
        /* Elimina espacios, convierte a minusculas, y separa */
        $accept = explode(',', strtolower(str_replace(' ', '', Input::server('HTTP_ACCEPT'))));
        foreach ($accept as $a) {
            $q = 1; /* Por defecto la prioridad es 1, el siguiente verifica si es otra */
            if (strpos($a, ';q=')) {
                /* parte el "mime/type;q=X" en dos: "mime/type" y "X" */
                list($a, $q) = explode(';q=', $a);
            }
            $aTypes[$a] = $q;
        }
        /* ordena por prioridad (mayor a menor) */
        arsort($aTypes);

        return $aTypes;
    }

    /**
     * Parse JSON
     * Convierte formato JSON en array asociativo.
     *
     * @param string $input
     *
     * @return array|string
     */
    protected static function parseJSON($input)
    {
        return json_decode($input, true);
    }

    /**
     * Parse XML.
     *
     * Convierte formato XML en un objeto, esto será necesario volverlo estandar
     * si se devuelven objetos o arrays asociativos
     *
     * @param string $input
     *
     * @return \SimpleXMLElement|null
     */
    protected static function parseXML($input)
    {    
        try {
            return new SimpleXMLElement($input);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Parse CSV.
     *
     * Convierte CSV en arrays numéricos,
     * cada item es una linea
     *
     * @param string $input
     *
     * @return array
     */
    protected static function parseCSV($input)
    {
        $temp = fopen('php://memory', 'rw');
        fwrite($temp, $input);
        fseek($temp, 0);
        $res = array();
        while (($data = fgetcsv($temp)) !== false) {
            $res[] = $data;
        }
        fclose($temp);

        return $res;
    }

    /**
     * Realiza la conversión de formato de Formulario a array.
     *
     * @param string $input
     *
     * @return array
     */
    protected static function parseForm($input)
    {
        parse_str($input, $vars);

        return $vars;
    }

    /**
     * Retorna el tipo de formato de entrada.
     *
     * @return string
     */
    protected static function getInputFormat()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $str = explode(';', $_SERVER['CONTENT_TYPE']);
            return trim($str[0]);
        }

        return '';
    }

    /**
     * Devuelve le nombre del formato de salida.
     *
     * @param array $validOutput Array de formatos de salida soportado
     *
     * @return string
     */
    protected function getOutputFormat(array $validOutput)
    {
        /* busco un posible formato de salida */
        $accept = self::accept();
        foreach ($accept as $key) {
            if (array_key_exists($key, $validOutput)) {
                return $validOutput[$key];
            }
        }

        return 'json';
    }

    /**
     * Retorna todas las cabeceras enviadas por el cliente.
     *
     * @return array
     */
    protected static function getHeaders()
    {
        return getallheaders();
    }
}
