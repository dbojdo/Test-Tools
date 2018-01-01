<?php
/**
 * File BundleConfigurationContext.php
 * Created at: 2015-05-17 09-22
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Tests\Behaviour\Bundle;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webit\Tests\Helper\ContainerDebugger;

abstract class BundleConfigurationContext implements Context
{
    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var array
     */
    protected $services = array();

    /**
     * @var ContainerDebugger
     */
    protected $containerDebugger;

    /** @var bool */
    private $isBootstrapped = false;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->containerDebugger = new ContainerDebugger();
    }

    /**
     * @Given the configuration contains:
     * @param PyStringNode $string
     */
    public function theConfigurationContains(PyStringNode $string)
    {
        $this->kernel->appendConfig($string->getRaw());
    }

    /**
     * @When I bootstrap the application
     */
    public function iBootstrapTheApplication()
    {
        if ($this->isBootstrapped) {
            return;
        }

        $this->kernel->boot();

        $this->onKernelBoot($this->kernel, $this->kernel->getContainer());
        $this->isBootstrapped = true;

        putenv("SF_KERNEL_CONFIG=". $this->kernel->getContainer()->getParameter('kernel.config'));
        putenv("SF_KERNEL_HASH=". $this->kernel->getContainer()->getParameter('kernel.hash'));
    }

    protected function onKernelBoot(Kernel $kernel, ContainerInterface $container)
    {
    }

    /**
     * @Then there should be following services defined:
     * @param PyStringNode $string
     */
    public function thereShouldBeFollowingServicesDefined(PyStringNode $string)
    {
        $container = $this->kernel->getRawContainerBuilder();

        foreach (explode(',', $string->getRaw()) as $serviceName) {
            $serviceName = trim($serviceName);

            if (empty($serviceName)) {
                continue;
            }

            Assert::assertTrue(
                $container->hasDefinition($serviceName),
                sprintf('Service "%s" is not defined in the Service Container.',
                    $serviceName
                )
            );

            $this->services[] = $serviceName;
        }
    }

    /**
     * @Then there should be following aliases defined:
     * @param TableNode $table
     */
    public function thereShouldBeFollowingAliasesDefined(TableNode $table)
    {
        $container = $this->kernel->getRawContainerBuilder();

        foreach ($table as $row) {
            Assert::assertTrue(
                $container->hasAlias($row['alias']),
                sprintf('Alias "%s" doesn\'t exist', $row['alias'])
            );

            Assert::assertEquals($row['service'], (string)$container->getAlias($row['alias']));
            $this->services[] = $row['alias'];
        }
    }

    /**
     * @Then there should not be following services defined:
     * @param PyStringNode $string
     */
    public function thereShouldNotBeFollowingServicesDefined(PyStringNode $string)
    {
        $container = $this->kernel->getRawContainerBuilder();

        foreach (explode(',', $string->getRaw()) as $serviceName) {
            $serviceName = trim($serviceName);

            if (empty($serviceName)) {
                continue;
            }

            Assert::assertFalse(
                $container->hasDefinition($serviceName),
                sprintf('Service "%s" is defined in the Service Container but it should not be.', $serviceName)
            );
        }
    }

    /**
     * @Then all given services should be reachable
     */
    public function allGivenServicesShouldBeReachable()
    {
        foreach ($this->services as $serviceName) {
            $this->kernel->getContainer()->get($serviceName);
        }
    }

    /**
     * @Then Doctrine ORM mapping for manager :emName should be valid
     */
    public function doctrineOrmMappingShouldBeValid($emName)
    {
        $container = $this->kernel->getContainer();
        Assert::assertTrue($container->has('doctrine'), 'Doctrine ORM Registry not found.');

        $manager = $container->get('doctrine')->getManager($emName);
        $validator = new \Doctrine\ORM\Tools\SchemaValidator($manager);
        $errors = $validator->validateMapping();

        if (!empty($errors)) {
            $errorsString = $this->stringifyMappingErrors($errors);
            Assert::assertEmpty($errors, $errorsString);
        }
    }

    /**
     * @param array $errors
     * @return string
     */
    private function stringifyMappingErrors(array $errors)
    {
        $string = array();
        foreach ($errors as $type => $typeErrors) {
            $string[] = $type . ":\n" . implode("\n", $typeErrors);
        }

        return implode("\n", $string);
    }

    /**
     * @Given The container is not broken
     */
    public function theContainerIsNotBroken()
    {
        $this->containerDebugger->debug(
            $this->kernel->getContainer()
        );
    }

    /**
     * @Given services that contains :regex
     */
    public function servicesThatContains($regex)
    {
        $this->containerDebugger->includeServicePattern($regex);
    }

    /**
     * @Given services that NOT contains :regex
     */
    public function serviceThatNOTContains($regex)
    {
        $this->containerDebugger->excludeServicePattern($regex);
    }
}
