<?php

use Dvelum\Config;
use Dvelum\File;

class Backend_Designer_Sub_Url extends Backend_Designer_Sub
{
    /**
     * Files list
     */
    public function fslistAction()
    {
        $path = Request::post('node', 'string', '');
        $path = str_replace('.', '', $path);


        $autoloaderConfig = Config::storage()->get('autoloader.php');
        $classPaths = $autoloaderConfig['paths'];

        $files = [];

        foreach ($classPaths as $item) {
            if (is_dir($item . $path)) {
                $list = File::scanFiles($item . $path, array('.php'), false, File::Files_Dirs);
                if (!empty($list)) {
                    $pathLen = strlen($item);
                    foreach ($list as $k => &$v) {
                        $v = substr($v, $pathLen);
                    }
                    unset($v);
                    $files = array_merge($files, $list);
                }
            }
        }

        if (empty($files)) {
            Response::jsonArray(array());
        }

        sort($files);
        $list = array();

        foreach ($files as $k => $fpath) {
            $text = basename($fpath);

            $obj = new stdClass();
            $obj->id = $fpath;
            $obj->text = $text;

            if ($obj->text === 'Controller.php') {
                $controllerName = str_replace('/', '_', substr($fpath, 1, -4));
                $obj->url = Backend_Designer_Code::getControllerUrl($controllerName);
            } else {
                $obj->url = '';
            }

            if (File::getExt($fpath) === '.php') {
                if ($text !== 'Controller.php' || $path === '/') {
                    continue;
                }

                $obj->leaf = true;
            } else {
                $obj->expanded = false;
                $obj->leaf = false;
            }
            $list[] = $obj;
        }
        Response::jsonArray($list);
    }


    public function actionsAction()
    {
        $controller = Request::post('controller', 'string', '');
        if (!strlen($controller)) {
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        if (strpos($controller, '.php') !== false) {
            $controller = Utils::classFromPath($controller, true);
        }

        $actions = Backend_Designer_Code::getPossibleActions($controller);
        Response::jsonSuccess($actions);
    }


    public function imgdirlistAction()
    {
        $path = Request::post('node', 'string', '');
        $path = str_replace('.', '', $path);

        $dirPath = $this->_configMain->get('wwwPath');

        if (!is_dir($dirPath . $path)) {
            Response::jsonArray(array());
        }

        $files = File::scanFiles($dirPath . $path, false, false, File::Dirs_Only);

        if (empty($files)) {
            Response::jsonArray(array());
        }

        sort($files);
        $list = array();

        foreach ($files as $k => $fpath) {
            $text = basename($fpath);
            if ($text === '.svn') {
                continue;
            }

            $obj = new stdClass();
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
        Response::jsonArray($list);
    }

    public function imglistAction()
    {
        $templates = $this->_config->get('templates');

        $dirPath = $this->_configMain->get('wwwPath');
        $dir = Request::post('dir', 'string', '');

        if (!is_dir($dirPath . $dir)) {
            Response::jsonArray(array());
        }

        $files = File::scanFiles($dirPath . $dir, array('.jpg', '.png', '.gif', '.jpeg'), false, File::Files_Only);

        if (empty($files)) {
            Response::jsonArray(array());
        }

        sort($files);
        $list = array();

        foreach ($files as $k => $fpath) {
            // ms fix
            $fpath = str_replace('\\', '/', $fpath);

            $text = basename($fpath);
            if ($text === '.svn') {
                continue;
            }

            $list[] = array(
                'name' => $text,
                'url' => str_replace($dirPath . '/', $this->_configMain->get('wwwroot'), $fpath),
                'path' => str_replace($dirPath . '/', $templates['wwwroot'], $fpath),
            );
        }
        Response::jsonSuccess($list);
    }
}