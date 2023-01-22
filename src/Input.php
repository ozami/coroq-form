<?php
namespace Coroq;
use Coroq\Input\Error;

class Input {
  /** @var mixed */
  protected $value = "";
  /** @var bool */
  protected $required = true;
  /** @var bool */
  protected $read_only = false;
  /** @var bool */
  protected $disabled = false;
  /** @var string */
  protected $label = "";
  /** @var Error|null */
  protected $error = null;
  /** @var callable|null */
  protected $error_stringifier;
  /** @var callable|null */
  protected static $default_error_stringifier = '\Coroq\Input::basicErrorStringifier';

  public function __construct() {
  }

  /**
   * @return mixed
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @param mixed $value
   * @return $this
   */
  public function setValue($value) {
    if ($this->read_only) {
      return $this;
    }
    $old_value = $this->value;
    $this->value = $this->filter($value);
    $this->setError(null);
    return $this;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return $this->getValue() . "" == "";
  }

  /**
   * @return $this
   */
  public function clear() {
    $this->setValue("");
    return $this;
  }

  /**
   * @return bool
   */
  public function isRequired() {
    return $this->required;
  }

  /**
   * @param bool $required
   * @return $this
   */
  public function setRequired($required) {
    $this->required = (bool)$required;
    return $this;
  }

  /**
   * @return bool
   */
  public function isReadOnly() {
    return $this->read_only;
  }

  /**
   * @param bool $read_only
   * @return $this
   */
  public function setReadOnly($read_only) {
    $this->read_only = (bool)$read_only;
    return $this;
  }

  /**
   * @return bool
   */
  public function isDisabled() {
    return $this->disabled;
  }

  /**
   * @param bool $disabled
   * @return $this
   */
  public function disable($disabled = true) {
    $this->disabled = (bool)$disabled;
    return $this;
  }

  /**
   * @return $this
   */
  public function enable() {
    return $this->disable(false);
  }

  /**
   * @return string
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param string $label
   * @return $this
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @return Error|null
   */
  public function getError() {
    return $this->error;
  }

  /**
   * @param Error|null $error
   * @return $this
   */
  public function setError($error) {
    $this->error = $error;
    return $this;
  }

  /**
   * @return bool
   */
  public function hasError() {
    return $this->getError() !== null;
  }

  /**
   * @return string|null
   */
  public function getErrorString() {
    $error = $this->getError();
    if ($error === null) {
      return null;
    }
    $error_string = $error->code;
    if (static::$default_error_stringifier) {
      $error_string = call_user_func(static::$default_error_stringifier, $error) ?: $error_string;
    }
    if ($this->error_stringifier) {
      $error_string = call_user_func($this->error_stringifier, $error) ?: $error_string;
    }
    return $error_string;
  }

  /**
   * @param callable|null $error_stringifier
   * @return $this
   */
  public function setErrorStringifier(callable $error_stringifier = null) {
    $this->error_stringifier = $error_stringifier;
    return $this;
  }

  /**
   * @return bool
   */
  public function validate() {
    $this->setError(null);
    if ($this->isEmpty()) {
      if ($this->isRequired()) {
        $this->setError(new Error("err_empty", $this));
      }
    }
    else {
      $error = $this->doValidate($this->getValue());
      if ($error) {
        if (!($error instanceof Error)) {
          $error = new Error($error, $this);
        }
        $this->setError($error);
      }
    }
    return !$this->hasError();
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function filter($value) {
    return $value;
  }

  /**
   * @param mixed $value
   * @return Error|string|null
   */
  public function doValidate($value) {
    return null;
  }

  public static function setDefaultErrorStringifier(callable $error_stringifier) {
    static::$default_error_stringifier = $error_stringifier;
  }

  public static function basicErrorStringifier(Error $error) {
    $error_string_templates = [
      "err_empty" => function($error) {
        if ($error->input instanceof \Coroq\Input\Select || $error->input instanceof \Coroq\Input\MultiSelect) {
          return "選択してください";
        }
        return "入力してください";
      },
      "err_invalid" => "正しく入力してください",
      "err_not_katakana" => "カタカナで入力してください",
      "err_too_short" => function($error) {
        return $error->input->getMinLength() . " 文字以上で入力してください";
      },
      "err_too_long" => function($error) {
        return $error->input->getMaxLength() . " 文字以内で入力してください";
      },
    ];
    $error_string = @$error_string_templates[$error->code];
    if (is_callable($error_string)) {
      $error_string = call_user_func($error_string, $error);
    }
    return $error_string;
  }
}
