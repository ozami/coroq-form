<?php
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\TooLongError;
use Coroq\Form\Error\SourceItemInvalidError;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\InvalidEmailError;
use Coroq\Form\Error\InvalidUrlError;
use Coroq\Form\FormItem\Input;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\UrlInput;
use Coroq\Form\FormItem\Derived;
use PHPUnit\Framework\TestCase;

class ErrorMessageFormatterTest extends TestCase {
  public function testFormatWithStringMessage() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => 'This field is required'
    ]);

    $this->assertSame('This field is required', $formatter->format($error));
  }

  public function testFormatWithClosureMessage() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => function(EmptyError $error) {
        return 'Field is required';
      }
    ]);

    $this->assertSame('Field is required', $formatter->format($error));
  }

  public function testFormatWithClosureAccessingErrorProperties() {
    $formatter = new ErrorMessageFormatter();
    $input = (new TextInput())->setMaxLength(10)->setLabel('Username');
    $error = new TooLongError($input);

    $formatter->setMessages([
      TooLongError::class => function(TooLongError $error) {
        return $error->formItem->getLabel() . ' must be at most ' . $error->formItem->getMaxLength() . ' characters';
      }
    ]);

    $this->assertSame('Username must be at most 10 characters', $formatter->format($error));
  }

  public function testFormatThrowsExceptionWhenNoMessageDefined() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([]);

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('No message defined for error class: Coroq\Form\Error\EmptyError');

    $formatter->format($error);
  }

  public function testFormatThrowsExceptionWhenClosureReturnsNonString() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => function(EmptyError $error) {
        return 123; // Return non-string
      }
    ]);

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Closure must return a string for error class: Coroq\Form\Error\EmptyError');

    $formatter->format($error);
  }

  public function testFormatThrowsExceptionWhenClosureReturnsNull() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => function(EmptyError $error) {
        return null; // Return null
      }
    ]);

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Closure must return a string for error class: Coroq\Form\Error\EmptyError');

    $formatter->format($error);
  }

  public function testFormatWithMultipleErrorTypes() {
    $formatter = new ErrorMessageFormatter();

    $formatter->setMessages([
      EmptyError::class => 'Required field',
      TooLongError::class => 'Too long',
      SourceItemInvalidError::class => 'Dependency invalid'
    ]);

    $input = new Input();
    $emptyError = new EmptyError($input);
    $this->assertSame('Required field', $formatter->format($emptyError));

    $textInput = (new TextInput())->setMaxLength(10);
    $tooLongError = new TooLongError($textInput);
    $this->assertSame('Too long', $formatter->format($tooLongError));

    $derived = new Derived();
    $sourceError = new SourceItemInvalidError($derived);
    $this->assertSame('Dependency invalid', $formatter->format($sourceError));
  }

  public function testSetMessagesReplacesExistingMessages() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => 'First message'
    ]);

    $this->assertSame('First message', $formatter->format($error));

    // Replace messages
    $formatter->setMessages([
      EmptyError::class => 'Second message'
    ]);

    $this->assertSame('Second message', $formatter->format($error));
  }

  public function testFormatWithMixedStringAndClosureMessages() {
    $formatter = new ErrorMessageFormatter();

    $formatter->setMessages([
      EmptyError::class => 'Static message',
      TooLongError::class => function(TooLongError $error) {
        return 'Dynamic message';
      }
    ]);

    $input = new Input();
    $emptyError = new EmptyError($input);
    $this->assertSame('Static message', $formatter->format($emptyError));

    $textInput = (new TextInput())->setMaxLength(10);
    $tooLongError = new TooLongError($textInput);
    $this->assertSame('Dynamic message', $formatter->format($tooLongError));
  }

  public function testFormatWithNumericStringMessage() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => '123'
    ]);

    $this->assertSame('123', $formatter->format($error));
  }

  public function testFormatWithEmptyStringMessage() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => ''
    ]);

    $this->assertSame('', $formatter->format($error));
  }

  public function testSetMessageOverwritesExistingMessage() {
    $formatter = new ErrorMessageFormatter();
    $input = new Input();
    $error = new EmptyError($input);

    $formatter->setMessages([
      EmptyError::class => 'First message'
    ]);

    $this->assertSame('First message', $formatter->format($error));

    // Overwrite with setMessage
    $formatter->setMessage(EmptyError::class, 'Second message');

    $this->assertSame('Second message', $formatter->format($error));
  }

  public function testSetMessageWithClosure() {
    $formatter = new ErrorMessageFormatter();
    $input = (new TextInput())->setMaxLength(10);
    $error = new TooLongError($input);

    $formatter->setMessage(TooLongError::class, function(TooLongError $e) {
      return 'Max ' . $e->formItem->getMaxLength();
    });

    $this->assertSame('Max 10', $formatter->format($error));
  }

  public function testSetMessageCanBeCalledMultipleTimes() {
    $formatter = new ErrorMessageFormatter();

    $formatter->setMessage(EmptyError::class, 'Required');
    $formatter->setMessage(TooLongError::class, 'Too long');

    $input = new Input();
    $emptyError = new EmptyError($input);
    $this->assertSame('Required', $formatter->format($emptyError));

    $textInput = (new TextInput())->setMaxLength(10);
    $tooLongError = new TooLongError($textInput);
    $this->assertSame('Too long', $formatter->format($tooLongError));
  }

  public function testFormatMatchesErrorHierarchy() {
    $formatter = new ErrorMessageFormatter();

    // Define only base error message
    $formatter->setMessages([
      InvalidError::class => 'Invalid value'
    ]);

    // InvalidEmailError extends InvalidError, so it should match
    $emailInput = new EmailInput();
    $emailError = new InvalidEmailError($emailInput);
    $this->assertSame('Invalid value', $formatter->format($emailError));

    // InvalidUrlError also extends InvalidError
    $urlInput = new UrlInput();
    $urlError = new InvalidUrlError($urlInput);
    $this->assertSame('Invalid value', $formatter->format($urlError));
  }

  public function testFormatLaterDefinitionOverridesEarlier() {
    $formatter = new ErrorMessageFormatter();

    // Define base message first, then specific override
    $formatter->setMessages([
      InvalidError::class => 'Generic invalid',
      InvalidEmailError::class => 'Invalid email',
    ]);

    // InvalidEmailError should use specific message
    $emailInput = new EmailInput();
    $emailError = new InvalidEmailError($emailInput);
    $this->assertSame('Invalid email', $formatter->format($emailError));

    // InvalidUrlError should fall back to base message
    $urlInput = new UrlInput();
    $urlError = new InvalidUrlError($urlInput);
    $this->assertSame('Generic invalid', $formatter->format($urlError));
  }

  public function testSetMessageOverridesBase() {
    $formatter = new ErrorMessageFormatter();

    // Set base messages
    $formatter->setMessages([
      InvalidError::class => 'Invalid value',
      EmptyError::class => 'Required',
    ]);

    // Override specific subtype with setMessage
    $formatter->setMessage(InvalidEmailError::class, 'Please enter a valid email');

    $emailInput = new EmailInput();
    $emailError = new InvalidEmailError($emailInput);
    $this->assertSame('Please enter a valid email', $formatter->format($emailError));

    // Base message still works for other subtypes
    $urlInput = new UrlInput();
    $urlError = new InvalidUrlError($urlInput);
    $this->assertSame('Invalid value', $formatter->format($urlError));
  }

  public function testFormatMessageOrderWithMultipleInheritanceLevels() {
    $formatter = new ErrorMessageFormatter();

    // Most specific should win
    $formatter->setMessages([
      InvalidError::class => 'Base invalid',
      InvalidEmailError::class => 'Specific email error',
    ]);

    $emailInput = new EmailInput();
    $emailError = new InvalidEmailError($emailInput);

    // Should match the most specific (InvalidEmailError), not the base
    $this->assertSame('Specific email error', $formatter->format($emailError));
  }
}
