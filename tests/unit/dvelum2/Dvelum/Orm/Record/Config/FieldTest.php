<?php

use PHPUnit\Framework\TestCase;


class FieldTest extends TestCase
{
    public function testProperties()
    {
        $config = \Dvelum\Orm\Record\Config::factory('Page');
        $field = $config->getField('id');
        $this->assertTrue($field->isSystem());
        $this->assertTrue($field->isSearch());
        $this->assertTrue($field->isNumeric());
        $this->assertTrue($field->isInteger());
        $this->assertTrue($field->isUnsigned());
        $this->assertTrue($field->isUnique());
        $this->assertTrue($config->getField('code')->isSearch());
        $this->assertEquals('id' , $field->getName());
        $this->assertEquals('bigint' , $field->getDbType());


        $authorField = $config->getField('author_id');
        $this->assertEquals('user' , $authorField->getLinkedObject());
        $this->assertEquals('link' , $authorField->getType());
        $this->assertTrue($authorField->isLink());
        $this->assertFalse($authorField->isDictionaryLink());
        $this->assertTrue($authorField->isObjectLink());
        $this->assertTrue($authorField->isRequired());

        $this->assertFalse($authorField->isBoolean());
        $this->assertFalse($authorField->isHtml());
        $this->assertFalse($authorField->isDateField());
        $this->assertFalse($authorField->isEncrypted());
        $this->assertFalse($authorField->isFloat());
        $this->assertFalse($authorField->isManyToManyLink());
        $this->assertFalse($authorField->isMultiLink());

        $editorField = $config->getField('editor_id');
        $this->assertTrue($editorField->isNull());
        $this->assertTrue($editorField->isNumeric());
        $this->assertFalse($editorField->isSearch());
        $this->assertFalse($editorField->isRequired());
        $this->assertFalse($editorField->hasDefault());

        $textField = $config->getField('text');
        $this->assertTrue($textField->isText());
        $this->assertTrue($textField->isHtml());
        $this->assertFalse($textField->isVirtual());
        $this->assertFalse($textField->isSystem());
    }
}