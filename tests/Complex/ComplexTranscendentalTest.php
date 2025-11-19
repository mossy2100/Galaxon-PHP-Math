<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Complex;

use DomainException;
use Galaxon\Core\Angle;
use Galaxon\Math\Complex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ValueError;

#[CoversClass(Complex::class)]
class ComplexTranscendentalTest extends TestCase
{
    /**
     * Test natural logarithm of various special values.
     */
    public function testLnSpecialValues(): void
    {
        // ln(1) = 0
        $result = new Complex(1)->ln();
        $this->assertEqualsWithDelta(0.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);

        // ln(e) = 1
        $result2 = new Complex(M_E)->ln();
        $this->assertEqualsWithDelta(1.0, $result2->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, Complex::EPSILON);

        // ln(2) = M_LN2
        $result3 = new Complex(2)->ln();
        $this->assertEqualsWithDelta(M_LN2, $result3->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result3->imaginary, Complex::EPSILON);

        // ln(10) = M_LN10
        $result4 = new Complex(10)->ln();
        $this->assertEqualsWithDelta(M_LN10, $result4->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result4->imaginary, Complex::EPSILON);

        // ln(π) = M_LNPI
        $result5 = new Complex(M_PI)->ln();
        $this->assertEqualsWithDelta(M_LNPI, $result5->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result5->imaginary, Complex::EPSILON);
    }

