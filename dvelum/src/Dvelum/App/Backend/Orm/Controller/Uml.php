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
use Dvelum\Config;
use Dvelum\Orm;

class Uml extends Controller
{
    protected $mapConfig = 'umlMap.php';

    public function getModule(): string
    {
        return 'Orm';
    }

    /**
     * Get data for UML diagram
     */
    public function loadMapAction()
    {
        $ormConfig = Config::storage()->get('orm.php');
        $config = Config::storage()->get($ormConfig->get('uml_map_data'),true,false);

        $items = $config->get('items');

        $data = [];

        $manager = new Orm\Record\Manager();

        $names = $manager->getRegisteredObjects();

        if(empty($names)){
            $names = [];
        }

        $showObj = $this->request->post('objects','array',[]);

        if(empty($showObj)){
            foreach($names as $name)
                if(!isset($items[$name]['show']) || $items[$name]['show'])
                    $showObj[] = $name;
        }else{
            foreach ($showObj as $k => $name)
                if(!in_array($name, $names, true))
                    unset($showObj[$k]);
        }

        $defaultX = 10;
        $defaultY = 10;

        foreach($names as $index=>$objectName){
            $objectConfig = Orm\Record\Config::factory($objectName);
            if(!empty($objectConfig->isRelationsObject()) || !in_array($objectName,$showObj)){
                unset($names[$index]);
                continue;
            }

            $data[$objectName]['links'] = $objectConfig->getLinks();
            $data[$objectName]['fields'] = [];

            $objectConfig = Orm\Record\Config::factory($objectName);
            $fields = $objectConfig->getFieldsConfig();

            foreach($fields as $fieldName => $fieldData){
                $data[$objectName]['fields'][] = $fieldName;
                $data[$objectName]['savedlinks'] = [];
                if(isset($items[$objectName])){
                    $data[$objectName]['position'] = array('x'=>$items[$objectName]['x'],'y'=>$items[$objectName]['y']);
                    $data[$objectName]['savedlinks'] = [];
                    if(!empty(isset($items[$objectName]['links'])))
                        $data[$objectName]['savedlinks'] = $items[$objectName]['links'];
                }else{
                    $data[$objectName]['position'] = array('x'=>$defaultX , 'y'=>$defaultY);
                    $defaultX+=10;
                    $defaultY+=10;
                }
            }
            sort($data[$objectName]['fields']);
        }

        foreach($names as $objectName){
            foreach($data[$objectName]['links'] as $link => $link_value){
                if(!isset($data[$link]))
                    continue;
                $data[$link]['weight'] = ( !isset($data[$link]['weight']) ? 1 : $data[$link]['weight'] + 1 );
            }
            if(!isset($data[$objectName]['weight']))
                $data[$objectName]['weight'] = 0;
        }

        $fieldName = "weight";

        uasort($data, function($a, $b) use($fieldName) {
            if($a[$fieldName] > $b[$fieldName]){
                return 1;
            }elseif ($a[$fieldName] < $b[$fieldName]){
                return -1;
            }else{
                return 0;
            }
            //return strnatcmp((string)$b[$fieldName], (string) $a[$fieldName]);
        });

        $result = [
            'mapWidth'=>$config->get('mapWidth'),
            'mapHeight'=>$config->get('mapHeight'),
            'items'=>$data
        ];
        $this->response->success($result);
    }

    /**
     * Save object coordinates
     */
    public function saveMapAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $map = $this->request->post('map', 'raw', '');

        if(!strlen($map)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $data = json_decode($map , true);

        $ormConfig = Config::storage()->get('orm.php');

        $config = Config::storage()->get($ormConfig->get('uml_map_data'),true,false);

        $saved = $config->get('items');

        $manager = new Orm\Record\Manager();
        $registered = $manager->getRegisteredObjects();
        if(empty($registered)){
            $registered = [];
        }

        /**
         * Check objects map from request and set show property
         */
        foreach($data as $k => $item){
            if(!in_array($k, $registered,true)){
                unset($data[$k]);
                continue;
            }
            $data[$k]['show'] = true;
        }

        /**
         * Add saved map objects with checking that object is registered
         */
        foreach($saved as $k => $item){
            $item['show'] = false;
            if(!array_key_exists($k,$data) && in_array($k,$registered,true))
                $data[$k] = $item;
        }

        $config->set('items' , $data);

        if(Config::storage()->save($config))
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
    }

}