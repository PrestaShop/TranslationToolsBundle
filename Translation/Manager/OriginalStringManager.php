<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Manager;

use PrestaShop\TranslationToolsBundle\Translation\Parser\CrowdinPhpParser;
use Symfony\Component\Translation\MessageCatalogue;

class OriginalStringManager
{
    /** @var string */
    private $defaultLocale = 'en-US';

    /** @var MessageCatalogue */
    private $catalogue;

    /** @var CrowdinPhpParser */
    private $parser;

    public function __construct(CrowdinPhpParser $crodwinPhpParser)
    {
        $this->parser = $crodwinPhpParser;
        $this->catalogue = new MessageCatalogue($this->defaultLocale);
    }

    /**
     * @param string $filePath
     * @param string $key
     *
     * @return string
     */
    public function get($filePath, $key)
    {
        if (!$this->catalogue->has($key)) {
            $this->extractFile($filePath);
        }

        return $this->catalogue->get($key);
    }

    /**
     * @param string $filePath
     */
    private function extractFile($filePath)
    {
        preg_match('/([a-z]{2}-[A-Z]{2})/', $filePath, $matches);
        $locale = end($matches);
        $originalFile = str_replace($locale, $this->defaultLocale, $filePath);

        $generator = $this->parser->parseFileTokens($originalFile);

        for (; $generator->valid(); $generator->next()) {
            $translation = $generator->current();

            $this->catalogue->set($translation['key'], $translation['message']);
        }
    }
}
