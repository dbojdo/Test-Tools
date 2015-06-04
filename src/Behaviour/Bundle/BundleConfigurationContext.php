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
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
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
        $this->kernel->boot();
    }

    /**
     * @Then there should be following services defined:
     * @param PyStringNode $string
     */
    public function thereShouldBeFollowingServicesDefined(PyStringNode $string)
    {
        $container = $this->kernel->getRawContainerBuilder();
        foreach (explode(',',$string->getRaw()) as $serviceName) {
            $serviceName = trim($serviceName);
            if (empty($serviceName)) {continue;}

            \PHPUnit_Framework_Assert::assertTrue($container->hasDefinition($serviceName), sprintf('Service "%s" is not defined in the Service Container.', $serviceName));
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
            \PHPUnit_Framework_Assert::assertTrue($container->hasAlias($row['alias']),
                sprintf('Alias "%s" doesn\'t exist', $row['alias']));
            \PHPUnit_Framework_Assert::assertEquals($row['service'], (string)$container->getAlias($row['alias']));
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
        foreach (explode(',',$string->getRaw()) as $serviceName) {
            $serviceName = trim($serviceName);
            if (empty($serviceName)) {continue;}
            \PHPUnit_Framework_Assert::assertFalse($container->hasDefinition($serviceName), sprintf('Service "%s" is defined in the Service Container but it should not be.', $serviceName));
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
        \PHPUnit_Framework_Assert::assertTrue($container->has('doctrine'), 'Doctrine ORM Registry not found.');

        $manager = $container->get('doctrine')->getManager($emName);
        $validator = new \Doctrine\ORM\Tools\SchemaValidator($manager);
        $errors = $validator->validateMapping();
        if (! empty($errors)) {
            $errorsString = $this->stringifyMappingErrors($errors);
            \PHPUnit_Framework_Assert::assertEmpty($errors, $errorsString);
        }
    }
}
