<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;
use Coroq\Form\Error\Error;
use Coroq\Form\Error\EmptyError;

class Input implements FormItemInterface {
  /** @var mixed */
  private $value = "";
  /** @var bool */
  private bool $required = true;
  /** @var bool */
  private bool $readOnly = false;
  /** @var bool */
  private bool $disabled = false;
  /** @var string */
  private string $label = "";
  /** @var Error|null */
  private ?Error $error = null;

  public function __construct() {
  }

  public function getValue(): mixed {
    return $this->value;
  }

  /**
   * Get parsed/typed value
   * Override in subclasses to return specific types (int, bool, DateTime, etc.)
   */
  public function getParsedValue(): mixed {
    return $this->getValue();
  }

  public function setValue(mixed $value): self {
    if ($this->readOnly) {
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

  public function isRequired(): bool {
    return $this->required;
  }

  public function setRequired(bool $required): self {
    $this->required = $required;
    return $this;
  }

  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  public function setReadOnly(bool $readOnly): self {
    $this->readOnly = $readOnly;
    return $this;
  }

  public function isDisabled(): bool {
    return $this->disabled;
  }

  public function setDisabled(bool $disabled): self {
    $this->disabled = $disabled;
    return $this;
  }

  public function getLabel(): string {
    return $this->label;
  }

  public function setLabel(string $label) {
    $this->label = $label;
    return $this;
  }

  public function getError(): ?Error {
    return $this->error;
  }

  public function setError(?Error $error): self {
    $this->error = $error;
    return $this;
  }

  public function hasError(): bool {
    return $this->getError() !== null;
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
