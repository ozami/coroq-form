<?php
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\UnicodeNormalization;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\PatternMismatchError;
use PHPUnit\Framework\TestCase;

class TextInputTest extends TestCase {
  public static function setUpBeforeClass(): void {
    // Configure UTF-8 replacement character for tests
    mb_substitute_character(0xFFFD);
  }

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

    // Invalid UTF-8 gets replaced with replacement characters during filter
    $value = $input->getValue();
    $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
    $this->assertStringContainsString("\u{FFFD}", $value);

    // Validation passes - replacement characters are valid UTF-8
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
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

  // Filter method tests

  public function testFilterWithMbConvertKana() {
    $input = (new TextInput())
      ->setMb('KVC')  // Katakana, voiced/semi-voiced sound marks, full-width
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue('あいうえお');

    $this->assertSame('アイウエオ', $input->getValue());
  }

  public function testFilterWithMbConvertKanaFullWidthToHalfWidth() {
    $input = (new TextInput())
      ->setMb('rnask')  // half-width numbers, ASCII, spaces, katakana
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue('ＡＢＣ１２３　アイウ');

    $this->assertSame('ABC123 ｱｲｳ', $input->getValue());
  }

  public function testFilterWithCaseUpper() {
    $input = (new TextInput())
      ->setCase(TextInput::UPPER)
      ->setTrim(null)
      ->setValue('Hello World');

    $this->assertSame('HELLO WORLD', $input->getValue());
  }

  public function testFilterWithCaseLower() {
    $input = (new TextInput())
      ->setCase(TextInput::LOWER)
      ->setTrim(null)
      ->setValue('Hello World');

    $this->assertSame('hello world', $input->getValue());
  }

  public function testFilterWithCaseTitle() {
    $input = (new TextInput())
      ->setCase(TextInput::TITLE)
      ->setTrim(null)
      ->setValue('hello world');

    $this->assertSame('Hello World', $input->getValue());
  }

  public function testFilterMultilineWithEolConversion() {
    $input = (new TextInput())
      ->setMultiline(true)
      ->setEol("\n")
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue("Line1\r\nLine2\rLine3\nLine4");

    $this->assertSame("Line1\nLine2\nLine3\nLine4", $input->getValue());
  }

  public function testFilterMultilineWithCustomEol() {
    $input = (new TextInput())
      ->setMultiline(true)
      ->setEol("<br>")
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue("Line1\r\nLine2\rLine3");

    $this->assertSame("Line1<br>Line2<br>Line3", $input->getValue());
  }

  public function testFilterNonMultilineConvertsNewlinesToSpaces() {
    $input = (new TextInput())
      ->setMultiline(false)
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue("Line1\nLine2\rLine3\r\nLine4");

    // Note: \r\n becomes two spaces (one for \r, one for \n)
    $this->assertSame("Line1 Line2 Line3  Line4", $input->getValue());
  }

  public function testFilterWithNoControlDefaultTrue() {
    // noControl is true by default
    $input = (new TextInput())
      ->setTrim(null)
      ->setValue("Text\x00with\x1fcontrol\x7fchars\xc2\xa0here");

    // Control chars should be replaced with spaces, except CR/LF
    $this->assertSame("Text with control chars here", $input->getValue());
  }

  public function testFilterWithNoControlExplicitTrue() {
    $input = (new TextInput())
      ->setNoControl(true)
      ->setTrim(null)
      ->setValue("Text\x00\x01\x08\x09\x0b\x0c\x0e\x1fhere");

    // All control chars except CR/LF should be replaced with spaces
    $result = $input->getValue();
    $this->assertStringNotContainsString("\x00", $result);
    $this->assertStringNotContainsString("\x01", $result);
  }

  public function testFilterWithNoSpace() {
    $input = (new TextInput())
      ->setNoWhitespace(true)
      ->setTrim(null)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue("Text with spaces\t\nand　tabs");

    // All spaces, tabs, newlines, and full-width spaces should be removed
    $this->assertSame("Textwithspacesandtabs", $input->getValue());
  }

