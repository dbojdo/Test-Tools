<?php
/**
 * ConsolePrinter.php
 *
 * @author cnastasi - Christian Nastasi <christian.nastasi@dxi.eu>
 * Created on Sep 10, 2015, 13:43
 * Copyright (C) DXI Ltd
 */

namespace Webit\Tests\Helper;

/**
 * Class ConsolePrinter
 */
class ConsolePrinter
{
    const NO_COLOR = "\033[0m";
    const RED      = "\033[31m";
    const GREEN    = "\033[32m";
    const YELLOW   = "\033[33m";
    const BLUE     = "\033[34m";

    public function write($string, $color = self::NO_COLOR)
    {
        echo $color . $string . self::NO_COLOR;
    }

    public function writeln($string = '', $color = self::NO_COLOR)
    {
        echo $color . $string . self::NO_COLOR . PHP_EOL;
    }
}
