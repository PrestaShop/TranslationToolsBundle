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

use PrestaShop\TranslationToolsBundle\Translation\Manager\OriginalStringManager;
use PrestaShop\TranslationToolsBundle\Translation\Parser\CrowdinPhpParser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class CrowdinPhpExtractor extends AbstractFileExtractor
{
    /**
     * Prefix for new found message.
     *
     * @var string
     */
    private $prefix = '';

    /** @var CrowdinPhpParser */
    private $crodwinPhpParser;

    /** @var OriginalStringManager */
    private $originalStringManager;

    public function __construct(CrowdinPhpParser $crodwinPhpParser, OriginalStringManager $originalStringManager)
    {
        $this->crodwinPhpParser = $crodwinPhpParser;
        $this->originalStringManager = $originalStringManager;
    }

    /**
     * {@inheritdoc}
     */
    public function extract($resource, MessageCatalogue $catalog)
    {
        $files = $this->extractFiles($resource);
        foreach ($files as $file) {
            $generator = $this->crodwinPhpParser->parseFileTokens($file);
            for (; $generator->valid(); $generator->next()) {
                $translation = $generator->current();
                $originalTranslation = $this->originalStringManager->get($file, $translation['key']);

                $catalog->set($originalTranslation, $translation['message']);
                $catalog->setMetadata(
                    $originalTranslation,
                    [
                        'key' => $translation['key'],
                        'file' => basename($file),
                    ]
                );
            }

            if (PHP_VERSION_ID >= 70000) {
                // PHP 7 memory manager will not release after token_get_all(), see https://bugs.php.net/70098
                gc_mem_caches();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param string $file
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && 'php' === pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * @param string|array $directory
     *
     * @return array
     */
    protected function extractFromDirectory($directory)
    {
        $finder = new Finder();

        return $finder->files()
            ->name('*.php')
            ->in($directory)
            ->exclude($this->getExcludedDirectories());
    }
}
