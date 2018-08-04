<?php
use Coroq\Input\Select;

class SelectTest extends PHPUnit_Framework_TestCase {
  public function testValidate() {
    $input = (new Select())->setOptions([
      "a" => "A",
      "b" => "B",
    ]);
    $input->validate();
    $this->assertSame("err_empty", $input->getError()->code);
    $input->setValue("a")->validate();
    $this->assertSame(null, $input->getError());
    $input->setValue("c")->validate();
    $this->assertSame("err_not_in_options", $input->getError()->code);
  }
}
