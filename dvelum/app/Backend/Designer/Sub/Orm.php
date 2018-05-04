<?php

use Dvelum\Orm;

class Backend_Designer_Sub_Orm extends Backend_Designer_Sub
{
    /**
     * Get list of objects from ORM
     */
    public function listAction()
    {
        $manager = new Orm\Record\Manager();
        $objects = $manager->getRegisteredObjects();
        $data = array();

        if (!empty($objects)) {
            foreach ($objects as $name) {
                $data[] = array('name' => $name, 'title' => Orm\Record\Config::factory($name)->getTitle());
            }
        }

        Response::jsonSuccess($data);
    }

    /**
     * Get list of ORM object fields
     */
    public function fieldsAction()
    {
        $objectName = Request::post('object', 'string', false);
        if (!$objectName) {
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        try {
            $config = Orm\Record\Config::factory($objectName);
        } catch (Exception $e) {
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $fields = $config->getFieldsConfig();
        if (empty($fields)) {
            Response::jsonSuccess(array());
        }

        $data = array();

        foreach ($fields as $name => $cfg)
        {
            $type = $cfg['db_type'];
            $field = $config->getField($name);

            if ($field->isLink()){
                if ($field->isDictionaryLink()){
                    $type = $this->_lang->get('DICTIONARY_LINK') . '"' . $config->getField($name)->getLinkedDictionary() . '"';
                } else {
                    $obj = $field->getLinkedObject();
                    $oName = $obj . '';
                    try {
                        $oCfg = Orm\Record\Config::factory($obj);
                        $oName .= ' (' . $oCfg->get('title') . ')';
                    } catch (Exception $e) {
                        //empty on error
                    }
                    $type = $this->_lang->get('OBJECT_LINK') . ' - ' . $oName;
                }
            }

            $data[] = array(
                'name' => $name,
                'title' => $cfg['title'],
                'type' => $type
            );
        }

        Response::jsonSuccess($data);
    }
}