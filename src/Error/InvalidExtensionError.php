<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FileInput;

/**
 * Validation error when file extension is not allowed
 *
 * @property-read FileInput $formItem
 */
class InvalidExtensionError extends Error {
}
