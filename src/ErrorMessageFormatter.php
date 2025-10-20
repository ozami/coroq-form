<?php
declare(strict_types=1);
namespace Coroq\Form;

use Closure;
use LogicException;
use Coroq\Form\Error\Error;

class ErrorMessageFormatter {
  /** @var array<string, string|Closure> */
  private array $messages = [];

  /**
   * @param array<string, string|Closure> $messages
   * @return void
   */
  public function setMessages(array $messages): void {
    $this->messages = $messages;
  }

  /**
   * @param Error $error
   * @return string
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
