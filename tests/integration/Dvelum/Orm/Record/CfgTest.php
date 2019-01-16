<?php

use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Record\Builder;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;

class ConfigTest extends TestCase
{
    public function testRenameField()
    {
        $o = Builder::factory('Page');
        $cfg = Record\Config::factory('Page');

        $this->assertTrue($cfg->renameField('page_title', 'untitle'));
        $this->assertTrue($o->validate());
        $this->assertTrue($cfg->renameField('untitle', 'page_title'));
        $this->assertTrue($o->validate());
    }
}