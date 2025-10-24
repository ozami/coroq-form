# Migration Guide: 2.1.0 → 3.0.0

This guide helps you migrate from version 2.1.0 to 3.0.0.

## Overview

Version 3.0.0 is a major rewrite with significant breaking changes. The core architecture has been redesigned for better type safety, modern PHP features, and improved developer experience.

**Key Changes:**
- PHP 8.0+ required (was PHP 7.2+)
- Namespace restructure: `Coroq\Form\Input\*` → `Coroq\Form\FormItem\*`
- Form API completely redesigned (property-based instead of array-based)
- Error handling redesigned (Error classes instead of error codes)
- New features: BooleanInput, FileInput, RepeatingForm, getParsedValue()
- Removed methods: `setItem()`, `getItemIn()`, `addItem()`, etc.

---

## Breaking Changes

### 1. PHP Version Requirement

**Before (2.1.0):**
```json
"require": {
    "php": ">=7.2"
}
```

**After (3.0.0):**
```json
"require": {
    "php": ">=8.0"
}
```

**Migration:** Update your PHP version to 8.0 or later.

---

### 2. Namespace Changes

**Before (2.1.0):**
```php
use Coroq\Form\Form;
use Coroq\Form\Input;

$text = new Input\Text();
$email = new Input\Email();
$integer = new Input\Integer();
$select = new Input\Select();
```

**After (3.0.0):**
```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\Select;

$text = new TextInput();
$email = new EmailInput();
$integer = new IntegerInput();
$select = new Select();
```

**Migration:** Replace all `Input\*` imports with `FormItem\*` and update class names:

| 2.1.0 | 3.0.0 |
|-------|-------|
| `Input\Text` | `FormItem\TextInput` |
| `Input\Email` | `FormItem\EmailInput` |
| `Input\Integer` | `FormItem\IntegerInput` |
| `Input\Number` | `FormItem\NumberInput` |
| `Input\Date` | `FormItem\DateInput` |
| `Input\Tel` | `FormItem\TelInput` |
| `Input\Url` | `FormItem\UrlInput` |
| `Input\Postal` | Removed - use `TextInput` with custom validation |
| `Input\Katakana` | Removed - use `TextInput` with custom validation |
| `Input\Select` | `FormItem\Select` |
| `Input\MultiSelect` | `FormItem\MultiSelect` |
| `Input\Computed` | `FormItem\Derived` |

---

### 3. Form API: Array-Based → Property-Based

This is the **biggest breaking change**. Forms now use public properties instead of array-like methods.

#### Before (2.1.0): Array-Based API

```php
$form = new Form();
$form->setItem('name', new Input\Text());
$form->setItem('email', new Input\Email());
$form->setItem('age', new Input\Integer());

// Set values
$form->setValue($_POST);

// Get item
$nameInput = $form->getItem('name');

// Get item value
$name = $form->getItemValue('name');

// Get nested item
$city = $form->getItem('address')->getItem('city');
// or
$city = $form->getItemIn('address/city');
```

#### After (3.0.0): Property-Based API

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;

// Option 1: Dynamic properties (for temporary use)
$form = new Form();
$form->name = new TextInput();
$form->email = new EmailInput();
$form->age = new IntegerInput();

// Set values
$form->setValue($_POST);

// Access items directly
$nameInput = $form->name;

// Get item value
$name = $form->name->getValue();

// Get nested item
$city = $form->address->city;
```

**Recommended in 3.0.0: Define Form Subclasses**

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;

class UserForm extends Form {
    public readonly TextInput $name;
    public readonly EmailInput $email;
    public readonly IntegerInput $age;

    public function __construct() {
        $this->name = new TextInput();
        $this->email = new EmailInput();
        $this->age = new IntegerInput();
    }
}

$form = new UserForm();
$form->setValue($_POST);

// Full IDE support and type safety
$name = $form->name->getValue();  // IDE knows this is TextInput
```

**Migration Steps:**

