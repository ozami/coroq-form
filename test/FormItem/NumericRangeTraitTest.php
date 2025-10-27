<?php
use Coroq\Form\FormItem\NumericRangeTrait;
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\HasNumericRangeInterface;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;
use PHPUnit\Framework\TestCase;

// Create a concrete test class that uses the trait
class NumericRangeTestInput extends Input implements HasNumericRangeInterface {
  use NumericRangeTrait;

  // Public setters for testing (similar to NumberInput)
  public function setMin(string $min): self {
    return $this->setMinInternal($min);
  }

  public function setMax(string $max): self {
    return $this->setMaxInternal($max);
  }

  // Expose validateRange for testing
  public function testValidateRange($value) {
    return $this->validateRange((string)$value);
  }
}

class NumericRangeTraitTest extends TestCase {
  public function testDefaultMinIsNull() {
    $input = new NumericRangeTestInput();
    $this->assertNull($input->getMin());
  }

  public function testDefaultMaxIsNull() {
    $input = new NumericRangeTestInput();
    $this->assertNull($input->getMax());
  }

  public function testSetMinInteger() {
    $input = new NumericRangeTestInput();
    $result = $input->setMin('10');

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame('10', $input->getMin());
  }

  public function testSetMinFloat() {
    $input = new NumericRangeTestInput();
    $input->setMin('10.5');

    $this->assertSame('10.5', $input->getMin());
  }

  public function testSetMinNegative() {
    $input = new NumericRangeTestInput();
    $input->setMin('-100');

    $this->assertSame('-100', $input->getMin());
  }

  public function testSetMaxInteger() {
    $input = new NumericRangeTestInput();
    $result = $input->setMax('100');

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame('100', $input->getMax());
  }

  public function testSetMaxFloat() {
    $input = new NumericRangeTestInput();
    $input->setMax('100.5');

    $this->assertSame('100.5', $input->getMax());
  }

  public function testSetMaxNegative() {
    $input = new NumericRangeTestInput();
    $input->setMax('-10');

    $this->assertSame('-10', $input->getMax());
  }

  public function testFluentInterface() {
    $input = new NumericRangeTestInput();
    $result = $input->setMin('10')->setMax('100');

    $this->assertSame($input, $result);
    $this->assertSame('10', $input->getMin());
    $this->assertSame('100', $input->getMax());
  }

  public function testValidateRangeValueBelowMin() {
    $input = (new NumericRangeTestInput())->setMin('10');
    $error = $input->testValidateRange(5);

    $this->assertInstanceOf(TooSmallError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateRangeValueAboveMax() {
    $input = (new NumericRangeTestInput())->setMax('100');
    $error = $input->testValidateRange(150);

    $this->assertInstanceOf(TooLargeError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateRangeValueInRange() {
    $input = (new NumericRangeTestInput())->setMin('10')->setMax('100');
    $error = $input->testValidateRange(50);

    $this->assertNull($error);
  }

  public function testValidateRangeValueExactlyAtMin() {
    $input = (new NumericRangeTestInput())->setMin('10');
    $error = $input->testValidateRange(10);

    $this->assertNull($error);
  }

  public function testValidateRangeValueExactlyAtMax() {
    $input = (new NumericRangeTestInput())->setMax('100');
    $error = $input->testValidateRange(100);

    $this->assertNull($error);
  }

  public function testValidateRangeWithFloatValues() {
    $input = (new NumericRangeTestInput())->setMin('10.5')->setMax('20.5');

    // Below min
    $error = $input->testValidateRange(10.4);
    $this->assertInstanceOf(TooSmallError::class, $error);

    // In range
    $error = $input->testValidateRange(15.0);
    $this->assertNull($error);

    // Above max
    $error = $input->testValidateRange(20.6);
    $this->assertInstanceOf(TooLargeError::class, $error);
  }

  public function testValidateRangeWithNegativeRange() {
    $input = (new NumericRangeTestInput())->setMin('-100')->setMax('-10');

    // Below min
    $error = $input->testValidateRange(-150);
    $this->assertInstanceOf(TooSmallError::class, $error);

    // In range
    $error = $input->testValidateRange(-50);
    $this->assertNull($error);

    // Above max
    $error = $input->testValidateRange(-5);
    $this->assertInstanceOf(TooLargeError::class, $error);
  }

  public function testValidateRangeWithOnlyMin() {
    $input = (new NumericRangeTestInput())->setMin('10');
    // Max is null by default (no upper limit)

    $error = $input->testValidateRange(5);
    $this->assertInstanceOf(TooSmallError::class, $error);

    $error = $input->testValidateRange(10);
    $this->assertNull($error);

    $error = $input->testValidateRange(999999);
    $this->assertNull($error);
  }

  public function testValidateRangeWithOnlyMax() {
    $input = (new NumericRangeTestInput())->setMax('100');
    // Min is null by default (no lower limit)

    $error = $input->testValidateRange(-999999);
    $this->assertNull($error);

    $error = $input->testValidateRange(100);
    $this->assertNull($error);

    $error = $input->testValidateRange(150);
    $this->assertInstanceOf(TooLargeError::class, $error);
  }

  public function testValidateRangeWithNoRangeSet() {
    $input = new NumericRangeTestInput();
    // Min is null, Max is null by default (no limits)

    $error = $input->testValidateRange(-999999);
    $this->assertNull($error);

    $error = $input->testValidateRange(0);
    $this->assertNull($error);

    $error = $input->testValidateRange(999999);
    $this->assertNull($error);
  }

  public function testValidateRangeWithZeroInRange() {
    $input = (new NumericRangeTestInput())->setMin('-10')->setMax('10');

    $error = $input->testValidateRange(0);
    $this->assertNull($error);
  }

  public function testValidateRangeWithStringNumeric() {
    $input = (new NumericRangeTestInput())->setMin('10')->setMax('100');

    // bccomp will compare string "5" < string "10" correctly
    $error = $input->testValidateRange("5");
    $this->assertInstanceOf(TooSmallError::class, $error);

    $error = $input->testValidateRange("50");
    $this->assertNull($error);

    $error = $input->testValidateRange("150");
    $this->assertInstanceOf(TooLargeError::class, $error);
  }

  public function testHighPrecisionFloatValues() {
    // Test that high precision floats are preserved as strings
    $input = (new NumericRangeTestInput())
      ->setMin('10.123456789012345')
      ->setMax('10.123456789012346');

    // Verify min/max stored correctly
    $this->assertSame('10.123456789012345', $input->getMin());
    $this->assertSame('10.123456789012346', $input->getMax());

    // Below min (would be equal if precision lost!)
    $error = $input->testValidateRange('10.123456789012344');
    $this->assertInstanceOf(TooSmallError::class, $error);

    // Exactly at min
    $error = $input->testValidateRange('10.123456789012345');
    $this->assertNull($error);

    // Between min and max
    $error = $input->testValidateRange('10.1234567890123455');
    $this->assertNull($error);

    // Exactly at max
    $error = $input->testValidateRange('10.123456789012346');
    $this->assertNull($error);

    // Above max
    $error = $input->testValidateRange('10.123456789012347');
    $this->assertInstanceOf(TooLargeError::class, $error);
  }
}
