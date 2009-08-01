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
 * Implementación para los detroyer
 *
 * @category   Kumbia
 * @package    modules
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see DestroyerInterface
 */
require_once CORE_PATH . 'libs/destroyer/destroyer_interface.php';
/**
 * @see ModelDestroyer
 */
require_once CORE_PATH . 'libs/destroyer/base_destroyers/model_destroyer.php';
/**
 * @see ControllerDestroyer
 */
require_once CORE_PATH . 'libs/destroyer/base_destroyers/controller_destroyer.php';
/**
 * @see HelperDestroyer
 */
require_once CORE_PATH . 'libs/destroyer/base_destroyers/helper_destroyer.php';
/**
 * @see FilterDestroyer
 */
require_once CORE_PATH . 'libs/destroyer/base_destroyers/filter_destroyer.php';

/**
 * Manejador de Destroyers
 *
 * @category   Kumbia
 * @package    modules
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Destroyer
{
	/**
	 * Ejecuta un destroyer
     *
	 * @param string $destroyer nombre del destroyer (destructor) a ejecutar
	 * @param string $name elemento a eliminar
	 * @param array $params array de parametros para el destructor
	 * @return boolean
	 */
	public static function destroy($destroyer, $name=null, $params=array())
    {
		$success = false;
        $destroyer_class = Util::camelcase($destroyer).'Destroyer';
        if (class_exists($destroyer_class)) {
            $success = call_user_func(array($destroyer_class, 'execute'), $name, $params);
        } else {
            throw new KumbiaException("No se ha encontrado la clase $destroyer_class necesaria para el destroyer");
        }
		
		return $success;
	}
}