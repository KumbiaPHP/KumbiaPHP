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
 * @package    Auth
 * @subpackage Adapters
 * 
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Esta clase permite autenticar usuarios usando una entidad de la base de datos
 *
 * @category   extensions
 * @package    Auth
 */
class ModelAuth implements AuthInterface
{
    /**
     * Atributos del modelo a comparar para autenticación válida
     */
    private $compare_attributes = array();
    /**
     * Identidad encontrara
     */
    private $identity = array();
    /**
     * Nombre de la clase del modelo
     */
    private $class;

    /**
     * Constructor del adaptador
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {
        foreach (array('class') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("Debe especificar el parámetro '$param' en los parámetros");
            }
        }
        unset($extra_args[0]);
        unset($extra_args['class']);
        $this->compare_attributes = $extra_args;
    }

    /**
     * Obtiene los datos de identidad obtenidos al autenticar
     *
     */
    public function get_identity()
    {
        return $this->identity;
    }

    /**
     * Autentica un usuario usando el adaptador
     *
     * @return boolean
     */
    public function authenticate()
    {
        $where_condition = array();
        foreach ($this->compare_attributes as $field => $value) {
            $value = addslashes($value);
            $where_condition[] = "$field = '$value'";
        }
        $result = (new $this->class)->count(join(" AND ", $where_condition));
        if ($result) {
            $model = (new $this->class)->find_first(join(" AND ", $where_condition));
            $identity = array();
            foreach ($model->fields as $field) {
                /**
                 * Trata de no incluir en la identidad el password del usuario
                 */
                if (!in_array($field, array('password', 'clave', 'contrasena', 'passwd', 'pass'))) {
                    $identity[$field] = $model->$field;
                }
            }
            $this->identity = $identity;
        }
        return $result;
    }

    /**
     * Asigna los valores de los parámetros al objeto autenticador
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('server', 'secret', 'principal', 'password', 'port', 'max_retries') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

}
