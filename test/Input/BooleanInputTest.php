<?php
use Coroq\Form\FormItem\BooleanInput;
use Coroq\Form\Error\EmptyError;
use PHPUnit\Framework\TestCase;

class BooleanInputTest extends TestCase {
  public function testIsEmpty() {
    $input = new BooleanInput();

    // Empty values
    $input->setValue('');
    $this->assertTrue($input->isEmpty());

    $input->setValue(null);
    $this->assertTrue($input->isEmpty());

    $input->setValue(false);
    $this->assertTrue($input->isEmpty());

    // Non-empty values
    $input->setValue('on');
    $this->assertFalse($input->isEmpty());

    $input->setValue('1');
    $this->assertFalse($input->isEmpty());

    $input->setValue(true);
    $this->assertFalse($input->isEmpty());

    $input->setValue('0');
    $this->assertFalse($input->isEmpty());

    $input->setValue(0);
    $this->assertFalse($input->isEmpty());
  }

  public function testGetBoolean() {
    $input = new BooleanInput();

    $input->setValue('on');
    $this->assertTrue($input->getBoolean());

    $input->setValue('');
    $this->assertFalse($input->getBoolean());

    $input->setValue('0');
    $this->assertTrue($input->getBoolean());  // '0' is NOT empty
  }

  public function testValidateRequired() {
    $input = new BooleanInput();  // required by default

    $input->setValue('')->validate();
    $this->assertInstanceOf(EmptyError::class, $input->getError());

    $input->setValue('on')->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateOptional() {
    $input = (new BooleanInput())->setRequired(false);

    $input->setValue('')->validate();
    $this->assertNull($input->getError());

    $input->setValue('on')->validate();
    $this->assertNull($input->getError());
  }

  public function testGetParsedValue() {
    $input = new BooleanInput();

    $input->setValue('on');
    $this->assertSame(true, $input->getParsedValue());

    $input->setValue('');
    $this->assertSame(false, $input->getParsedValue());

    $this->assertSame($input->getBoolean(), $input->getParsedValue());
  }
}
