<?php
/**
 * KumbiaPHP Web Framework 
 * Parámetros de conexión a la base de datos
 */
$databases['development'] = [
    /**
     * host: ip o nombre del host de la base de datos  
     */
    'host' => 'localhost',
    /**
     * username: usuario con permisos en la base de datos  
     */
    'username' => 'root', //no es recomendable usar el usuario root
    /**
     * password: clave del usuario de la base de datos 
     */
    'password' => '',
    /**
     * test: nombre de la base de datos
     */
    'name' => 'test',
    /**
     * type: tipo de motor de base de datos (mysql, pgsql, oracle o sqlite)
     */
    'type' => 'mysql',
    /**
     * charset: este valor es necesario para abrir conexiones UTF-8
     */
    'charset' => 'utf8',
    /**
     * dsn: para usar conexiones PDO; descomentar para usarlo
     */
    //'dsn' => '',
    /**
     * pdo: activar conexiones PDO (On/Off); descomentar para usar
     */
    //'pdo' => 'On',
];


$databases['production'] = [
    /**
     * host: ip o nombre del host de la base de datos
     */
    'host' => 'localhost',
    /**
     * username: usuario con permisos en la base de datos
     */
    'username' => 'root', //no es recomendable usar el usuario root
    /**
     * password: clave del usuario de la base de datos
     */
    'password' => '',
    /**
     * test: nombre de la base de datos
     */
    'name' => 'test',
    /**
     * type: tipo de motor de base de datos (mysql, pgsql o sqlite)
     */
    'type' => 'mysql',
    /**
     * charset: este valor es necesario para abrir conexiones UTF-8
     */
    'charset' => 'utf8',
    /**
     * dsn: para usar conexiones PDO; descomentar para usarlo
     */
    //'dsn' => '',
    /**
     * pdo: activar conexiones PDO (OnOff); descomentar para usar
     */
    //'pdo' => 'On',
];

/**
 * Ejemplo de SQLite
 */
/* $databases['development'] = [  
  'type' => 'sqlite',
  'dsn' => 'temp/data.sq3',
  'pdo' => 'On',
  ]; */

return $databases; //Siempre al final
