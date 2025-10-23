<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\IntegerInput;

/**
 * Validation error when value is not a valid integer
 *
 * @property-read IntegerInput $formItem
 */
class NotIntegerError extends Error {
}
