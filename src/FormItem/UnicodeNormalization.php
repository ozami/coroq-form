<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Unicode normalization forms
 *
 * This class provides constants for Unicode normalization forms used with TextInput.
 * Can be upgraded to enum when minimum PHP version is bumped to 8.1+.
 *
 * @see https://unicode.org/reports/tr15/
 */
class UnicodeNormalization {
  /** Normalization Form C (Canonical Composition) - default for most use cases */
  const NFC = 'NFC';

  /** Normalization Form D (Canonical Decomposition) */
  const NFD = 'NFD';

  /** Normalization Form KC (Compatibility Composition) */
  const NFKC = 'NFKC';

  /** Normalization Form KD (Compatibility Decomposition) */
  const NFKD = 'NFKD';
}
