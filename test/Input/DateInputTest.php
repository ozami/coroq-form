<?php
use Coroq\Form\FormItem\DateInput;
use Coroq\Form\Error\InvalidDateError;
use PHPUnit\Framework\TestCase;

class DateInputTest extends TestCase {
  public function testFilter() {
    $input = (new DateInput())->setValue('2024/01/15');
    $this->assertSame('2024-01-15', $input->getValue());

    $input->setValue('2024-1-5');
    $this->assertSame('2024-01-05', $input->getValue());
  }

  public function testValidate() {
    $input = new DateInput();
    $input->setValue('2024-01-15')->validate();
    $this->assertNull($input->getError());

    $input->setValue('invalid-date')->validate();
    $this->assertInstanceOf(InvalidDateError::class, $input->getError());
  }

  public function testGetDateTime() {
    $input = (new DateInput())->setValue('2024-01-15');
    $dt = $input->getDateTime();
    $this->assertInstanceOf(DateTime::class, $dt);
    $this->assertSame('2024-01-15', $dt->format('Y-m-d'));

    $input->setValue('');
    $this->assertNull($input->getDateTime());
  }

  public function testGetDateTimeImmutable() {
    $input = (new DateInput())->setValue('2024-01-15');
    $dt = $input->getDateTimeImmutable();
    $this->assertInstanceOf(DateTimeImmutable::class, $dt);
    $this->assertSame('2024-01-15', $dt->format('Y-m-d'));

    $input->setValue('');
    $this->assertNull($input->getDateTimeImmutable());
  }

  public function testGetParsedValue() {
    $input = (new DateInput())->setValue('2024-01-15');
    $parsed = $input->getParsedValue();
    $this->assertInstanceOf(DateTimeImmutable::class, $parsed);
    $this->assertEquals($input->getDateTimeImmutable(), $parsed);
  }

  // Additional getDateTime() tests

  public function testGetDateTimeReturnsNullForInvalidDate() {
    $input = (new DateInput())->setValue('invalid-date-string');
    $this->assertNull($input->getDateTime());
  }

  public function testGetDateTimeReturnsNullForGarbage() {
    $input = (new DateInput())->setValue('xyz123');
    $this->assertNull($input->getDateTime());
  }

  public function testGetDateTimeReturnsNullAfterValidationFails() {
    $input = (new DateInput())->setValue('not-a-date');
    $input->validate();
    $this->assertTrue($input->hasError());
    $this->assertNull($input->getDateTime());
  }

  public function testGetDateTimeWithVariousFormats() {
    $input = new DateInput();

    // ISO format
    $input->setValue('2024-03-15');
    $dt = $input->getDateTime();
    $this->assertSame('2024-03-15', $dt->format('Y-m-d'));

    // US format
    $input->setValue('03/15/2024');
    $dt = $input->getDateTime();
    $this->assertSame('2024-03-15', $dt->format('Y-m-d'));

    // Text format
    $input->setValue('15 March 2024');
    $dt = $input->getDateTime();
    $this->assertSame('2024-03-15', $dt->format('Y-m-d'));

    // Relative format
    $input->setValue('today');
    $dt = $input->getDateTime();
    $this->assertInstanceOf(DateTime::class, $dt);
  }

  public function testGetDateTimeWithTimezone() {
    // DateInput uses "@" format which creates timezone with +00:00 offset
    $input = (new DateInput())->setValue('2024-01-15 12:00:00');
    $dt = $input->getDateTime();
    $this->assertSame('+00:00', $dt->getTimezone()->getName());
  }

  public function testGetDateTimeImmutableReturnsNullForInvalidDate() {
    $input = (new DateInput())->setValue('invalid-date');
    $this->assertNull($input->getDateTimeImmutable());
  }

  public function testGetDateTimeImmutableReturnsNullForEmpty() {
    $input = (new DateInput())->setValue('');
    $this->assertNull($input->getDateTimeImmutable());
  }

  public function testGetParsedValueReturnsNullForInvalidDate() {
    $input = (new DateInput())->setValue('not-a-valid-date');
    $this->assertNull($input->getParsedValue());
  }

