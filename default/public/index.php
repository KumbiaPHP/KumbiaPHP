<?php
/**
 * KumbiaPHP web & app Framework.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Esta sección prepara el entorno
 * Todo esto se puede hacer desde la configuración del
 * Servidor/PHP, en caso de no poder usarlo desde ahí
 * Puedes descomentar  estas lineas.
 */

//*Locale*
//setlocale(LC_ALL, 'es_ES');

//*Timezone*
//ini_set('date.timezone', 'America/New_York');

/**
 * @TODO
 * REVISAR ESTA SECCIÓN
 */
const APP_CHARSET = 'UTF-8';

/*
 * Indicar si la aplicacion se encuentra en producción
 * directamente desde el index.php
 *
 * ¡¡¡ ADVERTENCIA !!!
 * Cuando se efectua el cambio de production=false, a production=true, es necesario eliminar
 * el contenido del directorio de cache de la aplicación para que se renueve
 * la metadata (/app/tmp/cache/*)
 */
const PRODUCTION = false;

/*
 * Descomentar para mostrar los errores
 */
//error_reporting(E_ALL ^ E_STRICT);ini_set('display_errors', 'On');

/*
 * Define el APP_PATH
 *
 * APP_PATH:
 * - Ruta al directorio de la aplicación (por defecto la ruta al directorio app)
 * - Esta ruta se utiliza para cargar los archivos de la aplicacion
 * - En producción, es recomendable ponerla manual usando const
 */
define('APP_PATH', dirname(__DIR__).'/app/');
//const APP_PATH = '/path/to/app/';

/*
 * Define el CORE_PATH
 *
 * CORE_PATH:
 * - Ruta al directorio que contiene el núcleo de Kumbia (por defecto la ruta al directorio core)
 * - En producción, es recomendable ponerla manual usando const
 */
define('CORE_PATH', dirname(dirname(APP_PATH)).'/core/');
//const CORE_PATH = '/path/to/core/';

/*
 * Define el PUBLIC_PATH.
 *
 * PUBLIC_PATH:
 * - Path para genera la Url en los links a acciones y controladores
 * - Esta ruta la utiliza Kumbia como base para generar las Urls para acceder de lado de
 *   cliente (con el navegador web) y es relativa al DOCUMENT_ROOT del servidor web
 *
 *  EN PRODUCCION ESTA CONSTANTE DEBERÍA SER ESTABLECIDA MANUALMENTE
 */
define('PUBLIC_PATH', substr($_SERVER['SCRIPT_NAME'], 0, -9)); // - index.php string[9]

/**
 * En producción descomentar la línea de arriba y usar const
 * '/'          en el root del dominio, recomendado
 * '/carpeta/'  en una carpeta o varias
 * 'https://www.midominio.com/'  usando dominio.
 */
//const PUBLIC_PATH = '/';

/**
 * Obtiene la url usando PATH_INFO.
 */
$url = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';

/**
 * Obtiene la url usando $_GET['_url']
 * Cambiar también en el .htaccess.
 */
 //$url = isset($_GET['_url']) ? $_GET['_url'] : '/';

/**
 * Carga el gestor de arranque
 * Por defecto el bootstrap del core.
 *
 * @see Bootstrap
 */
//require APP_PATH . 'libs/bootstrap.php'; //bootstrap de app
require CORE_PATH.'kumbia/bootstrap.php'; //bootstrap del core
