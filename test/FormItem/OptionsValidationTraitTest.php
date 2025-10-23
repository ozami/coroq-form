<?php
use Coroq\Form\FormItem\OptionsValidationTrait;
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\NotInOptionsError;
use PHPUnit\Framework\TestCase;

// Create a concrete test class that uses the trait
class OptionsValidationTestInput extends Input {
  use OptionsValidationTrait;

  // Expose validateInOptions for testing
  public function testValidateInOptions($value) {
    return $this->validateInOptions($value);
  }
}

class OptionsValidationTraitTest extends TestCase {
  public function testDefaultOptionsIsEmptyArray() {
    $input = new OptionsValidationTestInput();
    $this->assertSame([], $input->getOptions());
  }

  public function testSetOptions() {
    $input = new OptionsValidationTestInput();
    $options = ['a' => 'Option A', 'b' => 'Option B'];
    $result = $input->setOptions($options);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame($options, $input->getOptions());
  }

  public function testSetOptionsEmptyArray() {
    $input = new OptionsValidationTestInput();
    $input->setOptions(['a' => 'A']);
    $input->setOptions([]);

    $this->assertSame([], $input->getOptions());
  }

  public function testSetOptionsReplacesExisting() {
    $input = new OptionsValidationTestInput();
    $input->setOptions(['a' => 'A', 'b' => 'B']);
    $input->setOptions(['c' => 'C', 'd' => 'D']);

    $this->assertSame(['c' => 'C', 'd' => 'D'], $input->getOptions());
  }

  public function testValidateInOptionsValueExists() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B']);

    $error = $input->testValidateInOptions('a');
    $this->assertNull($error);

    $error = $input->testValidateInOptions('b');
    $this->assertNull($error);
  }

  public function testValidateInOptionsValueNotExists() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B']);

    $error = $input->testValidateInOptions('c');

    $this->assertInstanceOf(NotInOptionsError::class, $error);
    $this->assertSame($input, $error->formItem);
  }

  public function testValidateInOptionsWithEmptyOptions() {
    $input = (new OptionsValidationTestInput())->setOptions([]);

    $error = $input->testValidateInOptions('anything');

    $this->assertInstanceOf(NotInOptionsError::class, $error);
  }

  public function testValidateInOptionsWithIntegerKeys() {
    $input = (new OptionsValidationTestInput())
      ->setOptions([1 => 'One', 2 => 'Two', 3 => 'Three']);

    $error = $input->testValidateInOptions(1);
    $this->assertNull($error);

    $error = $input->testValidateInOptions(2);
    $this->assertNull($error);

    $error = $input->testValidateInOptions(99);
    $this->assertInstanceOf(NotInOptionsError::class, $error);
  }

  public function testValidateInOptionsWithStringIntegerKeys() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['1' => 'One', '2' => 'Two']);

    // String key '1'
    $error = $input->testValidateInOptions('1');
    $this->assertNull($error);

    // Integer key 1 - PHP will convert to string '1' for array access
    $error = $input->testValidateInOptions(1);
    $this->assertNull($error);
  }

  public function testValidateInOptionsWithZeroKey() {
    $input = (new OptionsValidationTestInput())
      ->setOptions([0 => 'Zero', 1 => 'One']);

    $error = $input->testValidateInOptions(0);
    $this->assertNull($error);
  }

  public function testValidateInOptionsWithEmptyStringKey() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['' => 'Empty', 'a' => 'A']);

    $error = $input->testValidateInOptions('');
    $this->assertNull($error);
  }

  public function testValidateInOptionsWithNullValue() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['a' => 'A', 'b' => 'B']);

    // isset() returns false for null keys
    $error = $input->testValidateInOptions(null);
    $this->assertInstanceOf(NotInOptionsError::class, $error);
  }

  public function testValidateInOptionsWithMixedKeyTypes() {
    $input = (new OptionsValidationTestInput())
      ->setOptions([
        'string' => 'String key',
        123 => 'Integer key',
        0 => 'Zero',
      ]);

    $error = $input->testValidateInOptions('string');
    $this->assertNull($error);

    $error = $input->testValidateInOptions(123);
    $this->assertNull($error);

    $error = $input->testValidateInOptions(0);
    $this->assertNull($error);

    $error = $input->testValidateInOptions('missing');
    $this->assertInstanceOf(NotInOptionsError::class, $error);
  }

  public function testValidateInOptionsUsesArrayKeyExists() {
    // array_key_exists() checks key existence, even if value is null
    $input = (new OptionsValidationTestInput())
      ->setOptions([
        'null' => null,
        'false' => false,
        'zero' => 0,
        'empty' => '',
      ]);

    // All these should be valid because the KEYS exist
    $error = $input->testValidateInOptions('null');
    $this->assertNull($error);

    $error = $input->testValidateInOptions('false');
    $this->assertNull($error);

    $error = $input->testValidateInOptions('zero');
    $this->assertNull($error);

    $error = $input->testValidateInOptions('empty');
    $this->assertNull($error);
  }

  public function testValidateInOptionsIsCaseSensitive() {
    $input = (new OptionsValidationTestInput())
      ->setOptions(['abc' => 'Lowercase', 'ABC' => 'Uppercase']);

    $error = $input->testValidateInOptions('abc');
    $this->assertNull($error);

    $error = $input->testValidateInOptions('ABC');
    $this->assertNull($error);

    // Mixed case - should not exist
    $error = $input->testValidateInOptions('Abc');
    $this->assertInstanceOf(NotInOptionsError::class, $error);
  }
}
