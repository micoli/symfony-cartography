<?php

declare(strict_types=1);

namespace App\Tests\TestApplication\Utils;

use App\Infrastructure\Utils\Validator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ValidatorTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testValidateUsername(): void
    {
        $test = 'username';

        self::assertSame($test, $this->validator->validateUsername($test));
    }

    public function testValidateUsernameEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The username can not be empty.');
        $this->validator->validateUsername(null);
    }

    public function testValidateUsernameInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The username must contain only lowercase latin characters and underscores.');
        $this->validator->validateUsername('INVALID');
    }

    public function testValidatePassword(): void
    {
        $test = 'password';

        self::assertSame($test, $this->validator->validatePassword($test));
    }

    public function testValidatePasswordEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The password can not be empty.');
        $this->validator->validatePassword(null);
    }

    public function testValidatePasswordInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The password must be at least 6 characters long.');
        $this->validator->validatePassword('12345');
    }

    public function testValidateEmail(): void
    {
        $test = '@';

        self::assertSame($test, $this->validator->validateEmail($test));
    }

    public function testValidateEmailEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The email can not be empty.');
        $this->validator->validateEmail(null);
    }

    public function testValidateEmailInvalid(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The email should look like a real email.');
        $this->validator->validateEmail('invalid');
    }

    public function testValidateFullName(): void
    {
        $test = 'Full Name';

        self::assertSame($test, $this->validator->validateFullName($test));
    }

    public function testValidateFullNameEmpty(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The full name can not be empty.');
        $this->validator->validateFullName(null);
    }
}
