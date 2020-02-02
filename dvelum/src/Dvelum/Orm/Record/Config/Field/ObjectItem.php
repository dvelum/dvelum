<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\Orm\Record\Config\Field;

use Dvelum\Orm;

class ObjectItem extends \Dvelum\Orm\Record\Config\Field
{
    /**
     * Apply value filter
     * @param mixed $value
     * @throws \Exception
     * @return mixed
     */
    public function filter($value)
    {
        if(is_object($value))
        {
            if($value instanceof Orm\Record)
            {
                if(!$value->isInstanceOf((string)$this->getLinkedObject())){
                    throw new \Exception('Invalid value type for field '. $this->getName().' expects ' . $this->getLinkedObject() . ', ' . $value->getName() . ' passed');
                }
                $value = $value->getId();
            }else{
                if(method_exists($value,'__toString')){
                    $value = $value->__toString();
                }else{
                    $value = null;
                }
            }
        }

        if(empty($value)){
            return null;
        }

        return (integer)$value;
    }
    /**
     * Validate value
     * @param mixed $value
     * @return bool
     */
    public function validate($value) : bool
    {
        if(!parent::validate($value)){
            return false;
        }

        if(!empty($value)) {

            if(!is_int($value)){
                return false;
            }
            return Orm\Record::objectExists($this->config['link_config']['object'], $value);
        }

        return true;
    }
}
