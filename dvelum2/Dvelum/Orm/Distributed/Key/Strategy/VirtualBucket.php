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

class VirtualBucket extends UserKeyNoID
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;
    protected $shardField;
    protected $options;
    protected $bucketField;

    /**
     * @var MapperInterface $numericMapper
     */
    protected $numericMapper;
    /**
     * @var MapperInterface $stringMapper
     */
    protected $stringMapper;


    public function __construct(ConfigInterface $config)
    {
        parent::__construct($config);
        $this->bucketField = $config->get('bucket_field');
        $numericAdapter = $config->get('keyToBucket')['number'];
        $stringAdapter = $config->get('keyToBucket')['string'];
        $this->numericMapper = new $numericAdapter();
        $this->stringMapper = new $stringAdapter();
    }

    /**
     * Reserve
     * @param string $objectName
     * @param array $keyData
     * @return Reserved|null
     */
    public function reserveKey(string $objectName, array $keyData): ?Reserved
    {
        $config = Record\Config::factory($objectName);
        $keyField = $config->getBucketMapperKey();

        $fieldObject = $config->getField($keyField);

        $bucket = null;

        if ($fieldObject->isNumeric()) {
            $bucket = $this->numericMapper->keyToBucket($keyData[$keyField]);
        } elseif ($fieldObject->isText()) {
            $bucket = $this->stringMapper->keyToBucket($keyData[$keyField]);
        }

        if (empty($bucket)) {
            return null;
        }

        $keyData[$this->bucketField] = $bucket;

        $result = parent::reserveKey($objectName, $keyData);

        if (!empty($result)) {
            $result->setBucket($bucket);
        }
        return $result;
    }
}