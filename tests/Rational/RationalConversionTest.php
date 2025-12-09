<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Rational;

use DomainException;
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
     * Test floatToRatio with common fractions.
     */
    public function testFloatToRatioCommonFractions(): void
    {
        // 0.5 = 1/2
        [$num, $den] = Rational::floatToRatio(0.5);
        $this->assertSame(1, $num);
        $this->assertSame(2, $den);

        // 0.25 = 1/4
        [$num, $den] = Rational::floatToRatio(0.25);
        $this->assertSame(1, $num);
        $this->assertSame(4, $den);

        // 0.75 = 3/4
        [$num, $den] = Rational::floatToRatio(0.75);
        $this->assertSame(3, $num);
        $this->assertSame(4, $den);

        // 0.333... ≈ 1/3
        [$num, $den] = Rational::floatToRatio(1 / 3);
        $this->assertSame(1, $num);
        $this->assertSame(3, $den);

        // 0.666... ≈ 2/3
        [$num, $den] = Rational::floatToRatio(2 / 3);
        $this->assertSame(2, $num);
        $this->assertSame(3, $den);
    }

    /**
     * Test floatToRatio with negative numbers.
     */
    public function testFloatToRatioNegative(): void
    {
        // -0.5 = -1/2
        [$num, $den] = Rational::floatToRatio(-0.5);
        $this->assertSame(-1, $num);
        $this->assertSame(2, $den);

        // -0.75 = -3/4
        [$num, $den] = Rational::floatToRatio(-0.75);
        $this->assertSame(-3, $num);
        $this->assertSame(4, $den);
    }

    /**
     * Test floatToRatio with whole numbers.
     */
    public function testFloatToRatioWholeNumbers(): void
    {
        // 5.0 = 5/1
        [$num, $den] = Rational::floatToRatio(5.0);
        $this->assertSame(5, $num);
        $this->assertSame(1, $den);

        // -3.0 = -3/1
        [$num, $den] = Rational::floatToRatio(-3.0);
        $this->assertSame(-3, $num);
        $this->assertSame(1, $den);

        // 0.0 = 0/1
        [$num, $den] = Rational::floatToRatio(0.0);
        $this->assertSame(0, $num);
        $this->assertSame(1, $den);
    }

    /**
     * Test floatToRatio with mathematical constants.
     */
    public function testFloatToRatioConstants(): void
    {
        // Test that it produces reasonable approximations
        [$num, $den] = Rational::floatToRatio(M_PI);
        $this->assertIsInt($num);
        $this->assertIsInt($den);
        $this->assertGreaterThan(0, $den);
        // Check it's close to π
        $this->assertEqualsWithDelta(M_PI, $num / $den, 1e-10);

        [$num, $den] = Rational::floatToRatio(M_E);
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

        $this->assertTrue($r->equal($r2));
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
     * Test floatToRatio with PHP_INT_MIN float throws RangeException.
     */
    public function testFloatToRatioWithPhpIntMinThrows(): void
    {
        $this->expectException(RangeException::class);
        // (float)PHP_INT_MIN is exactly representable as a float, but is outside valid Rational range
        Rational::floatToRatio((float)PHP_INT_MIN);
    }

    /**
     * Test floatToRatio with value too small throws RangeException.
     */
    public function testFloatToRatioTooSmallThrows(): void
    {
        $this->expectException(RangeException::class);
        // Value smaller than 1/PHP_INT_MAX
        Rational::floatToRatio(1e-20);
    }

    /**
     * Test floatToRatio with value too large throws RangeException.
     */
    public function testFloatToRatioTooLargeThrows(): void
    {
        $this->expectException(RangeException::class);
        // Value larger than PHP_INT_MAX
        Rational::floatToRatio((float)PHP_INT_MAX * 2);
    }

    /**
     * Test floatToRatio with infinity throws DomainException.
     */
    public function testFloatToRatioInfinityThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::floatToRatio(INF);
    }

    /**
     * Test floatToRatio with negative infinity throws DomainException.
     */
    public function testFloatToRatioNegativeInfinityThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::floatToRatio(-INF);
    }

    /**
     * Test floatToRatio with NaN throws DomainException.
     */
    public function testFloatToRatioNanThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::floatToRatio(NAN);
    }

    /**
     * Test floatToRatio with value that causes convergent overflow.
     *
     * Note: Code coverage tools can verify this hits the overflow guard, but the test cannot programmatically verify
     * the exit path.
     */
    public function testFloatToRatioConvergentExceedsLimit(): void
    {
        // Testing with random floats revealed many cases where the next convergent would exceed PHP_INT_MAX, causing
        // the algorithm to return the current best approximation. This is one such value.
        $value = 2.1213650134300899e-10;
        [$num, $den] = Rational::floatToRatio($value);

        $this->assertEquals(431, $num);
        $this->assertEquals(2031710701701, $den);

        // Should return best approximation before overflow, and the difference should be small.
        $this->assertEqualsWithDelta($value, $num / $den, 1e-10);
    }

    /**
     * Test floatToRatio with value that terminates with zero remainder.
     *
     * Note: Code coverage tools can verify this hits the zero remainder exit, but the test cannot programmatically
     * verify the exit path.
     */
    public function testFloatToRatioZeroRemainder(): void
    {
        // Testing with random floats revealed many cases where the test for zero remainder causes the algorithm to
        // return the current best approximation. This is one such value.
        $value = 2.176543618258578e-17;
        [$num, $den] = Rational::floatToRatio($value);

        $this->assertEquals(1, $num);
        $this->assertEquals(45944404312011256, $den);

        // Should return best approximation before overflow, and the difference should be small.
        $this->assertEqualsWithDelta($value, $num / $den, 1e-10);
    }
}
