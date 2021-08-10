<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
