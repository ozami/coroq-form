<?php
use Coroq\Form\Input\Select;
use PHPUnit\Framework\TestCase;

class SelectTest extends TestCase {
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
    $this->assertSame("err_invalid", $input->getError()->code);
  }
}
