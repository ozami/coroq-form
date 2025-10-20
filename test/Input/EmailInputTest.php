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
}
