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
// library load for cache management
Load::lib('cache');

/**
 * Console to manage the cache
 *
 * @category   Kumbia
 * @package    Console
 */
class CacheConsole
{

    /**
     * Console command to clear the cache
     *
     * @param array $params Named parameters of the console
     * @param string $group Group name (Optional)
     * @throws KumbiaException
     */
    public function clean($params, $group = '')
    {
        // Gets chache driver
        $cache = $this->setDriver($params);

        // Cleans cache
        if ($cache->clean($group)) {
            if ($group) {
                echo "-> The group has been cleaned $group", PHP_EOL;
            } else {
                echo "-> The cache has been cleared", PHP_EOL;
            }
        } else {
            throw new KumbiaException('Failed to remove cache content');
        }
    }

    /**
     * Console command to clear a element into cache
     *
     * @param array $params Named parameters of the console
     * @param string $id Element ID
     * @param string $group Group name (Optional)
     * @throws KumbiaException
     */
    public function remove($params, $id, $group = 'default')
    {
        // Gets chache driver
        $cache = $this->setDriver($params);

        // Removes element
        if ($cache->remove($id, $group)) {
            echo '-> The item has been removed from the cache', PHP_EOL;
        } else {
            throw new KumbiaException("Removing the item failed \"$id\" of the group \"$group\"");
        }
    }
    
    /**
     * Returns a cache instance of the passed driver
     *
     * @param array $params Named parameters of the console
     */
    private function setDriver($params)
    {
        if (isset($params['driver'])) {
            return Cache::driver($params['driver']);
        } 
        return Cache::driver();
        
    }

}
