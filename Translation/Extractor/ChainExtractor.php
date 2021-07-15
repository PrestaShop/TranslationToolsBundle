<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Configuration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\ChainExtractor as BaseChaineExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class ChainExtractor extends BaseChaineExtractor
{
    /**
     * The extractors.
     *
     * @var ExtractorInterface[]
     */
    private $extractors = [];

    /**
     * @param string $format
     *
     * @return self
     */
    public function addExtractor($format, ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($directory, MessageCatalogue $catalogue)
    {
        $finder = new Finder();

        $finder->ignoreUnreadableDirs();

        foreach (Configuration::getPaths() as $item) {
            $finder->path('{^' . $item . '}');
        }

        foreach (Configuration::getExcludeFiles() as $item) {
            $finder->notPath('{^' . $item . '}');
        }

        foreach ($this->extractors as $extractor) {
            $extractor->setFinder(clone $finder);
            $extractor->extract($directory, $catalogue);
        }
    }
}
