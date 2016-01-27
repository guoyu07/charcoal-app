<?php

namespace Charcoal\App\Provider;

// Dependencies from `pimple/pimple`
use \Pimple\ServiceProviderInterface;
use \Pimple\Container;

// Dependencies from `tedivm/stash`
use \Stash\DriverList;
use \Stash\Driver\Ephemeral;
use \Stash\Pool;

// Intra-Module `charcoal-app` dependencies
use \Charcoal\App\Config\CacheConfig;

/**
 * Cache Service Provider. Provider a Stash cache pool.
 *
 * ## Services
 * - `cache` `\Stash\Pool
 *
 * ## Helpers
 * - `cache/config` `\Charcoal\App\Config\CacheConfig`
 * - `cache/driver` `Stash\Interfaces\DriverInterface`
 */
class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance.
     * @return void
     */
    public function register(Container $container)
    {
        /**
        * @param Container $container A container instance.
        * @return CacheConfig
        */
        $container['cache/config'] = function (Container $container) {
            $appConfig = $container['config'];

            $cacheConfig =  new CacheConfig($appConfig->get('cache'));
            return $cacheConfig;
        };

        $container['cache/available-drivers'] = \Stash\DriverList::getAvailableDrivers();

        /**
        * @param Container $container A container instance.
        * @return Container The Collection of cache drivers, in a Container.
        */
        $container['cache/drivers'] = function (Container $container) {
            $cacheConfig = $container['cache/config'];

            $types = $cacheConfig->get('types');
            $drivers = new Container();

            $parentContainer = $container;

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\Apc
            */
            $drivers['apc'] = function (Container $container) {
                return new $container['cache/available-drivers']['Apc']();
            };

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\Sqlite
            */
            $drivers['db'] = function (Container $container) {
                return new $container['cache/available-drivers']['SQLite']();
            };

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\FileSystem
            */
            $drivers['file'] = function (Container $container) {
                return new $container['cache/available-drivers']['FileSystem']();
            };

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\Memcache
            */
            $drivers['memcache'] = function (Container $container) use ($parentContainer) {

                $cacheConfig = $parentContainer['cache/config'];
                $driver = new $parentContainer['cache/available-drivers']['Memcache']();

                if (isset($cacheConfig['servers'])) {
                    $servers = [];
                    foreach ($cacheConfig['servers'] as $server) {
                        $servers[] = array_values($server);
                    }
                } else {
                    $servers = [['127.0.0.1', 11211]];
                }
                $driver->setOptions([
                    'servers'=>$servers
                ]);
            };
            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\Ephemeral
            */
            $drivers['memory'] = function (Container $container) {
                return new $container['cache/available-drivers']['Ephemeral']();
            };

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\BlackHole
            */
            $drivers['noop'] = function (Container $container) {
                return new $container['cache/available-drivers']['BlackHole']();
            };

            /**
            * @param Container $container A container instance.
            * @return \Stash\Driver\Redis
            */
            $drivers['redis'] = function (Container $container) {
                return new $container['cache/available-drivers']['Redis']();
            };

            return $drivers;

        };

        /**
        * @param Container $container A container instance.
        * @return Container The Collection of DatabaseSourceConfig, in a Container.
        */
        $container['cache/driver'] = function (Container $container) {

            $cacheConfig = $container['cache/config'];
            $types = $cacheConfig->get('types');

            foreach ($types as $type) {
                if (isset($container['cache/drivers'][$type])) {
                    return $container['cache/drivers'][$type];
                }
            }

            // If no working drivers were available, fallback to an Ephemeral (memory) driver.
            return $container['cache/drivers']['memory'];
        };

        /**
        * The cache pool, using Stash.
        *
        * @param Container $container A container instance.
        * @return \Stash\Pool
        */
        $container['cache'] = function (Container $container) {

            $cacheConfig = $container['cache/config'];
            $driver = $container['cache/driver'];

            $pool = new Pool($driver);
            $pool->setLogger($container['logger']);
            $pool->setNamespace($cacheConfig['prefix']);

            return $pool;
        };

    }
}