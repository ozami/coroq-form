<?php
use Coroq\Form\RepeatingForm;
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\BooleanInput;
use PHPUnit\Framework\TestCase;

class RepeatingFormTest extends TestCase {
  public function testSetFactory() {
    $repeating = (new RepeatingForm())
      ->setFactory(fn(int $i) => new EmailInput());
    $this->assertInstanceOf(RepeatingForm::class, $repeating);
  }

  public function testSetValueCreatesItemsFromFactory() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com', 'c@example.com']);

    $this->assertEquals(3, $repeating->count());
    $this->assertEquals(['a@example.com', 'b@example.com', 'c@example.com'], $repeating->getValue());
  }

  public function testSetValueRebuildsAllItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);
    $this->assertEquals(2, $repeating->count());

    $repeating->setValue(['x@example.com', 'y@example.com', 'z@example.com']);
    $this->assertEquals(3, $repeating->count());
    $this->assertEquals(['x@example.com', 'y@example.com', 'z@example.com'], $repeating->getValue());
  }

  public function testSetValueNormalizesNonArrayToEmptyArray() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue('not an array');
    $this->assertEquals(0, $repeating->count());
    $this->assertEquals([], $repeating->getValue());
  }

  public function testSetValueEnsuresSequentialIndices() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new TextInput());
    // Input with gaps (associative array)
    $repeating->setValue([0 => 'first', 5 => 'second', 10 => 'third']);

    // Should be reindexed to 0, 1, 2
    $this->assertEquals(3, $repeating->count());
    $this->assertEquals(['first', 'second', 'third'], $repeating->getValue());
  }

  public function testMinItemCountEnsuresMinimumItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMinItemCount(3);

    $repeating->setValue(['a@example.com']);
    $this->assertEquals(3, $repeating->count());
    $this->assertEquals(['a@example.com', '', ''], $repeating->getValue());
  }

  public function testMinItemCountInGetValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMinItemCount(3);

    // No setValue called
    $values = $repeating->getValue();
    $this->assertEquals(3, count($values));
  }

  public function testMaxItemCountLimitsItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMaxItemCount(2);

    $repeating->setValue(['a@example.com', 'b@example.com', 'c@example.com', 'd@example.com']);
    $this->assertEquals(2, $repeating->count());
    $this->assertEquals(['a@example.com', 'b@example.com'], $repeating->getValue());
  }

  public function testFactoryReceivesCorrectIndex() {
    $receivedIndices = [];
    $repeating = (new RepeatingForm())->setFactory(function(int $i) use (&$receivedIndices) {
      $receivedIndices[] = $i;
      return new TextInput();
    });

    $repeating->setValue(['a', 'b', 'c']);
    $this->assertEquals([0, 1, 2], $receivedIndices);
  }

  public function testFactoryCanSetDifferentRequiredPerIndex() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      $input = new EmailInput();
      $input->setRequired($i === 0); // Only first is required
      return $input;
    });

    $repeating->setValue(['', '', '']);

    $valid = $repeating->validate();
    $this->assertFalse($valid);

    // First item should have error
    $errors = $repeating->getError();
    $this->assertNotNull($errors[0]);
    $this->assertNull($errors[1]);
    $this->assertNull($errors[2]);
  }

  public function testFactoryCanSetDifferentLabelsPerIndex() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      $labels = ['Primary', 'Secondary', 'Tertiary'];
      return (new EmailInput())->setLabel($labels[$i] ?? "Email #$i");
    });

    $repeating->setValue(['', '', '']);

    $this->assertEquals('Primary', $repeating->getItem(0)->getLabel());
    $this->assertEquals('Secondary', $repeating->getItem(1)->getLabel());
    $this->assertEquals('Tertiary', $repeating->getItem(2)->getLabel());
  }

  public function testClearClearsAllItemValues() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new TextInput());
    $repeating->setValue(['a', 'b', 'c']);

    $repeating->clear();

    $this->assertEquals(3, $repeating->count()); // Items still exist
    $this->assertEquals(['', '', ''], $repeating->getValue());
  }

  public function testIsEmptyReturnsTrueWhenNoItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $this->assertTrue($repeating->isEmpty());
  }

  public function testIsEmptyReturnsTrueWhenAllItemsEmpty() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['', '', '']);
    $this->assertTrue($repeating->isEmpty());
  }

  public function testIsEmptyReturnsFalseWhenAnyItemHasValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['', 'a@example.com', '']);
    $this->assertFalse($repeating->isEmpty());
  }

  public function testValidateSkipsWhenOptionalAndEmpty() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setRequired(false);
    $repeating->setValue([]);

    $this->assertTrue($repeating->validate());
  }

  public function testValidateValidatesEachItem() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => (new EmailInput())->setRequired(true));
    $repeating->setValue(['valid@example.com', 'invalid-email', '']);

    $valid = $repeating->validate();
    $this->assertFalse($valid);

    $errors = $repeating->getError();
    $this->assertNull($errors[0]); // valid
    $this->assertNotNull($errors[1]); // invalid format
    $this->assertNotNull($errors[2]); // empty
  }

  public function testGetErrorReturnsArrayOfItemErrors() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => (new EmailInput())->setRequired(true));
    $repeating->setValue(['a@example.com', '', 'c@example.com']);
    $repeating->validate();

    $errors = $repeating->getError();
    $this->assertIsArray($errors);
    $this->assertCount(3, $errors);
    $this->assertNull($errors[0]);
    $this->assertNotNull($errors[1]);
    $this->assertNull($errors[2]);
  }

  public function testHasErrorReturnsTrueWhenAnyItemHasError() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => (new EmailInput())->setRequired(true));
    $repeating->setValue(['valid@example.com', '']);
    $repeating->validate();

    $this->assertTrue($repeating->hasError());
  }

  public function testHasErrorReturnsFalseWhenNoErrors() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => (new EmailInput())->setRequired(true));
    $repeating->setValue(['a@example.com', 'b@example.com']);
    $repeating->validate();

    $this->assertFalse($repeating->hasError());
  }

  public function testGetItemReturnsItemAtIndex() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);

    $item = $repeating->getItem(1);
    $this->assertInstanceOf(EmailInput::class, $item);
    $this->assertEquals('b@example.com', $item->getValue());
  }

  public function testGetItemReturnsNullForInvalidIndex() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com']);

    $this->assertNull($repeating->getItem(5));
    $this->assertNull($repeating->getItem(-1));
  }

  public function testGetItemsReturnsAllItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);

    $items = $repeating->getItems();
    $this->assertIsArray($items);
    $this->assertCount(2, $items);
    $this->assertInstanceOf(EmailInput::class, $items[0]);
    $this->assertInstanceOf(EmailInput::class, $items[1]);
  }

  public function testAddItemCreatesNewItemFromFactory() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com']);

    $item = $repeating->addItem('b@example.com');

    $this->assertEquals(2, $repeating->count());
    $this->assertInstanceOf(EmailInput::class, $item);
    $this->assertEquals('b@example.com', $item->getValue());
  }

  public function testAddItemWithoutValueCreatesEmptyItem() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());

    $item = $repeating->addItem();

    $this->assertEquals(1, $repeating->count());
    $this->assertEquals('', $item->getValue());
  }

  public function testGetParsedValueReturnsTypedValues() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new IntegerInput());
    $repeating->setValue(['10', '20', '30']);

    $parsed = $repeating->getParsedValue();
    $this->assertSame(10, $parsed[0]);
    $this->assertSame(20, $parsed[1]);
    $this->assertSame(30, $parsed[2]);
  }

  public function testGetFilledValueExcludesEmptyItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', '', 'c@example.com', '']);

    $filled = $repeating->getFilledValue();
    $this->assertEquals([0 => 'a@example.com', 2 => 'c@example.com'], $filled);
  }

  public function testGetFilledParsedValueExcludesEmptyItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new IntegerInput());
    $repeating->setValue(['10', '', '30']);

    $filled = $repeating->getFilledParsedValue();
    $this->assertEquals([0 => 10, 2 => 30], $filled);
  }

  public function testGetFilledValueWithNestedForms() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      $form = new Form();
      $form->name = new TextInput();
      $form->email = new EmailInput();
      return $form;
    });

    $repeating->setValue([
      ['name' => 'John', 'email' => 'john@example.com'],
      ['name' => '', 'email' => ''],
      ['name' => 'Jane', 'email' => ''],
    ]);

    $filled = $repeating->getFilledValue();
    $this->assertCount(2, $filled);
    $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $filled[0]);
    $this->assertEquals(['name' => 'Jane'], $filled[2]);
  }

  public function testDisabledItemsExcludedFromGetValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com', 'c@example.com']);

    $repeating->getItem(1)->setDisabled(true);

    $values = $repeating->getValue();
    $this->assertEquals([0 => 'a@example.com', 2 => 'c@example.com'], $values);
  }

  public function testReadOnlyPreventsSetValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com']);
    $repeating->setReadOnly(true);

    $repeating->setValue(['b@example.com', 'c@example.com']);

    // Value should not change
    $this->assertEquals(1, $repeating->count());
    $this->assertEquals(['a@example.com'], $repeating->getValue());
  }

  public function testDisabledRepeatingFormStillReturnsValues() {
    // Disabled state is used by parent forms to filter children
    // The form itself doesn't filter when disabled
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);
    $repeating->setDisabled(true);

    // Still returns values (same behavior as Form)
    $this->assertEquals(['a@example.com', 'b@example.com'], $repeating->getValue());
  }

  public function testDisabledRepeatingFormFilteredByParent() {
    $form = new Form();
    $form->emails = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $form->names = (new RepeatingForm())->setFactory(fn(int $i) => new TextInput());

    $form->setValue([
      'emails' => ['a@example.com', 'b@example.com'],
      'names' => ['Alice', 'Bob'],
    ]);

    $form->emails->setDisabled(true);

    // Parent form should exclude disabled child
    $values = $form->getValue();
    $this->assertArrayNotHasKey('emails', $values);
    $this->assertEquals(['Alice', 'Bob'], $values['names']);
  }

  public function testNestedRepeatingForms() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      return (new RepeatingForm())->setFactory(fn(int $j) => new TextInput());
    });

    $repeating->setValue([
      ['a', 'b'],
      ['c', 'd', 'e'],
    ]);

    $this->assertEquals(2, $repeating->count());
    $this->assertEquals(['a', 'b'], $repeating->getItem(0)->getValue());
    $this->assertEquals(['c', 'd', 'e'], $repeating->getItem(1)->getValue());
  }

  public function testComplexFactoryWithBusinessLogic() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      $email = new EmailInput();

      if ($i === 0) {
        $email->setLabel('Primary Email')->setRequired(true);
      } elseif ($i === 1) {
        $email->setLabel('Secondary Email')->setRequired(false);
      } else {
        $email->setLabel('Additional Email #' . ($i - 1))->setRequired(false);
      }

      return $email;
    });

    $repeating->setValue(['', 'second@example.com', '']);
    $repeating->validate();

    // First should have error (required but empty)
    $errors = $repeating->getError();
    $this->assertNotNull($errors[0]);
    $this->assertNull($errors[1]);
    $this->assertNull($errors[2]);

    // Check labels
    $this->assertEquals('Primary Email', $repeating->getItem(0)->getLabel());
    $this->assertEquals('Secondary Email', $repeating->getItem(1)->getLabel());
    $this->assertEquals('Additional Email #1', $repeating->getItem(2)->getLabel());
  }

  public function testGetMinItemCount() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMinItemCount(5);
    $this->assertEquals(5, $repeating->getMinItemCount());
  }

  public function testGetMaxItemCount() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMaxItemCount(10);
    $this->assertEquals(10, $repeating->getMaxItemCount());
  }

  public function testCountReturnsNumberOfItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $this->assertEquals(0, $repeating->count());

    $repeating->setValue(['a@example.com', 'b@example.com']);
    $this->assertEquals(2, $repeating->count());
  }

  public function testGetItemAcceptsMixedType() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);

    // Valid integer indices
    $this->assertNotNull($repeating->getItem(0));
    $this->assertNotNull($repeating->getItem(1));

    // Non-integer should return null
    $this->assertNull($repeating->getItem('string'));
    $this->assertNull($repeating->getItem(null));
    $this->assertNull($repeating->getItem(1.5));

    // Out of bounds integer should return null
    $this->assertNull($repeating->getItem(10));
  }

  public function testGetItemWithFormInterface() {
    // Test that getItem works through FormInterface for generic code
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com']);

    $formInterface = $repeating; // Typed as FormInterface
    $item = $formInterface->getItem(0);
    $this->assertInstanceOf(\Coroq\Form\FormItem\EmailInput::class, $item);
    $this->assertEquals('a@example.com', $item->getValue());
  }
}
