<?php
namespace Dvelum\App\Data\Api;

use Dvelum\Config;

class Request
{
    protected $object;
    protected $pagination;
    protected $query;
    protected $filters;

    protected $config;

    public function __construct(\Dvelum\Request $request)
    {
        $this->config = Config::storage()->get('api/request.php');
        $this->pagination = $request->post($this->config->get('paginationParam')  , 'array' , []);
        $this->filters = array_merge($request->post($this->config->get('filterParam')  , 'array' , []), $request->extFilters());
        $this->query = $request->post($this->config->get('searchParam')  , 'string' , '');
        $this->object = $request->post($this->config->get('objectParam') , 'string' , '');
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getFilter($name)
    {
        if(isset($this->filters[$name])){
            return $this->filters[$name];
        }
        return null;
    }

    public function addFilter($key, $val)
    {
        $this->filters[$key] = $val;
    }

    public function resetFilter($key)
    {
        unset($this->filters[$key]);
    }

    public function setObject($name)
    {
        $this->object = $name;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getPagination()
    {
        return $this->pagination;
    }

    public function getQuery()
    {
        return $this->query;
    }
}