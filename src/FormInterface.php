<?php
declare(strict_types=1);
namespace Coroq\Form;

use Coroq\Form\FormItem\FormItemInterface;

/**
 * Interface for form containers (Form and RepeatingForm)
 *
 * Extends FormItemInterface with methods for managing collections of form items.
 */
interface FormInterface extends FormItemInterface {
  /** Get all values as an associative array */
  public function getValue(): array;

  /** Get all parsed values as an associative array */
  public function getParsedValue(): array;

  /** Get only non-empty values as an associative array */
  public function getFilledValue(): array;

  /** Get only non-empty parsed values as an associative array */
  public function getFilledParsedValue(): array;

  /** Get all validation errors as an associative array */
  public function getError(): array;

  /** Get a form item by name/index */
  public function getItem(mixed $name): ?FormItemInterface;

  /** Get all form items */
  public function getItems(): array;
}

