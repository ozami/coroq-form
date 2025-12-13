<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Common interface for all form items (inputs and forms)
 *
 * Defines the contract for value management, validation, and state control.
 * Implemented by Input (and its subclasses) and Form classes.
 */
interface FormItemInterface {
  /** Get the raw value */
  public function getValue(): mixed;

  /** Get the parsed/typed value (e.g., int for IntegerInput, DateTime for DateInput) */
  public function getParsedValue(): mixed;

  /** Set the value */
  public function setValue(mixed $value): static;

  /** Clear the value (set to empty) */
  public function clear(): static;

  /** Check if the value is empty */
  public function isEmpty(): bool;

  /** Check if this item is disabled */
  public function isDisabled(): bool;

  /** Set disabled state (disabled items are excluded from validation and getValue) */
  public function setDisabled(bool $disabled): static;

  /** Check if this item is required */
  public function isRequired(): bool;

  /** Set required state (required items cannot be empty) */
  public function setRequired(bool $required): static;

  /** Check if this item is read-only */
  public function isReadOnly(): bool;

  /** Set read-only state (read-only items ignore setValue calls) */
  public function setReadOnly(bool $readOnly): static;

  /** Get the human-readable label */
  public function getLabel(): string;

  /** Set the human-readable label */
  public function setLabel(string $label): static;

  /** Validate the value and return true if valid */
  public function validate(): bool;

  /** Get validation error (null if no error) */
  public function getError(): mixed;

  /** Check if there is a validation error */
  public function hasError(): bool;
}
