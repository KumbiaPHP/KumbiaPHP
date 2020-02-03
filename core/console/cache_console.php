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
// carga libreria para manejo de cache
Load::lib('cache');

/**
 * Consola para manejar la cache
 *
 * @category   Kumbia
 * @package    Console
 */
class CacheConsole
{

    /**
     * Comando de consola para limpiar la cache
     *
     * @param array $params parametros nombrados de la consola
     * @param string $group nombre de grupo
     * @throw KumbiaException
     */
    public function clean($params, $group = '')
    {
        // obtiene el driver de cache
        $cache = $this->setDriver($params);

        // limpia la cache
        if ($cache->clean($group)) {
            if ($group) {
                echo "-> Se ha limpiado el grupo $group", PHP_EOL;
            } else {
                echo "-> Se ha limpiado la cache", PHP_EOL;
            }
        } else {
            throw new KumbiaException('No se ha logrado eliminar el contenido de la cache');
        }
    }

    /**
     * Comando de consola para eliminar un elemento cacheado
     *
     * @param array $params parametros nombrados de la consola
     * @param string $id id del elemento
     * @param string $group nombre de grupo
     * @throw KumbiaException
     */
    public function remove($params, $id, $group = 'default')
    {
        // obtiene el driver de cache
        $cache = $this->setDriver($params);

        // elimina el elemento
        if ($cache->remove($id, $group)) {
            echo '-> Se ha eliminado el elemento de la cache', PHP_EOL;
        } else {
            throw new KumbiaException("No se ha logrado eliminar el elemento \"$id\" del grupo \"$group\"");
        }
    }
    
    /**
     * Devuelve una instancia de cache del driver pasado
     *
     * @param array $params parametros nombrados
     */
    private function setDriver($params)
    {
        if (isset($params['driver'])) {
            return Cache::driver($params['driver']);
        } 
        return Cache::driver();
        
    }

}