1. **Find all `setItem()` calls** and convert to properties
2. **Find all `getItem()` calls** and convert to property access (or keep using `getItem()` - it's still available)
3. **Find all `getItemValue()` calls** and convert to `->property->getValue()`
4. **Find all `getItemIn()` calls** and convert to nested property access
5. **Consider creating Form subclasses** for better type safety

---

### 4. Changed Methods in Form

The following methods have changed or have alternatives:

| 2.1.0 Method | 3.0.0 Alternative | Notes |
|--------------|-------------------|-------|
| `setItem($name, $item)` | `$form->name = $item` | Use property assignment |
| `getItem($name)` | `$form->name` or `$form->getItem($name)` | Property access recommended; `getItem()` still available |
| `getItemValue($name)` | `$form->name->getValue()` | Removed |
| `getItemIn($path)` | `$form->address->city` | Use nested property access |
| `addItem($item)` | Not available | Use properties instead |
| `unsetItem($name)` | Use `setDisabled(true)` | Disabled items are excluded from getValue/validate |
| `setItems($items)` | Set properties in constructor | |
| `getItems()` | Not public | Access properties directly |
| `getEnabledItems()` | Not public | Internal use only |
| `getFilled()` | `getFilledValue()` | Renamed for clarity |

```php
// Direct property access (recommended)
$street = $form->address->street;

// getItem() also works
$street = $form->getItem('address')->getItem('street');
```

---

### 5. Error System Redesign

#### Before (2.1.0): Error Codes (Strings)

```php
use Coroq\Form\Error;

$form->validate();

$input = $form->getItem('email');
if ($input->hasError()) {
    $error = $input->getError();
    echo $error->code;  // 'err_invalid', 'err_empty', etc.

    // Get error message
    echo $input->getErrorString();
}

// Set error stringifier
Input::setDefaultErrorStringifier(function(Error $error) {
    $messages = [
        'err_empty' => 'Required',
        'err_invalid' => 'Invalid',
    ];
    return $messages[$error->code] ?? null;
});
```

#### After (3.0.0): Error Classes

```php
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidEmailError;
use Coroq\Form\Error\TooLongError;

$form->validate();

if ($form->email->hasError()) {
    $error = $form->email->getError();
    echo get_class($error);  // "Coroq\Form\Error\InvalidEmailError"
}

// Format error messages
$formatter = new ErrorMessageFormatter();

// Define custom messages
$messages = [
    EmptyError::class => 'This field is required',
    InvalidEmailError::class => 'Invalid email',
    TooLongError::class => function(TooLongError $error) {
        return 'Max ' . $error->formItem->getMaxLength() . ' characters';
    },
];

$formatter->setMessages($messages);
echo $formatter->format($form->email->getError());
```

**Error Code → Error Class Mapping:**

| 2.1.0 Error Code | 3.0.0 Error Class |
|------------------|-------------------|
| `err_empty` | `\Coroq\Form\Error\EmptyError` |
| `err_invalid` | `\Coroq\Form\Error\InvalidError` |
| `err_too_short` | `\Coroq\Form\Error\TooShortError` |
| `err_too_long` | `\Coroq\Form\Error\TooLongError` |
| `err_too_small` | `\Coroq\Form\Error\TooSmallError` |
| `err_too_large` | `\Coroq\Form\Error\TooLargeError` |
| `err_not_int` | `\Coroq\Form\Error\NotIntegerError` |
| `err_too_few` | `\Coroq\Form\Error\TooFewSelectionsError` |
| `err_too_many` | `\Coroq\Form\Error\TooManySelectionsError` |

**Migration:**

1. Replace `$error->code` checks with `instanceof` checks or `get_class()`
2. Replace `setDefaultErrorStringifier()` with `ErrorMessageFormatter`
3. Use error class names as keys in message arrays

---

### 6. Method Renames

| 2.1.0 | 3.0.0 | Notes |
|-------|-------|-------|
| `getFilled()` | `getFilledValue()` | Renamed for clarity |
| `disable()` | `setDisabled(bool)` | Now takes bool parameter |
| `enable()` | `setDisabled(false)` | Use setDisabled instead |
| `disable(true/false)` | `setDisabled(bool)` | Same functionality |

**Migration:**

```php
// Before
$form->disable();
$form->enable();
$form->disable(true);

// After
$form->setDisabled(true);
$form->setDisabled(false);
$form->setDisabled(true);
```

---

### 7. Derived (Computed) Inputs

#### Before (2.1.0): Extend Computed Class

```php
use Coroq\Form\Input\Computed;

class FullName extends Computed {
    protected function computeValue(array $source_values) {
        [$first, $last] = $source_values;
        return trim($first . ' ' . $last);
    }
}

$form = new Form();
$firstName = new Input\Text();
$lastName = new Input\Text();
$form->setItem('first_name', $firstName);
$form->setItem('last_name', $lastName);

$fullName = new FullName();
$fullName->addSourceInput($firstName);
$fullName->addSourceInput($lastName);
$form->setItem('full_name', $fullName);
```

#### After (3.0.0): Use setValueCalculator() with Closure

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

        // Note: Calculator receives spread arguments, not an array
        $this->fullName = (new Derived())
            ->addSource($this->firstName)
            ->addSource($this->lastName)
            ->setValueCalculator(fn($first, $last) => trim($first . ' ' . $last));
    }
}
```

`Derived` also supports `setValidator()` for cross-field validation:

```php
// Password confirmation validation
$this->passwordMatch = (new Derived())
    ->addSource($this->password)
    ->addSource($this->passwordConfirm)
    ->setValidator(function($password, $confirm) {
        return $password !== $confirm
            ? new PasswordMismatchError($this)
            : null;
    });
