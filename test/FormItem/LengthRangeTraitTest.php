<?php
use Coroq\Form\FormItem\LengthRangeTrait;
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\TooShortError;
use Coroq\Form\Error\TooLongError;
use PHPUnit\Framework\TestCase;

// Create a concrete test class that uses the trait
class LengthRangeTestInput extends Input {
  use LengthRangeTrait;

  // Expose validateLength for testing
  public function testValidateLength($value) {
    return $this->validateLength($value);
  }
}

class LengthRangeTraitTest extends TestCase {
  public function testDefaultMinLengthIsZero() {
    $input = new LengthRangeTestInput();
    $this->assertSame(0, $input->getMinLength());
  }

  public function testDefaultMaxLengthIsPhpIntMax() {
    $input = new LengthRangeTestInput();
    $this->assertSame(PHP_INT_MAX, $input->getMaxLength());
  }

  public function testSetMinLength() {
    $input = new LengthRangeTestInput();
    $result = $input->setMinLength(5);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame(5, $input->getMinLength());
  }

  public function testSetMinLengthZero() {
    $input = new LengthRangeTestInput();
    $input->setMinLength(0);

    $this->assertSame(0, $input->getMinLength());
  }

  public function testSetMaxLength() {
    $input = new LengthRangeTestInput();
    $result = $input->setMaxLength(100);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame(100, $input->getMaxLength());
  }

  public function testFluentInterface() {
    $input = new LengthRangeTestInput();
    $result = $input->setMinLength(5)->setMaxLength(50);

    $this->assertSame($input, $result);
    $this->assertSame(5, $input->getMinLength());
    $this->assertSame(50, $input->getMaxLength());
  }

  public function testValidateLengthTooShort() {
    $input = (new LengthRangeTestInput())->setMinLength(5);
    $error = $input->testValidateLength('abc');

    $this->assertInstanceOf(TooShortError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateLengthTooLong() {
    $input = (new LengthRangeTestInput())->setMaxLength(10);
    $error = $input->testValidateLength('this is too long');

    $this->assertInstanceOf(TooLongError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateLengthInRange() {
    $input = (new LengthRangeTestInput())->setMinLength(5)->setMaxLength(10);
    $error = $input->testValidateLength('hello');

    $this->assertNull($error);
  }

  public function testValidateLengthExactlyAtMin() {
    $input = (new LengthRangeTestInput())->setMinLength(5);
    $error = $input->testValidateLength('hello'); // 5 characters

    $this->assertNull($error);
  }

  public function testValidateLengthExactlyAtMax() {
    $input = (new LengthRangeTestInput())->setMaxLength(5);
    $error = $input->testValidateLength('hello'); // 5 characters

    $this->assertNull($error);
  }

  public function testValidateLengthEmptyString() {
    $input = (new LengthRangeTestInput())->setMinLength(0)->setMaxLength(10);
    $error = $input->testValidateLength('');

    $this->assertNull($error);
  }

  public function testValidateLengthEmptyStringWithMinLength() {
    $input = (new LengthRangeTestInput())->setMinLength(1);
    $error = $input->testValidateLength('');

    $this->assertInstanceOf(TooShortError::class, $error);
  }

  public function testValidateLengthWithOnlyMin() {
    $input = (new LengthRangeTestInput())->setMinLength(5);
    // Max is PHP_INT_MAX by default

    $error = $input->testValidateLength('abc');
    $this->assertInstanceOf(TooShortError::class, $error);

    $error = $input->testValidateLength('hello');
    $this->assertNull($error);

    $error = $input->testValidateLength('a very long string that should still be valid');
    $this->assertNull($error);
  }

  public function testValidateLengthWithOnlyMax() {
    $input = (new LengthRangeTestInput())->setMaxLength(10);
    // Min is 0 by default

    $error = $input->testValidateLength('');
    $this->assertNull($error);

    $error = $input->testValidateLength('hello');
    $this->assertNull($error);

    $error = $input->testValidateLength('this is too long');
    $this->assertInstanceOf(TooLongError::class, $error);
  }

  public function testValidateLengthWithNoRangeSet() {
    $input = new LengthRangeTestInput();
    // Min is 0, Max is PHP_INT_MAX by default

    $error = $input->testValidateLength('');
    $this->assertNull($error);

    $error = $input->testValidateLength('any length string');
    $this->assertNull($error);
  }

  public function testValidateLengthExactlyOne() {
    $input = (new LengthRangeTestInput())->setMinLength(1)->setMaxLength(1);

    $error = $input->testValidateLength('');
    $this->assertInstanceOf(TooShortError::class, $error);

    $error = $input->testValidateLength('a');
    $this->assertNull($error);

    $error = $input->testValidateLength('ab');
    $this->assertInstanceOf(TooLongError::class, $error);
  }

  public function testValidateLengthMultibyteCharacters() {
    $input = (new LengthRangeTestInput())->setMinLength(3)->setMaxLength(5);

    // Japanese characters (3 characters, but more bytes)
    $error = $input->testValidateLength('あいう');
    $this->assertNull($error); // Should be valid (3 chars)

    // Too short (2 characters)
    $error = $input->testValidateLength('あい');
    $this->assertInstanceOf(TooShortError::class, $error);

    // Too long (6 characters)
    $error = $input->testValidateLength('あいうえおか');
    $this->assertInstanceOf(TooLongError::class, $error);
  }

  public function testValidateLengthEmoji() {
    $input = (new LengthRangeTestInput())->setMinLength(3)->setMaxLength(5);

    // Emoji (3 emoji = 3 characters)
    $error = $input->testValidateLength('😀😁😂');
    $this->assertNull($error);

    // Too long (6 emoji)
    $error = $input->testValidateLength('😀😁😂😃😄😅');
    $this->assertInstanceOf(TooLongError::class, $error);
  }

  public function testValidateLengthMixedCharacters() {
    $input = (new LengthRangeTestInput())->setMinLength(5)->setMaxLength(10);

    // Mix of ASCII, Japanese, emoji (8 characters total)
    $error = $input->testValidateLength('Hello世界😀');
    $this->assertNull($error);
  }
}
