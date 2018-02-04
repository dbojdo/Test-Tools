<?php
/**
 * ContainerDebugger.php
 *
 * @author cnastasi - Christian Nastasi <christian.nastasi@dxi.eu>
 * Created on Sep 10, 2015, 13:48
 * Copyright (C) DXI Ltd
 */

namespace Webit\Tests\Helper;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class ContainerDebugger
 * @package Webit\Tests\Helper
 */
class ContainerDebugger
{
    /** @var  string[] */
    private $whiteList = [];

    /** @var  string[] */
    private $blackList = [];

    /** @var ConsolePrinter  */
    private $console;

    public function __construct()
    {
        $this->console = new ConsolePrinter();
    }

    /**
     * @param $pattern
     */
    public function includeServicePattern($pattern)
    {
        $this->whiteList[] = $pattern;
    }

    /**
     * @param $pattern
     */
    public function excludeServicePattern($pattern)
    {
        $this->blackList[] = $pattern;
    }

    /**
     * @param Container $container
     */
    public function debug(Container $container)
    {
        $services = $container->getServiceIds();

        /** @var \Exception[] $errors */
        $errors = [];
        $servicesCount = 0;

        foreach ($services as $serviceName) {
            try {
                if ($this->testService($serviceName)) {
                    $servicesCount ++;

                    $container->get($serviceName);

                    $this->printOk();

                    $this->console->writeln($serviceName, ConsolePrinter::BLUE);
                }
            } catch (\Exception $ex) {
                $errors[$serviceName] = $ex;

                $this->printError();

                $this->console->writeln($serviceName, ConsolePrinter::YELLOW);
            }
        }

        $errorsCount = count($errors);

        $this->console->writeln();
        $this->console->writeln("Services checked: {$servicesCount}");

        if ($errorsCount > 0) {
            $this->console->writeln();
            $this->console->writeln("Found $errorsCount errors", ConsolePrinter::RED);
            $this->console->writeln();

            $this->printErrors($errors);
        } else {
            $this->console->writeln();
            $this->console->writeln('No errors found', ConsolePrinter::GREEN);
            $this->console->writeln();
        }
    }

    /**
     * @param \Exception[] $errors
     */
    private function printErrors(array &$errors)
    {
        foreach ($errors as $serviceName => $ex) {
            $this->console->writeln($serviceName, ConsolePrinter::YELLOW);
            $this->console->writeln($ex->getMessage(), ConsolePrinter::RED);
            $this->console->writeln('');

            $this->findInFiles('src/', $serviceName);
        }
    }

    /**
     * @param $basePath
     * @param $string
     */
    private function findInFiles($basePath, $string)
    {
        $format = 'find %s -type f | xargs grep "%s" --color -n';
        $cmd    = sprintf($format, $basePath, $string);

        $output = shell_exec($cmd);

        echo $output;
    }

    private function printOk()
    {
        $this->console->write('  OK   | ', ConsolePrinter::GREEN);
    }

    private function printError()
    {
        $this->console->write(' Error | ', ConsolePrinter::RED);
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    private function testService($serviceName)
    {
        $result = true;

        $result = $result && $this->isInWhitelist($serviceName);
        $result = $result && $this->isNotInBlackList($serviceName);

        return $result;
    }

    /**
     * Returns true if one of the patterns is match
     *
     * @param string $serviceName
     * @return bool
     */
    private function isInWhitelist($serviceName)
    {
        foreach ($this->whiteList as $pattern) {
            if (!preg_match($pattern, $serviceName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    private function isNotInBlackList($serviceName)
    {
        foreach ($this->blackList as $pattern) {
            if (preg_match($pattern, $serviceName)) {
                return false;
            }
        }

        return true;
    }
}
