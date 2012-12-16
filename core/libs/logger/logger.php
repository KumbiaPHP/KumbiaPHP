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
 * @category   Kumbia
 * @package    Flash 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Permite realizar logs en archivos de texto en la carpeta Logs
 *
 * $fileLogger = Es el File Handle para escribir los logs
 * $transaction = Indica si hay o no transaccion
 * $quenue = array con lista de logs pendientes
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
 * @package    Flash 
 */
abstract class Logger
{

    /**
     * Resource hacia el Archivo del Log
     *
     * @var resource
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
    private static $quenue = array();
    /**
     * Path donde se guardaran los logs
     *
     * @var string
     */
    private static $log_path = '';

    /**
     * Inicializa el Logger
     */
    public static function initialize($name='')
    {
        self::$log_path = APP_PATH . 'temp/logs/';
        if ($name === '' || $name === true) {
            $name = 'log' . date('Y-m-d') . '.txt';
        }
        self::$fileLogger = fopen(self::$log_path . $name, 'a');
        if (!self::$fileLogger) {
            throw new KumbiaException("No se puede abrir el log llamado: " . $name);
            return false;
        }
    }

    /**
     * Especifica el PATH donde se guardan los logs
     *
     * @param string $path
     */
    public static function set_path($path)
    {
        self::$log_path = $path;
    }

    /**
     * Obtener el path actual
     *
     * @return $path
     */
    public static function get_path()
    {
        return self::$log_path;
    }

    /**
     * Almacena un mensaje en el log
     *
     * @param string $type
     * @param string $msg
     * @param string $name_log
     */
    public static function log($type='DEBUG', $msg, $name_log)
    {
        self::initialize($name_log);
        if (!self::$fileLogger) {
            throw new KumbiaException('No se puede enviar mensaje al log porque es invalido');
        }
        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }
        $date = date(DATE_RFC1036);
        if (self::$transaction) {
            self::$quenue[] = "[$date][$type] " . $msg;
        } else {
            fputs(self::$fileLogger, "[$date][$type] " . $msg . PHP_EOL);
        }
        self::close();
    }

    /**
     * Inicia una transacción
     *
     */
    public static function begin()
    {
        self::$transaction = true;
    }

    /**
     * Deshace una transacción
     *
     */
    public static function rollback()
    {
        self::$transaction = false;
        self::$quenue = array();
    }

    /**
     * Commit a una transacción
     */
    public static function commit()
    {
        self::$transaction = false;
        foreach (self::$quenue as $msg) {
            self::log($msg);
        }
    }

    /**
     * Cierra el Logger
     *
     */
    public static function close()
    {
        if (!self::$fileLogger) {
            throw new KumbiaException("No se puede cerrar el log porque es invalido");
        }
        return fclose(self::$fileLogger);
    }

    /**
     * Genera un log de tipo WARNING
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function warning($msg, $name_log='')
    {
        self::log('WARNING', $msg, $name_log);
    }

    /**
     * Genera un log de tipo ERROR
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function error($msg, $name_log='')
    {
        self::log('ERROR', $msg, $name_log);
    }

    /**
     * Genera un log de tipo DEBUG
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function debug($msg, $name_log='')
    {
        self::log('DEBUG', $msg, $name_log);
    }

    /**
     * Genera un log de tipo ALERT
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function alert($msg, $name_log='')
    {
        self::log('ALERT', $msg, $name_log);
    }

    /**
     * Genera un log de tipo CRITICAL
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function critical($msg, $name_log='')
    {
        self::log('CRITICAL', $msg, $name_log);
    }

    /**
     * Genera un log de tipo NOTICE
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function notice($msg, $name_log='')
    {
        self::log('NOTICE', $msg, $name_log);
    }

    /**
     * Genera un log de tipo INFO
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function info($msg, $name_log='')
    {
        self::log('INFO', $msg, $name_log);
    }

    /**
     * Genera un log de tipo EMERGENCE
     * 
     * @return 
     * @param string $msg
     * @param string $name_log
     */
    public static function emergence($msg, $name_log='')
    {
        self::log('EMERGENCE', $msg, $name_log);
    }

    /**
     * Genera un log Personalizado
     * 
     * @param string $type
     * @param string $msg
     * @param string $name_log
     */
    public static function custom($type='CUSTOM', $msg, $name_log='')
    {
        self::log($type, $msg, $name_log);
    }

}