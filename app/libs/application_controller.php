<?php
/**
 * Todas las controladores heredan de esta clase en un nivel superior
 * por lo tanto los metodos aqui definidos estan disponibles para
 * cualquier controlador.
 *
 * @category Kumbia
 * @package Controller
 **/
// @see Controller antiguo por compatibilidad
require_once CORE_PATH . 'kumbia/controller-deprecated.php';
//@see Controller nuevo controller
//require_once CORE_PATH . 'kumbia/controller.php';

class ApplicationController extends Controller {

}
