<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\LengthRangeTrait;

/**
 * Validation error when text is shorter than minimum length
 *
 * @property-read LengthRangeTrait $formItem
 */
class TooShortError extends Error {
}
