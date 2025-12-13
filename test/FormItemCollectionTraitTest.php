<?php
use Coroq\Form\FormItemCollectionTrait;
use Coroq\Form\FormInterface;
use Coroq\Form\FormItem\FormItemInterface;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\Form;
use PHPUnit\Framework\TestCase;

// Create a minimal concrete test class that uses FormItemCollectionTrait
class FormItemCollectionTraitTestClass implements FormInterface {
  use FormItemCollectionTrait;

  private array $items = [];
  private bool $disabled = false;
  private bool $required = true;
  private bool $readonly = false;
  private string $label = '';

  public function addItem(string $name, FormItemInterface $item): void {
    $this->items[$name] = $item;
  }

  public function getItems(): array {
    return $this->items;
  }

  public function getItem(mixed $name): ?FormItemInterface {
    return $this->items[$name] ?? null;
  }

  public function setValue(mixed $value): static {
    if ($this->readonly) {
      return $this;
    }
    foreach ($this->items as $name => $item) {
      $item->setValue($value[$name] ?? '');
    }
    return $this;
  }

  public function isDisabled(): bool {
    return $this->disabled;
  }

  public function setDisabled(bool $disabled): static {
    $this->disabled = $disabled;
    return $this;
  }

  public function isRequired(): bool {
    return $this->required;
  }

  public function setRequired(bool $required): static {
    $this->required = $required;
    return $this;
  }

  public function isReadOnly(): bool {
    return $this->readonly;
  }

  public function setReadOnly(bool $readOnly): static {
    $this->readonly = $readOnly;
    return $this;
  }

  public function getLabel(): string {
    return $this->label;
  }

  public function setLabel(string $label): static {
    $this->label = $label;
    return $this;
  }
}

class FormItemCollectionTraitTest extends TestCase {
  // getValue() tests
  public function testGetValueReturnsAllEnabledItemValues() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com'));

