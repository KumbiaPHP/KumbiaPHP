<?php

/*
|------------------------------------------------------------
| Kumbia Web Framework Database Configuration
| Parámetros de base de datos
| Utiliza el nombre del controlador nativo (mysql, pgsql, oracle)
| Coloca database.pdo = On si usas PHP Data Objects
|------------------------------------------------------------
*/


$databases['development'] = [
  /*
  |---------------------------------------------------------
  |host: ip o nombre del host de la base de datos
  |---------------------------------------------------------
  */
  'host'      => 'localhost',
  /*
  |---------------------------------------------------------
  |username: usuario con permisos en la base de datos
  |---------------------------------------------------------
  */
  'username' => 'root', //no es recomendable usar el usuario root
  /*
  |---------------------------------------------------------
  |password: clave del usuario de la base de datos
  |---------------------------------------------------------
  */
  'password' => '',
  /*
  |---------------------------------------------------------
  |test: nombre de la base de datos
  |---------------------------------------------------------
  */
  'name' => 'test',
  /*
  |---------------------------------------------------------
  |type: tipo de motor de base de datos (mysql|pgsql|sqlite)
  |---------------------------------------------------------
  */
  'type' => 'mysql',
  /*
  |---------------------------------------------------------
  |charset: este valor es necesario para abrir conexiones UTF-8
  |---------------------------------------------------------
  */
  'charset' => 'utf8',
  /*
  |---------------------------------------------------------
  |dns: para usar conexiones PDO; descomentar para usarlo
  |---------------------------------------------------------
  */
  //'dns' => '',
  /*
  |---------------------------------------------------------
  |pdo: activar conexiones PDO (On|Off); descomentar para usar
  |---------------------------------------------------------
  */
  //'pdo' => 'On',
];


$databases['production'] = [
  /*
  |---------------------------------------------------------
  |host: ip o nombre del host de la base de datos
  |---------------------------------------------------------
  */
  'host'      => 'localhost',
  /*
  |---------------------------------------------------------
  |username: usuario con permisos en la base de datos
  |---------------------------------------------------------
  */
  'username' => 'root', //no es recomendable usar el usuario root
  /*
  |---------------------------------------------------------
  |password: clave del usuario de la base de datos
  |---------------------------------------------------------
  */
  'password' => '',
  /*
  |---------------------------------------------------------
  |test: nombre de la base de datos
  |---------------------------------------------------------
  */
  'name' => 'test',
  /*
  |---------------------------------------------------------
  |type: tipo de motor de base de datos (mysql|pgsql|sqlite)
  |---------------------------------------------------------
  */
  'type' => 'mysql',
  /*
  |---------------------------------------------------------
  |charset: este valor es necesario para abrir conexiones UTF-8
  |---------------------------------------------------------
  */
  'charset' => 'utf8',
  /*
  |---------------------------------------------------------
  |dns: para usar conexiones PDO; descomentar para usarlo
  |---------------------------------------------------------
  */
  //'dns' => '',
  /*
  |---------------------------------------------------------
  |pdo: activar conexiones PDO (On|Off); descomentar para usar
  |---------------------------------------------------------
  */
  //'pdo' => 'On',
];

/*
|---------------------------------------------------------
| Ejemplo de SQLite ; descomentar para usar
|---------------------------------------------------------
*/
//$databases['development'] = [
//  /*
//  |---------------------------------------------------------
//  |type: tipo de motor de base de datos (mysql|pgsql|sqlite)
//  |---------------------------------------------------------
//  */
//  'type' => 'sqlite',
//  /*
//  |---------------------------------------------------------
//  |dns: para usar conexiones PDO; elativo al APP_PATH (carpeta app)
//  |---------------------------------------------------------
//  */
//  'dns' => 'temp/data.sq3',
//  /*
//  |---------------------------------------------------------
//  |pdo: activar conexiones PDO (On|Off); descomentar para usar
//  |---------------------------------------------------------
//  */
//  'pdo' => 'On',
//];

//$databases['production'] = [
//  /*
//  |---------------------------------------------------------
//  |type: tipo de motor de base de datos (mysql|pgsql|sqlite)
//  |---------------------------------------------------------
//  */
//  'type' => 'sqlite',
//  /*
//  |---------------------------------------------------------
//  |dns: para usar conexiones PDO; elativo al APP_PATH (carpeta app)
//  |---------------------------------------------------------
//  */
//  'dns' => 'temp/data.sq3',
//  /*
//  |---------------------------------------------------------
//  |pdo: activar conexiones PDO (On|Off); descomentar para usar
//  |---------------------------------------------------------
//  */
//  'pdo' => 'On',
//];


return $databases; //Siempre al final
