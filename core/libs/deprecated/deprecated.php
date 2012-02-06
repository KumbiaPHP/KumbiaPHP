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
 * Helper para Tags HTML
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Merge Two Arrays Overwriting Values $a1
 * from $a2
 *
 * @param array $a1
 * @param array $a2
 * @return array
 */
function array_merge_overwrite($a1, $a2){
	foreach($a2 as $key2 => $value2){
		if(!is_array($value2)){
			$a1[$key2] = $value2;
		} else {
			if(!isset($a1[$key2])){
				$a1[$key2] = null;
			}
			if(!is_array($a1[$key2])){
				$a1[$key2] = $value2;
			} else {
				$a1[$key2] = array_merge_overwrite($a1[$key2], $a2[$key2]);
			}
		}
	}
	return $a1;
}

/**
 * Inserts a element into a defined position
 * in a array
 *
 * @param array $form
 * @param mixed $index
 * @param mixed $value
 * @param mixed $key
 */
function array_insert(&$form, $index, $value, $key=null){
	$ret = array();
	$n = 0;
	$i = false;
	foreach($form as $keys => $val){
		if($n!=$index){
			$ret[$keys] = $val;
		} else {
			if(!$key){
				$ret[$index] = $value;
				$i = true;
			} else {
				$ret[$key] = $value;
				$i = true;
			}
			$ret[$keys] = $val;
		}
		$n++;
	}
	if(!$i){
		if(!$key){
			$ret[$index] = $value;
			$i = true;
		} else {
			$ret[$key] = $value;
			$i = true;
		}
	}
	$form = $ret;
}

/**
 * Insert para arrays numericos
 * @param $array array donde se insertara (por referencia)
 * @param $index indice donde se realizara la insercion
 * @param $value valor a insertar
 **/
function array_num_insert(&$array, $index, $value) {
    $array2 = array_splice($array, $index);
    array_push($array, $value);
    $array = array_merge($array, $array2);
}

/**
 * Las siguientes funciones son utilizadas para la generación
 * de versiones escritas de numeros
 *
 * @param numeric $a
 * @return string
 */
function value_num($a){
	if($a<=21){
		switch ($a){
			case 1: return 'UNO';
			case 2: return 'DOS';
			case 3: return 'TRES';
			case 4: return 'CUATRO';
			case 5: return 'CINCO';
			case 6: return 'SEIS';
			case 7: return 'SIETE';
			case 8: return 'OCHO';
			case 9: return 'NUEVE';
			case 10: return 'DIEZ';
			case 11: return 'ONCE';
			case 12: return 'DOCE';
			case 13: return 'TRECE';
			case 14: return 'CATORCE';
			case 15: return 'QUINCE';
			case 16: return 'DIECISEIS';
			case 17: return 'DIECISIETE';
			case 18: return 'DIECIOCHO';
			case 19: return 'DIECINUEVE';
			case 20: return 'VEINTE';
			case 21: return 'VEINTIUN';
		}
	} else {
		if($a<=99){
			if($a>=22&&$a<=29)
			return "VENTI".value_num($a % 10);
			if($a==30) return  "TREINTA";
			if($a>=31&&$a<=39)
			return "TREINTA Y ".value_num($a % 10);
			if($a==40) $b = "CUARENTA";
			if($a>=41&&$a<=49)
			return "CUARENTA Y ".value_num($a % 10);
			if($a==50) return "CINCUENTA";
			if($a>=51&&$a<=59)
			return "CINCUENTA Y ".value_num($a % 10);
			if($a==60) return "SESENTA";
			if($a>=61&&$a<=69)
			return "SESENTA Y ".value_num($a % 10);
			if($a==70) return "SETENTA";
			if($a>=71&&$a<=79)
			return "SETENTA Y ".value_num($a % 10);
			if($a==80) return "OCHENTA";
			if($a>=81&&$a<=89)
			return "OCHENTA Y ".value_num($a % 10);
			if($a==90) return "NOVENTA";
			if($a>=91&&$a<=99)
			return "NOVENTA Y ".value_num($a % 10);
		} else {
			if($a==100) return "CIEN";
			if($a>=101&&$a<=199)
			return "CIENTO ".value_num($a % 100);
			if($a>=200&&$a<=299)
			return "DOSCIENTOS ".value_num($a % 100);
			if($a>=300&&$a<=399)
			return "TRECIENTOS ".value_num($a % 100);
			if($a>=400&&$a<=499)
			return "CUATROCIENTOS ".value_num($a % 100);
			if($a>=500&&$a<=599)
			return "QUINIENTOS ".value_num($a % 100);
			if($a>=600&&$a<=699)
			return "SEICIENTOS ".value_num($a % 100);
			if($a>=700&&$a<=799)
			return "SETECIENTOS ".value_num($a % 100);
			if($a>=800&&$a<=899)
			return "OCHOCIENTOS ".value_num($a % 100);
			if($a>=901&&$a<=999)
			return "NOVECIENTOS ".value_num($a % 100);
		}
	}
}
/**
 * Genera una cadena de millones
 *
 * @param numeric $a
 * @return string
 */
