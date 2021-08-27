<?php

namespace Dvelum\App\Data;

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\App\Session\User;

class Api
{
    /**
     * @var Api\Request
     */
    protected $apiRequest;
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Model\Query
     */
    protected $dataQuery;
    /**
     * @var bool $useApproximateCount
     */
    protected $useApproximateCount = false;

    public function __construct(Api\Request $request, User $user)
    {
        $this->apiRequest = $request;
        $this->user = $user;

        $object = $this->apiRequest->getObjectName();
        $ormObjectConfig = Orm\Record\Config::factory($object);

        $model = Model::factory($object);

        if ($ormObjectConfig->isDistributed() && empty($this->apiRequest->getShard())) {
            $model = Model::factory($ormObjectConfig->getDistributedIndexObject());
        }

        $filters = $this->apiRequest->getFilters();
        $permissions = $user->getModuleAcl()->getModulePermissions($request->getObjectName());

        // Check permissions if user can see only own records
        if ($permissions && $permissions->isOnlyOwn()) {
            $filters['author_id'] = $user->getId();
        }

        $this->dataQuery = $model->query()
            ->params($this->apiRequest->getPagination())
            ->filters($this->apiRequest->getFilters())
            ->search($this->apiRequest->getQuery());

        if ($ormObjectConfig->isDistributed() && !empty($this->apiRequest->getShard())) {
            $this->dataQuery->setShard($this->apiRequest->getShard());
        }
    }

    public function getList()
    {
        if (empty($this->fields)) {
            $fields = $this->getDefaultFields();
        } else {
            $fields = $this->fields;
        }

        $object = $this->apiRequest->getObjectName();
        $ormObjectConfig = Orm\Record\Config::factory($object);
        if ($ormObjectConfig->isDistributed() && empty($this->apiRequest->getShard())) {
            $indexConfig = Orm\Record\Config::factory($ormObjectConfig->getDistributedIndexObject());
            $fields = array_keys($indexConfig->getFields());
        }
        return $this->dataQuery->fields($fields)->fetchAll();
    }

    public function getCount(): int
    {
        return $this->dataQuery->getCount($this->isUseApproximateCount());
    }

    /**
     * Set fields to be fetched
     * @param array $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * Get list of fields to be fetched
     * @return array
     */
    public function getFields(): array
    {
        if (empty($this->fields)) {
            return $this->getDefaultFields();
        }
        return $this->fields;
    }

    /**
     * Get default field list
     * @return array
     */
    protected function getDefaultFields(): array
    {
        $result = [];
        $objectName = $this->apiRequest->getObjectName();
        $config = Orm\Record\Config::factory($objectName);

        $fields = $config->getFields();
        foreach ($fields as $v) {
            if ($v->isText() || $v->isMultiLink()) {
                continue;
            }
            $result[] = $v->getName();
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isUseApproximateCount(): bool
    {
        return $this->useApproximateCount;
    }

    /**
     * @param bool $useApproximateCount
     */
    public function setUseApproximateCount(bool $useApproximateCount): void
    {
        $this->useApproximateCount = $useApproximateCount;
    }

    /**
     * @return Model\Query
     */
    public function getDataQuery(): Model\Query
    {
        return $this->dataQuery;
    }
}
