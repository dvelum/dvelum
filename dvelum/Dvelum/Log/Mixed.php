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
namespace Dvelum\Log;

class Mixed extends \Psr\Log\AbstractLogger implements \Log
{
    /**
     * @var File
     */
    protected $logFile;
    /**
     * @var Db
     */
    protected $logDb;

    public function __construct(File $logFile , Db $logDb)
    {
        $this->logFile = $logFile;
        $this->logDb = $logDb;
    }

    public function log($level, $message, array $context = array())
    {
        if(!$this->logDb->log($level, $message, $context)){
            $this->logFile->log($level, $message, $context);
            $this->logFile->log(\Psr\Log\LogLevel::ERROR, $this->logDb->getLastError());
        }
    }
}