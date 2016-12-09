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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use PrestaShop\TranslationToolsBundle\Translation\Helper\LegacyHelper;
use PrestaShop\TranslationToolsBundle\Configuration;

class MigrateCommand extends BaseCommand
{
    use TraitCacheCommand;

    /** @var MessageCatalogue $catalogue **/
    protected $catalogue;

    /** @var MessageCatalogue[] **/
    protected $translationCatalogues = [];

    /** @var OutputInterface $output */
    protected $config = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:migrate')
            ->setDescription('Migrate translations from a branch to another')
            ->addArgument('configfile', InputArgument::REQUIRED, 'Path to the config file')
            ->addOption('origin-branch', '',  InputOption::VALUE_OPTIONAL, '', 'next-version')
            ->addOption('no-cache', 'nc', InputOption::VALUE_NONE);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $this->input->getArgument('configfile');
        Configuration::fromYamlFile($configFile);
        $cacheDir = Configuration::getCacheDir();

        if (!file_exists($configFile)) {
            throw new FileNotFoundException(null, 0, null, $configFile);
        }

        $this->extractTranslations();
        $package = $this->retrievePackage('all.zip', null);

        $this->extractArchive($package, $this->getDirectory().'all');
        $legacyTranslationsDir = sprintf('%sall/%s/', $cacheDir, $this->input->getOption('origin-branch'));

        $this->translateCatalogue($legacyTranslationsDir);
        $this->dump();
    }

    protected function extractTranslations()
    {
        $this->output->writeln(sprintf('Extracting translations from: %s', Configuration::getProjectDirectory()));
        $this->catalogue = new MessageCatalogue($this->getContainer()->getParameter('locale'));

        $this->getContainer()->get('prestashop.translation.chainextractor')->extract(
            Configuration::getProjectDirectory(),
            $this->catalogue
        );
    }

    /**
     * @param string $archivePath
     * @param string $destination
     */
    protected function extractArchive($archivePath, $destination)
    {
        $this->output->writeln('<info>Extracting archive</info>');
        $zipArchive = new ZipArchive();
        $zipArchive->open($archivePath);
        $zipArchive->extractTo($destination);
    }

    /**
     * @param string $translationsDir
     */
    protected function translateCatalogue($translationsDir)
    {
        $this->output->writeln(sprintf('Searching for translated strings in: <info>%s</info>', $translationsDir));
        $translationManager = $this->getContainer()->get('prestashop.translation.manager.translation_manager');

        foreach ($this->catalogue->all() as $domain => $translations) {
            foreach (array_keys($translations) as $key) {
                $metadata = $this->catalogue->getMetadata($key, $domain);
                $relativePath = str_replace(Configuration::getProjectDirectory().DIRECTORY_SEPARATOR, '', $metadata['file']);
                $outputInfo = LegacyHelper::getOutputInfo($relativePath);

                if (is_callable($outputInfo['generateKey'])) {
                    $this->fillTranslationCatalogues(
                        $key,
                        $metadata,
                        (array) $translationManager->get(
                            $translationsDir.str_replace('[locale]', 'en-US', $outputInfo['file']),
                            '$'.$outputInfo['var']."['".$outputInfo['generateKey']($key)."']"
                        ),
                        $domain
                    );
                }
            }
        }
    }

    /**
     * @param string $key
     * @param array  $metadata
     * @param array  $translations
     * @param string $domain
     */
    protected function fillTranslationCatalogues($key, $metadata, array $translations, $domain)
    {
        foreach ($translations as $locale => $translation) {
            if (!isset($this->translationCatalogues[$locale])) {
                $this->translationCatalogues[$locale] = new MessageCatalogue($locale);
            }

            $this->translationCatalogues[$locale]->set($key, $translation, $domain);
            $this->translationCatalogues[$locale]->setMetadata($key, $metadata, $domain);
        }
    }

    protected function dump()
    {
        $this->output->writeln(
            sprintf(
                'Dumping translations to <info>%s</info>',
                $this->getContainer()->getParameter('translation.dir_dump')
            )
        );
        $dumper = $this->getContainer()->get('prestashop.dumper.xliff');

        foreach ($this->translationCatalogues as $catalogue) {
            $dumper->dump(
                $catalogue,
                [
                    'path' => sprintf('%s/translations', $this->getContainer()->getParameter('translation.dir_dump')),
                ]
            );
        }

        $dumper->dump(
            $this->catalogue,
            [
                'path' => sprintf('%s/translatables', $this->getContainer()->getParameter('translation.dir_dump')),
            ]
        );
    }
}
