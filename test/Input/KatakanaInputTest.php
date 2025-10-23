<?php
use Coroq\Form\FormItem\KatakanaInput;
use Coroq\Form\Error\NotKatakanaError;
use PHPUnit\Framework\TestCase;

class KatakanaInputTest extends TestCase {
  public function testFilterConvertsHiraganaToKatakana() {
    $input = (new KatakanaInput())->setValue('あいうえお');
    $this->assertSame('アイウエオ', $input->getValue());
  }

  public function testFilterConvertsHalfWidthKatakanaToFullWidth() {
    $input = (new KatakanaInput())->setValue('ｱｲｳｴｵ');
    $this->assertSame('アイウエオ', $input->getValue());
  }

  public function testFilterKeepsKatakana() {
    $input = (new KatakanaInput())->setValue('アイウエオ');
    $this->assertSame('アイウエオ', $input->getValue());
  }

  public function testFilterConvertsVoicedSoundMarks() {
    $input = (new KatakanaInput())->setValue('がぎぐげご');
    $this->assertSame('ガギグゲゴ', $input->getValue());
  }

  public function testFilterWithLongVowelMark() {
    $input = (new KatakanaInput())->setValue('らーめん');
    $this->assertSame('ラーメン', $input->getValue());
  }

  public function testValidateWithValidKatakana() {
    $input = (new KatakanaInput())->setValue('カタカナ');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithInvalidCharacters() {
    $input = (new KatakanaInput())->setValue('ABC');
    // Filter converts hiragana to katakana, but ABC remains
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotKatakanaError::class, $input->getError());
  }

  public function testValidateWithMixedKatakanaAndHiragana() {
    // Filter should convert hiragana to katakana
    $input = (new KatakanaInput())->setValue('カタかな');
    $this->assertTrue($input->validate());
    $this->assertSame('カタカナ', $input->getValue());
  }

  public function testValidateWithNumbers() {
    $input = (new KatakanaInput())->setValue('123');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotKatakanaError::class, $input->getError());
  }

  public function testValidateWithSpaces() {
    $input = (new KatakanaInput())->setValue('カタ カナ');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotKatakanaError::class, $input->getError());
  }

  public function testValidateWithKatakanaAndNumbers() {
    $input = (new KatakanaInput())->setValue('カタカナ123');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(NotKatakanaError::class, $input->getError());
  }

  public function testGetKatakanaReturnsNullWhenEmpty() {
    $input = new KatakanaInput();
    $this->assertNull($input->getKatakana());
  }

  public function testGetKatakanaReturnsValueWhenValid() {
    $input = (new KatakanaInput())->setValue('カタカナ');
    $this->assertSame('カタカナ', $input->getKatakana());
  }

  public function testGetKatakanaReturnsNullWhenInvalid() {
    $input = (new KatakanaInput())->setValue('ABC');
    $this->assertNull($input->getKatakana());
  }

  public function testGetKatakanaReturnsNullAfterClear() {
    $input = (new KatakanaInput())->setValue('カタカナ');
    $input->clear();
    $this->assertNull($input->getKatakana());
  }

  public function testGetParsedValueReturnsSameAsGetKatakana() {
    $input = (new KatakanaInput())->setValue('カタカナ');
    $this->assertSame($input->getKatakana(), $input->getParsedValue());
    $this->assertSame('カタカナ', $input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenEmpty() {
    $input = new KatakanaInput();
    $this->assertNull($input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenInvalid() {
    $input = (new KatakanaInput())->setValue('ABC');
    $this->assertNull($input->getParsedValue());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new KatakanaInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmpty() {
    $input = (new KatakanaInput())->setValue('カタカナ');
    $this->assertFalse($input->isEmpty());
  }

  public function testFilterWithSmallKatakana() {
    $input = (new KatakanaInput())->setValue('ャュョ');
    $this->assertSame('ャュョ', $input->getValue());
  }

  public function testFilterWithExtendedKatakana() {
    $input = (new KatakanaInput())->setValue('ヴ');
    $this->assertSame('ヴ', $input->getValue());
  }

  public function testValidateEmptyWithRequired() {
    $input = (new KatakanaInput())->setRequired(true);
    $this->assertFalse($input->validate());
    $this->assertNotNull($input->getError());
  }

  public function testValidateEmptyWithNotRequired() {
    $input = (new KatakanaInput())->setRequired(false);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testInheritsTextInputFeatures() {
    // KatakanaInput extends TextInput, so it should inherit features like setMinLength
    $input = (new KatakanaInput())
      ->setMinLength(3)
      ->setValue('カナ'); // Only 2 characters

    $this->assertFalse($input->validate());
    // Should have TooShortError
  }
}
