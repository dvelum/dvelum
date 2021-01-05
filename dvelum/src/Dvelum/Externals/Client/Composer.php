<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2021  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Externals\Client;

use Composer\Console\Application;
use Composer\Command\UpdateCommand;
use Symfony\Component\Console\Input\ArrayInput;
#use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\StreamOutput;
use Dvelum\File;

class Composer extends Packagist
{
    /**
     * Download add-on
     * @param string $app
     * @param string $version
     * @return bool
     * @throws \Exception
     */
    public function download(string $app, string $version): bool
    {
        set_time_limit(0);
        putenv('COMPOSER_HOME=./temp/.composer');

        $stream = fopen('php://temp', 'w+');
        $output = new StreamOutput($stream);
        $application = new Application();
        $application->setAutoExit(false);
        $code = $application->run(new ArrayInput(['command' => 'require', $app]), $output);
        $res =  stream_get_contents($stream);

        if($code !==0){
            throw new \Exception('Cant download package. '.$app.' '.$res);
        }

        return true;
    }
}