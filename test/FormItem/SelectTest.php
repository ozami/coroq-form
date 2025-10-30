<?php
use Coroq\Form\FormItem\Select;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\NotInOptionsError;
use Coroq\Form\Error\InvalidError;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase {
  public function testValidate() {
    $input = (new Select())->setOptions([
      'a' => 'A',
      'b' => 'B',
    ]);

    $input->validate();
    $this->assertInstanceOf(EmptyError::class, $input->getError());

    $input->setValue('a')->validate();
    $this->assertNull($input->getError());

    $input->setValue('c')->validate();
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
  }

  public function testGetSelectedLabel() {
    $input = (new Select())->setOptions([
      'jp' => 'Japan',
      'us' => 'United States',
    ]);

    $input->setValue('jp');
    $this->assertSame('Japan', $input->getSelectedLabel());

    $input->setValue('invalid');
    $this->assertNull($input->getSelectedLabel());
  }

  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new Select())
      ->setOptions(['a' => 'A'])
      ->setValue('a');
    $this->assertSame($input->getValue(), $input->getParsedValue());
  }

  public function testValidatorIsCalledAfterDoValidate() {
    $validatorCalled = false;
    $input = (new Select())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue('a')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertTrue($validatorCalled);
  }

  public function testValidatorCanReturnError() {
    $input = (new Select())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue('a')
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidError::class, $input->getError());
  }

  public function testValidatorNotCalledWhenNotInOptions() {
    $validatorCalled = false;
    $input = (new Select())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue('c')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
    $this->assertFalse($validatorCalled);
  }
}
