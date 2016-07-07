<?php
class Backend_Externals_Controller extends Backend_Controller
{
    /**
     * @var Externals_Manager
     */
    protected $externalsManager;

    public function __construct()
    {
        parent::__construct();

        $externalsCfg = $this->_configMain->get('externals');
        if(!$externalsCfg['enabled']){
            if(Request::isAjax()){
                Response::jsonError($this->_lang->get('MODULE_DISABLED'));
            }else{
                Response::put($this->_lang->get('MODULE_DISABLED'));
                exit();
            }
        }
        Lang::addDictionaryLoader('externals', $this->_configMain->get('language').'/externals.php' , Config::File_Array);
        $this->externalsManager =  Externals_Manager::factory();
    }

    /**
     * Get list of available external modules
     */
    public function listAction()
    {
        $result = [];

        $this->externalsManager->scan();

        if($this->externalsManager->hasModules()){
            $result = $this->externalsManager->getModules();
        }

        foreach($result as $k=>&$v) {
            unset($v['autoloader']);
        }unset($v);

        Response::jsonSuccess($result);
    }

    /**
     * Reinstall external module
     */
    public function reinstallAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->install($id , true)) {
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }

        Response::jsonSuccess();
    }

    /**
     * Launch module installer
     */
    public function postInstallAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $langManager = new Backend_Localization_Manager($this->_configMain);
        try{
            $langManager->compileLangFiles();
        }catch (Exception $e){
            Response::jsonError($e->getMessage());
        }

        if(!$this->externalsManager->postInstall($id , true)) {
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }

        Response::jsonSuccess();
    }

    /**
     * Enable external module
     */
    public function enableAction()
    {
        $this->_checkCanEdit();

        $this->externalsManager->scan();

        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->setEnabled($id , true)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }

        Response::jsonSuccess();
    }

    /**
     * Disable external module
     */
    public function disableAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        if(!$this->externalsManager->setEnabled($id , false)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }
        Response::jsonSuccess();
    }

    /**
     * Uninstall external module
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();

        $id = Request::post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->uninstall($id)){
            $errors = $this->externalsManager->getErrors();
            Response::jsonError($this->_lang->get('CANT_EXEC').' '.implode(', ', $errors));
        }
        Response::jsonSuccess();
    }

    /**
     * Rebuild class map
     */
    public function buildMapAction()
    {
        $this->_checkCanEdit();

        $mapBuilder = new Classmap($this->_configMain);
        $mapBuilder->update();

        if(!$mapBuilder->save()){
            Response::jsonError($this->_lang->get('CANT_EXEC').' Build Map');
        }

        Response::jsonSuccess();
    }

    /**
     * Get Repo list
     */
    public function repoListAction()
    {
        Response::jsonSuccess($this->externalsManager->getRepoList());
    }

    /**
     * Get list of repository items
     */
    public function repoItemsListAction()
    {
        $repo = Request::post('repo', Filter::FILTER_STRING, false);
        $params = Request::post('pager' , FILTER::FILTER_ARRAY, []);

        $externalsLang = Lang::lang('externals');

        if(!extension_loaded('curl')){
            Response::jsonError($externalsLang->get('error_need_curl'));
        }

        $client = $this->getClient($repo);
        if(!$client){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        try{
           $list = $client->getList($params);
        }catch (Exception $e){
            Response::jsonError($e->getMessage());
        }

        Response::jsonArray($list);
    }

    /**
     * Prepare client adapter
     * @param string $repo
     * @return Externals_Client | boolean
     */
    protected function getClient($repo)
    {
        $externalsLang = Lang::lang('externals');
        $repoList = $this->externalsManager->getRepoList();
        $repoList = Utils::rekey('id', $repoList);

        if(!isset($repoList[$repo])){
           return false;
        }

        $config = new Config_Simple('externals_client');
        $config->setData($repoList[$repo]);

        $client = new Externals_Client($config);
        $client->setLanguage($this->_configMain->get('language'));
        $client->setLocalization($externalsLang);

        return $client;
    }

    /**
     * Download add-on
     */
    public function downloadAction()
    {
        $repo = Request::post('repo', Filter::FILTER_STRING, false);
        $app = Request::post('app', Filter::FILTER_STRING, false);
        $version = Request::post('version', Filter::FILTER_STRING, false);

        $externalsLang = Lang::lang('externals');

        if(!extension_loaded('zip')){
            Response::jsonError($externalsLang->get('error_need_zip'));
        }

        $client = $this->getClient($repo);
        if(!$client){
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));
        }

        $tmpFile = $this->_configMain->get('tmp').uniqid().'.zip';

        try{
            if(!$client->download($app, $version, $tmpFile)){
                Response::jsonError($this->_lang->get('CANT_EXEC'));
            }
        }catch (Exception $e){
            Response::jsonError($e->getMessage());
        }

        $tmpDir = $this->_configMain->get('tmp').$app;

        if(!is_dir($tmpDir)){
            if(!mkdir($tmpDir,0775)){
                Response::jsonError($this->_lang->get('CANT_WRITE_FS').$tmpDir);
            }
        }

        if(!File::unzipFiles($tmpFile, $tmpDir)){
            Response::jsonError($externalsLang->get('error_cant_extract').' '.$tmpFile);
        }

        $externalsCfg = $this->_configMain->get('externals');

        if(!File::copyDir($tmpDir, $externalsCfg['path'])){
            Response::jsonError($this->_lang->get('CANT_WRITE_FS') . ' ' . $externalsCfg['path']);
        }

        @unlink($tmpFile);
        File::rmdirRecursive($tmpDir, true);

        Response::jsonSuccess();
    }
}