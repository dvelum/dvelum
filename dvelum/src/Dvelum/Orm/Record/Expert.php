<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
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
 */
namespace Dvelum\Orm\Record;

use Dvelum\Orm\Model;
use Dvelum\Utils;
use Dvelum\Orm\Record;

/**
 * Orm Record information expert
 * Helps to find relations between objects
 */
class Expert
{
    static protected $objectAssociations = null;

    static protected function buildAssociations()
    {
        if (!is_null(self::$objectAssociations))
            return;

        $manager = new Manager();
        $objects = $manager->getRegisteredObjects();
        if(!empty($objects)){
            foreach ($objects as $name) {
                $config = Record\Config::factory($name);
                $links = $config->getLinks();
                self::$objectAssociations[$name] = $links;
            }
        }
    }

    /**
     * Get Associated objects
     * @param Record $object
     * @return array   like
     * array(
     *      'single' => array(
     *            'objectName'=>array(id1,id2,id3),
     *            ...
     *            'objectNameN'=>array(id1,id2,id3),
     *       ),
     *       'multi' =>array(
     *            'objectName'=>array(id1,id2,id3),
     *            ...
     *            'objectNameN'=>array(id1,id2,id3),
     *       )
     * )
     */
    static public function getAssociatedObjects(Record $object)
    {
        $linkedObjects = ['single' => [], 'multi' => []];

        self::buildAssociations();

        $objectName = $object->getName();
        $objectId = $object->getId();

        if (!isset(self::$objectAssociations[$objectName]))
            return array();

        foreach (self::$objectAssociations as $testObject => $links) {
            if (!isset($links[$objectName]))
                continue;

            $sLinks = self::getSingleLinks($objectId, $testObject, $links[$objectName]);

            if (!empty($sLinks))
                $linkedObjects['single'][$testObject] = $sLinks;
        }

        $linkedObjects['multi'] = self::getMultiLinks($objectName, $objectId);

        return $linkedObjects;
    }

    /**
     * Get "single link" associations
     * when object has link as own property
     * @param mixed $objectId
     * @param string $relatedObject - related object name
     * @param array $links - links config like
     *    array(
     *        'field1'=>'object',
     *        'field2'=>'multi'
     *        ...
     *        'fieldN'=>'object',
     *  )
     * @return array
     */
    static protected function getSingleLinks($objectId, $relatedObject, $links) : array
    {
        $relatedConfig = Config::factory($relatedObject);
        $relatedObjectModel = Model::factory($relatedObject);
        $fields = [];

        foreach ($links as $field => $type) {
            if ($type !== 'object')
                continue;

            $fields[] = $field;
        }

        if (empty($fields))
            return [];

        $db = $relatedObjectModel->getDbConnection();
        $sql = $db->select()->from($relatedObjectModel->table(), array($relatedConfig->getPrimaryKey()));
        /**
         * @var bool $first
         */
        $first = true;
        foreach ($fields as $field) {
            if ($first) {
                $sql->where($db->quoteIdentifier((string) $field) . ' =?', $objectId);
            } else {
                $sql->orWhere($db->quoteIdentifier((string) $field) . ' =?', $objectId);
                $first = false;
            }
        }
        $data = $db->fetchAll($sql);


        if (empty($data))
            return [];

        return Utils::fetchCol($relatedConfig->getPrimaryKey(), $data);
    }

    /**
     * Get multi-link associations
     * when links stored  in external objects
     * @param string $objectName
     * @param mixed $objectId
     * @return array
     */
    static protected function getMultiLinks($objectName, $objectId) : array
    {
        $ormConfig = \Dvelum\Config::storage()->get('orm.php');
        $linksModel = Model::factory($ormConfig->get('links_object'));
        $db = $linksModel->getDbConnection();
        $linkTable = $linksModel->table();

        $sql = $db->select()
            ->from($linkTable, array('id' => 'src_id', 'object' => 'src'))
            ->where('`target` =?', $objectName)
            ->where('`target_id` =?', $objectId);
        $links = $db->fetchAll($sql);

        $data = [];

        if (!empty($links))
            foreach ($links as $record)
                $data[$record['object']][] = $record['id'];

        return $data;
    }

    /**
     * Check if Object has associated objects
     * @param string $objectName
     * @return array - associations
     */
    static public function getAssociatedStructures($objectName)
    {
        $objectName = strtolower($objectName);

        self::buildAssociations();

        if (empty(self::$objectAssociations))
            return array();

        $associations = array();

        foreach (self::$objectAssociations as $object => $data) {
            if (empty($data))
                continue;

            foreach ($data as $oName => $fields) {
                if ($oName !== $objectName)
                    continue;

                $associations[] = array(
                    'object' => $object,
                    'fields' => $fields
                );
            }
        }
        return $associations;
    }
}