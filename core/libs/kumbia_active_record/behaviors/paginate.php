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
class Paginator
{
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
    public static function paginate ($model)
    {
        $params = Util::getParams(func_get_args());
        $page_number = isset($params['page']) ? (int) $params['page'] : 1;
        $per_page = isset($params['per_page']) ? (int) $params['per_page'] : 10;
        //Si la pagina o por pagina es menor de 1 (0 o negativo)
        if ($page_number < 1 && $per_page < 1) {
            throw new KumbiaException("La página $page_number no existe en el paginador");
        }
        $start = $per_page * ($page_number - 1);
        //Instancia del objeto contenedor de pagina
        $page = new stdClass();
        //Si es un array, se hace paginacion de array
        if (is_array($model)) {
            $items = $model;
            $n = count($items);
            //si el inicio es superior o igual al conteo de elementos,
            //entonces la página no existe, exceptuando cuando es la pagina 1
            if ($page_number > 1 && $start >= $n) {
                throw new KumbiaException("La página $page_number no existe en el paginador");
            }
            $page->items = array_slice($items, $start, $per_page);
        } else {
            //Arreglo que contiene los argumentos para el find
            $find_args = array();
            $conditions = null;
            //Asignando parametros de busqueda
            if (isset($params['conditions'])) {
                $conditions = $params['conditions'];
            } elseif (isset($params[1])) {
                $conditions = $params[1];
            }
            if (isset($params['columns'])) {
                $find_args[] = "columns: {$params['columns']}";
            }
            if (isset($params['join'])) {
                $find_args[] = "join: {$params['join']}";
            }
            if (isset($params['group'])) {
                $find_args[] = "group: {$params['group']}";
            }
            if (isset($params['having'])) {
                $find_args[] = "having: {$params['having']}";
            }
            if (isset($params['order'])) {
                $find_args[] = "order: {$params['order']}";
            }
            if (isset($params['distinct'])) {
                $find_args[] = "distinct: {$params['distinct']}";
            }
            if (isset($conditions)) {
                $find_args[] = $conditions;
            }
            //contar los registros
            $n = $model->count($conditions);
            //si el inicio es superior o igual al conteo de elementos,
            //entonces la página no existe, exceptuando cuando es la pagina 1
            if ($page_number > 1 && $start >= $n) {
                throw new KumbiaException("La página $page_number no existe en el paginador");
            }
            //Asignamos el offset y limit
            $find_args[] = "offset: $start";
            $find_args[] = "limit: $per_page";
            //Se efectua la busqueda
            $page->items = call_user_func_array(array($model , 'find'), $find_args);
        }
        //Se efectuan los calculos para las paginas
        $page->next = ($start + $per_page) < $n ? ($page_number + 1) : false;
        $page->prev = ($page_number > 1) ? ($page_number - 1) : false;
        $page->current = $page_number;
        $page->total = ceil($n / $per_page);
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
    public static function paginate_by_sql ($model, $sql)
    {
        $params = Util::getParams(func_get_args());
        $page_number = isset($params['page']) ? (int) $params['page'] : 1;
        $per_page = isset($params['per_page']) ? (int) $params['per_page'] : 10;
        //Si la pagina o por pagina es menor de 1 (0 o negativo)
        if ($page_number < 1 || $per_page < 1) {
            throw new KumbiaException("La página $page_number no existe en el paginador");
        }
        $start = $per_page * ($page_number - 1);
        //Instancia del objeto contenedor de pagina
        $page = new stdClass();
        //Cuento las apariciones atraves de una tabla derivada
        $n = $model->count_by_sql("SELECT COUNT(*) FROM ($sql) AS t");
        //si el inicio es superior o igual al conteo de elementos,
        //entonces la página no existe, exceptuando cuando es la pagina 1
        if ($page_number > 1 && $start >= $n) {
            throw new KumbiaException("La página $page_number no existe en el paginador");
        }
        $page->items = $model->find_all_by_sql($model->limit($sql, "offset: $start", "limit: $per_page"));
        //Se efectuan los calculos para las paginas
        $page->next = ($start + $per_page) < $n ? ($page_number + 1) : false;
        $page->prev = ($page_number > 1) ? ($page_number - 1) : false;
        $page->current = $page_number;
        $page->total = ceil($n / $per_page);
        $page->count = $n;
        $page->per_page = $per_page;
        return $page;
    }
}