  public function testGetParsedValueReturnsNullForEmpty() {
    $input = (new DateInput())->setValue('');
    $this->assertNull($input->getParsedValue());
  }

  public function testGetDateTimeWithLeapYear() {
    $input = (new DateInput())->setValue('2024-02-29');
    $dt = $input->getDateTime();
    $this->assertSame('2024-02-29', $dt->format('Y-m-d'));
  }

  public function testGetDateTimeWithInvalidLeapYear() {
    // 2023 is not a leap year
    $input = (new DateInput())->setValue('2023-02-29');
    // strtotime interprets this as March 1, 2023
    $dt = $input->getDateTime();
    $this->assertSame('2023-03-01', $dt->format('Y-m-d'));
  }

  public function testGetDateTimeReturnsMutableObject() {
    $input = (new DateInput())->setValue('2024-01-15');
    $dt1 = $input->getDateTime();
    $dt2 = $input->getDateTime();

    // Should be different instances
    $this->assertNotSame($dt1, $dt2);

    // But with same value
    $this->assertEquals($dt1, $dt2);

    // Verify it's mutable
    $dt1->modify('+1 day');
    $this->assertSame('2024-01-16', $dt1->format('Y-m-d'));
    $this->assertSame('2024-01-15', $dt2->format('Y-m-d'));
  }

  public function testGetDateTimeImmutableReturnsImmutableObject() {
    $input = (new DateInput())->setValue('2024-01-15');
    $dt1 = $input->getDateTimeImmutable();
    $dt2 = $input->getDateTimeImmutable();

    // Should be different instances
    $this->assertNotSame($dt1, $dt2);

    // But with same value
    $this->assertEquals($dt1, $dt2);

    // Verify it's immutable - modify returns new instance
    $dt3 = $dt1->modify('+1 day');
    $this->assertNotSame($dt1, $dt3);
    $this->assertSame('2024-01-15', $dt1->format('Y-m-d'));
    $this->assertSame('2024-01-16', $dt3->format('Y-m-d'));
  }

  public function testFilterNormalizesDateFormat() {
    $input = new DateInput();

    // Various formats should normalize to Y-m-d
    $input->setValue('2024/12/25');
    $this->assertSame('2024-12-25', $input->getValue());

    $input->setValue('12/25/2024');
    $this->assertSame('2024-12-25', $input->getValue());

    $input->setValue('25 Dec 2024');
    $this->assertSame('2024-12-25', $input->getValue());
  }

  public function testFilterWithFullWidthCharacters() {
    $input = (new DateInput())->setValue('２０２４−０１−１５');
    // Should convert to half-width
    $this->assertSame('2024-01-15', $input->getValue());
  }

  public function testFilterPreservesInvalidDateString() {
    $input = (new DateInput())->setValue('not-a-date');
    // Filter can't parse it, so it stays as-is
    $this->assertSame('not-a-date', $input->getValue());
  }

  public function testValidateWithEmptyStringWhenRequired() {
    $input = (new DateInput())->setRequired(true);
    $input->setValue('')->validate();
    $this->assertTrue($input->hasError());
  }

  public function testValidateWithEmptyStringWhenOptional() {
    $input = (new DateInput())->setRequired(false);
    $input->setValue('')->validate();
    $this->assertFalse($input->hasError());
  }

  public function testIsEmptyReturnsTrueForEmptyString() {
    $input = (new DateInput())->setValue('');
    $this->assertTrue($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForValidDate() {
    $input = (new DateInput())->setValue('2024-01-15');
    $this->assertFalse($input->isEmpty());
  }

  public function testIsEmptyReturnsFalseForInvalidDate() {
    $input = (new DateInput())->setValue('invalid');
    $this->assertFalse($input->isEmpty());
  }

  public function testGetDateTimeWithEpochZero() {
    $input = (new DateInput())->setValue('1970-01-01');
    $dt = $input->getDateTime();
    $this->assertSame('1970-01-01', $dt->format('Y-m-d'));
  }

  public function testClearRemovesDate() {
    $input = (new DateInput())->setValue('2024-01-15');
    $this->assertFalse($input->isEmpty());

    $input->clear();
    $this->assertTrue($input->isEmpty());
    $this->assertNull($input->getDateTime());
  }
}
