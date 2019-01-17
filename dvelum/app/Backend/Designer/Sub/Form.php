<?php
/**
 * Operations with forms
 */

use Dvelum\Orm;

class Backend_Designer_Sub_Form extends Backend_Designer_Sub
{
    /**
     * @var Designer_Project
     */
    protected $_project;
    /**
     * @var Ext_Grid
     */
    protected $_object;

    public function __construct()
    {
        parent::__construct();
        $this->_checkLoaded();
        $this->_checkObject();
    }

    protected function _checkObject()
    {
        $name = Request::post('object', 'string', '');
        $project = $this->_getProject();
        if (!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass() !== 'Form')
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_project = $project;
        $this->_object = $project->getObject($name);
    }

    /**
     * Import fields into the form object
     */
    public function importfieldsAction()
    {
        $importObject = Request::post('importobject', 'string', false);
        $importFields = Request::post('importfields', 'array', array());

        if (!$importObject || empty($importFields) || $this->_project->objectExists($importObject))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $importObjectConfig = Orm\Record\Config::factory($importObject);

        foreach ($importFields as $name)
            if ($importObjectConfig->fieldExists($name))
                $this->_importOrmField($name, $importObjectConfig);

        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Import DB fields into the form object
     */
    public function importdbfieldsAction()
    {
        $connection = Request::post('connection', 'string', false);
        $table = Request::post('table', 'string', false);
        $conType = Request::post('type', 'integer', false);

        $importFields = Request::post('importfields', 'array', array());

        if ($connection === false || !$table || empty($importFields) || $conType === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $conManager = new \Dvelum\Db\Manager($this->_configMain);

        try {
            $db = $conManager->getDbConnection($connection, $conType);
        } catch (Exception $e) {
            Response::jsonError($this->_lang->WRONG_REQUEST);
            return;
        }

        $columns = $db->getMeta()->getColumnsAsArray($table);

        if (empty($columns))
            Response::jsonError($this->_lang->CANT_CONNECT);

        foreach ($importFields as $name)
            if (isset($columns[$name]) && !empty($columns[$name]))
                $this->_importDbField($name, $columns[$name]);

        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Conver field from ORM format and add to the project
     * @param string $name
     * @param Db_Object_Config $importObject
     */
    protected function _importOrmField($name, $importObjectConfig)
    {
        $newField = Backend_Designer_Import::convertOrmFieldToExtField($name, $importObjectConfig->getFieldConfig($name));
        if ($newField !== false) {
            $newField->setName($this->_object->getName() . '_' . $name);
            $this->_project->addObject($this->_object->getName(), $newField);
        }

    }

    /**
     * Conver DB column into Ext field
     * @param string $name
     * @param array $config
     */
    protected function _importDbField($name, $config)
    {
        $newField = Backend_Designer_Import::convertDbFieldToExtField($config);
        if ($newField !== false) {
            $newField->setName($this->_object->getName() . '_' . $name);
            $this->_project->addObject($this->_object->getName(), $newField);
        }
    }
}