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

class Vc extends Model
{
    /**
     * Create new  version
     * @return int|false
     * @throws \Exception
     * @property Orm\Record $object
     */
    public function newVersion(Orm\RecordInterface $object)
    {
        $object->commitChanges();
        $newVersion = ($this->getLastVersion($object->getName(), $object->getId()) + 1);
        $newData = $object->getData();

        if ($object->getConfig()->hasEncrypted()) {
            $ivField = $object->getConfig()->getIvField();
            $ivKey = $object->get($ivField);

            if (empty($ivKey)) {
                $service = new \Dvelum\Security\CryptService(\Dvelum\Config::storage()->get('crypt.php'));
                $ivKey = $service->createVector();
                $newData[$ivField] = $ivKey;
            }

            $newData = $this->getStore()->encryptData($object, $newData);
        }

        $newData['id'] = $object->getId();
        try {
            $vObject = Orm\Record::factory('vc');
            $vObject->set('date', date('Y-m-d'));
            $vObject->set('data', base64_encode(serialize($newData)));
            $vObject->set('user_id', \Dvelum\App\Session\User::factory()->getId());
            $vObject->set('version', $newVersion);
            $vObject->set('record_id', $object->getId());
            $vObject->set('object_name', $object->getName());
            $vObject->set('date', date('Y-m-d H:i:s'));

            if ($vObject->save()) {
                return $newVersion;
            }

            return false;
        } catch (\Exception $e) {
            $this->logError(
                'Cannot create new version for ' . $object->getName() . '::' . $object->getId() . ' ' . $e->getMessage()
            );
            return false;
        }
    }

    /**
     * Get last version
     * @param string $objectName
     * @param mixed $record_id integer / array
     * @return mixed integer / array
     */
    public function getLastVersion($objectName, $record_id)
    {
        if (!is_array($record_id)) {
            $sql = $this->db->select()
                ->from(
                    $this->table(),
                    ['max_version' => 'MAX(version)']
                )
                ->where('record_id =?', $record_id)
                ->where('object_name =?', $objectName);
            return (int)$this->db->fetchOne($sql);
        }

        $sql = $this->db->select()
            ->from($this->table(), array('max_version' => 'MAX(version)', 'rec' => 'record_id'))
            ->where('`record_id` IN(?)', $record_id)
            ->where('`object_name` =?', $objectName)
            ->group('record_id');

        $revs = $this->db->fetchAll($sql);

        if (empty($revs)) {
            return [];
        }

        $data = [];
        foreach ($revs as $v) {
            $data[$v['rec']] = $v['max_version'];
        }

        return $data;
    }

    /**
     * (non-PHPdoc)
     * @see Model::_queryAddAuthor()
     */
    protected function _queryAddAuthor($sql, $fieldAlias): void
    {
        $sql->joinLeft(
            array('u1' => Model::factory('User')->table()),
            'user_id = u1.id',
            array($fieldAlias => 'u1.name')
        );
    }

    /**
     * Get version data
     * @param string $objectName
     * @param integer $recordId
     * @param integer $version
     * @return array
     */
    public function getData($objectName, $recordId, $version)
    {
        $sql = $this->db->select()
            ->from($this->table(), array('data'))
            ->where('object_name = ?', $objectName)
            ->where('record_id =?', $recordId)
            ->where('version = ?', $version);

        $data = $this->db->fetchOne($sql);

        if (!empty($data)) {
            return unserialize(base64_decode($data));
        } else {
            return [];
        }
    }

    /**
     * Remove item from version control
     * @param string $object
     * @param integer $recordId
     */
    public function removeItemVc($object, $recordId)
    {
        $select = $this->db->select()
            ->from($this->table(), 'id')
            ->where('`object_name` = ?', $this->db->quote($object))
            ->where('`record_id` = ?', $recordId);
        $vcIds = $this->db->fetchCol($select);
        $store = $this->getStore();
        $store->deleteObjects($this->name, $vcIds);
    }
}
