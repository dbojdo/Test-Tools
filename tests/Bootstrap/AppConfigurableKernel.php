<?php

namespace Webit\Tests\Bootstrap;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Webit\Tests\Kernel\ConfigurableKernel;

class AppConfigurableKernel extends ConfigurableKernel
{
    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle()
        ];
    }
}