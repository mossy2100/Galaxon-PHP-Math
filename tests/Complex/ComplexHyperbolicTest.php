<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Complex;

use Galaxon\Math\Complex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Complex hyperbolic and inverse hyperbolic functions.
 */
#[CoversClass(Complex::class)]
class ComplexHyperbolicTest extends TestCase
{
    // region Hyperbolic functions

    /**
     * Test sinh (hyperbolic sine).
     */
    public function testSinh(): void
    {
        // sinh(0) = 0
        $result = (new Complex(0))->sinh();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // sinh(1) ≈ 1.1752
        $result2 = (new Complex(1))->sinh();
        $this->assertEqualsWithDelta(sinh(1), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test sinh(z) = sinh(x)cos(y) + i·cosh(x)sin(y) identity.
     */
    public function testSinhIdentity(): void
    {
        $z = new Complex(1, 1);
        $result = $z->sinh();

        $expected_real = sinh(1) * cos(1);
        $expected_imag = cosh(1) * sin(1);

        $this->assertEqualsWithDelta($expected_real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($expected_imag, $result->imaginary, 1e-10);
    }

    /**
     * Test cosh (hyperbolic cosine).
     */
    public function testCosh(): void
    {
        // cosh(0) = 1
        $result = (new Complex(0))->cosh();
        $this->assertEqualsWithDelta(1.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // cosh(1) ≈ 1.5431
        $result2 = (new Complex(1))->cosh();
        $this->assertEqualsWithDelta(cosh(1), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test cosh(z) = cosh(x)cos(y) + i·sinh(x)sin(y) identity.
     */
    public function testCoshIdentity(): void
    {
        $z = new Complex(1, 1);
        $result = $z->cosh();

        $expected_real = cosh(1) * cos(1);
        $expected_imag = sinh(1) * sin(1);

        $this->assertEqualsWithDelta($expected_real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($expected_imag, $result->imaginary, 1e-10);
    }

    /**
     * Test tanh (hyperbolic tangent).
     */
    public function testTanh(): void
    {
        // tanh(0) = 0
        $result = (new Complex(0))->tanh();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // tanh(1) ≈ 0.7616
        $result2 = (new Complex(1))->tanh();
        $this->assertEqualsWithDelta(tanh(1), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test tanh(z) = sinh(z)/cosh(z) identity.
     */
    public function testTanhIdentity(): void
    {
        $z = new Complex(1, 1);

        $tanh = $z->tanh();
        $ratio = $z->sinh()->div($z->cosh());

        $this->assertEqualsWithDelta($ratio->real, $tanh->real, 1e-10);
        $this->assertEqualsWithDelta($ratio->imaginary, $tanh->imaginary, 1e-10);
    }

    /**
     * Test sech (hyperbolic secant).
     */
    public function testSech(): void
    {
        // sech(0) = 1
        $result = (new Complex(0))->sech();
        $this->assertEqualsWithDelta(1.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // sech(1) = 1/cosh(1)
        $result2 = (new Complex(1))->sech();
        $this->assertEqualsWithDelta(1 / cosh(1), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test sech(z) = 1/cosh(z) identity.
     */
    public function testSechIdentity(): void
    {
        $z = new Complex(1, 1);

        $sech = $z->sech();
        $inv_cosh = $z->cosh()->inv();

        $this->assertEqualsWithDelta($inv_cosh->real, $sech->real, 1e-10);
        $this->assertEqualsWithDelta($inv_cosh->imaginary, $sech->imaginary, 1e-10);
    }

    /**
     * Test csch (hyperbolic cosecant).
     */
    public function testCsch(): void
    {
        // csch(1) = 1/sinh(1)
        $result = (new Complex(1))->csch();
        $this->assertEqualsWithDelta(1 / sinh(1), $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    /**
     * Test csch(z) = 1/sinh(z) identity.
     */
    public function testCschIdentity(): void
    {
        $z = new Complex(1, 1);

        $csch = $z->csch();
        $inv_sinh = $z->sinh()->inv();

        $this->assertEqualsWithDelta($inv_sinh->real, $csch->real, 1e-10);
        $this->assertEqualsWithDelta($inv_sinh->imaginary, $csch->imaginary, 1e-10);
    }

    /**
     * Test coth (hyperbolic cotangent).
     */
    public function testCoth(): void
    {
        // coth(1) = cosh(1)/sinh(1)
        $result = (new Complex(1))->coth();
        $this->assertEqualsWithDelta(cosh(1) / sinh(1), $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    /**
     * Test coth(z) = cosh(z)/sinh(z) identity.
     */
    public function testCothIdentity(): void
    {
        $z = new Complex(1, 1);

        $coth = $z->coth();
        $ratio = $z->cosh()->div($z->sinh());

        $this->assertEqualsWithDelta($ratio->real, $coth->real, 1e-10);
        $this->assertEqualsWithDelta($ratio->imaginary, $coth->imaginary, 1e-10);
    }

    /**
     * Test hyperbolic Pythagorean identity: cosh²(z) - sinh²(z) = 1.
     */
    public function testHyperbolicPythagoreanIdentity(): void
    {
        $z = new Complex(1, 1);

        $cosh2 = $z->cosh()->sqr();
        $sinh2 = $z->sinh()->sqr();
        $result = $cosh2->sub($sinh2);

        $this->assertEqualsWithDelta(1.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    // endregion

    // region Inverse hyperbolic functions

    /**
     * Test asinh (inverse hyperbolic sine).
     */
    public function testAsinh(): void
    {
        // asinh(0) = 0
        $result = (new Complex(0))->asinh();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // asinh(1) ≈ 0.8814
        $result2 = (new Complex(1))->asinh();
        $this->assertEqualsWithDelta(asinh(1), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test asinh(sinh(z)) = z round-trip identity.
     */
    public function testAsinhSinhIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->sinh()->asinh();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test acosh (inverse hyperbolic cosine).
     */
    public function testAcosh(): void
    {
        // acosh(1) = 0
        $result = (new Complex(1))->acosh();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // acosh(2) ≈ 1.3170
        $result2 = (new Complex(2))->acosh();
        $this->assertEqualsWithDelta(acosh(2), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test acosh(cosh(z)) = z round-trip identity.
     */
    public function testAcoshCoshIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->cosh()->acosh();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test atanh (inverse hyperbolic tangent).
     */
    public function testAtanh(): void
    {
        // atanh(0) = 0
        $result = (new Complex(0))->atanh();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);

        // atanh(0.5) ≈ 0.5493
        $result2 = (new Complex(0.5))->atanh();
        $this->assertEqualsWithDelta(atanh(0.5), $result2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result2->imaginary, 1e-10);
    }

    /**
     * Test atanh(tanh(z)) = z round-trip identity.
     */
    public function testAtanhTanhIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->tanh()->atanh();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test asech (inverse hyperbolic secant).
     */
    public function testAsech(): void
    {
        // asech(1) = 0
        $result = (new Complex(1))->asech();
        $this->assertEqualsWithDelta(0.0, $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    /**
     * Test asech(sech(z)) = z round-trip identity.
     */
    public function testAsechSechIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->sech()->asech();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test acsch (inverse hyperbolic cosecant).
     */
    public function testAcsch(): void
    {
        // acsch(1) = asinh(1)
        $result = (new Complex(1))->acsch();
        $this->assertEqualsWithDelta(asinh(1), $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    /**
     * Test acsch(csch(z)) = z round-trip identity.
     */
    public function testAcschCschIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->csch()->acsch();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test acoth (inverse hyperbolic cotangent).
     */
    public function testAcoth(): void
    {
        // acoth(2) = atanh(0.5)
        $result = (new Complex(2))->acoth();
        $this->assertEqualsWithDelta(atanh(0.5), $result->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $result->imaginary, 1e-10);
    }

    /**
     * Test acoth(coth(z)) = z round-trip identity.
     */
    public function testAcothCothIdentity(): void
    {
        $z = new Complex(0.5, 0.5);

        $result = $z->coth()->acoth();

        $this->assertEqualsWithDelta($z->real, $result->real, 1e-10);
        $this->assertEqualsWithDelta($z->imaginary, $result->imaginary, 1e-10);
    }

    /**
     * Test reciprocal identities for hyperbolic functions with complex numbers.
     */
    public function testReciprocalIdentitiesComplex(): void
    {
        $z = new Complex(1, 1);

        // sech(z) × cosh(z) = 1
        $product1 = $z->sech()->mul($z->cosh());
        $this->assertEqualsWithDelta(1.0, $product1->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $product1->imaginary, 1e-10);

        // csch(z) × sinh(z) = 1
        $product2 = $z->csch()->mul($z->sinh());
        $this->assertEqualsWithDelta(1.0, $product2->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $product2->imaginary, 1e-10);

        // coth(z) × tanh(z) = 1
        $product3 = $z->coth()->mul($z->tanh());
        $this->assertEqualsWithDelta(1.0, $product3->real, 1e-10);
        $this->assertEqualsWithDelta(0.0, $product3->imaginary, 1e-10);
    }

    // endregion
}
