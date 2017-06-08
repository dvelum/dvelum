<?php
use Dvelum\App\Router\RouterInterface;
use Dvelum\Request;
use Dvelum\Response;

class Backend_Docs_Controller extends Backend_Controller implements  RouterInterface
{
    /**
     * (non-PHPdoc)
     * @see Router::run()
     */
    public function route(Request $request , Response $response) : void
    {
        $controller = new Sysdocs_Controller($this->_configMain ,2 , false);
        $controller->setCanEdit($this->_user->getModuleAcl()->canEdit($this->_module));
        $controller->run();
    }
    /**
     * (non-PHPdoc)
     * @see Backend_Controller::indexAction()
     */
    public function indexAction(){}
    /**
     * Find url
     * @param string $module
     * @return string
     */
    public function findUrl(string $module): string
    {
        return '';
    }
}