  public function testFilterWithNoSpaceRemovesAllWhitespace() {
    $input = (new TextInput())
      ->setNoWhitespace(true)
      ->setTrim(null)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue(" \t\n\r\x00\xc2\xa0　Text　Here ");

    $this->assertSame("TextHere", $input->getValue());
  }

  public function testFilterCombinationMbAndCase() {
    $input = (new TextInput())
      ->setMb('KVC')  // Hiragana to Katakana
      ->setCase(TextInput::LOWER)
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue('あいうABC');

    // First converts hiragana to katakana, then applies lowercase
    $this->assertSame('アイウabc', $input->getValue());
  }

  public function testFilterCombinationAllFeatures() {
    $input = (new TextInput())
      ->setMb('KVC')
      ->setCase(TextInput::UPPER)
      ->setMultiline(false)
      ->setNoControl(true)
      ->setNoWhitespace(false)
      ->setTrim(TextInput::BOTH)
      ->setValue("  あいう\ntest  ");

    // Should convert kana, uppercase, convert newline to space, trim
    $this->assertSame('アイウ TEST', $input->getValue());
  }

  public function testFilterOrderOfOperations() {
    // Test that operations happen in correct order:
    // 1. mb_convert_kana
    // 2. case conversion
    // 3. eol/newline handling
    // 4. noControl
    // 5. noWhitespace
    // 6. trim

    $input = (new TextInput())
      ->setMb('as')  // Full-width ASCII to half-width
      ->setCase(TextInput::LOWER)
      ->setMultiline(false)
      ->setTrim(TextInput::BOTH)
      ->setValue("  ＨＥＬＬＯ\nＷＯＲＬＤ  ");

    // Full-width -> half-width, then lowercase, newline->space, then trim
    $this->assertSame('hello world', $input->getValue());
  }

  public function testFilterDefaultBehaviors() {
    // Test default settings: trim=BOTH, noControl=true, multiline=false
    $input = new TextInput();
    $input->setValue("  Text\x00with\ndefaults  ");

    // Should trim, replace control chars with spaces, convert newline to space
    $this->assertSame('Text with defaults', $input->getValue());
  }

  public function testFilterWithNullMb() {
    $input = (new TextInput())
      ->setMb(null)
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue('あいうえお');

    // Should not convert anything
    $this->assertSame('あいうえお', $input->getValue());
  }

  public function testFilterWithNullCase() {
    $input = (new TextInput())
      ->setCase(null)
      ->setTrim(null)
      ->setValue('Hello World');

    // Should not change case
    $this->assertSame('Hello World', $input->getValue());
  }

  public function testFilterConversionsWithEmptyString() {
    $input = (new TextInput())
      ->setMb('KVC')
      ->setCase(TextInput::UPPER)
      ->setValue('');

    $this->assertSame('', $input->getValue());
  }

  public function testFilterConvertsNonStringToString() {
    $input = (new TextInput())
      ->setTrim(null)
      ->setNoControl(false)
      ->setValue(123);

    $this->assertSame('123', $input->getValue());
  }

  public function testFilterWithMultibyteCharactersAndNoControl() {
    $input = (new TextInput())
      ->setNoControl(true)
      ->setTrim(null)
      ->setValue("日本語\x00テスト");

    // Should preserve multibyte chars but replace control char
    $this->assertSame('日本語 テスト', $input->getValue());
  }

  public function testFilterTrimWithFullWidthSpace() {
    $input = (new TextInput())
      ->setTrim(TextInput::BOTH)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue("　　Text　　");

    // Should trim full-width spaces
    $this->assertSame('Text', $input->getValue());
  }

  public function testFilterNoSpaceWithFullWidthSpace() {
    $input = (new TextInput())
      ->setNoWhitespace(true)
      ->setTrim(null)
      ->setMultiline(true)
      ->setEol(null)
      ->setNoControl(false)
      ->setValue("Text　with　full　width");

    $this->assertSame('Textwithfullwidth', $input->getValue());
  }

