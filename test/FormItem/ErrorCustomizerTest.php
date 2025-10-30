<?php
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\BooleanInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\InvalidEmailError;
use Coroq\Form\Error\TooShortError;
use Coroq\Form\Error\Error;
use Coroq\Form\FormItem\FormItemInterface;
use PHPUnit\Framework\TestCase;

// Custom error for testing
class NoAgreementError extends Error {}
class CustomEmptyError extends Error {}

class ErrorCustomizerTest extends TestCase {
  public function testErrorCustomizerReplacesError() {
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof EmptyError) {
          return new CustomEmptyError($formItem);
        }
        return $error;
      });

    $input->validate();
    $this->assertInstanceOf(CustomEmptyError::class, $input->getError());
  }

  public function testErrorCustomizerMutatesError() {
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        $error->customProperty = 'test_value';
        return $error;
      });

    $input->validate();
    $error = $input->getError();
    $this->assertInstanceOf(EmptyError::class, $error);
    $this->assertSame('test_value', $error->customProperty);
  }

  public function testErrorCustomizerReturnsSameErrorUnchanged() {
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        return $error;
      });

    $input->validate();
    $this->assertInstanceOf(EmptyError::class, $input->getError());
  }

  public function testErrorCustomizerWithBooleanInput() {
    $agree = (new BooleanInput())
      ->setRequired(true)
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof EmptyError) {
          return new NoAgreementError($formItem);
        }
        return $error;
      });

    $agree->validate();
    $this->assertInstanceOf(NoAgreementError::class, $agree->getError());
  }

  public function testErrorCustomizerWithEmailInput() {
    $email = (new EmailInput())
      ->setValue('invalid-email')
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof InvalidEmailError) {
          return new InvalidError($formItem);
        }
        return $error;
      });

    $email->validate();
    $this->assertInstanceOf(InvalidError::class, $email->getError());
  }

  public function testErrorCustomizerWithIntegerInput() {
    $age = (new IntegerInput())
      ->setMin(18)
      ->setValue('15')
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        $error->context = ['min' => 18, 'actual' => 15];
        return $error;
      });

    $age->validate();
    $error = $age->getError();
    $this->assertTrue(isset($error->context));
    $this->assertSame(['min' => 18, 'actual' => 15], $error->context);
  }

  public function testErrorCustomizerNotCalledWhenNoError() {
    $customizerCalled = false;
    $input = (new TextInput())
      ->setValue('valid')
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem) use (&$customizerCalled): Error {
        $customizerCalled = true;
        return $error;
      });

    $input->validate();
    $this->assertFalse($customizerCalled);
    $this->assertNull($input->getError());
  }

  public function testErrorCustomizerNotCalledWhenErrorIsNull() {
    $customizerCalled = false;
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem) use (&$customizerCalled): Error {
        $customizerCalled = true;
        return $error;
      });

    $input->setError(null);
    $this->assertFalse($customizerCalled);
  }

  public function testErrorCustomizerCalledWhenSetErrorDirectly() {
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        return new CustomEmptyError($formItem);
      });

    $input->setError(new EmptyError($input));
    $this->assertInstanceOf(CustomEmptyError::class, $input->getError());
  }

  public function testErrorCustomizerRunsAfterValidator() {
    $input = (new TextInput())
      ->setValue('test')
      ->setValidator(function($formItem, $value) {
        return new InvalidError($formItem);
      })
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof InvalidError) {
          return new CustomEmptyError($formItem);
        }
        return $error;
      });

    $input->validate();
    $this->assertInstanceOf(CustomEmptyError::class, $input->getError());
  }

  public function testErrorCustomizerWithMultipleErrorTypes() {
    $input = (new TextInput())
      ->setMinLength(10)
      ->setValue('short')
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof TooShortError) {
          $error->message = 'Custom too short message';
        }
        return $error;
      });

    $input->validate();
    $error = $input->getError();
    $this->assertInstanceOf(TooShortError::class, $error);
    $this->assertSame('Custom too short message', $error->message);
  }

  public function testSetErrorCustomizerReturnsFormItem() {
    $input = new TextInput();
    $result = $input->setErrorCustomizer(function($error, $item) { return $error; });
    $this->assertSame($input, $result);
  }

  public function testSetErrorCustomizerWithNull() {
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        return new CustomEmptyError($formItem);
      });

    // Set to null to remove customizer
    $input->setErrorCustomizer(null);
    $input->validate();

    $this->assertInstanceOf(EmptyError::class, $input->getError());
    $this->assertNotInstanceOf(CustomEmptyError::class, $input->getError());
  }

  public function testErrorCustomizerReceivesCorrectFormItem() {
    $receivedFormItem = null;
    $input = (new TextInput())
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem) use (&$receivedFormItem): Error {
        $receivedFormItem = $formItem;
        return $error;
      });

    $input->validate();
    $this->assertSame($input, $receivedFormItem);
  }

  public function testErrorCustomizerCanAccessFormItemProperties() {
    $input = (new TextInput())
      ->setLabel('Username')
      ->setErrorCustomizer(function(Error $error, FormItemInterface $formItem): Error {
        if ($error instanceof EmptyError && $formItem->getLabel() === 'Username') {
          return new CustomEmptyError($formItem);
        }
        return $error;
      });

    $input->validate();
    $this->assertInstanceOf(CustomEmptyError::class, $input->getError());
  }
}
