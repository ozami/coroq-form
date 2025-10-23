<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\TextInput;

/**
 * Validation error when input does not match the required pattern
 *
 * @property-read TextInput $formItem
 */
class PatternMismatchError extends Error {
}
