<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FileInput;

/**
 * Validation error when file does not exist or cannot be accessed
 *
 * @property-read FileInput $formItem
 */
class FileNotFoundError extends Error {
}
