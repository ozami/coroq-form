<?php
namespace Coroq\Input;

class String extends \Coroq\Input {
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
  protected $no_space = false;
  /** @var bool */
  protected $no_control = true;
  /** @var string|null */
  protected $eol = "\n"; // used only when $multiline is true. set null for no conversion
  /** @var string|null */
  protected $trim = self::BOTH;
  /** @var int */
  protected $min_length = 0;
  /** @var int */
  protected $max_length = PHP_INT_MAX;
  /** @var string|null */
  protected $pattern = null;

  /**
   * @param string|null $mb
   * @return \Coroq\Input\String
   */
  public function setMb($mb) {
    $this->mb = $mb;
    return $this;
  }
  
  /**
   * @param int|null $case
   * @return \Coroq\Input\String
   */
  public function setCase($case) {
    $this->case = $case;
    return $this;
  }

  /**
   * @param bool $multiline
   * @return \Coroq\Input\String
   */
  public function setMultiline($multiline) {
    $this->multiline = $multiline;
    return $this;
  }

  /**
   * @param bool $no_space
   * @return \Coroq\Input\String
   */
  public function setNoSpace($no_space) {
    $this->no_space = $no_space;
    return $this;
  }

  /**
   * @param bool $no_control
   * @return \Coroq\Input\String
   */
  public function setNoControl($no_control) {
    $this->no_control = $no_control;
    return $this;
  }

  /**
   * @param string|null $eol
   * @return \Coroq\Input\String
   */
  public function setEol($eol) {
    $this->eol = $eol;
    return $this;
  }

  /**
   * @param string|null $trim
   * @return \Coroq\Input\String
   */
  public function setTrim($trim) {
    $this->trim = $trim;
    return $this;
  }

  /**
   * @param int $min_length
   * @return \Coroq\Input\String
   */
  public function setMinLength($min_length) {
    $this->min_length = $min_length;
    return $this;
  }

  /**
   * @param int $max_length
   * @return \Coroq\Input\String
   */
  public function setMaxLength($max_length) {
    $this->max_length = $max_length;
    return $this;
  }

  /**
   * @param string|null $pattern
   * @return \Coroq\Input\String
   */
  public function setPattern($pattern) {
    $this->pattern = $pattern;
    return $this;
  }

  public function filter($value) {
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
    if ($this->no_control) {
      // 0x00-0x1f, 0x7f and 0x00a0 (no-break space, which is 0xc2a0 in UTF-8) except CR and LF
      $value = preg_replace("/[\\x00-\\x09\\x0B\\x0c\\x0e-\\x1f\\x7f\\x{00a0}]/u", " ", $value);
    }
    if ($this->no_space) {
      $value = preg_replace("/[[:space:]\\x{00a0}　]/u", "", $value);
    }
    if ($this->trim == self::LEFT || $this->trim == self::BOTH) {
      $value = preg_replace("/^[[:space:]\\x{00a0}　]+/u", "", $value);
    }
    if ($this->trim == self::RIGHT || $this->trim == self::BOTH) {
      $value = preg_replace("/[[:space:]\\x{00a0}　]+$/u", "", $value);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if (!preg_match("//u", $value)) {
      return "err_invalid";
    }
    $length = mb_strlen($value, "UTF-8");
    if ($length < $this->min_length) {
      return "err_too_short";
    }
    if ($length > $this->max_length) {
      return "err_too_long";
    }
    if (isset($this->pattern) && !preg_match($this->pattern, $value)) {
      return "err_invalid";
    }
  }
}
