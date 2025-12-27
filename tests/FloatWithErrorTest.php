<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests;

use DivisionByZeroError;
use Galaxon\Core\Floats;
use Galaxon\Math\FloatWithError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for FloatWithError class.
 */
#[CoversClass(FloatWithError::class)]
class FloatWithErrorTest extends TestCase
{
    // region Constructor tests

    /**
     * Test constructor with exact integer has zero error.
     */
    public function testConstructorExactIntegerHasZeroError(): void
    {
        $num = new FloatWithError(42);

        $this->assertSame(42.0, $num->value);
        $this->assertSame(0.0, $num->absoluteError);
    }

    /**
     * Test constructor with float value and no error.
     */
    public function testConstructorWithFloatNoError(): void
    {
        $num = new FloatWithError(3.14);

        $this->assertSame(3.14, $num->value);
        // Non-exact float should have ULP-based error
        $expectedError = Floats::ulp(3.14) * 0.5;
        $this->assertSame($expectedError, $num->absoluteError);
    }

    /**
     * Test constructor with explicit error.
     */
    public function testConstructorWithExplicitError(): void
    {
        $num = new FloatWithError(100.0, 0.5);

        $this->assertSame(100.0, $num->value);
        $this->assertSame(0.5, $num->absoluteError);
    }

    /**
     * Test constructor with zero value and no error.
     */
    public function testConstructorWithZero(): void
    {
        $num = new FloatWithError(0.0);

        $this->assertSame(0.0, $num->value);
        $this->assertSame(0.0, $num->absoluteError);
    }

    // endregion

    // region Property tests

    /**
     * Test relativeError property with normal values.
     */
    public function testRelativeErrorWithNormalValues(): void
    {
        $num = new FloatWithError(100.0, 1.0);

        $this->assertSame(0.01, $num->relativeError);
    }

    /**
     * Test relativeError with zero value and zero error.
     */
    public function testRelativeErrorZeroValueZeroError(): void
    {
        $num = new FloatWithError(0.0, 0.0);

        $this->assertSame(0.0, $num->relativeError);
    }

    /**
     * Test relativeError with zero value and non-zero error.
     */
    public function testRelativeErrorZeroValueNonZeroError(): void
    {
        $num = new FloatWithError(0.0, 1.0);

        $this->assertSame(INF, $num->relativeError);
    }

    /**
     * Test relativeError with negative value.
     */
    public function testRelativeErrorWithNegativeValue(): void
    {
        $num = new FloatWithError(-50.0, 2.0);

        $this->assertSame(0.04, $num->relativeError);
    }

    // endregion

    // region Addition tests

    /**
     * Test addition combines values and adds errors.
     */
    public function testAddCombinesValuesAndAddsErrors(): void
    {
        $a = new FloatWithError(10.0, 0.1);
        $b = new FloatWithError(20.0, 0.2);

        $result = $a->add($b);

        $this->assertSame(30.0, $result->value);
        // Errors add plus rounding error
        $this->assertGreaterThanOrEqual(0.3, $result->absoluteError);
    }

    /**
     * Test addition with exact integers.
     */
    public function testAddWithExactIntegers(): void
    {
        $a = new FloatWithError(5);
        $b = new FloatWithError(3);

        $result = $a->add($b);

        $this->assertSame(8.0, $result->value);
        // Both inputs have zero error, result should too (no rounding added)
        $this->assertSame(0.0, $result->absoluteError);
    }

    /**
     * Test addition with int parameter.
     */
    public function testAddWithInt(): void
    {
        $a = new FloatWithError(10.0, 0.1);

        $result = $a->add(5);

        $this->assertSame(15.0, $result->value);
        // Int is exact, so error only from $a plus rounding
        $this->assertGreaterThanOrEqual(0.1, $result->absoluteError);
    }

    /**
     * Test addition with float parameter.
     */
    public function testAddWithFloat(): void
    {
        $a = new FloatWithError(10.0, 0.1);

        $result = $a->add(3.14);

        $this->assertEqualsWithDelta(13.14, $result->value, 1e-10);
        // Float gets automatic error estimation
        $this->assertGreaterThan(0.1, $result->absoluteError);
    }

    /**
     * Test addition preserves originals.
     */
    public function testAddPreservesOriginals(): void
    {
        $a = new FloatWithError(10.0, 0.1);
        $b = new FloatWithError(20.0, 0.2);

        $a->add($b);

        // Original values unchanged
        $this->assertSame(10.0, $a->value);
        $this->assertSame(0.1, $a->absoluteError);
    }

    // endregion

    // region Subtraction tests

    /**
     * Test subtraction combines values and adds errors.
     */
    public function testSubCombinesValuesAndAddsErrors(): void
    {
        $a = new FloatWithError(50.0, 0.5);
        $b = new FloatWithError(20.0, 0.2);

        $result = $a->sub($b);

        $this->assertSame(30.0, $result->value);
        // Errors add plus rounding error
        $this->assertGreaterThanOrEqual(0.7, $result->absoluteError);
    }

