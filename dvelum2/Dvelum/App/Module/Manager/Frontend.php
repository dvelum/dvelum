<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\App\Module\Manager;

use Dvelum\App\Module\Manager;
use Dvelum\File;
use Dvelum\Utils;

class Frontend extends Manager
{
    protected $mainConfigKey = 'frontend_modules';

    /**
     * Get list of Controllers
     * @return array
     */
    public function getControllers() : array
    {
        $autoloadCfg = $this->appConfig->get('autoloader');
        $paths = $autoloadCfg['paths'];
        $dir = $this->appConfig->get('frontend_controllers_dir');

        $data = array();

        foreach($paths as $path){
            if(!is_dir($path.'/'.$dir)){
                continue;
            }
            $folders = File::scanFiles($path . '/' . $dir, false, true, File::Dirs_Only);

            if(empty($folders))
                continue;

            foreach ($folders as $item)
            {
                $name = basename($item);

                if(file_exists($item.'/Controller.php'))
                {
                    $name = str_replace($path.'/', '', $item.'/Controller.php');
                    $name = Utils::classFromPath($name);
                    $data[$name] = array('id'=>$name,'title'=>$name);
                }
            }
        }
        return array_values($data);
    }
    /**
     * Update module data
     * @param string $name
     * @param array $data
     * @return bool
     */
    public function updateModule(string $name , array $data) : bool
    {
        if($name !== $data['code']){
            $this->modulesLocale->remove($name);
            $this->config->remove($name);
        }

        if(isset($data['title'])){
            $this->modulesLocale->set($data['code'] , $data['title']);
            if(!$this->modulesLocale->save()){
                return false;
            }
            unset($data['title']);
        }
        $this->config->set($data['code'] , $data);
        return $this->save();
    }

    /**
     * Get modules list
     * @return array
     */
    public function getList() : array
    {
        $list = parent::getList();
        if(!empty($list))
        {
            foreach($list as $k=>&$v)
            {
                if($this->curConfig && $this->curConfig->offsetExists($k)){
                    $cfg['dist'] = false;
                }else{
                    $cfg['dist'] = true;
                }
                $v['id'] = $v['code'];
            }
        }
        return $list;
    }
}