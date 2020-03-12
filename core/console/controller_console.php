<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Console
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Console to manage the cache
 *
 * @category   Kumbia
 * @package    Console
 */
class ControllerConsole
{

    /**
     * Console command to create a controller
     *
     * @param array $params Named parameters of the console
     * @param string $controller Controller name
     * @throws KumbiaException
     */
    public function create($params, $controller)
    {
        // File name
        $file = APP_PATH . 'controllers';

        // Cleans the controller path
        $clean_path = trim($controller, '/');

        // Gets path
        $path = explode('/', $clean_path);

        // Gets the controller name
        $controller_name = array_pop($path);

        // Is controller into a directory
        if (count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if (!is_dir($file) && !FileUtil::mkdir($file)) {
                throw new KumbiaException("No se ha logrado crear el directorio \"$file\"");
            }
        }
        $file .= "/{$controller_name}_controller.php";

        // If it does not exist or should be overwritten
        if (!is_file($file) ||
                Console::input("El controlador existe, ¿desea sobrescribirlo? (s/n): ", array('s', 'n')) == 's') {

            // Class name
            $class = Util::camelcase($controller_name);

            // Controller code
            ob_start();
            include __DIR__ . '/generators/controller.php';
            $code = '<?php' . PHP_EOL . ob_get_clean();

            // Generates file
            if (file_put_contents($file, $code)) {
                echo "-> Creado controlador $controller_name en: $file" . PHP_EOL;
            } else {
                throw new KumbiaException("No se ha logrado crear el archivo \"$file\"");
            }

            // Views directory
            $views_dir = APP_PATH . "views/$clean_path";

            // If folder does not exist
            if (!is_dir($views_dir)) {
                if (FileUtil::mkdir($views_dir)) {
                    echo "-> Creado directorio para vistas: $views_dir" . PHP_EOL;
                } else {
                    throw new KumbiaException("No se ha logrado crear el directorio \"$views_dir\"");
                }
            }
        }
    }

    /**
     * Console command to delete a controller
     *
     * @param array $params Named parameters of the console
     * @param string $controller Controller name
     * @throws KumbiaException
     */
    public function delete($params, $controller)
    {
        // Cleans the controller path
        $clean_path = trim($controller, '/');

        // File name
        $file = APP_PATH . "controllers/$clean_path";

        // If it is a directory
        if (is_dir($file)) {
            $success = FileUtil::rmdir($file);
        } else {
            // so is a file
            $file = "{$file}_controller.php";
            $success = unlink($file);
        }

        // Message
        if ($success) {
            echo "-> Eliminado: $file" . PHP_EOL;
        } else {
            throw new KumbiaException("No se ha logrado eliminar \"$file\"");
        }

        // Views directory
        $views_dir = APP_PATH . "views/$clean_path";

        // Try deletes the views directory
        if (is_dir($views_dir)
                && Console::input('¿Desea eliminar el directorio de vistas? (s/n): ', array('s', 'n')) == 's') {

            if (!FileUtil::rmdir($views_dir)) {
                throw new KumbiaException("No se ha logrado eliminar \"$views_dir\"");
            }

            echo "-> Eliminado: $views_dir" . PHP_EOL;
        }
    }

}