<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\App\Backend\Medialib;

use Dvelum\App\Backend;
use Dvelum\Orm\Model;
use Dvelum\App\Controller\EventManager;
use Dvelum\App\Controller\Event;
use Dvelum\Response;
use Dvelum\Utils;

class Controller extends Backend\Ui\Controller
{
    public function getModule(): string
    {
        return 'Medialib';
    }

    public function getObjectName(): string
    {
        return 'Medialib';
    }

    public function initListeners()
    {
        $apiRequest = $this->apiRequest;
        $apiRequest->setObjectName($this->getObjectName());

        $this->eventManager->on(EventManager::BEFORE_LIST, function (Event $event) use ($apiRequest) {
            $category = $apiRequest->getFilter('category');
            if (empty($category)) {
                $apiRequest->addFilter('category', null);
            }
        });
        $this->eventManager->on(EventManager::AFTER_LIST, [$this, 'prepareList']);
    }

    public function prepareList(Event $event)
    {
        $data = &$event->getData()->data;

        $wwwRoot = $this->appConfig->get('wwwRoot');

        if (!empty($data)) {
            foreach ($data as &$v) {
                if ($v['type'] == 'image') {
                    $v['srcpath'] = \Model_Medialib::addWebRoot(str_replace($v['ext'], '', $v['path']));
                    $v['thumbnail'] = \Model_Medialib::getImgPath($v['path'], $v['ext'], 'thumbnail', true);
                    $v['icon'] = \Model_Medialib::getImgPath($v['path'], $v['ext'], 'icon', true);
                } else {
                    $v['icon'] = $wwwRoot . 'i/unknown.png';
                    $v['thumbnail'] = $wwwRoot . 'i/unknown.png';
                    $v['srcpath'] = '';
                }
                $v['path'] = \Model_Medialib::addWebRoot($v['path']);
            }
            unset($v);
        }
    }

    /**
     * Upload images to media library
     */
    public function uploadAction()
    {
        $uploadCategory = $this->request->getPart(3);

        if (!$uploadCategory) {
            $uploadCategory = null;
        }

        if (!$this->checkCanEdit()) {
            return;
        }

        $docRoot = $this->appConfig->get('wwwPath');
        /**
         * @var \Model_Medialib $mediaModel
         */
        $mediaModel = Model::factory('Medialib');
        $mediaCfg = $mediaModel->getConfig();

        $path = $this->appConfig->get('uploads') . date('Y') . '/' . date('m') . '/' . date('d') . '/';

        if (!is_dir($path) && !@mkdir($path, 0775, true)) {
            $this->response->error($this->lang->get('CANT_WRITE_FS'));
        }

        $files = $this->request->files();

        $uploader = new \Upload($mediaCfg->__toArray());

        if (empty($files)) {
            $this->response->error($this->lang->get('NOT_UPLOADED'));
            return;
        }

        $uploaded = $uploader->start($files, $path);

        if (empty($uploaded)) {
            $this->response->error($this->lang->get('NOT_UPLOADED'));
            return;
        }

        $data = [];

        foreach ($uploaded as &$v) {
            $path = str_replace($docRoot, '/', $v['path']);

            $id = $mediaModel->addItem($v['title'], $path, $v['size'], $v['type'], $v['ext'], $uploadCategory);

            $item = Model::factory('Medialib')->getItem($id);

            if ($item['type'] == 'image') {
                $item['srcpath'] = \Model_Medialib::addWebRoot(str_replace($item['ext'], '', $item['path']));
            } else {
                $item['srcPath'] = '';
            }

            $item['thumbnail'] = \Model_Medialib::getImgPath($item['path'], $item['ext'], 'thumbnail', true);
            $item['icon'] = \Model_Medialib::getImgPath($item['path'], $item['ext'], 'icon', true);
            $item['path'] = \Model_Medialib::addWebRoot($item['path']);

            $data[] = $item;
        }
        $this->response->setFormat(Response::FORMAT_JSON);
        $this->response->success($data);
    }

    /**
     * Crop image
     */
    public function cropAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $id = $this->request->post('id', 'integer', false);
        $x = $this->request->post('x', 'integer', false);
        $y = $this->request->post('y', 'integer', false);
        $w = $this->request->post('w', 'integer', false);
        $h = $this->request->post('h', 'integer', false);
        $type = $this->request->post('type', 'string', false);

        if (!$id || !$w || !$h || !$type) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        /**
         * @var \Model_Medialib $mediaModel
         */
        $mediaModel = Model::factory('Medialib');
        $item = $mediaModel->getItem($id);

