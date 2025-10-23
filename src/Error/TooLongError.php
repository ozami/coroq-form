<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\LengthRangeTrait;

/**
 * Validation error when text exceeds maximum length
 *
 * @property-read LengthRangeTrait $formItem
 */
class TooLongError extends Error {
}

