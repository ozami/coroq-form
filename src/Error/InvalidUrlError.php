<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\UrlInput;

/**
 * Validation error for invalid URLs
 *
 * @property-read UrlInput $formItem
 */
class InvalidUrlError extends InvalidError {
}
