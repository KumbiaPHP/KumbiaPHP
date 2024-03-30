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
 * @package    Logger
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Permite realizar logs en archivos de texto en la carpeta Logs
 *
 * $fileLogger = Es el File Handle para escribir los logs
 * $transaction = Indica si hay o no transaccion
 * $queue = array con lista de logs pendientes
 *
 * Ej:
 * <code>
 * //Empieza un log en logs/logDDMMYY.txt
 *
 *
 * Logger::debug('Loggear esto como un debug');
 *
 * //Esto se guarda al log inmediatamente
 * Logger::error('Loggear esto como un error');
 *
 * //Inicia una transacción
 * Logger::begin();
 *
 * //Esto queda pendiente hasta que se llame a commit para guardar
 * //ó rollback para cancelar
 * Logger::warning('Esto es un log en la fila');
 * Logger::notice('Esto es un otro log en la fila');
 *
 * //Se guarda al log
 * Logger::commit();
 *
 * //Cierra el Log
 * Logger::close();
 * </code>
 *
 * @category   Kumbia
 * @package    Logger
 */
abstract class Logger
{

    /**
     * Resource hacia el Archivo del Log
     *
     * @var null|resource
     */
    private static $fileLogger;
    /**
     * @var
     */
    private static $log_name = null;
    /**
     * Indica si hay transaccion o no
     *
     * @var boolean
     */
    private static $transaction = false;
    /**
     * Array con mensajes de log en cola en una transsaccion
     *
     * @var array
     */
    private static $queue = array();
    /**
     * Path donde se guardaran los logs
     *
     * @var string
     */
    private static $log_path = '';

    /**
     * Inicializa el Logger
     *
     * @param string $name
     */
    public static function initialize(string $name = ''): void
    {
        self::$log_path = APP_PATH . 'temp/logs/'; //TODO poder cambiar el path
        if ($name === '') {
            $name = 'log' . date('Y-m-d') . '.txt';
        }
        self::$fileLogger = fopen(self::$log_path . $name, 'a');
        if (!self::$fileLogger) {
            throw new KumbiaException("No se puede abrir el log llamado: " . $name);
        }
    }

    /**
     * Especifica el PATH donde se guardan los logs
     *
     * @param string $path
     */
    public static function set_path(string $path): void
    {
        self::$log_path = $path;
    }

    /**
     * Obtener el path actual
     *
     * @return string
     */
    public static function get_path(): string
    {
        return self::$log_path;
    }

    /**
     * Almacena un mensaje en el log
     *
     * @param string $type
     * @param string|array $msg
     * @param string $name_log
     */
    public static function log(string $type = 'DEBUG', string|array $msg = '', string $name_log = ''): void
    {
        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }
        //TODO poder añadir otros formatos de log
        $date = date(DATE_RFC1036);
        $msg = "[$date][$type] " . $msg;
        if (self::$transaction) {
            self::$queue[] = $msg;
            return;
        }
        self::write($msg, $name_log);
    }

    /**
     * Escribir en el log
     *
     * @param string $msg
     */
    protected static function write(string $msg, string $name_log): void
    {
        self::initialize($name_log); //TODO dejarlo abierto cuando es un commit
        fputs(self::$fileLogger, $msg . PHP_EOL);
        self::close();
    }

    /**
     * Inicia una transacción
     *
     */
    public static function begin(): void
    {
        self::$transaction = true;
    }

    /**
     * Deshace una transacción
     *
     */
    public static function rollback(): void
    {
        self::$transaction = false;
        self::$queue = array();
    }

    /**
     * Commit a una transacción
     *
     * @param string $name_log
     */
    public static function commit(string $name_log = ''): void
    {
        foreach (self::$queue as $msg) {
            self::write($msg, $name_log);
        }
        self::$queue = array();
        self::$transaction = false;
    }

    /**
     * Cierra el Logger
     *
     */
    public static function close(): bool
    {
        if (!self::$fileLogger) {
            throw new KumbiaException("No se puede cerrar el log porque es invalido");
        }
        return fclose(self::$fileLogger);
    }

    /**
     * Genera un log de tipo WARNING
     *
     * @return void
     * @param string $msg
     * @param string $name_log
     */
    public static function warning(string $msg, string $name_log = ''): void
    {
        self::log('WARNING', $msg, $name_log);
    }

    /**
     * Genera un log de tipo ERROR
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function error(string|array $msg, string $name_log = ''): void
    {
        self::log('ERROR', $msg, $name_log);
    }

    /**
     * Genera un log de tipo DEBUG
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function debug(string|array $msg, string $name_log = ''): void
    {
        self::log('DEBUG', $msg, $name_log);
    }

    /**
     * Genera un log de tipo ALERT
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function alert(string|array $msg, string $name_log = ''): void
    {
        self::log('ALERT', $msg, $name_log);
    }

    /**
     * Genera un log de tipo CRITICAL
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function critical(string|array $msg, string $name_log = ''): void
    {
        self::log('CRITICAL', $msg, $name_log);
    }

    /**
     * Genera un log de tipo NOTICE
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function notice(string|array $msg, string $name_log = ''): void
    {
        self::log('NOTICE', $msg, $name_log);
    }

    /**
     * Genera un log de tipo INFO
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function info(string|array $msg, string $name_log = ''): void
    {
        self::log('INFO', $msg, $name_log);
    }

    /**
     * Genera un log de tipo EMERGENCE
     *
     * @return void
     * @param string|array $msg
     * @param string $name_log
     */
    public static function emergence(string|array $msg, string $name_log = ''): void
    {
        self::log('EMERGENCE', $msg, $name_log);
    }

    /**
     * Genera un log Personalizado
     *
     * @param string $type
     * @param string|array $msg
     * @param string $name_log
     */
    public static function custom(string $type = 'CUSTOM', string|array $msg = '', string $name_log = ''): void
    {
        self::log($type, $msg, $name_log);
    }

}
