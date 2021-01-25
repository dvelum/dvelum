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
use Dvelum\App\Backend\Designer\Import;
use Dvelum\App\Controller\Event;
use Dvelum\App\Controller\EventManager;
use Dvelum\App\Session\User;
use Dvelum\Config;
use Dvelum\Service;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Orm;
use Dvelum\Orm\RecordInterface;
use Dvelum\App\Data\Api;

class DataView extends ApiController
{
    /**
     * @var Orm\Service $ormService
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

        if (!$dataObject || !$this->ormService->configExists($dataObject)) {
            return 'Orm';
        }
        return implode('_', array_map('ucfirst', explode('_', $dataObject)));
    }

    public function initListeners()
    {
        parent::initListeners();
        $this->eventManager->on(EventManager::AFTER_LIST, [$this, 'prepareList']);
    }

    /**
     * Get pages list as array
     * @param Event $event
     * @return void
     */
    public function prepareList(Event $event): void
    {
        $data = &$event->getData()->data;

        if (empty($data)) {
            return;
        }

        $object = $this->request->post('d_object', 'string', null);
        $config = $this->ormService->config($object);
        $fields = $config->getFields();

        foreach ($data as &$row) {
            foreach ($fields as $item) {
                if ($item->isText()) {
                    $row[$item->getName()] = '[text]';
                }
            }
        }
    }

    public function shardListAction()
    {
        $distributed = Orm\Distributed::factory();
        $shards = $distributed->getShards();
        $list = [];
        foreach ($shards as $item) {
            $list[] = ['id' => $item['id']];
        }
        $this->response->success($list);
    }