```

**Migration:**

1. Replace `computeValue()` method with `setValueCalculator()` closure
2. Change `addSourceInput()` to `addSource()`
3. Update closure to receive spread arguments instead of array
4. Use property-based access instead of `setItem()`
5. Consider using `setValidator()` for cross-field validation

---

### 8. Form Options Removed

**Before (2.1.0):**
```php
$form = new Form(['path_separator' => '.']);
$form->getItemIn('address.city');
```

**After (3.0.0):**
```php
// No path separator needed - use property access
$form->address->city
```

**Migration:** Remove form options, use property access instead of paths.

---

## New Features in 3.0.0

### 1. BooleanInput

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\BooleanInput;

class RegistrationForm extends Form {
    public readonly BooleanInput $agreeToTerms;
    public readonly BooleanInput $newsletter;

    public function __construct() {
        // Required boolean - must be checked
        $this->agreeToTerms = new BooleanInput();

        // Optional boolean
        $this->newsletter = (new BooleanInput())
            ->setRequired(false);
    }
}

$form = new RegistrationForm();
$form->setValue(['agreeToTerms' => 'on', 'newsletter' => '']);
$form->agreeToTerms->getBoolean();  // true
$form->newsletter->getBoolean();    // false
```

### 2. FileInput

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\FileInput;

class UploadForm extends Form {
    public readonly FileInput $avatar;

    public function __construct() {
        $this->avatar = (new FileInput())
            ->setRequired(false)
            ->setMaxSize(5 * 1024 * 1024)  // 5 MB
            ->setAllowedMimeTypes(['image/jpeg', 'image/png'])
            ->setAllowedExtensions(['jpg', 'png']);
    }
}

// Your HTTP layer moves uploaded file
$tempPath = '/app/storage/temp/' . uniqid() . '.jpg';
move_uploaded_file($_FILES['avatar']['tmp_name'], $tempPath);

$form = new UploadForm();
$form->avatar->setValue($tempPath);
if ($form->validate()) {
    $filePath = $form->avatar->getValue();
}
```

### 3. RepeatingForm

For dynamic lists of form items (e.g., multiple email addresses):

```php
use Coroq\Form\Form;
use Coroq\Form\RepeatingForm;
use Coroq\Form\FormItem\EmailInput;

class ContactForm extends Form {
    public readonly RepeatingForm $emails;

    public function __construct() {
        $this->emails = (new RepeatingForm())->setFactory(function(int $index) {
            $email = new EmailInput();
            $email->setRequired($index === 0); // First required, rest optional
            return $email;
        });

        $this->emails->setMinItemCount(3);  // Always show 3 fields
        $this->emails->setMaxItemCount(5);  // Max 5 allowed
    }
}

