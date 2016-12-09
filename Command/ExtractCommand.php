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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use PrestaShop\TranslationToolsBundle\Configuration;

class ExtractCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('prestashop:translation:extract')
            ->addArgument('configfile', InputArgument::REQUIRED, 'Path to the config file')
            ->setDescription('Extract translation');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configFile = $input->getArgument('configfile');

        if (!file_exists($configFile)) {
            throw new FileNotFoundException(null, 0, null, $configFile);
        }

        Configuration::fromYamlFile($configFile);

        $locale = $this->getContainer()->getParameter('locale');

        $output->writeln(sprintf('Extracting Translations for locale <info>%s</info>', $locale));
        $catalog = $this->extract();

        $path = sprintf('%s%s%s', $this->getContainer()->getParameter('translation.dir_dump'), DIRECTORY_SEPARATOR, 'translatables');
        $dumper = $this->getContainer()->get('prestashop.dumper.xliff');
        $dumper->dump($catalog, ['path' => $path]);

        $output->writeln('<info>Dump '.$path.'</info>');
    }

    /**
     * @return MessageCatalogue
     */
    protected function extract()
    {
        $catalog = new MessageCatalogue($this->getContainer()->getParameter('locale'));

        $chainExtractor = $this->getContainer()->get('prestashop.translation.chainextractor');
        $chainExtractor->extract(Configuration::getProjectDirectory(), $catalog);

        return $catalog;
    }
}
