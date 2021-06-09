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
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (https://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Console to manage models.
 *
 * @category   Kumbia
 * @package    Console
 */
class ModelConsole
{
    /**
     * Console command to create a model
     *
     * @param array  $params Named parameters of the console
     * @param string $model  Model name
     * @throws KumbiaException
     */
    public function create($params, $model)
    {
        // File name
        $file = APP_PATH.'models';

        // Gets path
        $path = explode('/', trim($model, '/'));

        // Gets the model name
        $model_name = array_pop($path);

        if (count($path)) {
            $dir = implode('/', $path);
            $file .= "/$dir";
            if (!is_dir($file) && !FileUtil::mkdir($file)) {
                throw new KumbiaException("Failed to create directory \"$file\"");
            }
        }
        $file .= "/$model_name.php";

        // If it does not exist or should be overwritten
        if (!is_file($file) ||
            Console::input('The model exists, do you want to overwrite it? (y / n): ', array('y', 'n')) == 'y') {
            // Class name
            $class = Util::camelcase($model_name);

            // Model code
            ob_start();
            include __DIR__.'/generators/model.php';
            $code = '<?php'.PHP_EOL.ob_get_clean();

            // Generates file
            if (file_put_contents($file, $code)) {
                echo "-> Model $model_name created in: $file".PHP_EOL;
            } else {
                throw new KumbiaException("Failed to create file \"$file\"");
            }
        }
    }

    /**
     * Console command to delete a model
     *
     * @param array  $params Named parameters of the console
     * @param string $model  Model name
     * @throws KumbiaException
     */
    public function delete($params, $model)
    {
        // File name
        $file = APP_PATH.'models/'.trim($model, '/');

        // If it is a directory
        if (is_dir($file)) {
            $success = FileUtil::rmdir($file);
        } else {
            // so is a file
            $file = "$file.php";
            $success = unlink($file);
        }

        // Message
        if ($success) {
            echo "-> Deleted: $file".PHP_EOL;
        } else {
            throw new KumbiaException("Failed to delete \"$file\"");
        }
    }
}
