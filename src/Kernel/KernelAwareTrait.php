<?php

namespace Webit\Tests\Kernel;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait KernelAwareTrait
{
    /** @var Kernel */
    private $kernel;

    /**
     * @param string|null $hash
     * @return Kernel
     */
    abstract protected function createKernel($hash = null);

    /**
     * @param bool $boot
     * @return Kernel
     */
    protected function kernel($boot = true)
    {
        if (!$this->kernel) {
            $this->kernel = $this->createKernel();
        }

        if ($boot) {
            $this->kernel->boot();
        }

        return $this->kernel;
    }

    /**
     * @return ContainerInterface
     */
    protected function container()
    {
        return $this->kernel(true)->getContainer();
    }

    /**
     * @param string $serviceName
     * @return object
     */
    protected function service($serviceName)
    {
        return $this->container()->get($serviceName);
    }
}
