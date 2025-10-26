<?php
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\Error\InvalidEmailError;
use PHPUnit\Framework\TestCase;

class EmailInputTest extends TestCase {
  public function testFilter() {
    $input = (new EmailInput())->setValue(' ＴＥＳＴ＠example.com ');
    $this->assertSame('TEST@example.com', $input->getValue());
  }

  public function testLowerCaseDomain() {
    $input = new EmailInput();
    $input->setValue('TEST@EXAMPLE.COM');
    $this->assertSame('TEST@example.com', $input->getValue());

    $input->setValue('TEST-EXAMPLE.COM');
    $this->assertSame('TEST-EXAMPLE.COM', $input->getValue());

    $input->setValue('TEST@TEST@EXAMPLE.COM');
    $this->assertSame('TEST@TEST@example.com', $input->getValue());
  }

  public function testLowerCaseDomainDisabled() {
    $input = new EmailInput();
    $input->setLowerCaseDomain(false);
    $input->setValue('TEST@EXAMPLE.COM');
    $this->assertSame('TEST@EXAMPLE.COM', $input->getValue());
  }

  public function testValidate() {
    $input = new EmailInput();
    $input->setValue('valid@example.com')->validate();
    $this->assertNull($input->getError());

    $input->setValue('invalid..@example.com')->validate();
    $this->assertInstanceOf(InvalidEmailError::class, $input->getError());
  }

  public function testGetEmail() {
    $input = new EmailInput();
    $input->setValue('valid@example.com');
    $this->assertSame('valid@example.com', $input->getEmail());

    $input->setValue('');
    $this->assertNull($input->getEmail());
  }

  public function testGetParsedValue() {
    $input = (new EmailInput())->setValue('test@example.com');
    $this->assertSame('test@example.com', $input->getParsedValue());
    $this->assertSame($input->getEmail(), $input->getParsedValue());
  }

  // Additional getEmail() tests

  public function testGetEmailReturnsNullForInvalidEmail() {
    $input = (new EmailInput())->setValue('invalid-email');
    $this->assertNull($input->getEmail());
  }

  public function testGetEmailReturnsNullForInvalidEmailFormats() {
    $input = new EmailInput();

    // Missing @
    $input->setValue('notanemail');
    $this->assertNull($input->getEmail());

    // Missing domain
    $input->setValue('test@');
    $this->assertNull($input->getEmail());

    // Missing local part
    $input->setValue('@example.com');
    $this->assertNull($input->getEmail());

    // Multiple @
    $input->setValue('test@@example.com');
    $this->assertNull($input->getEmail());

    // Double dots
    $input->setValue('test..name@example.com');
    $this->assertNull($input->getEmail());

    // Spaces
    $input->setValue('test name@example.com');
    $this->assertNull($input->getEmail());
  }

  public function testGetEmailReturnsNullAfterValidationFails() {
    $input = (new EmailInput())->setValue('invalid@');
    $input->validate();
    $this->assertTrue($input->hasError());
    $this->assertNull($input->getEmail());
  }

