<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FileInput;

/**
 * Validation error when file MIME type is not allowed
 *
 * @property-read FileInput $formItem
 */
class InvalidMimeTypeError extends Error {
}
