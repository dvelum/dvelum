<?php
use PHPUnit\Framework\TestCase;

class Validator_LoginTest extends TestCase
{
    public function testValidate()
    {
        $this->assertTrue(Validator_Login::validate('1my@login-._'));
        $this->assertFalse(Validator_Login::validate('mylogin  |'));
    }
}