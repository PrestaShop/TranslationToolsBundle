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

use PrestaShop\TranslationToolsBundle\Translation\MultilanguageCatalog;
use PrestaShop\TranslationToolsBundle\Translation\Parser\CrowdinPhpParser;
use Symfony\Component\Finder\Finder;

class TranslationManager
{
    /** @var MultilanguageCatalog */
    private $catalog;

    /** @var CrowdinPhpParser */
    private $parser;

    public function __construct(CrowdinPhpParser $crodwinPhpParser)
    {
        $this->parser = $crodwinPhpParser;
        $this->catalog = new MultilanguageCatalog();
    }

    /**
     * @param string $filePath
     * @param string $key
     *
     * @return string
     */
    public function get($filePath, $key)
    {
        if (!$this->catalog->has($key)) {
            $this->extractFile($filePath);
        }

        return $this->catalog->has($key) ? $this->catalog->get($key) : null;
    }

    /**
     * @param string $filePath
     */
    private function extractFile($filePath)
    {
        $finder = new Finder();

        $fullpath = preg_replace('/([a-z]{2}-[A-Z]{2})/', '*', $filePath);
        $filename = basename($fullpath);
        $directory = pathinfo(str_replace('*', '', $fullpath), PATHINFO_DIRNAME);

        if (!file_exists($directory)) {
            return false;
        }

        $files = $finder->files()->name($filename)->in($directory);

        foreach ($files as $file) {
            if (preg_match('/([a-z]{2}-[A-Z]{2})/', $file->getRealpath(), $matches)) {
                $generator = $this->parser->parseFileTokens($file->getRealpath());

                for (; $generator->valid(); $generator->next()) {
                    $translation = $generator->current();

                    if (!empty($translation['message'])) {
                        $this->catalog->set($translation['key'], $matches[1], $translation['message']);
                    }
                }
            }
        }
    }
}
