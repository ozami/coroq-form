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

    $this->assertEquals(3, count($repeating->getItems()));
    $this->assertEquals(['a@example.com', 'b@example.com', 'c@example.com'], $repeating->getValue());
  }

  public function testSetValueRebuildsAllItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);
    $this->assertEquals(2, count($repeating->getItems()));

    $repeating->setValue(['x@example.com', 'y@example.com', 'z@example.com']);
    $this->assertEquals(3, count($repeating->getItems()));
    $this->assertEquals(['x@example.com', 'y@example.com', 'z@example.com'], $repeating->getValue());
  }

  public function testSetValueNormalizesNonArrayToEmptyArray() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue('not an array');
    $this->assertEquals(0, count($repeating->getItems()));
    $this->assertEquals([], $repeating->getValue());
  }

  public function testSetValueEnsuresSequentialIndices() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new TextInput());
    // Input with gaps (associative array)
    $repeating->setValue([0 => 'first', 5 => 'second', 10 => 'third']);

    // Should be reindexed to 0, 1, 2
    $this->assertEquals(3, count($repeating->getItems()));
    $this->assertEquals(['first', 'second', 'third'], $repeating->getValue());
  }

  public function testMinItemCountEnsuresMinimumItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMinItemCount(3);

    $repeating->setValue(['a@example.com']);
    $this->assertEquals(3, count($repeating->getItems()));
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
    $this->assertEquals(2, count($repeating->getItems()));
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

  public function testIsEmptyReturnsTrueWhenNoItems() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $this->assertTrue($repeating->isEmpty());
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

  public function testReadOnlyPreventsSetValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com']);
    $repeating->setReadOnly(true);

    $repeating->setValue(['b@example.com', 'c@example.com']);

    // Value should not change
    $this->assertEquals(1, count($repeating->getItems()));
    $this->assertEquals(['a@example.com'], $repeating->getValue());
  }

  public function testDisabledRepeatingFormReturnsEmptyAndRestoresOnReEnable() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setValue(['a@example.com', 'b@example.com']);
    $repeating->setDisabled(true);

    // Disabled items return empty value
    $this->assertEquals([], $repeating->getValue());
    $this->assertTrue($repeating->isEmpty());

    // Value is preserved and restored when re-enabled
    $repeating->setDisabled(false);
    $this->assertEquals(['a@example.com', 'b@example.com'], $repeating->getValue());
    $this->assertFalse($repeating->isEmpty());
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

    $this->assertEquals(2, count($repeating->getItems()));
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

  // Additional coverage tests

  public function testGetParsedValueWithMinItemCount() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new IntegerInput());
    $repeating->setMinItemCount(3);

    // No setValue called - getParsedValue should ensure minimum items
    $parsed = $repeating->getParsedValue();
    $this->assertEquals(3, count($parsed));
    $this->assertNull($parsed[0]); // Empty values parse to null
    $this->assertNull($parsed[1]);
    $this->assertNull($parsed[2]);
  }


  public function testGetFilledParsedValueWithNestedForms() {
    $repeating = (new RepeatingForm())->setFactory(function(int $i) {
      $form = new Form();
      $form->age = new IntegerInput();
      $form->active = new BooleanInput();
      return $form;
    });

    $repeating->setValue([
      ['age' => '25', 'active' => 'on'],
      ['age' => '', 'active' => ''],
      ['age' => '30', 'active' => ''],
    ]);

    $filled = $repeating->getFilledParsedValue();
    $this->assertCount(2, $filled);
    $this->assertIsArray($filled[0]);
    $this->assertSame(25, $filled[0]['age']);
    $this->assertTrue($filled[0]['active']);
    $this->assertIsArray($filled[2]);
    $this->assertSame(30, $filled[2]['age']);
  }

  public function testSetValueWithNonArrayValue() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());
    $repeating->setMinItemCount(2);

    // setValue with null should treat as empty array
    $repeating->setValue(null);
    $this->assertEquals(2, count($repeating->getItems())); // minItemCount
    $this->assertEquals(['', ''], $repeating->getValue());

    // setValue with string should treat as empty array
    $repeating->setValue('not-an-array');
    $this->assertEquals(2, count($repeating->getItems()));
    $this->assertEquals(['', ''], $repeating->getValue());

    // setValue with int should treat as empty array
    $repeating->setValue(123);
    $this->assertEquals(2, count($repeating->getItems()));
    $this->assertEquals(['', ''], $repeating->getValue());
  }

  public function testSetValueWithoutFactoryThrowsException() {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Factory not set');

    $repeating = new RepeatingForm();
    $repeating->setValue(['value']);
  }

  public function testIsDisabled() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());

    $this->assertFalse($repeating->isDisabled());

    $repeating->setDisabled(true);
    $this->assertTrue($repeating->isDisabled());

    $repeating->setDisabled(false);
    $this->assertFalse($repeating->isDisabled());
  }

  public function testIsRequired() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());

    // Default is required
    $this->assertTrue($repeating->isRequired());

    $repeating->setRequired(false);
    $this->assertFalse($repeating->isRequired());

    $repeating->setRequired(true);
    $this->assertTrue($repeating->isRequired());
  }

  public function testIsReadOnly() {
    $repeating = (new RepeatingForm())->setFactory(fn(int $i) => new EmailInput());

    $this->assertFalse($repeating->isReadOnly());

    $repeating->setReadOnly(true);
    $this->assertTrue($repeating->isReadOnly());

    $repeating->setReadOnly(false);
    $this->assertFalse($repeating->isReadOnly());
  }
}
