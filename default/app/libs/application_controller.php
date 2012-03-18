<?php
/**
 * @category Kumbia
 * @package ControllerDeprecated
 * @deprecated Ahora se usa AppController.
 * Se eliminará despues de la beta2
 *
 * Antiguo ApplicationController desaconsejado, ahora se usa el AppController.
 *
 * Todos los controladores heredan de esta clase en un nivel superior
 * por lo tanto los métodos aquí definidos estan disponibles para
 * cualquier controlador.
 */
/**
 * @see Tags
 */
require_once CORE_PATH . 'extensions/helpers/tags.php';

/**
 * @see ControllerDeprecated Antiguo controlador por compatibilidad
 */
require_once CORE_PATH . 'kumbia/controller_deprecated.php';

/**
 * (Obsoleto) Clase controladora que extienden los demás controllers 
 *
 * @deprecated Ahora se usa AppController.
 * Se eliminará despues de la beta2.
 * Se mantiene para portar apps fácilmente de 0.5 y beta1.
 *
 * @category Kumbia
 * @package ControllerDeprecated
 */
class ApplicationController extends ControllerDeprecated
{

    final protected function initialize()
    {

    }

    final protected function finalize()
    {
        parent::finalize(); // No tocar
        // Añadir código aqui
    }

}
