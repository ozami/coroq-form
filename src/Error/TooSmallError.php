<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\NumericRangeTrait;

/**
 * Validation error when number is below minimum value
 *
 * @property-read NumericRangeTrait $formItem
 */
class TooSmallError extends Error {
}
