<?php
use Coroq\Input;
use Coroq\Input\Computed;

class Sum extends Computed {
  public function computeValue(array $source_values) {
    return array_sum($source_values);
  }
}

class ComputedTest extends PHPUnit_Framework_TestCase {
  public function testGetValue() {
    $input1 = new Input();
    $input1->setValue(1);
    $input2 = new Input();
    $input2->setValue(2);
    $sum = new Sum();
    $sum->addSourceInput($input1);
    $sum->addSourceInput($input2);
    $this->assertSame(3, $sum->getValue());
  }

  public function testReadOnly() {
    $sum = new Sum();
    $this->assertTrue($sum->isReadOnly());
  }
}
