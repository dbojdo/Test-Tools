<?php

namespace Webit\Tests\Random;

trait RandomValuesTrait
{
    /**
     * @param int|null $minLength
     * @param int|null $maxLength
     * @return bool|string
     */
    protected function randomString($minLength = null, $maxLength = null)
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

    /**
     * @param int|null $max
     * @return int
     */
    protected function randomNonNegativeInt($max = null)
    {
        return $this->randomInt(0, $max < 0 ? 0 : $max);
    }

    /**
     * @param int|null $max
     * @return int
     */
    protected function randomPositiveInt($max = null)
    {
        return $this->randomInt(1, $max < 1 ? 1 : $max);
    }

    /**
     * @param int|null $min
     * @param int|null $max
     * @return int
     */
    protected function randomInt($min = null, $max = null)
    {
        $min = $min === null ? $this->minInt() : $min;
        $max = $max === null ? $this->maxInt() : $max;
        $max = $min > $max ? $min : $max;

        return mt_rand($min, $max);
    }

    /**
     * @return bool
     */
    protected function randomBool()
    {
        return (bool)$this->randomNonNegativeInt(1);
    }

    /**
     * @param float|null $min
     * @param float|null $max
     * @return float
     */
    protected function randomFloat($min = null, $max = null)
    {
        $min = $min === null || $min < $this->minFloat() ? $this->minFloat() : $min;
        $max = $max === null || $max > $this->maxFloat() ? $this->maxFloat() : $max;
        $max = $min > $max ? $min : $max;

        $randFloat = $min + lcg_value() * (abs($max-$min));

        return $randFloat;
    }

    private function minFloat()
    {
        return (float)$this->minInt();
    }

    private function maxFloat()
    {
        return (float)$this->maxInt();
    }

    private function minInt()
    {
        return -PHP_INT_MAX;
    }

    private function maxInt()
    {
        return PHP_INT_MAX;
    }

    private function minStringLength()
    {
        return 1;
    }

    private function maxStringLength()
    {
        return 256;
    }
}
