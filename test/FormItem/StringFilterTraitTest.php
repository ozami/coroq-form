<?php
use Coroq\Form\FormItem\StringFilterTrait;
use Coroq\Form\FormItem\Input;
use PHPUnit\Framework\TestCase;

// Create a concrete test class that uses the trait
class StringFilterTestInput extends Input {
  use StringFilterTrait;

  // Expose protected methods for testing
  public function testScrubUtf8(string $value): string {
    return $this->scrubUtf8($value);
  }

  public function testTrimStart(string $value): string {
    return $this->trimStart($value);
  }

  public function testTrimEnd(string $value): string {
    return $this->trimEnd($value);
  }

  public function testTrim(string $value): string {
    return $this->trim($value);
  }

  public function testToHalfwidthAscii(string $value): string {
    return $this->toHalfwidthAscii($value);
  }

  public function testRemoveWhitespace(string $value): string {
    return $this->removeWhitespace($value);
  }
}

class StringFilterTraitTest extends TestCase {
  public function testScrubUtf8RemovesInvalidBytes() {
    $input = new StringFilterTestInput();

    // Valid UTF-8 should pass through
    $this->assertSame('hello', $input->testScrubUtf8('hello'));

    // Invalid UTF-8 bytes should be replaced (default replacement is U+FFFD)
    $invalid = "hello\x80\x81world";
    $result = $input->testScrubUtf8($invalid);

    // Result should not contain the invalid bytes
    $this->assertStringNotContainsString("\x80", $result);
    $this->assertStringNotContainsString("\x81", $result);
  }

  public function testTrimStartRemovesLeadingWhitespace() {
    $input = new StringFilterTestInput();

    // ASCII spaces
    $this->assertSame('hello', $input->testTrimStart('  hello'));
    $this->assertSame('hello  ', $input->testTrimStart('  hello  '));

    // Tabs and newlines
    $this->assertSame('hello', $input->testTrimStart("\t\nhello"));

    // NBSP (no-break space U+00A0)
    $this->assertSame('hello', $input->testTrimStart("\xc2\xa0hello"));

    // Full-width space (U+3000)
    $this->assertSame('hello', $input->testTrimStart("　hello"));

    // Mixed whitespace
    $this->assertSame('hello', $input->testTrimStart("  \t\n\xc2\xa0　hello"));
  }

  public function testTrimEndRemovesTrailingWhitespace() {
    $input = new StringFilterTestInput();

    // ASCII spaces
    $this->assertSame('hello', $input->testTrimEnd('hello  '));
    $this->assertSame('  hello', $input->testTrimEnd('  hello  '));

    // Tabs and newlines
    $this->assertSame('hello', $input->testTrimEnd("hello\t\n"));

    // NBSP (no-break space U+00A0)
    $this->assertSame('hello', $input->testTrimEnd("hello\xc2\xa0"));

    // Full-width space (U+3000)
    $this->assertSame('hello', $input->testTrimEnd("hello　"));

    // Mixed whitespace
    $this->assertSame('hello', $input->testTrimEnd("hello  \t\n\xc2\xa0　"));
  }

  public function testTrimRemovesBothEnds() {
    $input = new StringFilterTestInput();

    $this->assertSame('hello', $input->testTrim('  hello  '));
    $this->assertSame('hello', $input->testTrim("\t\nhello\t\n"));
    $this->assertSame('hello', $input->testTrim("　hello　"));
    $this->assertSame('hello world', $input->testTrim('  hello world  '));

    // Should not remove whitespace in the middle
    $this->assertSame('hello  world', $input->testTrim('  hello  world  '));
  }

  public function testToHalfwidthAsciiConvertsFullWidth() {
    $input = new StringFilterTestInput();

    // Full-width digits to half-width
    $this->assertSame('0123456789', $input->testToHalfwidthAscii('０１２３４５６７８９'));

    // Full-width letters to half-width
    $this->assertSame('ABC', $input->testToHalfwidthAscii('ＡＢＣ'));
    $this->assertSame('abc', $input->testToHalfwidthAscii('ａｂｃ'));

    // Full-width symbols
    $this->assertSame('!@#', $input->testToHalfwidthAscii('！＠＃'));

    // Mixed
    $this->assertSame('test123', $input->testToHalfwidthAscii('ｔｅｓｔ１２３'));

    // Already half-width should not change
    $this->assertSame('test123', $input->testToHalfwidthAscii('test123'));
  }

  public function testRemoveWhitespaceRemovesAllWhitespace() {
    $input = new StringFilterTestInput();

    // ASCII spaces
    $this->assertSame('helloworld', $input->testRemoveWhitespace('hello world'));
    $this->assertSame('helloworld', $input->testRemoveWhitespace('  hello  world  '));

    // Tabs and newlines
    $this->assertSame('helloworld', $input->testRemoveWhitespace("hello\t\nworld"));

    // NBSP (no-break space U+00A0)
    $this->assertSame('helloworld', $input->testRemoveWhitespace("hello\xc2\xa0world"));

    // Full-width space (U+3000)
    $this->assertSame('helloworld', $input->testRemoveWhitespace("hello　world"));

    // Mixed whitespace
    $this->assertSame('helloworld', $input->testRemoveWhitespace("  hello \t\n\xc2\xa0　 world  "));

    // Empty string and whitespace only
    $this->assertSame('', $input->testRemoveWhitespace(''));
    $this->assertSame('', $input->testRemoveWhitespace('   '));
    $this->assertSame('', $input->testRemoveWhitespace("\t\n\xc2\xa0　"));
  }

  public function testRemoveWhitespaceHandlesNullBytes() {
    $input = new StringFilterTestInput();

    // Null bytes (0x00) should be removed (part of \00 pattern)
    $this->assertSame('hello', $input->testRemoveWhitespace("hello\x00"));
    $this->assertSame('helloworld', $input->testRemoveWhitespace("hello\x00world"));

    // Other control characters are NOT removed (only whitespace + null)
    $this->assertSame("hello\x01world", $input->testRemoveWhitespace("hello\x01world"));
  }

  public function testChainedFilterOperations() {
    $input = new StringFilterTestInput();

    // Common use case: convert full-width, remove whitespace, trim
    $value = '　１２３　４５６　';
    $value = $input->testToHalfwidthAscii($value);  // '　123　456　'
    $value = $input->testRemoveWhitespace($value);  // '123456'

    $this->assertSame('123456', $value);
  }

  public function testChainedFilterWithTrim() {
    $input = new StringFilterTestInput();

    // Another common pattern: trim then remove internal whitespace
    $value = '  hello  world  ';
    $value = $input->testTrim($value);              // 'hello  world'
    $value = $input->testRemoveWhitespace($value);  // 'helloworld'

    $this->assertSame('helloworld', $value);
  }

  public function testEdgeCases() {
    $input = new StringFilterTestInput();

    // Empty string
    $this->assertSame('', $input->testScrubUtf8(''));
    $this->assertSame('', $input->testTrim(''));
    $this->assertSame('', $input->testRemoveWhitespace(''));

    // Only whitespace
    $this->assertSame('', $input->testTrim('   '));
    $this->assertSame('', $input->testRemoveWhitespace('   '));

    // Unicode strings
    $this->assertSame('こんにちは', $input->testTrim('  こんにちは  '));
    $this->assertSame('こんにちは', $input->testRemoveWhitespace('こん に ち は'));
  }
}
