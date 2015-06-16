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
        $appPath = $this->_appConfig->get('application_path');
        $folders = File::scanFiles($this->_appConfig->get('frontend_controllers') , false , true , File::Dirs_Only);
        $data = array();

        if(!empty($folders))
        {
            foreach($folders as $item)
            {
                $name = basename($item);
                if(file_exists($item . '/Controller.php'))
                {
                    $name = str_replace($appPath , '' , $item . '/Controller.php');
                    $name = Utils::classFromPath($name);
                    $data[] = array(
                        'id' => $name ,
                        'title' => $name
                    );
                }
            }
        }
        return $data;
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
}