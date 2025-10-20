<?php
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\PatternMismatchError;
use PHPUnit\Framework\TestCase;

class TextInputTest extends TestCase {
  public function testTrim() {
    $ws = " \t\n\r\x00\x0b\xc2\xa0　";
    $sample = "{$ws}T{$ws}T{$ws}";
    $input = (new TextInput())
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false);

    // none
    $input->setTrim(null)->setValue($sample);
    $this->assertSame(bin2hex($sample), bin2hex($input->getValue()));

    // left
    $input->clear()->setTrim(TextInput::LEFT)->setValue($sample);
    $this->assertSame(bin2hex("T{$ws}T{$ws}"), bin2hex($input->getValue()));

    // right
    $input->clear()->setTrim(TextInput::RIGHT)->setValue($sample);
    $this->assertSame(bin2hex("{$ws}T{$ws}T"), bin2hex($input->getValue()));

    // both
    $input->clear()->setTrim(TextInput::BOTH)->setValue($sample);
    $this->assertSame(bin2hex("T{$ws}T"), bin2hex($input->getValue()));
  }

  public function testValidateUtf8() {
    $non_utf8 = mb_convert_encoding('テスト', 'EUC-JP', 'UTF-8');
    $input = (new TextInput())
      ->setTrim(null)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue($non_utf8);
    $input->validate();
    $this->assertInstanceOf(InvalidError::class, $input->getError());
  }

  public function testValidatePattern() {
    $input = (new TextInput())->setPattern('#^[0-9]{10}$#');
    $input->setValue('0')->validate();
    $this->assertInstanceOf(PatternMismatchError::class, $input->getError());

    $input->setValue('0123456789')->validate();
    $this->assertNull($input->getError());
  }

  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new TextInput())->setValue('test');
    $this->assertSame($input->getValue(), $input->getParsedValue());
  }
}
