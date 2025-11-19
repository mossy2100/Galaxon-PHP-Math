<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Rational;

use DomainException;
use Galaxon\Math\Rational;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Rational::class)]
class RationalFactoryTest extends TestCase
{
    /**
     * Test parsing integer strings.
     */
    public function testParseInteger(): void
    {
        $r = Rational::parse('5');
        $this->assertSame(5, $r->num);
        $this->assertSame(1, $r->den);

        $r2 = Rational::parse('-123');
        $this->assertSame(-123, $r2->num);
        $this->assertSame(1, $r2->den);

        $r3 = Rational::parse(' 42 ');
        $this->assertSame(42, $r3->num);
        $this->assertSame(1, $r3->den);
    }

    /**
     * Test parsing float strings.
     */
    public function testParseFloat(): void
    {
        $r = Rational::parse('0.5');
        $this->assertSame(1, $r->num);
        $this->assertSame(2, $r->den);

        $r2 = Rational::parse('-0.25');
        $this->assertSame(-1, $r2->num);
        $this->assertSame(4, $r2->den);

        $r3 = Rational::parse(' 3.14 ');
        // Should convert to some rational approximation
        $this->assertIsInt($r3->num); // @phpstan-ignore method.alreadyNarrowedType
        $this->assertIsInt($r3->den); // @phpstan-ignore method.alreadyNarrowedType
    }

    /**
     * Test parsing fraction strings.
     */
    public function testParseFraction(): void
    {
        $r = Rational::parse('3/4');
        $this->assertSame(3, $r->num);
        $this->assertSame(4, $r->den);

        $r2 = Rational::parse('-5/6');
        $this->assertSame(-5, $r2->num);
        $this->assertSame(6, $r2->den);

        $r3 = Rational::parse(' 7 / 8 ');
        $this->assertSame(7, $r3->num);
        $this->assertSame(8, $r3->den);

        // Should reduce
        $r4 = Rational::parse('6/8');
        $this->assertSame(3, $r4->num);
        $this->assertSame(4, $r4->den);
    }

    /**
     * Test parsing invalid strings throws exception.
     */
    public function testParseInvalidThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::parse('abc');
    }

    /**
     * Test parsing empty string throws exception.
     */
    public function testParseEmptyThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::parse('');
    }

    /**
     * Test parsing fraction with zero denominator throws exception.
     */
    public function testParseFractionZeroDenominatorThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::parse('5/0');
    }

    /**
     * Test toRational with Rational argument.
     */
    public function testToRationalWithRational(): void
    {
        $r = new Rational(3, 4);
        $r2 = Rational::toRational($r);

        $this->assertSame($r, $r2); // Should return same instance
    }

    /**
     * Test toRational with integer argument.
     */
    public function testToRationalWithInteger(): void
    {
        $r = Rational::toRational(5);
        $this->assertSame(5, $r->num);
        $this->assertSame(1, $r->den);
    }

    /**
     * Test toRational with float argument.
     */
    public function testToRationalWithFloat(): void
    {
        $r = Rational::toRational(0.5);
        $this->assertSame(1, $r->num);
        $this->assertSame(2, $r->den);
    }

    /**
     * Test toRational with string argument.
     */
    public function testToRationalWithString(): void
    {
        $r = Rational::toRational('3/4');
        $this->assertSame(3, $r->num);
        $this->assertSame(4, $r->den);
    }

    /**
     * Test toRational with invalid string throws exception.
     */
    public function testToRationalWithInvalidStringThrows(): void
    {
        $this->expectException(DomainException::class);
        Rational::toRational('invalid');
    }
}
