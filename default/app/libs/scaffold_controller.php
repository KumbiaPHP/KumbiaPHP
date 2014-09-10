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
 * Cargador Selectiva
 *
 * @category   Kumbia
 * @package    Kumbia
 * @copyright  Copyright (c) 2005-2014 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Controlador base para la construcción de CRUD para modelos rapidamente
 *
 * @category Kumbia
 * @package Controller
 */
class ScaffoldController extends AppController
{

    public $scaffold = 'kumbia';
    public $model;

    public function index($page=1)
    {
        $this->results = Load::model($this->model)->paginate("page: $page","per_page: 10", 'order: id desc');
    }

    /**
     * Crea un Registro
     */
    public function crear()
    {
        if (Input::hasPost($this->model)) {

            $obj = Load::model($this->model);
            //En caso que falle la operación de guardar
            if (!$obj->save(Input::post($this->model))) {
                Flash::error('Falló Operación');
                //se hacen persistente los datos en el formulario
                $this->{$this->model} = $obj;
                return;
            }
            return Redirect::to();
        }
        // Solo es necesario para el autoForm
        $this->{$this->model} = Load::model($this->model);
    }

    /**
     * Edita un Registro
     */
    public function editar($id)
    {
        View::select('crear');

        //se verifica si se ha enviado via POST los datos
        if (Input::hasPost($this->model)) {
            $obj = Load::model($this->model);
            if (!$obj->update(Input::post($this->model))) {
                Flash::error('Falló Operación');
                //se hacen persistente los datos en el formulario
                $this->{$this->model} = Input::post($this->model);
            } else {
                return Redirect::to();
            }
        }

        //Aplicando la autocarga de objeto, para comenzar la edición
        $this->{$this->model} = Load::model($this->model)->find((int) $id);
    }

    /**
     * Borra un Registro
     */
    public function borrar($id)
    {
        if (!Load::model($this->model)->delete((int) $id)) {
            Flash::error('Falló Operación');
        }
        //enrutando al index para listar los articulos
        Redirect::to();
    }

    /**
     * Ver un Registro
     */
    public function ver($id)
    {
        $this->result = Load::model($this->model)->find_first((int) $id);
    }

}