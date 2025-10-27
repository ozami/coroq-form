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

    // Test trailing zeros removal
    $input->setValue('123.00');
    $this->assertSame('123', $input->getValue());

    $input->setValue('123.0000000');
    $this->assertSame('123', $input->getValue());

    $input->setValue('-456.00');
    $this->assertSame('-456', $input->getValue());

    // Non-zero decimal should NOT be removed
    $input->setValue('123.10');
    $this->assertSame('123.10', $input->getValue());

    $input->setValue('123.01');
    $this->assertSame('123.01', $input->getValue());
  }

  public function testValidateInteger() {
    $input = new IntegerInput();
    $input->setValue('123')->validate();
    $this->assertNull($input->getError());

    // "123.00" should be filtered to "123" and validate as integer
    $input->setValue('123.00')->validate();
    $this->assertNull($input->getError());
    $this->assertSame(123, $input->getInteger());

    // "12.3" has non-zero decimals, should fail validation
    $input->setValue('12.3')->validate();
    $this->assertInstanceOf(NotIntegerError::class, $input->getError());

    $input->setValue('abc')->validate();
    $this->assertInstanceOf(NotIntegerError::class, $input->getError());
  }

  public function testValidateRange() {
    $input = (new IntegerInput())->setMin('10')->setMax('20');

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

  // Large integer tests

  public function testPhpIntMaxValidates() {
    $input = new IntegerInput();
    $input->setValue((string)PHP_INT_MAX);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
    $this->assertSame(PHP_INT_MAX, $input->getInteger());
  }

  public function testPhpIntMaxPlusOneFailsValidation() {
    $input = new IntegerInput();
    // PHP_INT_MAX + 1
    $input->setValue(bcadd((string)PHP_INT_MAX, '1'));
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooLargeError::class, $input->getError());
    $this->assertNull($input->getInteger());
  }

  public function testPhpIntMinValidates() {
    $input = new IntegerInput();
    $input->setValue((string)PHP_INT_MIN);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
    $this->assertSame(PHP_INT_MIN, $input->getInteger());
  }

  public function testPhpIntMinMinusOneFailsValidation() {
    $input = new IntegerInput();
    // PHP_INT_MIN - 1
    $input->setValue(bcsub((string)PHP_INT_MIN, '1'));
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooSmallError::class, $input->getError());
    $this->assertNull($input->getInteger());
  }

  public function testVeryLargeIntegerFailsValidation() {
    $input = new IntegerInput();
    $input->setValue('99999999999999999999');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooLargeError::class, $input->getError());
    $this->assertNull($input->getInteger());
  }

  public function testVerySmallNegativeIntegerFailsValidation() {
    $input = new IntegerInput();
    $input->setValue('-99999999999999999999');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooSmallError::class, $input->getError());
    $this->assertNull($input->getInteger());
  }

  public function testGetIntegerReturnsNullForOutOfRangeValue() {
    $input = new IntegerInput();
    $input->setValue('99999999999999999999');
    // Don't call validate - just test getInteger
    $this->assertNull($input->getInteger());
  }

  public function testUserDefinedRangeWithLargeIntegers() {
    $input = (new IntegerInput())
      ->setMin('1000')
      ->setMax((string)PHP_INT_MAX);

    // Value within range
    $input->setValue('5000');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());

    // Value exceeds PHP_INT_MAX (should fail with TooLargeError)
    $input->setValue(bcadd((string)PHP_INT_MAX, '1'));
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooLargeError::class, $input->getError());
  }

  public function testNegativeIntegersWithinRange() {
    $input = (new IntegerInput())
      ->setMin('-1000')
      ->setMax('-100');

    $input->setValue('-500');
    $this->assertTrue($input->validate());
    $this->assertSame(-500, $input->getInteger());

    $input->setValue('-50');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooLargeError::class, $input->getError());

    $input->setValue('-1500');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooSmallError::class, $input->getError());
  }

  public function testSetMinThrowsExceptionIfBelowPhpIntMin() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('IntegerInput min must be >= PHP_INT_MIN');

    $input = new IntegerInput();
    $input->setMin(bcadd((string)PHP_INT_MIN, '-1'));
  }

  public function testSetMinThrowsExceptionIfAbovePhpIntMax() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('IntegerInput min must be <= PHP_INT_MAX');

    $input = new IntegerInput();
    $input->setMin(bcadd((string)PHP_INT_MAX, '1'));
  }

  public function testSetMaxThrowsExceptionIfBelowPhpIntMin() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('IntegerInput max must be >= PHP_INT_MIN');

    $input = new IntegerInput();
    $input->setMax(bcadd((string)PHP_INT_MIN, '-1'));
  }

  public function testSetMaxThrowsExceptionIfAbovePhpIntMax() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('IntegerInput max must be <= PHP_INT_MAX');

    $input = new IntegerInput();
    $input->setMax(bcadd((string)PHP_INT_MAX, '1'));
  }

  public function testDefaultMinMaxArePhpIntLimits() {
    $input = new IntegerInput();
    $this->assertSame((string)PHP_INT_MIN, $input->getMin());
    $this->assertSame((string)PHP_INT_MAX, $input->getMax());
  }

  public function testGetIntegerAlwaysReturnsIntOrNull() {
    $input = new IntegerInput();

    // Within range - returns int
    $input->setValue('12345');
    $this->assertIsInt($input->getInteger());

    // At PHP_INT_MAX - returns int
    $input->setValue((string)PHP_INT_MAX);
    $this->assertSame(PHP_INT_MAX, $input->getInteger());

    // At PHP_INT_MIN - returns int
    $input->setValue((string)PHP_INT_MIN);
    $this->assertSame(PHP_INT_MIN, $input->getInteger());

    // Empty - returns null
    $input->setValue('');
    $this->assertNull($input->getInteger());

    // Invalid format - returns null
    $input->setValue('not a number');
    $this->assertNull($input->getInteger());

    // Out of range (even though validation would fail, getInteger handles it)
    $input->setValue(bcadd((string)PHP_INT_MAX, '1'));
    $this->assertNull($input->getInteger());
  }
}
