<?php

use Psr\Container\ContainerInterface as c;

return [
    \Dvelum\Externals\Manager::class => static function (c $c): \Dvelum\Externals\Manager {
        return new \Dvelum\Externals\Manager($c->get('config.main'), $c);
    },
    \Dvelum\Orm\Orm::class => static function (c $c): \Dvelum\Orm\Orm {
        $cache = $c->has(\Dvelum\Cache\CacheInterface::class) ? $c->get(
            \Dvelum\Cache\CacheInterface::class
        ) : null;
        $orm = new \Dvelum\Orm\Orm(
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('orm.php'),
            $c->get(\Dvelum\Db\ManagerInterface::class),
            $c->get('config.main')->get('language'),
            $cache
        );
        return $orm;
    },
    \Dvelum\App\Module\Manager::class => static function (c $c): \Dvelum\App\Module\Manager {
        return new  \Dvelum\App\Module\Manager($c->get('config.main'), $c->get(\Dvelum\Lang::class));
    }
];