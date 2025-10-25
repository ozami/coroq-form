<?php
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\Error\NotIntegerError;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;
use PHPUnit\Framework\TestCase;

class IntegerInputTest extends TestCase {
  public function testFilter() {
    $input = (new IntegerInput())->setValue('　１２３　');
    $this->assertSame('123', $input->getValue());
  }

  public function testValidateInteger() {
    $input = new IntegerInput();
    $input->setValue('123')->validate();
    $this->assertNull($input->getError());

    $input->setValue('12.3')->validate();
    $this->assertInstanceOf(NotIntegerError::class, $input->getError());

    $input->setValue('abc')->validate();
    $this->assertInstanceOf(NotIntegerError::class, $input->getError());
  }

  public function testValidateRange() {
    $input = (new IntegerInput())->setMin(10)->setMax(20);

    $input->setValue('5')->validate();
    $this->assertInstanceOf(TooSmallError::class, $input->getError());

    $input->setValue('15')->validate();
    $this->assertNull($input->getError());

    $input->setValue('25')->validate();
    $this->assertInstanceOf(TooLargeError::class, $input->getError());
  }

  public function testGetInteger() {
    $input = (new IntegerInput())->setValue('42');
    $this->assertSame(42, $input->getInteger());

    $input->setValue('');
    $this->assertNull($input->getInteger());

    $input->setValue('invalid');
    $this->assertNull($input->getInteger());
  }

  public function testGetParsedValue() {
    $input = (new IntegerInput())->setValue('100');
    $this->assertSame(100, $input->getParsedValue());
    $this->assertSame($input->getInteger(), $input->getParsedValue());
  }
}
