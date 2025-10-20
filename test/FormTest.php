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
}
