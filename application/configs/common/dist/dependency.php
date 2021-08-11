<?php

use Psr\Container\ContainerInterface as c;

return [
    \Dvelum\Externals\Manager::class => static function (c $c):\Dvelum\Externals\Manager{
      return  new \Dvelum\Externals\Manager($c->get('config.main'), $c);
    }
];