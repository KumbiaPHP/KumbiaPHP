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
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase principal para los controladores de Kumbia
 *
 * @category   Kumbia
 * @package    Controller
 */
#[\AllowDynamicProperties]
abstract class Controller
{

    /**
     * Nombre del modulo actual
     *
     * @var string
     */
    public string $module_name;
    /**
     * Nombre del controlador actual
     *
     * @var string
     */
    public string $controller_name;
    /**
     * Nombre de la acción actual
     *
     * @var string
     */
    public string $action_name;
    /**
     * Parámetros de la acción
     *
     * @var array
     */
    public array $parameters;
    /**
     * Limita la cantidad correcta de
     * parametros de una action
     *
     * @var bool
     */
    public $limit_params = true;

    /**
     * Data disponble para mostrar
     * 
     * @var mixed
     */
    public $data;

    public function __construct(array $args)
    {
        $this->module_name = $args['module'];
        $this->controller_name = $args['controller'];
        $this->parameters = $args['parameters'];
        $this->action_name = $args['action'];
        View::init($args['action'], $args['controller_path']);
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

    /**
     * Se llama cuando no existe un método
     *
     * @param string $name      
     * @param array  $arguments
     * @throws KumbiaException
     * 
     * @return void
     */
    public function __call($name, $arguments)
    {
        throw new KumbiaException($name, 'no_action');
    }
}
