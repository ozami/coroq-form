<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\OptionsValidationTrait;

/**
 * Validation error when selected value is not in available options
 *
 * @property-read OptionsValidationTrait $formItem
 */
class NotInOptionsError extends Error {
}
