<?php
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;
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

  public function testValidatorIsCalledAfterDoValidate() {
    $validatorCalled = false;
    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertTrue($validatorCalled);
  }

  public function testValidatorReceivesFormItemAndValue() {
    $receivedFormItem = null;
    $receivedValue = null;

    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) use (&$receivedFormItem, &$receivedValue) {
        $receivedFormItem = $formItem;
        $receivedValue = $value;
        return null;
      });

    $input->validate();

    $this->assertSame($input, $receivedFormItem);
    $this->assertSame('test', $receivedValue);
  }

  public function testValidatorCanReturnError() {
    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidError::class, $input->getError());
  }

  public function testValidatorNotCalledWhenEmpty() {
    $validatorCalled = false;
    $input = (new Input())
      ->setRequired(false)
      ->setValue('')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertFalse($validatorCalled);
  }

  public function testValidatorNotCalledWhenDoValidateFails() {
    $validatorCalled = false;

    // Create a subclass that always fails doValidate
    $input = new class extends Input {
      protected function doValidate($value): ?\Coroq\Form\Error\Error {
        return new InvalidError($this);
      }
    };

    $input->setValue('test')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertFalse($validatorCalled);
  }

  public function testValidatorReturnsNullMeansValid() {
    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testSetValidatorToNullRemovesValidator() {
    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      });

    $this->assertFalse($input->validate());

    $input->setValidator(null);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidatorCanAccessFormItemProperties() {
    $input = (new Input())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        // Validator can check if value matches some pattern
        if (strlen($value) < 5) {
          return new InvalidError($formItem);
        }
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidError::class, $input->getError());

    $input->setValue('longer value');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidatorWithRequiredEmptyValue() {
    $validatorCalled = false;
    $input = (new Input())
      ->setRequired(true)
      ->setValue('')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(EmptyError::class, $input->getError());
    $this->assertFalse($validatorCalled);
  }
}
