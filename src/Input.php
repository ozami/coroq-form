<?php
namespace Coroq;
use \Coroq\Input\Error;

class Input {
  /** @var mixed */
  protected $value = "";
  /** @var bool */
  protected $required = true;
  /** @var bool */
  protected $read_only = false;
  /** @var bool */
  protected $disabled = false;
  /** @var Error|null */
  protected $error = null;
  /** @var array<callable> */
  protected $observers = [];

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
   * @return Input
   */
  public function setValue($value) {
    if ($this->read_only) {
      return $this;
    }
    $old_value = $this->value;
    $this->value = $this->filter($value);
    $this->setError(null);
    if ($this->value !== $old_value) {
      foreach ($this->observers as $observer) {
        call_user_func($observer, $this);
      }
    }
    return $this;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return $this->getValue() === "";
  }

  /**
   * @return Input
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
   * @return Input
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
   * @return Input
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
   * @return Input
   */
  public function disable($disabled = true) {
    $this->disabled = (bool)$disabled;
    return $this;
  }

  /**
   * @return Input
   */
  public function enable() {
    return $this->disable(false);
  }

  /**
   * @return Error|null
   */
  public function getError() {
    return $this->error;
  }

  /**
   * @param Error|null $error
   * @return Input
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
        $this->setError(new Error($error, $this));
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
   * @return string|null
   */
  public function doValidate($value) {
    return null;
  }

  /**
   * @param callable $observer
   * @return Input
   */
  public function addObserver($observer) {
    $this->observers[] = $observer;
    return $this;
  }
}
