<?php
use Coroq\Form\FormItem\UrlInput;
use Coroq\Form\Error\InvalidUrlError;
use PHPUnit\Framework\TestCase;

class UrlInputTest extends TestCase {
  public function testFilterUsesStringFilterTrait() {
    $input = new UrlInput();

    // Verify filter calls trim() (one example is enough)
    $input->setValue('  https://example.com  ');
    $this->assertSame('https://example.com', $input->getValue());

    // Verify filter calls toHalfwidthAscii() (one example is enough)
    $input->setValue('ｈｔｔｐ://example.com');
    $this->assertSame('http://example.com', $input->getValue());

    // Details of trim/toHalfwidthAscii are tested in StringFilterTraitTest
  }

  public function testValidateWithValidHttpUrl() {
    $input = (new UrlInput())->setValue('http://example.com');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithValidHttpsUrl() {
    $input = (new UrlInput())->setValue('https://example.com');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithInvalidUrl() {
    $input = (new UrlInput())->setValue('not a url');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidUrlError::class, $input->getError());
  }

  public function testValidateWithInvalidScheme() {
    $input = (new UrlInput())->setValue('ftp://example.com');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidUrlError::class, $input->getError());
  }

  public function testValidateWithUrlWithoutScheme() {
    $input = (new UrlInput())->setValue('example.com');
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidUrlError::class, $input->getError());
  }

  public function testValidateWithUrlWithPath() {
    $input = (new UrlInput())->setValue('https://example.com/path/to/page');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithUrlWithQueryString() {
    $input = (new UrlInput())->setValue('https://example.com?foo=bar&baz=qux');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithUrlWithFragment() {
    $input = (new UrlInput())->setValue('https://example.com#section');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithUrlWithPort() {
    $input = (new UrlInput())->setValue('https://example.com:8080');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithLocalhostUrl() {
    $input = (new UrlInput())->setValue('http://localhost');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithIpAddressUrl() {
    $input = (new UrlInput())->setValue('http://192.168.1.1');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testGetUrlReturnsNullWhenEmpty() {
    $input = new UrlInput();
    $this->assertNull($input->getUrl());
  }

  public function testGetUrlReturnsValueWhenValid() {
    $input = (new UrlInput())->setValue('https://example.com');
    $this->assertSame('https://example.com', $input->getUrl());
  }

  public function testGetUrlReturnsNullWhenInvalid() {
    $input = (new UrlInput())->setValue('not a url');
    $this->assertNull($input->getUrl());
  }

  public function testGetUrlReturnsNullAfterClear() {
    $input = (new UrlInput())->setValue('https://example.com');
    $input->clear();
    $this->assertNull($input->getUrl());
  }

  public function testGetParsedValueReturnsSameAsGetUrl() {
    $input = (new UrlInput())->setValue('https://example.com');
    $this->assertSame($input->getUrl(), $input->getParsedValue());
    $this->assertSame('https://example.com', $input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenEmpty() {
    $input = new UrlInput();
    $this->assertNull($input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullWhenInvalid() {
    $input = (new UrlInput())->setValue('invalid');
    $this->assertNull($input->getParsedValue());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new UrlInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonEmpty() {
    $input = (new UrlInput())->setValue('https://example.com');
    $this->assertFalse($input->isEmpty());
  }

  public function testValidateWithComplexUrl() {
    $input = (new UrlInput())->setValue('https://user:pass@example.com:8080/path?query=value#hash');
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateEmptyUrlWithRequired() {
    $input = (new UrlInput())->setRequired(true);
    $this->assertFalse($input->validate());
    $this->assertNotNull($input->getError());
  }

  public function testValidateEmptyUrlWithNotRequired() {
    $input = (new UrlInput())->setRequired(false);
    $this->assertTrue($input->validate());
    $this->assertNull($input->getError());
  }

  public function testValidateWithInternationalDomain() {
    $input = (new UrlInput())->setValue('https://日本.jp');
    // FILTER_VALIDATE_URL does not support non-Punycode international domains on PHP 8.0-8.4
    // International domains must be converted to Punycode (e.g., xn--wgv71a.jp) to pass validation
    $this->assertFalse($input->validate());
    $this->assertInstanceOf(InvalidUrlError::class, $input->getError());
  }
}
