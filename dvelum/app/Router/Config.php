<?php
class Router_Config extends Router
{
    protected $_appConfig = false;

    public function __construct()
    {
        parent::__construct();
        $this->_appConfig = Registry::get('main' , 'config');
    }

    /**
     * Route request
     *
     * @throws Exception
     */
    public function route()
    {
        $controller = $this->_request->getPart(0);
        $pathCode = Filter::filterValue('pagecode' , $controller);
        $routes = Config::factory(Config::File_Array , $this->_appConfig->get('frontend_modules'))->__toArray();

        if(isset($routes[$pathCode]) && class_exists($routes[$pathCode]['class']))
            $controllerClass = $routes[$pathCode]['class'];
        else
            $controllerClass = 'Frontend_Index_Controller';

        $this->runController($controllerClass , $this->_request->getPart(1));
    }

    /**
     * Run controller
     *
     * @param string $controller - controller class
     * @param string $action - action name
     * @return mixed
     */
    public function runController($controller , $action = false)
    {
        if((strpos('Backend_' , $controller) === 0))
            Response::redirect('/');

        parent::runController($controller , $action);
    }

    /**
     * Define url address to call the module
     * The method locates the url of the published page with the attached
     * functionality
     * specified in the passed argument.
     * Thus, there is no need to know the exact page URL.
     *
     * @param string $module- module name
     * @return string
     */
    public function findUrl($module)
    {
        return '/'.$module;
    }
}