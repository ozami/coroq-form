# Coroq Form

PHP form validation library. Type-safe, zero dependencies.

## Scope

### What This Library Does

- **Value validation and filtering** - Validates and normalizes form input (email, URL, numbers, dates, text, etc.)
- **Type-safe form handling** - Provides typed input classes with IDE autocomplete support
- **Error management** - Tracks validation errors as typed objects (not string codes)
- **Nested forms** - Supports hierarchical form structures (forms within forms)
- **Dynamic lists** - Manages repeating form items (e.g., multiple email addresses)
- **Cross-field validation** - Validates relationships between fields (e.g., password confirmation)

### What This Library Does NOT Do

- **HTML rendering** - This library does not generate HTML. You write your own templates.
- **HTTP request handling** - Does not parse `$_POST` or `$_FILES`. You pass data to `setValue()`.
- **CSRF protection** - Does not generate or validate CSRF tokens. Use your framework's CSRF protection.
- **Database operations** - Does not save or load data from databases. Use your ORM/database layer.
- **Framework integration** - Framework-agnostic. Integrate it yourself or use it standalone.
- **Client-side validation** - Server-side only. Add your own JavaScript validation if needed.

This is a **validation and data processing layer** that sits between your HTTP layer and business logic.

## Requirements

- PHP >= 8.0
- mbstring extension
- fileinfo extension
- filter extension
- bcmath extension
- intl extension (optional)

## Installation

```bash
composer require coroq/form
```

## Quick Start

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\TextInput;

class LoginForm extends Form {
    public readonly EmailInput $email;
    public readonly TextInput $password;

    public function __construct() {
        $this->email = new EmailInput();
        $this->password = new TextInput();
    }
}

$form = new LoginForm();
$form->setValue($_POST);

if ($form->validate()) {
    $email = $form->email->getEmail();
    // Process login...
} else {
    $errors = $form->getError();
    // Handle validation errors
}
```

## Core Concepts

### 1. Forms and Form Items
A **Form** holds items with names. Each item represents a single field - an email address, a username, a number, etc.

### 2. Setting Values
When you assign values to a form, the form distributes those values to its items by matching names. Each item receives and stores its corresponding value.

### 3. Filtering
The moment a value is set, it is automatically **filtered** - normalized and transformed according to the item's type. Email addresses get trimmed and lowercased, numbers get stripped of formatting.

### 4. Validation and Errors
When you request validation, the form checks all its items. Each item validates its value against its rules. The form returns whether all items are valid. Invalid items store an error object representing what went wrong.

## Defining Forms

### Recommended: Form Subclasses

Define form classes with typed readonly properties for IDE support:

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\Select;

class UserRegistrationForm extends Form {
    public readonly TextInput $name;
    public readonly EmailInput $email;
    public readonly IntegerInput $age;
    public readonly Select $country;

    public function __construct() {
        $this->name = (new TextInput())
            ->setLabel('Name')
            ->setMaxLength(100);

        $this->email = (new EmailInput())
            ->setLabel('Email');

        $this->age = (new IntegerInput())
            ->setLabel('Age')
            ->setMin(18)
            ->setMax(120);

        $this->country = (new Select())
            ->setLabel('Country')
            ->setOptions([
                'us' => 'United States',
                'jp' => 'Japan',
                'uk' => 'United Kingdom'
            ]);
    }
}

// Usage with full IDE support
$form = new UserRegistrationForm();
$form->setValue($_POST);

if ($form->validate()) {
    // IDE knows the exact types
    $name = $form->name->getValue();
    $email = $form->email->getEmail();
    $age = $form->age->getInteger();
}
```

### Dynamic Forms (for temporal use)

For dynamic or one-off forms, you can use Form directly:

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\TextInput;

$form = new Form();
$form->email = new EmailInput();
$form->name = new TextInput();

$form->setValue($_POST);
$form->validate();
```

## Form State

Form items have three state flags that control their behavior:

### Required/Optional

**Input level:**
- `setRequired(true)` (default) - Empty value fails validation with EmptyError
- `setRequired(false)` - Empty value passes validation

**Form level:**
- `setRequired(true)` (default) - Validates all enabled items even if form is empty
- `setRequired(false)` - If the entire form is empty, validation passes without checking items

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class ProfileForm extends Form {
    public readonly TextInput $name;
    public readonly TextInput $nickname;

    public function __construct() {
        $this->name = new TextInput();  // Required (default)
        $this->nickname = (new TextInput())
            ->setRequired(false);  // Optional
    }
}

$form = new ProfileForm();
$form->setValue(['name' => '', 'nickname' => '']);
$form->validate();
// name has EmptyError, nickname passes validation
```

**Form-level example:**
```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class AddressForm extends Form {
    public readonly TextInput $street;
    public readonly TextInput $city;

    public function __construct() {
        $this->street = new TextInput();
        $this->city = new TextInput();
        $this->setRequired(false);  // Make entire form optional
    }
}

$form = new AddressForm();
$form->setValue(['street' => '', 'city' => '']);
$form->validate();  // Passes! Empty optional form skips item validation
```

### Read-Only

