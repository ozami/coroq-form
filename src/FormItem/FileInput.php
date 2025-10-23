<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\FileNotFoundError;
use Coroq\Form\Error\FileTooLargeError;
use Coroq\Form\Error\FileTooSmallError;
use Coroq\Form\Error\InvalidMimeTypeError;
use Coroq\Form\Error\InvalidExtensionError;

/**
 * File input with size, MIME type, and extension validation
 */
class FileInput extends Input {
  private ?int $maxSize = null;
  private ?int $minSize = null;
  private array $allowedMimeTypes = [];
  private array $allowedExtensions = [];

  public function __construct() {
    parent::__construct();
    $this->setRequired(false); // Usually optional - file might already be uploaded
  }

  public function isEmpty(): bool {
    $value = $this->getValue();
    return $value === null || $value === '';
  }

  /**
   * Set maximum file size in bytes
   */
  public function setMaxSize(int $bytes): self {
    $this->maxSize = $bytes;
    return $this;
  }

  public function getMaxSize(): ?int {
    return $this->maxSize;
  }

  /**
   * Set minimum file size in bytes
   */
  public function setMinSize(int $bytes): self {
    $this->minSize = $bytes;
    return $this;
  }

  public function getMinSize(): ?int {
    return $this->minSize;
  }

  /**
   * Set allowed MIME types (e.g., ['image/jpeg', 'image/png', 'application/pdf'])
   */
  public function setAllowedMimeTypes(array $types): self {
    $this->allowedMimeTypes = $types;
    return $this;
  }

  public function getAllowedMimeTypes(): array {
    return $this->allowedMimeTypes;
  }

  /**
   * Set allowed file extensions (e.g., ['jpg', 'png', 'pdf'])
   */
  public function setAllowedExtensions(array $extensions): self {
    $this->allowedExtensions = $extensions;
    return $this;
  }

  public function getAllowedExtensions(): array {
    return $this->allowedExtensions;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  protected function doValidate(mixed $value): ?Error {
    // Check file size (filesize returns false if file doesn't exist or can't be accessed)
    $size = @filesize($value);
    if ($size === false) {
      return new FileNotFoundError($this);
    }

    if ($this->minSize !== null && $size < $this->minSize) {
      return new FileTooSmallError($this);
    }

    if ($this->maxSize !== null && $size > $this->maxSize) {
      return new FileTooLargeError($this);
    }

    // Check MIME type if allowlist is set
    if (!empty($this->allowedMimeTypes)) {
      $mimeType = mime_content_type($value);
      if ($mimeType === false || !in_array($mimeType, $this->allowedMimeTypes, true)) {
        return new InvalidMimeTypeError($this);
      }
    }

    // Check extension if allowlist is set
    if (!empty($this->allowedExtensions)) {
      $pathInfo = pathinfo($value);
      $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';

      // Normalize allowed extensions to lowercase
      $normalizedAllowed = array_map('strtolower', $this->allowedExtensions);

      if (!in_array($extension, $normalizedAllowed, true)) {
        return new InvalidExtensionError($this);
      }
    }

    return null;
  }
}
