<?php
use Coroq\Form\FormItem\MultiSelect;
use Coroq\Form\Error\NotInOptionsError;
use Coroq\Form\Error\TooFewSelectionsError;
use Coroq\Form\Error\TooManySelectionsError;
use Coroq\Form\Error\InvalidError;
use PHPUnit\Framework\TestCase;

class MultiSelectTest extends TestCase {
  public function testSetValueWithArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setValue(['a', 'b']);

    $this->assertSame(['a', 'b'], $input->getValue());
  }

  public function testSetValueFiltersEmptyStrings() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', '', 'b']);

    // Should reindex after filtering
    $this->assertSame(['a', 'b'], $input->getValue());
  }

  public function testSetValueFiltersNullValues() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', null, 'b']);

    // Should reindex after filtering
    $this->assertSame(['a', 'b'], $input->getValue());
  }

  public function testSetValueConvertsNonArrayToArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setValue('a');

    $this->assertSame(['a'], $input->getValue());
  }

  public function testSetValueWithEmptyArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setValue([]);

    $this->assertSame([], $input->getValue());
  }

  public function testGetValueReturnsEmptyArrayForNonArrayValue() {
    $input = new MultiSelect();
    $this->assertSame([], $input->getValue());
  }

  public function testClearSetsEmptyArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', 'b']);

    $input->clear();
    $this->assertSame([], $input->getValue());
  }

  public function testIsEmptyReturnsTrueForEmptyArray() {
    $input = (new MultiSelect())->setValue([]);
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmptyArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setValue(['a']);

    $this->assertFalse($input->isEmpty());
  }

  public function testIsEmptyReturnsTrueByDefault() {
    $input = new MultiSelect();
    $this->assertTrue($input->isEmpty());
  }

  public function testValidateWithValidSelections() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setValue(['a', 'c']);

    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithInvalidOption() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', 'x']);

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
  }

  public function testValidateWithMultipleInvalidOptions() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setValue(['x', 'y', 'z']);

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
  }

  public function testValidateWithMinCount() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setMinCount(2)
      ->setValue(['a']);

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooFewSelectionsError::class, $input->getError());
  }

  public function testValidateWithMaxCount() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setMaxCount(2)
      ->setValue(['a', 'b', 'c']);

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(TooManySelectionsError::class, $input->getError());
  }

  public function testValidateWithMinAndMaxCount() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'])
      ->setMinCount(2)
      ->setMaxCount(3)
      ->setValue(['a', 'b']);

    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateExactlyMinCount() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C'])
      ->setMinCount(2)
      ->setValue(['a', 'b']);

    $this->assertTrue($input->validate());
  }

  public function testValidateExactlyMaxCount() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C'])
      ->setMaxCount(2)
      ->setValue(['a', 'b']);

    $this->assertTrue($input->validate());
  }

  public function testValidateEmptyWithRequired() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setRequired(true)
      ->setValue([]);

    $this->assertFalse($input->validate());
    $this->assertNotNull($input->getError());
  }

  public function testValidateEmptyWithNotRequired() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A'])
      ->setRequired(false)
      ->setValue([]);

    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testGetSelectedLabelReturnsLabels() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setValue(['a', 'c']);

    $this->assertSame(['Option A', 'Option C'], $input->getSelectedLabel());
  }

  public function testGetSelectedLabelReturnsEmptyArrayWhenEmpty() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue([]);

    $this->assertSame([], $input->getSelectedLabel());
  }

  public function testGetSelectedLabelSkipsInvalidOptions() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', 'x', 'b']);

    // x is not in options, so it's skipped
    $this->assertSame(['Option A', 'Option B'], $input->getSelectedLabel());
  }

  public function testGetSelectedLabelPreservesOrder() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'])
      ->setValue(['d', 'b', 'a']);

    $this->assertSame(['D', 'B', 'A'], $input->getSelectedLabel());
  }

  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a', 'b']);

    $this->assertSame($input->getValue(), $input->getParsedValue());
    $this->assertSame(['a', 'b'], $input->getParsedValue());
  }

  public function testGetParsedValueReturnsEmptyArrayWhenEmpty() {
    $input = new MultiSelect();
    $this->assertSame([], $input->getParsedValue());
  }

  public function testSetOptionsAndGetOptions() {
    $options = ['key1' => 'Label 1', 'key2' => 'Label 2'];
    $input = (new MultiSelect())->setOptions($options);

    $this->assertSame($options, $input->getOptions());
  }

  public function testFluentInterface() {
    $input = new MultiSelect();
    $result = $input
      ->setOptions(['a' => 'A', 'b' => 'B'])
      ->setMinCount(1)
      ->setMaxCount(2)
      ->setValue(['a']);

    $this->assertSame($input, $result);
    $this->assertSame(['a'], $input->getValue());
  }

  public function testValidateWithIntegerKeys() {
    $input = (new MultiSelect())
      ->setOptions([1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3'])
      ->setValue([1, 3]);

    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testGetSelectedLabelWithIntegerKeys() {
    $input = (new MultiSelect())
      ->setOptions([0 => 'Zero', 1 => 'One', 2 => 'Two'])
      ->setValue([0, 2]);

    $this->assertSame(['Zero', 'Two'], $input->getSelectedLabel());
  }

  public function testSetValueWithMixedTypes() {
    $input = (new MultiSelect())
      ->setOptions(['1' => 'String 1', 1 => 'Int 1', 'a' => 'A'])
      ->setValue(['1', 1, 'a']);

    // PHP arrays will have type coercion for keys, but values should work
    $this->assertSame(['1', 1, 'a'], $input->getValue());
  }

  public function testValidateChecksOptionsBeforeCount() {
    // Invalid option should be reported before count error
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A'])
      ->setMinCount(2)
      ->setValue(['a', 'invalid']);

    $this->assertFalse($input->validate());
    // Should get NotInOptionsError first, not TooFewSelectionsError
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
  }

  public function testValidateCountAfterFilteringInvalidOptions() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B'])
      ->setMinCount(1)
      ->setValue(['a', 'b']);

    // Valid selections with valid count
    $this->assertTrue($input->validate());
  }

  public function testClearAfterValidationClearsError() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A'])
      ->setValue(['invalid']);

    $input->validate();
    $this->assertTrue($input->hasError());

    $input->clear();
    $this->assertSame([], $input->getValue());
  }

  public function testSetValueClearsError() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B'])
      ->setValue(['invalid']);

    $input->validate();
    $this->assertTrue($input->hasError());

    $input->setValue(['a']);
    $this->assertFalse($input->hasError());
  }

  public function testMinCountDefaultIsZero() {
    $input = new MultiSelect();
    $this->assertSame(0, $input->getMinCount());
  }

  public function testMaxCountDefaultIsMaxInt() {
    $input = new MultiSelect();
    $this->assertSame(PHP_INT_MAX, $input->getMaxCount());
  }

  public function testValidateWithZeroSelections() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A'])
      ->setRequired(false)
      ->setValue([]);

    $this->assertTrue($input->validate());
  }

  public function testValidateAllOptions() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C'])
      ->setValue(['a', 'b', 'c']);

    $this->assertTrue($input->validate());
    $this->assertSame(['A', 'B', 'C'], $input->getSelectedLabel());
  }

  public function testSetValueReindexesArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C'])
      ->setValue(['a', 'c']);

    $value = $input->getValue();
    // Should always return sequential array keys
    $this->assertSame(['a', 'c'], $value);
    $this->assertSame([0, 1], array_keys($value));
  }

  public function testValueJsonEncodesAsArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B', 'c' => 'C'])
      ->setValue(['a', '', 'c']);

    $value = $input->getValue();
    $json = json_encode($value);
    // Should encode as JSON array, not object
    $this->assertSame('["a","c"]', $json);
  }

  public function testValidatorIsCalledAfterDoValidate() {
    $validatorCalled = false;
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B', 'c' => 'Option C'])
      ->setValue(['a', 'b'])
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertTrue($validatorCalled);
  }

  public function testValidatorCanReturnError() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['a'])
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidError::class, $input->getError());
  }

  public function testValidatorNotCalledWhenNotInOptions() {
    $validatorCalled = false;
    $input = (new MultiSelect())
      ->setOptions(['a' => 'Option A', 'b' => 'Option B'])
      ->setValue(['c'])
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotInOptionsError::class, $input->getError());
    $this->assertFalse($validatorCalled);
  }

  public function testDisabledMultiSelectReturnsEmptyArray() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B'])
      ->setValue(['a', 'b']);

    $this->assertEquals(['a', 'b'], $input->getValue());
    $this->assertFalse($input->isEmpty());

    $input->setDisabled(true);

    $this->assertEquals([], $input->getValue());
    $this->assertTrue($input->isEmpty());
  }

  public function testDisabledMultiSelectPreservesValueForReEnable() {
    $input = (new MultiSelect())
      ->setOptions(['a' => 'A', 'b' => 'B'])
      ->setValue(['a', 'b'])
      ->setDisabled(true);

    $this->assertEquals([], $input->getValue());

    $input->setDisabled(false);
    $this->assertEquals(['a', 'b'], $input->getValue());
  }
}
