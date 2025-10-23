<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\PatternMismatchError;

/**
 * Text input with extensive filtering options (trim, case conversion, mb_convert_kana, etc.)
 */
class TextInput extends Input implements HasLengthRangeInterface {
  use LengthRangeTrait;
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
  protected $noSpace = false;
  /** @var bool */
  protected $noControl = true;
  /** @var string|null */
  protected $eol = "\n"; // used only when $multiline is true. set null for no conversion
  /** @var string|null */
  protected $trim = self::BOTH;
  /** @var string|null */
  protected $pattern = null;

  /**
   * @param string|null $mb
   * @return $this
   */
  public function setMb(?string $mb): self {
    $this->mb = $mb;
    return $this;
  }

  /**
   * @param int|null $case
   * @return $this
   */
  public function setCase(?int $case): self {
    $this->case = $case;
    return $this;
  }

  /**
   * @param bool $multiline
   * @return $this
   */
  public function setMultiline(bool $multiline): self {
    $this->multiline = $multiline;
    return $this;
  }

  /**
   * @param bool $noSpace
   * @return $this
   */
  public function setNoSpace(bool $noSpace): self {
    $this->noSpace = $noSpace;
    return $this;
  }

  /**
   * @param bool $noControl
   * @return $this
   */
  public function setNoControl(bool $noControl): self {
    $this->noControl = $noControl;
    return $this;
  }

  /**
   * @param string|null $eol
   * @return $this
   */
  public function setEol(?string $eol): self {
    $this->eol = $eol;
    return $this;
  }

  /**
   * @param string|null $trim
   * @return $this
   */
  public function setTrim(?string $trim): self {
    $this->trim = $trim;
    return $this;
  }

  /**
   * @param string|null $pattern
   * @return $this
   */
  public function setPattern(?string $pattern): self {
    $this->pattern = $pattern;
    return $this;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    $value = "$value";
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
    if ($this->noSpace) {
      $value = preg_replace("/[[:space:]\\00\\xa0　]/u", "", $value);
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
    if (!preg_match("//u", $value)) {
      return new InvalidError($this);
    }
    $lengthError = $this->validateLength($value);
    if ($lengthError !== null) {
      return $lengthError;
    }
    if (isset($this->pattern) && !preg_match($this->pattern, $value)) {
      return new PatternMismatchError($this);
    }
    return null;
  }
}