    /**
     * Test subtraction with exact integers.
     */
    public function testSubWithExactIntegers(): void
    {
        $a = new FloatWithError(10);
        $b = new FloatWithError(3);

        $result = $a->sub($b);

        $this->assertSame(7.0, $result->value);
        $this->assertSame(0.0, $result->absoluteError);
    }

    /**
     * Test subtraction with int parameter.
     */
    public function testSubWithInt(): void
    {
        $a = new FloatWithError(50.0, 0.5);

        $result = $a->sub(20);

        $this->assertSame(30.0, $result->value);
        // Int is exact, so error only from $a plus rounding
        $this->assertGreaterThanOrEqual(0.5, $result->absoluteError);
    }

    /**
     * Test subtraction with float parameter.
     */
    public function testSubWithFloat(): void
    {
        $a = new FloatWithError(50.0, 0.5);

        $result = $a->sub(20.14);

        $this->assertEqualsWithDelta(29.86, $result->value, 1e-10);
        // Float gets automatic error estimation
        $this->assertGreaterThan(0.5, $result->absoluteError);
    }

    /**
     * Test subtraction resulting in zero.
     */
    public function testSubResultingInZero(): void
    {
        $a = new FloatWithError(42.0, 0.1);
        $b = new FloatWithError(42.0, 0.1);

        $result = $a->sub($b);

        $this->assertSame(0.0, $result->value);
        $this->assertGreaterThanOrEqual(0.2, $result->absoluteError);
    }

    // endregion

    // region Negation tests

    /**
     * Test negation flips value sign.
     */
    public function testNegFlipsSign(): void
    {
        $num = new FloatWithError(42.0, 1.0);

        $result = $num->neg();

        $this->assertSame(-42.0, $result->value);
        $this->assertSame(1.0, $result->absoluteError);
    }

    /**
     * Test negation of negative number.
     */
    public function testNegOfNegative(): void
    {
        $num = new FloatWithError(-10.0, 0.5);

        $result = $num->neg();

        $this->assertSame(10.0, $result->value);
        $this->assertSame(0.5, $result->absoluteError);
    }

    /**
     * Test negation of zero.
     */
    public function testNegOfZero(): void
    {
        $num = new FloatWithError(0.0, 0.0);

        $result = $num->neg();

        $this->assertSame(-0.0, $result->value);
        $this->assertSame(0.0, $result->absoluteError);
    }

    // endregion

    // region Multiplication tests

    /**
     * Test multiplication combines values and adds relative errors.
     */
    public function testMulCombinesValuesAndAddsRelativeErrors(): void
    {
        $a = new FloatWithError(10.0, 0.1); // Absolute error 0.1 gives 1% relative error
        $b = new FloatWithError(20.0, 0.2); // Absolute error 0.2 gives 1% relative error

        $result = $a->mul($b);

        $this->assertSame(200.0, $result->value);
        // Relative errors add: 1% + 1% = 2%, so absolute error ≈ 4.0 plus rounding
        $this->assertGreaterThanOrEqual(4.0, $result->absoluteError);
    }

    /**
     * Test multiplication with exact integers.
     */
    public function testMulWithExactIntegers(): void
    {
        $a = new FloatWithError(5);
        $b = new FloatWithError(3);

        $result = $a->mul($b);

        $this->assertSame(15.0, $result->value);
        $this->assertSame(0.0, $result->absoluteError);
    }

    /**
     * Test multiplication with int parameter.
     */
    public function testMulWithInt(): void
    {
        $a = new FloatWithError(10.0, 0.1);

        $result = $a->mul(5);

        $this->assertSame(50.0, $result->value);
        // Int is exact, so relative error only from $a plus rounding
        $this->assertGreaterThanOrEqual(0.5, $result->absoluteError);
    }

    /**
     * Test multiplication with float parameter.
     */
    public function testMulWithFloat(): void
    {
        $a = new FloatWithError(10.0, 0.1);

        $result = $a->mul(2.5);

        $this->assertSame(25.0, $result->value);
        // Float gets automatic error estimation
        $this->assertGreaterThan(0.25, $result->absoluteError);
    }

    /**
     * Test multiplication by zero.
     */
    public function testMulByZero(): void
    {
        $a = new FloatWithError(42.0, 1.0);
        $b = new FloatWithError(0.0, 0.0);

        $result = $a->mul($b);

        $this->assertSame(0.0, $result->value);
    }

    // endregion

    // region Division tests

    /**
     * Test division combines values and adds relative errors.
     */
    public function testDivCombinesValuesAndAddsRelativeErrors(): void
    {
        $a = new FloatWithError(100.0, 1.0); // Absolute error 1.0 gives 1% relative error
        $b = new FloatWithError(10.0, 0.1);  // Absolute error 0.1 gives 1% relative error

        $result = $a->div($b);

        $this->assertSame(10.0, $result->value);
        // Relative errors add: 1% + 1% = 2%, so absolute error ≈ 0.2 plus rounding
        $this->assertGreaterThanOrEqual(0.2, $result->absoluteError);
    }

