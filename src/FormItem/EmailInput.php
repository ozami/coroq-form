<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\InvalidEmailError;

class EmailInput extends Input {
  use StringFilterTrait;

  private bool $lowercaseDomain;

  public function __construct() {
    $this->setLowerCaseDomain(true);
  }

  /**
   * @param bool $lowercaseDomain
   * @return $this
   */
  public function setLowerCaseDomain(bool $lowercaseDomain = true): self {
    $this->lowercaseDomain = $lowercaseDomain;
    return $this;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->toHalfwidthAscii($value);
    $value = $this->trim($value);
    if ($this->lowercaseDomain) {
      $value = preg_replace_callback('#(.*@)(.*)#', function(array $matches) {
        return $matches[1] . strtolower($matches[2]);
      }, $value);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isValidEmail($value): bool {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isValidEmail($value)) {
      return new InvalidEmailError($this);
    }
    return null;
  }

  /**
   * @return string|null
   */
  public function getEmail(): ?string {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isValidEmail($value)) {
      return null;
    }
    return $value;
  }

  /**
   * @return string|null
   */
  public function getParsedValue(): ?string {
    return $this->getEmail();
  }
}
