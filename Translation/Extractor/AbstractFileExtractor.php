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
