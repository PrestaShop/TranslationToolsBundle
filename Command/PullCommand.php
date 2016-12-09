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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PrestaShop\TranslationToolsBundle\Configuration;

class PullCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:pull')
            ->setDescription('Pull translations from Crowdin')
            ->addOption('branch', null, InputOption::VALUE_REQUIRED)
            ->addOption('package', null, InputOption::VALUE_OPTIONAL, 'Language code or "all" to download a bundle with translations to all languages.', 'all');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packageFileList = $this->downloadCrowdinTranslations($input);

        $this->createPacks(
            $packageFileList, $this->getContainer()->getParameter('translation.dir_dump').DIRECTORY_SEPARATOR.'packs'
        );
    }

    /**
     * @param array  $packageFileList
     * @param string $destination
     */
    protected function createPacks(array $packageFileList, $destination)
    {
        $tempDir = Configuration::getCacheDir().'flattened'.DIRECTORY_SEPARATOR;

        $this->flattenPackageFiles($packageFileList, $tempDir);
        $flattenedPackageFileList = $this->getPackageFileList($tempDir);

        foreach ($flattenedPackageFileList as $locale => $files) {
            $this->createPackArchive($locale, $files, $destination);
        }
    }
}
