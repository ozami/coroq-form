<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\CountRangeTrait;

/**
 * Validation error when fewer items are selected than the minimum
 *
 * @property-read CountRangeTrait $formItem
 */
class TooFewSelectionsError extends Error {
}
