<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\CountRangeTrait;

/**
 * Validation error when more items are selected than the maximum
 *
 * @property-read CountRangeTrait $formItem
 */
class TooManySelectionsError extends Error {
}
