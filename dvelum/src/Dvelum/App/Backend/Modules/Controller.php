<?php
/**
 * DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
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
declare(strict_types=1);

namespace Dvelum\App\Backend\Modules;

use Dvelum\App\Backend;
use Dvelum\App\Classmap;
use Dvelum\App\Model\Permissions;
use Dvelum\App\Module\Generator\GeneratorInterface;
use Dvelum\App\Module\Manager;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Filter;
use Dvelum\Utils;
use Dvelum\File;

class Controller extends Backend\Controller
{

    public function getModule(): string
    {
        return 'Modules';
    }

    public function getObjectName(): string
    {
        return '';
    }

    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction()
    {
        $this->resource->addJs('/js/app/system/FilesystemWindow.js', 1);
        $this->resource->addJs('/js/app/system/ImageField.js', 1);
        $this->resource->addJs('/js/app/system/IconField.js', 1);
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 1);
        $this->resource->addJs('/js/app/system/EditWindow.js', 1);
        $this->resource->addJs('/js/app/system/crud/modules.js', 2);
        $this->resource->addJs('/js/app/system/Modules.js', 3);
        $module = $this->getModule();
        $this->resource->addInlineJs('
            var canEdit = ' . ((integer)$this->user->getModuleAcl()->canEdit($module)) . ';
            var canDelete = ' . ((integer)$this->user->getModuleAcl()->canDelete($module)) . ';
        ');
        parent::indexAction();
    }

    /**
     * Get modules list
     */
    public function listAction()
    {
        $type = $this->request->post('type', Filter::FILTER_STRING, false);

        if (!$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch ($type) {
            case 'backend':
                $this->listBackend();
                break;
            case 'frontend':
                $this->listFrontend();
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }
    }

    /**
     * Get list of backend modules
     */
    public function listBackend()
    {
        $manager = new Manager();
        $data = $manager->getList();

        foreach ($data as $k => &$item) {
            $item['related_files'] = '';
            if (empty($item['dist'])) {
                $relatedFiles = $this->getRelatedFiles($item);
                if (!empty($relatedFiles)) {
                    $item['related_files'] = implode('<br>', $relatedFiles);
                }
            }

            $item['iconUrl'] = $this->appConfig->get('wwwRoot') . $item['icon'];
        }
        $this->response->success(array_values($data));
    }

    /**
     * Get list of frontend modules
     */
    public function listFrontend()
    {
        $manager = new Manager\Frontend();
        $data = $manager->getList();
        $this->response->success(array_values($data));
    }

    /**
     * Update modules list
     */
    public function updateAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $type = $this->request->post('type', Filter::FILTER_STRING, false);

        if (!$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch ($type) {
            case 'backend':
                $this->updateBackendRecord();
                break;
            case 'frontend':
                $this->updateFrontendRecord();
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }
    }

    /**
     * Update frontend module record
     */
    protected function updateFrontendRecord()
    {
        $id = $this->request->post('id', Filter::FILTER_STRING, false);

        $acceptedFields = [
            'code' => Filter::FILTER_STRING,
            'title' => Filter::FILTER_STRING,
            'class' => Filter::FILTER_STRING,
        ];

        $data = [];
        $errors = [];
        foreach ($acceptedFields as $name => $type) {
            $data[$name] = $this->request->post($name, $type, false);
            if (empty($data[$name])) {
                $errors[$name] = $this->lang->get('CANT_BE_EMPTY');
            }
        }

        if (!empty($errors)) {
            $this->response->error($this->lang->get('FILL_FORM'), $errors);
            return;
        }

        $manager = new Manager\Frontend();

        if (empty($id) && $manager->isValidModule($data['code'])) {
            $this->response->error(
                $this->lang->get('INVALID_VALUE'),
                [
                    'code' => $this->lang->get('SB_UNIQUE')
                ]
            );
            return;
        }

        if (empty($id)) {
            $id = $data['code'];
        }

        if (!$manager->updateModule($id, $data)) {
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
            return;
        }

        $this->response->success(['id' => $data['code']]);
    }

    /**
     * Update module record
     */
    protected function updateBackendRecord()
    {
        $manager = new Manager();
        $moduleName = '';

        $id = $this->request->post('id', Filter::FILTER_STRING, false);
        $controller = $this->request->post('class', Filter::FILTER_STRING, false);

        if (!$id) {
            if (!$controller) {
                $this->response->error(
                    $this->lang->get('INVALID_VALUE'),
                    ['class' => $this->lang->get('CANT_BE_EMPTY')]
                );
            } else {
                $replace = $this->appConfig->get('backend_controllers_dirs');
                $replace[] = 'Controller';
                $replace[] = 'Dvelum\\App';

                foreach ($replace as &$item) {
                    $item = str_replace('/', '\\', $item);
                }
                unset($item);
                /**
                 * @var string $moduleName
                 */
                $moduleName = str_replace($replace, '', $controller);
                $moduleName = str_replace('\\', '_', $moduleName);
                $moduleName = trim($moduleName, '_\\');

                $list = $manager->getRegisteredModules();
                if (in_array($moduleName, $list, true)) {
                    $this->response->error(
                        $this->lang->get('INVALID_VALUE'),
                        ['class' => $this->lang->get('SB_UNIQUE')]
                    );
                }
            }
        }

        $acceptedFields = [
            //'id'=> Filter::FILTER_STRING ,
            'dev' => Filter::FILTER_BOOLEAN,
            'active' => Filter::FILTER_BOOLEAN,
            'title' => Filter::FILTER_STRING,
            'class' => Filter::FILTER_STRING,
            'designer' => Filter::FILTER_STRING,
            'in_menu' => Filter::FILTER_BOOLEAN,
            'icon' => Filter::FILTER_STRING
        ];

        $data = [];

        foreach ($acceptedFields as $name => $type) {
            if ($type === Filter::FILTER_BOOLEAN) {
                $data[$name] = $this->request->post($name, $type, false);
            } else {
                $data[$name] = $this->request->post($name, $type, null);
            }
        }

        $data['class'] = trim($data['class'], '\\');

        if ($id) {
            if (!$manager->isValidModule($id)) {
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
            }
        } else {
            $id = $moduleName;
        }

        $data['id'] = $id;

        if ($manager->updateModule($id, $data)) {
            $this->response->success(['id' => $id]);
        } else {
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
            return;
        }
    }

    /**
     * Get list of available controllers
     */
    public function controllersAction()
    {
        $type = $this->request->post('type', Filter::FILTER_STRING, false);

        if (!$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch ($type) {
            case 'backend':
                $manager = new Manager();
                $this->response->success($manager->getAvailableControllers());
                break;
            case 'frontend':
                $manager = new Manager\Frontend();
                $this->response->success($manager->getControllers());
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }
    }

    /**
     * Get Designer projects tree list
     */
    public function fslistAction()
    {
        $node = $this->request->post('node', 'string', '');
        $manager = new \Designer_Manager($this->appConfig);
        $this->response->json($manager->getProjectsList($node));
    }

    /**
     * Get list of registered Db_Object's
     */
    public function objectsAction()
    {
        $manager = new Orm\Record\Manager();
        $list = $manager->getRegisteredObjects();
        $data = array();

        $config = Config::storage()->get('backend.php');
        $systemObjects = $config->get('system_objects');

        if(!empty($list)){
            foreach ($list as $key) {
                if (
                    // not system object
                    !in_array(ucfirst($key), $systemObjects, true)
                    &&
                    // no core 1.x backend controller
                    !class_exists('Backend_' . Utils\Strings::formatClassName($key) . '_Controller')
                    &&
                    // no core 2.x backend controller
                    !class_exists('App\\Backend\\' . Utils\Strings::formatClassName($key) . '\\Controller')
                ) {
                    $data[] = array('id' => $key, 'title' => Orm\Record\Config::factory($key)->getTitle());
                }
            }
        }
        $this->response->success($data);
    }

    /**
     * Create new module
     */
    public function createAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $object = $this->request->post('object', 'string', false);

        if (!$object) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $objectClassName = Utils\Strings::formatClassName($object, true);
        $object = Utils\Strings::formatClassName($object, false);

        $class = 'App\\Backend\\' . $objectClassName . '\\Controller';

        if (class_exists($class)) {
            $this->response->error($this->lang->get('FILL_FORM'), [
                    'id' => 'name',
                    'msg' => $this->lang->get('SB_UNIQUE') . ' class ' . $class
            ]);
            return;
        }

        $designerConfig = Config::storage()->get('designer.php');

        $projectRelativePath = '/' . strtolower($object) . '.designer.dat';
        $projectFile = Config::storage()->getWrite() . $designerConfig->get('configs') . strtolower($object) . '.designer.dat';

        if (file_exists($projectFile)) {
            $this->response->error($this->lang->get('FILE_EXISTS') . '(' . $projectFile . ')');
            return;
        }

        $objectConfig = Orm\Record\Config::factory($object);

        $manager = new Orm\Record\Manager();

        if (!$manager->objectExists($object)) {
            $this->response->error($this->lang->get('FILL_FORM'),
                ['id' => 'object', 'msg' => $this->lang->get('INVALID_VALUE')]);
            return;
        }

        $config = Config::storage()->get('backend.php');
        $codeGenAdApter = $config->get('modules_generator');
        /**
         * @var GeneratorInterface $codeGen
         */
        $codeGen = new $codeGenAdApter(
            $this->appConfig,
            Config::storage()->get('designer.php'),
            Config::storage()->get('modules_generator.php')
        );
        try {
            if ($objectConfig->isRevControl()) {
                $codeGen->createVcModule($object, $class, $projectFile);
            } else {
                $codeGen->createModule($object, $class, $projectFile);
            }

        } catch (\Exception $e) {
            $this->response->error($e->getMessage());
            return;
        }

        $userInfo = $this->user->getInfo();
        /**
         * @var Permissions $permissionModel
         */
        $permissionModel = Model::factory('Permissions');

        if (!$permissionModel->setGroupPermissions($userInfo['group_id'], $object, true, true, true, true)) {
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }

        $modulesManager = new Manager();
        $modulesManager->addModule($object, array(
            'class' => $class,
            'id' => $object,
            'active' => true,
            'dev' => false,
            'title' => $objectConfig->getTitle(),
            'designer' => $projectRelativePath,
            'icon' => 'i/system/icons/default.png',
            'in_menu' => true
        ));

        $this->createClassMap();

        $this->response->success(
            array(
                'class' => $class,
                'name' => $object,
                'active' => true,
                'dev' => false,
                'title' => $objectConfig->getTitle(),
                'designer' => $projectFile
            )
        );
    }

    /**
     * Get module data
     */
    public function loadDataAction()
    {
        $id = $this->request->post('id', Filter::FILTER_STRING, false);
        $type = $this->request->post('type', Filter::FILTER_STRING, false);

        if (!$id || !$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch ($type) {
            case 'backend':
                $manager = new Manager();
                $this->response->success($manager->getModuleConfig($id));
                break;
            case 'frontend':
                $manager = new Manager\Frontend();
                $data = $manager->getModuleConfig($id);
                $data['id'] = $data['code'];
                $this->response->success($data);
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }
    }

    public function deleteAction()
    {
        if (!$this->checkCanDelete()) {
            return;
        }

        $type = $this->request->post('type', Filter::FILTER_STRING, false);

        if (!$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        switch ($type) {
            case 'backend':
                $this->deleteBackendModule();
                break;
            case 'frontend':
                $this->deleteFrontendModule();
                break;
            default:
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
        }
    }

    /**
     * Delete frontend module
     */
    protected function deleteFrontendModule()
    {
        $manager = new Manager\Frontend();
        $code = $this->request->post('code', Filter::FILTER_STRING, false);

        if (!$code || !$manager->isValidModule($code)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        if ($manager->removeModule($code)) {
            $this->createClassMap();
            $this->response->success();
        } else {
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
            return;
        }
    }

    /**
     * Get list of module files which can be deleted
     * @param array $moduleConfig
     * @return array
     */
    protected function getRelatedFiles($moduleConfig)
    {
        $relatedFiles = [];
        $classFile = $this->appConfig->get('local_controllers') . str_replace('_', '/',
                $moduleConfig['class']) . '.php';

        if (empty($moduleConfig['dist']) && file_exists($classFile)) {
            $relatedFiles[] = $classFile;
        }

        if (empty($moduleConfig['dist']) && !empty($moduleConfig['designer'])) {
            // local configs path
            $configWrite = Config::storage()->getWrite();
            // relative designer projects path
            $layoutsPath = Config::storage()->get('designer.php')->get('configs');
            // project path in local configs directory
            $projectWrite = $configWrite . $layoutsPath . $moduleConfig['designer'];

            if (file_exists($projectWrite)) {
                $relatedFiles[] = $projectWrite;
                if (is_dir($projectWrite . '.files')) {
                    $relatedFiles[] = $projectWrite . '.files';
                }
            }
        }
        return $relatedFiles;
    }

    /**
     * Delete module
     */
    protected function deleteBackendModule()
    {
        if (!$this->checkCanDelete()) {
            return;
        }

        $module = $this->request->post('id', 'string', false);
        $removeRelated = $this->request->post('delete_related', 'boolean', false);

        $manager = new Manager();
        $data = $manager->getList();
        if (!$module || !strlen($module) || !$manager->isValidModule($module) || !isset($data[$module])) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $item = $data[$module];

        $filesToDelete = [];

        if ($removeRelated && empty($item['dist'])) {
            $filesToDelete = $this->getRelatedFiles($item);
        }

        // check before deleting
        if (!empty($filesToDelete)) {
            $err = array();
            foreach ($filesToDelete as $file) {
                if (!is_writable($file)) {
                    $err[] = $file;
                }
            }

            if (!empty($err)) {
                $this->response->error($this->lang->get('CANT_WRITE_FS') . "\n<br>" . implode(",\n<br>", $err));
                return;
            }
        }

        $manager->removeModule($module);

        if (!$manager->save()) {
            $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $manager->getConfig()->getName());
            return;
        }

        // try to delete related files
        if (!empty($filesToDelete)) {
            $err = array();
            foreach ($filesToDelete as $file) {
                if (!file_exists($file)) {
                    continue;
                }

                if (is_dir($file)) {
                    if (!File::rmdirRecursive($file, true)) {
                        $err[] = $file;
                    }
                } else {
                    if (!unlink($file)) {
                        $err[] = $file;
                    }
                }
            }

            if (!empty($err)) {
                $this->response->error($this->lang->get('CANT_WRITE_FS') . "\n<br>" . implode(",\n<br>", $err));
                return;
            }
        }

        $this->createClassMap();
        $this->response->success();
    }

    /**
     * Get list of image folders
     */
    public function iconDirsAction()
    {
        $path = $this->request->post('node', 'string', '');
        $path = str_replace('.', '', $path);

        $dirPath = $this->appConfig->get('wwwPath');

        if (!is_dir($dirPath . $path)) {
            $this->response->json([]);
            return;
        }

        $files = File::scanFiles($dirPath . $path, [], false, File::DIRS_ONLY);

        if (empty($files)) {
            $this->response->json([]);
            return;
        }

        sort($files);
        $list = [];

        foreach ($files as $k => $fpath) {
            $text = basename($fpath);

            $obj = new \stdClass();
            $obj->id = str_replace($dirPath, '', $fpath);
            $obj->text = $text;
            $obj->url = '/' . $obj->id;

            if (is_dir($fpath)) {
                $obj->expanded = false;
                $obj->leaf = false;
            } else {
                $obj->leaf = true;
            }
            $list[] = $obj;
        }
        $this->response->json($list);
    }

    /**
     * Get image list
     */
    public function iconListAction()
    {
        $dirPath = $this->appConfig->get('wwwPath');
        $dir = $this->request->post('dir', 'string', '');

        if (!is_dir($dirPath . $dir)) {
            $this->response->json([]);
            return;
        }

        // windows & linux paths fix
        $scanPath = str_replace('//', '/', $dirPath . $dir);

        $files = File::scanFiles($scanPath, array('.jpg', '.png', '.gif', '.jpeg'), false, File::FILES_ONLY);

        if (empty($files)) {
            $this->response->json([]);
            return;
        }

        sort($files);
        $list = [];

        foreach ($files as $filePath) {
            $text = basename($filePath);
            $list[] = [
                'name' => $text,
                'url' => str_replace($dirPath, $this->appConfig->get('wwwroot'), $filePath),
                'path' => str_replace($dirPath, '', $filePath),
            ];
        }
        $this->response->success($list);
    }

    /**
     * Rebuild class map
     */
    public function rebuildMapAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        if (!$this->createClassMap()) {
            $this->response->error('Cannot create map');
            return;
        }
        $this->response->success();
    }

    /**
     *
     */
    public function createClassMap()
    {
        $mapBuilder = new Classmap($this->appConfig);
        $mapBuilder->update();
        return $mapBuilder->save();
    }

    /**
     * Get desktop module info
     */
    public function desktopModuleInfo()
    {
        $projectData = [];
        $projectData['includes']['js'][] = '/js/app/system/Modules.js';
        $projectData['includes']['js'][] = '/js/app/system/FilesystemWindow.js';
        $projectData['includes']['js'][] = '/js/app/system/IconField.js';

        $module = $this->getModule();
        /*
         * Module bootstrap
         */
        if (file_exists($this->appConfig->get('jsPath') . 'app/system/desktop/' . strtolower($module) . '.js')) {
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($module) . '.js';
        }

        return $projectData;
    }
}
