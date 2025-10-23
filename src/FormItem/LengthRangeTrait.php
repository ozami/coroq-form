<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\TooShortError;
use Coroq\Form\Error\TooLongError;

/**
 * Provides min/max length validation for text inputs
 */
trait LengthRangeTrait {
  /** @var int */
  protected $minLength = 0;
  /** @var int */
  protected $maxLength = PHP_INT_MAX;

  public function getMinLength(): int {
    return $this->minLength;
  }

  public function setMinLength(int $minLength): self {
    $this->minLength = $minLength;
    return $this;
  }

  public function getMaxLength(): int {
    return $this->maxLength;
  }

  public function setMaxLength(int $maxLength): self {
    $this->maxLength = $maxLength;
    return $this;
  }

  /**
   * Validate string length is within min/max range
   *
   * @return Error|null TooShortError or TooLongError if out of range
   */
  protected function validateLength($value): ?Error {
    $length = mb_strlen($value, "UTF-8");
    if ($length < $this->minLength) {
      return new TooShortError($this);
    }
    if ($length > $this->maxLength) {
      return new TooLongError($this);
    }
    return null;
  }
}
