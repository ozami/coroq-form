<?php
use Coroq\Form\FormItem\AbstractFormItem;
use Coroq\Form\Error\EmptyError;
use PHPUnit\Framework\TestCase;

// Create a minimal concrete test class that extends AbstractFormItem
class AbstractFormItemTestClass extends AbstractFormItem {
  private $value = '';

  public function getValue(): mixed {
    return $this->value;
  }

  public function setValue(mixed $value): self {
    $this->value = $value;
    return $this;
  }

  public function clear(): self {
    $this->value = '';
    return $this;
  }

  public function isEmpty(): bool {
    return $this->value === '';
  }

  public function validate(): bool {
    return true; // Minimal implementation
  }
}

class AbstractFormItemTest extends TestCase {
  // Label tests
  public function testDefaultLabelIsEmptyString() {
    $input = new AbstractFormItemTestClass();
    $this->assertSame('', $input->getLabel());
  }

  public function testSetLabel() {
    $input = new AbstractFormItemTestClass();
    $result = $input->setLabel('Username');

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame('Username', $input->getLabel());
  }

  public function testSetLabelEmptyString() {
    $input = new AbstractFormItemTestClass();
    $input->setLabel('Test');
    $input->setLabel('');

    $this->assertSame('', $input->getLabel());
  }

  public function testSetLabelWithSpecialCharacters() {
    $input = new AbstractFormItemTestClass();
    $input->setLabel('メールアドレス'); // Japanese

    $this->assertSame('メールアドレス', $input->getLabel());
  }

  // Error tests
  public function testDefaultErrorIsNull() {
    $input = new AbstractFormItemTestClass();
    $this->assertNull($input->getError());
  }

  public function testSetError() {
    $input = new AbstractFormItemTestClass();
    $error = new EmptyError($input);
    $result = $input->setError($error);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertSame($error, $input->getError());
  }

  public function testSetErrorToNull() {
    $input = new AbstractFormItemTestClass();
    $input->setError(new EmptyError($input));
    $input->setError(null);

    $this->assertNull($input->getError());
  }

  public function testHasErrorReturnsFalseByDefault() {
    $input = new AbstractFormItemTestClass();
    $this->assertFalse($input->hasError());
  }

  public function testHasErrorReturnsTrueWhenErrorSet() {
    $input = new AbstractFormItemTestClass();
    $input->setError(new EmptyError($input));

    $this->assertTrue($input->hasError());
  }

  public function testHasErrorReturnsFalseAfterClearingError() {
    $input = new AbstractFormItemTestClass();
    $input->setError(new EmptyError($input));
    $input->setError(null);

    $this->assertFalse($input->hasError());
  }

  // Disabled tests
  public function testDefaultDisabledIsFalse() {
    $input = new AbstractFormItemTestClass();
    $this->assertFalse($input->isDisabled());
  }

  public function testSetDisabledTrue() {
    $input = new AbstractFormItemTestClass();
    $result = $input->setDisabled(true);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertTrue($input->isDisabled());
  }

  // Required tests
  public function testDefaultRequiredIsTrue() {
    $input = new AbstractFormItemTestClass();
    $this->assertTrue($input->isRequired());
  }

  public function testSetRequiredFalse() {
    $input = new AbstractFormItemTestClass();
    $result = $input->setRequired(false);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertFalse($input->isRequired());
  }

  // ReadOnly tests
  public function testDefaultReadOnlyIsFalse() {
    $input = new AbstractFormItemTestClass();
    $this->assertFalse($input->isReadOnly());
  }

  public function testSetReadOnlyTrue() {
    $input = new AbstractFormItemTestClass();
    $result = $input->setReadOnly(true);

    $this->assertSame($input, $result); // Fluent interface
    $this->assertTrue($input->isReadOnly());
  }

  // Fluent interface tests
  public function testFluentInterfaceForStateSetters() {
    $input = new AbstractFormItemTestClass();
    $result = $input
      ->setDisabled(true)
      ->setRequired(false)
      ->setReadOnly(true);

    $this->assertSame($input, $result);
    $this->assertTrue($input->isDisabled());
    $this->assertFalse($input->isRequired());
    $this->assertTrue($input->isReadOnly());
  }

  public function testFluentInterfaceForLabelAndError() {
    $input = new AbstractFormItemTestClass();
    $error = new EmptyError($input);
    $result = $input
      ->setLabel('Test')
      ->setError($error);

    $this->assertSame($input, $result);
    $this->assertSame('Test', $input->getLabel());
    $this->assertSame($error, $input->getError());
  }

  // getParsedValue default implementation test
  public function testGetParsedValueDefaultImplementation() {
    $input = new AbstractFormItemTestClass();
    $input->setValue('test value');

    // Default implementation should return getValue()
    $this->assertSame($input->getValue(), $input->getParsedValue());
    $this->assertSame('test value', $input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenValueIsNull() {
    $input = new AbstractFormItemTestClass();
    $input->setValue(null);

    $this->assertSame($input->getValue(), $input->getParsedValue());
    $this->assertNull($input->getParsedValue());
  }

  // Combined state tests
  public function testAllStatesCanBeSetIndependently() {
    $input = new AbstractFormItemTestClass();

    // Set all states
    $input->setDisabled(true);
    $input->setRequired(false);
    $input->setReadOnly(true);
    $input->setLabel('Test Label');
    $input->setError(new EmptyError($input));

    // Verify all states
    $this->assertTrue($input->isDisabled());
    $this->assertFalse($input->isRequired());
    $this->assertTrue($input->isReadOnly());
    $this->assertSame('Test Label', $input->getLabel());
    $this->assertTrue($input->hasError());
  }
}
