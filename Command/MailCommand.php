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
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace AppBundle\Command;

use PrestaShop\TranslationToolsBundle\Exception\ModuleNotFoundException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use PrestaShop\TranslationToolsBundle\Configuration;

class MailCommand extends BaseCommand
{
    private $localesList = array();

    public function enable()
    {
        return class_exists('AppBundle\Exception\ModuleNotFoundException');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:mails')
            ->setDescription('Package emails using ps_emailgenerator')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to PrestaShop project')
            ->addOption('branch', null, InputOption::VALUE_OPTIONAL, 'Branch you want to download translations from', '1.7')
            ->addOption('package', null, InputOption::VALUE_OPTIONAL, 'Language code or "all" to download a bundle with translations to all languages.', 'all');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $psPath = $input->getOption('path').DIRECTORY_SEPARATOR;
        $modulePath = $psPath.'modules'.DIRECTORY_SEPARATOR.'ps_emailgenerator'.DIRECTORY_SEPARATOR;

        if (!is_file(
            $modulePath
            .'ps_emailgenerator.php'
        )) {
            throw new ModuleNotFoundException('Module ps_emailgenerator not found');
        }

        $packageFileList = $this->downloadCrowdinTranslations($input);

        $this->copyLastTranslationsToPrestaShop($packageFileList, $psPath);

        $this->clearPrestaShopCache($psPath);

        $this->buildEmailFiles($modulePath);

        $this->createPackages($modulePath);
    }

    private function copyLastTranslationsToPrestaShop(array $packageFileList, $psPath)
    {
        $tempDir = Configuration::getCacheDir().'flattened'.DIRECTORY_SEPARATOR;
        $translationsPath = $psPath
            .DIRECTORY_SEPARATOR
            .'app'
            .DIRECTORY_SEPARATOR
            .'Resources'
            .DIRECTORY_SEPARATOR
            .'translations'
            .DIRECTORY_SEPARATOR;

        $this->flattenPackageFiles($packageFileList, $tempDir);
        $flattenedPackageFileList = $this->getPackageFileList($tempDir);

        foreach ($flattenedPackageFileList as $locale => $files) {
            $fs = new Filesystem();
            $fs->mkdir($translationsPath.$locale);
            $this->localesList[] = $locale;

            foreach ($files as $file) {
                $fs->rename($file->getRealpath(), $translationsPath.$locale.DIRECTORY_SEPARATOR.$file->getFilename(), true);
            }
        }
    }

    private function clearPrestaShopCache($psPath)
    {
        $finder = new Finder();
        $fs = new Filesystem();

        $directoriesToRemove = $finder->directories()->in($psPath.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache');
        $fs->remove($directoriesToRemove);
    }

    private function buildEmailFiles($modulePath)
    {
        foreach ($this->localesList as $locale) {
            $command = 'php '.$modulePath.'generate-all-emails.php '.$locale;
            $process = new Process($command, null, null, null, null);
            $process->run();
        }
    }

    private function createPackages($modulePath)
    {
        $filesFinder = new Finder();
        $destination = $this->getContainer()->getParameter('translation.dir_dump').DIRECTORY_SEPARATOR.'emails';
        (new Filesystem())->mkdir($destination);

        $directories = (new Finder())->depth('< 1')->directories()->in($modulePath.'dumps');

        foreach ($directories as $locale) {
            $this->createPackArchive(
                $locale->getBasename(),
                iterator_to_array($filesFinder->files()->in($locale->getRealPath())),
                $destination
            );
        }
    }
}