  public function testGetParsedValueReturnsNullForInvalidEmail() {
    $input = (new EmailInput())->setValue('not-an-email');
    $this->assertNull($input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullForEmpty() {
    $input = (new EmailInput())->setValue('');
    $this->assertNull($input->getParsedValue());
  }

  public function testGetEmailWithVariousValidFormats() {
    $input = new EmailInput();

    $input->setValue('simple@example.com');
    $this->assertSame('simple@example.com', $input->getEmail());

    $input->setValue('with.dots@example.com');
    $this->assertSame('with.dots@example.com', $input->getEmail());

    $input->setValue('with+plus@example.com');
    $this->assertSame('with+plus@example.com', $input->getEmail());

    $input->setValue('with_underscore@example.com');
    $this->assertSame('with_underscore@example.com', $input->getEmail());

    $input->setValue('with-hyphen@example.com');
    $this->assertSame('with-hyphen@example.com', $input->getEmail());

    $input->setValue('123@example.com');
    $this->assertSame('123@example.com', $input->getEmail());
  }

  public function testSetLowerCaseDomainReturnsFluentInterface() {
    $input = new EmailInput();
    $result = $input->setLowerCaseDomain(false);
    $this->assertSame($input, $result);
  }

  public function testFilterWithLowerCaseDomainPreservesLocalPart() {
    $input = (new EmailInput())->setValue('TeSt@EXAMPLE.COM');
    $this->assertSame('TeSt@example.com', $input->getValue());
  }

  public function testFilterWithMultipleAtSymbols() {
    // Only the last @ is treated as domain separator
    $input = (new EmailInput())->setValue('user@host@EXAMPLE.COM');
    $this->assertSame('user@host@example.com', $input->getValue());
  }

  public function testValidateWithComplexValidEmails() {
    $input = new EmailInput();

    $validEmails = [
      'user@example.com',
      'user.name@example.com',
      'user+tag@example.co.uk',
      'user_name@example-domain.com',
      '123@example.com',
      'a@b.co',
    ];

    foreach ($validEmails as $email) {
      $input->setValue($email)->validate();
      $this->assertNull($input->getError(), "Expected $email to be valid");
      $this->assertSame($email, $input->getEmail());
    }
  }

  public function testValidateWithComplexInvalidEmails() {
    $input = new EmailInput();

    $invalidEmails = [
      'invalid',
      '@example.com',
      'user@',
      'user@@example.com',
      'user..name@example.com',
      'user name@example.com',
      'user@example',
      '',
    ];

    foreach ($invalidEmails as $email) {
      if ($email === '') {
        $input->setRequired(true);
      }
      $input->setValue($email)->validate();
      $this->assertNotNull($input->getError(), "Expected '$email' to be invalid");
      $this->assertNull($input->getEmail());
    }
  }

  public function testGetEmailAfterClear() {
    $input = (new EmailInput())->setValue('test@example.com');
    $this->assertSame('test@example.com', $input->getEmail());

    $input->clear();
    $this->assertNull($input->getEmail());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new EmailInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForValidEmail() {
    $input = (new EmailInput())->setValue('test@example.com');
    $this->assertFalse($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForInvalidEmail() {
    $input = (new EmailInput())->setValue('invalid');
    $this->assertFalse($input->isEmpty());
  }

  public function testFilterTrimsWhitespace() {
    $input = (new EmailInput())->setValue('  test@example.com  ');
    $this->assertSame('test@example.com', $input->getValue());
  }

  public function testFilterConvertsFullWidthCharacters() {
    $input = (new EmailInput())->setValue('ｔｅｓｔ＠ｅｘａｍｐｌｅ．ｃｏｍ');
    $this->assertSame('test@example.com', $input->getValue());
  }

  public function testValidateEmptyWhenRequired() {
    $input = (new EmailInput())->setRequired(true);
    $input->setValue('')->validate();
    $this->assertTrue($input->hasError());
  }

  public function testValidateEmptyWhenOptional() {
    $input = (new EmailInput())->setRequired(false);
    $input->setValue('')->validate();
    $this->assertFalse($input->hasError());
  }

  public function testSetValueClearsError() {
    $input = new EmailInput();
    $input->setValue('invalid')->validate();
    $this->assertTrue($input->hasError());

    $input->setValue('valid@example.com');
    $this->assertFalse($input->hasError());
  }

  public function testGetEmailDoesNotRevalidate() {
    // getEmail() should check validation state, not trigger validation
    $input = (new EmailInput())->setValue('invalid-email');
    $this->assertNull($input->getEmail());
    $this->assertFalse($input->hasError()); // No error until validate() is called
  }

  public function testLowerCaseDomainDefaultIsTrue() {
    $input = new EmailInput();
    $input->setValue('TEST@EXAMPLE.COM');
    $this->assertSame('TEST@example.com', $input->getValue());
  }

  public static function setUpBeforeClass(): void {
    // Configure UTF-8 replacement character for tests
    mb_substitute_character(0xFFFD);
  }

  public function testInvalidUtf8ReplacedWithReplacementCharacter() {
    $input = new EmailInput();
    $input->setValue("test\x80@example.com");

    // Invalid UTF-8 byte replaced with �
    $value = $input->getValue();
    $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
    $this->assertStringContainsString("\u{FFFD}", $value);
  }
}
