<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use Symfony\Component\Translation\Extractor\AbstractFileExtractor as BaseAbstractFileExtractor;
use Symfony\Component\Translation\Extractor\ExtractorInterface;

abstract class AbstractFileExtractor extends BaseAbstractFileExtractor implements ExtractorInterface
{
    /**
     * @var array
     */
    private $excludedDirectories = [];

    public function getExcludedDirectories(): array
    {
        return $this->excludedDirectories;
    }

    /**
     * @return AbstractFileExtractor
     */
    public function excludedDirectories(array $excludedDirectories): self
    {
        $this->excludedDirectories = $excludedDirectories;

        return $this;
    }
}
