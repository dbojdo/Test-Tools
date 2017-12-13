<?php

namespace Webit\Tests\Unit;

use PHPUnit\Framework\TestCase;

class RandomValuesTraitTest extends TestCase
{
    use RandomValuesTrait;

    /**
     * @test
     */
    public function itGeneratesRandomBoolean()
    {
        $hasTrue = false;
        $hasFalse = false;
        for ($i = 0; $i < 100; $i++) {
            if ($this->randomBool()) {
                $hasTrue = true;
            } else {
                $hasFalse = true;
            }

            if ($hasTrue && $hasFalse) {
                break;
            }
        }

        $this->assertTrue($hasTrue, 'Could not generate "true" during 100 attempts.');
        $this->assertTrue($hasFalse, 'Could not generate "false" during 100 attempts.');
    }

    /**
     * @param int|null $minLength
     * @param int|null $maxLength
     * @param callable $assert
     * @test
     * @dataProvider randomStringMinMax
     */
    public function itGeneratesRandomString(int $minLength = null, int $maxLength = null, callable $assert)
    {
        call_user_func($assert, $this->randomString($minLength, $maxLength));
    }

    public function randomStringMinMax()
    {
        return [
            'no min, no max (0 >= result <= 256)' => [
                null,
                null,
                function ($result) {
                    $this->assertLessThanOrEqual(256, strlen($result));
                    $this->assertGreaterThanOrEqual(0, strlen($result));
                }
            ],
            'no max (MIN >= result <= 256)' => [
                $min = $this->randomPositiveInt(5),
                null,
                function ($result) use ($min) {
                    $this->assertLessThanOrEqual(256, strlen($result));
                    $this->assertGreaterThanOrEqual($min, strlen($result));
                }
            ],
            'no min (0 >= result <= MAX)' => [
                null,
                $max = $this->randomInt(10, 70),
                function ($result) use ($max) {
                    $this->assertLessThanOrEqual($max, strlen($result));
                    $this->assertGreaterThanOrEqual(0, strlen($result));
                }
            ],
            'min and max (' => [
                $min = $this->randomInt(10, 70),
                $max = $this->randomInt($min, 70),
                function ($result) use ($min, $max) {
                    $this->assertLessThanOrEqual($max, strlen($result));
                    $this->assertGreaterThanOrEqual($min, strlen($result));
                }
            ],
            'min greater then max (result == MIN)' => [
                $min = $this->randomInt(10, 70),
                mt_rand(1, $min - 1),
                function ($result) use ($min) {
                    $this->assertEquals($min, strlen($result));
                }
            ]
        ];
    }

    /**
     * @param int $min
     * @param int $max
     * @param callable $assert
     * @dataProvider randomIntMinMax
     * @test
     */
    public function itGeneratesRandomInteger(int $min = null, int $max = null, callable $assert)
    {
        call_user_func($assert, $this->randomInt($min, $max));
    }

    public function randomIntMinMax()
    {
        return [
            'no min, no max (PHP_INT_MIN >= result <= PHP_INT_MAX)' => [
                null,
                null,
                function ($result) {
                    $this->assertLessThanOrEqual(mt_getrandmax(), $result);
                    $this->assertGreaterThanOrEqual(PHP_INT_MIN, $result);
                }
            ],
            'no max (MIN >= result <= PHP_INT_MAX)' => [
                $min = PHP_INT_MIN + mt_rand(0, 100000),
                null,
                function ($result) use ($min) {
                    $this->assertLessThanOrEqual(mt_getrandmax(), $result);
                    $this->assertGreaterThanOrEqual($min, $result);
                }
            ],
            'no min (PHP_INT_MIN >= result <= MAX)' => [
                null,
                $max = mt_getrandmax() - mt_rand(0, 100000),
                function ($result) use ($max) {
                    $this->assertLessThanOrEqual($max, $result);
                    $this->assertGreaterThanOrEqual(PHP_INT_MIN, $result);
                }
            ],
            'min and max (MIN >= result <= MAX)' => [
                $min = PHP_INT_MIN + mt_rand(0, 100000),
                $max = mt_getrandmax() - mt_rand(0, 100000),
                function ($result) use ($min, $max) {
                    $this->assertLessThanOrEqual($max, $result);
                    $this->assertGreaterThanOrEqual($min, $result);
                }
            ],
            'min greater then max (result == MIN)' => [
                $min = mt_rand(PHP_INT_MIN + 1000, mt_getrandmax()),
                mt_rand(PHP_INT_MIN, $min),
                function ($result) use ($min) {
                    $this->assertEquals($result, $min);
                }
            ]
        ];
    }

    /**
     * @param int|null $max
     * @param callable $assert
     * @test
     * @dataProvider randomPositiveIntMax
     */
    public function itGeneratesPositiveInteger(int $max = null, callable $assert)
    {
        call_user_func($assert, $this->randomPositiveInt($max));
    }

    public function randomPositiveIntMax()
    {
        return [
            'no max' => [
                null,
                function ($result) {
                    $this->assertGreaterThanOrEqual(1, $result);
                }
            ],
            'max' => [
                $max = $this->randomInt(1),
                function ($result) use ($max) {
                    $this->assertGreaterThanOrEqual(1, $result);
                    $this->assertLessThanOrEqual($max, $result);
                }
            ],
            'max < 1' => [
                $max = $this->randomInt(null, 0),
                function ($result) use ($max) {
                    $this->assertEquals(1, $result);
                }
            ]
        ];
    }

    /**
     * @param int|null $max
     * @param callable $assert
     * @test
     * @dataProvider randomNonNegativeIntMax
     */
    public function itGeneratesNonNegativeInteger(int $max = null, callable $assert)
    {
        call_user_func($assert, $this->randomNonNegativeInt($max));
    }

    public function randomNonNegativeIntMax()
    {
        return [
            'no max' => [
                null,
                function ($result) {
                    $this->assertGreaterThanOrEqual(0, $result);
                }
            ],
            'max' => [
                $max = $this->randomInt(0),
                function ($result) use ($max) {
                    $this->assertGreaterThanOrEqual(0, $result);
                    $this->assertLessThanOrEqual($max, $result);
                }
            ],
            'max < 0' => [
                $max = $this->randomInt(null, -1),
                function ($result) use ($max) {
                    $this->assertEquals(0, $result);
                }
            ]
        ];
    }

    /**
     * @test
     * @param float|null $min
     * @param float|null $max
     * @param callable $assert
     * @dataProvider randomFloatMinMaxPrecision
     */
    public function itGeneratesRandomFloat(float $min = null, float $max = null, callable $assert)
    {
        call_user_func($assert, $this->randomFloat($min, $max));
    }

    public function randomFloatMinMaxPrecision()
    {
        return [
            'no min, no max' => [
                null,
                null,
                function ($result) {
                    $this->assertInternalType('float', $result);
                }
            ],
            'min, no max' => [
                $min = $this->randomInt() / 100,
                null,
                function ($result) use ($min) {
                    $this->assertGreaterThanOrEqual($min, $result);
                }
            ],
            'min, max' => [
                $min = $this->randomInt() / 100,
                $max = $this->randomInt((int)($min * 100)) / 100,
                function ($result) use ($min, $max) {
                    $this->assertGreaterThanOrEqual($min, $result);
                    $this->assertLessThanOrEqual($max, $result);
                }
            ],
            'min greater then max (result == MIN)' => [
                $min = $this->randomInt() / 100,
                $this->randomInt(null, (int)($min * 100 - 1)) / 100,
                function ($result) use ($min) {
                    $this->assertEquals($result, $min);
                }
            ]
        ];
    }
}