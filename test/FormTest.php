<?php
use Coroq\Form\Form;
use Coroq\Form\Input;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase {
  public function testGetItemIn() {
    $form = new Form();
    $a = (new Input())->setValue("a");
    $form->setItem("a", $a);
    $c = (new Input())->setValue("c");
    $b = (new Form())->setItem("c", $c);
    $form->setItem("b", $b);
    $this->assertSame($a, $form->getItemIn("a"));
    $this->assertSame($b, $form->getItemIn("b"));
    $this->assertSame($c, $form->getItemIn("b/c"));
  }

  public function testGetItemThrowsExceptionIfNoItem() {
    $this->expectException(LogicException::class);
    $form = new Form();
    $form->getItem("a");
  }
  
  public function testGetValueCollectsValuesOnlyFromEnabledItems() {
    $form = new Form();
    $form->setItem("a", (new Input())->setValue("a"));
    $form->setItem("b", (new Input())->setValue("b")->disable());
    $this->assertEquals(["a" => "a"], $form->getValue());
  }
  
  public function testSetValue() {
    $form = new Form();
    $form->setItem("a", (new Input())->setValue("a"));
    $form->setItem("b", (new Input())->setValue("b"));
    $form->setItem("c", new Form());
    $form->getItem("c")->setItem("d", (new Input())->setValue("d"));
    $form->getItem("c")->setItem("e", (new Input())->setValue("e"));
    $form->setValue([
      "a" => "A",
      "c" => [
        "d" => "D",
      ],
      "f" => "F",
      "g" => [
        "h" => "H",
      ],
    ]);
    $this->assertEquals([
      "a" => "A",
      "b" => null,
      "c" => [
        "d" => "D",
        "e" => null,
      ],
    ], $form->getValue());
  }
}
