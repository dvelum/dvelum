<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
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
declare(strict_types=1);

namespace Dvelum\App\Backend\Externals;

use Dvelum\Config;
use Dvelum\Externals\ClientInterface;
use Dvelum\Externals\Manager;
use Dvelum\Filter;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Service;
use Dvelum\App;

class Controller extends App\Backend\Controller
{
    /**
     * @var Manager
     */
    protected $externalsManager;

    public function getModule(): string
    {
        return 'Externals';
    }

    public function getObjectName(): string
    {
        return '';
    }

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');

        $langService->addLoader('externals', $this->appConfig->get('language').'/externals.php' , Config\Factory::File_Array);
        $this->externalsManager = Manager::factory();

        $this->externalsManager->scan();
    }

    /**
     * Get list of available external modules
     */
    public function listAction()
    {
        $result = [];

        if($this->externalsManager->hasModules()){
            $result = $this->externalsManager->getModules();
        }

        foreach($result as $k=>&$v) {
            unset($v['autoloader']);
        }unset($v);

        $this->response->success($result);
    }

    /**
     * Reinstall external module
     */
    public function reinstallAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if(!$this->externalsManager->install($id)) {
            $errors = $this->externalsManager->getErrors();
            $this->response->error($this->lang->get('CANT_EXEC').' '.implode(', ', $errors));
            return;
        }

        $this->response->success();
    }

    /**
     * Launch module installer
     */
    public function postInstallAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $langManager = new App\Backend\Localization\Manager($this->appConfig);
        try{
            $langManager->compileLangFiles();
        }catch (\Exception $e){
            $this->response->error($e->getMessage());
            return;
        }

        if(!$this->externalsManager->postInstall($id)) {
            $errors = $this->externalsManager->getErrors();
            $this->response->error($this->lang->get('CANT_EXEC').' '.implode(', ', $errors));
            return;
        }

        $this->response->success();
    }

    /**
     * Enable external module
     */
    public function enableAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $this->externalsManager->scan();

        $id =  $this->request->post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if(!$this->externalsManager->setEnabled($id , true)){
            $errors = $this->externalsManager->getErrors();
            $this->response->error($this->lang->get('CANT_EXEC').' '.implode(', ', $errors));
            return;
        }

        $this->response->success();
    }

    /**
     * Disable external module
     */
    public function disableAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }
        $id =  $this->request->post('id', Filter::FILTER_STRING, false);

        if(!$this->externalsManager->moduleExists($id)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if(!$this->externalsManager->setEnabled($id , false)){
            $errors = $this->externalsManager->getErrors();
            $this->response->error($this->lang->get('CANT_EXEC').' '.implode(', ', $errors));
            return;
        }
        $this->response->success();
    }

    /**
     * Uninstall external module
     */
    public function deleteAction()
    {
        $repo =  $this->externalsManager->getComposerRepo();

        if(!$this->checkCanEdit()){
            return;
        }

        $id =  $this->request->post('id', Filter::FILTER_STRING, false);
        $composerPackageName = $this->externalsManager->getComposerPackageName($id);

        if(!$this->externalsManager->uninstall($id)){
            $errors = $this->externalsManager->getErrors();
            $this->response->error($this->lang->get('CANT_EXEC').' '.json_encode($errors));
            return;
        }

        // Composer package
        if(!empty($composerPackageName)){
            $client = $this->getClient($repo);
            if(!$client){
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
            }
            try{
                if(!$client->remove($composerPackageName)){
                    throw new \Exception('Composer: cant delete '.$composerPackageName);
                }
            }catch (\Exception $e){
                $this->response->error($this->lang->get('CANT_EXEC').' '.implode(', ', $e->getMessage()));
                return;
            }
        }

        $this->response->success();
    }

    /**
     * Rebuild class map
     */
    public function buildMapAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }
        $mapBuilder = new App\Classmap($this->appConfig);
        $mapBuilder->update();

        if(!$mapBuilder->save()){
            $this->response->error($this->lang->get('CANT_EXEC').' Build Map');
            return;
        }

        $this->response->success();
    }

    /**
     * Get Repo list
     */
    public function repoListAction()
    {
        $repoList = $this->externalsManager->getRepoList();
        $data = [];
        foreach ($repoList as $key=>$info){
            $data[] = [
                'id' => $key,
                'title' => $info['title']
            ];
        }
        $this->response->success($data);
    }

    /**
     * Get list of repository items
     */
    public function repoItemsListAction()
    {
        $repo =  $this->request->post('repo', Filter::FILTER_STRING, false);
        $params =  $this->request->post('pager' , Filter::FILTER_ARRAY, []);

        $externalsLang = Lang::lang('externals');


        $client = $this->getClient($repo);
        if(!$client){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try{
           $list = $client->getList($params);
        }catch (\Exception $e){
            $this->response->error($e->getMessage());
            return;
        }

        $this->response->json($list);
    }

    /**
     * Prepare client adapter
     * @param string $repo
     * @return ClientInterface|null
     * @throws \Exception
     */
    protected function getClient(string $repo) : ?ClientInterface
    {
        $externalsLang = Lang::lang('externals');
        $repoList = $this->externalsManager->getRepoList();

        if(!isset($repoList[$repo])){
           return null;
        }

        $config = \Dvelum\Config\Factory::create($repoList[$repo]['adapterConfig']);

        /**
         * @var ClientInterface $client
         */
        $client = new $repoList[$repo]['adapter']($config);
        $client->setLanguage($this->appConfig->get('language'));
        $client->setLocalization($externalsLang);
        $client->setTmpDir($this->appConfig->get('tmp'));

        return $client;
    }

    /**
     * Download add-on
     */
    public function downloadAction()
    {
        $repo =  $this->request->post('repo', Filter::FILTER_STRING, false);
        $app =  $this->request->post('app', Filter::FILTER_STRING, false);

        if(empty($app) || empty($repo)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $client = $this->getClient($repo);
        if(!$client){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        try{
           if($client->download($app)){
               $this->response->json(['success'=>true,'msg'=>Lang::lang('externals')->get('package_downloaded')]);
           }else{
               $this->response->error($this->lang->get('CANT_EXEC'));
           }
        }catch (\Exception $e){
            $this->response->error($e->getMessage());
            return;
        }
    }
}