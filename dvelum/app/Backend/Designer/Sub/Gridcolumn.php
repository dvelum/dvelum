<?php

class Backend_Designer_Sub_Gridcolumn extends Backend_Designer_Sub
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
        if (!strlen($name) || !$project->objectExists($name) || $project->getObject($name)->getClass() !== 'Grid')
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $this->_project = $project;
        $this->_object = $project->getObject($name);
    }

    /**
     * Get columns list as tree structure
     */
    public function columnlistAction()
    {
        Response::jsonArray($this->_object->getColumnsList());
    }

    /**
     * Get object properties
     */
    public function listAction()
    {
        $id = Request::post('id', 'string', false);

        if (!$id || !$this->_object->columnExists($id))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $column = $this->_object->getColumn($id);
        $config = $column->getConfig();
        $properties = $config->__toArray();

        $properties['renderer'] = '';

        if ($config->xtype !== 'actioncolumn') {
            unset($properties['items']);

        } else {
            unset($properties['summaryRenderer']);
            unset($properties['summaryType']);
        }
        Response::jsonSuccess($properties);
    }

    /**
     * Set object property
     */
    public function setpropertyAction()
    {
        $id = Request::post('id', 'string', false);
        $property = Request::post('name', 'string', false);
        $value = Request::post('value', 'string', false);

        if (!$id || !$this->_object->columnExists($id))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $object = $this->_object->getColumn($id);
        if (!$object->isValidProperty($property))
            Response::jsonError();

        if ($property === 'text') {
            $value = Request::post('value', 'raw', false);
        }

        $object->$property = $value;
        $this->_storeProject();
        Response::jsonSuccess();
    }

    /**
     * Get list of available renderers
     */
    public function renderersAction()
    {
        $data = array();
        $autoloaderCfg = Config::storage()->get('autoloader.php')->__toArray();
        $autoloaderPaths = $autoloaderCfg['paths'];
        $files = array();
        $classes = array();

        $data[] = array('id' => '', 'title' => $this->_lang->NO);

        foreach ($autoloaderPaths as $path) {
            $scanPath = $path . '/' . $this->_config->get('components') . '/Renderer';
            if (is_dir($scanPath)) {
                $files = array_merge($files, File::scanFiles($scanPath, array('.php'), true, File::Files_Only));
                if (!empty($files)) {
                    foreach ($files as $item) {
                        $class = Utils::classFromPath(str_replace($autoloaderPaths, '', $item));
                        if (!in_array($class, $classes)) {
                            $data[] = array('id' => $class, 'title' => str_replace($scanPath . '/', '', substr($item, 0, -4)));
                            array_push($classes, $class);
                        }
                    }
                }
            }
        }

        Response::jsonArray($data);
    }

    /**
     * Get list of accepted dictionaries for cell renderer
     */
    public function dictionariesAction()
    {
        $manager = Dictionary_Manager::factory();
        $data = [];
        $list = $manager->getList();

        foreach ($list as $path) {
            $data[] = ['id' => $path, 'title' => $path];
        }
        Response::jsonSuccess($data);
    }

    /**
     * Change column width
     */
    public function changesizeAction()
    {
        $object = Request::post('object', 'string', false);
        $column = Request::post('column', 'string', false);
        $width = Request::post('width', 'integer', false);

        $project = $this->_getProject();

        if ($object === false || !$project->objectExists($object) || $column === false || $width === false)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $object = $project->getObject($object);

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $column = $object->getColumn($column);
        $column->width = $width;

        $this->_storeProject();

        Response::jsonSuccess();
    }

    /**
     * Move column
     */
    public function moveAction()
    {
        $object = Request::post('object', 'string', false);
        $order = Request::post('order', 'raw', '');

        $project = $this->_getProject();

        if ($object === false || !$project->objectExists($object) || empty($order))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $order = json_decode($order);

        if (!is_array($order))
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $object = $project->getObject($object);

        if ($object->getClass() !== 'Grid')
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $object->updateColumnsSortingOrder($order);

        $this->_storeProject();

        Response::jsonSuccess();
    }

    /**
     * Get list of items for actioncolumn
     */
    public function itemslistAction()
    {
        $designerManager = new Designer_Manager($this->_configMain);

        $object = $this->_object;
        $column = Request::post('column', 'string', false);

        if ($column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $columnObject = $object->getColumn($column);

        if ($columnObject->getClass() !== 'Grid_Column_Action')
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 3');

        $result = array();
        $actions = $columnObject->getActions();
        if (!empty($actions)) {
            foreach ($actions as $name => $object) {
                $result[] = array(
                    'id' => $name,
                    'icon' => Designer_Factory::replaceCodeTemplates($designerManager->getReplaceConfig(), $object->icon),
                    'tooltip' => $object->tooltip
                );
            }
        }
        Response::jsonSuccess($result);
    }

    public function addactionAction()
    {
        $object = $this->_object;
        $actionName = Request::post('name', 'alphanum', false);
        $column = Request::post('column', 'string', false);

        if ($actionName === false || $column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $columnObject = $object->getColumn($column);

        if ($columnObject->getClass() !== 'Grid_Column_Action')
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 3');

        $actionName = $this->_object->getName() . '_action_' . $actionName;

        if ($columnObject->actionExists($actionName))
            Response::jsonError($this->_lang->SB_UNIQUE);

        $newButton = Ext_Factory::object('Grid_Column_Action_Button', array('text' => $actionName));
        $newButton->setName($actionName);

        $columnObject->addAction($actionName, $newButton);
        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function removeactionAction()
    {
        $object = $this->_object;
        $actionName = Request::post('name', 'alphanum', false);
        $column = Request::post('column', 'string', false);

        if ($actionName === false || $column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $columnObject = $object->getColumn($column);

        if ($columnObject->getClass() !== 'Grid_Column_Action')
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 3');


        $columnObject->removeAction($actionName);

        $this->_project->getEventManager()->removeObjectEvents($actionName);

        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function sortactionsAction()
    {
        $object = $this->_object;
        $order = Request::post('order', 'array', array());
        $column = Request::post('column', 'string', false);

        if ($column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $columnObject = $object->getColumn($column);

        if ($columnObject->getClass() !== 'Grid_Column_Action')
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 3');

        if (!empty($order)) {
            $index = 0;
            foreach ($order as $name) {
                if ($columnObject->actionExists($name)) {
                    $columnObject->setActionOrder($name, $index);
                    $index++;
                }
            }
            if ($index > 0)
                $columnObject->sortActions();
        }

        $this->_storeProject();
        Response::jsonSuccess();
    }

    public function rendererLoadAction()
    {
        $object = $this->_object;
        $column = Request::post('column', 'string', false);
        $data = [];

        if ($column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $columnObject = $object->getColumn($column);
        $renderer = $columnObject->renderer;

        if (empty($renderer) || is_string($renderer)) {
            $data = [
                'type' => 'adapter',
                'adapter' => $renderer
            ];
        } elseif ($renderer instanceof Ext_Helper_Grid_Column_Renderer) {
            $data = [
                'type' => $renderer->getType(),
            ];
            switch ($renderer->getType()) {
                case Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY:
                    $data['dictionary'] = $renderer->getValue();
                    break;
                case Ext_Helper_Grid_Column_Renderer::TYPE_ADAPTER:
                    $data['adapter'] = $renderer->getValue();
                    break;
                case Ext_Helper_Grid_Column_Renderer::TYPE_JSCALL:
                    $data['call'] = $renderer->getValue();
                    break;
                case Ext_Helper_Grid_Column_Renderer::TYPE_JSCODE:
                    $data['code'] = $renderer->getValue();
                    break;
            }
        }

        Response::jsonSuccess($data);
    }

    public function rendererSaveAction()
    {
        $object = $this->_object;
        $column = Request::post('column', 'string', false);

        if ($column === false)
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 1');

        if ($object->getClass() !== 'Grid' || !$object->columnExists($column))
            Response::jsonError($this->_lang->WRONG_REQUEST . ' code 2');

        $rendererHelper = new Ext_Helper_Grid_Column_Renderer();

        $type = Request::post('type', 'string', false);

        if (!in_array($type, $rendererHelper->getTypes(), true)) {
            Response::jsonError($this->_lang->get('FILL_FORM'), array('type' => $this->_lang->get('INVALID_VALUE')));
        }

        $rendererHelper->setType($type);

        switch ($type) {
            case Ext_Helper_Grid_Column_Renderer::TYPE_DICTIONARY:
                $rendererHelper->setValue(Request::post('dictionary', Filter::FILTER_RAW, ''));
                break;
            case Ext_Helper_Grid_Column_Renderer::TYPE_ADAPTER:
                $rendererHelper->setValue(Request::post('adapter', Filter::FILTER_RAW, ''));
                break;
            case Ext_Helper_Grid_Column_Renderer::TYPE_JSCALL:
                $rendererHelper->setValue(Request::post('call', Filter::FILTER_RAW, ''));
                break;
            case Ext_Helper_Grid_Column_Renderer::TYPE_JSCODE:
                $rendererHelper->setValue(Request::post('code', Filter::FILTER_RAW, ''));
                break;

        }

        $columnObject = $object->getColumn($column);
        $columnObject->renderer = $rendererHelper;

        $this->_storeProject();
        Response::jsonSuccess();
    }
}