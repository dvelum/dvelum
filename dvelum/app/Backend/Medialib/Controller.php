<?php
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Request;
use Dvelum\App\Controller\EventManager;

class Backend_Medialib_Controller extends Dvelum\App\Backend\Api\Controller
{

    public function initListeners()
    {
        $apiRequest = $this->apiRequest;
        $apiRequest->setObject($this->getObjectName());

        $this->eventManager->on(EventManager::BEFORE_LIST, function(\Dvelum\App\Controller\Event $event) use ($apiRequest){
            $category = $apiRequest->getFilter('category');
            if(empty($category)){
                $apiRequest->resetFilter('category');
            }
        });

        $this->eventManager->on(EventManager::AFTER_LIST,[$this, 'prepareList']);
    }

    public function prepareList(\Dvelum\App\Controller\Event $event)
    {
        $data = & $event->getData()->data;

        $wwwRoot = $this->appConfig->get('wwwroot');

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
    }

    /**
     * Upload images to media library
     */
    public function uploadAction()
    {
        $uploadCategory = $this->request->getPart(3);

        if(!$uploadCategory)
            $uploadCategory = null;

        $this->checkCanEdit();

        $docRoot = $this->appConfig->get('wwwPath');
        $mediaModel = Model::factory('Medialib');
        $mediaCfg = $mediaModel->getConfig();

        $path = $this->appConfig->get('uploads') . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        if(!is_dir($path) && !@mkdir($path, 0775, true))
            $this->response->error($this->lang->get('CANT_WRITE_FS'));

        $files = $this->request->files();

        $uploader = new Upload($mediaCfg->__toArray());

        if(empty($files))
            $this->response->error($this->lang->get('NOT_UPLOADED'));

        $uploaded = $uploader->start($files, $path);

        if(empty($uploaded))
            $this->response->error($this->lang->get('NOT_UPLOADED'));

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
        $this->response->success($data);
    }

    /**
     * Crop image
     */
    public function cropAction()
    {
        $this->checkCanEdit();

        $id = $this->request->post('id', 'integer', false);
        $x = $this->request->post('x', 'integer', false);
        $y = $this->request->post('y', 'integer', false);
        $w = $this->request->post('w', 'integer', false);
        $h = $this->request->post('h', 'integer', false);
        $type = $this->request->post('type','string', false)  ;

        if(!$id || !$w || !$h || !$type){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $mediaModel = Model::factory('Medialib');
        $item = $mediaModel->getItem($id);

        if(!$item){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        if($mediaModel->cropAndResize($item, $x, $y, $w, $h , $type))
        {
            $mediaModel->updateModifyDate($id);
            $mediaModel->markCroped($id);
            $this->response->success();
        }else{
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Remove image
     */
    public function removeAction()
    {
        $this->checkCanDelete();
        $id = $this->request->post('id','integer', false);

        if(!$id)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $media= Model::factory('Medialib');
        if($media->remove($id))
            $this->response->success();
        else
            $this->response->error($this->lang->get('WRONG_REQUEST'));
    }

    /**
     * Update image info
     */
    public function updateAction()
    {
        $this->checkCanEdit();
        $id = $this->request->post('id' , 'integer' , false);

        if(!$id)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $fields = ['title' , 'alttext' , 'caption' , 'description'];
        $data = [];

        foreach($fields as $v)
        {
            if($v == 'caption')
                $data[$v] = $this->request->post($v , 'raw' , '');
            elseif($v == 'category')
                $data[$v] = $this->request->post($v,'integer', null);
            else
                $data[$v] = $this->request->post($v , 'string' , '');
        }

        if(!strlen($data['title']))
            $this->response->error($this->lang->get('FILL_FORM') , array('title' => $this->lang->get('CANT_BE_EMPTY')));

        $media = Model::factory('Medialib');

        if($media->update($id , $data))
            $this->response->success();
        else
            $this->response->error($this->lang->get('CANT_EXEC'));
    }

    /**
     * Get item data
     */
    public function getitemAction()
    {
        $id = $this->request->post('id','integer',false);

        if(!$id)
            $this->response->error();

        $item = Model::factory('Medialib')->getItem($id);

        if($item['type'] == 'image')
            $item['srcpath'] = Model_Medialib::addWebRoot(str_replace($item['ext'],'',$item['path']));
        else
            $item['srcPath'] = '';

        $item['thumbnail'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'thumbnail' , true);
        $item['icon'] = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'icon' , true);
        $item['path'] = Model_Medialib::addWebRoot($item['path']);

        $this->response->success($item);
    }

    /**
     * Get item info for media item field
     */
    public function infoAction()
    {
        $id = $this->request->post('id','integer',false);

        if(!$id)
            $this->response->success(array('exists'=>false));

        $item = Model::factory('Medialib')->getItem($id);

        if(empty($item))
            $this->response->success(array('exists'=>false));

        if($item['type'] == 'image')
            $icon = Model_Medialib::getImgPath($item['path'] , $item['ext'] , 'thumbnail' , true).'?m='.date('ymdhis' , strtotime($item['modified']));
        else
            $icon = $this->appConfig->get('wwwroot') . 'i/unknown.png';

        $this->response->success([
            'exists'=>true ,
            'type'=>$item['type'],
            'icon'=>$icon,
            'title'=>$item['title'],
            'size' => $item['size'].' Mb'
        ]);
    }

    /**
     * Get access permissions for current user
     */
    public function rightsAction()
    {
        $results = array(
            'canEdit'=>$this->moduleAcl->canEdit($this->module),
            'canDelete'=>$this->moduleAcl->canDelete($this->module),
        );
        $this->response->success($results);
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

        if(!$this->appConfig->get('development')){
            $this->response->put('Use development mode');
            $this->response->send();
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->appConfig->get('wwwPath');

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