$form = new ContactForm();
$form->setValue(['emails' => ['user@example.com', 'alt@example.com']]);

// Access items
$form->emails->getItem(0)->getValue();  // 'user@example.com'
$form->emails->count();  // 3 (minItemCount enforced)
```

### 4. getParsedValue() for Type Safety

Get properly typed values instead of strings:

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\FormItem\BooleanInput;
use Coroq\Form\FormItem\DateInput;

class UserForm extends Form {
    public readonly IntegerInput $age;
    public readonly BooleanInput $newsletter;
    public readonly DateInput $birthDate;

    public function __construct() {
        $this->age = new IntegerInput();
        $this->newsletter = (new BooleanInput())->setRequired(false);
        $this->birthDate = new DateInput();
    }
}

$form = new UserForm();
$form->setValue([
    'age' => '25',                  // String from $_POST
    'newsletter' => 'on',           // String from checkbox
    'birthDate' => '1998-05-10'
]);

// getValue() - returns raw strings
$values = $form->getValue();
// ['age' => '25', 'newsletter' => 'on', 'birthDate' => '1998-05-10']

// getParsedValue() - returns proper types
$parsed = $form->getParsedValue();
// ['age' => 25, 'newsletter' => true, 'birthDate' => DateTimeImmutable]

// Individual items
$form->age->getParsedValue();       // int: 25
$form->newsletter->getParsedValue(); // bool: true
$form->birthDate->getParsedValue();  // DateTimeImmutable
```

### 5. getFilledParsedValue()

Combines `getFilledValue()` and `getParsedValue()`:

```php
$form->setValue(['age' => '30', 'newsletter' => '', 'notes' => '']);

$filled = $form->getFilledParsedValue();
// ['age' => 30]  // Only non-empty, with type conversion
```

### 6. TelInput Now Preserves Leading `+`

**Before (2.1.0):**
```php
$phone = new TelInput();
$phone->setValue('+81-90-1234-5678');
echo $phone->getValue();  // "819012345678" (+ was stripped)
```

**After (3.0.0):**
```php
use Coroq\Form\FormItem\TelInput;

$phone = new TelInput();
$phone->setValue('+81-90-1234-5678');
echo $phone->getValue();  // "+819012345678" (E.164 format, + preserved)

$phone->setValue('090-1234-5678');
echo $phone->getValue();  // "09012345678" (domestic format)
```

**Why this change?**
- Preserving the `+` prefix allows distinguishing international (E.164) from domestic numbers
- E.164 format: `+819012345678` (country code explicit)
- Domestic format: `09012345678` (requires country context)

**For validation/formatting:**
Use libphonenumber (giggsey/libphonenumber-for-php):
```php
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

$phoneUtil = PhoneNumberUtil::getInstance();
$number = $phoneUtil->parse($phone->getValue(), 'JP');  // Country hint for domestic
$formatted = $phoneUtil->format($number, PhoneNumberFormat::NATIONAL);  // "090-1234-5678"
```

---

## Step-by-Step Migration Example

### Before (2.1.0)

```php
use Coroq\Form\Form;
use Coroq\Form\Input;
use Coroq\Form\Error;

$form = new Form();

$form->setItem('username', (new Input\Text())
    ->setLabel('Username')
    ->setRequired(true)
    ->setMinLength(3));

$form->setItem('email', (new Input\Email())
    ->setLabel('Email')
    ->setRequired(true));

$form->setItem('age', (new Input\Integer())
    ->setLabel('Age')
    ->setMin(13));

$address = new Form();
$address->setItem('city', new Input\Text());
$address->setItem('postal', new Input\Text());
$form->setItem('address', $address);

Input::setDefaultErrorStringifier(function(Error $error) {
    $messages = [
        'err_empty' => 'Required',
        'err_invalid' => 'Invalid',
    ];
    return $messages[$error->code] ?? null;
});

$form->setValue($_POST);

if ($form->validate()) {
    $data = $form->getValue();
} else {
    foreach ($form->getItems() as $name => $item) {
        if ($item->hasError()) {
            echo $item->getLabel() . ': ' . $item->getErrorString() . "\n";
        }
    }
}
```

