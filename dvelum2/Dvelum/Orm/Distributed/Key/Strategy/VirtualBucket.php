<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2018  Kirill Yegorov
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

namespace Dvelum\Orm\Distributed\Key\Strategy;

use Dvelum\Orm\Distributed\Key\GeneratorInterface;

use Dvelum\Config\ConfigInterface;
use Dvelum\Orm\Distributed\Key\Reserved;
use Dvelum\Orm\Distributed\Key\Strategy\VirtualBucket\MapperInterface;
use Dvelum\Orm\Record;
use Dvelum\Orm\RecordInterface;

class VirtualBucket extends UserKeyNoID
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;
    protected $options;
    protected $bucketField;
    protected $exceptIndexPrimaryKey = false;

    /**
     * @var MapperInterface $numericMapper
     */
    protected $numericMapper = null;
    /**
     * @var MapperInterface $stringMapper
     */
    protected $stringMapper = null;

    public function __construct(ConfigInterface $config)
    {
        parent::__construct($config);
        $this->bucketField = $config->get('bucket_field');
    }

    /**
     * @return MapperInterface
     * @throws \Exception
     */
    public function getNumericMapper():MapperInterface
    {
        if(empty($this->numericMapper)){
            $numericAdapter = $this->config->get('keyToBucket')['number'];
            $this->numericMapper =   new $numericAdapter();
        }
        return $this->numericMapper;
    }

    /**
     * @return MapperInterface
     * @throws \Exception
     */
    public function getStringMapper():MapperInterface
    {
        if(empty($this->stringMapper)){
            $numericAdapter = $this->config->get('keyToBucket')['string'];
            $this->stringMapper =   new $numericAdapter();
        }
        return $this->stringMapper;
    }

    /**
     * Reserve
     * @param RecordInterface $object
     * @param array $keyData
     * @return Reserved|null
     */
    public function reserveKey(RecordInterface $object, array $keyData): ?Reserved
    {
        $config = $object->getConfig();
        $keyField = $config->getBucketMapperKey();

        $fieldObject = $config->getField($keyField);

        $bucket = null;


        if ($fieldObject->isNumeric()) {
            $bucket = $this->getNumericMapper()->keyToBucket($object->get($keyField));
        } elseif ($fieldObject->isText(true)) {
            $bucket = $this->getStringMapper()->keyToBucket($object->get($keyField));
        }

        if (empty($bucket)) {
            return null;
        }

        $keyData[$this->bucketField] = $bucket->getId();

        unset($keyData[$config->getPrimaryKey()]);
        $result = parent::reserveKey($object, $keyData);

        if (!empty($result)) {
            $result->setBucket($bucket->getId());
        }
        return $result;
    }
}