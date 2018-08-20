<?php
use Coroq\Input\Email;

class EmailTest extends PHPUnit_Framework_TestCase {
  public function testFilter() {
    $input = (new Email())->setValue(" ＴＥＳＴ＠example.com ");
    $this->assertSame("TEST@example.com", $input->getValue());
  }
  
  public function testValidate() {
    $input = new Email();
    $input->setValue("valid@example.com")->validate();
    $this->assertNull($input->getError());
    $input->setValue("invalid..@example.com")->validate();
    $this->assertSame("err_invalid", $input->getError()->code);
  }
}
