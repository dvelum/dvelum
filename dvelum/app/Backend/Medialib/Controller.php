<?php
class Backend_Medialib_Controller extends Backend_Controller
{
    /**
     * Get list of media library items
     */
    public function listAction()
    {
        $pager = Request::post('pager', 'array', array());
        $filter = Request::post('filter', 'array', array());
        $query = Request::post('search', 'string', false);

        if(isset($filter['category']) && !intval($filter['category']))
            $filter['category'] = null;

        $media = Model::factory('Medialib');
        $data = $media->getListVc($pager , $filter, $query,'*','user_name');

        $wwwRoot = $this->_configMain->get('wwwroot');

        if(!empty($data))
        {
            foreach ($data as $k=>&$v)
            {
                if($v['type'] == 'image')
                {
                    $v['srcpath'] = Model_Medialib::addWebRoot(str_replace($v['ext'],'',$v['path']));
                    $v['thumbnail'] = Model_Medialib::getImgPath($v['path'] , $v['ext'] , 'thumbnail', true);
                    $v['icon'] = Model_Medialib::getImgPath($v['path'] , $v['ext'] , 'icon' , true);
                }
                else
                {
                    $v['icon'] = $wwwRoot . 'i/unknown.png';
                    $v['thumbnail'] = $wwwRoot . 'i/unknown.png';
                    $v['srcpath'] = '';
                }

                $v['path'] = Model_Medialib::addWebRoot($v['path']);
            }unset($v);
        }

        $result = array(
            'success'=>true,
            'count'=>$media->getCount( $filter , $query),
            'data'=>$data
        );
        Response::jsonArray($result);
    }

    /**
     * Upload images to media library
     */
    public function uploadAction()
    {
        $uploadCategory = Request::getInstance()->getPart(3);

        if(!$uploadCategory)
            $uploadCategory = null;

        $this->_checkCanEdit();

        $docRoot = $this->_configMain->get('wwwpath');
        $mediaModel = Model::factory('Medialib');
        $mediaCfg = $mediaModel->getConfig();

        $path = $this->_configMain->get('uploads') . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        if(!is_dir($path) && !@mkdir($path, 0775, true))
            Response::jsonError($this->_lang->CANT_WRITE_FS);

        $files = Request::files();

        $uploader = new Upload($mediaCfg->__toArray());

        if(empty($files))
            Response::jsonError($this->_lang->NOT_UPLOADED);

        $uploaded = $uploader->start($files, $path);

        if(empty($uploaded))
            Response::jsonError($this->_lang->NOT_UPLOADED);

        $data = array();

        foreach ($uploaded as $k=>&$v)
        {
            $path = str_replace($docRoot , '/' , $v['path']);

            $id =  $mediaModel->addItem($v['title'] , $path , $v['size'] , $v['type'] ,$v['ext']  , $uploadCategory);

            $item = Model::factory('Medialib')->getItem($id);

            if($item['type'] == 'image')
                $item['srcpath'] = Model_Medialib::addWebRoot(str_replace($item['ext'],'',$item['path']));
            else
                $item['srcPath'] = '';

            $item['thumbnail'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'thumbnail' , true);
            $item['icon'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'icon' , true);
            $item['path'] = Model_Medialib::addWebRoot($item['path']);

            $data[] = $item;
        }
        Response::jsonSuccess($data);
    }

    /**
     * Crop image
     */
    public function cropAction()
    {
        $this->_checkCanEdit();

        $id = Request::post('id', 'integer', false);
        $x = Request::post('x', 'integer', false);
        $y = Request::post('y', 'integer', false);
        $w = Request::post('w', 'integer', false);
        $h = Request::post('h', 'integer', false);
        $type = Request::post('type','string', false)  ;

        if(!$id || !$w || !$h || !$type){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        $mediaModel = Model::factory('Medialib');
        $item = $mediaModel->getItem($id);

        if(!$item){
            Response::jsonError($this->_lang->WRONG_REQUEST);
        }

        if($mediaModel->cropAndResize($item, $x, $y, $w, $h , $type))
        {
            $mediaModel->updateModifyDate($id);
            $mediaModel->markCroped($id);
            Response::jsonSuccess();
        }else{
            Response::jsonError($this->_lang->CANT_EXEC);
        }
    }

    /**
     * Remove image
     */
    public function removeAction()
    {
        $this->_checkCanDelete();
        $id = Request::post('id','integer', false);

        if(!$id)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $media= Model::factory('Medialib');
        if($media->remove($id))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->WRONG_REQUEST);
    }

