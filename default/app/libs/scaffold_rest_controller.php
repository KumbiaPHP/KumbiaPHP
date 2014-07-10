<?php

/**
 * Controlador base para la construcción de API REST para modelos rapidamente
 *
 * @category Kumbia
 * @package Controller
 */
class ScaffoldRestController extends RestController
{
    
    public $model;
    
    /**
     * Retorna un registro a través de su $id 
     * metodo get objeto/:id
     */
    public function get($id){        
        $this->data = Load::model($this->model)->find((int)$id);
    }
 
    /**
     * Lista los registros
     * metodo get objeto/
     */
    public function getAll(){	
        $this->data = Load::model($this->model)->find();
    }
 
    /**
     * Crea un nuevo registro
     * metodo post objeto/
     */
    public function post(){
	$obj = Load::model($this->model);
	if($obj->save($this->param())){
		$this->setCode(201);
		$this->data = $obj;
	}else{
		$this->data = $this->error("error inesperado", 400);
	}
    }
 
    /**
     * Modifica un registro por $id
     * metodo put objeto/:id
     */
    public function put($id){
	$obj = Load::model($this->model);
	$obj = $obj->find((int)$id);
	if($obj->save($this->param())){
		$this->setCode(202);
		$this->data = $obj;
	}else{
		$this->data = $this->error("error inesperado", 400);
	}
    }
 
    /**
     * Elimina un registro por $id
     * metodo delete objeto/:id
     */
    public function delete($id){
	$obj = Load::model($this->model);
	if($obj->delete((int)$id)){
		$this->setCode(200);
		$this->data = array();
	}else{
		$this->data = $this->error("error inesperado", 400);
	}	
    }
}
