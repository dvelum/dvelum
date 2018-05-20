<?php
/**
 *  DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
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
 *
 */
declare(strict_types=1);

namespace Dvelum\App\Frontend;

use Dvelum\{
    App, Config, Config\ConfigInterface, Lang, Request, Response, Service, Orm\Model, Resource, View
};

class Controller extends App\Controller
{
    /**
     * @var ConfigInterface
     */
    protected $frontendConfig;
    /**
     * @var Lang
     */
    protected $lang;

    public function __construct(Request $request, Response $response)
    {
        $this->frontendConfig = Config::storage()->get('frontend.php');
        $this->lang = Lang::lang();
        parent::__construct($request, $response);
    }

    /**
     * Show Page.
     * Running this method initiates rendering of templates and sending of HTML
     * data.
     * @return void
     */
    public function showPage(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $vers = $this->request->get('vers', 'int', false);

        $page = \Page::getInstance();
        $page->setTemplatesPath('public/');

        /**
         * @var BlockManager $blockManager
         */
        $blockManager = Service::get('blockManager');

        if ($vers) {
            $blockManager->disableCache();
        }

        if ($page->show_blocks) {
            $blockManager->init($page->id, $page->default_blocks, $vers);
        }

        $layoutPath = $page->getThemePath() . 'layout.php';
        $this->render($layoutPath, [
            'development' => $this->appConfig->get('development'),
            'page' => $page,
            'path' => $page->getThemePath(),
            'blockManager' => $blockManager,
            'resource' => Resource::factory(),
            'pagesTree' => Model::factory('Page')->getTree()
        ], false);
    }
}