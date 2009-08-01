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
 * Builder
 * 
 * @category   Kumbia
 * @package    Builder
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see BuilderInterface
 */
require_once CORE_PATH . 'libs/builder/builder_interface.php';
/**
 * @see ModelBuilder
 */
require_once CORE_PATH . 'libs/builder/base_builders/model_builder.php';
/**
 * @see ControllerBuilder
 */
require_once CORE_PATH . 'libs/builder/base_builders/controller_builder.php';
/**
 * @see HelperBuilder
 */
require_once CORE_PATH . 'libs/builder/base_builders/helper_builder.php';
/**
 * @see FilterBuilder
 */
require_once CORE_PATH . 'libs/builder/base_builders/filter_builder.php';
/**
 * Manejador de builders
 */
class Builder
{
    /**
     * Ejecuta un builder
     *
     * @param string $builder nombre del builder (constructor) a ejecutar
     * @param string $name nombre del elemento a construir
     * @param array $params array de parametros para el builder
     * @return boolean
     */
    public static function build ($builder, $name = null, $params = array())
    {
        $success = false;
        $builder_class = Util::camelcase($builder) . 'Builder';
        if (class_exists($builder_class)) {
            $success = call_user_func(array($builder_class , 'execute'), $name, $params);
        } else {
            throw new KumbiaException("No se ha encontrado la clase $builder_class necesaria para el builder");
        }
        return $success;
    }
}