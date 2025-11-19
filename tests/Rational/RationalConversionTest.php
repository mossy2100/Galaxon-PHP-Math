<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Rational;

use Galaxon\Math\Rational;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RangeException;

#[CoversClass(Rational::class)]
class RationalConversionTest extends TestCase
{
    /**
     * Test toFloat conversion.
     */
    public function testToFloat(): void
    {
        $r = new Rational(1, 2);
        $this->assertSame(0.5, $r->toFloat());

        $r2 = new Rational(3, 4);
        $this->assertSame(0.75, $r2->toFloat());

        $r3 = new Rational(5, 1);
        $this->assertSame(5.0, $r3->toFloat());

        $r4 = new Rational(-7, 2);
        $this->assertSame(-3.5, $r4->toFloat());
    }

    /**
     * Test toInt conversion.
     */
    public function testToInt(): void
    {
        // Truncates towards zero
        $r = new Rational(7, 3);
        $this->assertSame(2, $r->toInt());

        $r2 = new Rational(-7, 3);
        $this->assertSame(-2, $r2->toInt());

        $r3 = new Rational(5, 1);
        $this->assertSame(5, $r3->toInt());

        $r4 = new Rational(1, 2);
        $this->assertSame(0, $r4->toInt());
    }

    /**
     * Test __toString for whole numbers.
     */
    public function testToStringWholeNumber(): void
    {
        $r = new Rational(5, 1);
        $this->assertSame('5', (string)$r);

        $r2 = new Rational(10, 2);
        $this->assertSame('5', (string)$r2);

        $r3 = new Rational(0);
        $this->assertSame('0', (string)$r3);

        $r4 = new Rational(-7, 1);
        $this->assertSame('-7', (string)$r4);
    }

    /**
     * Test __toString for fractions.
     */
    public function testToStringFraction(): void
    {
        $r = new Rational(3, 4);
        $this->assertSame('3/4', (string)$r);

        $r2 = new Rational(-5, 6);
        $this->assertSame('-5/6', (string)$r2);

        $r3 = new Rational(1, 2);
        $this->assertSame('1/2', (string)$r3);
    }

    /**
     * Test __toString with reduced fractions.
     */
    public function testToStringReduced(): void
    {
        $r = new Rational(6, 8);
        $this->assertSame('3/4', (string)$r);

        $r2 = new Rational(10, 15);
        $this->assertSame('2/3', (string)$r2);
    }

    /**
     * Test floatToRational with common fractions.
     */
    public function testFloatToRationalCommonFractions(): void
    {
        // 0.5 = 1/2
        [$num, $den] = Rational::floatToRational(0.5);
        $this->assertSame(1, $num);
        $this->assertSame(2, $den);

        // 0.25 = 1/4
        [$num, $den] = Rational::floatToRational(0.25);
        $this->assertSame(1, $num);
        $this->assertSame(4, $den);

        // 0.75 = 3/4
        [$num, $den] = Rational::floatToRational(0.75);
        $this->assertSame(3, $num);
        $this->assertSame(4, $den);

        // 0.333... ≈ 1/3
        [$num, $den] = Rational::floatToRational(1 / 3);
        $this->assertSame(1, $num);
        $this->assertSame(3, $den);

        // 0.666... ≈ 2/3
        [$num, $den] = Rational::floatToRational(2 / 3);
        $this->assertSame(2, $num);
        $this->assertSame(3, $den);
    }

    /**
     * Test floatToRational with negative numbers.
     */
    public function testFloatToRationalNegative(): void
    {
        // -0.5 = -1/2
        [$num, $den] = Rational::floatToRational(-0.5);
        $this->assertSame(-1, $num);
        $this->assertSame(2, $den);

        // -0.75 = -3/4
        [$num, $den] = Rational::floatToRational(-0.75);
        $this->assertSame(-3, $num);
        $this->assertSame(4, $den);
    }

    /**
     * Test floatToRational with whole numbers.
     */
    public function testFloatToRationalWholeNumbers(): void
    {
        // 5.0 = 5/1
        [$num, $den] = Rational::floatToRational(5.0);
        $this->assertSame(5, $num);
        $this->assertSame(1, $den);

        // -3.0 = -3/1
        [$num, $den] = Rational::floatToRational(-3.0);
        $this->assertSame(-3, $num);
        $this->assertSame(1, $den);

        // 0.0 = 0/1
        [$num, $den] = Rational::floatToRational(0.0);
        $this->assertSame(0, $num);
        $this->assertSame(1, $den);
    }

    /**
     * Test floatToRational with mathematical constants.
     */
    public function testFloatToRationalConstants(): void
    {
        // Test that it produces reasonable approximations
        [$num, $den] = Rational::floatToRational(M_PI);
        $this->assertIsInt($num);
        $this->assertIsInt($den);
        $this->assertGreaterThan(0, $den);
        // Check it's close to π
        $this->assertEqualsWithDelta(M_PI, $num / $den, 1e-10);

        [$num, $den] = Rational::floatToRational(M_E);
        $this->assertIsInt($num);
        $this->assertIsInt($den);
        $this->assertGreaterThan(0, $den);
        // Check it's close to e
        $this->assertEqualsWithDelta(M_E, $num / $den, 1e-10);
    }

    /**
     * Test round-trip conversion for exact values.
     */
    public function testRoundTripExact(): void
    {
        $r = new Rational(3, 4);
        $f = $r->toFloat();
        $r2 = new Rational($f);

        $this->assertTrue($r->equals($r2));
    }

    /**
     * Test round-trip conversion for approximate values.
     */
    public function testRoundTripApproximate(): void
    {
        // Create from float
        $r = new Rational(M_PI);
        $f = $r->toFloat();

        // Should be very close
        $this->assertEqualsWithDelta(M_PI, $f, 1e-10);
    }

    /**
     * Test floatToRational with PHP_INT_MIN float throws RangeException.
     */
    public function testFloatToRationalWithPhpIntMinThrows(): void
    {
        $this->expectException(RangeException::class);
        // (float)PHP_INT_MIN is exactly representable as a float, but is outside valid Rational range
        Rational::floatToRational((float)PHP_INT_MIN);
    }

    /**
     * Test floatToRational with value too small throws RangeException.
     */
    public function testFloatToRationalTooSmallThrows(): void
    {
        $this->expectException(RangeException::class);
        // Value smaller than 1/PHP_INT_MAX
        Rational::floatToRational(1e-20);
    }

    /**
     * Test floatToRational with value too large throws RangeException.
     */
    public function testFloatToRationalTooLargeThrows(): void
    {
        $this->expectException(RangeException::class);
        // Value larger than PHP_INT_MAX
        Rational::floatToRational((float)PHP_INT_MAX * 2);
    }
}
