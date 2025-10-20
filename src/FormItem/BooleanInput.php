<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

class BooleanInput extends Input {
  public function isEmpty(): bool {
    return strval($this->getValue()) == "";
  }

  /**
   * @return bool
   */
  public function getBoolean(): bool {
    return !$this->isEmpty();
  }

  /**
   * @return bool
   */
  public function getParsedValue(): bool {
    return $this->getBoolean();
  }
}
