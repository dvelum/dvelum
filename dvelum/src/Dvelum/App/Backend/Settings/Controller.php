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

namespace Dvelum\App\Backend\Settings;

use Dvelum\App\Backend;
use Dvelum\Designer\Manager;
use Dvelum\Orm\Model;
use Dvelum\App;
use Dvelum\Config;
use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;
use \Exception;



class Controller extends Backend\Controller
{
    public function getModule(): string
    {
        return 'Settings';
    }

    public function getObjectName(): string
    {
        return 'User_Settings';
    }

    /**
     * Get controller configuration
     * @return Config\ConfigInterface
     */
    protected function getConfig() : Config\ConfigInterface
    {
        return Config::storage()->get('backend/controller/settings.php');
    }

    /**
     * Get list of accepted themes
     */
    public function themesAction()
    {
        $themes = $this->backofficeConfig->get('themes');
        $data = [];
        foreach ($themes as $item){
            $data[] = ['id' => $item];
        }
        $this->response->success($data);
    }

    /**
     * Get list of accepted languages
     */
    public function languagesAction()
    {
        $languages = $this->backofficeConfig->get('languages');
        $data = [];
        foreach ($languages as $item){
            $data[] = ['id' => $item];
        }
        $this->response->success($data);
    }

    /**
     * Load Forms Data
     */
    public function loadDataAction()
    {
        $userData = Model::factory('User');
        $item = $userData->getCachedItem($this->user->getId());

        if(empty($item)){
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $userSettings = Model::factory('User_Settings')->getItemByField('user', $this->user->getId());

        if(!isset($userSettings['theme']) || empty($userSettings['theme'])){
            $userSettings['theme'] = $this->backofficeConfig->get('theme');
        }

        if(!isset($userSettings['language']) || empty($userSettings['language'])){
            $userSettings['language'] = $this->appConfig->get('language');
        }

        $data = [
            'user' => [
                'name' => $item['name'],
                'login' => $item['login'],
                'email' => $item['email'],
            ],
            'settings' => $userSettings
        ];
        $this->response->success($data);
    }

    /**
     * Save user info action
     */
    public function userSaveAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $pass = $this->request->post('pass', 'string', false);
        if ($pass) {
            $this->request->updatePost('pass', password_hash($pass, PASSWORD_DEFAULT));
        }
        $this->request->updatePost('id', $this->user->getId());

        $object = $this->getPostedData('User');

        if (empty($object)){
            return;
        }
        /*
         * New user
         */
        if (!$object->getId()) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if (!$object->save()) {
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }
        $this->response->success();
    }

    /**
     * Get posted data and put it into Orm\Record
     * (in case of failure, JSON error message is sent)
     * @param string $objectName
     * @throws Exception
     * @return Record | null
     */
    public function getPostedData($objectName) : ?Record
    {
        $formCfg = $this->config->get('form');
        $adapterConfig = Config::storage()->get($formCfg['config']);
        $adapterConfig->set('orm_object', $objectName);
        /**
         * @var App\Form\Adapter $form
         */
        $form = new $formCfg['adapter']($this->request, $this->lang, $adapterConfig);
        if (!$form->validateRequest()) {
            $errors = $form->getErrors();
            $formMessages = [$this->lang->get('FILL_FORM')];
            $fieldMessages = [];
            /**
             * @var App\Form\Error $item
             */
            foreach ($errors as $item) {
                $field = $item->getField();
                if (empty($field)) {
                    $formMessages[] = $item->getMessage();
                } else {
                    $fieldMessages[$field] = $item->getMessage();
                }
            }
            $this->response->error(implode('; <br>', $formMessages), $fieldMessages);
            return null;
        }
        return $form->getData();
    }

    /**
     * Save user settings
     */
    public function settingsSaveAction()
    {
        $theme = $this->request->post('theme','string','gray');
        $lang = $this->request->post('language','string','ru');

        $themes = $this->backofficeConfig->get('themes');
        $langs = $this->backofficeConfig->get('languages');

        if(!in_array($theme, $themes, true)){
            $this->response->error($this->lang->get('FILL_FORM'),['theme'=>$this->lang->get('INVALID_VALUE')]);
            return;
        }

        if(!in_array($lang, $langs, true)){
            $this->response->error($this->lang->get('FILL_FORM'),['language'=>$this->lang->get('INVALID_VALUE')]);
            return;
        }

        $settingsModel = Model::factory('User_Settings');
        $userSettings = $settingsModel->getItemByField('user', $this->user->getId());
        $settingId = null;
        $config = Record\Config::factory('User_Settings');
        if(!empty($userSettings)){
            $settingId = $userSettings[$config->getPrimaryKey()];
        }
        try{
            /**
             * @var RecordInterface $object
             */
            $object = Record::factory('User_Settings', $settingId);
            $object->setValues([
                'user' => $this->user->getId(),
                'theme' => $theme,
                'language' => $lang
            ]);
            if(!$object->save()){
                throw new Exception('Cannot save settings for user '.$this->user->getId());
            }
            $this->response->success();
        }catch (Exception $e){
            $settingsModel->logError($e->getMessage());
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Get desktop module info
     */
    public function desktopModuleInfo()
    {
        $moduleName = $this->getModule();

        $modulesConfig = Config::factory(Config\Factory::File_Array , $this->appConfig->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($moduleName);

        $projectData = [];

        if(strlen($moduleCfg['designer']))
        {
            $manager = new Manager($this->appConfig);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$moduleName , $moduleName);
            $projectData['isDesigner'] = true;
            $modulesManager = new App\Module\Manager();
            $modulesList = $modulesManager->getList();
            $projectData['title'] = (isset($modulesList[$this->module])) ? $modulesList[$moduleName]['title'] : '';
        }
        else
        {
            if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($moduleName) . '.js'))
                $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($moduleName) .'.js';
        }
        return $projectData;
    }
}