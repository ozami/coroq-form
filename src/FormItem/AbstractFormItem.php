<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Closure;
use Coroq\Form\Error\Error;

/**
 * Abstract base class for form items
 *
 * Provides optional default implementation of FormItemInterface for label, error,
 * and state management (disabled, required, readonly).
 *
 * Note: Form class implements FormItemInterface directly and does NOT extend this class.
 */
abstract class AbstractFormItem implements FormItemInterface {
  /** @var bool */
  private bool $disabled = false;
  /** @var bool */
  private bool $required = true;
  /** @var bool */
  private bool $readOnly = false;
  /** @var string */
  private string $label = "";
  /** @var Error|null */
  private ?Error $error = null;
  /** @var Closure|null */
  protected ?Closure $errorCustomizer = null;

  /** Check if this input is disabled */
  public function isDisabled(): bool {
    return $this->disabled;
  }

  /** Set disabled state */
  public function setDisabled(bool $disabled): static {
    $this->disabled = $disabled;
    return $this;
  }

  /** Check if this input is required */
  public function isRequired(): bool {
    return $this->required;
  }

  /** Set required state (default is true) */
  public function setRequired(bool $required): static {
    $this->required = $required;
    return $this;
  }

  /** Check if this input is read-only */
  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  /** Set read-only state */
  public function setReadOnly(bool $readOnly): static {
    $this->readOnly = $readOnly;
    return $this;
  }

  /** Get the human-readable label */
  public function getLabel(): string {
    return $this->label;
  }

  /** Set the human-readable label */
  public function setLabel(string $label): static {
    $this->label = $label;
    return $this;
  }

  /** Get the validation error (null if no error) */
  public function getError(): mixed {
    return $this->error;
  }

  /** Set the validation error */
  public function setError(?Error $error): static {
    if ($error !== null && $this->errorCustomizer !== null) {
      $error = ($this->errorCustomizer)($error, $this);
    }
    $this->error = $error;
    return $this;
  }

  /** Set error customizer closure */
  public function setErrorCustomizer(?\Closure $customizer): static {
    $this->errorCustomizer = $customizer;
    return $this;
  }

  /** Check if there is a validation error */
  public function hasError(): bool {
    return $this->getError() !== null;
  }

  /**
   * Get parsed value
   * Returns null if the value is empty or invalid, otherwise returns the value converted to a suitable type.
   * Default implementation returns null when empty, getValue() otherwise.
   * Override in subclasses for type conversion (int, bool, DateTime, etc.)
   */
  public function getParsedValue(): mixed {
    if ($this->isEmpty()) {
      return null;
    }
    return $this->getValue();
  }
}