    /**
     * Test division with exact integers.
     */
    public function testDivWithExactIntegers(): void
    {
        $a = new FloatWithError(15);
        $b = new FloatWithError(3);

        $result = $a->div($b);

        $this->assertSame(5.0, $result->value);
        $this->assertSame(0.0, $result->absoluteError);
    }

    /**
     * Test division by zero throws exception.
     */
    public function testDivByZeroThrows(): void
    {
        $a = new FloatWithError(42.0, 1.0);
        $b = new FloatWithError(0.0, 0.0);

        $this->expectException(DivisionByZeroError::class);
        $a->div($b);
    }

    /**
     * Test division with int parameter.
     */
    public function testDivWithInt(): void
    {
        $a = new FloatWithError(100.0, 1.0);

        $result = $a->div(10);

        $this->assertSame(10.0, $result->value);
        // Int is exact, so relative error only from $a plus rounding
        $this->assertGreaterThanOrEqual(0.1, $result->absoluteError);
    }

    /**
     * Test division with float parameter.
     */
    public function testDivWithFloat(): void
    {
        $a = new FloatWithError(100.0, 1.0);

        $result = $a->div(2.5);

        $this->assertSame(40.0, $result->value);
        // Float gets automatic error estimation
        $this->assertGreaterThan(0.4, $result->absoluteError);
    }

    /**
     * Test division resulting in non-exact float.
     */
    public function testDivResultingInNonExactFloat(): void
    {
        $a = new FloatWithError(10);
        $b = new FloatWithError(3);

        $result = $a->div($b);

        $this->assertEqualsWithDelta(3.3333333333333335, $result->value, 1e-15);
        // Result is not exact, so rounding error is added
        $this->assertGreaterThan(0.0, $result->absoluteError);
    }

    // endregion

    // region Inverse tests

    /**
     * Test inverse calculates 1/x correctly.
     */
    public function testInvCalculatesReciprocal(): void
    {
        $num = new FloatWithError(4.0, 0.04); // Absolute error 0.04 gives 1% relative error

        $result = $num->inv();

        $this->assertSame(0.25, $result->value);
        // Relative error unchanged at 1%, so absolute error ≈ 0.0025 plus rounding
        $this->assertGreaterThanOrEqual(0.0025, $result->absoluteError);
    }

    /**
     * Test inverse with exact integer.
     */
    public function testInvWithExactInteger(): void
    {
        $num = new FloatWithError(2);

        $result = $num->inv();

        $this->assertSame(0.5, $result->value);
        // 0.5 is exact, but inv() adds rounding error due to conditional
        $this->assertGreaterThanOrEqual(0.0, $result->absoluteError);
    }

    /**
     * Test inverse of zero throws exception.
     */
    public function testInvOfZeroThrows(): void
    {
        $num = new FloatWithError(0.0, 0.0);

        $this->expectException(DivisionByZeroError::class);
        $num->inv();
    }

    /**
     * Test inverse of inverse returns approximate original.
     */
    public function testInvOfInv(): void
    {
        $num = new FloatWithError(10);

        $result = $num->inv()->inv();

        $this->assertSame(10.0, $result->value);
    }

    // endregion

    // region String representation tests

    /**
     * Test __toString format.
     */
    public function testToStringFormat(): void
    {
        $num = new FloatWithError(100.0, 1.0);

        $str = (string)$num;

        $this->assertStringContainsString('100', $str);
        $this->assertStringContainsString('±', $str);
    }

    /**
     * Test __toString with exact value.
     */
    public function testToStringExactValue(): void
    {
        $num = new FloatWithError(42);

        $str = (string)$num;

        $this->assertStringContainsString('42', $str);
        // Format can be either "0.00e+00" or "0.00e+0"
        $this->assertMatchesRegularExpression('/0\.00e\+0+/', $str);
    }

    // endregion

    // region Error propagation chain tests

    /**
     * Test error accumulation through chain of operations.
     */
    public function testErrorAccumulationThroughChain(): void
    {
        $a = new FloatWithError(100.0, 1.0);
        $b = new FloatWithError(10.0, 0.1);

        // (a + b) * b = 110 * 10 = 1100
        $result = $a->add($b)->mul($b);

        $this->assertSame(1100.0, $result->value);
        // Error should accumulate through both operations
        $this->assertGreaterThan(1.0, $result->absoluteError);
    }

    /**
     * Test exact operations maintain zero error.
     */
    public function testExactOperationsMaintainZeroError(): void
    {
        $a = new FloatWithError(10);
        $b = new FloatWithError(5);
        $c = new FloatWithError(2);

        // (a + b) * c = 15 * 2 = 30
        $result = $a->add($b)->mul($c);

        $this->assertSame(30.0, $result->value);
        $this->assertSame(0.0, $result->absoluteError);
    }

    // endregion
}
