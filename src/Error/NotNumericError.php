<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\NumberInput;

/**
 * Validation error when value is not numeric
 *
 * @property-read NumberInput $formItem
 */
class NotNumericError extends Error {
}
