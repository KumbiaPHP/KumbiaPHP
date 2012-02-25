<?php
/**
 * Antiguo Application Controller desaconsejado, ahora se usa el AppController
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category Kumbia
 * @package Controller
 * @deprecated Ahora se usa AppController. Sirve para portar apps fácilmente de 0.5 y beta1. Se eliminará despues de la beta2
 **/

// Para cargar los helpers antiguos
require_once CORE_PATH . 'extensions/helpers/tags.php';

// @see Controller antiguo por compatibilidad
require_once CORE_PATH . 'kumbia/controller_deprecated.php';

class ApplicationController extends ControllerDeprecated {

	final protected function initialize()
	{
	}

	final protected function finalize()
	{
		parent::finalize();// No tocar
		// Añadir código aqui

	}
}
