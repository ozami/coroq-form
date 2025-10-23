<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;

/**
 * Abstract base class for input-like form items
 *
 * Provides common functionality for label, error, and state management
 * (disabled, required, readonly) shared by Input, Derived, and other input types.
 *
 * Note: Form class implements FormItemInterface directly and does NOT extend this class.
 */
abstract class AbstractInput implements FormItemInterface {
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

  public function isDisabled(): bool {
    return $this->disabled;
  }

  public function setDisabled(bool $disabled): self {
    $this->disabled = $disabled;
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

  public function getLabel(): string {
    return $this->label;
  }

  public function setLabel(string $label): self {
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

  /**
   * Get parsed value
   * Default implementation returns getValue()
   * Override in subclasses for type conversion (int, bool, DateTime, etc.)
   */
  public function getParsedValue(): mixed {
    return $this->getValue();
  }
}
