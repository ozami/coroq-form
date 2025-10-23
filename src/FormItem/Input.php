<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;
use Coroq\Form\Error\Error;
use Coroq\Form\Error\EmptyError;

class Input extends AbstractInput {
  /** @var mixed */
  private $value = "";

  public function __construct() {
  }

  public function getValue(): mixed {
    return $this->value;
  }

  public function setValue(mixed $value): self {
    if ($this->isReadOnly()) {
      return $this;
    }
    $this->value = $this->filter($value);
    $this->setError(null);
    return $this;
  }

  public function isEmpty(): bool {
    return $this->getValue() . "" == "";
  }

  public function clear(): self {
    $this->setValue("");
    return $this;
  }

  public function validate(): bool {
    $this->setError(null);
    if ($this->isEmpty()) {
      if ($this->isRequired()) {
        $this->setError(new EmptyError($this));
      }
    }
    else {
      $error = $this->doValidate($this->getValue());
      if ($error) {
        $this->setError($error);
      }
    }
    return !$this->getError();
  }

  /**
   * Filter/normalize input value
   * Override to transform input (e.g., trim, convert encoding)
   */
  public function filter(mixed $value): mixed {
    return $value;
  }

  /**
   * Validate the filtered value
   * Override to add validation logic
   *
   * @return ?Error Error object if validation fails, null if valid
   */
  protected function doValidate(mixed $value): ?Error {
    return null;
  }
}
