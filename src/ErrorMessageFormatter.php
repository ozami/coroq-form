<?php
declare(strict_types=1);
namespace Coroq\Form;

use Closure;
use LogicException;
use Coroq\Form\Error\Error;

/**
 * Converts Error objects to human-readable error messages
 *
 * Maps error class names to message strings or closures.
 * Closures receive the typed error object and return a string.
 *
 * Example:
 *   $formatter = new ErrorMessageFormatter();
 *   $formatter->setMessages([
 *     EmptyError::class => 'This field is required',
 *     TooLongError::class => fn($e) => 'Maximum ' . $e->formItem->getMaxLength() . ' characters',
 *   ]);
 *   echo $formatter->format($error);
 */
class ErrorMessageFormatter {
  /** @var array<string, string|Closure> Map of error class names to messages */
  private array $messages = [];

  /**
   * Set the message map
   *
   * @param array<string, string|Closure> $messages Array of error class => message/closure
   * @return void
   */
  public function setMessages(array $messages): void {
    $this->messages = $messages;
  }

  /**
   * Format an error object to a string
   *
   * @param Error $error The error to format
   * @return string The formatted error message
   * @throws LogicException If no message defined for the error class
   */
  public function format(Error $error): string {
    $message = $this->messages[get_class($error)] ?? null;
    if ($message === null) {
      throw new LogicException("No message defined for error class: " . get_class($error));
    }
    if ($message instanceof Closure) {
      $message = $message($error);
      if (!is_string($message)) {
        throw new LogicException("Closure must return a string for error class: " . get_class($error));
      }
    }
    return strval($message);
  }
}
