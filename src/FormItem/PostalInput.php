<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Japanese postal code input with formatting
 */
class PostalInput extends Input {
  use StringFilterTrait;

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->toHalfwidthAscii($value);
    $value = $this->trim($value);
    return $value;
  }

  /**
   * @return string|null
   */
  public function getPostal(): ?string {
    if ($this->isEmpty()) {
      return null;
    }
    return $this->getValue();
  }

  /**
   * @return string|null
   */
  public function getParsedValue(): ?string {
    return $this->getPostal();
  }
}
