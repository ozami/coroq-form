<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Closure;
use Coroq\Form\Error\Error;
use Coroq\Form\Error\SourceItemInvalidError;

/**
 * Form item that depends on other form items
 *
 * Derived items reference other form items and can:
 * - Calculate their value from sources (via setValueCalculator)
 * - Validate based on sources (via setValidator)
 *
 * Derived items are always readonly - their value and validity come from
 * their dependencies, not from user input.
 *
 * Examples:
 *
 * Calculated value from multiple inputs:
 * ```php
 * $form->fullName = (new Derived())
 *   ->setValueCalculator(fn($first, $last) => $first . ' ' . $last)
 *   ->addSource($form->firstName)
 *   ->addSource($form->lastName);
 * ```
 *
 * Cross-field validation:
 * ```php
 * $form->passwordMatch = (new Derived())
 *   ->setValidator(fn($password, $confirm, $calculated) =>
 *     $password !== $confirm
 *       ? new PasswordMismatchError($this)
 *       : null
 *   )
 *   ->addSource($form->password)
 *   ->addSource($form->passwordConfirm);
 * ```
 *
 * Calculated value with validation:
 * ```php
 * $form->displayName = (new Derived())
 *   ->setValueCalculator(fn($first, $last) => strtoupper($first . ' ' . $last))
 *   ->setValidator(fn($first, $last, $calculated) =>
 *     strlen($calculated) > 50 ? new TooLongError($this) : null
 *   )
 *   ->addSource($form->first)
 *   ->addSource($form->last);
 * ```
 */
/**
 * Computed field derived from other form inputs
 */
class Derived extends AbstractFormItem {
  /** @var array<FormItemInterface> */
  private array $sources = [];
  /** @var Closure|null */
  private ?Closure $valueCalculator = null;
  /** @var Closure|null */
  private ?Closure $validator = null;

  public function __construct() {
    $this->setReadOnly(true);
    $this->setRequired(false);
  }

  /**
   * Always returns true - Derived items are always readonly
   */
  public function isReadOnly(): bool {
    return true;
  }

  /**
   * No-op - Derived items are always readonly
   */
  public function setReadOnly(bool $readOnly): self {
    return $this;
  }

  /**
   * Set value calculator function to derive value from sources
   *
   * Calculator receives source values as separate arguments.
   *
   * @param Closure $valueCalculator Function that receives source values and returns calculated value
   * @return $this
   */
  public function setValueCalculator(Closure $valueCalculator): self {
    $this->valueCalculator = $valueCalculator;
    return $this;
  }

  /**
   * Set validator function
   *
   * Validator receives source values as separate arguments, with calculated value as the last argument.
   * Returns Error object if invalid, null if valid.
   *
   * @param Closure $validator Validation function
   * @return $this
   */
  public function setValidator(Closure $validator): self {
    $this->validator = $validator;
    return $this;
  }

  /**
   * Add source item this item depends on
   *
   * @return $this
   */
  public function addSource(FormItemInterface $source): self {
    $this->sources[] = $source;
    return $this;
  }

  /**
   * Get calculated value
   *
   * Returns null if no value calculator is set or if any source is invalid.
   * Otherwise calculates and returns the value.
   */
  public function getValue(): mixed {
    if (!$this->valueCalculator) {
      return null;
    }

    // Validate all sources first
    foreach ($this->sources as $source) {
      if (!$source->validate()) {
        return null;
      }
    }

    // Calculate value
    $sourceValues = [];
    foreach ($this->sources as $source) {
      $sourceValues[] = $source->getValue();
    }

    return ($this->valueCalculator)(...$sourceValues);
  }

  /**
   * No-op - Derived values cannot be set directly
   */
  public function setValue(mixed $value): self {
    return $this;
  }

  /**
   * Check if calculated value is empty
   */
  public function isEmpty(): bool {
    return $this->getValue() . "" == "";
  }

  /**
   * Clear error (no value to clear)
   */
  public function clear(): self {
    $this->setError(null);
    return $this;
  }

  /**
   * Validate this item
   *
   * Validates all sources first. If any source is invalid, sets SourceItemInvalidError and returns false.
   * If validator is set, runs the validator with source values and calculated value.
   * If no validator is set, returns true.
   *
   * @return bool True if valid
   */
  public function validate(): bool {
    $this->setError(null);

    // Validate all sources first
    foreach ($this->sources as $source) {
      if (!$source->validate()) {
        $this->setError(new SourceItemInvalidError($this));
        return false;
      }
    }

    if (!$this->validator) {
      return true;
    }

    // Get source values
    $sourceValues = [];
    foreach ($this->sources as $source) {
      $sourceValues[] = $source->getValue();
    }

    // Get calculated value (may be null if no value calculator)
    $calculatedValue = $this->getValue();

    // Append calculated value as last argument
    $sourceValues[] = $calculatedValue;

    // Run validator with spread arguments
    $error = ($this->validator)(...$sourceValues);
    if ($error) {
      $this->setError($error);
    }

    return !$this->hasError();
  }
}
