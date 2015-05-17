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

    public function __construct($env = 'test', $debug = true)
    {
        parent::__construct($env, $debug);
        $this->hash = substr(md5(mt_rand(0,10000).microtime(true). mt_rand(0, 100000)), -6);
    }

    /**
     * @param $yaml
     */
    public function appendConfig($yaml) {
        $this->configs[] = $yaml;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     *
     * @api
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $config = $this->getRootDir(). '/config.yml';
        if (is_file($config)) {
            $loader->load($config);
        }

        foreach ($this->configs as $i => $config) {
            $file = $this->getCacheDir() .'/config'.$i.'.yml';
            file_put_contents($file, $config);
            $loader->load($file);
        }
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/kernel/'.$this->hash.'/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir() . '/kernel/'.$this->hash.'/logs';
    }

    /**
     * @return string
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
        if (! $this->containerBuilder) {
            $this->containerBuilder = $this->buildContainer();
            $this->containerBuilder->compile();
        }

        return $this->containerBuilder;
    }
}