### After (3.0.0)

```php
use Coroq\Form\Form;
use Coroq\Form\FormItem\TextInput;
use Coroq\Form\FormItem\EmailInput;
use Coroq\Form\FormItem\IntegerInput;
use Coroq\Form\ErrorMessageFormatter;
use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;

class AddressForm extends Form {
    public readonly TextInput $city;
    public readonly TextInput $postal;

    public function __construct() {
        $this->city = new TextInput();
        $this->postal = new TextInput();
    }
}

class UserForm extends Form {
    public readonly TextInput $username;
    public readonly EmailInput $email;
    public readonly IntegerInput $age;
    public readonly AddressForm $address;

    public function __construct() {
        $this->username = (new TextInput())
            ->setLabel('Username')
            ->setRequired(true)
            ->setMinLength(3);

        $this->email = (new EmailInput())
            ->setLabel('Email')
            ->setRequired(true);

        $this->age = (new IntegerInput())
            ->setLabel('Age')
            ->setMin(13);

        $this->address = new AddressForm();
    }
}

$formatter = new ErrorMessageFormatter();
$messages = [
    EmptyError::class => 'Required',
    InvalidError::class => 'Invalid',
];
$formatter->setMessages($messages);

$form = new UserForm();
$form->setValue($_POST);

if ($form->validate()) {
    $data = $form->getValue();
    // Or get parsed values with proper types
    $parsed = $form->getParsedValue();
} else {
    foreach ([$form->username, $form->email, $form->age] as $field) {
        if ($field->hasError()) {
            echo $field->getLabel() . ': ';
            echo $formatter->format($field->getError()) . "\n";
        }
    }
}
```

---

## Quick Reference: API Changes

### Form Methods

| 2.1.0 | 3.0.0 |
|-------|-------|
| `$form->setItem('name', $input)` | `$form->name = $input` |
| `$form->getItem('name')` | `$form->name` or `$form->getItem('name')` |
| `$form->getItemValue('name')` | `$form->name->getValue()` |
| `$form->getItemIn('address/city')` | `$form->address->city` |
| `$form->getFilled()` | `$form->getFilledValue()` |
| `$form->disable()` | `$form->setDisabled(true)` |
| `$form->enable()` | `$form->setDisabled(false)` |

### Input State Methods

| 2.1.0 | 3.0.0 |
|-------|-------|
| `$input->disable()` | `$input->setDisabled(true)` |
| `$input->enable()` | `$input->setDisabled(false)` |
| `$input->disable(true)` | `$input->setDisabled(true)` |

### Error Handling

| 2.1.0 | 3.0.0 |
|-------|-------|
| `$error->code` | `get_class($error)` or `$error instanceof \Coroq\Form\Error\EmptyError` |
| `$input->getErrorString()` | `$formatter->format($error)` |
| `Input::setDefaultErrorStringifier()` | `ErrorMessageFormatter` with custom message array |

---

## Checklist

- [ ] Update PHP to 8.0+
- [ ] Update all `use Coroq\Form\Input\*` to `use Coroq\Form\FormItem\*`
- [ ] Rename all Input class names (e.g., `Input\Text` → `FormItem\TextInput`)
- [ ] Replace `setItem()` calls with property assignments
- [ ] Replace `getItem()` calls with property access
- [ ] Replace `getItemValue()` with `->property->getValue()`
- [ ] Replace `getItemIn()` with nested property access
- [ ] Convert `getFilled()` to `getFilledValue()`
- [ ] Replace `disable()`/`enable()` with `setDisabled(bool)`
- [ ] Update error handling from error codes to error classes
- [ ] Replace error stringifiers with `ErrorMessageFormatter`
- [ ] Update Computed inputs to use `Derived` with `setValueCalculator()` and `addSource()`
- [ ] Create Form subclasses for better type safety (recommended)
- [ ] Consider using new features: `BooleanInput`, `FileInput`, `RepeatingForm`, `getParsedValue()`

---

## Need Help?

If you encounter issues during migration:

1. Check the [README.md](README.md) for complete 3.0.0 documentation
2. Review the test files in `test/` for usage examples
