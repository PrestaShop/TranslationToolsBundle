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
