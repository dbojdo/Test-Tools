<?php

namespace Webit\Tests\Unit;

trait RandomValuesTrait
{
    protected function randomString(int $minLength = null, int $maxLength = null): string
    {
        $minLength = $minLength === null || $minLength < $this->minStringLength() ? $this->minStringLength() : $minLength;
        $maxLength = $maxLength === null || $maxLength > $this->maxStringLength() ? $this->maxStringLength() : $maxLength;

        $maxLength = $minLength > $maxLength ? $minLength : $maxLength;

        $string = '';
        do {
            $string .= md5($this->randomInt().microtime());
        } while (strlen($string) < $maxLength);

        return substr($string, 0, $this->randomInt($minLength, $maxLength));
    }

    protected function randomNonNegativeInt(int $max = null): int
    {
        return $this->randomInt(0, $max < 0 ? 0 : $max);
    }

    protected function randomPositiveInt(int $max = null): int
    {
        return $this->randomInt(1, $max < 1 ? 1 : $max);
    }

    protected function randomInt(int $min = null, int $max = null): int
    {
        $min = $min === null ? $this->minInt() : $min;
        $max = $max === null ? $this->maxInt() : $max;
        $max = $min > $max ? $min : $max;

        return mt_rand($min, $max);
    }

    /**
     * @return bool
     */
    protected function randomBool(): bool
    {
        return (bool)$this->randomNonNegativeInt(1);
    }

    /**
     * @param float|null $min
     * @param float|null $max
     * @return float
     */
    protected function randomFloat(float $min = null, float $max = null): float
    {
        $min = $min === null || $min < $this->minFloat() ? $this->minFloat() : $min;
        $max = $max === null || $max > $this->maxFloat() ? $this->maxFloat() : $max;
        $max = $min > $max ? $min : $max;

        $randFloat = $min + lcg_value() * (abs($max-$min));

        return $randFloat;
    }

    private function minFloat(): float
    {
        if ($this->isPhp72()) {
            return PHP_FLOAT_MIN;
        }

        return (float)$this->minInt();
    }

    private function maxFloat(): float
    {
        if ($this->isPhp72()) {
            return PHP_FLOAT_MAX;
        }

        return (float)$this->maxInt();
    }

    private function minInt(): int
    {
        return PHP_INT_MIN;
    }

    private function maxInt(): int
    {
        return PHP_INT_MAX;
    }

    private function isPhp72(): bool
    {
        return version_compare(phpversion(), '7.2.0') >= 0;
    }

    private function minStringLength(): int
    {
        return 0;
    }

    private function maxStringLength(): int
    {
        return 256;
    }
}