**Input level:**
- `setValue()` is ignored (value doesn't change)
- Item is included in `getValue()` and `validate()`

**Form level:**
- `setValue()` is ignored for the entire form
- Items are included in `getValue()` and `validate()`

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class UserForm extends Form {
    public readonly TextInput $id;
    public readonly TextInput $name;

    public function __construct() {
        $this->id = (new TextInput())
            ->setValue('12345')
            ->setReadOnly(true);
        $this->name = new TextInput();
    }
}

$form = new UserForm();
$form->setValue(['id' => '99999', 'name' => 'Taro']);

echo $form->id->getValue();    // "12345" (unchanged)
echo $form->name->getValue();  // "Taro"
$form->validate();             // Both items are validated
```

**Form-level example:**
```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class DisplayForm extends Form {
    public readonly TextInput $field;

    public function __construct() {
        $this->field = (new TextInput())->setValue('fixed');
        $this->setReadOnly(true);  // Entire form is read-only
    }
}

$form = new DisplayForm();
$form->setValue(['field' => 'new value']);  // Ignored!
echo $form->field->getValue();  // "fixed"
```

### Disabled

**Input level:**
- Excluded from `getValue()` - not in returned array
- Excluded from `setValue()` - value is not set
- Excluded from `validate()` - not validated

**Form level:**
- Excluded from parent form's `getValue()`, `setValue()`, and `validate()`
- Useful for conditionally hiding entire form sections

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class OrderForm extends Form {
    public readonly TextInput $customerName;
    public readonly TextInput $legacyField;

    public function __construct() {
        $this->customerName = new TextInput();
        $this->legacyField = (new TextInput())
            ->setDisabled(true);
    }
}

$form = new OrderForm();
$form->setValue([
    'customerName' => 'Taro',
    'legacyField' => 'ignored'
]);

$values = $form->getValue();
// ['customerName' => 'Taro']
// legacyField is completely ignored
```

**Form-level example:**
```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class CheckoutForm extends Form {
    public readonly TextInput $name;
    public readonly AddressForm $billing;
    public readonly AddressForm $shipping;

    public function __construct() {
        $this->name = new TextInput();
        $this->billing = new AddressForm();
        $this->shipping = new AddressForm();
    }

    public function disableShipping() {
        $this->shipping->setDisabled(true);
        return $this;
    }
}

$form = new CheckoutForm();
$form->disableShipping();

$form->setValue([
    'name' => 'Taro',
    'billing' => ['street' => '1-1-1', 'city' => 'Tokyo'],
    'shipping' => ['street' => '2-2-2', 'city' => 'Osaka']  // Ignored!
]);

$values = $form->getValue();
// ['name' => 'Taro', 'billing' => ['street' => '1-1-1', 'city' => 'Tokyo']]
// shipping is completely excluded
```

### State Summary

| State | setValue() | getValue() | validate() |
|-------|------------|------------|------------|
| Normal (required=true) | ✓ Sets value | ✓ Included | ✓ Validated, must not be empty |
| Optional (required=false) | ✓ Sets value | ✓ Included | ✓ Validated, empty allowed |
| Read-only | ✗ Ignored | ✓ Included | ✓ Validated |
| Disabled | ✗ Ignored | ✗ Excluded | ✗ Skipped |

**Form-level states apply to the form as a whole:**
- Required=false on Form: Empty form passes validation
- ReadOnly on Form: setValue() ignored for entire form
- Disabled on Form: Entire form excluded from parent's getValue/setValue/validate

## Validation

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;

class LoginForm extends Form {
    public readonly EmailInput $email;
    public readonly IntegerInput $age;

    public function __construct() {
        $this->email = new EmailInput();
        $this->age = (new IntegerInput())->setMin(18);
    }
}

$form = new LoginForm();
$form->setValue([
    'email' => 'invalid-email',
    'age' => '15'
]);

if ($form->validate()) {
    // All valid
} else {
    // Check individual fields
    if ($form->email->hasError()) {
        $error = $form->email->getError();
        echo get_class($error); // "Coroq\Form\Error\InvalidEmailError"
    }

    if ($form->age->hasError()) {
        $error = $form->age->getError();
        echo get_class($error); // "Coroq\Form\Error\TooSmallError"
    }

    // Get all errors at once
    $errors = $form->getError();
    // ['email' => InvalidEmailError, 'age' => TooSmallError]
}
```

### Custom Validators

All Input subclasses support custom validators via `setValidator()`. This allows you to add validation logic without creating custom subclasses.

#### Basic Example

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\Error\InvalidError;

class RegistrationForm extends Form {
    public readonly TextInput $username;

    public function __construct() {
        $this->username = (new TextInput())
            ->setMinLength(3)
            ->setValidator(function($formItem, $value) {
                // Additional validation: no special characters
                if (preg_match('/[^a-z0-9_]/', $value)) {
                    return new InvalidError($formItem);
                }
                return null;
            });
    }
}
```

#### How It Works

The validator:
- Receives two parameters: `$formItem` (the input itself) and `$value` (the filtered value)
- Runs **after** the input's built-in validation (`doValidate()`) passes
- Returns an `Error` object if validation fails, or `null` if valid
- Does **not** run if the value is empty or if built-in validation fails

```php
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\Error\InvalidError;

$email = (new EmailInput())
    ->setValidator(function($formItem, $value) {
        // Block disposable email domains
        if (str_ends_with($value, '@tempmail.com')) {
            return new InvalidError($formItem);
        }
        return null;
    });

$email->setValue('user@tempmail.com');
$email->validate(); // Fails - custom validator returns error

$email->setValue('invalid-email');
$email->validate(); // Fails - built-in email validation fails first
                   // Custom validator never runs
```

#### Advanced Examples

**Accessing form item properties:**

```php
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\Error\InvalidError;

$quantity = (new IntegerInput())
    ->setMin(1)
    ->setMax(100)
    ->setValidator(function($formItem, $value) {
        // Reject quantities not divisible by 5
        if ((int)$value % 5 !== 0) {
            return new InvalidError($formItem);
        }
        return null;
    });
```

### External Validation

When you validate a value in external logic (authentication, API calls, business rules) but want to hold the error in the form, use `setError()` on a form item. The form item can be used only for holding the error.

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\Input;
use Coroq\Form\Error\InvalidError;

class LoginForm extends Form {
    public readonly EmailInput $email;
    public readonly TextInput $password;
    public readonly Input $authResult;

    public function __construct() {
        $this->email = new EmailInput();
        $this->password = new TextInput();
        $this->authResult = (new Input())->setReadOnly(true);
    }
}

$form = new LoginForm();
$form->setValue($_POST);

if ($form->validate()) {
    // External validation
    if (!$authService->authenticate($form->email->getValue(), $form->password->getValue())) {
        $form->authResult->setError(new InvalidError($form->authResult));
    }
}

if ($form->hasError()) {
    // Handle all errors uniformly
}
```

## Error Handling

### Error Customizer

Transform error objects before they are stored. Useful for converting generic errors to field-specific error types.

```php
use Coroq\Form\FormItem\BooleanInput;
use Coroq\Form\Error\Error;
use Coroq\Form\Error\EmptyError;

class NoAgreementError extends Error {}

$agree = (new BooleanInput())
    ->setRequired(true)
    ->setErrorCustomizer(function(Error $error, $formItem): Error {
        if ($error instanceof EmptyError) {
            return new NoAgreementError($formItem);
        }
        return $error;
    });

$agree->validate();
echo get_class($agree->getError()); // "NoAgreementError"
```

The customizer receives `$error` and `$formItem`, runs after validation, and returns the transformed error. You can replace the error object or mutate it by adding properties.

### Error Messages

Use `ErrorMessageFormatter` to convert error objects to human-readable messages. You define your own message set by mapping error class names to messages (strings or closures).

#### Basic Usage

```php
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\TooLongError;
use Coroq\Form\Error\TooSmallError;

// Define your message set
$messages = [
    EmptyError::class => 'This field is required',
    InvalidError::class => 'Invalid value',  // Catch-all for all Invalid* errors
    TooSmallError::class => 'Value is too small',
    TooLongError::class => 'Text is too long',
];

$formatter = new ErrorMessageFormatter();
$formatter->setMessages($messages);

// Format errors
$form->validate();
if ($form->email->hasError()) {
    echo $formatter->format($form->email->getError());
    // "Invalid value" (InvalidEmailError extends InvalidError)
}
```

### Error Hierarchy and Inheritance

The formatter uses `instanceof` matching, supporting error class inheritance. Many specific errors extend base error types. For example, `InvalidEmailError`, `InvalidUrlError`, `InvalidDateError`, `InvalidMimeTypeError`, and `InvalidExtensionError` all extend `InvalidError`.

**Define base messages as defaults, then override specific types as needed:**

```php
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\InvalidEmailError;

$messages = [
    InvalidError::class => 'Invalid value',  // Base message for all Invalid* errors
    InvalidEmailError::class => 'Please enter a valid email address',  // Specific override
];

$formatter = new ErrorMessageFormatter();
$formatter->setMessages($messages);

// InvalidEmailError → 'Please enter a valid email address' (specific)
// InvalidUrlError → 'Invalid value' (falls back to base)
// InvalidDateError → 'Invalid value' (falls back to base)
```

**Later definitions override earlier ones.** This makes it easy to merge preset messages with custom overrides:

```php
// Start with preset base messages
$messages = [
    EmptyError::class => 'This field is required',
    InvalidError::class => 'Invalid value',
    TooLongError::class => 'Text is too long',
    TooSmallError::class => 'Value is too small',
];

// Add specific overrides
$messages = [
    ...$messages,  // Base messages
    InvalidEmailError::class => 'Please enter a valid email address',
    TooLongError::class => fn($e) => "Maximum {$e->formItem->getMaxLength()} characters",
];

$formatter = new ErrorMessageFormatter();
$formatter->setMessages($messages);
```

### Adding Individual Messages

Use `setMessage()` to add or override individual messages without replacing the entire set:

```php
$formatter = new ErrorMessageFormatter();

// Set base messages
$formatter->setMessages([
    EmptyError::class => 'Required field',
    InvalidError::class => 'Invalid value',
]);

// Add or override specific messages
$formatter->setMessage(InvalidEmailError::class, 'Please enter a valid email');
$formatter->setMessage(TooLongError::class, fn($e) => "Max {$e->formItem->getMaxLength()} chars");
```

### Dynamic Messages with Closures

Use closures to access error object properties for dynamic messages:

```php
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\TooLongError;
use Coroq\Form\Error\TooSmallError;

$messages = [
    EmptyError::class => function(EmptyError $error) {
        return $error->formItem->getLabel() . ' is required';
    },
    TooLongError::class => function(TooLongError $error) {
        return 'Maximum ' . $error->formItem->getMaxLength() . ' characters allowed';
    },
    TooSmallError::class => function(TooSmallError $error) {
        return 'Minimum value is ' . $error->formItem->getMin();
    },
];

$formatter = new ErrorMessageFormatter();
$formatter->setMessages($messages);
```

### Custom Error Types

You can create custom error classes for application-specific validation:

```php
use Coroq\Form\Error\Error;
use Coroq\Form\FormItem\FormItemInterface;

// Define custom error
class PasswordMismatchError extends Error {
    /** @property-read PasswordInput $formItem */
}

class RateLimitError extends Error {
    public function __construct(
        FormItemInterface $formItem,
        public readonly int $remainingSeconds
    ) {
        parent::__construct($formItem);
    }
}

// Use in messages
$messages = [
    PasswordMismatchError::class => 'Passwords do not match',
    RateLimitError::class => function(RateLimitError $error) {
        return 'Too many attempts. Try again in ' . $error->remainingSeconds . ' seconds';
    },
];
```

### Built-in Error Types

The library provides these error types:

**Base Errors:**
- `EmptyError` - Required field is empty
- `InvalidError` - Generic validation failure (base class for format validation errors)

**Invalid* Hierarchy (all extend InvalidError):**
- `InvalidEmailError` - Invalid email format
- `InvalidUrlError` - Invalid URL format
- `InvalidDateError` - Invalid date format
- `InvalidMimeTypeError` - File MIME type not allowed
- `InvalidExtensionError` - File extension not allowed

**Range/Length Errors:**
- `TooShortError`, `TooLongError` - String length validation
- `TooSmallError`, `TooLargeError` - Number range validation
- `TooFewSelectionsError`, `TooManySelectionsError` - Multi-select count

**Type/Format Errors:**
- `NotIntegerError`, `NotNumericError` - Type validation
- `PatternMismatchError` - Pattern validation failure

**Selection Errors:**
- `NotInOptionsError` - Invalid selection value

**File Errors:**
- `FileNotFoundError` - File not found at path
- `FileTooLargeError`, `FileTooSmallError` - File size range

**Derived Errors:**
- `SourceItemInvalidError` - Derived item's source failed validation

**Tip:** Define messages for base error types (like `InvalidError`) as catch-alls, then optionally override specific subtypes for custom messages.

## Form Values

Forms provide four methods to retrieve values:

- **`getValue()`** - All values as strings (includes empty values)
- **`getFilledValue()`** - Only non-empty values as strings
- **`getParsedValue()`** - All values with proper types (int, bool, DateTime, etc.)
- **`getFilledParsedValue()`** - Only non-empty values with proper types

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\BooleanInput;
use Coroq\Form\FormItem\TextInput;

class UserForm extends Form {
    public readonly EmailInput $email;
    public readonly IntegerInput $age;
    public readonly BooleanInput $newsletter;
    public readonly TextInput $notes;

    public function __construct() {
        $this->email = new EmailInput();
        $this->age = (new IntegerInput())->setRequired(false);
        $this->newsletter = (new BooleanInput())->setRequired(false);
        $this->notes = (new TextInput())->setRequired(false);
    }
}

$form = new UserForm();
$form->setValue([
    'email' => 'user@example.com',
    'age' => '25',
    'newsletter' => 'on',
    'notes' => ''
]);

// getValue() - raw strings, includes empty
$form->getValue();
// ['email' => 'user@example.com', 'age' => '25', 'newsletter' => 'on', 'notes' => '']

// getFilledValue() - raw strings, excludes empty
$form->getFilledValue();
// ['email' => 'user@example.com', 'age' => '25', 'newsletter' => 'on']

// getParsedValue() - proper types, includes empty
$form->getParsedValue();
// ['email' => 'user@example.com', 'age' => 25, 'newsletter' => true, 'notes' => '']

// getFilledParsedValue() - proper types, excludes empty
$form->getFilledParsedValue();
// ['email' => 'user@example.com', 'age' => 25, 'newsletter' => true]
```


## Input Types

### Text Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\UnicodeNormalization;

class ProfileForm extends Form {
    public readonly TextInput $name;
    public readonly TextInput $bio;

    public function __construct() {
        $this->name = (new TextInput())
            ->setMinLength(2)
            ->setMaxLength(100)
            ->setTrim(TextInput::BOTH)     // LEFT, RIGHT, BOTH, or null
            ->setCase(TextInput::TITLE)    // UPPER, LOWER, TITLE
            ->setMb('KV')                      // mb_convert_kana option
            ->setPattern('/^[A-Za-z ]+$/');    // Regex validation

        $this->bio = (new TextInput())
            ->setMultiline(true)
            ->setEol("\n")                     // Normalize line endings
            ->setMaxLength(1000);
    }
}
```

**Unicode Normalization:**

Text input values are normalized using NFC (Canonical Composition) by default if the `intl` extension is available. This ensures consistent character representation (e.g., Japanese combining marks: か゛ → が).

```php
use Coroq\Form\FormItem\UnicodeNormalization;

// Default: NFC if intl available, otherwise no normalization
$input = new TextInput();

// Use different form (NFD, NFKC, NFKD)
$input->setUnicodeNormalization(UnicodeNormalization::NFKC);

// Disable normalization
$input->setUnicodeNormalization(null);
```

For normalization form details, see [Normalizer class documentation](https://www.php.net/manual/en/class.normalizer.php).

### Email Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\EmailInput;

class ContactForm extends Form {
    public readonly EmailInput $email;

    public function __construct() {
        $this->email = new EmailInput();
        // Note: setLowerCaseDomain(true) is also default
    }
}

$form = new ContactForm();
$form->email->setValue('User@EXAMPLE.COM');
echo $form->email->getValue();    // "User@example.com"
echo $form->email->getEmail();    // "User@example.com" or null if invalid
```

### URL Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\UrlInput;

class ProfileForm extends Form {
    public readonly UrlInput $website;

    public function __construct() {
        $this->website = new UrlInput();
    }
}

$form = new ProfileForm();
$form->website->setValue('https://example.com/path?query=value');
$form->validate();  // true
echo $form->website->getUrl();  // "https://example.com/path?query=value"

// Invalid URL
$form->website->setValue('not a url');
$form->validate();  // false - InvalidUrlError
```

UrlInput validates URLs using PHP's `FILTER_VALIDATE_URL`. It converts full-width characters to half-width and trims whitespace. You can restrict allowed schemes (default: http, https).

### Telephone Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TelInput;

class ContactForm extends Form {
    public readonly TelInput $phone;

    public function __construct() {
        $this->phone = new TelInput();
    }
}

$form = new ContactForm();

// International format (E.164)
$form->phone->setValue('+81-90-1234-5678');
echo $form->phone->getValue();   // "+819012345678" (E.164 format)

// Domestic format
$form->phone->setValue('090-1234-5678');
echo $form->phone->getValue();   // "09012345678" (domestic, digits only)
```

**TelInput** strips all formatting characters (spaces, hyphens, parentheses) but preserves a leading `+` for international E.164 format. It does **NOT** validate phone numbers - use libphonenumber for validation and formatting:

```php
use Coroq\Form\FormItem\TelInput;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

$phone = new TelInput();
$phone->setValue('+81-90-1234-5678');
echo $phone->getValue(); // "+819012345678"

// For validation/formatting, use libphonenumber (giggsey/libphonenumber-for-php)
$phoneUtil = PhoneNumberUtil::getInstance();

// Parse with country hint for domestic numbers
$number = $phoneUtil->parse($phone->getValue(), 'JP');

// Or parse E.164 directly (no country hint needed)
$number = $phoneUtil->parse('+819012345678');

// Format for display
$formatted = $phoneUtil->format($number, PhoneNumberFormat::NATIONAL);
// "090-1234-5678"
```

### Select Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\Select;

class SettingsForm extends Form {
    public readonly Select $country;

    public function __construct() {
        $this->country = (new Select())
            ->setOptions([
                'us' => 'United States',
                'jp' => 'Japan',
                'uk' => 'United Kingdom'
            ]);
    }
}

$form = new SettingsForm();
$form->country->setValue('jp');
echo $form->country->getValue();          // "jp"
echo $form->country->getSelectedLabel();  // "Japan"
```

### Multi-Select Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\MultiSelect;

class SurveyForm extends Form {
    public readonly MultiSelect $hobbies;

    public function __construct() {
        $this->hobbies = (new MultiSelect())
            ->setOptions([
                'sports' => 'Sports',
                'music' => 'Music',
                'reading' => 'Reading',
                'gaming' => 'Gaming'
            ])
            ->setMinCount(1)
            ->setMaxCount(3);
    }
}

$form = new SurveyForm();
$form->hobbies->setValue(['sports', 'music']);
print_r($form->hobbies->getValue());         // ['sports', 'music']
print_r($form->hobbies->getSelectedLabel()); // ['Sports', 'Music']
```

### Number Inputs

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\NumberInput;
use Coroq\Form\FormItem\IntegerInput;

class ProductForm extends Form {
    public readonly NumberInput $price;
    public readonly IntegerInput $quantity;

    public function __construct() {
        $this->price = (new NumberInput())
            ->setMin(0.01)
            ->setMax(999999.99);

        $this->quantity = (new IntegerInput())
            ->setMin(1)
            ->setMax(100);
    }
}

$form = new ProductForm();
$form->price->setValue('１２３．４５');  // Full-width input
echo $form->price->getValue();           // "123.45" (normalized)
echo $form->price->getNumber();          // 123.45 (float)
echo $form->quantity->getInteger();      // 42 or null
```

**Note on IntegerInput limits:**
- IntegerInput validates values against PHP_INT_MIN to PHP_INT_MAX range
- Values outside this range (e.g., very large database bigint IDs) will fail validation with TooLargeError/TooSmallError
- `getInteger()` returns null for values outside PHP int range
- For very large integers (e.g., Twitter snowflake IDs, large database bigints), use TextInput instead

### Date Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\DateInput;

class EventForm extends Form {
    public readonly DateInput $eventDate;

    public function __construct() {
        $this->eventDate = new DateInput();
    }
}

$form = new EventForm();
$form->eventDate->setValue('2000/1/15');
echo $form->eventDate->getValue();              // "2000-01-15" (normalized)
$dt = $form->eventDate->getDateTime();          // DateTime object or null
$dti = $form->eventDate->getDateTimeImmutable(); // DateTimeImmutable or null
```

### Boolean Input

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\BooleanInput;

class RegistrationForm extends Form {
    public readonly BooleanInput $agreeToTerms;
    public readonly BooleanInput $newsletter;

    public function __construct() {
        // Required boolean - user must accept (value must be truthy)
        $this->agreeToTerms = new BooleanInput();

        // Optional boolean - can be true or false
        $this->newsletter = (new BooleanInput())
            ->setRequired(false);
    }
}

$form = new RegistrationForm();

// User didn't check the checkbox (empty/false)
$form->setValue(['agreeToTerms' => '', 'newsletter' => '']);
$form->validate();  // FAILS - agreeToTerms is required but empty
$form->agreeToTerms->getBoolean();  // false
$form->newsletter->getBoolean();    // false

// User checked both checkboxes
$form->setValue(['agreeToTerms' => 'on', 'newsletter' => '1']);
$form->validate();  // PASSES
$form->agreeToTerms->getBoolean();  // true
$form->newsletter->getBoolean();    // true

// From API with actual booleans
$form->setValue(['agreeToTerms' => true, 'newsletter' => false]);
$form->agreeToTerms->getBoolean();  // true
$form->newsletter->getBoolean();    // false
```

BooleanInput considers only `''`, `null`, and `false` as "empty" (false).
Everything else including `'0'`, `0`, `'off'`, `'no'` is considered "not empty" (true).

### File Input

FileInput validates files by their path. It checks file size, MIME type, and extension. This library **does not** handle HTTP file uploads ($_FILES) - that should be done by your HTTP layer.

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\FileInput;

class UploadForm extends Form {
    public readonly FileInput $avatar;
    public readonly FileInput $document;

    public function __construct() {
        // Image upload with size and type restrictions
        $this->avatar = (new FileInput())
            ->setRequired(false)  // Usually optional
            ->setMaxSize(5 * 1024 * 1024)  // 5 MB
            ->setAllowedMimeTypes(['image/jpeg', 'image/png', 'image/gif'])
            ->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif']);

        // Document upload
        $this->document = (new FileInput())
            ->setRequired(false)
            ->setMaxSize(10 * 1024 * 1024)  // 10 MB
            ->setMinSize(1024)  // 1 KB minimum
            ->setAllowedMimeTypes(['application/pdf'])
            ->setAllowedExtensions(['pdf']);
    }
}

// Your HTTP layer moves uploaded file to temporary storage
$tempPath = '/app/storage/temp/' . uniqid() . '.jpg';
move_uploaded_file($_FILES['avatar']['tmp_name'], $tempPath);

// FileInput validates the file at the path
$form = new UploadForm();
$form->avatar->setValue($tempPath);

if ($form->validate()) {
    $filePath = $form->avatar->getValue();
    // Move to permanent storage, save file ID, etc.
}
```

FileInput works with file paths (strings), not $_FILES arrays. For tracking uploaded files across form submissions, use a separate TextInput for file ID.

Example upload flow:
```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\FileInput;
use Coroq\Form\FormItem\TextInput;

class ProfileForm extends Form {
    public readonly FileInput $newAvatar;  // Optional - for new uploads
    public readonly TextInput $avatarId;   // Required - tracks saved file
}

// First submit: user uploads new file
if ($_FILES['newAvatar']['tmp_name']) {
    $tempPath = moveToTempStorage($_FILES['newAvatar']);
    $form->newAvatar->setValue($tempPath);
}

if ($form->validate()) {
    if ($form->newAvatar->getValue()) {
        // Save new file and get ID
        $avatarId = $storage->save($form->newAvatar->getValue());
        $form->avatarId->setValue($avatarId);
    }
}

// Resubmission after error: newAvatar is empty, avatarId still has value
```


## Nested Forms

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;

class AddressForm extends Form {
    public readonly TextInput $street;
    public readonly TextInput $city;
    public readonly TextInput $postal;

    public function __construct() {
        $this->street = new TextInput();
        $this->city = new TextInput();
        $this->postal = new TextInput();
    }
}

class UserForm extends Form {
    public readonly TextInput $name;
    public readonly EmailInput $email;
    public readonly AddressForm $address;

    public function __construct() {
        $this->name = new TextInput();
        $this->email = new EmailInput();
        $this->address = new AddressForm();
    }
}

$form = new UserForm();
$form->setValue([
    'name' => 'Taro Yamada',
    'email' => 'taro@example.com',
    'address' => [
        'street' => '1-1-1 Shibuya',
        'city' => 'Tokyo',
        'postal' => '150-0001'
    ]
]);

// Full IDE support for nested access
echo $form->address->street->getValue();
echo $form->address->postal->getValue();

// Hierarchical values
$values = $form->getValue();
/*
[
  'name' => 'Taro Yamada',
  'email' => 'taro@example.com',
  'address' => [
    'street' => '1-1-1 Shibuya',
    'city' => 'Tokyo',
    'postal' => '150-0001'
  ]
]
*/

// Alternative: getItem() method
$addressForm = $form->getItem('address');  // Returns FormInterface
if ($addressForm instanceof FormInterface) {
    $street = $addressForm->getItem('street');
    echo $street->getValue();
}
```

## Repeating Forms

`RepeatingForm` manages dynamic lists of form items using a factory pattern:

```php
use Coroq\Form\Form;
use Coroq\Form\RepeatingForm;
use Coroq\Form\FormItem\EmailInput;

class ContactForm extends Form {
    public readonly RepeatingForm $emails;

    public function __construct() {
        $this->emails = (new RepeatingForm())->setFactory(function(int $index) {
            $email = new EmailInput();
            $email->setRequired($index === 0);
            $email->setLabel($index === 0 ? 'Primary Email' : 'Additional Email');
            return $email;
        });

        $this->emails->setMinItemCount(3);
        $this->emails->setMaxItemCount(5);
    }
}

$form = new ContactForm();
$form->setValue(['emails' => ['user@example.com', 'alt@example.com']]);

if ($form->validate()) {
    // Access items by index
    echo $form->emails->getItem(0)->getValue();  // 'user@example.com'
    echo $form->emails->getItem(1)->getValue();  // 'alt@example.com'
    echo $form->emails->getItem(2)->getValue();  // '' (minItemCount=3)

    // Get all values
    print_r($form->emails->getValue());
    // ['user@example.com', 'alt@example.com', '']

    // Get only filled values
    print_r($form->emails->getFilledValue());
    // [0 => 'user@example.com', 1 => 'alt@example.com']
}
```

### Factory Function

The factory function receives an index parameter:

```php
use Coroq\Form\RepeatingForm;
use Coroq\Form\FormItem\TelInput;

// Complex business logic
$phoneNumbers = (new RepeatingForm())->setFactory(function(int $index) {
    $phone = new TelInput();

    if ($index === 0) {
        $phone->setLabel('Primary Phone')->setRequired(true);
    } elseif ($index === 1) {
        $phone->setLabel('Mobile Phone')->setRequired(false);
    } else {
        $phone->setLabel('Emergency Contact #' . ($index - 1))->setRequired(false);
    }

    return $phone;
});

$phoneNumbers->setMinItemCount(2);   // Always show primary + mobile
$phoneNumbers->setMaxItemCount(10);  // Max 10 total
```

### Nested Repeating Forms

RepeatingForm can contain other forms, including nested RepeatingForms:

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class AddressForm extends Form {
    public readonly TextInput $street;
    public readonly TextInput $city;
    public readonly TextInput $postal;

    public function __construct() {
        $this->street = new TextInput();
        $this->city = new TextInput();
        $this->postal = new TextInput();
    }
}

class UserForm extends Form {
    public readonly RepeatingForm $addresses;

    public function __construct() {
        // RepeatingForm of nested Forms
        $this->addresses = (new RepeatingForm())->setFactory(function(int $index) {
            $form = new AddressForm();
            // First address required, others optional
            $form->setRequired($index === 0);
            return $form;
        });

        $this->addresses->setMinItemCount(2);  // Show 2 address forms
    }
}

$form = new UserForm();
$form->setValue([
    'addresses' => [
        ['street' => '1-1-1 Shibuya', 'city' => 'Tokyo', 'postal' => '150-0001'],
        ['street' => '2-2-2 Umeda', 'city' => 'Osaka', 'postal' => '530-0001'],
    ]
]);

// Access nested values
echo $form->addresses->getItem(0)->street->getValue();  // '1-1-1 Shibuya'
echo $form->addresses->getItem(1)->city->getValue();    // 'Osaka'
```

Items can be added programmatically:

```php
use Coroq\Form\RepeatingForm;
use Coroq\Form\FormItem\EmailInput;

$emails = (new RepeatingForm())->setFactory(fn($i) => new EmailInput());
$emails->addItem('user1@example.com');
$emails->addItem('user2@example.com');
echo $emails->count();  // 2
```

## Derived Inputs

Derived inputs are special form items that depend on other form items. They can:
- **Calculate values** from source inputs (e.g., full name from first + last name)
- **Perform cross-field validation** (e.g., password confirmation matching)
- **Track external validation** results (e.g., authentication status)

**Key Properties:**
- Always **read-only** - their value comes from sources, not user input
- Return `null` if any source input fails validation
- Can have both value calculation (`setValueCalculator`) and validation (`setValidator`)

### Basic Example: Calculated Values

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\Derived;

class UserForm extends Form {
    public readonly TextInput $firstName;
    public readonly TextInput $lastName;
    public readonly Derived $fullName;

    public function __construct() {
        $this->firstName = new TextInput();
        $this->lastName = new TextInput();

        // Derived field calculates value from sources
        $this->fullName = (new Derived())
            ->setValueCalculator(fn($first, $last) => $first . ' ' . $last)
            ->addSource($this->firstName)
            ->addSource($this->lastName);
    }
}

$form = new UserForm();
$form->setValue([
    'firstName' => 'Taro',
    'lastName' => 'Yamada'
]);

echo $form->fullName->getValue(); // "Taro Yamada"

// If a source is invalid, getValue() returns null
$form->firstName->setValue('');  // Empty (fails validation if required)
echo $form->fullName->getValue(); // null
```

### More Calculation Examples

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\NumberInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\Derived;

class OrderForm extends Form {
    public readonly NumberInput $price;
    public readonly IntegerInput $quantity;
    public readonly Derived $total;

    public function __construct() {
        $this->price = new NumberInput();
        $this->quantity = new IntegerInput();

        // Calculate total price
        $this->total = (new Derived())
            ->setValueCalculator(fn($price, $quantity) => $price * $quantity)
            ->addSource($this->price)
            ->addSource($this->quantity);
    }
}
```

### Cross-Field Validation

Use `setValidator()` to validate relationships between fields. The validator receives:
1. All source values as individual parameters
2. The calculated value as the last parameter (or `null` if no calculator)

The validator returns an `Error` object if invalid, or `null` if valid.

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\Derived;
use Coroq\Form\Error\InvalidError;

class RegistrationForm extends Form {
    public readonly TextInput $password;
    public readonly TextInput $passwordConfirm;
    public readonly Derived $passwordMatch;

    public function __construct() {
        $this->password = (new TextInput())
            ->setMinLength(8);
        $this->passwordConfirm = new TextInput();

        // Validate that passwords match (no value calculator needed)
        $this->passwordMatch = (new Derived())
            ->setValidator(function($password, $confirm, $calculated) {
                // $password = source 1 value
                // $confirm = source 2 value
                // $calculated = null (no setValueCalculator)
                return $password !== $confirm
                    ? new InvalidError($this)
                    : null;
            })
            ->addSource($this->password)
            ->addSource($this->passwordConfirm);
    }
}

$form = new RegistrationForm();
$form->setValue([
    'password' => 'secret123',
    'passwordConfirm' => 'secret456'
]);

if (!$form->validate()) {
    if ($form->passwordMatch->hasError()) {
        echo "Passwords must match";
    }
}
```

**Note:** Derived validation only runs if all source inputs pass their own validation first. If any source fails, the Derived item automatically gets a `SourceItemInvalidError`.

### Combined: Calculation with Validation

You can use both `setValueCalculator()` and `setValidator()` together:

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\Derived;
use Coroq\Form\Error\TooLongError;

class ProfileForm extends Form {
    public readonly TextInput $firstName;
    public readonly TextInput $lastName;
    public readonly Derived $displayName;

    public function __construct() {
        $this->firstName = new TextInput();
        $this->lastName = new TextInput();

        // Calculate display name and validate its length
        $this->displayName = (new Derived())
            ->setValueCalculator(fn($first, $last) => strtoupper($first . ' ' . $last))
            ->setValidator(function($first, $last, $calculated) {
                // $first = source 1 value
                // $last = source 2 value
                // $calculated = the computed value from setValueCalculator
                return strlen($calculated) > 50
                    ? new TooLongError($this)
                    : null;
            })
            ->addSource($this->firstName)
            ->addSource($this->lastName);
    }
}

$form = new ProfileForm();
$form->setValue(['firstName' => 'Taro', 'lastName' => 'Yamada']);
echo $form->displayName->getValue(); // "TARO YAMADA" (calculated)

// Validation runs on the calculated value
$form->setValue(['firstName' => str_repeat('A', 30), 'lastName' => str_repeat('B', 30)]);
$form->validate(); // Fails - displayName has TooLongError
```

## Complete Example

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\Select;
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\TooSmallError;

class UserRegistrationForm extends Form {
    public readonly TextInput $name;
    public readonly EmailInput $email;
    public readonly IntegerInput $age;
    public readonly Select $country;

    public function __construct() {
        $this->name = (new TextInput())
            ->setLabel('Name')
            ->setMaxLength(100);

        $this->email = (new EmailInput())
            ->setLabel('Email');

        $this->age = (new IntegerInput())
            ->setLabel('Age')
            ->setRequired(false)  // Make optional
            ->setMin(18)
            ->setMax(120);

        $this->country = (new Select())
            ->setLabel('Country')
            ->setOptions([
                'us' => 'United States',
                'jp' => 'Japan',
                'uk' => 'United Kingdom'
            ]);
    }
}

// Setup error messages
$formatter = new ErrorMessageFormatter();
$formatter->setMessages([
    EmptyError::class => 'This field is required',
    InvalidError::class => 'Invalid value',  // Catch-all for Invalid* errors
    TooSmallError::class => function(TooSmallError $error) {
        return 'Minimum value is ' . $error->formItem->getMin();
    },
]);

// Process form submission
$form = new UserRegistrationForm();
$form->setValue($_POST);

if ($form->validate()) {
    // Get validated data with full type safety
    $name = $form->name->getValue();
    $email = $form->email->getEmail();
    $age = $form->age->getInteger(); // null if not provided
    $country = $form->country->getValue();

    // Save to database
    $db->insert('users', $form->getFilledValue());

    header('Location: /success');
} else {
    // Display errors with IDE support
    foreach ([$form->name, $form->email, $form->age, $form->country] as $field) {
        if ($field->hasError()) {
            echo $field->getLabel() . ': ';
            echo $formatter->format($field->getError());
            echo "\n";
        }
    }
}
```

## Configuration

### UTF-8 Invalid Character Handling

This library assumes all input is UTF-8 encoded. Invalid UTF-8 byte sequences are automatically replaced with a substitute character during filtering.

By default, PHP uses `?` (U+003F QUESTION MARK) as the substitute character. For better visibility of data corruption, it's recommended to use `�` (U+FFFD REPLACEMENT CHARACTER) instead by configuring it in your application bootstrap:

```php
// Recommended: Use Unicode Replacement Character for invalid UTF-8 bytes
mb_substitute_character(0xFFFD);  // U+FFFD: �
```

Alternative configurations:
```php
mb_substitute_character('none');   // Remove invalid bytes silently
mb_substitute_character('long');   // Use U+XXXX notation
mb_substitute_character('entity'); // Use &#XXXX; HTML entities
```

See [mb_substitute_character documentation](https://www.php.net/manual/en/function.mb-substitute-character.php) for more options.

## API Reference

### Form

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;

class MyForm extends Form {
    public readonly TextInput $field;
    // Define form items as typed readonly properties
}

$form = new MyForm();

// Values
$form->setValue(array $data);
$values = $form->getValue();              // All enabled items (raw values)
$parsed = $form->getParsedValue();        // All enabled items (parsed values)
$filled = $form->getFilledValue();        // Non-empty values only (raw)
$filledParsed = $form->getFilledParsedValue();  // Non-empty values (parsed)

// Validation
$valid = $form->validate();
$hasError = $form->hasError();
$errors = $form->getError();              // Array of errors

// Item access
$item = $form->getItem(mixed $name);      // Get item by name

// State
$form->setRequired(bool);
$form->setReadOnly(bool);
$form->setDisabled(bool);

// Utility
$form->clear();
$isEmpty = $form->isEmpty();
```

### Input

All input types extend `Input` and support:

```php
use Coroq\Form\FormItem\TextInput;

$input = new TextInput();

// Values
$input->setValue(mixed $value);
$value = $input->getValue();              // Raw value
$parsed = $input->getParsedValue();       // Parsed value (int, bool, DateTime, etc.)
$input->clear();

// Validation
$valid = $input->validate();
$error = $input->getError();             // Error object or null
$hasError = $input->hasError();

// State
$input->setRequired(bool);
$input->setReadOnly(bool);
$input->setDisabled(bool);
$input->setLabel(string);

// Custom validation and error handling
$input->setValidator(?callable);         // fn($formItem, $value): ?Error
$input->setErrorCustomizer(?\Closure);   // fn($error, $formItem): Error

// Checks
$isEmpty = $input->isEmpty();
$isRequired = $input->isRequired();
$isReadOnly = $input->isReadOnly();
$isDisabled = $input->isDisabled();
```

### Text Input

```php
use Coroq\Form\FormItem\TextInput;

$text = new TextInput();
$text->setMinLength(int);
$text->setMaxLength(int);
$text->setPattern(string);               // Regex
$text->setTrim(string);                  // LEFT, RIGHT, BOTH, null
$text->setCase(int);                     // UPPER, LOWER, TITLE
$text->setMb(string);                    // mb_convert_kana option
$text->setUnicodeNormalization(string);  // NFC, NFD, NFKC, NFKD, null
$text->setMultiline(bool);
$text->setNoWhitespace(bool);
$text->setNoControl(bool);
```

### Select/MultiSelect

```php
use Coroq\Form\FormItem\Select;
use Coroq\Form\FormItem\MultiSelect;

$select = new Select();
$select->setOptions(array);
$label = $select->getSelectedLabel();    // string|null

$multi = new MultiSelect();
$multi->setOptions(array);
$multi->setMinCount(int);
$multi->setMaxCount(int);
$labels = $multi->getSelectedLabel();    // array
```

### Number Inputs

```php
use Coroq\Form\FormItem\NumberInput;
use Coroq\Form\FormItem\IntegerInput;

$number = new NumberInput();
$number->setMin(string);
$number->setMax(string);
$value = $number->getNumber();           // float|null

$int = new IntegerInput();
$int->setMin(string);
$int->setMax(string);
$value = $int->getInteger();             // int|null
```

### Boolean Input

```php
use Coroq\Form\FormItem\BooleanInput;

$bool = new BooleanInput();
$value = $bool->getBoolean();            // bool (true if not empty, false if empty)
// Note: Only '', null, and false are considered empty
```

### File Input

```php
use Coroq\Form\FormItem\FileInput;

$file = new FileInput();
$file->setMaxSize(int);                  // Max file size in bytes
$file->setMinSize(int);                  // Min file size in bytes
$file->setAllowedMimeTypes(array);       // e.g., ['image/jpeg', 'image/png']
$file->setAllowedExtensions(array);      // e.g., ['jpg', 'png', 'pdf']
$path = $file->getValue();               // string|null - file path
// Note: Usually setRequired(false) - file might already be uploaded
```

### RepeatingForm

```php
use Coroq\Form\RepeatingForm;
use Coroq\Form\FormItem\EmailInput;

// Create with factory
$repeating = (new RepeatingForm())->setFactory(function(int $index) {
    return (new EmailInput())->setRequired($index === 0);
});

// Structural constraints
$repeating->setMinItemCount(int);        // Always have at least N items
$repeating->setMaxItemCount(int);        // Never exceed N items
$min = $repeating->getMinItemCount();
$max = $repeating->getMaxItemCount();

// Values
$repeating->setValue(array);             // Recreates all items from factory
$values = $repeating->getValue();        // Array of values (int-indexed)
$parsed = $repeating->getParsedValue();  // Array of parsed values
$filled = $repeating->getFilledValue();  // Non-empty values only
$filledParsed = $repeating->getFilledParsedValue();

// Item access
$item = $repeating->getItem(int);        // Get item at index (or null)
$items = $repeating->getItems();         // Get all items
$count = $repeating->count();            // Number of items

// Manual item addition
$item = $repeating->addItem(?string);    // Add new item, returns the item

// Validation
$valid = $repeating->validate();         // Validates each item
$errors = $repeating->getError();        // Array of errors (int-indexed)
$hasError = $repeating->hasError();

// State (same as Form/Input)
$repeating->setRequired(bool);
$repeating->setReadOnly(bool);
$repeating->setDisabled(bool);
$repeating->clear();                     // Clears all item values
$isEmpty = $repeating->isEmpty();
```

## License

MIT
