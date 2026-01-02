<?php
use Coroq\Form\Form;
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\BooleanInput;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase {
  public function testGetFilledValueWithNestedForm() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->email = (new Input())->setValue('');
    $form->address = new Form();
    $form->address->street = (new Input())->setValue('Main St');
    $form->address->city = (new Input())->setValue('');
    $form->address->zip = (new Input())->setValue('12345');

    $filled = $form->getFilledValue();
    $this->assertEquals([
      'name' => 'John',
      'address' => [
        'street' => 'Main St',
        'zip' => '12345',
      ]
    ], $filled);
  }

  public function testGetFilledValueWithEmptyNestedForm() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->address = new Form();
    $form->address->street = (new Input())->setValue('');
    $form->address->city = (new Input())->setValue('');

    // Empty nested form should be excluded
    $filled = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $filled);
  }

  public function testGetFilledValueWithMultipleLevelsOfNesting() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->contact = new Form();
    $form->contact->email = (new Input())->setValue('john@example.com');
    $form->contact->address = new Form();
    $form->contact->address->street = (new Input())->setValue('Main St');
    $form->contact->address->city = (new Input())->setValue('');
    $form->contact->address->zip = (new Input())->setValue('12345');

    $filled = $form->getFilledValue();
    $this->assertEquals([
      'name' => 'John',
      'contact' => [
        'email' => 'john@example.com',
        'address' => [
          'street' => 'Main St',
          'zip' => '12345',
        ]
      ]
    ], $filled);
  }

  public function testGetFilledValueWithDisabledNestedForm() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->address = new Form();
    $form->address->street = (new Input())->setValue('Main St');
    $form->address->city = (new Input())->setValue('Springfield');
    $form->address->setDisabled(true);

    // Disabled nested form should be excluded
    $filled = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $filled);
  }

  public function testGetFilledValueWithMixedEmptyAndFilledNestedForms() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->work = new Form();
    $form->work->company = (new Input())->setValue('Acme Corp');
    $form->home = new Form();
    $form->home->street = (new Input())->setValue('');

    $filled = $form->getFilledValue();
    $this->assertEquals([
      'name' => 'John',
      'work' => [
        'company' => 'Acme Corp'
      ]
    ], $filled);
  }

  public function testNestedFormGetFilledParsedValue() {
    $form = new Form();
    $form->age = (new IntegerInput())->setValue('25');
    $form->address = new Form();
    $form->address->street = (new Input())->setValue('Main St');
    $form->address->city = (new Input())->setValue('');

    $filled = $form->getFilledParsedValue();
    $this->assertSame(25, $filled['age']);
    $this->assertEquals(['street' => 'Main St'], $filled['address']);
  }

  public function testSetValue() {
    $form = new Form();
    $form->a = new Input();
    $form->b = new Input();
    $form->c = new Form();
    $form->c->d = new Input();
    $form->c->e = new Input();

    $form->setValue([
      'a' => 'A',
      'c' => [
        'd' => 'D',
      ],
      'f' => 'F',  // Non-existent field
    ]);

    $this->assertEquals([
      'a' => 'A',
      'b' => '',
      'c' => [
        'd' => 'D',
        'e' => '',
      ],
    ], $form->getValue());
  }

  public function testSetValueOnReadOnlyFormIsIgnored() {
    $form = new Form();
    $form->name = (new Input())->setValue('Original');
    $form->setReadOnly(true);

    $form->setValue(['name' => 'New']);
    $this->assertSame('Original', $form->name->getValue());
  }

  public function testGetItem() {
    $form = new Form();
    $form->name = new Input();
    $form->email = new Input();

    // Get existing items
    $this->assertSame($form->name, $form->getItem('name'));
    $this->assertSame($form->email, $form->getItem('email'));

    // Get non-existent item
    $this->assertNull($form->getItem('nonexistent'));
  }

  public function testGetItemWithFormInterface() {
    // Test that getItem works through FormInterface
    $form = new Form();
    $form->name = new Input();

    $formInterface = $form; // Typed as FormInterface
    $this->assertInstanceOf(\Coroq\Form\FormItem\FormItemInterface::class, $formInterface->getItem('name'));
  }

  // Form::clear() tests

  public function testClearClearsDisabledItems() {
    $form = new Form();
    $form->name = (new Input())->setValue('John')->setDisabled(true);
    $form->email = (new Input())->setValue('john@example.com');

    // clear() should clear ALL items, including disabled ones
    $form->clear();

    $this->assertSame('', $form->name->getValue());
    $this->assertSame('', $form->email->getValue());
  }

  public function testClearClearsNestedForms() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->address = new Form();
    $form->address->street = (new Input())->setValue('Main St');
    $form->address->city = (new Input())->setValue('Springfield');

    $form->clear();

    $this->assertSame('', $form->name->getValue());
    $this->assertSame('', $form->address->street->getValue());
    $this->assertSame('', $form->address->city->getValue());
  }

  public function testClearRemovesErrors() {
    $form = new Form();
    $form->name = new Input(); // Required by default

    $form->validate(); // Should fail
    $this->assertTrue($form->hasError());

    $form->clear();
    // clear() calls setValue('') which clears errors
    $this->assertFalse($form->hasError());
  }

  public function testClearWithMultipleLevelsOfNesting() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->contact = new Form();
    $form->contact->email = (new Input())->setValue('john@example.com');
    $form->contact->address = new Form();
    $form->contact->address->street = (new Input())->setValue('Main St');

    $form->clear();

    $this->assertSame('', $form->name->getValue());
    $this->assertSame('', $form->contact->email->getValue());
    $this->assertSame('', $form->contact->address->street->getValue());
  }

  // Form::isReadOnly() tests

  public function testIsReadOnlyDefaultIsFalse() {
    $form = new Form();
    $this->assertFalse($form->isReadOnly());
  }

  public function testIsReadOnlyReturnsTrueAfterSetReadOnly() {
    $form = new Form();
    $form->setReadOnly(true);
    $this->assertTrue($form->isReadOnly());
  }

  public function testIsReadOnlyReturnsFalseAfterSetReadOnlyFalse() {
    $form = new Form();
    $form->setReadOnly(true);
    $form->setReadOnly(false);
    $this->assertFalse($form->isReadOnly());
  }

  public function testSetReadOnlyReturnsFluentInterface() {
    $form = new Form();
    $result = $form->setReadOnly(true);
    $this->assertSame($form, $result);
  }

  public function testReadOnlyFormIgnoresSetValue() {
    $form = new Form();
    $form->name = (new Input())->setValue('Original');
    $form->setReadOnly(true);

    $form->setValue(['name' => 'New']);

    // Should still have original value
    $this->assertSame('Original', $form->name->getValue());
  }

  // Form::getError() tests

  public function testGetErrorIncludesNestedFormErrors() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->address = new Form();
    $form->address->street = new Input(); // Required, empty
    $form->address->city = (new Input())->setValue('Springfield');

    $form->validate();

    $errors = $form->getError();
    $this->assertNull($errors['name']);
    $this->assertIsArray($errors['address']);
    $this->assertNotNull($errors['address']['street']);
    $this->assertNull($errors['address']['city']);
  }

  public function testGetErrorWithMultipleLevelsOfNesting() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->contact = new Form();
    $form->contact->email = new Input(); // Required, empty - ERROR
    $form->contact->address = new Form();
    $form->contact->address->street = (new Input())->setValue('Main St');
    $form->contact->address->city = new Input(); // Required, empty - ERROR

    $form->validate();

    $errors = $form->getError();
    $this->assertNull($errors['name']);
    $this->assertIsArray($errors['contact']);
    $this->assertNotNull($errors['contact']['email']);
    $this->assertIsArray($errors['contact']['address']);
    $this->assertNull($errors['contact']['address']['street']);
    $this->assertNotNull($errors['contact']['address']['city']);
  }

  public function testGetErrorWithNestedFormPartiallyDisabled() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->address = new Form();
    $form->address->street = new Input(); // Required, empty
    $form->address->city = new Input(); // Required, empty
    $form->address->city->setDisabled(true);

    $form->validate();

    $errors = $form->getError();
    $this->assertIsArray($errors['address']);
    $this->assertNotNull($errors['address']['street']); // Has error
    $this->assertArrayNotHasKey('city', $errors['address']); // Disabled
  }

  public function testGetErrorBeforeValidation() {
    $form = new Form();
    $form->name = new Input(); // Required, empty but not validated

    // getError() should still return structure
    $errors = $form->getError();
    $this->assertIsArray($errors);
    $this->assertArrayHasKey('name', $errors);
    $this->assertNull($errors['name']); // No error set yet
  }

  public function testDefaultLabelIsEmptyString() {
    $form = new Form();
    $this->assertSame('', $form->getLabel());
  }

  public function testSetLabel() {
    $form = new Form();
    $result = $form->setLabel('User Registration');

    $this->assertSame($form, $result); // Fluent interface
    $this->assertSame('User Registration', $form->getLabel());
  }

  public function testGetLabel() {
    $form = new Form();
    $form->setLabel('Contact Form');
    $this->assertSame('Contact Form', $form->getLabel());
  }

  // Form::isRequired / setRequired tests

  public function testDefaultRequiredIsTrue() {
    $form = new Form();
    $this->assertTrue($form->isRequired());
  }

  public function testSetRequiredFalse() {
    $form = new Form();
    $result = $form->setRequired(false);

    $this->assertSame($form, $result); // Fluent interface
    $this->assertFalse($form->isRequired());
  }

  public function testSetRequiredTrue() {
    $form = new Form();
    $form->setRequired(false);
    $form->setRequired(true);

    $this->assertTrue($form->isRequired());
  }

  // Form::isDisabled / setDisabled tests

  public function testDefaultDisabledIsFalse() {
    $form = new Form();
    $this->assertFalse($form->isDisabled());
  }

  public function testSetDisabledTrue() {
    $form = new Form();
    $result = $form->setDisabled(true);

    $this->assertSame($form, $result); // Fluent interface
    $this->assertTrue($form->isDisabled());
  }

  public function testSetDisabledFalse() {
    $form = new Form();
    $form->setDisabled(true);
    $form->setDisabled(false);

    $this->assertFalse($form->isDisabled());
  }

  public function testDisabledFormReturnsEmptyValue() {
    $form = new Form();
    $form->name = new Input();
    $form->email = new Input();
    $form->setValue(['name' => 'John', 'email' => 'john@example.com']);
    $form->setDisabled(true);

    $this->assertEquals([], $form->getValue());
    $this->assertEquals([], $form->getParsedValue());
    $this->assertEquals([], $form->getFilledValue());
    $this->assertEquals([], $form->getFilledParsedValue());
    $this->assertTrue($form->isEmpty());
  }

  public function testDisabledFormPreservesValueForReEnable() {
    $form = new Form();
    $form->name = new Input();
    $form->email = new Input();
    $form->setValue(['name' => 'John', 'email' => 'john@example.com']);
    $form->setDisabled(true);

    // Disabled: returns empty
    $this->assertEquals([], $form->getValue());
    $this->assertTrue($form->isEmpty());

    // Re-enabled: values restored
    $form->setDisabled(false);
    $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $form->getValue());
    $this->assertFalse($form->isEmpty());
  }
}
