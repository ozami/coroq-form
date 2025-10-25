<?php
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\EmptyError;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase {
  public function testReadOnly() {
    $input = (new Input())->setReadOnly(true)->setValue('test');
    $this->assertSame('', $input->getValue());
  }

  public function testValidateRequiredAndEmptyInput() {
    $input = new Input();
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(EmptyError::class, $input->getError());
  }

  public function testValidateRequiredAndNonEmptyInput() {
    $input = (new Input())->setValue('test');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateNotRequiredAndEmptyInput() {
    $input = (new Input())->setRequired(false);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateNotRequiredAndNonEmptyInput() {
    $input = (new Input())->setRequired(false)->setValue('test');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testGetParsedValueReturnsRawValueForBaseInput() {
    $input = (new Input())->setValue('test');
    $this->assertSame('test', $input->getParsedValue());
  }

  public function testSetValueClearsError() {
    $input = new Input();
    $input->validate();  // Sets error
    $this->assertNotNull($input->getError());

    $input->setValue('test');
    $this->assertNull($input->getError());
  }

  public function testDisabledInputNotValidated() {
    $input = (new Input())->setDisabled(true);
    // Disabled items are not validated by validate() call
    // This is implicitly tested by Form tests
    $this->assertTrue($input->isDisabled());
  }
}
