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

namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\App\Backend\Api\Controller as ApiController;
use Dvelum\Service;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Orm;
use Dvelum\Orm\RecordInterface;

class DataView extends ApiController
{
    /**
     * @var Orm $ormService
     */
    protected $ormService;

    public function __construct(Request $request, Response $response)
    {
        $this->ormService = Service::get('orm');
        parent::__construct($request, $response);
    }


    public function getModule(): string
    {
        return 'Orm';
    }

    public function getObjectName(): string
    {
        $dataObject = $this->request->post('d_object', 'string', false);

        if(!$dataObject || !$this->ormService->configExists($dataObject)){
           return 'Orm';
        }
        return ucfirst($dataObject);
    }

    public function viewConfigAction()
    {
        $object = $this->request->post('d_object', 'string', false);

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $cfg = $this->ormService->config($object);

        $fields = $cfg->getFieldsConfig(true);
        $fieldsCfg = [];
        $columns = [];
        $systemColumns = [];
        $searchFields = $cfg->getSearchFields();

        foreach ($fields as $name => $itemCfg)
        {
            $fieldCfg = new \stdClass();
            $fieldCfg->name = $name;

            $col = new \stdClass();
            $col->dataIndex = $name;
            $col->text = $itemCfg['title'];

            $field = $cfg->getField($name);
            $dbType = $field->getDbType();

            if ($field->isLink()) {
                $col->align = 'left';
                $fieldCfg->type = "string";

                if ($field->isDictionaryLink()) {
                    $col->text .= ' <span style="color:green;">(' . $this->lang->get('DICTIONARY') . ')</span>';
                } elseif ($field->isMultiLink()) {
                    $col->text .= ' <span style="color:red;">(' . $this->lang->get('MULTI_LINK') . ')</span>';
                } else {
                    $col->text .= ' <span style="color:#0000FF;">(' . $this->lang->get('LINK') . ')</span>';
                }
            } elseif ($field->isNumeric()) {
                $col->align = 'right';
                if ($field->isFloat()) {
                    $fieldCfg->type = "float";
                    $col->xtype = "numbercolumn";
                } else {
                    $fieldCfg->type = "integer";
                }
            } elseif ($field->isBoolean()) {
                $fieldCfg->type = "boolean";
                $col->xtype = "booleancolumn";
                $col->align = 'center';
            } elseif ($field->isText()) {
                $col->align = 'left';
                $fieldCfg->type = "string";
            } elseif ($dbType == 'date' || $dbType == 'datetime') {
                $col->xtype = 'datecolumn';
                $fieldCfg->type = "date";
                if ($dbType == 'date') {
                    $col->format = 'd.m.Y';
                    $fieldCfg->dateFormat = 'Y-m-d';
                } else {
                    $col->format = 'd.m.Y H:i:s';
                    $fieldCfg->dateFormat = 'Y-m-d H:i:s';
                }
            } else {
                $col->align = 'left';
                $fieldCfg->type = "string";
            }

            if ($field->isSearch()) {
                $col->text = '<img data-qtip="' . $this->lang->get('SEARCH') . '" src="' . $this->appConfig->get('wwwroot') . 'i/system/search.png" height="10"/> ' . $col->text;
            }

            if ($field->isMultiLink()) {
                $col->sortable = false;
            }

            if (isset($itemCfg['system']) && $itemCfg['system']) {
                $systemColumns[] = $col;
            } else {
                $columns[] = $col;
            }

            $fieldsCfg[] = $fieldCfg;
        }

        $columns = array_merge($systemColumns, $columns);

        $result = array(
            'fields' => $fieldsCfg,
            'columns' => $columns,
            'searchFields' => $searchFields
        );

        $this->response->success($result);
    }

