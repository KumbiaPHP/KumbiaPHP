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
 * Componente para paginar
 * 
 * @category   Kumbia
 * @package    Db
 * @subpackage Behaviors 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
* Paginador
*	
* page: numero de pagina a mostrar (por defecto la pagina 1)
* per_page: cantidad de elementos por pagina (por defecto 10 items por pagina)
*	
* Para paginacion por array:
*  Parametros sin nombre en orden:
*    Parametro1: array a paginar
*	
* Para paginacion de modelo:
*  Parametros sin nombre en orden:
*   Parametro1: nombre del modelo o objeto modelo
*   Parametro2: condicion de busqueda
*			
* Parametros con nombre:
*  conditions: condicion de busqueda
*  order: ordenamiento
*  columns: columnas a mostrar
*	
* Retorna un PageObject que tiene los siguientes atributos:
*  next: numero de pagina siguiente, si no hay pagina siguiente entonces es false
*  prev: numero de pagina anterior, si no hay pagina anterior entonces es false
*  current: numero de pagina actual
*  total: total de paginas que se pueden mostrar
*  items: array de items de la pagina
*  count: Total de registros
*  per_page: cantidad de elementos por pagina
*
* Ejemplos:
*  $page = paginate($array, 'per_page: 5', "page: $page_num");
*  $page = paginate('usuario', 'per_page: 5', "page: $page_num");
*  $page = paginate('usuario', 'sexo="F"' , 'per_page: 5', "page: $page_num");
*  $page = paginate('Usuario', 'sexo="F"' , 'per_page: 5', "page: $page_num");
*  $page = paginate($this->Usuario, 'conditions: sexo="F"' , 'per_page: 5', "page: $page_num");
*	
* @return object
**/
class Paginator {
	
	public static function paginate() {
		$params = Util::getParams(func_get_args());
		
		$page_number = isset($params['page']) ? $params['page'] : 1;
		$per_page = isset($params['per_page']) ? $params['per_page'] : 10;
		$start = $per_page*($page_number-1);
		
		/**
		 * Instancia del objeto contenedor de pagina
		 **/
		$page = new stdClass();
		
		/**
		 * Si es un array, se hace paginacion de array
		 **/
		if(is_array($params[0])) {
			$items = $params[0];
			$n = count($items);
			$page->items = array_slice($items, $start, $per_page);
		} else {
		
			/**
			 * Si es una cadena, instancio el modelo
			 **/
			if(is_string($params[0])) {
				$m = Util::camelcase($params[0]);
				$model = ActiveRecord::get($m);
			} else {
				$model = $params[0];
			}
		
			/**
			 * Arreglo que contiene los argumentos para el find
			 **/
			$find_args = array();
			$conditions = null;
			/**
			 * Asignando parametros de busqueda
			 **/
			if(isset($params['conditions'])) {
				$conditions = $params['conditions'];
			}elseif(isset($params[1])) {
				$conditions = $params[1];
			}
	
			if(isset($params['columns'])) {
				array_push($find_args, "columns: {$params['columns']}");
			}
			if(isset($params['join'])) {
				array_push($find_args, "join: {$params['join']}");
			}
			if(isset($params['group'])) {
				array_push($find_args, "group: {$params['group']}");
			}
			if(isset($params['having'])) {
				array_push($find_args, "having: {$params['having']}");
			}
			if(isset($params['order'])) {
				array_push($find_args, "order: {$params['order']}");
			}
			if(isset($params['distinct'])) {
				array_push($find_args, "distinct: {$params['distinct']}");
			}
			if(isset($conditions)) {
				array_push($find_args, $conditions);
			}
			
			/**
			 * Cuento las apariciones
			 **/
			//$n = call_user_func_array(array($model, 'count'), $find_args);
			$n = call_user_func_array(array($model, 'count'), $conditions);
			
			/**
			 * Asignamos el offset y limit
			 **/
			array_push($find_args, "offset: $start");
			array_push($find_args, "limit: $per_page");
			
			/**
			 * Se efectua la busqueda
			 **/
			$page->items = call_user_func_array(array($model, 'find'), $find_args);
		}
		
		/**
		 * Se efectuan los calculos para las paginas
		 **/
		$page->next = ($start + $per_page)<$n ? ($page_number+1) : false ;
		$page->prev = ($page_number>1) ? ($page_number-1) : false ;
		$page->current = $page_number;
		$page->total = ($n % $per_page) ? ((int)($n/$per_page) + 1):($n/$per_page);
		$page->count = $n;
        $page->per_page = $per_page;
		
		return $page;
	}
	
	/**
	* Paginador por sql
	*	
	* @param string $model nombre del modelo
	* @param string $sql consulta sql
	*
	* page: numero de pagina a mostrar (por defecto la pagina 1)
	* per_page: cantidad de elementos por pagina (por defecto 10 items por pagina)
	*			
	*	
	* Retorna un PageObject que tiene los siguientes atributos:
	*  next: numero de pagina siguiente, si no hay pagina siguiente entonces es false
	*  prev: numero de pagina anterior, si no hay pagina anterior entonces es false
	*  current: numero de pagina actual
	*  total: total de paginas que se pueden mostrar
	*  items: array de items de la pagina
	*  count: Total de registros
	*
	* Ejemplos:
	*  $page = paginate_by_sql('usuario', 'SELECT * FROM usuario' , 'per_page: 5', "page: $page_num");
	*	
	* @return object
	**/
	public static function paginate_by_sql($model, $sql) {
		$params = Util::getParams(func_get_args());
		
		$page_number = isset($params['page']) ? $params['page'] : 1;
		$per_page = isset($params['per_page']) ? $params['per_page'] : 10;
		$start = $per_page*($page_number-1);
	
		/**
		 * Si es una cadena, instancio el modelo
		 **/
		if(is_string($params[0])) {
			$m = Util::camelcase($params[0]);
			$model = ActiveRecord::get($m);
		}
	
		/**
		 * Instancia del objeto contenedor de pagina
		 **/
		$page = new stdClass();
	
		/**
		 * Cuento las apariciones atraves de una tabla derivada
		 **/
		$n = $model->count_by_sql("SELECT COUNT(*) FROM ($sql) AS t");
		$page->items = $model->find_all_by_sql($model->limit($sql, "offset: $start", "limit: $per_page"));
	
		/**
		 * Se efectuan los calculos para las paginas
		 **/
		$page->next = ($start + $per_page)<$n ? ($page_number+1) : false ;
		$page->prev = ($page_number>1) ? ($page_number-1) : false ;
		$page->current = $page_number;
		$page->total = ($n % $per_page) ? ((int)($n/$per_page) + 1):($n/$per_page);
		$page->count = $n;
        $page->per_page = $per_page;
		
		return $page;
	}
}