<?php

class ScaffoldController extends ApplicationController {

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
	$model = Util::camelcase($this->model);
        if(Input::hasPost($this->model)){

            $obj = Load::model($this->model);
            //En caso que falle la operación de guardar
            if(!$obj->save(Input::post($this->model))){
                Flash::error('Falló Operación');
                //se hacen persistente los datos en el formulario
                $this->result = $obj;//post($model);
                return;
            }
			return Router::redirect("$this->controller_path");
        }
		// Solo es necesario para el autoForm
		$this->result = Load::model($this->model);
    }

    /**
     * Edita un Registro
     */
    public function editar($id = null)
    {
    	if($id != null){
		View::select('crear');

		$model = Util::camelcase($this->model);
		//se verifica si se ha enviado via POST los datos
		if(Input::hasPost($this->model)){
			$obj = Load::model($this->model);
			if(!$obj->update(Input::post($this->model))){
				Flash::error('Falló Operación');
				//se hacen persistente los datos en el formulario
				$this->result = Input::post($this->model);
			} else {
			   return Router::redirect("$this->controller_path");
			}
		}

		//Aplicando la autocarga de objeto, para comenzar la edición
		$this->result = Load::model($this->model)->find($id);
    	} else {

		}
    }

    /**
     * Eliminar un menu
     *
     * @param int $id
     */
    public function borrar($id = null)
    {
        if ($id) {
            if (!Load::model($this->model)->delete($id)) {
                Flash::error('Falló Operación');
            }
        }
        //enrutando al index para listar los articulos
	Router::redirect("$this->controller_path");
	View::select(NULL, NULL);
    }
	public function ver($id = null) {
       if($id){
		$this->result = Load::model($this->model)->find_first($id);
       }
	}
}
