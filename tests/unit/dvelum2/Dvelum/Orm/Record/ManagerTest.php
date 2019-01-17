<?php

use PHPUnit\Framework\TestCase;

class ManagerTestTest extends TestCase
{
    public function testGetRegisteredObjects()
    {
        $manager = new \Dvelum\Orm\Record\Manager();
        $objects = $manager->getRegisteredObjects();
        $this->assertTrue(is_array($objects));
        $this->assertTrue(in_array('page', $objects, true));
    }

    public function testObjectExists()
    {
        $manager = new \Dvelum\Orm\Record\Manager();
        $this->assertTrue($manager->objectExists('user'));
        $this->assertFalse($manager->objectExists('user_0123'));
    }
}