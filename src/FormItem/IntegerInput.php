<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotIntegerError;

class IntegerInput extends Input implements HasNumericRange {
  use NumericRangeTrait;

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = parent::filter($value);
    $value = mb_convert_kana($value, "as", "UTF-8");
    $value = preg_replace("/[[:space:]]/u", "", $value);
    return $value;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isInteger($value): bool {
    return (string)(int)$value == (string)$value;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isInteger($value)) {
      return new NotIntegerError($this);
    }
    $rangeError = $this->validateRange($value);
    if ($rangeError !== null) {
      return $rangeError;
    }
    return parent::doValidate($value);
  }

  /**
   * @return int|null
   */
  public function getInteger(): ?int {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isInteger($value)) {
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
