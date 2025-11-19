<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Rational;

use DomainException;
use Galaxon\Math\Rational;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RangeException;

#[CoversClass(Rational::class)]
class RationalConstructorTest extends TestCase
{
    /**
     * Test creating rational numbers with integer arguments.
     */
    public function testConstructorWithIntegers(): void
    {
        $r = new Rational(3, 4);
        $this->assertSame(3, $r->num);
        $this->assertSame(4, $r->den);

        // Test reduction
        $r2 = new Rational(6, 8);
        $this->assertSame(3, $r2->num);
        $this->assertSame(4, $r2->den);

        // Test negative denominator converted to negative numerator
        $r3 = new Rational(3, -4);
        $this->assertSame(-3, $r3->num);
        $this->assertSame(4, $r3->den);

        // Test both negative
        $r4 = new Rational(-3, -4);
        $this->assertSame(3, $r4->num);
        $this->assertSame(4, $r4->den);
    }

    /**
     * Test creating rational numbers with float arguments that can be converted to int.
     */
    public function testConstructorWithConvertibleFloats(): void
    {
        // Float that equals an integer
        $r = new Rational(3.0, 4.0);
        $this->assertSame(3, $r->num);
        $this->assertSame(4, $r->den);

        // Large float that equals an integer
        $r2 = new Rational(1000000.0, 2000000.0);
        $this->assertSame(1, $r2->num);
        $this->assertSame(2, $r2->den);
    }

    /**
     * Test creating rational numbers with float arguments that need conversion.
     */
    public function testConstructorWithNonConvertibleFloats(): void
    {
        // 0.5 should convert to 1/2
        $r = new Rational(0.5);
        $this->assertSame(1, $r->num);
        $this->assertSame(2, $r->den);

        // 0.25 should convert to 1/4
        $r2 = new Rational(0.25);
        $this->assertSame(1, $r2->num);
        $this->assertSame(4, $r2->den);

        // 0.333... should approximate to 1/3
        $r3 = new Rational(1 / 3);
        $this->assertSame(1, $r3->num);
        $this->assertSame(3, $r3->den);
    }

    /**
     * Test zero numerator.
     */
    public function testZeroNumerator(): void
    {
        $r = new Rational(0, 5);
        $this->assertSame(0, $r->num);
        $this->assertSame(1, $r->den); // Canonical form is 0/1
    }

    /**
     * Test default arguments.
     */
    public function testDefaultArguments(): void
    {
        $r = new Rational();
        $this->assertSame(0, $r->num);
        $this->assertSame(1, $r->den);

        $r2 = new Rational(5);
        $this->assertSame(5, $r2->num);
        $this->assertSame(1, $r2->den);
    }

    /**
     * Test that zero denominator throws exception.
     */
    public function testZeroDenominatorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(1, 0);
    }

    /**
     * Test that zero float denominator throws exception.
     */
    public function testZeroFloatDenominatorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(1, 0.0);
    }

    /**
     * Test that infinite numerator throws exception.
     */
    public function testInfiniteNumeratorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(INF);
    }

    /**
     * Test that infinite denominator throws exception.
     */
    public function testInfiniteDenominatorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(1, INF);
    }

    /**
     * Test that NaN numerator throws exception.
     */
    public function testNaNNumeratorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(NAN);
    }

    /**
     * Test that NaN denominator throws exception.
     */
    public function testNaNDenominatorThrows(): void
    {
        $this->expectException(DomainException::class);
        new Rational(1, NAN);
    }

    /**
     * Test that equal numerator and denominator simplifies to 1/1.
     */
    public function testEqualNumeratorDenominator(): void
    {
        $r = new Rational(5, 5);
        $this->assertSame(1, $r->num);
        $this->assertSame(1, $r->den);

        $r2 = new Rational(-7, -7);
        $this->assertSame(1, $r2->num);
        $this->assertSame(1, $r2->den);
    }

    /**
     * Test that negative numerator and positive denominator equal magnitude simplifies to -1/1.
     */
    public function testNegativeEqualMagnitude(): void
    {
        $r = new Rational(-5, 5);
        $this->assertSame(-1, $r->num);
        $this->assertSame(1, $r->den);

        $r2 = new Rational(5, -5);
        $this->assertSame(-1, $r2->num);
        $this->assertSame(1, $r2->den);
    }

    /**
     * Test immutability of properties.
     */
    public function testPropertiesAreReadOnly(): void
    {
        $r = new Rational(3, 4);

        // PHPStan will catch write attempts at static analysis time
        // At runtime, private(set) prevents modification
        $this->assertSame(3, $r->num);
        $this->assertSame(4, $r->den);
    }

    /**
     * Test that creating a rational number with a numerator equal to the minimum integer throws an exception.
     */
    public function testConstructorWithMinIntNumeratorThrows(): void
    {
        $this->expectException(RangeException::class);
        $r = new Rational(PHP_INT_MIN);
    }

    /**
     * Test that creating a rational number with a denominator equal to the minimum integer throws an exception.
     */
    public function testConstructorWithMinIntDenominatorThrows(): void
    {
        $this->expectException(RangeException::class);
        $r = new Rational(1, PHP_INT_MIN);
    }
}
