<?php
namespace Coroq;

class Form {
  /** @var array<Input|Form> */
  protected $items = [];
  /** @var bool */
  protected $disabled = false;
  /** @var array */
  protected $options = [];

  /**
   * @param array $options
   */
  public function __construct($options = []) {
    $this->options = $options + [
      "path_separator" => "/",
    ];
  }

  /**
   * @param string $name
   * @return Input|Form
   */
  public function getItem($name) {
    if (!isset($this->items[$name])) {
      throw new \LogicException("Item '$name' not found.");
    }
    return $this->items[$name];
  }

  /**
   * @param array|string $path
   * @return Input|Form
   */
  public function getItemIn($path) {
    if (!is_array($path)) {
      $path = explode($this->options["path_separator"], $path);
    }
    $item = $this;
    foreach ($path as $node) {
      $item = $item->getItem($node);
    }
    return $item;
  }

  /**
   * @return array<Input|Form>
   */
  public function getItems() {
    return $this->items;
  }

  /**
   * @return array<Input|Form>
   */
  public function getEnabledItems() {
    return array_filter($this->items, function($item) {
      return !$item->isDisabled();
    });
  }

  /**
   * @param string $name
   * @param Input|Form $item
   * @return Form
   */
  public function setItem($name, $item) {
    $this->items[$name] = $item;
    return $this;
  }

  /**
   * @param Input|Form $item
   * @return Form
   */
  public function addItem($item) {
    $this->items[] = $item;
    return $this;
  }

  /**
   * @param string $name
   * @return Form
   */
  public function unsetItem($name) {
    unset($this->items[$name]);
    return $this;
  }

  /**
   * @param array<Input|Form> $items
   * @return Form
   */
  public function setItems(array $items) {
    $this->items = $items;
    return $this;
  }

  /**
   * @return array
   */
  public function getValue() {
    $values = [];
    foreach ($this->getEnabledItems() as $i => $item) {
      $values[$i] = $item->getValue();
    }
    return $values;
  }

  /**
   * @param array $value
   * @return Form
   */
  public function setValue($value) {
    foreach ($this->items as $i => $item) {
      $item->setValue(@$value[$i]);
    }
    return $this;
  }

  /**
   * @return Form
   */
  public function clear() {
    foreach ($this->items as $item) {
      $item->clear();
    }
    return $this;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    foreach ($this->getEnabledItems() as $i => $item) {
      if (!$item->isEmpty()) {
        return false;
      }
    }
    return true;
  }

  /**
   * @return array
   */
  public function getFilled() {
    $values = [];
    foreach ($this->getEnabledItems() as $i => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      if ($item instanceof Form) {
        $values[$i] = $item->getFilled();
      }
      else {
        $values[$i] = $item->getValue();
      }
    }
    return $values;
  }

  /**
   * @return bool
   */
  public function isDisabled() {
    return $this->disabled;
  }

  /**
   * @return bool $disabled
   * @return Form
   */
  public function disable($disabled = true) {
    $this->disabled = (bool)$disabled;
    return $this;
  }

  /**
   * @return Form
   */
  public function enable() {
    return $this->disable(false);
  }

  /**
   * @return bool
   */
  public function validate() {
    foreach ($this->getEnabledItems() as $item) {
      $item->validate();
    }
    return !$this->getError();
  }

  /**
   * @return array<Input\Error|array>
   */
  public function getError() {
    $errs = [];
    foreach ($this->getEnabledItems() as $i => $item) {
      $errs[$i] = $item->getError();
    }
    return array_diff($errs, [null]);
  }

  /**
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }
}
