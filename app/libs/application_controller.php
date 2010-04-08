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
Load::coreLib('controller-deprecated');
//@see Controller nuevo controller
Load::coreLib('controller');

class ApplicationController extends Controller {

}
