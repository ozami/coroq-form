<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FormItemInterface;

/**
 * Base class for all form validation errors
 *
 * Stores a reference to the form item that failed validation.
 * Each specific error type is a subclass (EmptyError, TooLongError, etc.)
 */
abstract class Error {
  /**
   * @param FormItemInterface $formItem The form item that failed validation
   */
  public function __construct(
    public FormItemInterface $formItem,
  ) {
  }
}
