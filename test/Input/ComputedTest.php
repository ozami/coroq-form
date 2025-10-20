<?php
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\Computed;
use PHPUnit\Framework\TestCase;

class ComputedTest extends TestCase {
  public function testGetValue() {
    $input1 = (new Input())->setValue('1');
    $input2 = (new Input())->setValue('2');
    $sum = (new Computed())
      ->setComputation(function(array $values) {
        return (int)$values[0] + (int)$values[1];
      })
      ->addSourceInput($input1)
      ->addSourceInput($input2);

    $this->assertSame(3, $sum->getValue());
  }

  public function testReadOnly() {
    $sum = new Computed();
    $this->assertTrue($sum->isReadOnly());
  }

  public function testGetValueReturnsNullWhenSourceInputHasError() {
    $input1 = new Input();  // Required, empty - will fail validation
    $input2 = (new Input())->setValue('test');

    $computed = (new Computed())
      ->setComputation(function(array $values) {
        return implode('', $values);
      })
      ->addSourceInput($input1)
      ->addSourceInput($input2);

    // Source input1 is invalid, so computed value should be null
    $this->assertNull($computed->getValue());
  }

  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new Input())->setValue('test');
    $computed = (new Computed())
      ->setComputation(function(array $values) {
        return strtoupper($values[0]);
      })
      ->addSourceInput($input);

    $this->assertSame($computed->getValue(), $computed->getParsedValue());
  }
}
