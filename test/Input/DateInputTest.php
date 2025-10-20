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
}
