<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Rational;

use Galaxon\Math\Rational;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

#[CoversClass(Rational::class)]
class RationalComparisonTest extends TestCase
{
    /**
     * Test compare with equal Rationals.
     */
    public function testCompareEqual(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(3, 4);

        $this->assertSame(0, $r1->compare($r2));

        // Different representations of same value
        $r3 = new Rational(6, 8);
        $this->assertSame(0, $r1->compare($r3));
    }

    /**
     * Test compare with less than.
     */
    public function testCompareLessThan(): void
    {
        $r1 = new Rational(1, 3);
        $r2 = new Rational(1, 2);

        $this->assertSame(-1, $r1->compare($r2));
    }

    /**
     * Test compare with greater than.
     */
    public function testCompareGreaterThan(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(1, 2);

        $this->assertSame(1, $r1->compare($r2));
    }

    /**
     * Test compare with integer.
     */
    public function testCompareWithInteger(): void
    {
        $r = new Rational(3, 2);

        $this->assertSame(-1, $r->compare(2)); // 3/2 < 2
        $this->assertSame(1, $r->compare(1));  // 3/2 > 1
        $this->assertSame(0, new Rational(4, 2)->compare(2)); // 2 == 2
    }

    /**
     * Test compare with float.
     */
    public function testCompareWithFloat(): void
    {
        $r = new Rational(1, 2);

        $this->assertSame(0, $r->compare(0.5));  // 1/2 == 0.5
        $this->assertSame(-1, $r->compare(0.6)); // 1/2 < 0.6
        $this->assertSame(1, $r->compare(0.4));  // 1/2 > 0.4
    }

    /**
     * Test compare with floats that could be integers.
     */
    public function testCompareWithFloatsThatCouldBeInts(): void
    {
        $r = new Rational(11, 2);

        $this->assertSame(-1, $r->compare(6.0)); // 11/2 < 6.0
        $this->assertSame(1, $r->compare(5.0));  // 11/2 > 5.0
    }

    /**
     * Test compare with same denominator optimization.
     */
    public function testCompareSameDenominator(): void
    {
        $r1 = new Rational(3, 7);
        $r2 = new Rational(5, 7);

        $this->assertSame(-1, $r1->compare($r2));
        $this->assertSame(1, $r2->compare($r1));
    }

    /**
     * Test compare with negative numbers.
     */
    public function testCompareNegative(): void
    {
        $r1 = new Rational(-3, 4);
        $r2 = new Rational(1, 4);

        $this->assertSame(-1, $r1->compare($r2));
        $this->assertSame(1, $r2->compare($r1));

        $r3 = new Rational(-1, 2);
        $r4 = new Rational(-3, 4);

        $this->assertSame(1, $r3->compare($r4)); // -1/2 > -3/4
    }

    /**
     * Test compare with large integers that overflow when multiplied.
     */
    public function testCompareWithIntegerMultiplyOverflow(): void
    {
        $r1 = new Rational(2 ** 30 - 1, 2 ** 35);
        $r2 = new Rational(2 ** 30 - 1, 2 ** 33);
        $result = $r1->compare($r2);
        $this->assertSame(-1, $result);
    }

    /**
     * Test compare with invalid type throws TypeError.
     */
    public function testCompareInvalidTypeThrows(): void
    {
        $this->expectException(TypeError::class);
        $r = new Rational(3, 4);
        $r->compare('string');
    }

    /**
     * Test equals with equal Rationals.
     */
    public function testEqualsTrue(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(3, 4);

        $this->assertTrue($r1->equals($r2));

        // Different representations of same value
        $r3 = new Rational(6, 8);
        $this->assertTrue($r1->equals($r3));
    }

    /**
     * Test equals with unequal Rationals.
     */
    public function testEqualsFalse(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(1, 2);

        $this->assertFalse($r1->equals($r2));
    }

    /**
     * Test equals with integer.
     */
    public function testEqualsWithInteger(): void
    {
        $r = new Rational(4, 2);
        $this->assertTrue($r->equals(2));
        $this->assertFalse($r->equals(3));
    }

    /**
     * Test equals with float.
     */
    public function testEqualsWithFloat(): void
    {
        $r = new Rational(1, 2);
        $this->assertTrue($r->equals(0.5));
        $this->assertFalse($r->equals(0.6));
    }

    /**
     * Test equals with invalid type returns false.
     */
    public function testEqualsWithInvalidType(): void
    {
        $r = new Rational(3, 4);
        $this->assertFalse($r->equals('string'));
        $this->assertFalse($r->equals([]));
        $this->assertFalse($r->equals(new stdClass()));
    }

    /**
     * Test isLessThan.
     */
    public function testIsLessThan(): void
    {
        $r1 = new Rational(1, 3);
        $r2 = new Rational(1, 2);

        $this->assertTrue($r1->isLessThan($r2));
        $this->assertFalse($r2->isLessThan($r1));
        $this->assertFalse($r1->isLessThan($r1));
    }

    /**
     * Test isLessThanOrEqual.
     */
    public function testIsLessThanOrEqual(): void
    {
        $r1 = new Rational(1, 3);
        $r2 = new Rational(1, 2);
        $r3 = new Rational(1, 3);

        $this->assertTrue($r1->isLessThanOrEqual($r2));
        $this->assertTrue($r1->isLessThanOrEqual($r3));
        $this->assertFalse($r2->isLessThanOrEqual($r1));
    }

    /**
     * Test isGreaterThan.
     */
    public function testIsGreaterThan(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(1, 2);

        $this->assertTrue($r1->isGreaterThan($r2));
        $this->assertFalse($r2->isGreaterThan($r1));
        $this->assertFalse($r1->isGreaterThan($r1));
    }

    /**
     * Test isGreaterThanOrEqual.
     */
    public function testIsGreaterThanOrEqual(): void
    {
        $r1 = new Rational(3, 4);
        $r2 = new Rational(1, 2);
        $r3 = new Rational(3, 4);

        $this->assertTrue($r1->isGreaterThanOrEqual($r2));
        $this->assertTrue($r1->isGreaterThanOrEqual($r3));
        $this->assertFalse($r2->isGreaterThanOrEqual($r1));
    }

    /**
     * Test compare with PHP_INT_MIN falls through to float comparison.
     */
    public function testCompareWithPhpIntMin(): void
    {
        $r = new Rational(1, 2);

        // Should compare via floats since PHP_INT_MIN can't be converted to Rational
        $this->assertSame(1, $r->compare(PHP_INT_MIN)); // 0.5 > PHP_INT_MIN

        // Also test with negative rational
        $r2 = new Rational(-1, 2);
        $this->assertSame(1, $r2->compare(PHP_INT_MIN)); // -0.5 > PHP_INT_MIN
    }

    /**
     * Test compare with PHP_INT_MIN as float falls through to float comparison.
     */
    public function testCompareWithPhpIntMinAsFloat(): void
    {
        $r = new Rational(1, 2);

        // (float)PHP_INT_MIN is exactly representable, but should still use float comparison path
        $this->assertSame(1, $r->compare((float)PHP_INT_MIN)); // 0.5 > PHP_INT_MIN
    }
}
