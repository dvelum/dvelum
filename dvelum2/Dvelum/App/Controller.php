<?php
namespace Dvelum\App;

use Dvelum\Config;
use Dvelum\Request;
use Dvelum\Resource;
use Dvelum\Response;

class Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Config\Config|false
     */
    protected $appConfig;
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Router_Interface
     */
    protected $router;

    public function __construct()
    {
        $this->request = Request::factory();
        $this->response = Response::factory();
        $this->resource = Resource::factory();
        $this->appConfig = Config::storage()->get('main.php');

        if($this->request->isAjax()){
            $this->response->setFormat(Response::FORMAT_JSON);
        }
    }

    /**
     * Set link to router
     * @param \Router_Interface $router
     */
    public function setRouter(\Router_Interface $router)
    {
        $this->router = $router;
    }
}