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

namespace PrestaShop\TranslationToolsBundle\Translation\Builder;

use Exception;

class PhpBuilder
{
    const POS_NEWLINE = 0;
    const POS_VAR = 1;
    const POS_ARRAY_KEY = 2;
    const POS_ASSIGN = 3;
    const POS_VALUE = 4;

    protected $fileName;

    protected $output;

    protected $pos = self::POS_NEWLINE;

    public function __construct()
    {
        $this->open();
    }

    /**
     * @return string
     */
    public function build()
    {
        return $this->output;
    }

    /**
     * @param string $varName
     */
    public function appendGlobalDeclaration($varName)
    {
        $this->output .= 'global ';
        $this->appendVar($varName);
        $this->appendEndOfLine();
        $this->output .= PHP_EOL;
    }

    /**
     * @param string $varName
     * @param string $key
     * @param string $value
     *
     * @return \PrestaShop\TranslationToolsBundle\Translation\Builder\PhpBuilder
     *
     * @throws Exception
     */
    public function appendStringLine($varName, $key, $value)
    {
        if ($this->pos !== self::POS_NEWLINE) {
            throw new Exception('Unable to append new line (current pos is ' . $this->pos . ')');
        }

        $this->appendVar($varName)
            ->appendKey($key)
            ->appendVarAssignation()
            ->appendValue("'" . $value . "'")
            ->appendEndOfLine();

        return $this;
    }

    /**
     * @return PhpBuilder
     */
    protected function open()
    {
        $this->output .= '<?php' . PHP_EOL . PHP_EOL;
        $this->pos = self::POS_NEWLINE;

        return $this;
    }

    /**
     * @param string $varName
     *
     * @return PhpBuilder
     */
    protected function appendVar($varName)
    {
        $this->output .= '$' . $varName;
        $this->pos = self::POS_VAR;

        return $this;
    }

    /**
     * @param string $key
     *
     * @return PhpBuilder
     */
    protected function appendKey($key)
    {
        $this->output .= "['" . $key . "']";
        $this->pos = self::POS_ARRAY_KEY;

        return $this;
    }

    /**
     * @return PhpBuilder
     */
    protected function appendVarAssignation()
    {
        $this->output .= ' = ';
        $this->pos = self::POS_ASSIGN;

        return $this;
    }

    /**
     * @param string $value
     *
     * @return PhpBuilder
     */
    protected function appendValue($value)
    {
        $this->output .= (string) $value;
        $this->pos = self::POS_VALUE;

        return $this;
    }

    /**
     * @return PhpBuilder
     */
    protected function appendEndOfLine()
    {
        $this->output .= ';' . PHP_EOL;
        $this->pos = self::POS_NEWLINE;

        return $this;
    }
}
