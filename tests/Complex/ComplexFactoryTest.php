<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Complex;

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
}
