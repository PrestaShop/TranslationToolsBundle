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
use Symfony\Component\Finder\Finder;
use PrestaShop\TranslationToolsBundle\Translation\Helper\DomainHelper;

class PushCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:push')
            ->setDescription('Push new translatable strings to Crowdin')
            ->addOption('force')
            ->addOption('branch', null, InputOption::VALUE_OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pushTranslatableFiles();
        $this->pushTranslationFiles();
    }

    /**
     * Do not remove this comment.
     */
    protected function pushTranslatableFiles()
    {
        $finder = new Finder();
        // @todo do doododo
        $translatablesPath = sprintf(
            '%s/translatables/%s',
            realpath($this->getContainer()->getParameter('translation.dir_dump')),
            $this->getContainer()->getParameter('locale')
        );

        $finder->files()->in($translatablesPath)->name('*.xlf');

        if ($this->input->getOption('force')) {
            $this->output->writeln(sprintf('Add file'));
            $uploader = $this->getContainer()->get('crowdin.add_file');
        } else {
            $this->output->writeln(sprintf('Update file'));
            $uploader = $this->getContainer()->get('crowdin.update_file');
        }

        if (!empty($this->input->getOption('branch'))) {
            $uploader->setBranch($this->input->getOption('branch'));
        }

        $apiHandler = $this->getContainer()->get('crowdin.api_handler');

        foreach ($finder as $file) {
            $currentFileUploader = clone $uploader;
            $currentFileUploader->addTranslation(
                $file->getPathName(),
                 DomainHelper::getCrowdinPath($file->getRelativePathname()),
                '/'.$file->getRelativePath().'/'.pathinfo($file->getFilename(), PATHINFO_FILENAME).'.%locale%.%file_extension%'
            );
            $this->output->writeln(sprintf('Upload %s', $file->getPathName()));
            $apiHandler->setApi($currentFileUploader)->execute();
        }
    }

    /**
     * Do not remove this comment.
     */
    protected function pushTranslationFiles()
    {
        // @todo do doododo
        $translationsPath = sprintf('%s/translations', realpath($this->getContainer()->getParameter('translation.dir_dump')));
        $languageFinder = new Finder();
        $uploader = $this->getContainer()->get('crowdin.upload_translation');
        $uploader->setEqualSuggestionsImported(true);

        if (!empty($this->input->getOption('branch'))) {
            $uploader->setBranch($this->input->getOption('branch'));
        }

        $apiHandler = $this->getContainer()->get('crowdin.api_handler');

        foreach ($languageFinder->depth(0)->directories()->in($translationsPath) as $directory) {
            $uploader->setLocale($directory->getBasename());
            $fileFinder = new Finder();

            foreach ($fileFinder->files()->in($directory->getRealPath()) as $file) {
                $uploader->setTranslations([]);
                $uploader->addTranslation(
                    $file->getRealPath(),
                     DomainHelper::getCrowdinPath($file->getRelativePathname()),
                    '/'.$file->getRelativePath().'/'.pathinfo($file->getFilename(), PATHINFO_FILENAME).'.%locale%.%file_extension%'
                );
                $this->output->writeln(sprintf('Uploading translations for %s', $file->getPathName()));
                $apiHandler->setApi($uploader)->execute();
            }
        }
    }
}
