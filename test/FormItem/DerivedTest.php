<?php
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\Derived;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\SourceItemInvalidError;
use PHPUnit\Framework\TestCase;

class DerivedTest extends TestCase {
  // Calculator tests
  public function testGetValue() {
    $input1 = (new Input())->setValue('1');
    $input2 = (new Input())->setValue('2');
    $sum = (new Derived())
      ->setValueCalculator(function($val1, $val2) {
        return (int)$val1 + (int)$val2;
      })
      ->addSource($input1)
      ->addSource($input2);

    $this->assertSame(3, $sum->getValue());
  }

  public function testGetValueWithThreeSources() {
    $input1 = (new Input())->setValue('a');
    $input2 = (new Input())->setValue('b');
    $input3 = (new Input())->setValue('c');
    $derived = (new Derived())
      ->setValueCalculator(function($val1, $val2, $val3) {
        return $val1 . $val2 . $val3;
      })
      ->addSource($input1)
      ->addSource($input2)
      ->addSource($input3);

    $this->assertSame('abc', $derived->getValue());
  }

  public function testGetValueCalculatesEvenWhenSourceHasError() {
    $input1 = new Input();  // Required, empty - will fail validation
    $input2 = (new Input())->setValue('test');

    $derived = (new Derived())
      ->setValueCalculator(function($val1, $val2) {
        return $val1 . $val2;
      })
      ->addSource($input1)
      ->addSource($input2);

    // getValue() should calculate regardless of source validity
    // Calculator receives empty string from input1 and 'test' from input2
    $this->assertSame('test', $derived->getValue());
  }

  public function testGetValueReturnsNullWhenNoCalculator() {
    $input = (new Input())->setValue('test');
    $derived = (new Derived())
      ->addSource($input);

    $this->assertNull($derived->getValue());
  }

  public function testGetValueRecalculatesEveryTime() {
    $input = (new Input())->setValue('test');
    $derived = (new Derived())
      ->setValueCalculator(function($val) {
        return strtoupper($val);
      })
      ->addSource($input);

    $this->assertSame('TEST', $derived->getValue());

    // Change source value
    $input->setValue('changed');

    // Should recalculate, not return cached value
    $this->assertSame('CHANGED', $derived->getValue());
  }

  // Readonly tests
  public function testReadOnly() {
    $sum = new Derived();
    $this->assertTrue($sum->isReadOnly());
  }

  public function testSetValueDoesNothing() {
    $input = (new Input())->setValue('original');
    $derived = (new Derived())
      ->setValueCalculator(function($val) {
        return $val;
      })
      ->addSource($input);

    $derived->setValue('ignored');

    $this->assertSame('original', $derived->getValue());
  }

  public function testSetReadOnlyDoesNothing() {
    $derived = new Derived();
    $derived->setReadOnly(false);

    $this->assertTrue($derived->isReadOnly());
  }

  // Validator tests
  public function testValidatorReceivesFlatArguments() {
    $input1 = (new Input())->setValue('password');
    $input2 = (new Input())->setValue('password');

    $validatorCalled = false;
    $receivedArgs = [];

    $derived = (new Derived())
      ->setValidator(function($val1, $val2, $calculated) use (&$validatorCalled, &$receivedArgs) {
        $validatorCalled = true;
        $receivedArgs = [$val1, $val2, $calculated];
        return null;
      })
      ->addSource($input1)
      ->addSource($input2);

    $derived->validate();

    $this->assertTrue($validatorCalled);
    $this->assertSame('password', $receivedArgs[0]);
    $this->assertSame('password', $receivedArgs[1]);
    $this->assertNull($receivedArgs[2]); // No calculator, so null
  }

  public function testValidatorReceivesCalculatedValueAsLastArgument() {
    $input1 = (new Input())->setValue('Hello');
    $input2 = (new Input())->setValue('World');

    $receivedCalculated = null;

    $derived = (new Derived())
      ->setValueCalculator(function($val1, $val2) {
        return $val1 . ' ' . $val2;
      })
      ->setValidator(function($val1, $val2, $calculated) use (&$receivedCalculated) {
        $receivedCalculated = $calculated;
        return null;
      })
      ->addSource($input1)
      ->addSource($input2);

    $derived->validate();

    $this->assertSame('Hello World', $receivedCalculated);
  }

  public function testValidatorReturningErrorSetsError() {
    $input1 = (new Input())->setValue('password');
    $input2 = (new Input())->setValue('different');

    $derived = (new Derived())
      ->setValidator(function($val1, $val2, $calculated) use (&$derived) {
        if ($val1 !== $val2) {
          return new EmptyError($derived); // Using EmptyError as example
        }
        return null;
      })
      ->addSource($input1)
      ->addSource($input2);

    $result = $derived->validate();

    $this->assertFalse($result);
    $this->assertInstanceOf(EmptyError::class, $derived->getError());
  }

  public function testValidatorReturningNullPassesValidation() {
    $input1 = (new Input())->setValue('password');
    $input2 = (new Input())->setValue('password');

    $derived = (new Derived())
      ->setValidator(function($val1, $val2, $calculated) {
        return $val1 === $val2 ? null : new EmptyError($this);
      })
      ->addSource($input1)
      ->addSource($input2);

    $result = $derived->validate();

    $this->assertTrue($result);
    $this->assertNull($derived->getError());
  }

  // SourceItemInvalidError tests
  public function testValidateSetsSourceItemInvalidErrorWhenSourceFails() {
    $input1 = new Input(); // Required, empty - will fail
    $input2 = (new Input())->setValue('test');

    $derived = (new Derived())
      ->setValidator(function($val1, $val2, $calculated) {
        return null; // Validator won't be called
      })
      ->addSource($input1)
      ->addSource($input2);

    $result = $derived->validate();

    $this->assertFalse($result);
    $this->assertInstanceOf(SourceItemInvalidError::class, $derived->getError());
  }

  public function testValidateWithoutValidatorReturnsTrueWhenSourcesValid() {
    $input = (new Input())->setValue('test');

    $derived = (new Derived())
      ->addSource($input);

    $result = $derived->validate();

    $this->assertTrue($result);
    $this->assertNull($derived->getError());
  }

  public function testValidateWithoutValidatorReturnsFalseWhenSourceInvalid() {
    $input = new Input(); // Required, empty - will fail

    $derived = (new Derived())
      ->addSource($input);

    $result = $derived->validate();

    $this->assertFalse($result);
    $this->assertInstanceOf(SourceItemInvalidError::class, $derived->getError());
  }

  // Other methods
  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new Input())->setValue('test');
    $derived = (new Derived())
      ->setValueCalculator(function($val) {
        return strtoupper($val);
      })
      ->addSource($input);

    $this->assertSame($derived->getValue(), $derived->getParsedValue());
  }

  public function testIsEmpty() {
    $input = (new Input())->setValue('test');
    $derived = (new Derived())
      ->setValueCalculator(function($val) {
        return $val;
      })
      ->addSource($input);

    $this->assertFalse($derived->isEmpty());

    // Change to empty value
    $input->setValue('');
    $this->assertTrue($derived->isEmpty());
  }

  public function testClearRemovesError() {
    $input = new Input(); // Required, empty
    $derived = (new Derived())
      ->addSource($input);

    $derived->validate(); // Sets SourceItemInvalidError
    $this->assertNotNull($derived->getError());

    $derived->clear();
    $this->assertNull($derived->getError());
  }
}
