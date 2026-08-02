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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see Util
 */
require CORE_PATH . 'kumbia/util.php';
/**
 * @see KumbiaException
 */
require CORE_PATH . 'kumbia/kumbia_exception.php';
/**
 * @see Config
 */
require CORE_PATH . 'kumbia/config.php';
/**
 * @see Load
 */
require CORE_PATH . 'kumbia/load.php';

/**
 * modificado por nelsonrojas
 * el problema: al usar console controller create produce un error en linea 85.
 *              no reconoce FileUtil
 * solucion: incluir la libreria con la linea siguiente
 */
require CORE_PATH . 'libs/file_util/file_util.php';

/**
 * Manejador de consolas de KumbiaPHP
 *
 * Consola para la creación de modelos.
 * Consola para la creación de controladores.
 * Consola para el manejo de cache.
 *
 * @category   Kumbia
 * @package    Core
 */
class Console
{

    /**
     * Genera la lista de argumentos para la consola, el primer argumento
     * retornado corresponde al array de parametros nombrados de terminal
     *
     * @param array $argv argumentos de terminal
     * @return array
     * */
    private static function _getConsoleArgs($argv)
    {
        $args = array(array());

        foreach ($argv as $p) {
            if (is_string($p) && preg_match("/--([a-z_0-9]+)[=](.+)/", $p, $regs)) {
                // carga en el array de parametros nombrados
                $args[0][$regs[1]] = $regs[2];
            } else {
                // lo carga como argumento simple
                $args[] = $p;
            }
        }

        return $args;
    }

    /**
     * Crea una instancia de la consola indicada
     *
     * @param string $console_name nombre de la consola
     * return object
     * @throw KumbiaException
     * */
    public static function load($console_name)
    {
        // nombre de la clase de consola
        $Console = Util::camelcase($console_name) . 'Console';

        if (!class_exists($Console)) {
            // intenta carga el archivo de consola
            $file = APP_PATH . "extensions/console/{$console_name}_console.php";

            if (!is_file($file)) {
                $file = CORE_PATH . "console/{$console_name}_console.php";

                if (!is_file($file)) {
                    throw new KumbiaException('Consola "' . $file . '" no se encontro');
                }
            }

            // incluye la consola
            include_once $file;
        }

        // crea la instancia de objeto
        $console = new $Console();

        // inicializa la consola
        if (method_exists($console, 'initialize')) {
            $console->initialize();
        }

        return $console;
    }

    /**
     * Despacha y carga la consola a ejecutar desde argumentos del terminal
     *
     * @param array $argv argumentos recibidos desde el terminal
     * @throw KumbiaException
     * */
    public static function dispatch($argv)
    {
        // Elimino el nombre de archivo del array de argumentos
        array_shift($argv);

        // obtiene el nombre de consola
        $console_name = array_shift($argv);
        if (!$console_name) {
            throw new KumbiaException('No ha indicado la consola a ejecutar');
        }

        // obtiene el nombre de comando a ejecutar
        $command = array_shift($argv);
        if (!$command) {
            $command = 'main';
        }

        // Obtiene los argumentos para la consola, el primer argumento
        // es el array de parametros nombrados para terminal
        $args = self::_getConsoleArgs($argv);

        // verifica el path de aplicacion
        if (isset($args[0]['path'])) {
            $path = $args[0]['path'];
            $dir = realpath($path);
            $trimmedPath = rtrim($path, '/\\');
            if ($dir === false && $trimmedPath !== '') {
                $dir = realpath($trimmedPath);
            }
            if (!$dir) {
                throw new KumbiaException("La ruta \"$path\" es invalida");
            }
            // elimina el parametro path del array
            unset($args[0]['path']);
        } else {
            // obtiene el directorio de trabajo actual
            $dir = getcwd();
            if ($dir === false) {
                throw new KumbiaException('No se pudo obtener el directorio de trabajo actual');
            }
        }

        // define el path de la aplicacion
        define('APP_PATH', self::resolveAppPath($dir));

        // lee la configuracion
        $config = Config::read('config');

        // constante que indica si la aplicacion se encuentra en produccion
        define('PRODUCTION', $config['application']['production'] ?? false);

        // crea la consola
        $console = self::load($console_name);

        // verifica que exista el comando en la consola
        if (!method_exists($console, $command)) {
            throw new KumbiaException("El comando \"$command\" no existe para la consola \"$console_name\"");
        }

        // si se intenta ejecutar
        if ($command == 'initialize') {
            throw new KumbiaException("El comando initialize es un comando reservado");
        }

        // verifica los parametros para la accion de consola
        $reflectionMethod = new ReflectionMethod($console, $command);
        if (count($args) < $reflectionMethod->getNumberOfRequiredParameters()) {
            throw new KumbiaException("Número de parametros erroneo para ejecutar el comando \"$command\" en la consola \"$console_name\"");
        }

        // ejecuta el comando
        call_user_func_array(array($console, $command), $args);
    }

    /**
     * Lee un dato de entrada desde la consola
     *
     * @param string $message mensaje a mostrar
     * @param array $values array de valores validos para entrada
     * @return string Valor leido desde la consola
     * */
    public static function input($message, $values = null)
    {
        // abre la entrada
        $stdin = fopen('php://stdin', 'r');

        do {
            // imprime el mensaje
            echo $message;

            // lee la linea desde el terminal
            $data = str_replace(PHP_EOL, '', fgets($stdin));
        } while ($values && !in_array($data, $values));

        // cierra el recurso
        fclose($stdin);

        return $data;
    }

    /**
     * Resolves the console application path from an app directory or project root.
     *
     * @param string $dir Application directory or KumbiaPHP project root
     *
     * @throws KumbiaException
     * @return string Canonical application path with one trailing separator
     */
    private static function resolveAppPath(string $dir): string
    {
        $candidates = [
            $dir,
            $dir . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'app',
        ];

        foreach ($candidates as $candidate) {
            $path = rtrim($candidate, '/\\');
            $path = $path === '' ? DIRECTORY_SEPARATOR : $path;
            $config = $path . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config';

            if (is_file("$config.php") || is_file("$config.ini")) {
                $resolved = realpath($candidate);
                if ($resolved !== false) {
                    return rtrim($resolved, '/\\') . DIRECTORY_SEPARATOR;
                }
            }
        }

        throw new KumbiaException(
            'No se encontró la aplicación. --path puede apuntar al directorio de la aplicación '
            . 'o a la raíz del proyecto KumbiaPHP'
        );
    }
}
