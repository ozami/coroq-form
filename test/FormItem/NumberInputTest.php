<?php
use Coroq\Form\FormItem\NumberInput;
use Coroq\Form\Error\NotNumericError;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;
use PHPUnit\Framework\TestCase;

class NumberInputTest extends TestCase {
  public function testFilterUsesStringFilterTrait() {
    $input = new NumberInput();

    // Verify filter calls toHalfwidthAscii() (one example is enough)
    $input->setValue('１２３．４５');
    $this->assertSame('123.45', $input->getValue());

    // Verify filter calls removeWhitespace() (one example is enough)
    $input->setValue('1 2 3.4 5');
    $this->assertSame('123.45', $input->getValue());

    // Details of what constitutes "whitespace" or "full-width" are tested in StringFilterTraitTest
  }

  public function testValidateNumeric() {
    $input = new NumberInput();
    $input->setValue('123.45')->validate();
    $this->assertNull($input->getError());

    $input->setValue('abc')->validate();
    $this->assertInstanceOf(NotNumericError::class, $input->getError());
  }

  public function testValidateRange() {
    $input = (new NumberInput())->setMin(10.5)->setMax(20.5);

    $input->setValue('5.0')->validate();
    $this->assertInstanceOf(TooSmallError::class, $input->getError());

    $input->setValue('15.0')->validate();
    $this->assertNull($input->getError());

    $input->setValue('25.0')->validate();
    $this->assertInstanceOf(TooLargeError::class, $input->getError());
  }

  public function testGetNumber() {
    $input = (new NumberInput())->setValue('3.14');
    $this->assertSame(3.14, $input->getNumber());

    $input->setValue('');
    $this->assertNull($input->getNumber());

    $input->setValue('invalid');
    $this->assertNull($input->getNumber());
  }

  public function testGetParsedValue() {
    $input = (new NumberInput())->setValue('99.99');
    $this->assertSame(99.99, $input->getParsedValue());
    $this->assertSame($input->getNumber(), $input->getParsedValue());
  }

  public static function setUpBeforeClass(): void {
    mb_substitute_character(0xFFFD);
  }

  public function testInvalidUtf8DoesNotCauseFatalError() {
    $input = new NumberInput();
    $input->setValue("123\x80\x81\x82");
    // Should not crash - invalid bytes replaced with �
    $value = $input->getValue();
    $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
  }
}
