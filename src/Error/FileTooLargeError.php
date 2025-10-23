<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FileInput;

/**
 * Validation error when file size exceeds maximum
 *
 * @property-read FileInput $formItem
 */
class FileTooLargeError extends Error {
}
