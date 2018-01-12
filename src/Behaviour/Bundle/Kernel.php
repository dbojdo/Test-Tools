<?php
/**
 * File Kernel.php
 * Created at: 2015-05-17 09-22
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Tests\Behaviour\Bundle;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Yaml\Yaml;

abstract class Kernel extends BaseKernel
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var array
     */
    private $configs = array();

    public function __construct($env = 'test', $debug = true, $kernelHash = null)
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

    public function dumpConfig(): string
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

    private function generateHash()
    {
        return substr(md5(mt_rand(0, 10000) . microtime(true) . mt_rand(0, 100000)), -6);
    }
}
