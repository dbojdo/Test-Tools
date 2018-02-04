<?php

namespace Webit\Tests\Kernel;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Yaml\Yaml;

class ConfigurableKernel extends BaseKernel
{
    /** @var string */
    private $hash;

    /** @var ContainerBuilder */
    private $containerBuilder;

    /** @var array */
    private $configs = array();

    /** @var BundleInterface[] */
    private $configuredBundles;

    public function __construct($kernelHash = null, $env = 'test', $debug = true)
    {
        $this->hash = $kernelHash ?: $this->generateHash();
        parent::__construct($env, $debug);
    }

    /**
     * @param $yaml
     * @return $this
     */
    public function appendConfig($yaml)
    {
        $this->configs[] = $yaml;
        return $this;
    }

    public function addBundle(BundleInterface $bundle)
    {
        $this->configuredBundles[] = $bundle;
    }

    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        return $this->configuredBundles;
    }

    /**
     * @inheritdoc
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->dumpConfig());
    }

    /**
     * @inheritdoc
     */
    public function getCacheDir()
    {
        return sprintf('%s/kernel/%s/cache', sys_get_temp_dir(), $this->hash);
    }

    /**
     * @inheritdoc
     */
    public function getLogDir()
    {
        return sprintf('%s/kernel/%s/logs', sys_get_temp_dir(), $this->hash);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return parent::getName() . $this->hash;
    }

    /**
     * @inheritdoc
     */
    protected function getContainerClass()
    {
        $class = parent::getContainerClass();
        return $class . ucfirst($this->hash);
    }

    /**
     * @return ContainerBuilder
     */
    public function getRawContainerBuilder()
    {
        if (!$this->containerBuilder) {
            $this->containerBuilder = $this->buildContainer();
            $this->containerBuilder->compile();
        }

        return $this->containerBuilder;
    }

    public function dumpConfig()
    {
        $configsDir = $this->getCacheDir() . '/configs_' . $this->generateHash();

        @mkdir($configsDir, 0755, true);

        $configs = $this->configs;
        $rootConfig = $this->getRootDir() . '/config.yml';
        if (is_file($rootConfig)) {
            array_unshift($configs, file_get_contents($rootConfig));
        }

        $resources = [];
        foreach ($configs as $i => $config) {
            $file = $configsDir . '/config' . $i . '.yml';
            file_put_contents($file, $config);
            $resources[] = ['resource' => $file];
        }

        $filename = sprintf('%s/main.config.%s.yml', $configsDir, $this->generateHash());

        file_put_contents(
            $filename,
            Yaml::dump([
                'imports' => $resources
            ])
        );

        return $filename;
    }

    protected function getKernelParameters()
    {
        $params = parent::getKernelParameters();
        $params['kernel.config'] = $this->dumpConfig();
        $params['kernel.hash'] = $this->hash;

        return $params;
    }

    /**
     * @return string
     */
    public function hash()
    {
        return $this->hash;
    }

    private function generateHash()
    {
        return substr(md5(mt_rand(0, 10000) . microtime(true) . mt_rand(0, 100000)), -6);
    }

    public function clear()
    {
        exec(sprintf('rm -rf %s', $this->rootDir));
    }
}
