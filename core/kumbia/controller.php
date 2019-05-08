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
 * @package    Controller
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase principal para los controladores de Kumbia
 *
 * @category   Kumbia
 * @package    Controller
 */
class Controller
{

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
     * Parámetros de la acción
     *
     * @var array
     */
    public $parameters;
    /**
     * Limita la cantidad correcta de
     * parametros de una action
     *
     * @var bool
     */
    public $limit_params = true;
    /**
     * Nombre del scaffold a usar
     *
     * @var string
     */
    public $scaffold = '';

    /**
     * Data disponble para mostrar
     * 
     * @var mixed
     */
    public $data;

    /**
     * Constructor
     *
     * @param array $args
     */
    public function __construct($args)
    {
        /*modulo al que pertenece el controlador*/
        $this->module_name = $args['module'];
        $this->controller_name = $args['controller'];
        $this->parameters = $args['parameters'];
        $this->action_name = $args['action'];
        View::select($args['action']);
        View::setPath($args['controller_path']);
    }

    /**
     * BeforeFilter
     *
     * @return false|null
     */
    protected function before_filter()
    {
    }

    /**
     * AfterFilter
     *
     * @return false|void
     */
    protected function after_filter()
    {
    }

    /**
     * Initialize
     *
     * @return false|void
     */
    protected function initialize()
    {
    }

    /**
     * Finalize
     *
     * @return false|void
     */
    protected function finalize()
    {
    }

    /**
     * Ejecuta los callback filter
     *
     * @param boolean $init filtros de inicio
     * @return false|void
     */
    final public function k_callback($init = false)
    {
        if ($init) {
            if ($this->initialize() !== false) {
                return $this->before_filter();
            }
            return false;
        }

        $this->after_filter();
        $this->finalize();
    }
}