    /**
     * Update image info
     */
    public function updateAction()
    {
        $this->_checkCanEdit();
        $id = Request::post('id' , 'integer' , false);

        if(!$id)
            Response::jsonError($this->_lang->WRONG_REQUEST);

        $fields = array('title' , 'alttext' , 'caption' , 'description');
        $data = array();

        foreach($fields as $v)
        {
            if($v == 'caption')
                $data[$v] = Request::post($v , 'raw' , '');
            elseif($v == 'category')
                $data[$v] = Request::post($v,'integer', null);
            else
                $data[$v] = Request::post($v , 'string' , '');
        }

        if(!strlen($data['title']))
            Response::jsonError($this->_lang->FILL_FORM , array('title' => $this->_lang->CANT_BE_EMPTY));

        $media = Model::factory('Medialib');

        if($media->update($id , $data))
            Response::jsonSuccess();
        else
            Response::jsonError($this->_lang->CANT_EXEC);
    }

    /**
     * Get item data
     */
    public function getitemAction()
    {
        $id = Request::post('id','integer',false);

        if(!$id)
            Response::jsonError();

        $item = Model::factory('Medialib')->getItem($id);

        if($item['type'] == 'image')
            $item['srcpath'] = Model_Medialib::addWebRoot(str_replace($item['ext'],'',$item['path']));
        else
            $item['srcPath'] = '';

        $item['thumbnail'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'thumbnail' , true);
        $item['icon'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'icon' , true);
        $item['path'] = Model_Medialib::addWebRoot($item['path']);

        Response::jsonSuccess($item);
    }

    /**
     * Get item info for mediaitem field
     */
    public function infoAction()
    {
        $id = Request::post('id','integer',false);

        if(!$id)
            Response::jsonSuccess(array('exists'=>false));

        $item = Model::factory('Medialib')->getItem($id);

        if(empty($item))
            Response::jsonSuccess(array('exists'=>false));

        if($item['type'] == 'image')
            $icon = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'thumbnail' , true).'?m='.date('ymdhis' , strtotime($item['modified']));
        else
            $icon = $this->_configMain->get('wwwroot') . 'i/unknown.png';

        Response::jsonSuccess(
            array(
                'exists'=>true ,
                'type'=>$item['type'],
                'icon'=>$icon,
                'title'=>$item['title'],
                'size' => $item['size'].' Mb'
            )
        );
    }

    /**
     * Get access permissions for current user
     */
    public function rightsAction()
    {
        $user = User::getInstance();
        $results = array(
            'canEdit'=>$user->canEdit($this->_module),
            'canDelete'=>$user->canDelete($this->_module),
        );
        Response::jsonSuccess($results);
    }

    /**
     * Dev. method. Compile JavaScript sources
     */
    public function compileAction()
    {
        $sources = array(
            'js/app/system/medialib/Category.js',
            'js/app/system/medialib/Panel.js',
            'js/app/system/medialib/Models.js',
            'js/app/system/medialib/FileUploadWindow.js',
            'js/app/system/medialib/ImageSizeWindow.js',
            'js/app/system/medialib/SelectMediaItemWindow.js',
            'js/app/system/medialib/ItemField.js',
            'js/app/system/medialib/EditWindow.js',
            'js/app/system/medialib/CropWindow.js'
        );

        if(!$this->_configMain->get('development')){
            die('Use development mode');
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->_configMain->get('wwwpath');

        foreach ($sources as $filePath){
            $s.=file_get_contents($wwwPath.'/'.$filePath)."\n";
            $totalSize+=filesize($wwwPath.'/'.$filePath);
        }

        $time = microtime(true);
        file_put_contents($wwwPath.'/js/app/system/Medialib.js', Code_Js_Minify::minify($s));
        echo '
      			Compilation time: '.number_format(microtime(true)-$time,5).' sec<br>
      			Files compiled: '.sizeof($sources).' <br>
      			Total size: '.Utils::formatFileSize($totalSize).'<br>
      			Compiled File size: '.Utils::formatFileSize(filesize($wwwPath.'/js/app/system/Medialib.js')).' <br>
      		';
        exit;
    }
}