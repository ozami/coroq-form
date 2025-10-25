<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;
use Coroq\Form\Error\Error;
use Coroq\Form\Error\EmptyError;

/**
 * Base class for all form input types
 *
 * Provides core functionality for value storage, filtering, and validation.
 * Subclasses override filter() and doValidate() to implement specific input types.
 */
class Input extends AbstractFormItem {
  /** @var mixed The current value */
  private $value = "";

  public function __construct() {
  }

  /**
   * Get the current value
   *
   * @return mixed The filtered value
   */
  public function getValue(): mixed {
    return $this->value;
  }

  /**
   * Set the value (applies filtering and clears errors)
   *
   * @param mixed $value The value to set
   * @return self
   */
  public function setValue(mixed $value): self {
    if ($this->isReadOnly()) {
      return $this;
    }
    $this->value = $this->filter($value);
    $this->setError(null);
    return $this;
  }

  /**
   * Check if the value is empty
   *
   * @return bool True if the value is empty
   */
  public function isEmpty(): bool {
    return $this->getValue() . "" == "";
  }

  /**
   * Clear the value (set to empty string)
   *
   * @return self
   */
  public function clear(): self {
    $this->setValue("");
    return $this;
  }

  /**
   * Validate the value
   *
   * Checks required constraint first, then calls doValidate() if not empty.
   *
   * @return bool True if valid, false if validation failed
   */
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
