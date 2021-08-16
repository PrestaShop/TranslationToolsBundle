<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Parser;

class CrowdinPhpParser
{
    /**
     * Extracts trans message from PHP tokens.
     *
     * @param $file            $tokens
     * @param MessageCatalogue $catalog
     */
    public function parseFileTokens($file)
    {
        preg_match_all('/^(\$_\w+\[\'.+\'\]) = \'(.*)\';/m', file_get_contents($file), $matches);

        foreach ($matches[0] as $key => $match) {
            yield [
                'message' => $matches[2][$key],
                'key' => $matches[1][$key],
            ];
        }
    }
}
