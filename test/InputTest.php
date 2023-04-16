<?php
use Coroq\Form\Input;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase {
  public function testReadOnly() {
    $input = (new Input())->setReadOnly(true)->setValue("test");
    $this->assertSame("", $input->getValue());
  }

  public function testValidateRequiredAndEmptyInput() {
    $input = new Input();
    $this->assertSame(false, $input->validate());
    $this->assertEquals("err_empty", $input->getError()->code);
  }

  public function testValidateRequiredAndNonEmptyInput() {
    $input = (new Input())->setValue("test");
    $this->assertSame($input->validate(), true);
    $this->assertSame($input->getError(), null);
  }

  public function testValidateNotRequiredAndEmptyInput() {
    $input = (new Input())->setRequired(false);
    $this->assertSame($input->validate(), true);
    $this->assertSame($input->getError(), null);
  }

  public function testValidateNotRequiredAndNonEmptyInput() {
    $input = (new Input())->setRequired(false)->setValue("test");
    $this->assertSame($input->validate(), true);
    $this->assertSame($input->getError(), null);
  }
}
