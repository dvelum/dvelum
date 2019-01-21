<?php
namespace Dvelum\App\Module;

class Permissions
{
    public $view = false;
    public $edit = false;
    public $publish = false;
    public $delete = false;
    public $only_own = false;
    public $module = '';

    public function __construct(array $data = [])
    {
        if(!empty($data)){
            foreach ($data as $k=>$v){
                $this->{$k} = $v;
            }
        }
    }
}