    public function listAction()
    {
        $object = $this->request->post('d_object', 'string', null);

        if(!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $config = $this->ormService->config($object);
        $fields = $config->getFields();

        foreach ($fields as $item)
        {
            if($item->isLink()){
                $this->listLinks[] = $item->getName();
            }
        }

        parent::listAction();
    }

    public function editorConfigAction()
    {
        $object = $this->request->post('d_object', 'string', null);

        if(!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $objectConfig = $this->ormService->config($object);

        $data = [];
        $tabs = [];

        $tab = \Ext_Factory::object('Panel');
        $tab->setName('_generalTab');
        $tab->frame = false;
        $tab->border = false;
        $tab->layout = 'anchor';
        $tab->bodyPadding = 3;
        $tab->scrollable = true;
        $tab->bodyCls = 'formBody';
        $tab->anchor = '100%';
        $tab->title = $this->lang->get('GENERAL');
        $tab->fieldDefaults = "{labelAlign: \"right\",labelWidth: 160,anchor: \"100%\"}";

        $tabs[] = $tab;
        $related = [];
        $objectFieldList = array_keys($objectConfig->getFieldsConfig(false));

        $readOnly = $objectConfig->isReadOnly();

        foreach ($objectFieldList as $field) {
            if (is_string($field) && $field == 'id') {
                continue;
            }

            $fieldCfg = $objectConfig->getFieldConfig($field);

            $fieldObj = $objectConfig->getField($field);

            if ($fieldObj->isMultiLink()) {
                $linkedObject = $fieldObj->getLinkedObject();
                $linkedCfg =  $this->ormService->config($linkedObject);
                $related[] = array(
                    'field' => $field,
                    'object' => $linkedObject,
                    'title' => $fieldCfg['title'],
                    'titleField' => $linkedCfg->getLinkTitle()
                );
            } elseif ($fieldObj->isObjectLink()) {
                if ($fieldObj->getLinkedObject() === 'medialib') {
                    $data[] = '{
									xtype:"medialibitemfield",
									resourceType:"all",
									name:"' . $field . '",
									readOnly:' . intval($readOnly) . ',
									fieldLabel:"' . $fieldCfg['title'] . '"
								}';
                } else {

                    $data[] = '
						{
							xtype:"objectfield",
							objectName:"' . $fieldObj->getLinkedObject() . '",
							controllerUrl:"' . $this->request->url([$this->appConfig->get('adminPath'), 'orm', 'dataview', '']) . '",
							fieldLabel:"' . $fieldCfg['title'] . '",
							name:"' . $field . '",
							anchor:"100%",
							readOnly:' . intval($readOnly) . ',
							isVc:' . intval($objectConfig->isRevControl()) . '
						}
					';
                }
            } else {
                $newField = \Backend_Designer_Import::convertOrmFieldToExtField($field, $fieldCfg);

                if ($newField !== false) {
                    $newField->setName($field);
                    $fieldClass = $newField->getClass();

                    if ($readOnly && $newField->getConfig()->isValidProperty('readOnly')) {
                        $newField->readOnly = true;
                    }

                    if ($fieldObj->isText() && $fieldObj->isHtml()) {
                        $tabs[] = $newField->__toString();
                    } else {
                        $data[] = $newField->__toString();
                    }
                }
            }
        }

        $tab->items = '[' . implode(',', $data) . ']';

        $this->response->success([
            'related' => $related,
            'fields' => str_replace(["\n", "\t"], '', '[' . implode(',', $tabs) . ']'),
            'readOnly' => intval($readOnly),
            'primaryKey' => $objectConfig->getPrimaryKey()
        ]);
    }

    public function checkCanEdit(): bool
    {
        return true;
    }

    public function checkCanDelete(): bool
    {
        return true;
    }

    public function checkCanPublish(): bool
    {
        return true;
    }

    public function checkOwner(RecordInterface $object): bool
    {
        return true;
    }

    public function objectTitleAction() : void
    {
       $object = $this->request->post('object', 'string', null);

       if(!$object || !$this->ormService->configExists($object)) {
           $this->response->error($this->lang->get('WRONG_REQUEST'));
           return;
       }

       $this->canViewObjects[] = $object;

       parent::objectTitleAction();
    }
}