function millones($a){
	$a = $a / 1000000;
	if($a==1)
	return "UN MILLON ";
	else
	return value_num($a)." MILLONES ";
}

/**
 * Genera una cadena de miles
 *
 * @param numeric $a
 * @return string
 */
function miles($a){
	$a = $a / 1000;
	if($a==1)
	return "MIL";
	else
	return value_num($a)."MIL ";
}

/**
 * Escribe en letras un monto numerico
 *
 * @param numeric $valor
 * @param string $moneda
 * @param string $centavos
 * @return string
 */
function money_letter($valor, $moneda, $centavos){
	$a = $valor;
	$p = $moneda;
	$c = $centavos;
	$val = "";
	$v = $a;
	$a = (int) $a;
	$d = round($v - $a, 2);
	if($a>=1000000){
		$val = millones($a - ($a % 1000000));
		$a = $a % 1000000;
	}
	if($a>=1000){
		$val.= miles($a - ($a % 1000));
		$a = $a % 1000;
	}
	$val.= value_num($a)." $p ";
	if($d){
		$d*=100;
		$val.=" CON ".value_num($d)." $c ";
	}
	return $val;
}

/**
 * Escribe un valor en bytes en forma humana
 *
 * @param integer $num
 * @return string
 */
function to_human($num){
	if($num<1024){
		return $num." bytes";
	} else {
		if($num<1024*1024){
			return round($num/1024, 2)." kb";
		} else {
			return round($num/1024/1024, 2)." mb";
		}
	}
}

/**
 * Cameliza una cadena
 *
 * @param string $str
 * @return string
 */
function camelize($str) {	
	$str = strtr($str, '_', ' ');
	$str = ucwords($str);
	$str = str_replace(' ', '', $str);
	
	return $str;
}

/**
 * Descameliza una cadena camelizada
 *
 * @param string $s
 * @return string
 */
function uncamelize($str) {
		
	return strtolower(preg_replace('/([A-Z])/', "_\\1", $str));
}

/**
 * Convierte los parametros de una funcion o metodo de parametros por nombre a un array
 *
 * @param array $params 
 * @return array
 */
function get_params($params){
	$data = array();
	foreach ($params as $p) {
		if(is_string($p) && preg_match('/^(\w+): (.*)/', $p, $match)){
			$data[$match[1]] = $match[2];
		} else {
			$data[] = $p;
		}
	}
	return $data;
}

/*
 * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
 * @param string $lista Cadena con Items separados por comas (,).
 * @return string $listaEncomillada Cadena con Items encerrados en doblecomillas y separados por comas (,).
 */
function encomillar_lista($lista){
	$arrItems = explode(',', $lista);
	$n = count($arrItems);
	$listaEncomillada = '';
	for ($i=0; $i<$n-1; $i++) {
		$listaEncomillada.= "\"".$arrItems[$i]."\",";
	}
	$listaEncomillada.= "\"".$arrItems[$n-1]."\"";
	return $listaEncomillada;
}

/**
 * Devuelve un string encerrado en comillas
 *
 * @param string $word
 * @return string
 */