        if (!$item) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        if ($mediaModel->cropAndResize($item, $x, $y, $w, $h, $type)) {
            $mediaModel->updateModifyDate($id);
            $mediaModel->markCroped($id);
            $this->response->success();
        } else {
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Remove image
     */
    public function removeAction()
    {
        if (!$this->checkCanDelete()) {
            return;
        }

        $id = $this->request->post('id', 'integer', false);

        if (!$id) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $media = Model::factory('Medialib');
        if ($media->remove($id)) {
            $this->response->success();
        } else {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }
    }

    /**
     * Update image info
     */
    public function updateAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $id = $this->request->post('id', 'integer', false);

        if (!$id) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $fields = ['title', 'alttext', 'caption', 'description'];
        $data = [];

        foreach ($fields as $v) {
            if ($v == 'caption') {
                $data[$v] = $this->request->post($v, 'raw', '');
            } elseif ($v == 'category') {
                $data[$v] = $this->request->post($v, 'integer', null);
            } else {
                $data[$v] = $this->request->post($v, 'string', '');
            }
        }

        if (!strlen($data['title'])) {
            $this->response->error($this->lang->get('FILL_FORM'), array('title' => $this->lang->get('CANT_BE_EMPTY')));
        }

        /**
         * @var \Model_Medialib $media
         */
        $media = Model::factory('Medialib');

        if ($media->update($id, $data)) {
            $this->response->success();
        } else {
            $this->response->error($this->lang->get('CANT_EXEC'));
        }
    }

    /**
     * Get item data
     */
    public function getitemAction()
    {
        $id = $this->request->post('id', 'integer', false);

        if (!$id) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $item = Model::factory('Medialib')->getItem($id);

        if ($item['type'] == 'image') {
            $item['srcpath'] = \Model_Medialib::addWebRoot(str_replace($item['ext'], '', $item['path']));
        } else {
            $item['srcPath'] = '';
        }

        $item['thumbnail'] = \Model_Medialib::getImgPath($item['path'], $item['ext'], 'thumbnail', true);
        $item['icon'] = \Model_Medialib::getImgPath($item['path'], $item['ext'], 'icon', true);
        $item['path'] = \Model_Medialib::addWebRoot($item['path']);

        $this->response->success($item);
    }

    /**
     * Get item info for media item field
     */
    public function infoAction()
    {
        $id = $this->request->post('id', 'integer', false);

        if (!$id) {
            $this->response->success(['exists' => false]);
            return;
        }

        $item = Model::factory('Medialib')->getItem($id);

        if (empty($item)) {
            $this->response->success(array('exists' => false));
        }

        if ($item['type'] == 'image') {
            $stamp = 1;

            if (!empty($item['modified'])) {
                $stamp = date('ymdhis', strtotime($item['modified']));
            }

            $icon = \Model_Medialib::getImgPath($item['path'], $item['ext'], 'thumbnail', true) . '?m=' . $stamp;

        } else {
            $icon = $this->appConfig->get('wwwroot') . 'i/unknown.png';
        }

        $this->response->success([
            'exists' => true,
            'type' => $item['type'],
            'icon' => $icon,
            'title' => $item['title'],
            'size' => $item['size'] . ' Mb'
        ]);
    }

    /**
     * Get access permissions for current user
     */
    public function rightsAction()
    {
        $results = array(
            'canEdit' => $this->moduleAcl->canEdit($this->module),
            'canDelete' => $this->moduleAcl->canDelete($this->module),
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

        if (!$this->appConfig->get('development')) {
            $this->response->put('Use development mode');
            $this->response->send();
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->appConfig->get('wwwPath');

        foreach ($sources as $filePath) {
            $s .= file_get_contents($wwwPath . '/' . $filePath) . "\n";
            $totalSize += filesize($wwwPath . '/' . $filePath);
        }

        $time = microtime(true);
        file_put_contents($wwwPath . '/js/app/system/Medialib.js', \Dvelum\App\Code\Minify\Minify::factory()->minifyJs($s));
        echo '
      			Compilation time: ' . number_format(microtime(true) - $time, 5) . ' sec<br>
      			Files compiled: ' . sizeof($sources) . ' <br>
      			Total size: ' . Utils::formatFileSize($totalSize) . '<br>
      			Compiled File size: ' . Utils::formatFileSize(filesize($wwwPath . '/js/app/system/Medialib.js')) . ' <br>
      		';
        exit;
    }
}