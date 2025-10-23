<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\NumericRangeTrait;

/**
 * Validation error when number exceeds maximum value
 *
 * @property-read NumericRangeTrait $formItem
 */
class TooLargeError extends Error {
}