  public function testFluentInterfaceForSetters() {
    $input = new TextInput();

    // Verify all setters return $this for fluent interface
    $result = $input
      ->setMb('KVC')
      ->setCase(TextInput::UPPER)
      ->setMultiline(true)
      ->setNoWhitespace(true)
      ->setNoControl(false)
      ->setEol("\r\n")
      ->setTrim(TextInput::LEFT)
      ->setPattern('/test/')
      ->setUnicodeNormalization(UnicodeNormalization::NFC);

    $this->assertSame($input, $result);
  }

  // Unicode normalization tests

  public function testUnicodeNormalizationNFCDefault() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    // NFCis default
    $input = (new TextInput())
      ->setTrim(null)
      ->setNoControl(false);

    // Decomposed form (NFD): か (U+304B) + ゛ (U+3099)
    $decomposed = "\u{304B}\u{3099}";
    // Composed form (NFC): が (U+304C)
    $composed = "\u{304C}";

    $input->setValue($decomposed);
    $this->assertSame($composed, $input->getValue());
  }

  public function testUnicodeNormalizationNFCExplicit() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFC)
      ->setTrim(null)
      ->setNoControl(false);

    // Decomposed: é (e + combining acute)
    $decomposed = "e\u{0301}";
    // Composed: é
    $composed = "\u{00E9}";

    $input->setValue($decomposed);
    $this->assertSame($composed, $input->getValue());
  }

  public function testUnicodeNormalizationNFD() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFD)
      ->setTrim(null)
      ->setNoControl(false);

    // Composed: が (U+304C)
    $composed = "\u{304C}";
    // Decomposed: か (U+304B) + ゛ (U+3099)
    $decomposed = "\u{304B}\u{3099}";

    $input->setValue($composed);
    $this->assertSame($decomposed, $input->getValue());
  }

  public function testUnicodeNormalizationNFKC() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFKC)
      ->setTrim(null)
      ->setNoControl(false);

    // Full-width digits (compatibility characters)
    $fullWidth = "１２３";
    // Half-width digits (canonical form)
    $halfWidth = "123";

    $input->setValue($fullWidth);
    $this->assertSame($halfWidth, $input->getValue());
  }

  public function testUnicodeNormalizationNFKD() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFKD)
      ->setTrim(null)
      ->setNoControl(false);

    // Composed compatibility character: ㌀ (U+3300, SQUARE APAATO)
    $compatibility = "\u{3300}";
    // Decomposed: ア (U+30A2) + パ (U+30D1) + ー (U+30FC) + ト (U+30C8) decomposed further
    // Note: NFKD also decomposes パ into ハ + combining dakuten

    $input->setValue($compatibility);
    $result = $input->getValue();

    // Verify it's different from the original and uses decomposition
    $this->assertNotSame($compatibility, $result);
    $this->assertStringContainsString("\u{30A2}", $result); // ア
  }

  public function testUnicodeNormalizationDisabled() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(null)
      ->setTrim(null)
      ->setNoControl(false);

    // Decomposed form should stay decomposed
    $decomposed = "\u{304B}\u{3099}";

    $input->setValue($decomposed);
    $this->assertSame($decomposed, $input->getValue());
  }

  public function testUnicodeNormalizationWithoutIntl() {
    // This test verifies behavior when intl is not available
    // We can't actually test this if intl IS available, so we'll skip
    if (extension_loaded('intl')) {
      $this->markTestSkipped('Cannot test non-intl behavior when intl is available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFC)
      ->setTrim(null)
      ->setNoControl(false);

    $value = "\u{304B}\u{3099}";
    $input->setValue($value);

    // Should return original value unchanged (silent fallback)
    $this->assertSame($value, $input->getValue());
  }

  public function testUnicodeNormalizationInvalidForm() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid normalization form');

    $input = new TextInput();
    $input->setUnicodeNormalization('INVALID');
  }

  public function testUnicodeNormalizationJapaneseDakuten() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFC)
      ->setTrim(null)
      ->setNoControl(false);

    // Various decomposed Japanese characters
    $decomposed = "\u{304B}\u{3099}\u{304D}\u{3099}\u{304F}\u{3099}"; // か゛き゛く゛
    $composed = "\u{304C}\u{304E}\u{3050}"; // がぎぐ

    $input->setValue($decomposed);
    $this->assertSame($composed, $input->getValue());
  }

  public function testUnicodeNormalizationOrderOfOperations() {
    if (!extension_loaded('intl')) {
      $this->markTestSkipped('intl extension not available');
    }

    // Unicode normalization should happen after scrubUtf8 but before mb_convert_kana
    $input = (new TextInput())
      ->setUnicodeNormalization(UnicodeNormalization::NFC)
      ->setMb('KVC')  // Hiragana to Katakana
      ->setTrim(null)
      ->setNoControl(false);

    // Decomposed hiragana が (ka + dakuten)
    $decomposed = "\u{304B}\u{3099}";
    // Should first normalize to NFC が, then convert to Katakana ガ
    $expected = "\u{30AC}"; // ガ

    $input->setValue($decomposed);
    $this->assertSame($expected, $input->getValue());
  }

  public function testValidateTooLongError() {
    $input = (new TextInput())
      ->setMaxLength(5)
      ->setValue('toolong');

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(\Coroq\Form\Error\TooLongError::class, $input->getError());
  }

  public function testValidateTooShortError() {
    $input = (new TextInput())
      ->setMinLength(5)
      ->setValue('abc');

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(\Coroq\Form\Error\TooShortError::class, $input->getError());
  }

  // UTF-8 handling tests

  public function testFilterReplacesInvalidUtf8WithReplacementCharacter() {
    $input = new TextInput();
    // Invalid UTF-8 bytes
    $input->setValue("\x80\x81\x82");

    // Should be replaced with replacement character (U+FFFD)
    $this->assertSame("\u{FFFD}\u{FFFD}\u{FFFD}", $input->getValue());
  }

  public function testFilterPreservesValidUtf8PartsAndReplacesInvalidBytes() {
    $input = new TextInput();
    // Mixed: valid UTF-8 "Hello" + invalid byte + valid "World"
    $input->setValue("Hello\x80World");

    // Invalid byte should become replacement character
    $this->assertSame("Hello\u{FFFD}World", $input->getValue());
  }

  public function testFilterHandlesCompletelyInvalidUtf8() {
    $input = new TextInput();
    // All bytes invalid
    $input->setValue("\xFF\xFE\xFD");

    // All should become replacement characters
    $this->assertSame("\u{FFFD}\u{FFFD}\u{FFFD}", $input->getValue());
  }

  public function testFilterDoesNotCrashWithInvalidUtf8() {
    $input = (new TextInput())
      ->setTrim(TextInput::BOTH)
      ->setNoWhitespace(true)
      ->setNoControl(true);

    // This used to cause fatal error: preg_replace NULL cascade
    $input->setValue("\x80\x81\x82");

    // Should not crash - replacement characters are valid UTF-8
    $this->assertSame("\u{FFFD}\u{FFFD}\u{FFFD}", $input->getValue());
  }

  public function testInvalidUtf8PassesValidationAfterReplacement() {
    $input = (new TextInput())->setRequired(true);
    $input->setValue("\x80\x81\x82");

    // Replacement characters are valid UTF-8, not empty
    $this->assertTrue($input->validate());
    $this->assertFalse($input->hasError());
  }

  public function testValidatorIsCalledAfterDoValidate() {
    $validatorCalled = false;
    $input = (new TextInput())
      ->setValue('test')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertTrue($input->validate());
    $this->assertTrue($validatorCalled);
  }

  public function testValidatorCanReturnError() {
    $input = (new TextInput())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidError::class, $input->getError());
  }

  public function testValidatorNotCalledWhenPatternMismatch() {
    $validatorCalled = false;
    $input = (new TextInput())
      ->setPattern('/^[0-9]+$/')
      ->setValue('abc')
      ->setValidator(function($formItem, $value) use (&$validatorCalled) {
        $validatorCalled = true;
        return null;
      });

    $this->assertFalse($input->validate());
    $this->assertInstanceOf(PatternMismatchError::class, $input->getError());
    $this->assertFalse($validatorCalled);
  }
}