function comillas($word){
	return "'$word'";
}

/**
 * Resalta un Texto en otro Texto
 *
 * @param string $sentence
 * @param string $what
 * @return string
 */
function highlight($sentence, $what){
	return str_replace($what, '<strong class="highlight">'.$what.'</strong>', $sentence);
}

/**
 * Escribe un numero usando formato numerico
 *
 * @param string $number
 * @return string
 */
function money($number){
	$number = my_round($number);
	return "$&nbsp;".number_format($number, 2, ",", ".");
}

/**
 * Redondea un numero
 *
 * @param numeric $n
 * @param integer $d
 * @return string
 */
function roundnumber($n, $d = 0) {
	$n = $n - 0;
	if ($d === NULL) $d = 2;

	$f = pow(10, $d);
	$n += pow(10, - ($d + 1));
	$n = round($n * $f) / $f;
	$n += pow(10, - ($d + 1));
	$n += '';

	if ( $d == 0 ):
		return substr($n, 0, strpos($n, '.'));
	else:
		return substr($n, 0, strpos($n, '.') + $d + 1);
	endif;
}

/**
 * Realiza un redondeo usando la funcion round de la base
 * de datos.
 *
 * @param numeric $number
 * @param integer $n
 * @return numeric
 */
function my_round($number, $n=2){
	$number = (float) $number;
	$n = (int) $number;
	return ActiveRecord::static_select_one("round($number, $n)");
}

/**
 * Copia un directorio.
 *
 * @param string $source directorio fuente
 * @param string $target directorio destino
 */
function copy_dir($source, $target) {
	if (is_dir($source)) {
		
		if (!is_dir($target)){
			@mkdir($target);
		}
           
		$d = dir($source);
           
		while (false !== ($entry = $d->read())) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
               
			$Entry = $source.'/'.$entry;           
			if (is_dir($Entry)) {
				copy_dir($Entry, $target.'/'.$entry);
				continue;
			}
			copy($Entry, $target.'/'. $entry);
		}
           
		$d->close();
	}else {
		copy($source, $target);
	}
}

/**
 * Crea un path apartir de directorios.
 * @param array $dirs array de partes de la ruta
 * @return string
 */
function join_path($dirs){
	if(!is_array($dirs)) {
		$dirs = func_get_args();
	}
	$n = count($dirs);
	
	$path= '';
	for($i=0; $i<$n; $i++) {
		$dir = $dirs[$i];
		if(!empty($dir)) {
			$path.=$dir;
			if($i<($n-1) && $dir[strlen($dir)-1]!='/') $path.='/';
		}
	}
	
	return $path;
}

/**
 * Crea un path.
 *
 * @param string $path ruta a crear
 * @return boolean
 */
function mkpath($path){
	$path = join_path(func_get_args());
	if(@mkdir($path) or file_exists($path)) return true;
    return (mkpath(dirname($path)) and mkdir($path));
}

/**
 * Calcula la edad
 *
 * order: orden de la fecha especificada (YYYY-MM-DD, DD-MM-YYYY, ...)
 * bithdate: fecha de nacimiento
 * today: fecha de referencia para calcular la edad (por defecto la de hoy)
 * year: año de nacimiento
 * month: mes de nacimiento
 * day: dia de nacimiento
 * today_year: año de hoy
 * today_month: mes de hoy
 * today_day: dia de hoy
 * @return integer
 */
