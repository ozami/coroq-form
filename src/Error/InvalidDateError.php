<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\DateInput;

/**
 * Validation error for invalid date formats
 *
 * @property-read DateInput $formItem
 */
class InvalidDateError extends InvalidError {
}
