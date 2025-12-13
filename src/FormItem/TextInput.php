<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\PatternMismatchError;
use Coroq\Form\FormItem\UnicodeNormalization;

/**
 * Text input with extensive filtering options (trim, case conversion, mb_convert_kana, etc.)
 */
class TextInput extends Input implements HasLengthRangeInterface {
  use LengthRangeTrait;
  use StringFilterTrait;
  const UPPER = MB_CASE_UPPER;
  const LOWER = MB_CASE_LOWER;
  const TITLE = MB_CASE_TITLE;
  const LEFT = "left";
  const RIGHT = "right";
  const BOTH = "both";

  /** @var string|null */
  protected $mb = null;
  /** @var string|null */
  protected $case = null;
  /** @var bool */
  protected $multiline = false;
  /** @var bool */
  protected $noWhitespace = false;
  /** @var bool */
  protected $noControl = true;
  /** @var string|null */
  protected $eol = "\n"; // used only when $multiline is true. set null for no conversion
  /** @var string|null */
  protected $trim = self::BOTH;
  /** @var string|null */
  protected $pattern = null;
  /** @var string|null */
  protected $unicodeNormalization = UnicodeNormalization::NFC;

  /**
   * @param string|null $mb
   * @return static
   */
  public function setMb(?string $mb): static {
    $this->mb = $mb;
    return $this;
  }

  /**
   * @param int|null $case
   * @return static
   */
  public function setCase(?int $case): static {
    $this->case = $case;
    return $this;
  }

  /**
   * @param bool $multiline
   * @return static
   */
  public function setMultiline(bool $multiline): static {
    $this->multiline = $multiline;
    return $this;
  }

  /**
   * Remove all whitespace characters
   *
   * When enabled, removes ALL whitespace including:
   * - Regular spaces
   * - Tabs (\t)
   * - Newlines (\n, \r)
   * - Other whitespace (\v, \f)
   * - No-break spaces (\xa0)
   * - Full-width spaces (　)
   *
   * Note: This will remove newlines even when multiline=true.
   * If you want to preserve newlines, don't enable this option.
   *
   * @param bool $noWhitespace
   * @return static
   */
  public function setNoWhitespace(bool $noWhitespace): static {
    $this->noWhitespace = $noWhitespace;
    return $this;
  }

  /**
   * @param bool $noControl
   * @return static
   */
  public function setNoControl(bool $noControl): static {
    $this->noControl = $noControl;
    return $this;
  }

  /**
   * @param string|null $eol
   * @return static
   */
  public function setEol(?string $eol): static {
    $this->eol = $eol;
    return $this;
  }

  /**
   * @param string|null $trim
   * @return static
   */
  public function setTrim(?string $trim): static {
    $this->trim = $trim;
    return $this;
  }

  /**
   * @param string|null $pattern
   * @return static
   */
  public function setPattern(?string $pattern): static {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * @param string|null $form Unicode normalization form (NFC|NFD|NFKC|NFKD) or null to disable
   * @return static
   */
  public function setUnicodeNormalization(?string $form): static {
    if ($form !== null && !in_array($form, [
      UnicodeNormalization::NFC,
      UnicodeNormalization::NFD,
      UnicodeNormalization::NFKC,
      UnicodeNormalization::NFKD
    ])) {
      throw new \InvalidArgumentException("Invalid normalization form: $form");
    }
    $this->unicodeNormalization = $form;
    return $this;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
    $value = $this->scrubUtf8($value);
    if ($this->unicodeNormalization !== null) {
      $value = $this->normalizeUnicode($value, $this->unicodeNormalization);
    }
    if ($this->mb !== null) {
      $value = mb_convert_kana($value, $this->mb, "UTF-8");
    }
    if ($this->case !== null) {
      $value = mb_convert_case($value, $this->case, "UTF-8");
    }
    if ($this->multiline) {
      if ($this->eol !== null) {
        $value = preg_replace("/(\r\n|\r|\n)/u", $this->eol, $value);
      }
    }
    else {
      $value = preg_replace("/[\r\n]/u", " ", $value);
    }
    if ($this->noControl) {
      // 0x00-0x1f, 0x7f and 0x00a0 (no-break space, which is 0xc2a0 in UTF-8) except CR and LF
      $value = preg_replace("/[\\x00-\\x09\\x0B\\x0c\\x0e-\\x1f\\x7f\\xa0]/u", " ", $value);
    }
    if ($this->noWhitespace) {
      $value = $this->removeWhitespace($value);
    }
    if ($this->trim == self::LEFT || $this->trim == self::BOTH) {
      $value = preg_replace("/^[[:space:]\\00\\xa0　]+/u", "", $value);
    }
    if ($this->trim == self::RIGHT || $this->trim == self::BOTH) {
      $value = preg_replace("/[[:space:]\\00\\xa0　]+$/u", "", $value);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    $lengthError = $this->validateLength($value);
    if ($lengthError !== null) {
      return $lengthError;
    }
    if (isset($this->pattern) && !preg_match($this->pattern, $value)) {
      return new PatternMismatchError($this);
    }
    return parent::doValidate($value);
  }
}
