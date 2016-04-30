<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net Copyright
 * (C) 2011-2013 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */
/**
 * Base class for routing of requests
 */
abstract class Router implements Router_Interface
{
    /**
     * @var Request
     */
    protected $_request;

    public function __construct()
    {
        $this->_request = Request::getInstance();
    }

    /**
     * Route request
     */
    abstract public function route();

    /**
     * Calc url for module
     * @param string $module — module name
     * @return string
     */
    abstract public function findUrl($module);

    /**
     * Run controller
     * @param string $controller - controller class
     * @param string|boolean $action - action name
     * @return mixed
     */
    public function runController($controller , $action = false)
    {
        if(!class_exists($controller))
            return false;

        $controller = new $controller();
        $controller->setRouter($this);

        if($controller instanceof Router_Interface){
            return $controller->route();
        }

        if($action===false || !strlen($action) || !method_exists($controller , $action.'Action'))
        {
            if(strlen($action) && Request::isAjax()){
                Response::jsonError(Lang::lang()->get('WRONG_REQUEST').' ' . Request::getInstance()->getUri());
            }
            $action = 'index';
        }
        return $controller->{$action.'Action'}();
    }
}