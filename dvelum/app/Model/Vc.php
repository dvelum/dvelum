<?php

use Dvelum\Orm;
use Dvelum\Orm\Model;

class Model_Vc extends Model
{
    /**
     * Create new  version
     * @property Orm\Record $object
     * @return boolean
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
                $ivKey = Utils_String::createEncryptIv();
                $newData[$ivField] = $ivKey;
            }

            $newData = $this->getStore()->encryptData($object, $newData);
        }

        $newData['id'] = $object->getId();
        try {
            $vObject = Orm\Record::factory('vc');
            $vObject->set('date', date('Y-m-d'));
            $vObject->set('data', base64_encode(serialize($newData)));
            $vObject->set('user_id', User::getInstance()->id);
            $vObject->set('version', $newVersion);
            $vObject->set('record_id', $object->getId());
            $vObject->set('object_name', $object->getName());
            $vObject->set('date', date('Y-m-d H:i:s'));

            if ($vObject->save())
                return $newVersion;

            return false;

        } catch (Exception $e) {
            $this->logError('Cannot create new version for ' . $object->getName() . '::' . $object->getId() . ' ' . $e->getMessage());
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

            $sql = $this->dbSlave->select()
                ->from(
                    $this->table(),
                    array('max_version' => 'MAX(version)')
                )
                ->where('record_id =?', $record_id)
                ->where('object_name =?', $objectName);
            return (integer)$this->dbSlave->fetchOne($sql);

        } else {
            $sql = $this->dbSlave->select()
                ->from($this->table(), array('max_version' => 'MAX(version)', 'rec' => 'record_id'))
                ->where('`record_id` IN(?)', $record_id)
                ->where('`object_name` =?', $objectName)
                ->group('record_id');

            $revs = $this->dbSlave->fetchAll($sql);

            if (empty($revs))
                return array();

            $data = array();
            foreach ($revs as $k => $v)
                $data[$v['rec']] = $v['max_version'];

            return $data;
        }
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
        $sql = $this->dbSlave->select()
            ->from($this->table(), array('data'))
            ->where('object_name = ?', $objectName)
            ->where('record_id =?', $recordId)
            ->where('version = ?', $version);

        $data = $this->dbSlave->fetchOne($sql);

        if (!empty($data))
            return unserialize(base64_decode($data));
        else
            return [];
    }

    /**
     * Remove item from version control
     * @param string $object
     * @param integer $recordId
     */
    public function removeItemVc($object, $recordId)
    {
        $select = $this->dbSlave->select()
            ->from($this->table(), 'id')
            ->where('`object_name` = ?', $this->dbSlave->quote($object))
            ->where('`record_id` = ?', $recordId);
        $vcIds = $this->dbSlave->fetchCol($select);
        $store = $this->getStore();
        $store->deleteObjects($this->name, $vcIds);
    }
}