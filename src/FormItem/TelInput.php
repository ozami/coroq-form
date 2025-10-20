<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

class TelInput extends Input {
  use StringFilterTrait;

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->toHalfwidthAscii($value);
    $value = $this->trim($value);
    return preg_replace('#[^0-9]#', "", $value);
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
