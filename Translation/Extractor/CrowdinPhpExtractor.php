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

use PrestaShop\TranslationToolsBundle\Translation\Manager\OriginalStringManager;
use PrestaShop\TranslationToolsBundle\Translation\Parser\CrowdinPhpParser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Extractor\AbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class CrowdinPhpExtractor extends AbstractFileExtractor implements ExtractorInterface
{
    use TraitExtractor;

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
