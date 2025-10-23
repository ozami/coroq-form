<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FileInput;

/**
 * Validation error when file size is below minimum
 *
 * @property-read FileInput $formItem
 */
class FileTooSmallError extends Error {
}
