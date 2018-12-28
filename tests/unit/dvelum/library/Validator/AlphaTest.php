<?php
use PHPUnit\Framework\TestCase;

class Validator_AlphaTest extends TestCase
{
    public function testValidate()
    {
        $this->assertTrue(Validator_Alpha::validate('myName'));
        $this->assertFalse(Validator_Alpha::validate('myName 12 \\'));
    }
}