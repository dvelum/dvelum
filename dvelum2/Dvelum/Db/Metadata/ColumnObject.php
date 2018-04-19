<?php
/**
 * Created by PhpStorm.
 * User: kegorov
 * Date: 10.04.18
 * Time: 15:38
 */

namespace Dvelum\Db\Metadata;
use Zend\Db\Metadata\Object\ColumnObject as ZendColumnObject;


class ColumnObject extends ZendColumnObject
{
    /**
     *
     * @var bool
     */
    protected $autoIncrement = false;

    /**
     * Get Auto-increment flag
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * Set Auto-increment flag
     * @param bool $autoIncrement
     */
    public function setAutoIncrement(bool $autoIncrement)
    {
        $this->autoIncrement = $autoIncrement;
    }
}