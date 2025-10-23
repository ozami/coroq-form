<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\KatakanaInput;

/**
 * Validation error when text contains non-Katakana characters
 *
 * @property-read KatakanaInput $formItem
 */
class NotKatakanaError extends Error {
}
