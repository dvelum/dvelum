<?php
use PHPUnit\Framework\TestCase;

class Validator_AlphanumTest extends TestCase
{
    public function testValidate()
    {
        $this->assertTrue(Validator_Alphanum::validate('myName12'));
        $this->assertFalse(Validator_Alphanum::validate('myName 12 \\'));
    }
}