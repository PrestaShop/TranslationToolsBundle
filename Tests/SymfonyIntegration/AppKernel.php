<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Appkernel for tests
 */
class AppKernel extends Kernel
{
    /**
     * Force the container to be built everytime we instanciate AppKernel.
     *
     * @var string
     */
    private $uniqCachePath;

    public function __construct()
    {
        $this->uniqCachePath = uniqid();

        parent::__construct('test', true);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        date_default_timezone_set('UTC');

        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new PrestaShop\TranslationToolsBundle\TranslationToolsBundle(),
        ];

        return $bundles;
    }

    /**
     * @return null
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config_test.yml');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->guessTempDirectoryFor('cache');
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->guessTempDirectoryFor('logs');
    }

    private function guessTempDirectoryFor($dirname)
    {
        return is_writable(__DIR__ . '/../../../../build/tmp') ? __DIR__ . '/build/tmp/' . $this->uniqCachePath . '/' . $dirname : sys_get_temp_dir() . '/TranslationToolsBundleTest/' . $this->uniqCachePath . '/' . $dirname;
    }
}
