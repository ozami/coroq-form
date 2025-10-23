<?php
use Coroq\Form\FormItem\PostalInput;
use PHPUnit\Framework\TestCase;

class PostalInputTest extends TestCase {
  public function testFilterConvertsFullWidthToHalfWidth() {
    $input = (new PostalInput())->setValue('１２３−４５６７');
    $this->assertSame('123-4567', $input->getValue());
  }

  public function testFilterTrimsWhitespace() {
    $input = (new PostalInput())->setValue('  123-4567  ');
    $this->assertSame('123-4567', $input->getValue());
  }

  public function testFilterKeepsHyphen() {
    $input = (new PostalInput())->setValue('123-4567');
    $this->assertSame('123-4567', $input->getValue());
  }

  public function testFilterWithFullWidthHyphen() {
    $input = (new PostalInput())->setValue('１２３－４５６７');
    $this->assertSame('123-4567', $input->getValue());
  }

  public function testFilterWithNoHyphen() {
    $input = (new PostalInput())->setValue('1234567');
    $this->assertSame('1234567', $input->getValue());
  }

  public function testFilterWithEmptyString() {
    $input = (new PostalInput())->setValue('');
    $this->assertSame('', $input->getValue());
  }

  public function testFilterWithMixedFullAndHalfWidth() {
    $input = (new PostalInput())->setValue('１２３-4567');
    $this->assertSame('123-4567', $input->getValue());
  }

  public function testFilterPreservesLetters() {
    $input = (new PostalInput())->setValue('ABC-123');
    $this->assertSame('ABC-123', $input->getValue());
  }

  public function testFilterWithSpacesInMiddle() {
    $input = (new PostalInput())->setValue(' 123 - 4567 ');
    $this->assertSame('123 - 4567', $input->getValue());
  }

  public function testGetPostalReturnsNullWhenEmpty() {
    $input = new PostalInput();
    $this->assertNull($input->getPostal());
  }

  public function testGetPostalReturnsValueWhenNotEmpty() {
    $input = (new PostalInput())->setValue('123-4567');
    $this->assertSame('123-4567', $input->getPostal());
  }

  public function testGetPostalReturnsNullAfterClear() {
    $input = (new PostalInput())->setValue('123-4567');
    $input->clear();
    $this->assertNull($input->getPostal());
  }

  public function testGetParsedValueReturnsSameAsGetPostal() {
    $input = (new PostalInput())->setValue('123-4567');
    $this->assertSame($input->getPostal(), $input->getParsedValue());
    $this->assertSame('123-4567', $input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenEmpty() {
    $input = new PostalInput();
    $this->assertNull($input->getParsedValue());
  }

  public function testValidateWithValidPostal() {
    $input = (new PostalInput())->setValue('123-4567');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new PostalInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmpty() {
    $input = (new PostalInput())->setValue('123-4567');
    $this->assertFalse($input->isEmpty());
  }

  public function testFilterWithLeadingZeros() {
    $input = (new PostalInput())->setValue('０１２−３４５６');
    $this->assertSame('012-3456', $input->getValue());
  }

  public function testFilterWithSpecialCharacters() {
    $input = (new PostalInput())->setValue('123@4567');
    $this->assertSame('123@4567', $input->getValue()); // Preserved
  }
}