    $values = $form->getValue();
    $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $values);
  }

  public function testGetValueExcludesDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com')->setDisabled(true));

    $values = $form->getValue();
    $this->assertEquals(['name' => 'John'], $values);
  }

  // getParsedValue() tests
  public function testGetParsedValueReturnsParsedValues() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('age', (new IntegerInput())->setValue('25'));
    $form->addItem('name', (new TextInput())->setValue('John'));

    $values = $form->getParsedValue();
    $this->assertSame(25, $values['age']);
    $this->assertSame('John', $values['name']);
  }

  public function testGetParsedValueExcludesDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('age', (new IntegerInput())->setValue('25')->setDisabled(true));
    $form->addItem('name', (new TextInput())->setValue('John'));

    $values = $form->getParsedValue();
    $this->assertArrayNotHasKey('age', $values);
    $this->assertArrayHasKey('name', $values);
  }

  // getFilledValue() tests
  public function testGetFilledValueExcludesEmptyItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue(''));
    $form->addItem('phone', (new TextInput())->setValue(''));

    $values = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $values);
  }

  public function testGetFilledValueWithNestedForm() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));

    $nested = new Form();
    $nested->street = (new TextInput())->setValue('Main St');
    $nested->city = (new TextInput())->setValue('');
    $form->addItem('address', $nested);

    $values = $form->getFilledValue();
    $this->assertEquals([
      'name' => 'John',
      'address' => ['street' => 'Main St']
    ], $values);
  }

  public function testGetFilledValueExcludesDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com')->setDisabled(true));

    $values = $form->getFilledValue();
    $this->assertEquals(['name' => 'John'], $values);
  }

  // getFilledParsedValue() tests
  public function testGetFilledParsedValueExcludesEmptyItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('age', (new IntegerInput())->setValue('25'));
    $form->addItem('name', (new TextInput())->setValue(''));

    $values = $form->getFilledParsedValue();
    $this->assertSame(['age' => 25], $values);
  }

  public function testGetFilledParsedValueWithNestedForm() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));

    $nested = new Form();
    $nested->age = (new IntegerInput())->setValue('30');
    $nested->city = (new TextInput())->setValue('');
    $form->addItem('info', $nested);

    $values = $form->getFilledParsedValue();
    $this->assertEquals([
      'name' => 'John',
      'info' => ['age' => 30]
    ], $values);
  }

  // clear() tests
  public function testClearClearsAllItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com'));

    $form->clear();

    $this->assertSame('', $form->getItem('name')->getValue());
    $this->assertSame('', $form->getItem('email')->getValue());
  }

  public function testClearReturnsFluentInterface() {
    $form = new FormItemCollectionTraitTestClass();
    $result = $form->clear();
    $this->assertSame($form, $result);
  }

  // isEmpty() tests
  public function testIsEmptyReturnsTrueWhenAllItemsEmpty() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue(''));
    $form->addItem('email', (new TextInput())->setValue(''));

    $this->assertTrue($form->isEmpty());
  }

  public function testIsEmptyReturnsFalseWhenAnyItemNotEmpty() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue(''));

    $this->assertFalse($form->isEmpty());
  }

  public function testIsEmptyIgnoresDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John')->setDisabled(true));
    $form->addItem('email', (new TextInput())->setValue(''));

    $this->assertTrue($form->isEmpty());
  }

  // validate() tests
  public function testValidateCallsValidateOnAllEnabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('')); // Required, empty - will fail

    $result = $form->validate();

    $this->assertFalse($result);
    $this->assertTrue($form->hasError());
  }

  public function testValidateSkipsWhenOptionalAndEmpty() {
    $form = new FormItemCollectionTraitTestClass();
    $form->setRequired(false);
    $form->addItem('name', (new TextInput())->setValue(''));

    $result = $form->validate();

    $this->assertTrue($result);
    $this->assertFalse($form->hasError());
  }

  public function testValidateIgnoresDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('')->setDisabled(true)); // Disabled, won't be validated

    $result = $form->validate();

    $this->assertTrue($result);
    $this->assertFalse($form->hasError());
  }

  // getError() tests
  public function testGetErrorReturnsErrorsFromAllEnabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue(''));
    $form->addItem('email', (new TextInput())->setValue(''));

    $form->validate();
    $errors = $form->getError();

    $this->assertArrayHasKey('name', $errors);
    $this->assertArrayHasKey('email', $errors);
    $this->assertNotNull($errors['name']);
    $this->assertNotNull($errors['email']);
  }

  public function testGetErrorExcludesDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue(''));
    $form->addItem('email', (new TextInput())->setValue('')->setDisabled(true));

    $form->validate();
    $errors = $form->getError();

    $this->assertArrayHasKey('name', $errors);
    $this->assertArrayNotHasKey('email', $errors);
  }

  // hasError() tests
  public function testHasErrorReturnsTrueWhenAnyItemHasError() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('')); // Required, empty

    $form->validate();

    $this->assertTrue($form->hasError());
  }

  public function testHasErrorReturnsFalseWhenNoErrors() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com'));

    $form->validate();

    $this->assertFalse($form->hasError());
  }

  public function testHasErrorIgnoresDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('')->setDisabled(true)); // Has error but disabled

    $form->validate();

    $this->assertFalse($form->hasError());
  }

  // getEnabledItems() tests
  public function testGetEnabledItemsExcludesDisabledItems() {
    $form = new FormItemCollectionTraitTestClass();
    $form->addItem('name', (new TextInput())->setValue('John'));
    $form->addItem('email', (new TextInput())->setValue('john@example.com')->setDisabled(true));
    $form->addItem('phone', (new TextInput())->setValue('1234567890'));

    // getEnabledItems is protected, but we can test it indirectly via getValue
    $values = $form->getValue();

    $this->assertArrayHasKey('name', $values);
    $this->assertArrayNotHasKey('email', $values);
    $this->assertArrayHasKey('phone', $values);
  }
}
