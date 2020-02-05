<?php

use PHPUnit\Framework\TestCase;
use Dvelum\Orm\Record\Builder;
use Dvelum\Orm\Record;
use Dvelum\Orm\Model;

class CfgTest extends TestCase
{
    public function testRenameField()
    {
        $o = Builder::factory('page_rename');
        $cfg = Record\Config::factory('page_rename');

        $fieldManager = new Record\Config\FieldManager();
        $fieldManager->renameField($cfg,'page_title', 'untitle');

        $this->assertTrue($cfg->fieldExists('untitle'));
        $this->assertFalse($cfg->fieldExists('page_title'));
        $o->build();
        $this->assertTrue($o->validate());

        $fieldManager->renameField($cfg, 'untitle', 'page_title');
        $o->build();
        $this->assertTrue($o->validate());
        $this->assertFalse($cfg->fieldExists('untitle'));
        $this->assertTrue($cfg->fieldExists('page_title'));
    }
}