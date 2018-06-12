<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Clear;

use Dvelum\App\Console;
use Dvelum\File;

class StaticCache extends Console\Action
{
    public function action(): bool
    {
       $cssDir = $this->appConfig->get('cssCachePath');
       $jsDir = $this->appConfig->get('jsCachePath');
       File::rmdirRecursive($cssDir);
       File::rmdirRecursive($jsDir);
       return true;
    }
}