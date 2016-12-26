<?php
use Dvelum\Config;

class Sysdocs_Controller
{
    /**
     * Documentation configuration object
     * @var Config_Abstract
     */
    protected $docConfig;
    /**
     * Documentation version
     * @var string
     */
    protected $version;
    /**
     * Documentation version index
     * @var integer
     */
    protected $versionIndex;
    /**
     * @var Model_Sysdocs_File
     */
    protected $fileModel;

    /**
     * System configuration object
     * @var Config_Abstract
     */
    protected $configMain;

    /**
     * Index of first url param
     * @var integer
     */
    protected $paramsIndex;
    /**
     * Documentation language
     * @var string
     */
    protected $language;
    /**
     * Documentation version
     * @var string
     */
    /**
     * @var Lang
     */
    protected $lang;
    /**
     * Edit permissions
     * @var boolean
     */
    protected $canEdit = false;
    /**
     * Cache adapter
     * @var Cache_Abstract
     */
    protected $cache = false;

    public function __construct($mainConfig , $paramsIndex = 0, $container = false)
    {

        $this->container = $container;
        $this->configMain = Config::storage()->get('main.php');
        $this->fileModel = Model::factory('Sysdocs_File');
        $this->lang = Lang::lang();
        $this->paramsIndex = $paramsIndex;
        $this->docConfig = Config::storage()->get('sysdocs.php');
        $langDictionary = Dictionary::factory('sysdocs_language');

        $request = Request::getInstance();

        $lang = $request->getPart($this->paramsIndex);
        $version = $request->getPart(($this->paramsIndex+1));

        if($lang && $langDictionary->isValidKey($lang)){
            $this->language = $lang;
        }else{
            $this->language = $this->docConfig->get('default_languge');
        }

        if($version!==false && array_key_exists($version, $this->docConfig->get('versions'))){
            $this->version = $version;
        }else{
            $this->version = $this->docConfig->get('default_version');
        }
        $vList = $this->docConfig->get('versions');

        $this->versionIndex = $vList[$this->version];

        // change theme
        $page = Page::getInstance();
        $page->setTemplatesPath('system/gray/');
    }
    /**
     * Set edit permissions
     * @param boolean $flag
     */
    public function setCanEdit($flag)
    {
        $this->canEdit = (boolean) $flag;
    }
    /**
     * Set Cache adapter
     * @param Cache_Abstract $cache
     */
    public function setCacheAdapter(Cache_Abstract $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Run controller
     */
    public function run()
    {
        $action = Request::getInstance()->getPart(($this->paramsIndex+2));

        if($action && method_exists($this, $action.'Action')){
            $this->{$action.'Action'}();
        }else{
            if(strlen($action) && Request::isAjax()){
                Response::jsonError(Lang::lang()->get('WRONG_REQUEST').' ' . Request::getInstance()->getUri());
            }else{
                $this->indexAction();
            }
        }
    }

    /**
     * Default action. Load UI
     */
    public function indexAction()
    {
        $this->includeScripts();
        \Dvelum\Resource::factory()->addInlineJs('
           app.docLang = "'.$this->language.'";
           app.docVersion = "'.$this->version.'";
           var canEdit = '.intval($this->canEdit).';    
      ');
        $this->_runDesignerProject('./application/configs/dist/layouts/system/documentation.designer.dat', $this->container);

    }

    public function setDefaultVersion($versNum)
    {
        $this->version = $versNum;
    }

    /**
     * Get API tree.Panel data
     */
    public function apitreeAction()
    {
        Response::jsonArray($this->fileModel->getTreeList($this->versionIndex));
    }
    /**
     * Get class info
     */
    public function infoAction()
    {
        $fileHid = Request::post('fileHid', Filter::FILTER_STRING, false);

        $info = new Sysdocs_Info();

        $classInfo = $info->getClassInfoByFileHid($fileHid, $this->language , $this->versionIndex);

        Response::jsonSuccess($classInfo);
    }
    /**
     * Set class desctiption
     */
    public function setdescriptionAction()
    {
        if(!$this->canEdit){
            Response::jsonError($this->lang->get('CANT_MODIFY'));
        }
        $fileHid = Request::post('hid', Filter::FILTER_STRING, false);
        $text = Request::post('text', 'raw', '');
        $objectId = Request::post('object_id', Filter::FILTER_INTEGER, false);
        $objectClass = Request::post('object_class', Filter::FILTER_STRING, false);

        if(!$objectId){
            Response::jsonError($this->lang->get('WRONG_REQUEST'));
        }

        $info = new Sysdocs_Info();
        if($info->setDescription($objectId , $fileHid, $this->versionIndex , $this->language , $text , $objectClass)){
            Response::jsonSuccess();
        }
        Response::jsonError($this->lang->get('CANT_EXEC'));
    }
    /**
     * Get interface config
     */
    public function configAction()
    {
        $versionsList = array_keys($this->docConfig->get('versions'));
        $preparedVersions = array();

        foreach ($versionsList as $k=>$v){
            $preparedVersions[] = array('id'=>$v,'title'=>$v);
        }

        $langs = Dictionary::factory('sysdocs_language')->getData();
        $langData = array();

        foreach ($langs as $k=>$v){
            $langData[] = array('id'=>$k,'title'=>$v);
        }

        $result = array(
            'version' => $this->version,
            'language' => $this->language,
            'languages' => $langData,
            'versions' => $preparedVersions,
        );

        Response::jsonSuccess($result);
    }

    /**
     * Include required JavaScript files defined in the configuration file
     */
    public function includeScripts()
    {
        $resource = Resource::getInstance();

        $media = Model::factory('Medialib');
        $media->includeScripts();
        $cfg = Config::storage()->get('js_inc_backend.php');

        $theme = 'gray';
        $lang = $this->configMain->get('language');

        $resource->addJs('/js/lib/jquery.js', 1 , true , 'head');
        $resource->addJs('/js/lang/'.$lang.'.js', 1 , true , 'head');
        $resource->addJs('/js/app/system/common.js', 3 , false ,  'head');

        if($this->configMain->get('development'))
            $resource->addJs('/js/lib/ext6/build/ext-all-debug.js', 2 , true , 'head');
        else
            $resource->addJs('/js/lib/ext6/build/ext-all.js', 2 , true , 'head');

        $resource->addJs('/js/lib/ext6/build/theme-'.$theme.'/theme-'.$theme.'.js', 3 , true , 'head');


        $resource->addJs('/js/lib/ext6/build/locale/locale-'.$lang.'.js', 4 , true , 'head');

        $resource->addInlineJs('var developmentMode = '.intval($this->configMain->get('development')).';');

        $resource->addCss('/js/lib/ext6/build/theme-'.$theme.'/resources/theme-'.$theme.'-all.css' , 1);
        $resource->addCss('/css/system/style.css' , 2);
        $resource->addCss('/css/system/'.$theme.'/style.css' , 3);

        if($cfg->getCount())
        {
            $js = $cfg->get('js');
            if(!empty($js))
                foreach($js as $file => $config)
                    $resource->addJs($file , $config['order'] , $config['minified']);

            $css = $cfg->get('css');
            if(!empty($css))
                foreach($css as $file => $config)
                    $resource->addCss($file , $config['order']);
        }

    }

    /**
     * Run Layout project
     *
     * @param string $project - path to project file
     */
    protected function _runDesignerProject($project, $renderTo = false)
    {
        $manager = new Designer_Manager($this->configMain);
        $manager->renderProject($project , $renderTo);
    }

    /**
     * Send JSON error message
     *
     * @return string
     */
    protected function _errorResponse($msg)
    {
        if(Request::isAjax())
            Response::jsonError($msg);
        else
            Response::redirect(Request::url(array('index')));
    }

    /**
     * Search request from UI
     */
    public function searchAction()
    {
        $query = Request::post('search' , Filter::FILTER_STRING , '');

        if(empty($query)){
            Response::jsonSuccess(array());
        }

        $search = new Sysdocs_Search();
        $result = $search->find($query , $this->versionIndex);

        Response::jsonSuccess($result);
    }
}