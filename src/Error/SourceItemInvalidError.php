<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\Derived;

/**
 * Error indicating that one or more source items failed validation
 *
 * This error is set on Derived items when their source items are invalid.
 *
 * @property-read Derived $formItem
 */
class SourceItemInvalidError extends Error {
}
