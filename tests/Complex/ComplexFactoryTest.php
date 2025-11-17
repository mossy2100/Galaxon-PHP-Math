<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Complex;

use DomainException;
use Galaxon\Math\Complex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Complex::class)]
class ComplexFactoryTest extends TestCase
{
    /**
     * Test the constructor with various inputs.
     */
    public function testConstructor(): void
    {
        $z1 = new Complex(3, 4);
        $this->assertSame(3.0, $z1->real);
        $this->assertSame(4.0, $z1->imaginary);

        $z2 = new Complex(-5.5, 2.3);
        $this->assertSame(-5.5, $z2->real);
        $this->assertSame(2.3, $z2->imaginary);

        $z3 = new Complex();
        $this->assertSame(0.0, $z3->real);
        $this->assertSame(0.0, $z3->imaginary);

        $z4 = new Complex(5);
        $this->assertSame(5.0, $z4->real);
        $this->assertSame(0.0, $z4->imaginary);
    }

    /**
     * Test the constructor accepts int or float.
     */
    public function testConstructorIntFloat(): void
    {
        $z1 = new Complex(3, 4.5);
        $this->assertSame(3.0, $z1->real);
        $this->assertSame(4.5, $z1->imaginary);

        $z2 = new Complex(3.5, 4);
        $this->assertSame(3.5, $z2->real);
        $this->assertSame(4.0, $z2->imaginary);
    }

    /**
     * Test the imaginary unit static method.
     */
    public function testImaginaryUnit(): void
    {
        $i = Complex::i();
        $this->assertSame(0.0, $i->real);
        $this->assertSame(1.0, $i->imaginary);

        // Verify it's cached (same instance returned)
        $i2 = Complex::i();
        $this->assertSame($i, $i2);
    }

    /**
     * Test toComplex with Complex input.
     */
    public function testToComplexWithComplex(): void
    {
        $z = new Complex(3, 4);
        $result = Complex::toComplex($z);

        $this->assertSame($z, $result);
    }

    /**
     * Test toComplex with int input.
     */
    public function testToComplexWithInt(): void
    {
        $result = Complex::toComplex(5);

        $this->assertSame(5.0, $result->real);
        $this->assertSame(0.0, $result->imaginary);
    }

    /**
     * Test toComplex with float input.
     */
    public function testToComplexWithFloat(): void
    {
        $result = Complex::toComplex(3.14);

        $this->assertSame(3.14, $result->real);
        $this->assertSame(0.0, $result->imaginary);
    }

    /**
     * Test fromPolar with positive magnitude.
     */
    public function testFromPolarPositive(): void
    {
        $mag = 5.0;
        $phase = M_PI / 3;

        $z = Complex::fromPolar($mag, $phase);

        $this->assertEqualsWithDelta($mag * cos($phase), $z->real, 1e-10);
        $this->assertEqualsWithDelta($mag * sin($phase), $z->imaginary, 1e-10);
        $this->assertEqualsWithDelta($mag, $z->magnitude, 1e-10);
        $this->assertEqualsWithDelta($phase, $z->phase, 1e-10);
    }

    /**
     * Test fromPolar with zero magnitude.
     */
    public function testFromPolarZero(): void
    {
        $z = Complex::fromPolar(0, M_PI / 4);

        $this->assertSame(0.0, $z->real);
        $this->assertSame(0.0, $z->imaginary);
    }

    /**
     * Test fromPolar with various angles.
     */
    public function testFromPolarVariousAngles(): void
    {
        $angles = [0, M_PI / 6, M_PI / 4, M_PI / 3, M_PI / 2, M_PI, -M_PI / 2, -M_PI];

        foreach ($angles as $angle) {
            $z = Complex::fromPolar(1.0, $angle);
            $this->assertEqualsWithDelta(cos($angle), $z->real, 1e-10);
            $this->assertEqualsWithDelta(sin($angle), $z->imaginary, 1e-10);
        }
    }

    /**
     * Test fromPolar with negative magnitude throws exception.
     */
    public function testFromPolarNegativeMagnitude(): void
    {
        $this->expectException(DomainException::class);
        Complex::fromPolar(-5, M_PI / 4);
    }

    /**
     * Test fromPolar accepts int or float.
     */
    public function testFromPolarIntFloat(): void
    {
        $z1 = Complex::fromPolar(5, M_PI / 4);
        $this->assertInstanceOf(Complex::class, $z1);

        $z2 = Complex::fromPolar(5.0, M_PI / 4);
        $this->assertInstanceOf(Complex::class, $z2);

        $z3 = Complex::fromPolar(5, 0.785398163);
        $this->assertInstanceOf(Complex::class, $z3);

        $z4 = Complex::fromPolar(5.0, 0.785398163);
        $this->assertInstanceOf(Complex::class, $z4);
    }
}
