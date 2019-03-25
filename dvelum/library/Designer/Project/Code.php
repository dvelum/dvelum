<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2013  Kirill A Egorov
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
class Designer_Project_Code
{
    static public $NEW_INSTANCE_TOKEN = '[new:]';

    protected $storesApplied = false;
    /**
     * @var Designer_Project
     */
    protected $_project;

    public function __construct(Designer_Project $project)
    {
        $this->_project = $project;
    }

    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * Get project code
     */
    public function getCode()
    {
        $this->applyStoreInstances();
        $code = $this->_compileJs(0);
        return '
		Ext.ns("' . $this->_project->namespace . '","' . $this->_project->runnamespace . '");
		' . $code['defines'] . '
	    ' . $code['layout'].'
	    ' . $this->_project->getActionJs();
    }

    /**
     * Update store property for all objects. Find instance token, update namespace
     */
    protected function applyStoreInstances()
    {
        if($this->storesApplied)
            return;

        $items = $this->_project->getObjects();

        foreach ($items as $k=>$v)
        {
            if($v instanceof Designer_Project_Container)
                continue;

            /**
             * @var Ext_Object $v
             */
            // Object instances
            if(method_exists($v, 'getViewObject'))
            {
                $o = $v->getViewObject();
                if($o->getConfig()->isValidProperty('store')){
                    $this->appendStoreNs($o);
                }
            }

            // Grid editors
            if($v->getClass() == 'Grid')
            {
                $columns = $v->getColumns();
                foreach($columns as $col)
                {
                    $column = $col['data'];
                    if($column->getConfig()->isValidProperty('editor')){
                        $editor = $column->editor;
                        if($editor instanceof Ext_Object && $editor->getConfig()->isValidProperty('store')){
                            $this->appendStoreNs($editor);
                        }
                    }
                }
            }
            // Others
            if($v->getConfig()->isValidProperty('store')){
                $this->appendStoreNs($v);
            }
        }
        $this->storesApplied = true;
    }

