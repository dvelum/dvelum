<?php

/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
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

namespace Dvelum\App\Model;

use Dvelum\Orm;
use Dvelum\Orm\Model;

class Links extends Model
{
    /**
     * Clear object links
     * @param Orm\Record $object
     */
    public function clearObjectLinks(Orm\Record $object)
    {
        $this->db->delete(
            $this->table(),
            'src = ' . $this->db->quote($object->getName()) . ' AND src_id = ' . intval($object->getId())
        );
    }

    /**
     * Clear links for object list
     * @param string $objectName
     * @param array $objectsIds
     */
    public function clearLinksFor($objectName, array $objectsIds)
    {
        $this->db->delete(
            $this->table(),
            '`src` = ' . $this->db->quote($objectName) . ' 
    			AND
    		 `src_id` IN(' . \Dvelum\Utils::listIntegers($objectsIds) . ')'
        );
    }
}
