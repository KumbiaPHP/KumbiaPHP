<?php

class ScaffoldController extends AppController {

	public $scaffold = 'kumbia';
	public $model;

	public function index($page=1)
	{
		$this->results = Load::model($this->model)->paginate("page: $page", 'order: id desc');
	}

	/**
	 * Crea un Registro
	 */
	public function crear ()
	{
		if(Input::hasPost($this->model)){

		    $obj = Load::model($this->model);
		    //En caso que falle la operación de guardar
		    if(!$obj->save(Input::post($this->model))){
			Flash::error('Falló Operación');
			//se hacen persistente los datos en el formulario
			$this->result = $obj;
			return;
		    }
		    return Router::redirect();
		}
		// Solo es necesario para el autoForm
		$this->result = Load::model($this->model);
	}

	/**
	 * Edita un Registro
	 */
	public function editar($id)
	{
		View::select('crear');

		//se verifica si se ha enviado via POST los datos
		if(Input::hasPost($this->model)){
			$obj = Load::model($this->model);
			if(!$obj->update(Input::post($this->model))){
				Flash::error('Falló Operación');
				//se hacen persistente los datos en el formulario
				$this->result = Input::post($this->model);
			} else {
			   return Router::redirect();
			}
		}

		//Aplicando la autocarga de objeto, para comenzar la edición
		$this->result = Load::model($this->model)->find((int)$id);

	}

	/**
	 * Borra un Registro
	 */
	public function borrar($id)
	{
		if (!Load::model($this->model)->delete((int)$id)) {
			Flash::error('Falló Operación');
		}
		//enrutando al index para listar los articulos
		Router::redirect();
	}

	/**
	 * Ver un Registro
	 */
	public function ver($id) {
		$this->result = Load::model($this->model)->find_first((int)$id);
	}
}
