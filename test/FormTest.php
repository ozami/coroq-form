<?php
use Coroq\Form\Form;
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\BooleanInput;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase {
  public function testGetValueCollectsValuesFromEnabledItems() {
    $form = new Form();
    $form->a = (new Input())->setValue('value_a');
    $form->b = (new Input())->setValue('value_b');
    $form->c = (new Input())->setValue('value_c')->setDisabled(true);

    $this->assertEquals(['a' => 'value_a', 'b' => 'value_b'], $form->getValue());
  }

  public function testGetParsedValue() {
    $form = new Form();
    $form->age = (new IntegerInput())->setValue('25');
    $form->newsletter = (new BooleanInput())->setValue('on');

    $parsed = $form->getParsedValue();
    $this->assertSame(25, $parsed['age']);
    $this->assertSame(true, $parsed['newsletter']);
  }

  public function testGetFilledValue() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->email = (new Input())->setValue('');
    $form->phone = (new Input())->setValue('');

    $filled = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $filled);
  }

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

  public function testGetFilledValueWithDisabledItems() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->email = (new Input())->setValue('john@example.com')->setDisabled(true);
    $form->phone = (new Input())->setValue('');

    // Disabled items should be excluded
    $filled = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $filled);
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

  public function testGetFilledParsedValue() {
    $form = new Form();
    $form->age = (new IntegerInput())->setValue('30');
    $form->newsletter = (new BooleanInput())->setValue('');
    $form->notes = (new Input())->setValue('');

    $filled = $form->getFilledParsedValue();
    $this->assertSame(30, $filled['age']);
    $this->assertArrayNotHasKey('newsletter', $filled);
    $this->assertArrayNotHasKey('notes', $filled);
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

  public function testValidateRequired() {
    $form = new Form();
    $form->name = new Input();

    $this->assertFalse($form->validate());
    $this->assertTrue($form->hasError());

    $form->name->setValue('John');
    $this->assertTrue($form->validate());
    $this->assertFalse($form->hasError());
  }

  public function testValidateOptional() {
    $form = new Form();
    $form->name = new Input();
    $form->setRequired(false);

    // Empty optional form passes validation
    $this->assertTrue($form->validate());
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

  public function testClearClearsAllItems() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->email = (new Input())->setValue('john@example.com');
    $form->age = (new IntegerInput())->setValue('30');

    $form->clear();

    $this->assertSame('', $form->name->getValue());
    $this->assertSame('', $form->email->getValue());
    $this->assertSame('', $form->age->getValue());
  }

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

  public function testClearReturnsFluentInterface() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');

    $result = $form->clear();

    $this->assertSame($form, $result);
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

  public function testGetErrorReturnsEmptyArrayWhenNoErrors() {
    $form = new Form();
    $form->name = (new Input())->setValue('John');
    $form->email = (new Input())->setValue('john@example.com');

    $form->validate();

    $errors = $form->getError();
    $this->assertIsArray($errors);
    $this->assertArrayHasKey('name', $errors);
    $this->assertArrayHasKey('email', $errors);
    $this->assertNull($errors['name']);
    $this->assertNull($errors['email']);
  }

  public function testGetErrorReturnsErrorsFromItems() {
    $form = new Form();
    $form->name = new Input(); // Required, empty
    $form->email = (new Input())->setValue('john@example.com');

    $form->validate();

    $errors = $form->getError();
    $this->assertNotNull($errors['name']);
    $this->assertInstanceOf(\Coroq\Form\Error\Error::class, $errors['name']);
    $this->assertNull($errors['email']);
  }

  public function testGetErrorExcludesDisabledItems() {
    $form = new Form();
    $form->name = new Input(); // Required, empty
    $form->email = (new Input())->setDisabled(true); // Required, empty, but disabled

    $form->validate();

    $errors = $form->getError();
    $this->assertArrayHasKey('name', $errors);
    $this->assertArrayNotHasKey('email', $errors); // Disabled items excluded
  }

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

  public function testGetErrorReturnsOnlyEnabledItemErrors() {
    $form = new Form();
    $form->name = new Input(); // Required, empty
    $form->email = new Input(); // Required, empty
    $form->phone = new Input(); // Required, empty
    $form->email->setDisabled(true);

    $form->validate();

    $errors = $form->getError();
    $this->assertArrayHasKey('name', $errors);
    $this->assertArrayNotHasKey('email', $errors); // Disabled
    $this->assertArrayHasKey('phone', $errors);
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
}
