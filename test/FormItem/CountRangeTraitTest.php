<?php
use Coroq\Form\FormItem\CountRangeTrait;
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\TooFewSelectionsError;
use Coroq\Form\Error\TooManySelectionsError;
use PHPUnit\Framework\TestCase;

// Create a concrete test class that uses the trait
class CountRangeTestInput extends Input {
  use CountRangeTrait;

  // Expose validateCount for testing
  public function testValidateCount(int $count) {
    return $this->validateCount($count);
  }
}

class CountRangeTraitTest extends TestCase {
  public function testDefaultMinCountIsZero() {
    $input = new CountRangeTestInput();
    $this->assertSame(0, $input->getMinCount());
  }

  public function testDefaultMaxCountIsPhpIntMax() {
    $input = new CountRangeTestInput();
    $this->assertSame(PHP_INT_MAX, $input->getMaxCount());
  }

  public function testSetMinCount() {
    $input = new CountRangeTestInput();
    $result = $input->setMinCount(2);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame(2, $input->getMinCount());
  }

  public function testSetMinCountZero() {
    $input = new CountRangeTestInput();
    $input->setMinCount(0);

    $this->assertSame(0, $input->getMinCount());
  }

  public function testSetMaxCount() {
    $input = new CountRangeTestInput();
    $result = $input->setMaxCount(10);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame(10, $input->getMaxCount());
  }

  public function testFluentInterface() {
    $input = new CountRangeTestInput();
    $result = $input->setMinCount(1)->setMaxCount(5);

    $this->assertSame($input, $result);
    $this->assertSame(1, $input->getMinCount());
    $this->assertSame(5, $input->getMaxCount());
  }

  public function testValidateCountBelowMin() {
    $input = (new CountRangeTestInput())->setMinCount(2);
    $error = $input->testValidateCount(1);

    $this->assertInstanceOf(TooFewSelectionsError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateCountAboveMax() {
    $input = (new CountRangeTestInput())->setMaxCount(5);
    $error = $input->testValidateCount(6);

    $this->assertInstanceOf(TooManySelectionsError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateCountInRange() {
    $input = (new CountRangeTestInput())->setMinCount(1)->setMaxCount(5);
    $error = $input->testValidateCount(3);

    $this->assertNull($error);
  }

  public function testValidateCountExactlyAtMin() {
    $input = (new CountRangeTestInput())->setMinCount(2);
    $error = $input->testValidateCount(2);

    $this->assertNull($error);
  }

  public function testValidateCountExactlyAtMax() {
    $input = (new CountRangeTestInput())->setMaxCount(5);
    $error = $input->testValidateCount(5);

    $this->assertNull($error);
  }

  public function testValidateCountZero() {
    $input = (new CountRangeTestInput())->setMinCount(0)->setMaxCount(5);
    $error = $input->testValidateCount(0);

    $this->assertNull($error);
  }

  public function testValidateCountZeroBelowMin() {
    $input = (new CountRangeTestInput())->setMinCount(1);
    $error = $input->testValidateCount(0);

    $this->assertInstanceOf(TooFewSelectionsError::class, $error);
  }

  public function testValidateCountWithOnlyMin() {
    $input = (new CountRangeTestInput())->setMinCount(2);
    // Max is PHP_INT_MAX by default

    $error = $input->testValidateCount(1);
    $this->assertInstanceOf(TooFewSelectionsError::class, $error);

    $error = $input->testValidateCount(2);
    $this->assertNull($error);

    $error = $input->testValidateCount(1000);
    $this->assertNull($error);
  }

  public function testValidateCountWithOnlyMax() {
    $input = (new CountRangeTestInput())->setMaxCount(5);
    // Min is 0 by default

    $error = $input->testValidateCount(0);
    $this->assertNull($error);

    $error = $input->testValidateCount(5);
    $this->assertNull($error);

    $error = $input->testValidateCount(6);
    $this->assertInstanceOf(TooManySelectionsError::class, $error);
  }

  public function testValidateCountWithNoRangeSet() {
    $input = new CountRangeTestInput();
    // Min is 0, Max is PHP_INT_MAX by default

    $error = $input->testValidateCount(0);
    $this->assertNull($error);

    $error = $input->testValidateCount(100);
    $this->assertNull($error);

    $error = $input->testValidateCount(999999);
    $this->assertNull($error);
  }

  public function testValidateCountExactlyOne() {
    $input = (new CountRangeTestInput())->setMinCount(1)->setMaxCount(1);

    $error = $input->testValidateCount(0);
    $this->assertInstanceOf(TooFewSelectionsError::class, $error);

    $error = $input->testValidateCount(1);
    $this->assertNull($error);

    $error = $input->testValidateCount(2);
    $this->assertInstanceOf(TooManySelectionsError::class, $error);
  }

  public function testValidateCountLargeNumbers() {
    $input = (new CountRangeTestInput())->setMinCount(100)->setMaxCount(200);

    $error = $input->testValidateCount(99);
    $this->assertInstanceOf(TooFewSelectionsError::class, $error);

    $error = $input->testValidateCount(150);
    $this->assertNull($error);

    $error = $input->testValidateCount(201);
    $this->assertInstanceOf(TooManySelectionsError::class, $error);
  }
}
