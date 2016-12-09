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

use ZipArchive;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use PrestaShop\TranslationToolsBundle\Configuration;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\Flattenizer;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (OutputInterface::VERBOSITY_DEBUG === $output->getVerbosity()) {
            $this->getContainer()->get('crowdin.client')->setDebug(true);
        }

        return parent::initialize($input, $output);
    }

    protected function downloadCrowdinTranslations(InputInterface $input)
    {
        $downloader = $this->getContainer()->get('crowdin.download')
            ->setPackage($input->getOption('package').'.zip')
            ->setCopyDestination(Configuration::getCacheDir());

        if (!empty($input->getOption('branch'))) {
            $downloader->setBranch($input->getOption('branch'));
        }

        $downloader->execute();
        $file = Configuration::getCacheDir().$input->getOption('package').'.zip';

        $zipArchive = new ZipArchive();
        $zipArchive->open($file);
        $zipArchive->extractTo(
            $this->getContainer()->getParameter('translation.dir_dump').DIRECTORY_SEPARATOR.'download'
        );

        return $this->getPackageFileList(
            $this->getContainer()->getParameter('translation.dir_dump').DIRECTORY_SEPARATOR.'download'
        );
    }

    /**
     * @param string $filesDirectory
     *
     * @return array
     */
    protected function getPackageFileList($filesDirectory)
    {
        $packages = [];
        $finder = new Finder();
        $files = $finder->files()->in($filesDirectory);

        foreach ($files as $file) {
            preg_match('/([a-z]{2}-[A-Z]{2})/', $file->getRealpath(), $matches);

            if (!isset($packages[$matches[1]])) {
                $packages[$matches[1]] = [];
            }

            $packages[$matches[1]][] = $file;
        }

        return $packages;
    }

    protected function flattenPackageFiles(array $packageFileList, $tempDir)
    {
        $fs = new Filesystem();
        $fs->remove($tempDir);
        $fs->mkdir($tempDir);

        foreach ($packageFileList as $locale => $files) {
            Flattenizer::flattenFiles($files, $tempDir.$locale, $locale, $fs, false);
        }
    }

    protected function createPackArchive($locale, array $files, $destination)
    {
        $zipArchive = new ZipArchive();
        $archivePath = $destination.DIRECTORY_SEPARATOR.$locale.'.zip';

        /**
         * TODO: Handle ZIP issues with Exception.
         * We could do something like https://gist.github.com/mickaelandrieu/6358e70b93901b4082279e4c02735628
         */
        if ($zipArchive->open($archivePath, ZipArchive::CREATE)) {
            foreach ($files as $file) {
                $zipArchive->addFile($file->getRealpath(), $file->getRelativePathName());
            }
        }
        $zipArchive->close();
    }
}
