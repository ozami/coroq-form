<?php
use Coroq\Form\Form;
use Coroq\Form\Input;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase {
  public function testGetItemIn() {
    $form = new Form();
    $form->a = (new Input())->setValue("a");
    $form->b = new Form();
    $form->b->c = (new Input())->setValue("c");
    $this->assertSame($form->a, $form->getItemIn("a"));
    $this->assertSame($form->b, $form->getItemIn("b"));
    $this->assertSame($form->b->c, $form->getItemIn("b/c"));
  }

  public function testGetItemThrowsExceptionIfNoItem() {
    $this->expectException(LogicException::class);
    $form = new Form();
    $form->getItem("a");
  }
  
  public function testGetValueCollectsValuesOnlyFromEnabledItems() {
    $form = new Form();
    $form->a = (new Input())->setValue("a");
    $form->b = (new Input())->setValue("b")->setDisabled(true);
    $this->assertEquals(["a" => "a"], $form->getValue());
  }
  
  public function testSetValue() {
    $form = new Form();
    $form->a = (new Input())->setValue("a");
    $form->b = (new Input())->setValue("b");
    $form->c = new Form();
    $form->c->d = (new Input())->setValue("d");
    $form->c->e = (new Input())->setValue("e");
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
