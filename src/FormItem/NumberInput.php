<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotNumericError;

/**
 * Numeric input with validation and range constraints
 */
class NumberInput extends Input implements HasNumericRangeInterface {
  use NumericRangeTrait;
  use StringFilterTrait;

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->scrubUtf8($value);
    $value = $this->toHalfwidthAscii($value);
    $value = preg_replace("/[[:space:]]/u", "", $value);
    return $value;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isNumeric($value): bool {
    return is_numeric($value);
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isNumeric($value)) {
      return new NotNumericError($this);
    }
    $rangeError = $this->validateRange($value);
    if ($rangeError !== null) {
      return $rangeError;
    }
    return parent::doValidate($value);
  }

  /**
   * @return float|null
   */
  public function getNumber(): ?float {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isNumeric($value)) {
      return null;
    }
    return (float)$value;
  }

  /**
   * @return float|null
   */
  public function getParsedValue(): ?float {
    return $this->getNumber();
  }
}