function age(){
	$params = get_params(func_get_args());
	$error = false;
	
	$active_app = Router::get_application();
	
	if(!isset($params['order'])){
		if($kumbia_config = Config::read('config')){
			if(preg_match('/^DD[^DMY]MM[^DMY]YYYY$/', $kumbia_config->$active_app->dbdate)){
				$params['order'] = 'd-m-Y';
			} elseif(preg_match('/^DD[^DMY]YYYY[^DMY]MM$/', $kumbia_config->$active_app->dbdate)){
				$params['order'] = 'd-Y-m';
			} elseif(preg_match('/^MM[^DMY]DD[^DMY]YYYY$/', $kumbia_config->$active_app->dbdate)) {
				$params['order'] = 'm-d-Y';
			} elseif(preg_match('/^MM[^DMY]YYYY[^DMY]DD$/', $kumbia_config->$active_app->dbdate)) {
				$params['order'] = 'm-Y-d';
			} elseif(preg_match('/^YYYY[^DMY]DD[^DMY]MM$/', $kumbia_config->$active_app->dbdate)) {
				$params['order'] = 'Y-d-m';
			} else {
				$params['order'] = 'Y-m-d';
			}
		}
	}

	if(isset($params['month'], $params['day'], $params['year'])){
		$time_nac = mktime(0, 0, 0, $params['month'], $params['day'], $params['year']);	
	} elseif(isset($params['birthdate'])) {
		if (preg_match( '/^([0-9]+)[^0-9]([0-9]+)[^0-9]([0-9]+)$/', $params['birthdate'], $date)) {
			if($params['order'] == 'd-m-Y'){
				if(checkdate($date[2], $date[1], $date[3])) {
					$time_nac = mktime(0, 0, 0, $date[2], $date[1], $date[3]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'd-Y-m'){
				if(checkdate($date[3], $date[1], $date[2])) {
					$time_nac = mktime(0, 0, 0, $date[3], $date[1], $date[2]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'm-d-Y') {
				if(checkdate($date[1], $date[2], $date[3])) {
					$time_nac = mktime(0, 0, 0, $date[1], $date[2], $date[3]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'm-Y-d') {
				if(checkdate($date[1], $date[3], $date[2])) {
					$time_nac = mktime(0, 0, 0, $date[1], $date[3], $date[2]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'Y-d-m') {
				if(checkdate($date[3], $date[2], $date[1])) {
					$time_nac = mktime(0, 0, 0, $date[3], $date[2], $date[1]);
				} else {
					$error = true;
				}
			} else {
				if(checkdate($date[2], $date[3], $date[1])) {
					$time_nac = mktime(0, 0, 0, $date[2], $date[3], $date[1]);
				} else {
					$error = true;
				}
			}
		} else {
			$error = true;
		}
	} else {
		$time_nac = time();
	}
	
	if(isset($params['today_month'], $params['today_day'], $params['today_year'])){
		$time = mktime(0, 0, 0, $params['today_month'], $params['today_day'], $params['today_year']);
	} elseif(isset($params['today'])) {
		if (preg_match( '/^([0-9]+)[^0-9]([0-9]+)[^0-9]([0-9]+)$/', $params['today'], $date)) {
			if($params['order'] == 'd-m-Y'){
				if(checkdate($date[2], $date[1], $date[3])) {
					$time = mktime(0, 0, 0, $date[2], $date[1], $date[3]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'd-Y-m'){
				if(checkdate($date[3], $date[1], $date[2])) {
					$time = mktime(0, 0, 0, $date[3], $date[1], $date[2]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'm-d-Y') {
				if(checkdate($date[1], $date[2], $date[3])) {
					$time = mktime(0, 0, 0, $date[1], $date[2], $date[3]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'm-Y-d') {
				if(checkdate($date[1], $date[3], $date[2])) {
					$time = mktime(0, 0, 0, $date[1], $date[3], $date[2]);
				} else {
					$error = true;
				}
			} elseif($params['order'] == 'Y-d-m') {
				if(checkdate($date[3], $date[2], $date[1])) {
					$time = mktime(0, 0, 0, $date[3], $date[2], $date[1]);
				} else {
					$error = true;
				}
			} else {
				if(checkdate($date[2], $date[3], $date[1])) {
					$time = mktime(0, 0, 0, $date[2], $date[3], $date[1]);
				} else {
					$error = true;
				}
			}	
		} else {
			$error = true;
		}
	} else {
		$time = time();
	}
 
	if(!$error){
		$edad = idate('Y' ,$time) - idate('Y' ,$time_nac);
	} else {
		$edad = 0;
	}

	if($edad>0){
		if(idate('m' ,$time) < idate('m' ,$time_nac)){
			$edad--;
		} else if(idate('m' ,$time) == idate('m' ,$time_nac)){
			if(idate('d' ,$time) < idate('d' ,$time_nac)){
				$edad--;
			}
		}
	} elseif($edad<0) {
		$edad = 0;
	}

	return $edad;
} 

/**
 * Elimina un directorio.
 *
 * @param string $dir ruta de directorio a eliminar
 * @return boolean
 */
function remove_dir($dir){
	/**
		Si no es una variable vacia
	**/
	$dir = join_path(func_get_args());
	
	/**
		Obtengo los archivos en el directorio a eliminar
	**/
	if($files = array_merge(glob(join_path($dir,'*')), glob(join_path($dir,'.*')))) {
		/**
			Elimino cada subdirectorio o archivo
		**/
		foreach($files as $file) {
			/**
				Si no son los directorios "." o ".." 
			**/
			if(!preg_match("/^.*\/?[\.]{1,2}$/",$file)) {
				if(is_dir($file)) {
					remove_dir($file);
				} else {
					unlink($file);
				}
			}
		}
	}
	return rmdir($dir);
}

/**
 * Obtiene un array de argumentos para usar con call_user_func_array a partir del array obtenido por get_params
 * @param $params array array de parametros obtenido de get_params
 * @return array
 **/
function get_arguments($params) {
	$args = array();
	foreach($params as $k=>$v) {
		if(is_numeric($k)) {
			array_push($args, $v);
		} else {
			array_push($args, "$k: $v");
		}
	}
	return $args;
}

/**
 * Coloca la primera letra en minuscula
 * @param s string cadena a convertir
 * @return string
 **/
function lcfirst($s) {
    return strtolower(substr($s, 0, 1)) . substr($s, 1);
}

/**
 * Crea un objeto stdClass apartir de parametros con nombre
 * @return object
 **/
function object_from_params($s='') {
	$params = is_array($s) ? $s : get_params(func_get_args());
	$obj = (object) $params;
	return $obj;
}

/**
 * Efectua la misma operacion que range excepto que el key es identico a val
 * @param mixed $start
 * @param mixed $end
 * @param int $step
 **/
function mirror_range($start, $end, $step=1) {
	$args = func_get_args(); 
	$arr = call_user_func_array('range', $args);
	$mirror = array();
	foreach($arr as $v) {
		$mirror[$v] = $v;
	}
	return $mirror;
}

/**
 * Obtiene la extension del archivo
 * @param string $filename nombre del archivo
 * @return string 
 **/
function file_extension($filename) {
	$ext = strchr($filename,".");
	return $ext;
} 

/**
 * Obtiene una url completa para la accion en el servidor
 * @param string $route ruta a la accion
 * @return string
 **/
function get_server_url($route) {
	$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
	return "$protocol://{$_SERVER['SERVER_NAME']}".get_kumbia_url($route);
}


/**
 * Carga uno o varios helpers (soporta argumento variable)
 * @param string $helper nombre del helper a cargar
 **/
function use_helper($helper) {
	call_user_func_array(array('view' , 'helpers'), func_get_args());
}


/**
 * Envia la salida en buffer al navegador
 *
 */
function content(){
	View::content();
}

/**
 * Trunca un texto
 * 
 * @param $text
 * @param $word
 * @param $number
 * @return unknown_type
 */
function truncate($text, $word, $number=0){
	if($number){
		$word = substr($word, 0, $number);
	} else {
		$word = rtrim($word);
	}
	
	return $word.$text;
}

/**
 * Renderiza una vista parcial
 * 
 * @param string $partial vista a renderizar
 * @param string $time tiempo de cache
 * @param array $params
 **/
function render_partial($partial, $time=false, $params=array()) {
	View::partial($partial, $time, $params);
}

/**
 * Redirecciona una a accion luego de transcurrir un periodo de tiempo
 * @param string $action accion a ejecutar
 * @param float $seconds segundos a esperar
 * @return string
 **/
function redirect_to($action, $seconds = 0.01){
	$seconds*=1000;
	return xhtml_tag('script', 'type: text/javascript', "content: setTimeout('window.location=\"?/$action\"', $seconds)");
}
