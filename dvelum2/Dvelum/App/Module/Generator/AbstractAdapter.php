<?php
/**
 * DVelum project http://code.google.com/p/dvelum/ , https://github.com/k-samuel/dvelum , http://dvelum.net
 * Copyright (C) 2011-2017  Kirill Yegorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\App\Module\Generator;

use Dvelum\Config\ConfigInterface;
use Dvelum\Request;
use Dvelum\Lang;
use Dvelum\View;

abstract class AbstractAdapter implements GeneratorInterface
{
    /**
     * @var ConfigInterface
     */
    protected $designerConfig;
    /**
     * @var ConfigInterface
     */
    protected $appConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Request
     */
    protected $request;
    /**
     * AbstractAdapter constructor.
     * @param ConfigInterface $appConfig
     * @param ConfigInterface $designerConfig
     */
    public function __construct(ConfigInterface $appConfig, ConfigInterface $designerConfig, ConfigInterface $generatorConfig)
    {
        $this->appConfig = $appConfig;
        $this->designerConfig = $designerConfig;
        $this->config = $generatorConfig;
        $this->request = Request::factory();
    }

    /**
     * Create controller file
     * @param string $dir - controller dirrectory
     * @param string $content - file content
     * @throws \Exception
     * @return string file path
     */
    protected function createControllerFile($dir , $content)
    {
        if(file_exists($dir)){
            if(!is_dir($dir))
                throw new \Exception('Invalid controller dir');
        }else{
            if(!@mkdir($dir , $this->config->get('create_directory_mode') , true))
                throw new \Exception(Lang::lang()->get('CANT_WRITE_FS') . ' ' . $dir);
        }

        if(!@file_put_contents($dir . '/' . 'Controller.php' ,  $content))
            throw new \Exception('Cant create Controller');

        @chmod($dir . '/' . 'Controller.php', $this->config->get('create_file_mode'));

        return $dir . '/' . 'Controller.php';
    }

    public function addGridMethods(\Designer_Project $project ,  \Ext_Object $grid , $object, $vc = false)
    {
        $methodsManager =  $project->getMethodManager();

        // initComponent
        $initTemplate = View::factory();
        $m = $methodsManager->addMethod($grid->getName() ,
            'initComponent' ,
            [],
            $initTemplate->render('generator/simple/initComponent.php')
        );

        $urlTemplates =  $this->designerConfig->get('templates');
        $deleteUrl = $this->request->url([$urlTemplates['adminpath'] ,  $object , 'delete']);

        // deleteRecord
        $deleteTemplate = View::factory();
        $deleteTemplate->setData([
            'deleteUrl' => $deleteUrl
        ]);
        $m = $methodsManager->addMethod(
            $grid->getName() ,
            'deleteRecord' ,
            [['name'=>'record','type'=>'Ext.data.record']] ,
            $deleteTemplate->render('generator/simple/deleteRecord.php')
        );
        $m->setDescription('Delete record');

        // setCanEdit
        $setCanEditTemplate = View::factory();
        $m = $methodsManager->addMethod(
            $grid->getName(),
            'setCanEdit' ,
            [['name'=>'canEdit','type'=>'boolean']] ,
            $setCanEditTemplate->render('generator/simple/setCanEdit.php')
        );
        $m->setDescription('Set edit permission');

        // setCanDelete
        $m = $methodsManager->addMethod($grid->getName(), 'setCanDelete' , [['name'=>'canDelete','type'=>'boolean']], ' this.canDelete = canDelete;');
        $m->setDescription('Set delete permission');

        // setCanPublish
        if($vc){
            $m = $methodsManager->addMethod($grid->getName(), 'setCanPublish' , [['name'=>'canPublish','type'=>'boolean']] , 'this.canPublish = canPublish;');
            $m->setDescription('Set publish permission');
        }

        // showEditWindow
        $editTemplate = View::factory();
        $editTemplate->setData([
            'namespace'=>$project->namespace,
            'vc' => $vc
        ]);
        $m = $methodsManager->addMethod(
            $grid->getName(),
            'showEditWindow' ,
            [['name'=>'id','type'=>'integer']] ,
            $editTemplate->render('generator/simple/showEditWindow.php')
        );
        $m->setDescription('Show editor window');
    }

    /**
     * Create actionJs code for designer project
     * @param string $object
     * @param string $runNamespace
     * @param string $classNamespace
     * @param boolean $vc - use version control
     * @return string
     * @throws \Exception
     */
    protected function createActionJS($object, $runNamespace , $classNamespace , $vc = false)
    {
        $actionTemplate = View::factory();
        $actionTemplate->setData([
            'object'=>$object,
            'runNamespace'=>$runNamespace,
            'classNamespace'=>$classNamespace,
            'vc'=>$vc
        ]);
        return $actionTemplate->render('generator/simple/actionJS.php');
    }
}