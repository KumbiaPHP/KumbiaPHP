<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * 
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbiaphp.com/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category  Kumbia
 * @package   Helpers
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright 2007-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2007-2008 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN:$id
 */

/**
 * Helper para Tags HTML
 *
 * @category  Kumbia
 * @package   Helpers
 * @author    Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright 2005-2008 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright 2007-2008 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN:$id
 */
class Util
{
    /**
     * Convierte la cadena en notacion camelcase
	 *
     * @param string $s cadena a convertir
	 * @param boolean $lower indica si es lower camelcase
     * @return string
     **/
    public static function camelcase($s, $lower=false)
    {
        $w = ucwords(preg_replace('/[\s_]+/', ' ', trim($s)));
        /**
         * Notacion lowerCamelCase
         **/
        if($lower) {
            $w = Util::lcfirst($w);
        }
        return str_replace(' ', '', $w);
    }
    /**
     * Convierte la cadena en notacion smallcase
     * @param string $s cadena a convertir
     * @return string
     **/
    public static function smallcase($s) {
        return strtolower(preg_replace('/([A-Z])/', "_\\1", Util::lcfirst(trim($s))));
    }
    /**
     * Remplaza en la cadena los espacios por underscores
     * @param string $s
     * @return string
     **/
    public static function underscore($s)
    {
        return preg_replace('/\s+/', '_', $s);
    }
    /**
     * Remplaza en la cadena los espacios por dash (guiones)
     * @param string $s
     * @return string
     **/
    public static function dash($s)
    {
        return preg_replace('/\s+/', '-', $s);
    }
    /**
     * Remplaza en una cadena los underscore o dashed por espacios
     * @param string $s
     * @return string
     **/
    public static function humanize($s)
    {
        return preg_replace('/[_-]+/', ' ', $s);
    }
    /**
     * Merge Two Arrays Overwriting Values $a1
     * from $a2
     *
     * @param array $a1
     * @param array $a2
     * @return array
     */
    public static function array_merge_overwrite($a1, $a2)
    {
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
                    $a1[$key2] = self::arrayMergeOverwrite($a1[$key2], $a2[$key2]);
                }
            }
        }
        return $a1;
    }
    /**
     * Insert para arrays numericos
     *
     * @param &array $array array donde se insertara (por referencia)
     * @param int $index indice donde se realizara la insercion
     * @param mixed $value valor a insertar
     **/
    public static function array_insert(&$array, $index, $value)
    {
        $array2 = array_splice($array, $index);
        array_push($array, $value);
        $array = array_merge($array, $array2);
    }
    /**
     * Las siguientes funciones son utilizadas para la generacion
     * de versiones escritas de numeros
     *
     * @param numeric $a
     * @return string
     */
    public static function value_num($a)
    {
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
                return "VENTI".self::valueNum($a % 10);
                if($a==30) return  "TREINTA";
                if($a>=31&&$a<=39)
                return "TREINTA Y ".self::valueNum($a % 10);
                if($a==40) $b = "CUARENTA";
                if($a>=41&&$a<=49)
                return "CUARENTA Y ".self::valueNum($a % 10);
                if($a==50) return "CINCUENTA";
                if($a>=51&&$a<=59)
                return "CINCUENTA Y ".self::valueNum($a % 10);
                if($a==60) return "SESENTA";
                if($a>=61&&$a<=69)
                return "SESENTA Y ".self::valueNum($a % 10);
                if($a==70) return "SETENTA";
                if($a>=71&&$a<=79)
                return "SETENTA Y ".self::valueNum($a % 10);
                if($a==80) return "OCHENTA";
                if($a>=81&&$a<=89)
                return "OCHENTA Y ".self::valueNum($a % 10);
                if($a==90) return "NOVENTA";
                if($a>=91&&$a<=99)
                return "NOVENTA Y ".self::valueNum($a % 10);
            } else {
                if($a==100) return "CIEN";
                if($a>=101&&$a<=199)
                return "CIENTO ".self::valueNum($a % 100);
                if($a>=200&&$a<=299)
                return "DOSCIENTOS ".self::valueNum($a % 100);
                if($a>=300&&$a<=399)
                return "TRECIENTOS ".self::valueNum($a % 100);
                if($a>=400&&$a<=499)
                return "CUATROCIENTOS ".self::valueNum($a % 100);
                if($a>=500&&$a<=599)
                return "QUINIENTOS ".self::valueNum($a % 100);
                if($a>=600&&$a<=699)
                return "SEICIENTOS ".self::valueNum($a % 100);
                if($a>=700&&$a<=799)
                return "SETECIENTOS ".self::valueNum($a % 100);
                if($a>=800&&$a<=899)
                return "OCHOCIENTOS ".self::valueNum($a % 100);
                if($a>=901&&$a<=999)
                return "NOVECIENTOS ".self::valueNum($a % 100);
            }
        }
    }
    /**
     * Genera una cadena de millones
     *
     * @param numeric $a
     * @return string
     */
    public static function millones($a)
    {
        $a = $a / 1000000;
        if($a==1)
        return "UN MILLON ";
        else
        return self::valueNum($a)." MILLONES ";
    }
    /**
     * Genera una cadena de miles
     *
     * @param numeric $a
     * @return string
     */
    public static function miles($a)
    {
        $a = $a / 1000;
        if($a==1)
        return "MIL";
        else
        return self::valueNum($a)."MIL ";
    }
    /**
     * Escribe en letras un monto numerico
     *
     * @param numeric $valor
     * @param string $moneda
     * @param string $centavos
     * @return string
     */
    public static function money_letter($valor, $moneda, $centavos)
    {
        $a = $valor;
        $p = $moneda;
        $c = $centavos;
        $val = "";
        $v = $a;
        $a = (int) $a;
        $d = round($v - $a, 2);
        if($a>=1000000){
            $val = self::millones($a - ($a % 1000000));
            $a = $a % 1000000;
        }
        if($a>=1000){
            $val.= self::miles($a - ($a % 1000));
            $a = $a % 1000;
        }
        $val.= self::valueNum($a)." $p ";
        if($d){
            $d*=100;
            $val.=" CON ".self::valueNum($d)." $c ";
        }
        return $val;
    }
    /**
     * Escribe un valor en bytes en forma humana
     *
     * @param integer $num
     * @return string
     */
    public static function to_human($num)
    {
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
     * Convierte los argumentos de una funcion o metodo a parametros por nombre
     *
     * @param array $params argumentos de la funcion de donde se analizaran los argumentos
     * @param int $fixed_args numero de argumentos fijos en la funcion de donde se analizaran los argumentos
	 * @return array
     */
    public static function get_params($params, $fixed_args=0)
    {
		$data = array();
		$i = $fixed_args;
		$len = count($params);
		do {
			$match = explode(': ', $params[$i], 2);
			$data[$match[0]] = $match[1];
		} while(++$i < $len);
		return $data;
    }
    /**
     * Obtiene un arreglo con los parametros pasados por terminal
     *
     * @param string $params arreglo de parametros con nombres con el formato de terminal
     * @return array
     */
    public static function get_term_params($params){
        $data = array();
        foreach ($params as $p) {
            if(is_string($p) && preg_match("/--([a-z_0-9]+)[=](.+)/", $p, $regs)){
                $data[$regs[1]] = $regs[2];
            }
        }
        return $data;
    }
    /**
     * Devuelve una URL adecuada de Kumbia
     *
     * @param string $url
     * @return string
     */
    public static function get_kumbia_url($url)
    {        
		$return_url = URL_PATH;
		
		$action = $url;
		$module = '';
		if(is_array($url)){
			$action = $url[0];
			if(isset($url['module'])){
				$module = $url['module'];
			}
			if(isset($url['application']) && $url['application']){
				$application = $url['application'];
			}
		}
		if($module){
			$return_url.=$module.'/';
		}
		$return_url.=$action;
		return $return_url;
    }
    /*
     * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
     *
     * @param string $lista Cadena con Items separados por comas (,).
     * @return string Cadena con Items encerrados en doblecomillas y separados por comas (,).
     */
    public static function encomillar_lista($lista)
    {
        $arrItems = split(",", $lista);
        $n = count($arrItems);
        $listaEncomillada = "";
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
    public static function comillas($word)
    {
        return "'$word'";
    }
    /**
     * Resalta un Texto en otro Texto
     *
     * @param string $sentence
     * @param string $what
     * @return string
     */
    public static function highlight($sentence, $what)
    {
        return str_replace($what, '<strong class="highlight">'.$what.'</strong>', $sentence);
    }
    /**
     * Escribe un numero usando formato numerico
     *
     * @param string $number
     * @return string
     */
    public static function money($number)
    {
        $number = self::myRound($number);
        return "$&nbsp;".number_format($number, 2, ",", ".");
    }
    /**
     * Redondea un numero
     *
     * @param numeric $n
     * @param integer $d
     * @return string
     */
    public static function roundnumber($n, $d = 0)
    {
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
    public static function my_round($number, $n=2)
    {
        $number = (float) $number;
        $n = (int) $number;
        return ActiveRecord::staticSelectOne("round($number, $n)");
    }
    /**
     * Copia un directorio.
     *
     * @param string $source directorio fuente
     * @param string $target directorio destino
     */
    public static function copy_dir($source, $target)
    {
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
                    self::copy_dir($Entry, $target.'/'.$entry);
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
     * Crea un path.
     *
     * @param string $path ruta a crear
     * @return boolean
     */
    public static function mkpath($path)
    {
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
     *
     * @return integer
     */
    public static function age()
    {
        $params = self::getParams(func_get_args());
        $error = false;
        
        if(!isset($params['order'])){
            $dbdate = Config::get('config.application.dbdate');
            if(preg_match('/^DD[^DMY]MM[^DMY]YYYY$/', $dbdate)){
                $params['order'] = 'd-m-Y';
            } elseif(preg_match('/^DD[^DMY]YYYY[^DMY]MM$/', $dbdate)){
                $params['order'] = 'd-Y-m';
            } elseif(preg_match('/^MM[^DMY]DD[^DMY]YYYY$/', $dbdate)) {
                $params['order'] = 'm-d-Y';
            } elseif(preg_match('/^MM[^DMY]YYYY[^DMY]DD$/', $dbdate)) {
                $params['order'] = 'm-Y-d';
            } elseif(preg_match('/^YYYY[^DMY]DD[^DMY]MM$/', $dbdate)) {
                $params['order'] = 'Y-d-m';
            } else {
                $params['order'] = 'Y-m-d';
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
    public static function removedir($dir)
    {
 
        /**
            Obtengo los archivos en el directorio a eliminar
        **/
        if($files = array_merge(glob("$dir/*"), glob("$dir/.*"))) {
            /**
                Elimino cada subdirectorio o archivo
            **/
            foreach($files as $file) {
                /**
                    Si no son los directorios "." o ".." 
                **/
                if(!preg_match("/^.*\/?[\.]{1,2}$/",$file)) {
                    if(is_dir($file)) {
                        return self::removeDir($file);
                    } elseif(!@unlink($file)) {
                        return false;
                    }
                }
            }
        }
        return @rmdir($dir);
    }
    /**
     * Coloca la primera letra en minuscula
     *
     * @param s string cadena a convertir
     * @return string
     **/
    public static function lcfirst($s)
    {
        return strtolower(substr($s, 0, 1)) . substr($s, 1);
    }
    /**
     * Efectua la misma operacion que range excepto que el key es identico a val
     *
     * @param mixed $start
     * @param mixed $end
     * @param int $step
     * @return array
     **/
    public static function mirror_range($start, $end, $step=1)
    {
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
     *
     * @param string $filename nombre del archivo
     * @return string
     **/
    public static function file_extension($filename)
    {
        $ext = strchr($filename,".");
        return $ext;
    }
    /**
     * Obtiene una url completa para la accion en el servidor
     *
     * @param string $route ruta a la accion
     * @return string
     **/
    public static function get_server_url($route)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http';
        return "$protocol://{$_SERVER['SERVER_NAME']}" . self::get_kumbia_url($route);
    }
    /**
     * Trunca una cadena
     *
     * @param string $word cadena a truncar
     * @param int $number numero de caracteres
     * @return string
     **/
    public static function truncate($word, $number=0)
    {
        if($number){
            return substr($word, 0, $number);
        } else {
            return rtrim($word);
        }
    }
}