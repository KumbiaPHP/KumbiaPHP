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
 * Consola para manejar modelos
 *
 * @category   Kumbia
 * @package    consoles
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class ModelConsole
{
    /**
     * Comando de consola para crear un modelo
     *
     * @param array $params parametros nombrados de la consola
     * @param string $model modelo
     **/
    public function create($params, $model)
    {   
        // nombre de archivo
        $file = APP_PATH . 'models';
        
        // obtiene el path
        $path = explode('/', trim($model, '/'));
        
        // obtiene el nombre de modelo
        $model_name = array_pop($path);
        
        if(count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if(!is_dir($file) && !Util::mkpath($file)) {
                throw new KumbiaException("No se ha logrado crear el directorio \"$file\"");
            }
        }
        $file .= "/$model_name.php";
        
        // nombre de clase
        $class = Util::camelcase($model_name);
        
        // codigo de modelo
        $code = <<<EOT
<?php
/**
 * Modelo $class
 * 
 * @category app
 * @package models
 **/
class $class extends ActiveRecord
{

}
EOT;

        // genera el archivo
        if(file_put_contents($file, $code)) {
            echo "-> Creado modelo $model_name en: $file\n";
        } else {
            echo "Operación Fallida\n";
        }
    }
    
    /**
     * Comando de consola para eliminar un modelo
     *
     * @param array $params parametros nombrados de la consola
     * @param string $model modelo
     **/
    public function delete($params, $model)
    {
        // nombre de archivo
        $file = APP_PATH . 'models/' . trim($model, '/');

        // si es un directorio
        if(is_dir($file)) {
            $success = Util::removedir($file);
        } else {
            // entonces es un archivo
            $file = "$file.php";
            $success = unlink($file);
        }
        
        // mensaje
        if($success) {
            echo "-> Eliminado: $file\n";
        } else {
            echo "Operación Fallida\n";
        }
    }
}