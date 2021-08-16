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

use Symfony\Component\Finder\Finder;

trait TraitExtractor
{
    protected $defaultDomain = 'messages';

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @param $domainName
     *
     * @return string
     */
    protected function resolveDomain($domainName)
    {
        if (empty($domainName)) {
            return $this->defaultDomain;
        }

        return $domainName;
    }

    /**
     * @param $comments
     * @param $file
     * @param $line
     *
     * @return array
     */
    public function getEntryComment(array $comments, $file, $line)
    {
        foreach ($comments as $comment) {
            if ($comment['file'] == $file && $comment['line'] == $line) {
                return $comment['comment'];
            }
        }
    }

    /**
     * @param $finder
     *
     * @return $this
     */
    public function setFinder(Finder $finder)
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * @return Finder
     */
    public function getFinder()
    {
        if (null === $this->finder) {
            return new Finder();
        }

        return $this->finder;
    }
}
