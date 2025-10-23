<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\InvalidDateError;

/**
 * Date input with parsing and validation
 */
class DateInput extends Input {
  use StringFilterTrait;

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->toHalfwidthAscii($value);
    $value = $this->trim($value);
    $time = strtotime($value);
    if ($time !== false) {
      $value = date("Y-m-d", $time);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isValidDate($value): bool {
    return strtotime($value) !== false;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    if (!$this->isValidDate($value)) {
      return new InvalidDateError($this);
    }
    return null;
  }

  /**
   * @return \DateTime|null
   */
  public function getDateTime(): ?\DateTime {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isValidDate($value)) {
      return null;
    }
    return new \DateTime("@" . strtotime($value));
  }

  /**
   * @return \DateTimeImmutable|null
   */
  public function getDateTimeImmutable(): ?\DateTimeImmutable {
    $dateTime = $this->getDateTime();
    if ($dateTime === null) {
      return null;
    }
    return \DateTimeImmutable::createFromMutable($dateTime);
  }

  /**
   * @return \DateTimeImmutable|null
   */
  public function getParsedValue(): ?\DateTimeImmutable {
    return $this->getDateTimeImmutable();
  }
}
