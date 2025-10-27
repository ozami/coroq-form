<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotIntegerError;

/**
 * Integer input with numeric validation and range constraints
 */
class IntegerInput extends Input implements HasNumericRangeInterface {
  use NumericRangeTrait {
    setMin as private setMinInternal;
    setMax as private setMaxInternal;
  }
  use StringFilterTrait;

  public function __construct() {
    parent::__construct();
    // Default range to PHP int limits
    $this->setMinInternal((string)PHP_INT_MIN);
    $this->setMaxInternal((string)PHP_INT_MAX);
  }

  /**
   * Set minimum value
   * Must be >= PHP_INT_MIN to guarantee getInteger() can return int
   */
  public function setMin(int|float|string $min): self {
    $min = (string)$min;
    if (bccomp($min, (string)PHP_INT_MIN) < 0) {
      throw new \InvalidArgumentException(
        "IntegerInput min must be >= PHP_INT_MIN (" . PHP_INT_MIN . "), got: " . $min
      );
    }
    if (bccomp($min, (string)PHP_INT_MAX) > 0) {
      throw new \InvalidArgumentException(
        "IntegerInput min must be <= PHP_INT_MAX (" . PHP_INT_MAX . "), got: " . $min
      );
    }
    return $this->setMinInternal($min);
  }

  /**
   * Set maximum value
   * Must be <= PHP_INT_MAX to guarantee getInteger() can return int
   */
  public function setMax(int|float|string $max): self {
    $max = (string)$max;
    if (bccomp($max, (string)PHP_INT_MIN) < 0) {
      throw new \InvalidArgumentException(
        "IntegerInput max must be >= PHP_INT_MIN (" . PHP_INT_MIN . "), got: " . $max
      );
    }
    if (bccomp($max, (string)PHP_INT_MAX) > 0) {
      throw new \InvalidArgumentException(
        "IntegerInput max must be <= PHP_INT_MAX (" . PHP_INT_MAX . "), got: " . $max
      );
    }
    return $this->setMaxInternal($max);
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->scrubUtf8($value);
    $value = $this->toHalfwidthAscii($value);
    $value = $this->removeWhitespace($value);

    // Remove trailing .0000... (e.g., "123.00" -> "123", "123.10" unchanged)
    $value = preg_replace('/\.0+$/', '', $value);

    return $value;
  }

  /**
   * Check if value is a valid integer format (any size)
   *
   * @param mixed $value
   * @return bool
   */
  private function isInteger($value): bool {
    return preg_match('/^-?[0-9]+$/', $value) === 1;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isInteger($value)) {
      return new NotIntegerError($this);
    }

    // Check range (includes PHP int range via default min/max set in constructor)
    $rangeError = $this->validateRange((string)$value);
    if ($rangeError !== null) {
      return $rangeError;
    }

    return parent::doValidate($value);
  }

  /**
   * Get parsed integer value
   * Returns null if value is invalid or outside PHP int range
   *
   * @return int|null
   */
  public function getInteger(): ?int {
    $value = $this->getValue();

    // Use doValidate to check if value is valid (handles empty, format, range)
    if ($this->doValidate($value) !== null) {
      return null;
    }

    return (int)$value;
  }

  /**
   * @return int|null
   */
  public function getParsedValue(): ?int {
    return $this->getInteger();
  }
}
