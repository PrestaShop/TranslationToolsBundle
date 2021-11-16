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
     * Directories ignored when scanning files for catalogue extraction.
     *
     * @var array
     */
    protected $excludedDirectories = [];

    public function getExcludedDirectories(): array
    {
        return $this->excludedDirectories;
    }

    public function setExcludedDirectories(array $excludedDirectories): self
    {
        $this->excludedDirectories = $excludedDirectories;

        return $this;
    }

    protected function resolveDomain(?string $domainName): string
    {
        if (empty($domainName)) {
            return $this->defaultDomain;
        }

        return $domainName;
    }

    /**
     * Retrieves comments on the same line as translation string
     */
    public function getEntryComment(array $comments, string $file, int $line): ?string
    {
        foreach ($comments as $comment) {
            if ($comment['file'] == $file && $comment['line'] == $line) {
                return $comment['comment'];
            }
        }

        return null;
    }

    public function setFinder(Finder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    public function getFinder(): Finder
    {
        if (null === $this->finder) {
            return new Finder();
        }

        return $this->finder;
    }
}