    /**
     * Test ln of complex numbers.
     */
    public function testLnComplex(): void
    {
        // ln(z) = ln|z| + i*arg(z)
        $z = new Complex(3, 4);
        $result = $z->ln();

        /** @var float $mag */
        $mag = $z->magnitude;

        $expectedReal = log($mag);
        $expectedImag = $z->phase;

        $this->assertEqualsWithDelta($expectedReal, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta($expectedImag, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test ln(0) throws exception.
     */
    public function testLnZero(): void
    {
        $this->expectException(ValueError::class);
        new Complex(0)->ln();
    }

    /**
     * Test logarithm with various bases.
     */
    public function testLogReals(): void
    {
        // log_2(8) = 3
        $result = new Complex(8)->log(2);
        $this->assertEqualsWithDelta(3.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);

        // log_10(100) = 2
        $result2 = new Complex(100)->log(10);
        $this->assertEqualsWithDelta(2.0, $result2->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, Complex::EPSILON);

        // log_e(e) = 1 (natural log)
        $result3 = new Complex(M_E)->log(M_E);
        $this->assertEqualsWithDelta(1.0, $result3->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result3->imaginary, Complex::EPSILON);

        // log_2(e)
        $result4 = new Complex(M_E)->log(2);
        $this->assertEqualsWithDelta(M_LOG2E, $result4->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result4->imaginary, Complex::EPSILON);

        // log_10(e)
        $result5 = new Complex(M_E)->log(10);
        $this->assertEqualsWithDelta(M_LOG10E, $result5->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result5->imaginary, Complex::EPSILON);
    }

    /**
     * Test log with complex numbers.
     */
    public function testLogComplex(): void
    {
        // log_b(z) = ln(z) / ln(b)
        $z = new Complex(3, 4);
        $base = new Complex(2, 1);

        $result = $z->log($base);

        // Verify using the change of base formula
        $lnZ = $z->ln();
        $lnBase = $base->ln();
        $expected = $lnZ->div($lnBase);

        $this->assertEqualsWithDelta($expected->real, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta($expected->imaginary, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test log with base 0 throws exception.
     */
    public function testLogBaseZero(): void
    {
        $this->expectException(DomainException::class);
        new Complex(5)->log(0);
    }

    /**
     * Test log with base 1 throws exception.
     */
    public function testLogBaseOne(): void
    {
        $this->expectException(DomainException::class);
        new Complex(5)->log(1);
    }

    /**
     * Test exponential function.
     */
    public function testExpSpecialValues(): void
    {
        // e^0 = 1
        $result = new Complex(0)->exp();
        $this->assertEqualsWithDelta(1.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);

        // e^1 = e
        $result2 = new Complex(1)->exp();
        $this->assertEqualsWithDelta(M_E, $result2->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, Complex::EPSILON);

        // e^ln(2) = 2
        $result3 = new Complex(M_LN2)->exp();
        $this->assertEqualsWithDelta(2.0, $result3->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result3->imaginary, Complex::EPSILON);

        // e^ln(10) = 10
        $result4 = new Complex(M_LN10)->exp();
        $this->assertEqualsWithDelta(10.0, $result4->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result4->imaginary, Complex::EPSILON);

        // e^ln(π) = π
        $result5 = new Complex(M_LNPI)->exp();
        $this->assertEqualsWithDelta(M_PI, $result5->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result5->imaginary, Complex::EPSILON);
    }

    /**
     * Test Euler's identity, both forms.
     */
    public function testEulersIdentity(): void
    {
        // e^πi = -1
        $result5 = new Complex(0, M_PI)->exp();
        $this->assertEqualsWithDelta(-1, $result5->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result5->imaginary, Complex::EPSILON);

        // e^τi = 1
        $result5 = new Complex(0, Angle::TAU)->exp();
        $this->assertEqualsWithDelta(1, $result5->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result5->imaginary, Complex::EPSILON);
    }

    /**
     * Test power with integer exponents.
     */
    public function testPowInteger(): void
    {
        // (3 + 4i)^2
        $z = new Complex(3, 4);
        $result = $z->pow(2);

        // (3 + 4i)^2 = 9 + 24i + 16i² = 9 + 24i - 16 = -7 + 24i
        $this->assertEqualsWithDelta(-7.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(24.0, $result->imaginary, Complex::EPSILON);

        // z^0 = 1
        $result2 = $z->pow(0);
        $this->assertEqualsWithDelta(1.0, $result2->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, Complex::EPSILON);

        // z^1 = z
        $result3 = $z->pow(1);
        $this->assertEqualsWithDelta(3.0, $result3->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(4.0, $result3->imaginary, Complex::EPSILON);
    }

    /**
     * Test i^2 = -1
     */
    public function testISquared(): void
    {
        $result = Complex::i()->pow(2);

        $this->assertEqualsWithDelta(-1.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test e^w shortcut.
     */
    public function testPowEBase(): void
    {
        $w = new Complex(2, 3);
        $result = new Complex(M_E)->pow($w);

        // e^(2+3i) should equal exp(2+3i)
        $expected = $w->exp();

        $this->assertEqualsWithDelta($expected->real, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta($expected->imaginary, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test 0^0 returns 1 (conventional).
     */
    public function testZeroPowerZero(): void
    {
        $result = new Complex(0)->pow(0);

        $this->assertEqualsWithDelta(1.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test 0 raised to positive real returns 0.
     */
    public function testZeroPowerPositive(): void
    {
        $result = new Complex(0)->pow(5);

        $this->assertEqualsWithDelta(0.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test 0 raised to negative real throws exception.
     */
    public function testZeroPowerNegative(): void
    {
        $this->expectException(DomainException::class);
        new Complex(0)->pow(-2);
    }

    /**
     * Test 0 raised to complex throws exception.
     */
    public function testZeroPowerComplex(): void
    {
        $this->expectException(DomainException::class);
        new Complex(0)->pow(new Complex(1, 1));
    }

    /**
     * Test nth roots.
     */
    public function testRoots(): void
    {
        // Cube roots of 1
        $z = new Complex(1);
        $roots = $z->roots(3);

        $this->assertCount(3, $roots);

        // Verify all roots satisfy z^3 = 1
        foreach ($roots as $root) {
            $cubed = $root->pow(3);
            $this->assertEqualsWithDelta(1.0, $cubed->real, Complex::EPSILON);
            $this->assertEqualsWithDelta(0.0, $cubed->imaginary, Complex::EPSILON);
        }
    }

    /**
     * Test square roots of -1 (should be ±i).
     */
    public function testRootsOfMinusOne(): void
    {
        $z = new Complex(-1);
        $roots = $z->roots(2);

        $this->assertCount(2, $roots);

        // One root should be i, the other -i
        $root1 = $roots[0];
        $root2 = $roots[1];

        $this->assertEqualsWithDelta(0.0, $root1->real, Complex::EPSILON);
        $this->assertTrue(
            abs($root1->imaginary - 1.0) < Complex::EPSILON || abs($root1->imaginary + 1.0) < Complex::EPSILON
        );

        $this->assertEqualsWithDelta(0.0, $root2->real, Complex::EPSILON);
        $this->assertTrue(
            abs($root2->imaginary - 1.0) < Complex::EPSILON || abs($root2->imaginary + 1.0) < Complex::EPSILON
        );
    }

    /**
     * Test roots with invalid n throws exception.
     */
    public function testRootsInvalidN(): void
    {
        $this->expectException(DomainException::class);
        new Complex(1)->roots(0);
    }

    /**
     * Test roots of zero.
     */
    public function testRootsOfZero(): void
    {
        $roots = new Complex(0)->roots(3);

        $this->assertCount(1, $roots);
        $this->assertEqualsWithDelta(0.0, $roots[0]->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $roots[0]->imaginary, Complex::EPSILON);
    }

    /**
     * Test sqr (square).
     */
    public function testSqr(): void
    {
        $z = new Complex(3, 4);
        $result = $z->sqr();

        $this->assertEqualsWithDelta(-7.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(24.0, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test sqrt (principal square root).
     */
    public function testSqrt(): void
    {
        // sqrt(4) = 2
        $result = new Complex(4)->sqrt();
        $this->assertEqualsWithDelta(2.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);

        // sqrt(-1) = i (principal value)
        $result2 = new Complex(-1)->sqrt();
        $this->assertEqualsWithDelta(0.0, $result2->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(1.0, $result2->imaginary, Complex::EPSILON);
    }

    /**
     * Test cube.
     */
    public function testCube(): void
    {
        $z = new Complex(2, 0);
        $result = $z->cube();

        $this->assertEqualsWithDelta(8.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);
    }

    /**
     * Test cbrt (principal cube root).
     */
    public function testCbrt(): void
    {
        // cbrt(8) = 2
        $result = new Complex(8)->cbrt();
        $this->assertEqualsWithDelta(2.0, $result->real, Complex::EPSILON);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, Complex::EPSILON);
    }
}
