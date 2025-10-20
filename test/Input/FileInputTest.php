<?php
use Coroq\Form\FormItem\FileInput;
use Coroq\Form\Error\FileNotFoundError;
use Coroq\Form\Error\FileTooLargeError;
use Coroq\Form\Error\FileTooSmallError;
use Coroq\Form\Error\InvalidMimeTypeError;
use Coroq\Form\Error\InvalidExtensionError;
use PHPUnit\Framework\TestCase;

class FileInputTest extends TestCase {
  private string $tempFile;

  protected function setUp(): void {
    // Create a temporary test file
    $this->tempFile = tempnam(sys_get_temp_dir(), 'test_file_');
    file_put_contents($this->tempFile, 'Test content');
  }

  protected function tearDown(): void {
    // Clean up temporary file
    if (file_exists($this->tempFile)) {
      unlink($this->tempFile);
    }
  }

  public function testValidateFileExists() {
    $input = new FileInput();

    $input->setValue('/nonexistent/file.txt')->validate();
    $this->assertInstanceOf(FileNotFoundError::class, $input->getError());

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateFileSize() {
    $input = (new FileInput())
      ->setMinSize(5)
      ->setMaxSize(100);

    // File too small (content is "Test content" = 12 bytes)
    $input->setMinSize(50)->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(FileTooSmallError::class, $input->getError());

    // File too large
    $input->setMinSize(1)->setMaxSize(5)->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(FileTooLargeError::class, $input->getError());

    // File size OK
    $input->setMinSize(1)->setMaxSize(100)->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateMimeType() {
    // Note: mime_content_type returns 'text/plain' for our test file
    $input = (new FileInput())
      ->setAllowedMimeTypes(['text/plain']);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());

    $input->setAllowedMimeTypes(['image/jpeg']);
    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidMimeTypeError::class, $input->getError());
  }

  public function testValidateExtension() {
    // Rename temp file to have .txt extension
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())
      ->setAllowedExtensions(['txt']);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());

    $input->setAllowedExtensions(['jpg', 'png']);
    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidExtensionError::class, $input->getError());
  }

  public function testGetParsedValueReturnsSameAsGetValue() {
    $input = (new FileInput())->setValue($this->tempFile);
    $this->assertSame($input->getValue(), $input->getParsedValue());
  }

  public function testDefaultIsOptional() {
    $input = new FileInput();
    $this->assertFalse($input->isRequired());
  }
}
