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

  // Getter tests

  public function testGetMaxSizeReturnsNullByDefault() {
    $input = new FileInput();
    $this->assertNull($input->getMaxSize());
  }

  public function testGetMaxSizeReturnsSetValue() {
    $input = (new FileInput())->setMaxSize(1024);
    $this->assertSame(1024, $input->getMaxSize());
  }

  public function testGetMinSizeReturnsNullByDefault() {
    $input = new FileInput();
    $this->assertNull($input->getMinSize());
  }

  public function testGetMinSizeReturnsSetValue() {
    $input = (new FileInput())->setMinSize(100);
    $this->assertSame(100, $input->getMinSize());
  }

  public function testGetAllowedMimeTypesReturnsEmptyArrayByDefault() {
    $input = new FileInput();
    $this->assertSame([], $input->getAllowedMimeTypes());
  }

  public function testGetAllowedMimeTypesReturnsSetValue() {
    $types = ['image/jpeg', 'image/png'];
    $input = (new FileInput())->setAllowedMimeTypes($types);
    $this->assertSame($types, $input->getAllowedMimeTypes());
  }

  public function testGetAllowedExtensionsReturnsEmptyArrayByDefault() {
    $input = new FileInput();
    $this->assertSame([], $input->getAllowedExtensions());
  }

  public function testGetAllowedExtensionsReturnsSetValue() {
    $extensions = ['jpg', 'png', 'pdf'];
    $input = (new FileInput())->setAllowedExtensions($extensions);
    $this->assertSame($extensions, $input->getAllowedExtensions());
  }

  // isEmpty() tests

  public function testIsEmptyReturnsTrueForNull() {
    $input = new FileInput();
    $input->setValue(null);
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new FileInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForFilePath() {
    $input = (new FileInput())->setValue($this->tempFile);
    $this->assertFalse($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForNonexistentPath() {
    $input = (new FileInput())->setValue('/nonexistent/file.txt');
    $this->assertFalse($input->isEmpty());
  }

  // Fluent interface tests

  public function testFluentInterface() {
    $input = new FileInput();
    $result = $input
      ->setMaxSize(1024)
      ->setMinSize(10)
      ->setAllowedMimeTypes(['image/jpeg'])
      ->setAllowedExtensions(['jpg']);

    $this->assertSame($input, $result);
  }

  // Validation edge cases

  public function testValidateEmptyFileWhenOptional() {
    $input = (new FileInput())->setRequired(false);
    $input->setValue('')->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateEmptyFileWhenRequired() {
    $input = (new FileInput())->setRequired(true);
    $input->setValue('')->validate();
    $this->assertNotNull($input->getError());
  }

  public function testValidateWithZeroByteFile() {
    $emptyFile = tempnam(sys_get_temp_dir(), 'empty_');
    file_put_contents($emptyFile, '');

    $input = (new FileInput())->setMinSize(1);
    $input->setValue($emptyFile)->validate();
    $this->assertInstanceOf(FileTooSmallError::class, $input->getError());

    unlink($emptyFile);
  }

  public function testValidateWithZeroByteFileNoMinSize() {
    $emptyFile = tempnam(sys_get_temp_dir(), 'empty_');
    file_put_contents($emptyFile, '');

    $input = new FileInput();
    $input->setValue($emptyFile)->validate();
    $this->assertNull($input->getError());

    unlink($emptyFile);
  }

  public function testValidateExtensionCaseInsensitive() {
    $txtFile = $this->tempFile . '.TXT';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())->setAllowedExtensions(['txt']);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateExtensionWithUppercaseInAllowlist() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())->setAllowedExtensions(['TXT', 'PDF']);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateFileWithNoExtension() {
    $input = (new FileInput())->setAllowedExtensions(['txt']);
    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidExtensionError::class, $input->getError());
  }

  public function testValidateMultipleMimeTypes() {
    $input = (new FileInput())
      ->setAllowedMimeTypes(['text/plain', 'text/html', 'application/json']);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateMultipleExtensions() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())
      ->setAllowedExtensions(['txt', 'pdf', 'doc', 'docx']);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateExactMinSize() {
    $size = filesize($this->tempFile);
    $input = (new FileInput())->setMinSize($size);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateExactMaxSize() {
    $size = filesize($this->tempFile);
    $input = (new FileInput())->setMaxSize($size);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateOneByteBelowMinSize() {
    $size = filesize($this->tempFile);
    $input = (new FileInput())->setMinSize($size + 1);
    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(FileTooSmallError::class, $input->getError());
  }

  public function testValidateOneByteAboveMaxSize() {
    $size = filesize($this->tempFile);
    $input = (new FileInput())->setMaxSize($size - 1);
    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(FileTooLargeError::class, $input->getError());
  }

  public function testValidateWithBothMinAndMaxSize() {
    $size = filesize($this->tempFile);
    $input = (new FileInput())
      ->setMinSize($size - 1)
      ->setMaxSize($size + 1);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateWithOnlyMaxSize() {
    $input = (new FileInput())->setMaxSize(1000);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateWithOnlyMinSize() {
    $input = (new FileInput())->setMinSize(1);
    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateWithBothMimeTypeAndExtension() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())
      ->setAllowedMimeTypes(['text/plain'])
      ->setAllowedExtensions(['txt']);

    $input->setValue($this->tempFile)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateFailsOnMimeTypeBeforeExtension() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    // MIME type check happens first and should fail
    $input = (new FileInput())
      ->setAllowedMimeTypes(['image/jpeg'])
      ->setAllowedExtensions(['txt']);

    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidMimeTypeError::class, $input->getError());
  }

  public function testValidateFailsOnExtensionWhenMimeTypeOk() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    // MIME type is OK but extension should fail
    $input = (new FileInput())
      ->setAllowedMimeTypes(['text/plain'])
      ->setAllowedExtensions(['jpg', 'png']);

    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidExtensionError::class, $input->getError());
  }

  // Clear and setValue tests

  public function testClearSetsValueToEmptyString() {
    $input = (new FileInput())->setValue($this->tempFile);
    $input->clear();
    $this->assertSame('', $input->getValue());
  }

  public function testSetValueWithNull() {
    $input = (new FileInput())->setValue(null);
    $this->assertTrue($input->isEmpty());
  }

  public function testSetValueClearsError() {
    $input = new FileInput();
    $input->setValue('/nonexistent')->validate();
    $this->assertTrue($input->hasError());

    $input->setValue($this->tempFile);
    $this->assertFalse($input->hasError());
  }

  public function testGetValueReturnsFilePath() {
    $input = (new FileInput())->setValue($this->tempFile);
    $this->assertSame($this->tempFile, $input->getValue());
  }

  // Validation priority tests

  public function testValidateFileNotFoundBeforeSizeCheck() {
    $input = (new FileInput())
      ->setMinSize(1)
      ->setMaxSize(1000);

    $input->setValue('/nonexistent/file.txt')->validate();
    $this->assertInstanceOf(FileNotFoundError::class, $input->getError());
  }

  public function testValidateSizeCheckBeforeMimeType() {
    $input = (new FileInput())
      ->setMaxSize(1)
      ->setAllowedMimeTypes(['text/plain']);

    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(FileTooLargeError::class, $input->getError());
  }

  public function testValidateMimeTypeCheckBeforeExtension() {
    $txtFile = $this->tempFile . '.txt';
    rename($this->tempFile, $txtFile);
    $this->tempFile = $txtFile;

    $input = (new FileInput())
      ->setAllowedMimeTypes(['image/jpeg'])
      ->setAllowedExtensions(['txt']);

    $input->setValue($this->tempFile)->validate();
    $this->assertInstanceOf(InvalidMimeTypeError::class, $input->getError());
  }

  // Revalidation tests

  public function testRevalidationAfterChangingConstraints() {
    // Test file is 12 bytes ("Test content")
    $input = (new FileInput())
      ->setMaxSize(5)
      ->setValue($this->tempFile);

    $input->validate();
    $this->assertInstanceOf(FileTooLargeError::class, $input->getError());

    $input->setMaxSize(1000)->validate();
    $this->assertNull($input->getError());
  }

  public function testValidateAfterClear() {
    $input = (new FileInput())
      ->setRequired(false)
      ->setValue($this->tempFile);

    $input->validate();
    $this->assertNull($input->getError());

    $input->clear()->validate();
    $this->assertNull($input->getError()); // Optional, so empty is OK
  }

  public function testRequiredFileInputWithNullValue() {
    $input = (new FileInput())->setRequired(true);
    $input->setValue(null)->validate();
    $this->assertNotNull($input->getError());
  }

  public function testValidateWhenFilesizeReturnsFalse() {
    // filesize() returns false when the file doesn't exist or can't be accessed
    // This is now the primary check (no redundant file_exists() call)
    $input = new FileInput();
    $input->setValue('/nonexistent/file/path_' . uniqid())->validate();

    $this->assertInstanceOf(FileNotFoundError::class, $input->getError());
  }

  public function testValidateWithSpecialFiles() {
    // Test with special device files like /dev/null
    // filesize() on these returns 0 (not false), so they should validate if no size constraints
    if (file_exists('/dev/null')) {
      $input = new FileInput();
      $input->setValue('/dev/null')->validate();

      // Should pass validation (filesize returns 0, not false)
      $this->assertNull($input->getError());

      // But should fail if we require a minimum size
      $input->setMinSize(1)->validate();
      $this->assertInstanceOf(FileTooSmallError::class, $input->getError());
    } else {
      $this->markTestSkipped('/dev/null not available on this system');
    }
  }

  public function testValidateWithSymbolicLink() {
    // Test with a symbolic link to verify file handling
    if (!function_exists('symlink')) {
      $this->markTestSkipped('symlink() function not available');
      return;
    }

    $linkPath = sys_get_temp_dir() . '/test_symlink_' . uniqid();

    try {
      // Create a symlink pointing to our test file
      symlink($this->tempFile, $linkPath);

      $input = new FileInput();
      $input->setValue($linkPath)->validate();

      // Should validate successfully since target exists
      $this->assertNull($input->getError());

      unlink($linkPath);
    } catch (\Exception $e) {
      if (file_exists($linkPath)) {
        unlink($linkPath);
      }
      throw $e;
    }
  }

  public function testValidateWithBrokenSymbolicLink() {
    // Test with a broken symbolic link (points to non-existent file)
    if (!function_exists('symlink')) {
      $this->markTestSkipped('symlink() function not available');
      return;
    }

    $linkPath = sys_get_temp_dir() . '/test_broken_symlink_' . uniqid();
    $nonExistentTarget = '/nonexistent/path/to/file_' . uniqid();

    try {
      // Create a symlink pointing to a non-existent file
      symlink($nonExistentTarget, $linkPath);

      $input = new FileInput();
      $input->setValue($linkPath)->validate();

      // Should fail with FileNotFoundError because target doesn't exist
      $this->assertInstanceOf(FileNotFoundError::class, $input->getError());

      unlink($linkPath);
    } catch (\Exception $e) {
      if (file_exists($linkPath) || is_link($linkPath)) {
        unlink($linkPath);
      }
      throw $e;
    }
  }
}
