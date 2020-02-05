<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
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

namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Controller;

use Dvelum\Filter;
use Dvelum\Config;
use Dvelum\File;

class Log extends Controller
{
    public function getModule(): string
    {
        return 'Orm';
    }

    public function indexAction(){}

    /**
     * Get DB_Object_Builder log contents
     * for current development version
     */
    public function getlogAction()
    {
        $file = $this->request->post('file', Filter::FILTER_STRING, false);

        $ormConfig = Config::storage()->get('orm.php');
        $logPath = $ormConfig->get('log_path');
        $fileName = $logPath . $file . '.sql';

        if (file_exists($fileName)) {
            $data = nl2br((string)file_get_contents($fileName));
        } else {
            $data = '';
        }
        $this->response->json(['success'=>true, 'data'=>$data]);
    }

    public function getLogFilesAction()
    {
        $ormConfig = Config::storage()->get('orm.php');
        $logPath = $ormConfig->get('log_path');

        $files = File::scanFiles($logPath, ['.sql'], false);
        $data = [];

        foreach ($files as $file) {
            $file = basename($file, '.sql');
            $data[] = ['id' => $file];
        }

        $this->response->success($data);
    }
}