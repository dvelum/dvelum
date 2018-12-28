<?php
use PHPUnit\Framework\TestCase;

class Validator_PagecodeTest extends TestCase
{
    public function testValidate()
    {
        $this->assertTrue(Validator_Pagecode::validate('mypage-1_'));
        $this->assertFalse(Validator_Pagecode::validate('mypage@'));
    }
}