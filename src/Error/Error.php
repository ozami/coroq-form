<?php
declare(strict_types=1);
namespace Coroq\Form\Error;

use Coroq\Form\FormItem\FormItemInterface;

abstract class Error {
  public function __construct(
    public FormItemInterface $formItem,
  ) {
  }
}