    /**
     * Append Project Namespace to store property
     * @param Ext_Object $object
     */
    protected function appendStoreNs(Ext_Object $object)
    {
        $store = $object->store;

        if($store instanceof Ext_Helper_Store){

            switch ($store->getType()){
                case Ext_Helper_Store::TYPE_INSTANCE :
                    $object->store = 'Ext.create("'.Ext_Code::appendNamespace(trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $store->getValue()))).'",{})';
                    break;
                case Ext_Helper_Store::TYPE_STORE :
                    $object->store = Ext_Code::appendRunNamespace($store->getValue());
                    break;
                case Ext_Helper_Store::TYPE_JSCODE :
                    $object->store = $store->getValue();
                    break;
            }

        }else{
            //backward compatibility
            $store = trim($store);
            if(strpos($store , Designer_Project_Code::$NEW_INSTANCE_TOKEN) !==false){
                $object->store = 'Ext.create("'.Ext_Code::appendNamespace(trim(str_replace(Designer_Project_Code::$NEW_INSTANCE_TOKEN, '', $store))).'",{})';
            }elseif (strlen($store)){
                $object->store = Ext_Code::appendRunNamespace($store);
            }
        }
    }

    protected function _compileJs($parent)
    {
        $definesCode = '';
        $layoutCode = '';
        $items = array();
        $docked = array();
        $menu = array();

        /*
         * Compile Components
         */
        if($parent === 0)
        {
            if($this->_project->itemExist(Designer_Project::COMPONENT_ROOT) && $this->_project->hasChilds(Designer_Project::COMPONENT_ROOT))
            {
                foreach($this->_project->getChilds(Designer_Project::COMPONENT_ROOT) as $itemData)
                {
                    $item = $itemData['data'];
                    $item->extendedComponent(true);
                    $result = $this->_compileExtendedItem($itemData['id'], Designer_Project::COMPONENT_ROOT);
                    $definesCode.= $result['defines'];
                }
            }
            $parent = Designer_Project::LAYOUT_ROOT;
        }

        if($this->_project->hasChilds($parent))
        {
            $childs = $this->_project->getChilds($parent);

            if($parent == Designer_Project::LAYOUT_ROOT && !empty($childs)){
                $childs = $this->sortByRenderPriority($childs);
            }

            foreach($childs as $item)
            {
                if($item['data'] instanceof Designer_Project_Container)
                    continue;

                $itemObject = $item['data'];
                $oClass = $item['data']->getClass();

                switch($oClass)
                {
                    case 'Docked' :

                        if(!$this->_project->hasChilds($item['id']))
                            break;

                        $result = $this->_compileItem($item['id']);
                        $layoutCode .= $result['layout'];
                        /*
                         * Only last Ext_Docked object will be processed
                         */
                        $docked = $this->_project->runnamespace . '.' . $itemObject->getName();
                        break;

                    case 'Menu' :
                        if(!$this->_project->hasChilds($item['id']))
                            break;

                        $menu = $this->_compileConfig($item['id']);
                        break;

                    default:
                        $result = $this->_compileItem($item['id']);
                        $layoutCode .= $result['layout'];
                        $items[] = $this->_project->runnamespace . '.' . $itemObject->getName();


                        break;

                }

            }
        }

        if($parent !== 0)
        {
            $parentObject = $this->_project->getItemData($parent);

            if($parentObject instanceof Ext_Object)
            {
                if(!empty($items) && $parentObject->isValidProperty('items'))
                    $parentObject->items = Utils_String::addIndent("[\n" . Utils_String::addIndent(implode(",\n" , $items)) . "\n]\n");

                if(!empty($docked) && $parentObject->isValidProperty('dockedItems'))
                    $parentObject->dockedItems = $docked;

                if(!empty($menu) && $parentObject->isValidProperty('menu'))
                    $parentObject->menu = $menu;
            }
        }

        return array(
            'defines' => $definesCode ,
            'layout' => $layoutCode
        );
    }

    /**
     * Sort tree items list by renderer priority
     * @param array $items
     * @return array
     */
    protected function sortByRenderPriority(array $items)
    {
        $models = [];
        $stores = [];
        $other = [];

        foreach($items as $k=>$item)
        {
            $obj = $item['data'];

            if($obj instanceof Ext_Object) {

                if($obj instanceof Ext_Model){
                    $models[$k] = $item;
                }elseif($obj instanceof Ext_Store){
                    $stores[$k] = $item;
                }else{
                    $other[$k] = $item;
                }

            }else{
                $other[$k] = $item;
            }
        }
        return array_merge($models, $stores, $other);
    }

    /**
     * Get object define code
     * @param string $id - object id
     * @return string
     */
    public function getObjectDefineJs($id)
    {
        $eventManager = $this->_project->getEventManager();
        $objectEvents = $eventManager->getObjectEvents($id);
        $object = $this->_project->getObject($id);

        if(!empty($objectEvents))
        {
            $eventObject = $object;

            while (method_exists($eventObject, 'getObject')){
                $eventObject = $eventObject->getObject();
            }

            $eventsConfig = $eventObject->getConfig()->getEvents()->__toArray();

            foreach ($objectEvents as $event => $config)
            {
                if(empty($config['code']))
                    continue;

                $params = '';

                if(isset($eventsConfig[$event]))
                    $params = implode(',', array_keys($eventsConfig[$event]));
                elseif(is_array($config['params']) && !empty($config['params']))
                    $params = implode(',', array_keys($config['params']));

                $bufferString = '';
                if(!empty($config['buffer'])){
                    $bufferString.=",\n".'buffer:'.intval($config['buffer'])."\n";
                }

                $object->addListener(
                    $event,
                    Utils_String::addIndent("{\n".
                        Utils_String::addIndent("fn:function(".$params."){\n".
                            Utils_String::addIndent($config['code'])
                            ."\n},\nscope:this".$bufferString)
                        ."\n}",
                        2,
                        "\t",true
                    )
                );
            }
        }

        if($object->isExtendedComponent())
        {
            $manager = $this->_project->getMethodManager();
            $objectMethods = $manager->getObjectMethods($id);
            if(!empty($objectMethods)){
                foreach ($objectMethods as $name => $method){
                    $object->addMethod($name, $method->getParamsLine(), $method->getCode(), $method->getJsDoc());
                }
            }

            $manager = $this->_project->getEventManager();
            $localEvents = $manager->getLocalEvents($object->getName());

            if(!empty($localEvents))
            {
                foreach ($localEvents as $name=>$info)
                {
                    $params = '';
                    $doc = "/**\n * @event ".$name;
                    if(!empty($info['params']) && is_array($info['params']))
                    {
                        $params = implode(' , ' , array_keys($info['params']));
                        foreach ($info['params'] as $key=>$type)
                            $doc.= "\n * @param ".$type." ".$key;
                    }
                    $doc.="\n */";
                    $object->addLocalEvent($name , $params , $doc);
                }
            }
        }
        /**
         * Convert ActionColumn listeners
         */
        if($object->getClass() === 'Grid'){
            $this->_applycolumnEvents($object);
        }
        return $this->_project->getObject($id)->getDefineJs($this->_project->namespace);
    }

    /**
     * Get object js code for layout
     * @param string $id - object id
     * @return string
     */
    public function getObjectLayoutCode($id)
    {

        $object = $this->_project->getObject($id);
        $oClass = $object->getClass();

        $eventManager = $this->_project->getEventManager();
        $objectEvents = $eventManager->getObjectEvents($id);

        $eventObject = $object;

        while (method_exists($eventObject, 'getObject')){
            $eventObject = $eventObject->getObject();
        }

        $eventsConfig = $eventObject->getConfig()->getEvents()->__toArray();

        // set handlers
        if(isset($objectEvents['handler'])){
            $config = $objectEvents['handler'];
            $params = '';

            if(isset($eventsConfig['handler']))
                $params = implode(',', array_keys($eventsConfig['handler']));

            $object->addListener('handler' ,"function(".$params."){\n".Utils_String::addIndent($config['code'],2)."\n}");
        }

        if(!$object->isInstance() && !empty($objectEvents))
        {
            foreach ($objectEvents as $event => $config)
            {
                $params = '';

                if(isset($eventsConfig[$event])){
                    $params = implode(',', array_keys($eventsConfig[$event]));
                }

                if($event === 'handler'){
                    continue;
                } else{

                    $bufferString = '';
                    if(!empty($config['buffer'])){
                        $bufferString.=",\n".'buffer:'.intval($config['buffer'])."\n";
                    }

                    $object->addListener($event ,
                        "{\n\t\t\tfn:function(".$params."){\n".
                        Utils_String::addIndent($config['code'],2).
                        "\n},\n\t\t\tscope:this\n\t\t".$bufferString."}\n"
                    );
                }

                //$object->addListener($event ,"function(".$params."){\n".Utils_String::addIndent($config['code'],2)."\n}");
            }
        }

        /**
         * Convert ActionColumn listeners
         */
        if($oClass === 'Grid'){
            $this->_applycolumnEvents($object);
        }

        $result = '';

        $objectVar = Ext_Code::appendRunNamespace($object->getName());

        switch($oClass)
        {
            case 'Component_JSObject' :
            case 'Docked' :
                $result =  "\n". $objectVar . ' = ' . Utils_String::addIndent($object->__toString(),1,"\t",true) . ';' . "\n";
                break;

            case 'Component_Filter':
                $result =  "\n". $objectVar . ' = Ext.create("' .
                    $object->getViewObject()->getConfig()->getExtends() . '",' .
                    Utils_String::addIndent($object->__toString()) . "\n" .
                    ');' . "\n";
                break;

            case 'Menu':
                $result =  "\n". $objectVar . ' = ' . Utils_String::addIndent($object->__toString(),1,"\t",true) . ';' . "\n";

                break;


            default :
                if($object->isInstance())
                {

                    $result =  "\n". $objectVar . ' = Ext.create("' .
                        Ext_Code::appendNamespace($object->getObject()->getName()) . '",' .
                        Utils_String::addIndent($object->__toString())."\n".
                        ');' . "\n";

                }else{

                    if($object->isExtendedComponent()){
                        $result = "\n". $objectVar . ' = Ext.create("' .
                            Ext_Code::appendNamespace($object->getName()) . '",{});' . "\n";
                    }else{
                        $result =  "\n". $objectVar . ' = Ext.create("' .
                            $object->getConfig()->getExtends() . '",' .
                            Utils_String::addIndent($object->__toString())."\n".
                            ');' . "\n";
                    }
                }

                break;

        }
        if($object->isInstance() && !empty($objectEvents))
        {
            /**
             * @var Ext_Object_Instance $object
             */
            $eventsConfig = $object->getObject()->getConfig()->getEvents()->__toArray();

            foreach ($objectEvents as $event => $config)
            {
                $params = '';
                if(isset($eventsConfig[$event]))
                    $params = implode(',', array_keys($eventsConfig[$event]));

                if($event === 'handler')
                    continue;
                //$result.= "\n". $objectVar. '.on("click", function(){'."\n".Utils_String::addIndent($config['code'],2)."\n})";
                else
                    $result.= "\n". $objectVar. '.on("'.$event.'" , function('.$params.'){'."\n".Utils_String::addIndent($config['code'],2)."\n});";
            }
        }
        return $result;
    }

    /**
     * Add listeners for column's
     * @param Ext_Grid $grid
     */
    protected function _applycolumnEvents(Ext_Grid $grid)
    {
        $columns = $grid->getColumns();

        if(empty($columns))
            return;

        $eventManager = $this->_project->getEventManager();

        foreach ($columns as $k=>$v)
        {
            $this->_convertColumnEvents($grid , $v['data']);

            if(is_object($v['data']->editor))
                $this->_convertColumnEditorActions($v['data']);

            if($v['data']->filter instanceof Ext_Grid_Filter)
                $this->_convertFilterEvents($grid , $v['data']->filter);

            if($v['data']->getClass()==='Grid_Column_Action')
                $this->_convertColumnActions($v['data']);
        }
    }

    /**
     * Add column events from EventManager
     * @param Ext_Grid_Column $column
     */
    protected function _convertColumnEvents(Ext_Grid $grid, Ext_Grid_Column $column)
    {
        $eventManager = $this->_project->getEventManager();
        $eventsConfig = $column->getConfig()->getEvents()->__toArray();
        $columnEvents = $eventManager->getObjectEvents($grid->getName().'.column.'.$column->getName());

        if(empty($columnEvents))
            return;

        foreach ($columnEvents as $event =>$config)
        {
            if(!strlen($config['code']))
                continue;

            $params = '';

            if(isset($eventsConfig[$event]))
                $params = implode(',', array_keys($eventsConfig[$event]));

            $bufferString = '';
            if(!empty($config['buffer'])){
                $bufferString.=",\n\t".'buffer:'.intval($config['buffer'])."\n";
            }

            $column->addListener($event ,
                "{".
                Utils_String::addIndent("\n\tfn:function(".$params."){\n".
                    Utils_String::addIndent($config['code'],3)
                    ."\n\t},\n\tscope:this".$bufferString."\n",2)
                ."}")
            ;
        }
    }

    /**
     * Apply filter events
     * @param Ext_Grid_Filter $filter
     */
    protected function _convertFilterEvents(Ext_Grid $grid, Ext_Grid_Filter $filter)
    {
        $eventManager = $this->_project->getEventManager();
        $eventsConfig = $filter->getConfig()->getEvents()->__toArray();
        $filterEvents = $eventManager->getObjectEvents($grid->getName().'.filter.'.$filter->getName());

        if(empty($filterEvents))
            return;

        foreach ($filterEvents as $event =>$config)
        {
            if(!strlen($config['code']))
                continue;

            $params = '';

            if(isset($eventsConfig[$event]))
                $params = implode(',', array_keys($eventsConfig[$event]));

            $bufferString = '';
            if(!empty($config['buffer'])){
                $bufferString.=",\n\t".'buffer:'.intval($config['buffer'])."\n";
            }

            $filter->addListener($event ,
                "{".
                Utils_String::addIndent("\n\tfn:function(".$params."){\n".
                    Utils_String::addIndent($config['code'],3)
                    ."\n\t},\n\tscope:this".$bufferString."\n",2)
                ."}"
            );
        }
    }

    /**
     * Convert listeners for actioncolumn
     * @param Ext_Grid_Column_Action $column
     */
    protected function _convertColumnActions(Ext_Grid_Column_Action $column)
    {
        $actions = $column->getActions();

        if(empty($actions))
            return;

        $eventManager = $this->_project->getEventManager();

        foreach($actions as $object)
        {
            $eventsConfig = $object->getConfig()->getEvents()->__toArray();
            $colEvents = $eventManager->getObjectEvents($object->getName());

            if(empty($colEvents))
                continue;

            foreach ($colEvents as $event =>$config)
            {
                if(!strlen($config['code']))
                    continue;

                $params = '';
                if(isset($eventsConfig[$event]))
                    $params = implode(',', array_keys($eventsConfig[$event]));

                $object->addListener($event ,"function(".$params."){\n".Utils_String::addIndent($config['code'],2)."\n}");
                $object->scope = 'this';
            }
        }
    }

    /**
     * Convert listeners for column editor
     * @param Ext_Grid_Column $column
     */
    protected function _convertColumnEditorActions(Ext_Grid_Column $column)
    {
        $object = $column->editor;

        $eventManager = $this->_project->getEventManager();

        $eventsConfig = $object->getConfig()->getEvents()->__toArray();
        $editorEvents = $eventManager->getObjectEvents($object->getName());

        if(empty($editorEvents))
            return;

        foreach ($editorEvents as $event =>$config)
        {
            if(!strlen($config['code']))
                continue;

            $params = '';
            if(isset($eventsConfig[$event]))
                $params = implode(',', array_keys($eventsConfig[$event]));

            $bufferString = '';
            if(!empty($config['buffer'])){
                $bufferString.=",\n".'buffer:'.intval($config['buffer'])."\n";
            }

            $object->addListener($event ,
                "{\n".
                Utils_String::addIndent("fn:function(".$params."){\n".
                    Utils_String::addIndent($config['code'],2)."\n},\n".
                    Utils_String::addIndent("scope:this".$bufferString."\n"),2).
                "}"
            );
        }
    }

    /**
     * Conpile object
     * @param string $id
     * @return array()
     */
    protected function _compileItem($id)
    {
        $object = $this->_project->getObject($id);

        if($object->getClass() === 'Component_Field_System_Medialibhtml')
        {
             Model::factory('Medialib')->includeScripts();
        }

        $code = array('defines'=>'','layout'=>'');
        $code = $this->_compileJs($id);

        $definesCode = $code['defines'];
        $layoutCode = $code['layout'];

        $layoutCode.= $this->getObjectLayoutCode($id);

        return array(
            'layout' => $layoutCode ,
            'defines' => $definesCode
        );
    }

    /**
     * Compile object as Config
     * @param string $name
     */
    protected function _compileConfig($name)
    {
        $o = $this->_project->getObject($name);
        $menu = '';
        $docked = '';
        $items = array();

        $eventManager = $this->_project->getEventManager();
        $objectEvents = $eventManager->getObjectEvents($name);

        if(!empty($objectEvents))
        {
            $eventsConfig = $o->getConfig()->getEvents()->__toArray();

            foreach ($objectEvents as $event => $config)
            {
                $params = '';
                if(isset($eventsConfig[$event]))
                    $params = implode(',', array_keys($eventsConfig[$event]));

                if($event === 'handler'){
                    $o->addListener($event ,"function(".$params."){\n".Utils_String::addIndent($config['code'],2)."\n}");
                } else{

                    $bufferString = '';
                    if(!empty($config['buffer'])){
                        $bufferString.=",\n".'buffer:'.intval($config['buffer'])."\n";
                    }

                    $o->addListener($event ,
                        "{\n\t\t\tfn:function(".$params."){\n".
                        Utils_String::addIndent($config['code'],4)
                        ."\n\t\t\t},\n\t\t\tscope:this".$bufferString."\n\t\t}\n"
                    );
                }
           }
        }


        if($this->_project->hasChilds($name))
        {
            $children = $this->_project->getChilds($name);

            foreach($children as $k => $item)
            {
                $oClass = $item['data']->getClass();

                switch($oClass)
                {
                    case 'Docked' :

                        if(!$this->_project->hasChilds($item['id']))
                            break;

                        $docked = $this->_compileConfig($item['id']);
                        break;

                    case 'Menu' :
                        if(!$this->_project->hasChilds($item['id']))
                            break;

                        $menu = $this->_compileConfig($item['id']);
                        break;

                    default:
                        $items[] =  $this->_compileConfig($item['id']);

                        break;
                }

            }
        }

        if(!empty($items) && $o->isValidProperty('items'))
            $o->items = Utils_String::addIndent("[\n" . Utils_String::addIndent(implode(",\n" , $items)) . "\n]\n");

        if(!empty($docked) && $o->isValidProperty('dockedItems'))
            $o->dockedItems = $docked;

        if(!empty($menu) && $o->isValidProperty('menu'))
            $o->menu = $menu;

        return $o;
    }

    protected function _compileExtendedItem($id , $parent)
    {
        $object = $this->_project->getItemData($id);
        $hasChilds = $this->_project->hasChilds($parent);

        if($object->isValidProperty('items') && $hasChilds)
            $this->_compileExtendedSubItems($id , $id);

        $definesCode = $this->getObjectDefineJs($id);

        return array(
            'layout' => '' ,
            'defines' => $definesCode
        );
    }

    protected function _compileExtendedSubItems($parent , $mainContainer)
    {
        if(!$this->_project->hasChilds($parent))
            return array();

        $mainContainerObject = $this->_project->getItemData($mainContainer);

        $eventManager = $this->_project->getEventManager();

        $childs = $this->_project->getChilds($parent);

        $items = array();
        $docked = array();
        $menu = array();

        foreach($childs as $k => $item)
        {
            if($this->_project->hasChilds($item['id']))
                $this->_compileExtendedSubItems($item['id'] , $mainContainer);

            $itemName = 'me.childObjects.' . $item['id'];

            switch ($item['data']->getClass()){

                case 'Docked' :
                    if(!$this->_project->hasChilds($item['id']))
                        break;

                    $docked[] = $item['data'];
                    break;

                case 'Menu' :

                    if(!$this->_project->hasChilds($item['id']))
                        break;

                    $menu[] = $item['data'];
                    break;

                default: $items[] = $itemName;
                    break;
            }


            $objectEvents = $eventManager->getObjectEvents($item['id']);

            if(!empty($objectEvents))
            {
                $eventObject = $item['data'];

                while (method_exists($eventObject, 'getObject')){
                    $eventObject = $eventObject->getObject();
                }
                $eventsConfig = $eventObject->getConfig()->getEvents()->__toArray();

                foreach ($objectEvents as $event => $config)
                {
                    if(empty($config['code']))
                        continue;

                    $params = '';
                    if(isset($eventsConfig[$event]))
                        $params = implode(',', array_keys($eventsConfig[$event]));

                    if($event === 'handler')
                    {
                        $item['data']->addListener($event ,"function(".$params."){\n".Utils_String::addIndent($config['code'],2)."\n}");
                        $item['data']->scope = 'this';
                    }
                    else{

                        $bufferString = '';
                        if(!empty($config['buffer'])){
                            $bufferString.=",\n".'buffer:'.intval($config['buffer'])."\n";
                        }

                        $item['data']->addListener($event ,
                            "{\n".
                            Utils_String::addIndent("fn:function(".$params."){\n".
                                Utils_String::addIndent($config['code'],2)."\n},\n" .
                                Utils_String::addIndent("scope:this".$bufferString)."\n}" , 2)
                            ."\n"
                        );

                    }
               }
            }

            $mainContainerObject->addElement($itemName , $item['data']);

            /**
             * Convert ActionColumn listeners
             */
            if($item['data']->getClass() === 'Grid'){
                $this->_applycolumnEvents($item['data']);
            }
        }

        if($parent!=='0')
        {
            $container = $this->_project->getItemData($parent);

            if(!empty($items))
                $container->items = "[\n" . Utils_String::addIndent(implode(",\n" , $items),1) . "\n]\n";

            if(!empty($docked))
                $container->dockedItems = implode(',' , $docked);

            if(!empty($menu))
                $container->menu = implode(',' , $menu);
        }

    }

    /**
     * Get object javascript source code
     * @param string $name
     * @return string
     */
    public function getObjectCode($name)
    {
        if(!$this->_project->objectExists($name))
            return '';

        $this->applyStoreInstances();

        $object = $this->_project->getObject($name);
        $oClass = $object->getClass();

        if(in_array($oClass , Designer_Project::$defines , true) || $object->isExtendedComponent())
            $code = $this->_compileExtendedItem($name , 0);
        else
            $code = $this->_compileItem($name);

        return $code['defines'] . "\n" . $code['layout'];
    }
}