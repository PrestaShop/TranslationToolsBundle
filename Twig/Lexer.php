<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Twig;

use Twig\Source;
use Twig\TokenStream;

class Lexer extends \Twig\Lexer
{
    /**
     * @var array
     */
    protected $comments = [];

    public function tokenize($code, $name = null): TokenStream
    {
        if (!$code instanceof Source) {
            $source = new Source($code, $name);
        } else {
            $source = $code;
            $code = $source->getCode();
        }
        preg_match_all('|\{#\s(.+)\s#\}|i', $code, $matches, \PREG_OFFSET_CAPTURE);
        foreach (current($matches) as $key => $match) {
            $matchValue = end($matches)[$key][0];
            $lineNumber = substr_count(mb_substr($code, 0, $match[1]), PHP_EOL) + 1;
            $this->comments[] = [
                'line' => $lineNumber,
                'comment' => $matchValue,
                'file' => $source->getPath() . $source->getName(),
            ];
        }

        return parent::tokenize($source);
    }

    public function getComments()
    {
        return $this->comments;
    }
}
