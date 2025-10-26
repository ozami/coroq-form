<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\InvalidUrlError;

/**
 * URL input with validation and scheme restrictions
 */
class UrlInput extends Input {
  use StringFilterTrait;

  protected array $schemes = ["http", "https"]; // lower case only

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->scrubUtf8($value);
    $value = $this->toHalfwidthAscii($value);
    $value = $this->trim($value);
    return $value;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isValidUrl($value): bool {
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
      return false;
    }
    if ($this->schemes && !in_array(parse_url($value, PHP_URL_SCHEME), $this->schemes)) {
      return false;
    }
    return true;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isValidUrl($value)) {
      return new InvalidUrlError($this);
    }
    return null;
  }

  /**
   * @return string|null
   */
  public function getUrl(): ?string {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isValidUrl($value)) {
      return null;
    }
    return $value;
  }

  /**
   * @return string|null
   */
  public function getParsedValue(): ?string {
    return $this->getUrl();
  }
}
