<?php
use Coroq\Form\Input\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase {
  public function testFilter() {
    $input = (new Email())->setValue(" ＴＥＳＴ＠example.com ");
    $this->assertSame("TEST@example.com", $input->getValue());
  }

  public function testLowerCaseDomain() {
    $input = new Email();
    $input->setValue("TEST@EXAMPLE.COM");
    $this->assertSame("TEST@example.com", $input->getValue());
    $input->setValue("TEST-EXAMPLE.COM");
    $this->assertSame("TEST-EXAMPLE.COM", $input->getValue());
    $input->setValue("TEST@TEST@EXAMPLE.COM");
    $this->assertSame("TEST@TEST@example.com", $input->getValue());
  }

  public function testLowerCaseDomainDisabled() {
    $input = new Email();
    $input->setLowercaseDomain(false);
    $input->setValue("TEST@EXAMPLE.COM");
    $this->assertSame("TEST@EXAMPLE.COM", $input->getValue());
  }

  public function testValidate() {
    $input = new Email();
    $input->setValue("valid@example.com")->validate();
    $this->assertNull($input->getError());
    $input->setValue("invalid..@example.com")->validate();
    $this->assertSame("err_invalid", $input->getError()->code);
  }
}