    public function viewConfigAction()
    {
        $object = $this->request->post('d_object', 'string', false);
        $shard = $this->request->post('shard', 'string', '');

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $objectConfig = $this->ormService->config($object);
        $title = $objectConfig->getTitle();


        $fields = $objectConfig->getFields();
        $fieldsCfg = [];
        $columns = [];
        $systemColumns = [];
        $searchFields = $objectConfig->getSearchFields();
        $selectShard = false;
        $findBucket = false;
        $canEditObject = true;
        $shardField = false;

        if (empty($shard) && $objectConfig->isDistributed()) {
            $shadrdingType = $objectConfig->getShardingType();
            $title .= ' INDEX';
            $fields = Orm\Record\Config::factory($objectConfig->getDistributedIndexObject())->getFields();
            if ($shadrdingType == Orm\Record\Config::SHARDING_TYPE_VIRTUAL_BUCKET) {
                $fieldName = $objectConfig->getBucketMapperKey();
                if(empty($fieldName)){
                    $this->response->error($this->lang->get('CANT_EXEC'));
                    return;
                }
                $findBucket = [
                    'field' => $objectConfig->getField($fieldName)->getTitle(),
                ];
                $canEditObject = false;
            }
            $selectShard = true;
            if ($shadrdingType == Orm\Record\Config::SHARDING_TYPE_KEY_NO_INDEX) {
                $canEditObject = false;
            }
        }

        if ($objectConfig->isShardRequired()) {
            $shardField = Orm\Distributed::factory()->getShardField();
        } else {
            $shardField = false;
        }

        foreach ($fields as $name => $field) {
            $fieldCfg = new \stdClass();
            $fieldCfg->name = $name;

            $col = new \stdClass();
            $col->dataIndex = $name;
            $col->text = $field->getTitle();

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

            if ($field->isSystem()) {
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
            'searchFields' => $searchFields,
            'selectShard' => $selectShard,
            'findBucket' => $findBucket,
            'title' => $title,
            'canEditObject' => $canEditObject,
            'shardField' => $shardField
        );

        $this->response->success($result);
    }

    public function listAction()
    {
        $object = $this->request->post('d_object', 'string', null);

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $config = $this->ormService->config($object);
        $fields = $config->getFields();

        foreach ($fields as $item) {
            if ($item->isLink()) {
                $this->listLinks[] = $item->getName();
            }
        }
        parent::listAction();
    }

    public function editorConfigAction()
    {
        $object = $this->request->post('d_object', 'string', null);

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $objectConfig = $this->ormService->config($object);
        $designerConfig = Config::storage()->get('designer.php');

        $data = [];
        $tabs = [];

        $tab = \Ext_Factory::object('Panel');
        $tab->setName('_generalTab');
        $tab->setValues([
            'frame' => false,
            'border' => false,
            'layout' => 'anchor',
            'bodyPadding' => 3,
            'scrollable' => true,
            'bodyCls' => 'formBody',
            'anchor' => '100%',
            'title' => $this->lang->get('GENERAL'),
            'fieldDefaults' => "{labelAlign: \"right\",labelWidth: 160,anchor: \"100%\"}"
        ]);

        $tabs[] = $tab;
        $related = [];

        $objectFieldList = array_keys($objectConfig->getFieldsConfig($objectConfig->isDistributed()));

        $readOnly = $objectConfig->isReadOnly();

        foreach ($objectFieldList as $field) {
            if (is_string($field) && $field == $objectConfig->getPrimaryKey()) {
                continue;
            }

            if ($objectConfig->isDistributed()) {
                // distributed fields fills automatically
                if (in_array($field, array_keys($objectConfig->getDistributedFields()))) {
                    if ($field != $objectConfig->getShardingKey()) {
                        continue;
                    }
                }
            }

            $fieldCfg = $objectConfig->getFieldConfig($field);

            $fieldObj = $objectConfig->getField($field);

            if ($fieldObj->isMultiLink()) {
                $linkedObject = $fieldObj->getLinkedObject();
                if(empty($linkedObject)){
                    $this->response->error($this->lang->get('CANT_EXEC'));
                    return;
                }
                $linkedCfg = $this->ormService->config($linkedObject);
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
                $newField = Import::convertOrmFieldToExtField($field, $fieldCfg);

                if ($newField !== false) {
                    $newField->setName($field);

                    if ($readOnly && $newField->getConfig()->isValidProperty('readOnly')) {
                        $newField->set('readOnly', true);
                    }

                    if ($designerConfig->get('html_editor') && $fieldObj->isText() && $fieldObj->isHtml()) {
                        $tabs[] = $newField->__toString();
                    } else {
                        $data[] = $newField->__toString();
                    }
                }
            }
        }

        $tab->set('items', '[' . implode(',', $data) . ']');
        $shardKey = 'shard';

        if ($objectConfig->isShardRequired()) {
            $shardKey = Orm\Distributed::factory()->getShardField();
        }

        $mapKey = null;
        if ($objectConfig->isDistributed()) {
            $mapKey = $objectConfig->getBucketMapperKey();
            if (empty($mapKey)) {
                $mapKey = $objectConfig->getShardingKey();
            }
        }
        $this->response->success([
            'related' => $related,
            'fields' => str_replace(["\n", "\t"], '', '[' . implode(',', $tabs) . ']'),
            'readOnly' => intval($readOnly),
            'primaryKey' => $objectConfig->getPrimaryKey(),
            'shardKey' => $shardKey,
            'readOnlyAfterCreate' => [$mapKey]
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

    public function objectTitleAction(): void
    {
        $object = $this->request->post('object', 'string', null);

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $this->canViewObjects[] = $object;

        parent::objectTitleAction();
    }

    public function findBucketAction()
    {
        $object = $this->request->post('d_object', 'string', null);

        if (!$object || !$this->ormService->configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $value = $this->request->post('value', 'string', '');
        if (empty($value)) {
            $this->response->success(['bucket' => null]);
        }

        $objectConfig = $this->ormService->config($object);
        $mapField = $objectConfig->getBucketMapperKey();

        if(empty($mapField)){
            $this->response->error($this->lang->get('CANT_EXEC'));
            return;
        }
        $field = $objectConfig->getField($mapField);

        /**
         * @var Orm\Distributed\Key\Strategy\VirtualBucket $keyGen ;
         */
        $keyGen = Orm\Distributed::factory()->getKeyGenerator($object);
        if ($field->isNumeric()) {
            $bucket = $keyGen->getNumericMapper()->keyToBucket((int)$value);
        } else {
            $bucket = $keyGen->getStringMapper()->keyToBucket((string)$value);
        }
        if (!empty($bucket)) {
            $bucket = $bucket->getId();
        } else {
            $bucket = null;
        }
        $this->response->success(['bucket' => $bucket]);
    }

    /**
     * @param Api\Request $request
     * @param User $user
     * @return Api
     */
    protected function getApi(Api\Request $request, User $user): Api
    {
        $api = parent::getApi($request, $user);
        $api->setUseApproximateCount(true);
        return $api;
    }
}