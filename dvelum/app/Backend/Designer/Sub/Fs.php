<?php

class Backend_Designer_Sub_Fs extends Backend_Designer_Sub
{
    /**
     * Designer config
     * @var Config_File_Array
     */
    protected $_config;

    protected $_module = 'Designer';

    /**
     * Files list
     */
    public function fslistAction()
    {
        $node = Request::post('node', 'string', '');
        $manager = new Designer_Manager($this->_configMain);
        Response::jsonArray($manager->getProjectsList($node));
    }

    /**
     * Create config subfolder
     */
    public function fsmakedirAction()
    {

        $name = Request::post('name', 'string', '');
        $path = Request::post('path', 'string', '');

        $name = str_replace(array(DIRECTORY_SEPARATOR), '', $name);

        if (!strlen($name)) {
            Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');
        }

        $newPath = Config::storage()->getWrite() . $this->_config->get('configs');

        if (strlen($path)) {
            if (!is_dir($newPath . $path)) {
                Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 2]');
            }

            $newPath .= $path . DIRECTORY_SEPARATOR;
        }

        $newPath .= DIRECTORY_SEPARATOR . $name;

        if (@mkdir($newPath, 0775)) {
            Response::jsonSuccess();
        } else {
            Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' . $newPath);
        }
    }

    /**
     * Create new report
     */
    public function fsmakefileAction()
    {
        $name = Request::post('name', 'string', '');
        $path = Request::post('path', 'string', '');

        if (!strlen($name)) {
            Response::jsonError($this->_lang->WRONG_REQUEST . ' [code 1]');
        }

        $writePath = Config::storage()->getWrite();
        $configsPath = $this->_config->get('configs');
        $actionsPath = $this->_config->get('actionjs_path');

        if (strlen($path)) {
            $savePath = $writePath . $configsPath . $path . DIRECTORY_SEPARATOR . $name . '.designer.dat';
            $relPath = $path . DIRECTORY_SEPARATOR . $name . '.designer.dat';
            $actionFilePath = $actionsPath . str_replace($configsPath, '', $path) . DIRECTORY_SEPARATOR . $name . '.js';
        } else {
            $savePath = $writePath . $configsPath . $name . '.designer.dat';
            $relPath = DIRECTORY_SEPARATOR . $name . '.designer.dat';
            $actionFilePath = $actionsPath . $name . '.js';
        }

        $relPath = str_replace('//', '/', $relPath);
        $savePath = str_replace('//', '/', $savePath);

        if (file_exists($savePath)) {
            Response::jsonError($this->_lang->FILE_EXISTS);
        }

        $obj = new Designer_Project();
        $obj->actionjs = $actionFilePath;

        $dir = dirname($savePath);

        if (!file_exists($dir)) {
            try {
                mkdir($dir, 0775, true);
            } catch (Error $e) {
                Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' . $dir);
            }
        }

        if ($this->_storage->save($savePath, $obj)) {
            Response::jsonSuccess(array('file' => $relPath));
        } else {
            Response::jsonError($this->_lang->CANT_WRITE_FS . ' ' . $savePath);
        }
    }
}