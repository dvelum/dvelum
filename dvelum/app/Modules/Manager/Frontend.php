<?php
/**
 * Frontend modules manager
 */
class Modules_Manager_Frontend extends Modules_Manager
{
    protected $_mainconfigKey = 'frontend_modules';
    /**
     * Get list of Controllers
     * @return array
     */
    public function getControllers()
    {
        $autoloadCfg = $this->_appConfig->get('autoloader');
        $paths = $autoloadCfg['paths'];
        $dir = $this->_appConfig->get('frontend_controllers_dir');

        $data = array();

        foreach($paths as $path){
            if(!is_dir($path.'/'.$dir)){
                continue;
            }
            $folders = File::scanFiles($path . '/' . $dir, false, true, File::Dirs_Only);

            if(empty($folders))
                continue;

            foreach ($folders as $item)
            {
                $name = basename($item);

                if(file_exists($item.'/Controller.php'))
                {
                    $name = str_replace($path.'/', '', $item.'/Controller.php');
                    $name = Utils::classFromPath($name);
                    $data[$name] = array('id'=>$name,'title'=>$name);
                }
            }
        }
        return array_values($data);
    }
    /**
     * Update module data
     * @param $name
     * @param array $data
     * @return boolean
     */
    public function updateModule($name , array $data)
    {
        if($name !== $data['code']){
            $this->_modulesLocale->remove($name);
            $this->_config->remove($name);
        }

        if(isset($data['title'])){
            $this->_modulesLocale->set($data['code'] , $data['title']);
            if(!$this->_modulesLocale->save()){
                return false;
            }
            unset($data['title']);
        }
        $this->_config->set($data['code'] , $data);
        return $this->save();
    }

    /**
     * Get modules list
     * @return array
     */
    public function getList()
    {
        $list = parent::getList();
        if(!empty($list))
        {
            foreach($list as $k=>&$v)
            {
                if($this->_curConfig && $this->_curConfig->offsetExists($k)){
                    $cfg['dist'] = false;
                }else{
                    $cfg['dist'] = true;
                }
                $v['id'] = $v['code'];
            }
        }
        return $list;
    }
}