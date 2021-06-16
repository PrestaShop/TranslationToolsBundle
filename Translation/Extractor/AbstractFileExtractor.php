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

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor;

use Symfony\Component\Translation\Extractor\AbstractFileExtractor as BaseAbstractFileExtractor;

abstract class AbstractFileExtractor extends BaseAbstractFileExtractor
{
    /**
     * @param string|iterable $resource Files, a file or a directory
     * @param array $excludedResources Directories that can be excluded from scanned resources
     *
     * @return iterable
     */
    protected function extractFiles($resource, array $excludedResources = [])
    {
        if (\is_array($resource) || $resource instanceof \Traversable) {
            $files = [];
            foreach ($resource as $file) {
                if ($this->canBeExtracted($file) && !in_array($file, $excludedResources, true)) {
                    $files[] = $this->toSplFileInfo($file);
                }
            }
        } elseif (is_file($resource)) {
            $files = $this->canBeExtracted($resource) ? [$this->toSplFileInfo($resource)] : [];
        } else {
            $files = $this->extractFromDirectory($resource, $excludedResources);
        }

        return $files;
    }

    private function toSplFileInfo(string $file): \SplFileInfo
    {
        return new \SplFileInfo($file);
    }

    /**
     * @param string|array $resource Files, a file or a directory
     * @param array $excludedResources Directories that can be excluded from scanned resources
     *
     * @return iterable files to be extracted
     */
    abstract protected function extractFromDirectory($resource, array $excludedResources = []);
}
