<?php

/**
 * 2007-2016 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\TranslationToolsBundle\Command;

use PrestaShop\TranslationToolsBundle\Configuration;

trait TraitCacheCommand
{
    protected static $defaultConfig = [
        'cache-ttl' => '3600',
    ];

    /**
     * @param string $name
     * @param string $branch
     *
     * @return string
     */
    protected function retrievePackage($name, $branch)
    {
        $cacheDir = Configuration::getCacheDir();

        if (true === $this->input->getOption('no-cache') || self::$defaultConfig['cache-ttl'] > @filemtime($cacheDir.'/all.zip')) {
            $package = $this->downloadPackage($name, $branch);
        } else {
            $package = $this->copyToCache();
        }

        return $package;
    }

    /**
     * @param string $name
     * @param string $branch
     *
     * @return string package filepath
     */
    public function downloadPackage($name = 'all.zip', $branch = null)
    {
        $this->output->writeln('Downloading archive </info>');

        /** @var \Akeneo\Crowdin\Api\Download $downloadManager */
        $downloadManager = $this->getContainer()->get('crowdin.download');
        $dataDir = Configuration::getCacheDir();

        if ($this->input->hasOption('branch') && null == $branch) {
            $downloadManager->setBranch($this->input->getOption('branch'));
        }

        $downloadManager
            ->setPackage($name)
            ->setCopyDestination($dataDir)
            ->execute();

        return $dataDir.DIRECTORY_SEPARATOR.$name;
    }

    /**
     * @return string
     */
    protected function copyToCache()
    {
        $this->output->writeln('Loading from cache');

        return $this->getContainer()->getParameter('translation.dir_dump').DIRECTORY_SEPARATOR.'download'.DIRECTORY_SEPARATOR.'all.zip';
    }

    /**
     * @return string
     */
    protected function getDirectory()
    {
        return Configuration::getCacheDir();
    }
}
