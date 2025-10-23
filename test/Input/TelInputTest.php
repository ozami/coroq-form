<?php
use Coroq\Form\FormItem\TelInput;
use PHPUnit\Framework\TestCase;

class TelInputTest extends TestCase {
  public function testFilterRemovesNonNumericCharacters() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterRemovesSpaces() {
    $input = (new TelInput())->setValue('090 1234 5678');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterRemovesParentheses() {
    $input = (new TelInput())->setValue('(090) 1234-5678');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterConvertsFullWidthToHalfWidth() {
    $input = (new TelInput())->setValue('０９０１２３４５６７８');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterTrimsWhitespace() {
    $input = (new TelInput())->setValue('  090-1234-5678  ');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterRemovesLetters() {
    $input = (new TelInput())->setValue('090-1234-ABCD');
    $this->assertSame('0901234', $input->getValue());
  }

  public function testFilterKeepsOnlyDigits() {
    $input = (new TelInput())->setValue('abc123def456ghi');
    $this->assertSame('123456', $input->getValue());
  }

  public function testFilterWithEmptyString() {
    $input = (new TelInput())->setValue('');
    $this->assertSame('', $input->getValue());
  }

  public function testFilterWithPlusSign() {
    $input = (new TelInput())->setValue('+81-90-1234-5678');
    $this->assertSame('819012345678', $input->getValue());
  }

  public function testFilterWithMixedFullAndHalfWidth() {
    $input = (new TelInput())->setValue('090-１２３４-5678');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testGetTelReturnsNullWhenEmpty() {
    $input = new TelInput();
    $this->assertNull($input->getTel());
  }

  public function testGetTelReturnsValueWhenNotEmpty() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $this->assertSame('09012345678', $input->getTel());
  }

  public function testGetTelReturnsNullAfterClear() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $input->clear();
    $this->assertNull($input->getTel());
  }

  public function testGetParsedValueReturnsSameAsGetTel() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $this->assertSame($input->getTel(), $input->getParsedValue());
    $this->assertSame('09012345678', $input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenEmpty() {
    $input = new TelInput();
    $this->assertNull($input->getParsedValue());
  }

  public function testValidateWithValidTel() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new TelInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmpty() {
    $input = (new TelInput())->setValue('090-1234-5678');
    $this->assertFalse($input->isEmpty());
  }

  public function testFilterWithSpecialCharacters() {
    $input = (new TelInput())->setValue('090@1234#5678');
    $this->assertSame('09012345678', $input->getValue());
  }

  public function testFilterWithDotsAndCommas() {
    $input = (new TelInput())->setValue('090.1234,5678');
    $this->assertSame('09012345678', $input->getValue());
  }
}
