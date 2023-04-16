<?php
use Coroq\Form\Input\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase {
  public function testTrim() {
    $ws = " \t\n\r\x00\x0b\xc2\xa0　";
    $sample = "{$ws}T{$ws}T{$ws}";
    $input = (new Text())
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false);
    // none
    $input->setTrim(null)->setValue($sample);
    $this->assertSame(bin2hex($sample), bin2hex($input->getValue()));
    // left
    $input->clear()->setTrim(Text::LEFT)->setValue($sample);
    $this->assertSame(bin2hex("T{$ws}T{$ws}"), bin2hex($input->getValue()));
    // right
    $input->clear()->setTrim(Text::RIGHT)->setValue($sample);
    $this->assertSame(bin2hex("{$ws}T{$ws}T"), bin2hex($input->getValue()));
    // both
    $input->clear()->setTrim(Text::BOTH)->setValue($sample);
    $this->assertSame(bin2hex("T{$ws}T"), bin2hex($input->getValue()));
  }

  public function testValidateUtf8() {
    $non_utf8 = mb_convert_encoding("テスト", "EUC-JP", "UTF-8");
    $input = (new Text())
      ->setTrim(null)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue($non_utf8);
    $input->validate();
    $this->assertSame("err_invalid", $input->getError()->code);
  }

  public function testValidate() {
    $input = (new Text())->setPattern("#^[0-9]{10}+$#");
    $input->setValue("0")->validate();
    $this->assertSame("err_invalid", $input->getError()->code);
    $input->setValue("0123456789")->validate();
    $this->assertSame(null, $input->getError());
  }
}
