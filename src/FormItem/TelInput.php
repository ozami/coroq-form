<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Telephone input that strips non-numeric characters
 */
class TelInput extends Input {
  use StringFilterTrait;

  /**
   * Filter telephone number input
   *
   * Preserves leading + for international E.164 format.
   * Strips all other non-numeric characters.
   *
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->toHalfwidthAscii($value);

    // Remove all non-numeric characters except +
    $value = preg_replace('#[^0-9+]#', '', $value);

    // Keep only the first + if it's at the start
    $hasLeadingPlus = str_starts_with($value, '+');
    $value = str_replace('+', '', $value);
    if ($hasLeadingPlus) {
      $value = '+' . $value;
    }

    return $value;
  }

  /**
   * @return string|null
   */
  public function getTel(): ?string {
    if ($this->isEmpty()) {
      return null;
    }
    return $this->getValue();
  }

  /**
   * @return string|null
   */
  public function getParsedValue(): ?string {
    return $this->getTel();
  }
}
