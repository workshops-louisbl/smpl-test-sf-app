<?php

namespace App\Tests\Utils;

use App\Utils\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidateFullName()
    {
        $expected = "Full Name";
        $result = $this->validator->validateFullName($expected);

        $this->assertSame($expected, $result);
    }

    public function testValidateFullNameEmpty()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The full name can not be empty');

        $this->validator->validateFullName("");
    }

}
